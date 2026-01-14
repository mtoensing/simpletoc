<?php
/**
 * Class to manage headline IDs.
 *
 * Ensures a unique anchor for each headline.
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
 * Class to manage headline IDs.
 *
 * Ensures a unique anchor for each headline.
 */
class SimpleTOC_Headline_Ids {
	/**
	 * Array of headlines and their counts.
	 *
	 * @var array
	 */
	public static $headlines = array();

	/**
	 * Add a headline to the array
	 *
	 * @param string $headline_slug The slug of the headline.
	 */
	protected static function add_headline( $headline_slug ) {
		if ( empty( $headline_slug ) ) {
			return;
		}

		if ( ! isset( self::$headlines[ $headline_slug ] ) ) {
			self::$headlines[ $headline_slug ] = 1;
		} else {
			self::$headlines[ $headline_slug ] = self::get_headline_count( $headline_slug ) + 1;
		}
		if ( self::$headlines[ $headline_slug ] > 1 ) {
			$new_headline_slug = $headline_slug . '-' . self::$headlines[ $headline_slug ];
			if ( isset( self::$headlines[ $new_headline_slug ] ) ) {
				$new_headline_slug = self::add_headline( $new_headline_slug );
			}
			$new_headline_count = self::get_headline_count( $new_headline_slug );
			if ( 0 === $new_headline_count ) {
				$new_headline_count = 1;
			}
			self::$headlines[ $new_headline_slug ] = $new_headline_count;
			$headline_slug                         = $new_headline_slug;
		}
		return $headline_slug;
	}

	/**
	 * Get the anchor for a headline
	 *
	 * @param string $headline The headline.
	 * @param bool   $add_headline Whether to add the headline to the array.
	 * @return string The anchor for the headline
	 */
	public static function get_headline_anchor( $headline, $add_headline = false ) {
		if ( empty( $headline ) ) {
			return '';
		}

		$headline_slug = simpletoc_sanitize_string( $headline );

		if ( $add_headline ) {
			$headline_slug = self::add_headline( $headline_slug );
		}
		return $headline_slug;
	}

	/**
	 * Get the count of a headline slug.
	 *
	 * @param string $headline_slug The slug of the headline.
	 * @return mixed The count of the headline slug.
	 */
	private static function get_headline_count( $headline_slug = '' ) {
		return self::$headlines[ $headline_slug ] ?? 0;
	}
}
