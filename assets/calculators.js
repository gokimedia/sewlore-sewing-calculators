/** Sewlore local calculator UI. MIT License. Copyright (c) 2026 Sewlore. */
(function () {
	'use strict';
	var math = window.sewloreSewingCalculatorMath;
	if (!math) return;
	function initialize() {
		document.querySelectorAll('[data-sewlore-calculators]').forEach(function (widget) {
			if (widget.dataset.sewloreInitialized === 'yes') return;
			widget.dataset.sewloreInitialized = 'yes';
			var strings;
			try { strings = JSON.parse(widget.getAttribute('data-strings')); } catch (error) { return; }
			var locale = document.documentElement.lang || 'en';
			var formatter;
			try { formatter = new Intl.NumberFormat(locale, { maximumFractionDigits: 6 }); }
			catch (error) { formatter = new Intl.NumberFormat('en', { maximumFractionDigits: 6 }); }
			function format(value) { return formatter.format(Object.is(value, -0) ? 0 : value); }
			widget.querySelectorAll('[data-tool]').forEach(function (tool) {
				var name = tool.getAttribute('data-tool');
				var result = tool.querySelector('[data-result]');
				var button = tool.querySelector('[data-calculate]');
				var currentField = null;
				function field(key) { return tool.querySelector('[data-field="' + name + '-' + key + '"]'); }
				function number(key) {
					currentField = field(key);
					return math.parseDecimal(currentField.value);
				}
				function paragraph(text, className) {
					var element = document.createElement('p');
					element.textContent = text;
					if (className) element.className = className;
					result.appendChild(element);
				}
				function resetValidity() {
					tool.querySelectorAll('[data-field]').forEach(function (input) {
						input.removeAttribute('aria-invalid');
					});
				}
				function changed() {
					resetValidity();
					result.classList.remove('sewlore-calculators__result--error');
					result.textContent = strings.changed;
				}
				result.textContent = strings.initial;
				tool.addEventListener('input', changed);
				tool.addEventListener('change', changed);
				button.addEventListener('click', function () {
					resetValidity();
					result.classList.remove('sewlore-calculators__result--error');
					result.replaceChildren();
					currentField = null;
					try {
						if (name === 'stretch') {
							var original = number('original');
							var stretched = number('stretched');
							var released = number('released');
							if (original <= 0) currentField = field('original');
							else if (stretched <= 0 || stretched < original) currentField = field('stretched');
							else if (released <= 0) currentField = field('released');
							else currentField = null;
							var stretch = math.stretch(original, stretched, released);
							paragraph(strings.stretchLabel + ': ' + format(stretch.stretchPercent) + '%', 'sewlore-calculators__metric');
							paragraph(strings.growthLabel + ': ' + format(stretch.residualChangePercent) + '%');
							paragraph(strings.recoveryLabel + ': ' + (stretch.recoveredExtensionPercent === null ? strings.undefined : format(stretch.recoveredExtensionPercent) + '%'));
							paragraph(stretch.unusualReleasedLength ? strings.unusual : strings.signed);
						} else if (name === 'buttons') {
							var length = number('length');
							var top = number('top');
							var bottom = number('bottom');
							var count = number('count');
							if (length <= 0) currentField = field('length');
							else if (top < 0) currentField = field('top');
							else if (bottom < 0) currentField = field('bottom');
							else if (!Number.isInteger(count) || count < 2 || count > 100) currentField = field('count');
							else currentField = null;
							var buttons = math.buttons(length, top, bottom, count);
							var unit = field('unit').value;
							paragraph(strings.spacingLabel + ': ' + format(buttons.spacing) + ' ' + unit, 'sewlore-calculators__metric');
							paragraph(strings.positionsLabel + ': ' + buttons.positions.map(format).join('; ') + ' ' + unit + '.');
						} else if (name === 'units') {
							var value = number('value');
							currentField = value < 0 ? field('value') : null;
							var to = field('to').value;
							var converted = math.convert(value, field('from').value, to);
							paragraph(strings.conversion + ': ' + format(converted) + ' ' + to, 'sewlore-calculators__metric');
						}
						paragraph(strings.rounding);
					} catch (error) {
						result.classList.add('sewlore-calculators__result--error');
						paragraph(strings.errorPrefix + (strings[error.code] || strings.range));
						if (currentField) {
							currentField.setAttribute('aria-invalid', 'true');
							currentField.focus();
						}
					}
				});
			});
		});
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
	else initialize();
}());
