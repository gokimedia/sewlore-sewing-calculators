<?php
/**
 * Plugin Name: Sewlore Sewing Calculators
 * Description: Local fabric stretch and recovery, button-centre spacing, and centimetre, millimetre and inch calculators.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Sewlore
 * Author URI: https://sewlore.com/
 * License: MIT
 * License URI: https://opensource.org/license/mit/
 * Text Domain: sewlore-sewing-calculators
 * Domain Path: /languages
 *
 * @package SewloreSewingCalculators
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Readable local calculators with no storage, outbound requests or public credits by default. */
final class Sewlore_Sewing_Calculators {
	const VERSION = '0.1.0';
	const HANDLE  = 'sewlore-sewing-calculators';

	/** Register hooks without creating settings, data tables or background jobs. */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	/** Register the shortcode and self-hosted assets. */
	public static function register() {
		$base = plugin_dir_url( __FILE__ );
		wp_register_style( self::HANDLE, $base . 'assets/calculators.css', array(), self::VERSION );
		wp_register_script( self::HANDLE . '-math', $base . 'assets/math.js', array(), self::VERSION, true );
		wp_register_script( self::HANDLE, $base . 'assets/calculators.js', array( self::HANDLE . '-math' ), self::VERSION, true );
		add_shortcode( 'sewlore_sewing_calculators', array( __CLASS__, 'render_shortcode' ) );
	}

	/** Enqueue early for ordinary post content; render_shortcode also covers dynamic placements. */
	public static function maybe_enqueue() {
		$post = get_post();
		if ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'sewlore_sewing_calculators' ) ) {
			self::enqueue();
		}
	}

	/** Enqueue only on a page that contains the calculator. */
	private static function enqueue() {
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
	}

	/** Browser strings are translated in PHP then safely encoded into each widget. */
	private static function strings() {
		return array(
			'initial'        => __( 'Enter your readings, then choose Calculate. Nothing is submitted or saved by this calculator.', 'sewlore-sewing-calculators' ),
			'changed'        => __( 'Inputs changed. Calculate again to update the result.', 'sewlore-sewing-calculators' ),
			'required'       => __( 'Enter a number for each measurement. Use a decimal point or decimal comma, without grouping separators or scientific notation.', 'sewlore-sewing-calculators' ),
			'positive'       => __( 'All fabric lengths and the opening length must be greater than zero.', 'sewlore-sewing-calculators' ),
			'nonnegative'    => __( 'Offsets and the conversion length must be zero or greater.', 'sewlore-sewing-calculators' ),
			'stretched'      => __( 'Stretched length cannot be less than original length. Check the marked points and units.', 'sewlore-sewing-calculators' ),
			'count'          => __( 'Use a whole number from 2 to 100 for the number of button centres.', 'sewlore-sewing-calculators' ),
			'span'           => __( 'The two offsets together must be less than the opening length.', 'sewlore-sewing-calculators' ),
			'range'          => __( 'These values exceed a reliable numerical range. Recheck the measurements and use a suitable unit.', 'sewlore-sewing-calculators' ),
			'unit'           => __( 'Choose centimetres, millimetres or inches.', 'sewlore-sewing-calculators' ),
			'errorPrefix'    => __( 'Check your readings: ', 'sewlore-sewing-calculators' ),
			'stretchLabel'   => __( 'Stretch', 'sewlore-sewing-calculators' ),
			'growthLabel'    => __( 'Residual change after release', 'sewlore-sewing-calculators' ),
			'recoveryLabel'  => __( 'Recovered extension', 'sewlore-sewing-calculators' ),
			'undefined'      => __( 'Not defined: the stretched reading equals the original reading.', 'sewlore-sewing-calculators' ),
			'unusual'        => __( 'The released reading is outside the original-to-stretched range. The signed results are shown without clamping. Remeasure the sample; this arithmetic does not diagnose fabric behaviour.', 'sewlore-sewing-calculators' ),
			'signed'         => __( 'A negative residual change means the released reading is shorter than the original. Recovery is a percentage of the stretched extension, not of the original length.', 'sewlore-sewing-calculators' ),
			'spacingLabel'   => __( 'Interval between centres', 'sewlore-sewing-calculators' ),
			'positionsLabel' => __( 'Centre positions measured from the top', 'sewlore-sewing-calculators' ),
			'conversion'     => __( 'Converted length', 'sewlore-sewing-calculators' ),
			'rounding'       => __( 'Results display up to six decimal places. Keep full readings; round only when marking with your ruler.', 'sewlore-sewing-calculators' ),
		);
	}

	/**
	 * Render a widget. A source link requires an explicit per-shortcode opt-in.
	 *
	 * @param array|string $attributes Author-supplied shortcode attributes.
	 * @return string Escaped HTML.
	 */
	public static function render_shortcode( $attributes = array() ) {
		$attributes = shortcode_atts(
			array( 'tool' => 'all', 'show_reference' => 'no' ),
			$attributes,
			'sewlore_sewing_calculators'
		);
		$tool = is_string( $attributes['tool'] ) ? $attributes['tool'] : 'all';
		if ( ! in_array( $tool, array( 'all', 'stretch', 'buttons', 'units' ), true ) ) {
			$tool = 'all';
		}
		$show_reference = is_string( $attributes['show_reference'] ) && 'yes' === strtolower( trim( $attributes['show_reference'] ) );
		$id             = wp_unique_id( 'sewlore-calc-' );
		self::enqueue();
		ob_start();
		?>
		<div class="sewlore-calculators" data-sewlore-calculators data-strings="<?php echo esc_attr( wp_json_encode( self::strings() ) ); ?>">
			<p class="sewlore-calculators__intro"><?php esc_html_e( 'Sewing measurement calculators', 'sewlore-sewing-calculators' ); ?></p>
			<p><?php esc_html_e( 'Arithmetic for your own readings. Use the same unit and the same marked points within each fabric test. A decimal point or decimal comma is accepted; do not use thousands separators.', 'sewlore-sewing-calculators' ); ?></p>
			<?php if ( in_array( $tool, array( 'all', 'stretch' ), true ) ) : ?>
				<?php self::stretch( $id ); ?>
			<?php endif; ?>
			<?php if ( in_array( $tool, array( 'all', 'buttons' ), true ) ) : ?>
				<?php self::buttons( $id ); ?>
			<?php endif; ?>
			<?php if ( in_array( $tool, array( 'all', 'units' ), true ) ) : ?>
				<?php self::units( $id ); ?>
			<?php endif; ?>
			<?php if ( $show_reference ) : ?>
				<p class="sewlore-calculators__reference"><a href="<?php echo esc_url( 'https://sewlore.com/pages/fabric-measurement-methods-guide' ); ?>"><?php esc_html_e( 'Measurement-method reference from Sewlore', 'sewlore-sewing-calculators' ); ?></a></p>
			<?php endif; ?>
			<noscript><p><?php esc_html_e( 'Enable JavaScript for the interactive results. The formulas and worked examples above remain available.', 'sewlore-sewing-calculators' ); ?></p></noscript>
		</div>
		<?php
		return ob_get_clean();
	}

	/** Output an accessible text numeric input. @param string $id Unique widget prefix. @param string $name Field key. @param string $label Translated visible label. */
	private static function number_input( $id, $name, $label ) {
		?>
		<div class="sewlore-calculators__field">
			<label for="<?php echo esc_attr( $id . '-' . $name ); ?>"><?php echo esc_html( $label ); ?></label>
			<input id="<?php echo esc_attr( $id . '-' . $name ); ?>" data-field="<?php echo esc_attr( $name ); ?>" type="text" inputmode="decimal" maxlength="48" autocomplete="off" spellcheck="false" aria-describedby="<?php echo esc_attr( $id . '-help-' . explode( '-', $name )[0] ); ?>">
		</div>
		<?php
	}

	/** Output a local unit selector. @param string $id Prefix. @param string $name Key. @param string $label Translated label. */
	private static function unit_select( $id, $name, $label ) {
		?>
		<div class="sewlore-calculators__field">
			<label for="<?php echo esc_attr( $id . '-' . $name ); ?>"><?php echo esc_html( $label ); ?></label>
			<select id="<?php echo esc_attr( $id . '-' . $name ); ?>" data-field="<?php echo esc_attr( $name ); ?>">
				<option value="cm"><?php esc_html_e( 'Centimetres (cm)', 'sewlore-sewing-calculators' ); ?></option>
				<option value="mm"><?php esc_html_e( 'Millimetres (mm)', 'sewlore-sewing-calculators' ); ?></option>
				<option value="in"><?php esc_html_e( 'Inches (in)', 'sewlore-sewing-calculators' ); ?></option>
			</select>
		</div>
		<?php
	}

	/** Output action and an initially empty live region. @param string $id Prefix. @param string $tool Tool key. */
	private static function actions( $id, $tool ) {
		?>
		<button type="button" data-calculate="<?php echo esc_attr( $tool ); ?>" aria-controls="<?php echo esc_attr( $id . '-result-' . $tool ); ?>"><?php esc_html_e( 'Calculate', 'sewlore-sewing-calculators' ); ?></button>
		<div id="<?php echo esc_attr( $id . '-result-' . $tool ); ?>" class="sewlore-calculators__result" role="status" aria-live="polite" aria-atomic="true" data-result></div>
		<?php
	}

	/** Fabric stretch and released-length arithmetic. @param string $id Prefix. */
	private static function stretch( $id ) {
		?>
		<fieldset class="sewlore-calculators__tool" data-tool="stretch">
			<legend><?php esc_html_e( 'Fabric stretch and recovery', 'sewlore-sewing-calculators' ); ?></legend>
			<p id="<?php echo esc_attr( $id . '-help-stretch' ); ?>"><?php esc_html_e( 'Record one marked direction before stretching, while stretched, and after release. These readings do not determine suitable garment ease or replace a fabric test.', 'sewlore-sewing-calculators' ); ?></p>
			<div class="sewlore-calculators__grid">
				<?php self::number_input( $id, 'stretch-original', __( 'Original length', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::number_input( $id, 'stretch-stretched', __( 'Stretched length', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::number_input( $id, 'stretch-released', __( 'Released length', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::unit_select( $id, 'stretch-unit', __( 'Unit for all three lengths', 'sewlore-sewing-calculators' ) ); ?>
			</div>
			<?php self::actions( $id, 'stretch' ); ?>
			<details><summary><?php esc_html_e( 'Formulas and hypothetical example', 'sewlore-sewing-calculators' ); ?></summary>
				<p><?php esc_html_e( 'Stretch = 100 × (stretched − original) ÷ original. Residual change = 100 × (released − original) ÷ original. Recovered extension = 100 × (stretched − released) ÷ (stretched − original).', 'sewlore-sewing-calculators' ); ?></p>
				<p><?php esc_html_e( 'For original 10 cm, stretched 14 cm and released 10.5 cm: stretch is 40%, residual change is 5%, and recovered extension is 87.5%. No sample was measured for this example.', 'sewlore-sewing-calculators' ); ?></p>
			</details>
		</fieldset>
		<?php
	}

	/** Button centre planning. @param string $id Prefix. */
	private static function buttons( $id ) {
		?>
		<fieldset class="sewlore-calculators__tool" data-tool="buttons">
			<legend><?php esc_html_e( 'Button-centre intervals', 'sewlore-sewing-calculators' ); ?></legend>
			<p id="<?php echo esc_attr( $id . '-help-buttons' ); ?>"><?php esc_html_e( 'Offsets locate the first centre from the top and the last centre from the bottom. Use 2–100 centres. Check closure instructions, seam allowances and buttonhole sizes before marking.', 'sewlore-sewing-calculators' ); ?></p>
			<div class="sewlore-calculators__grid">
				<?php self::number_input( $id, 'buttons-length', __( 'Opening length', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::number_input( $id, 'buttons-top', __( 'First centre: offset from top', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::number_input( $id, 'buttons-bottom', __( 'Last centre: offset from bottom', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::number_input( $id, 'buttons-count', __( 'Number of centres', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::unit_select( $id, 'buttons-unit', __( 'Unit for all three lengths', 'sewlore-sewing-calculators' ) ); ?>
			</div>
			<?php self::actions( $id, 'buttons' ); ?>
			<details><summary><?php esc_html_e( 'Formula and hypothetical example', 'sewlore-sewing-calculators' ); ?></summary>
				<p><?php esc_html_e( 'Interval = (opening length − top offset − bottom offset) ÷ (number of centres − 1). Position i = top offset + i × interval, with i starting at zero.', 'sewlore-sewing-calculators' ); ?></p>
				<p><?php esc_html_e( 'A 60 cm opening, 3 cm offsets at both ends and 7 centres give six 9 cm intervals. Positions from the top are 3, 12, 21, 30, 39, 48 and 57 cm. No garment was tested for this example.', 'sewlore-sewing-calculators' ); ?></p>
			</details>
		</fieldset>
		<?php
	}

	/** Exact-definition length unit conversion. @param string $id Prefix. */
	private static function units( $id ) {
		?>
		<fieldset class="sewlore-calculators__tool" data-tool="units">
			<legend><?php esc_html_e( 'Length unit conversion', 'sewlore-sewing-calculators' ); ?></legend>
			<p id="<?php echo esc_attr( $id . '-help-units' ); ?>"><?php esc_html_e( 'Enter a non-negative length. This converter changes the unit; the unit menus in the other tools only label the readings you enter.', 'sewlore-sewing-calculators' ); ?></p>
			<div class="sewlore-calculators__grid">
				<?php self::number_input( $id, 'units-value', __( 'Length to convert', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::unit_select( $id, 'units-from', __( 'From unit', 'sewlore-sewing-calculators' ) ); ?>
				<?php self::unit_select( $id, 'units-to', __( 'To unit', 'sewlore-sewing-calculators' ) ); ?>
			</div>
			<?php self::actions( $id, 'units' ); ?>
			<details><summary><?php esc_html_e( 'Conversion definitions', 'sewlore-sewing-calculators' ); ?></summary>
				<p><?php esc_html_e( '1 cm = 10 mm. 1 international inch = exactly 25.4 mm. For example, 2.54 cm converts to 1 in. Conversion does not improve measurement precision.', 'sewlore-sewing-calculators' ); ?></p>
			</details>
		</fieldset>
		<?php
	}

	/** Add a single optional documentation page under Tools. */
	public static function admin_menu() {
		add_management_page( __( 'Sewing Calculators', 'sewlore-sewing-calculators' ), __( 'Sewing Calculators', 'sewlore-sewing-calculators' ), 'manage_options', 'sewlore-sewing-calculators', array( __CLASS__, 'admin_page' ) );
	}

	/** Render documentation only; this page accepts no POST or settings. */
	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sewing Calculators', 'sewlore-sewing-calculators' ); ?></h1>
			<p><?php esc_html_e( 'Add a Shortcode block to a post or page, then paste:', 'sewlore-sewing-calculators' ); ?></p>
			<p><code>[sewlore_sewing_calculators]</code></p>
			<p><?php esc_html_e( 'Choose a single tool with tool="stretch", tool="buttons" or tool="units". The default shows all three.', 'sewlore-sewing-calculators' ); ?></p>
			<h2><?php esc_html_e( 'Optional external reference link', 'sewlore-sewing-calculators' ); ?></h2>
			<p><?php esc_html_e( 'The calculator displays no external link by default. Only add show_reference="yes" if you want a public link to the Sewlore measurement-method guide. Removing the link does not change any calculation.', 'sewlore-sewing-calculators' ); ?></p>
			<p><code>[sewlore_sewing_calculators tool="stretch" show_reference="yes"]</code></p>
			<p><a href="<?php echo esc_url( 'https://sewlore.com/pages/fabric-measurement-methods-guide' ); ?>"><?php esc_html_e( 'Read the measurement-method guide', 'sewlore-sewing-calculators' ); ?></a></p>
			<h2><?php esc_html_e( 'Privacy and limits', 'sewlore-sewing-calculators' ); ?></h2>
			<p><?php esc_html_e( 'Calculations run in the visitor’s browser using assets served by this site. The plugin has no telemetry, forms that submit data, cookies, storage, account requirement, external runtime or background requests. Clicking an optional reference link visits that external site.', 'sewlore-sewing-calculators' ); ?></p>
			<p><?php esc_html_e( 'These tools calculate arithmetic from user readings. They do not measure fabrics, select garment ease, diagnose fit or guarantee closure performance. Check the relevant pattern and test a sample.', 'sewlore-sewing-calculators' ); ?></p>
		</div>
		<?php
	}
}

Sewlore_Sewing_Calculators::boot();
