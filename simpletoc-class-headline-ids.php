<?php
/**
 * Class to manage headline IDs.
 *
 * Ensures a unique anchor for each headline.
 *
 * @package simpletoc
 */

namespace MToensing\SimpleTOC;

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
	private $headlines = array();

	/**
	 * Add a headline to the array
	 *
	 * @param string $headline_slug The slug of the headline.
	 */
	private function add_headline( $headline_slug ) {
		if ( empty( $headline_slug ) ) {
			return;
		}

		if ( ! isset( $this->headlines[ $headline_slug ] ) ) {
			$this->headlines[ $headline_slug ] = 1;
		} else {
			$this->headlines[ $headline_slug ] = $this->get_headline_count( $headline_slug ) + 1;
		}
		if ( $this->headlines[ $headline_slug ] > 1 ) {
			$new_headline_slug = $headline_slug . '-' . $this->headlines[ $headline_slug ];
			if ( isset( $this->headlines[ $new_headline_slug ] ) ) {
				$new_headline_slug = $this->add_headline( $new_headline_slug );
			}
			$new_headline_count = $this->get_headline_count( $new_headline_slug );
			if ( 0 === $new_headline_count ) {
				$new_headline_count = 1;
			}
			$this->headlines[ $new_headline_slug ] = $new_headline_count;
			return $new_headline_slug;
		}
		return $headline_slug;
	}

	/**
	 * Get the anchor for a headline
	 *
	 * @param string $headline The headline.
	 * @return string The anchor for the headline
	 */
	public function get_headline_anchor( $headline ) {
		if ( empty( $headline ) ) {
			return '';
		}

		$headline_slug = simpletoc_sanitize_string( $headline );

		$headline_slug = $this->add_headline( $headline_slug );
		return $headline_slug;
	}

	/**
	 * Get the count of a headline slug.
	 *
	 * @param string $headline_slug The slug of the headline.
	 * @return mixed The count of the headline slug.
	 */
	private function get_headline_count( $headline_slug = '' ) {
		return $this->headlines[ $headline_slug ] ?? 0;
	}
}
