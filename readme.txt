=== Sewlore Sewing Calculators ===
Tags: sewing, calculator, fabric, measurements
Requires at least: 6.5
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 0.1.0
License: MIT
License URI: https://opensource.org/license/mit/

Fabric stretch and recovery, button-centre intervals, and cm/mm/in conversion. Local calculations with formulas and worked examples.

== Description ==

Add practical measurement calculators to a sewing lesson, tutorial or project page with one shortcode. All arithmetic runs locally in the visitor's browser using scripts served by your WordPress site. There is no account, API key, paid feature, tracking, database storage or external executable code.

The three tools are:

* Fabric stretch and recovery: compare original, stretched and released readings in one marked direction. Signed residual change and unusual released readings are explained. Recovery is not defined when there is no stretched extension.
* Button-centre intervals: divide the usable span between two centre offsets into equal intervals and list each centre position from the top. Use 2–100 centres.
* Length conversion: convert non-negative values between centimetres, millimetres and international inches.

Every tool includes its formula, an explicitly hypothetical worked example, visible labels, keyboard controls and a live result region. Decimal points and decimal commas are accepted. Thousands separators and scientific notation are rejected to avoid ambiguous readings. Empty, invalid, non-finite and unsuitable values produce an explanation instead of a misleading result.

These are arithmetic tools. They do not measure fabric, choose garment ease, diagnose fit or guarantee closure performance. Check your pattern instructions, keep original readings and units, and test your own sample. Results display up to six decimal places; this does not imply that your measurements have six-decimal precision.

For the underlying recording method, see the [Sewlore measurement-method guide](https://sewlore.com/pages/fabric-measurement-methods-guide). That page is optional reading; the plugin works independently of it.

**No public reference link by default.** The site author may explicitly opt in with `show_reference="yes"`. Removing or omitting that attribute never changes functionality. No hidden links or credits are added.

== Installation ==

1. Upload the plugin ZIP through Plugins > Add New > Upload Plugin, or copy the `sewlore-sewing-calculators` folder to `wp-content/plugins`.
2. Activate Sewlore Sewing Calculators.
3. Insert WordPress's built-in Shortcode block into a post or page and paste `[sewlore_sewing_calculators]`.
4. Publish or preview the page, enter your own readings and select Calculate. Inputs start blank.

Tools > Sewing Calculators contains the same usage instructions. No setup wizard or site-wide notice is added.

== Frequently Asked Questions ==

= How do I show a single calculator? =

Use `[sewlore_sewing_calculators tool="stretch"]`, `[sewlore_sewing_calculators tool="buttons"]` or `[sewlore_sewing_calculators tool="units"]`. Omitting `tool` displays all three. Multiple instances on the same page are independent.

= Is there a custom Gutenberg block? =

Use the built-in Shortcode block. Version 0.1.0 does not register a separate block or load an editor framework.

= Does changing a unit menu convert the existing fabric readings? =

No. The fabric and button tools' menus label all readings in that tool. Enter measurements already expressed in the same unit. Use the separate length converter when you need to change units.

= Why are recovery values sometimes outside 0–100%? =

The formulas use the supplied readings without clamping. A released reading outside the original-to-stretched range is flagged for remeasurement. When stretched equals original, recovered extension has a zero denominator and is reported as not defined.

= How can I display the optional method reference? =

Only if you want a public external link, add `show_reference="yes"`, for example `[sewlore_sewing_calculators tool="stretch" show_reference="yes"]`. It is off by default and never required.

= What data leaves the visitor's browser? =

The plugin sends no entered readings or results to any server. It creates no cookies, local/session storage, telemetry, AJAX or REST endpoint. Your site serves the local CSS and JavaScript as usual. If the site author enables the optional reference and a visitor clicks it, the browser visits Sewlore under normal browser behaviour. Other WordPress plugins, hosting access logs or site analytics have their own behaviour outside this plugin.

= Does it work without JavaScript? =

Formulas and hypothetical examples remain readable. Interactive results require JavaScript. There is no server fallback and nothing is submitted.

= What happens on deactivation or deletion? =

No plugin data needs removal because no options, tables, posts or files are created. Existing shortcodes remain in post content; remove them if you stop using the plugin.

== Changelog ==

= 0.1.0 =
* Initial local shortcode calculators, validation, signed stretch/recovery results, centre-position list and three-unit conversion.
* Optional method reference disabled by default.

== Upgrade Notice ==

= 0.1.0 =
Initial release. Read the formulas and test your own sample before using the results in a sewing project.
