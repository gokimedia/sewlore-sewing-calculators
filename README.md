# Sewlore Sewing Calculators

Add fabric stretch and recovery, button-centre intervals, and cm/mm/in conversion to a WordPress sewing lesson or project page. All calculations run locally in the visitor's browser. The plugin contains no telemetry, submitted forms, account requirement, database storage or remote executable code.

## Install and use

1. [Download the installable 0.1.0 ZIP](https://github.com/gokimedia/sewlore-sewing-calculators/raw/refs/heads/main/releases/sewlore-sewing-calculators-0.1.0.zip).
2. In WordPress, open **Plugins → Add New → Upload Plugin**, select the ZIP, and activate it.
3. Add the built-in **Shortcode** block to a post or page and enter:

```text
[sewlore_sewing_calculators]
```

Inputs start blank. Choose a single tool if needed:

```text
[sewlore_sewing_calculators tool="stretch"]
[sewlore_sewing_calculators tool="buttons"]
[sewlore_sewing_calculators tool="units"]
```

Multiple instances are independent. There is no custom block framework; the standard WordPress Shortcode block is sufficient. Assets are enqueued on pages containing a calculator. Formula explanations remain readable without JavaScript; interactive results require JavaScript.

## Calculation methods

**Fabric stretch and recovery** uses one marked direction and consistent units:

```text
stretch % = 100 × (stretched − original) / original
residual change % = 100 × (released − original) / original
recovered extension % = 100 × (stretched − released) / (stretched − original)
```

For the hypothetical readings 10 cm, 14 cm and 10.5 cm, results are 40%, 5% and 87.5%. A zero stretched extension makes recovery not defined. A released reading outside the original-to-stretched range is flagged, and signed values remain visible without clamping.

**Button centres** use `interval = (opening − top offset − bottom offset) / (count − 1)`. A hypothetical 60 cm opening, 3 cm offsets and seven centres gives six 9 cm intervals, with positions 3, 12, 21, 30, 39, 48 and 57 cm from the top. Counts must be whole numbers from 2 to 100.

**Unit conversion** uses 1 cm = 10 mm and 1 international inch = exactly 25.4 mm. The separate converter changes units; the fabric/button menus only label readings already expressed in a common unit.

Decimal points and decimal commas are accepted without grouping separators. Scientific notation and partial numeric parses are rejected. Invalid, zero-denominator, non-finite and unreliable ranges give explanations rather than misleading results. Results display up to six decimal places; that display does not increase measurement precision.

For consistent sample readings, see the [Sewlore measurement-method guide](https://sewlore.com/pages/fabric-measurement-methods-guide). These calculators do arithmetic from your readings; they do not measure fabric, choose garment ease, diagnose fit or guarantee closure performance. Follow the pattern instructions and test your own sample.

## Optional reference and privacy

No public source link or credit is added by default. Only an explicit per-instance choice displays the method reference:

```text
[sewlore_sewing_calculators show_reference="yes"]
```

Omitting or removing that choice never changes functionality. The plugin adds no cookies, local/session storage, AJAX/REST endpoint or background requests. Clicking an enabled reference link visits that external site using normal browser behaviour. Other site analytics and hosting logs are outside this plugin's behaviour.

**Tools → Sewing Calculators** provides local documentation for authorized administrators. There is no settings mutation, setup wizard or site-wide notice. Deactivation/deletion has no plugin data to remove; remove shortcodes from posts if you stop using the plugin.

## Validation and requirements

Version 0.1.0 was activated and tested in an isolated **WordPress 7.1.3 / PHP 8.5.11** environment. Twenty-seven integration checks passed, and **official WordPress Plugin Check 2.1.0 static and runtime checks** reported “Checks complete. No errors found.” The checker's WP-CLI dependency emitted an unrelated PHP 8.5 deprecation; no plugin PHP warnings were observed during rendering.

The original build also passed 21 Node test groups and 4,000 deterministic generated arithmetic cases. Native browser checks confirmed all three worked examples on desktop and a 390 px viewport without horizontal overflow. This is validation of the performed scenarios, not a claim of universal theme/host or screen-reader certification.

Declared minimum requirements are WordPress 6.5 and PHP 7.4. PHP 7.4 grammar was checked, but the minimum PHP runtime was not used. Use a maintained PHP release suitable for your WordPress host.

The dependency-free arithmetic suite included here is reproducible with Node.js 18 or newer:

```sh
node --test tests/math.test.cjs
```

The complete readable plugin source is included. No compilation or CDN is needed. The included ZIP contains exactly the eight installable plugin source/license/readme/POT files; it excludes demo pages, tests and private QA material.

This repository package is not a WordPress.org directory approval. Directory submission, acceptance, compatibility beyond performed checks and public search indexing must be verified separately.

## License and attribution

[MIT](LICENSE), Copyright © 2026 Sewlore. The stretch arithmetic is adapted from Sewlore's existing MIT sewing-math package; the attribution and complete permission notice are retained in [NOTICE](NOTICE) and [LICENSE](LICENSE). AI-assisted development; the worked examples are hypothetical and no physical fabric or garment was measured for them.
