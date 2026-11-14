<?php
/**
 * SimpleTOC admin settings page.
 *
 * @package simpletoc
 */

namespace MToensing\SimpleTOC;

/**
 * Returns the effective global setting. Filters override saved options.
 * A false global setting still allows individual blocks to opt in.
 *
 * @param string $option Setting name.
 * @return bool
 */
function simpletoc_setting_enabled( $option ) {
	return (bool) apply_filters( $option, (bool) get_option( $option, false ) );
}

/**
 * Whether scroll highlighting is enforced globally.
 *
 * @return bool
 */
function simpletoc_scroll_spy_enabled() {
	return simpletoc_setting_enabled( 'simpletoc_scroll_spy_enabled' );
}

/**
 * Global values shared by the block editor settings and script fallback.
 *
 * @return array
 */
function simpletoc_get_editor_settings() {
	return array(
		'settingsUrl'         => admin_url( 'options-general.php?page=simpletoc' ),
		'scrollSpyEnabled'    => simpletoc_scroll_spy_enabled(),
		'autoRefreshDisabled' => simpletoc_setting_enabled( 'simpletoc_autoupdate_enabled' ),
		'accordionEnabled'    => simpletoc_setting_enabled( 'simpletoc_accordion_enabled' ),
		'smoothEnabled'       => simpletoc_setting_enabled( 'simpletoc_smooth_enabled' ),
		'absoluteUrlsEnabled' => simpletoc_setting_enabled( 'simpletoc_absolute_urls_enabled' ),
		'boxStyleEnabled'     => simpletoc_setting_enabled( 'simpletoc_box_style_enabled' ),
	);
}

/**
 * Add SimpleTOC global settings page.
 */
function simpletoc_add_settings_page() {
	add_options_page(
		__( 'SimpleTOC Settings', 'simpletoc' ),
		__( 'SimpleTOC', 'simpletoc' ),
		'manage_options',
		'simpletoc',
		__NAMESPACE__ . '\simpletoc_settings_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\simpletoc_add_settings_page' );

/**
 * SimpleTOC settings page content.
 */
function simpletoc_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'SimpleTOC Settings', 'simpletoc' ); ?></h1>
		<form method="post" action="options.php">
			<?php
				settings_fields( 'simpletoc_settings' );
				do_settings_sections( 'simpletoc' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Register SimpleTOC settings.
 */
function simpletoc_register_settings() {
	$options = array(
		'simpletoc_accordion_enabled',
		'simpletoc_smooth_enabled',
		'simpletoc_absolute_urls_enabled',
		'simpletoc_autoupdate_enabled',
		'simpletoc_box_style_enabled',
		'simpletoc_scroll_spy_enabled',
	);

	foreach ( $options as $option ) {
		if ( null !== apply_filters( $option, null ) ) {
			continue;
		}

		register_setting(
			'simpletoc_settings',
			$option,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => false,
				'show_in_rest'      => 'simpletoc_autoupdate_enabled' === $option,
			)
		);
	}

	// Add settings sections and fields.
	add_settings_section(
		'simpletoc_wrapper_section',
		esc_html__( 'Global settings', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_wrapper_section_callback',
		'simpletoc'
	);

	add_settings_field(
		'simpletoc_accordion_enabled',
		esc_html__( 'Force accordion menu', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_accordion_enabled_callback',
		'simpletoc',
		'simpletoc_wrapper_section'
	);

	add_settings_field(
		'simpletoc_smooth_enabled',
		esc_html__( 'Force smooth scrolling', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_smooth_enabled_callback',
		'simpletoc',
		'simpletoc_wrapper_section'
	);

	add_settings_field(
		'simpletoc_absolute_urls_enabled',
		esc_html__( 'Force absolute urls', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_absolute_urls_enabled_callback',
		'simpletoc',
		'simpletoc_wrapper_section'
	);

	// Add the autoupdate settings field.
	add_settings_field(
		'simpletoc_autoupdate_enabled',
		esc_html__( 'Force manual refresh', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_autoupdate_enabled_callback',
		'simpletoc',
		'simpletoc_wrapper_section'
	);

	add_settings_field(
		'simpletoc_scroll_spy_enabled',
		esc_html__( 'Force highlight current section', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_scroll_spy_enabled_callback',
		'simpletoc',
		'simpletoc_wrapper_section'
	);

	add_settings_field(
		'simpletoc_box_style_enabled',
		esc_html__( 'Force box style', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_box_style_enabled_callback',
		'simpletoc',
		'simpletoc_wrapper_section'
	);
}

add_action( 'admin_init', __NAMESPACE__ . '\simpletoc_register_settings' );

/**
 * SimpleTOC wrapper section callback.
 */
function simpletoc_wrapper_section_callback() {
	$donatelink = '<a href="https://marc.tv/out/donate">' . esc_html__( 'Donate here!', 'simpletoc' ) . '</a>';

	echo '<p>' .
		esc_html__( 'Enforce these settings globally, ignoring any block-level configurations.', 'simpletoc' ) . '</p><p>' .
		esc_html__( 'Think about making a donation if you use any of these features.', 'simpletoc' ) . ' ' .
		$donatelink .
		'</p>';
}

/**
 * Outputs a global checkbox with its effective value and filter ownership.
 *
 * @param string $option Setting name.
 * @param string $description Help text.
 */
function simpletoc_setting_checkbox( $option, $description ) {
	$controlled = null !== apply_filters( $option, null );
	echo '<input type="checkbox" name="' . esc_attr( $option ) . '" id="' . esc_attr( $option ) . '" value="1" ' . checked( true, simpletoc_setting_enabled( $option ), false ) . disabled( $controlled, true, false ) . ' />';
	echo '<label for="' . esc_attr( $option ) . '" class="description">' . esc_html( $description ) . '</label>';
	if ( $controlled ) {
		/* translators: %s: WordPress filter name. */
		echo '<p class="description">' . esc_html( sprintf( __( 'Setting controlled by the %s filter.', 'simpletoc' ), $option ) ) . '</p>';
	}
}

/**
 * Outputs the accordion setting.
 */
function simpletoc_accordion_enabled_callback() {
	simpletoc_setting_checkbox( 'simpletoc_accordion_enabled', __( 'Collapses the table of contents by default. Click the heading to expand it. Adds minimal JavaScript and CSS.', 'simpletoc' ) );
}

/**
 * Outputs the smooth setting.
 */
function simpletoc_smooth_enabled_callback() {
	simpletoc_setting_checkbox( 'simpletoc_smooth_enabled', __( 'Adds the following CSS to the HTML element: "scroll-behavior: smooth;"', 'simpletoc' ) );
}

/**
 * Outputs the absolute urls setting.
 */
function simpletoc_absolute_urls_enabled_callback() {
	simpletoc_setting_checkbox( 'simpletoc_absolute_urls_enabled', __( 'Adds the permalink url to the fragment.', 'simpletoc' ) );
}

/**
 * Outputs the autoupdate setting.
 */
function simpletoc_autoupdate_enabled_callback() {
	simpletoc_setting_checkbox( 'simpletoc_autoupdate_enabled', __( 'Deactivate the automatic table of contents refresh feature.', 'simpletoc' ) );
}

/**
 * Outputs the box style setting.
 */
function simpletoc_box_style_enabled_callback() {
	simpletoc_setting_checkbox( 'simpletoc_box_style_enabled', __( 'Applies the Box style with the default gray background to all SimpleTOC blocks.', 'simpletoc' ) );
}

/**
 * Outputs the scroll spy setting.
 */
function simpletoc_scroll_spy_enabled_callback() {
	simpletoc_setting_checkbox( 'simpletoc_scroll_spy_enabled', __( 'Underlines the current section link in all SimpleTOC blocks in supported browsers. No JavaScript fallback.', 'simpletoc' ) );
}
