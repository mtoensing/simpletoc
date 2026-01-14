<?php
/**
 * Class to manage headline IDs for the wrapper.
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
 * Wrapper for the IDs. This ensures that IDs are not double-counted or lost in recursion.
 */
class SimpleTOC_Headline_Ids_Wrapper {
	/**
	 * The instance of the SimpleTOC_Headline_Ids class for the wrapper.
	 *
	 * @var SimpleTOC_Headline_Ids
	 */
	private static $inner_content_id_instance = null;

	/**
	 * The instance of the SimpleTOC_Headline_Ids class for the wrapper.
	 *
	 * @var SimpleTOC_Headline_Ids
	 */
	private static $inner_html_id_instance = null;

	/**
	 * Get the instance of the SimpleTOC_Headline_Ids class for the wrapper for inner content.
	 *
	 * @return SimpleTOC_Headline_Ids
	 */
	public static function get_inner_content_id_instance() {
		if ( null === self::$inner_content_id_instance ) {
			self::$inner_content_id_instance = new SimpleTOC_Headline_Ids();
		}
		return self::$inner_content_id_instance;
	}

	/**
	 * Get the instance of the SimpleTOC_Headline_Ids class for the wrapper for inner HTML.
	 *
	 * @return SimpleTOC_Headline_Ids
	 */
	public static function get_inner_html_id_instance() {
		if ( null === self::$inner_html_id_instance ) {
			self::$inner_html_id_instance = new SimpleTOC_Headline_Ids();
		}
		return self::$inner_html_id_instance;
	}
}
