<?php
/**
 * Tests for SimpleTOC rendering helpers.
 *
 * @package simpletoc
 */

use MToensing\SimpleTOC\SimpleTOC_Headline_Ids;

/**
 * Covers the core frontend markup helpers.
 */
class SimpleTOC_Test extends WP_UnitTestCase {
	/**
	 * Returns default block attributes for TOC rendering tests.
	 *
	 * @param array $overrides Attribute overrides.
	 * @return array
	 */
	private function get_default_attributes( $overrides = array() ) {
		return array_merge(
			array(
				'remove_indent'      => false,
				'use_ol'             => false,
				'use_absolute_urls'  => false,
				'min_level'          => 1,
				'max_level'          => 6,
				'hidden'             => false,
				'accordion'          => false,
				'no_title'           => true,
				'title_text'         => '',
				'title_level'        => 2,
				'add_smooth'         => false,
			),
			$overrides
		);
	}

	/**
	 * @covers \MToensing\SimpleTOC\add_anchor_attribute
	 */
	public function test_add_anchor_attribute_handles_highlighted_heading_markup() {
		$html = '<h2 class="wp-block-heading"><sup>the</sup> <em>Modern </em><mark style="background-color:var(--theme-palette-color-10, #fcd34d)" class="has-inline-color has-palette-color-11-color">Font Stacks</mark></h2>';

		$result = MToensing\SimpleTOC\add_anchor_attribute( $html, new SimpleTOC_Headline_Ids() );

		$this->assertStringContainsString( 'id="the-modern-font-stacks"', $result );
		$this->assertStringContainsString( '<mark style="background-color:var(--theme-palette-color-10, #fcd34d)" class="has-inline-color has-palette-color-11-color">Font Stacks</mark>', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\add_anchor_attribute
	 */
	public function test_add_anchor_attribute_preserves_existing_id() {
		$html = '<h2 id="custom-anchor">Existing Anchor</h2>';

		$result = MToensing\SimpleTOC\add_anchor_attribute( $html, new SimpleTOC_Headline_Ids() );

		$this->assertSame( $html, $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\add_anchor_attribute
	 * @covers \MToensing\SimpleTOC\SimpleTOC_Headline_Ids
	 */
	public function test_add_anchor_attribute_generates_unique_duplicate_ids() {
		$headline_ids = new SimpleTOC_Headline_Ids();

		$first  = MToensing\SimpleTOC\add_anchor_attribute( '<h2>Duplicate</h2>', $headline_ids );
		$second = MToensing\SimpleTOC\add_anchor_attribute( '<h2>Duplicate</h2>', $headline_ids );

		$this->assertStringContainsString( 'id="duplicate"', $first );
		$this->assertStringContainsString( 'id="duplicate-2"', $second );
	}

	/**
	 * @covers \MToensing\SimpleTOC\add_anchor_attribute
	 * @covers \MToensing\SimpleTOC\simpletoc_sanitize_string
	 */
	public function test_add_anchor_attribute_handles_multibyte_and_emoji_headings() {
		$headline_ids = new SimpleTOC_Headline_Ids();
		$heading      = '日本語の見出し 😀';
		$expected_id  = MToensing\SimpleTOC\simpletoc_sanitize_string( $heading );

		$result = MToensing\SimpleTOC\add_anchor_attribute( '<h2>' . $heading . '</h2>', $headline_ids );

		$this->assertStringContainsString( 'id="' . $expected_id . '"', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\get_page_number_from_headline
	 */
	public function test_get_page_number_from_headline_reads_heading_data_page() {
		$heading = '<h2 data-page="2"><mark>Second Page</mark></h2>';

		$result = MToensing\SimpleTOC\get_page_number_from_headline( $heading );

		$this->assertSame( '2/', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\get_page_number_from_headline
	 */
	public function test_get_page_number_from_headline_ignores_first_or_missing_page() {
		$this->assertSame( '', MToensing\SimpleTOC\get_page_number_from_headline( '<h2 data-page="1">First Page</h2>' ) );
		$this->assertSame( '', MToensing\SimpleTOC\get_page_number_from_headline( '<h2>No Page</h2>' ) );
	}

	/**
	 * @covers \MToensing\SimpleTOC\extract_id
	 */
	public function test_extract_id_reads_heading_id_with_html_tag_processor() {
		$result = MToensing\SimpleTOC\extract_id( '<h2 class="wp-block-heading" id=\'custom-anchor\'>Heading</h2>' );

		$this->assertSame( 'custom-anchor', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\generate_toc
	 * @covers \MToensing\SimpleTOC\render_toc_list_items
	 */
	public function test_generate_toc_renders_nested_heading_list() {
		$headings = array(
			'<h2 id="first">First</h2>',
			'<h3 id="child">Child</h3>',
			'<h2 id="second">Second</h2>',
		);

		$result = MToensing\SimpleTOC\generate_toc( $headings, $this->get_default_attributes() );

		$this->assertStringContainsString( '<ul class="simpletoc-list">', $result );
		$this->assertStringContainsString( '<a href="#first">First</a>', $result );
		$this->assertStringContainsString( '<ul>', $result );
		$this->assertStringContainsString( '<a href="#child">Child</a>', $result );
		$this->assertStringContainsString( '<a href="#second">Second</a>', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\generate_toc
	 * @covers \MToensing\SimpleTOC\should_exclude_headline
	 */
	public function test_generate_toc_excludes_hidden_headings() {
		$headings = array(
			'<h2 id="visible">Visible</h2>',
			'<h2 id="hidden" class="simpletoc-hidden">Hidden</h2>',
		);

		$result = MToensing\SimpleTOC\generate_toc( $headings, $this->get_default_attributes() );

		$this->assertStringContainsString( '<a href="#visible">Visible</a>', $result );
		$this->assertStringNotContainsString( 'Hidden', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\generate_toc
	 * @covers \MToensing\SimpleTOC\render_toc_list_items
	 */
	public function test_generate_toc_escapes_heading_link_text() {
		$headings = array(
			'<h2 id="quoted">AT&T "Quotes"</h2>',
		);

		$result = MToensing\SimpleTOC\generate_toc( $headings, $this->get_default_attributes() );

		$this->assertStringContainsString( '<a href="#quoted">AT&amp;T &quot;Quotes&quot;</a>', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\generate_toc
	 * @covers \MToensing\SimpleTOC\render_toc_list_items
	 */
	public function test_generate_toc_keeps_multibyte_and_emoji_link_text() {
		$headings = array(
			'<h2 id="special-chars">日本語の見出し 😀 & More</h2>',
		);

		$result = MToensing\SimpleTOC\generate_toc( $headings, $this->get_default_attributes() );

		$this->assertStringContainsString( '<a href="#special-chars">日本語の見出し 😀 &amp; More</a>', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\generate_toc
	 * @covers \MToensing\SimpleTOC\render_toc_list_items
	 */
	public function test_generate_toc_keeps_paginated_relative_links_relative() {
		$headings = array(
			'<h2 data-page="2" id="second-page">Second Page</h2>',
		);

		$result = MToensing\SimpleTOC\generate_toc( $headings, $this->get_default_attributes() );

		$this->assertStringContainsString( '<a href="2/#second-page">Second Page</a>', $result );
		$this->assertStringNotContainsString( 'http://0.0.0.2/', $result );
	}

	/**
	 * @covers \MToensing\SimpleTOC\render_toc_list_items
	 */
	public function test_render_toc_list_items_keeps_paginated_absolute_links_absolute() {
		$toc_headings = array(
			array(
				'headline' => '<h2 data-page="2" id="second-page">Second Page</h2>',
				'depth'    => 2,
				'title'    => 'Second Page',
				'link'     => 'second-page',
			),
		);

		$result = MToensing\SimpleTOC\render_toc_list_items( $toc_headings, 'ul', 'https://example.com/post/', 2 );

		$this->assertStringContainsString( '<a href="https://example.com/post/2/#second-page">Second Page</a>', $result );
	}
}
