<?php
/**
 * PHPUnit bootstrap for SimpleTOC.
 *
 * @package simpletoc
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = getenv( 'WP_DEVELOP_DIR' ) ? getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit' : '';
}

if ( ! $_tests_dir || ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	$_tests_dir = '/var/www/html/tests/phpunit';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find the WordPress test suite.' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

$autoload_file = dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! file_exists( $autoload_file ) ) {
	echo 'Could not find Composer dependencies. Run `composer install` before PHPUnit.' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

require_once $autoload_file;
require_once $_tests_dir . '/includes/functions.php';

/**
 * Loads the plugin for the WordPress test suite.
 */
function simpletoc_manually_load_plugin() {
	require dirname( __DIR__ ) . '/plugin.php';
}

tests_add_filter( 'muplugins_loaded', 'simpletoc_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
