'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const math = require('../assets/math.js');
function close(actual, expected, epsilon = 1e-10) {
	assert.ok(Math.abs(actual - expected) <= epsilon * Math.max(1, Math.abs(expected)), `${actual} versus ${expected}`);
}
function code(action, expected) { assert.throws(action, error => error instanceof RangeError && error.code === expected); }

test('decimal comma and point accepted without partial or grouping parses', () => {
	for (const [text, expected] of [['10,5', 10.5], ['  .5  ', .5], ['-,5', -.5], ['+12.0', 12], ['0', 0], ['12.', 12]]) close(math.parseDecimal(text), expected);
	for (const text of ['', ' ', '1,000.5', '1.000,5', '1 000', '1e3', 'Infinity', 'NaN', '10cm', '10,2,3', '--1', '0x10', '1'.repeat(49)]) code(() => math.parseDecimal(text), 'required');
	code(() => math.parseDecimal(null), 'required');
});
test('stretch worked example and correctly signed out-of-range released readings', () => {
	assert.deepEqual(math.stretch(10, 14, 10.5), { stretchPercent: 40, residualChangePercent: 5, recoveredExtensionPercent: 87.5, unusualReleasedLength: false });
	const short = math.stretch(10, 14, 9.5);
	close(short.residualChangePercent, -5); close(short.recoveredExtensionPercent, 112.5); assert.equal(short.unusualReleasedLength, true);
	const long = math.stretch(10, 14, 15);
	close(long.recoveredExtensionPercent, -25); assert.equal(long.unusualReleasedLength, true);
});
test('zero extension is explicitly undefined, not zero-percent recovery', () => {
	assert.equal(math.stretch(10, 10, 10).recoveredExtensionPercent, null);
	assert.equal(math.stretch(10, 10, 9).recoveredExtensionPercent, null);
});
test('stretch rejects unsuitable readings and computed overflow', () => {
	for (const values of [[0, 14, 10], [10, 0, 10], [10, 14, 0], [-10, 14, 10]]) code(() => math.stretch(...values), 'positive');
	code(() => math.stretch(10, 9, 10), 'stretched');
	for (const value of [NaN, Infinity, '10', null]) code(() => math.stretch(value, 14, 10), 'range');
	code(() => math.stretch(Number.MIN_VALUE, Number.MAX_VALUE, 1), 'range');
});
test('button interval example, count boundaries and exact last endpoint', () => {
	assert.deepEqual(math.buttons(60, 3, 3, 7), { spacing: 9, positions: [3, 12, 21, 30, 39, 48, 57] });
	assert.deepEqual(math.buttons(10, 0, 0, 2), { spacing: 10, positions: [0, 10] });
	const hundred = math.buttons(100, 0, 0, 100);
	assert.equal(hundred.positions.length, 100); assert.equal(hundred.positions[99], 100);
});
test('button invalid spans, fractional counts and numerical precision collapse are rejected', () => {
	for (const count of [1, 101, 7.5, Infinity, NaN, '7']) code(() => math.buttons(60, 3, 3, count), 'count');
	code(() => math.buttons(0, 0, 0, 2), 'positive');
	code(() => math.buttons(60, -1, 3, 7), 'nonnegative');
	code(() => math.buttons(60, 3, -1, 7), 'nonnegative');
	code(() => math.buttons(6, 3, 3, 7), 'span');
	code(() => math.buttons(6, 4, 3, 7), 'span');
	code(() => math.buttons(1e20, 1e20 - 16384, 0, 100), 'range');
});
test('all 9 unit pairs and round trips agree with exact international inch definition', () => {
	const units = {cm: 10, mm: 1, in: 25.4};
	close(math.convert(2.54, 'cm', 'in'), 1);
	close(math.convert(1, 'in', 'mm'), 25.4);
	for (const from of Object.keys(units)) for (const to of Object.keys(units)) {
		close(math.convert(123.456, from, to), 123.456 * units[from] / units[to]);
		close(math.convert(math.convert(123.456, from, to), to, from), 123.456);
		assert.equal(math.convert(0, from, to), 0);
	}
});
test('conversions reject invalid units, negatives and genuine overflow; avoid needless intermediate overflow', () => {
	code(() => math.convert(1, '__proto__', 'cm'), 'unit');
	code(() => math.convert(1, 'cm', 'constructor'), 'unit');
	code(() => math.convert(-1, 'in', 'cm'), 'nonnegative');
	code(() => math.convert(Infinity, 'in', 'cm'), 'range');
	code(() => math.convert(Number.MAX_VALUE, 'in', 'mm'), 'range');
	assert.equal(math.convert(Number.MAX_VALUE, 'cm', 'cm'), Number.MAX_VALUE);
	assert.equal(math.convert(Number.MAX_VALUE, 'in', 'in'), Number.MAX_VALUE);
});
test('deterministic property checks: 2000 stretch cases and 2000 button grids', () => {
	let seed = 7143;
	function random() { seed = (Math.imul(seed, 1664525) + 1013904223) >>> 0; return seed / 4294967296; }
	for (let i = 0; i < 2000; i += 1) {
		const original = 1 + random() * 100;
		const stretched = original + .01 + random() * 100;
		const released = .1 + random() * 220;
		const result = math.stretch(original, stretched, released);
		close(result.stretchPercent, ((stretched - original) / original) * 100);
		close(result.residualChangePercent, ((released - original) / original) * 100);
		close(result.recoveredExtensionPercent, ((stretched - released) / (stretched - original)) * 100);
		const scale = .2 + random() * 3;
		close(math.stretch(original * scale, stretched * scale, released * scale).stretchPercent, result.stretchPercent);
		const length = 1 + random() * 1000, top = length * random() / 4, bottom = length * random() / 4, count = 2 + Math.floor(random() * 99);
		const grid = math.buttons(length, top, bottom, count);
		assert.equal(grid.positions.length, count); assert.equal(grid.positions[0], top); assert.equal(grid.positions.at(-1), length - bottom);
		for (let j = 1; j < count; j += 1) { assert.ok(grid.positions[j] > grid.positions[j - 1]); close(grid.positions[j] - grid.positions[j - 1], grid.spacing); }
	}
});
