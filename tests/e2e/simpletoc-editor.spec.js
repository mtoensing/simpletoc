const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const postContent = `<!-- wp:simpletoc/toc {"no_title":true} /-->

<!-- wp:heading -->
<h2 class="wp-block-heading"><sup>The</sup> <em>Modern </em><mark style="background-color:#fcd34d" class="has-inline-color">Font Stacks</mark></h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Nested Details</h3>
<!-- /wp:heading -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Second Section</h2>
<!-- /wp:heading -->`;

const paginatedPostContent = `<!-- wp:simpletoc/toc {"no_title":true} /-->

<!-- wp:heading -->
<h2 class="wp-block-heading">First Page</h2>
<!-- /wp:heading -->

<!-- wp:nextpage -->
<!--nextpage-->
<!-- /wp:nextpage -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Second Page</h2>
<!-- /wp:heading -->`;

const legacySimpletocPostContent = `<!-- wp:simpletoc/toc {"no_title":false,"title_level":2,"title_text":"Table of Contents","use_ol":false,"remove_indent":false,"add_smooth":false,"use_absolute_urls":false,"min_level":1,"max_level":6,"accordion":false,"hidden":false,"wrapper":false,"autoupdate":true} /-->

<!-- wp:heading -->
<h2 class="wp-block-heading">Legacy Heading</h2>
<!-- /wp:heading -->`;

test.describe( 'SimpleTOC editor rendering', () => {
	test.beforeEach( async ( { requestUtils } ) => {
		await requestUtils.activatePlugin(
			'simpletoc-table-of-contents-block'
		);
		await requestUtils.deleteAllPosts();
	} );

	test.afterEach( async ( { requestUtils } ) => {
		await requestUtils.deleteAllPosts();
	} );

	test( 'publishes a TOC for rich-text and nested headings', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			title: 'SimpleTOC rich heading smoke test',
		} );

		await page.waitForFunction( () =>
			wp.blocks.getBlockType( 'simpletoc/toc' )
		);
		await editor.setContent( postContent );

		await expect
			.poll( async () =>
				page.evaluate( () =>
					wp.data
						.select( 'core/block-editor' )
						.getBlocks()
						.map( ( block ) => block.name )
				)
			)
			.toEqual( [
				'simpletoc/toc',
				'core/heading',
				'core/heading',
				'core/heading',
			] );

		const postId = await editor.publishPost();
		expect( postId ).toBeTruthy();

		await page.goto( `/?p=${ postId }` );

		const toc = page.locator( '.simpletoc-list' );
		await expect( toc ).toBeVisible();

		await expect(
			toc.getByRole( 'link', { name: 'The Modern Font Stacks' } )
		).toHaveAttribute( 'href', '#the-modern-font-stacks' );
		await expect(
			toc.locator( 'ul' ).getByRole( 'link', { name: 'Nested Details' } )
		).toHaveAttribute( 'href', '#nested-details' );
		await expect(
			toc.getByRole( 'link', { name: 'Second Section' } )
		).toHaveAttribute( 'href', '#second-section' );

		await expect(
			page.locator( '#the-modern-font-stacks mark' )
		).toHaveText( 'Font Stacks' );
		await expect( page.locator( '#nested-details' ) ).toHaveText(
			'Nested Details'
		);
	} );

	test( 'keeps paginated TOC links relative on the frontend', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			title: 'SimpleTOC paginated link smoke test',
		} );

		await page.waitForFunction( () =>
			wp.blocks.getBlockType( 'simpletoc/toc' )
		);
		await editor.setContent( paginatedPostContent );

		const postId = await editor.publishPost();
		expect( postId ).toBeTruthy();

		await page.goto( `/?p=${ postId }` );

		const toc = page.locator( '.simpletoc-list' );
		await expect( toc ).toBeVisible();
		await expect(
			toc.getByRole( 'link', { name: 'Second Page' } )
		).toHaveAttribute( 'href', '2/#second-page' );
		await expect( toc.locator( 'a[href^="http://0.0.0.2/"]' ) ).toHaveCount(
			0
		);
	} );

	test( 'loads legacy serialized SimpleTOC blocks as valid blocks', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			title: 'SimpleTOC legacy block compatibility smoke test',
		} );

		await page.waitForFunction( () =>
			wp.blocks.getBlockType( 'simpletoc/toc' )
		);
		await editor.setContent( legacySimpletocPostContent );

		await expect
			.poll( async () =>
				page.evaluate( () =>
					wp.data
						.select( 'core/block-editor' )
						.getBlocks()
						.map( ( block ) => ( {
							name: block.name,
							isValid: block.isValid,
							attributes: block.attributes,
						} ) )
				)
			)
			.toMatchObject( [
				{
					name: 'simpletoc/toc',
					isValid: true,
					attributes: {
						title_text: 'Table of Contents',
						min_level: 1,
						max_level: 6,
					},
				},
				{
					name: 'core/heading',
					isValid: true,
				},
			] );

		await expect( page.locator( '.block-editor-warning' ) ).toHaveCount(
			0
		);

		await expect
			.poll( async () =>
				page.evaluate( () =>
					wp.data.select( 'core/editor' ).getEditedPostContent()
				)
			)
			.toContain( '<!-- wp:simpletoc/toc' );
	} );
} );
