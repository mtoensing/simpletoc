<?php
/**
 * SimpleTOC admin settings page.
 *
 * @package simpletoc
 */

namespace MToensing\SimpleTOC;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
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
		<h1><?php _e( 'SimpleTOC Settings', 'simpletoc' ); ?></h1>
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
	// Register settings and filters for existing features.
	$wrapper_enabled_filter       = apply_filters( 'simpletoc_wrapper_enabled', null );
	$accordion_enabled_filter     = apply_filters( 'simpletoc_accordion_enabled', null );
	$smooth_enabled_filter        = apply_filters( 'simpletoc_smooth_enabled', null );
	$absolute_urls_enabled_filter = apply_filters( 'simpletoc_absolute_urls_enabled', null );
	$autoupdate_enabled_filter    = apply_filters( 'simpletoc_autoupdate_enabled', null );

	if ( null === $wrapper_enabled_filter ) {
		register_setting( 'simpletoc_settings', 'simpletoc_wrapper_enabled' );
	}

	if ( null === $accordion_enabled_filter ) {
		register_setting( 'simpletoc_settings', 'simpletoc_accordion_enabled' );
	}

	if ( null === $smooth_enabled_filter ) {
		register_setting( 'simpletoc_settings', 'simpletoc_smooth_enabled' );
	}

	if ( null === $absolute_urls_enabled_filter ) {
		register_setting( 'simpletoc_settings', 'simpletoc_absolute_urls_enabled' );
	}

	if ( null === $autoupdate_enabled_filter ) {
		register_setting( 'simpletoc_settings', 'simpletoc_autoupdate_enabled', array( 'show_in_rest' => true ) );
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
		'simpletoc_wrapper_enabled',
		esc_html__( 'Force wrapper div', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_wrapper_enabled_callback',
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
		esc_html__( 'Force no auto refresh', 'simpletoc' ),
		__NAMESPACE__ . '\simpletoc_autoupdate_enabled_callback',
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
 * SimpleTOC wrapper enabled callback.
 */
function simpletoc_wrapper_enabled_callback() {
	$wrapper_enabled = get_option( 'simpletoc_wrapper_enabled', false );

	if ( has_filter( 'simpletoc_wrapper_enabled' ) ) {
		echo '<input type="checkbox" name="simpletoc_wrapper_enabled" id="simpletoc_wrapper_enabled" value="1" checked="checked" disabled="disabled" />';
		echo '<label for="simpletoc_wrapper_enabled" class="description">' . esc_html__( 'Setting controlled by "simpletoc_wrapper_enabled" filter. Remove filter to adjust setting.', 'simpletoc' ) . '</label>';
	} else {
		echo '<input type="checkbox" name="simpletoc_wrapper_enabled" id="simpletoc_wrapper_enabled" value="1" ' . checked( 1, $wrapper_enabled, false ) . ' />';
		echo '<label for="simpletoc_wrapper_enabled" class="description">' . esc_html__( 'Additionally adds the role "navigation" and ARIA attributes.', 'simpletoc' ) . '</label>';
	}
}

/**
 * SimpleTOC accordion enabled callback.
 */
function simpletoc_accordion_enabled_callback() {
	$accordion_enabled = get_option( 'simpletoc_accordion_enabled', false );
	if ( $accordion_enabled ) {
		update_option( 'simpletoc_wrapper_enabled', true );
	}
	echo '<input type="checkbox" name="simpletoc_accordion_enabled" id="simpletoc_accordion_enabled" value="1" ' . checked( 1, $accordion_enabled, false ) . ' />';
	echo '<label for="simpletoc_accordion_enabled" class="description">' . esc_html__( 'Adds minimal JavaScript and css styles.', 'simpletoc' ) . ' <strong>' . esc_html__( 'Notice:', 'simpletoc' ) . '</strong> ' . esc_html__( 'This will automatically enable the wrapper div.', 'simpletoc' ) . '</label>';
}

/**
 * SimpleTOC smooth enabled callback.
 */
function simpletoc_smooth_enabled_callback() {
	$smooth_enabled = get_option( 'simpletoc_smooth_enabled', false );
	echo '<input type="checkbox" name="simpletoc_smooth_enabled" id="simpletoc_smooth_enabled" value="1" ' . checked( 1, $smooth_enabled, false ) . ' />';
	echo '<label for="simpletoc_smooth_enabled" class="description">' . esc_html__( 'Adds the following CSS to the HTML element: "scroll-behavior: smooth;"', 'simpletoc' ) . '</label>';
}

/**
 * SimpleTOC absolute urls enabled callback.
 */
function simpletoc_absolute_urls_enabled_callback() {
	$absolute_urls_enabled = get_option( 'simpletoc_absolute_urls_enabled', false );
	echo '<input type="checkbox" name="simpletoc_absolute_urls_enabled" id="simpletoc_absolute_urls_enabled" value="1" ' . checked( 1, $absolute_urls_enabled, false ) . ' />';
	echo '<label for="simpletoc_absolute_urls_enabled" class="description">' . esc_html__( 'Adds the permalink url to the fragment.', 'simpletoc' ) . '</label>';
}

/**
 * SimpleTOC autoupdate enabled callback.
 */
function simpletoc_autoupdate_enabled_callback() {
	$autoupdate_enabled = get_option( 'simpletoc_autoupdate_enabled', false );
	echo '<input type="checkbox" name="simpletoc_autoupdate_enabled" id="simpletoc_autoupdate_enabled" value="1" ' . checked( 1, $autoupdate_enabled, false ) . ' />';
	echo '<label for="simpletoc_autoupdate_enabled" class="description">' . esc_html__( 'Deactivate the automatic table of contents refresh feature.', 'simpletoc' ) . '</label>';
}
