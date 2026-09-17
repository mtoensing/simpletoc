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

const typographyPostContent = `<!-- wp:simpletoc/toc {"fontSize":"large","style":{"typography":{"lineHeight":"2"}}} /-->

<!-- wp:heading -->
<h2 class="wp-block-heading">Styled TOC Heading</h2>
<!-- /wp:heading -->`;

const boxedStylePostContent = `<!-- wp:simpletoc/toc {"className":"is-style-boxed","backgroundColor":"contrast","textColor":"base","style":{"elements":{"link":{"color":{"text":"#abcdef"}}},"spacing":{"margin":{"top":"10px","bottom":"20px"},"padding":{"top":"30px","right":"31px","bottom":"32px","left":"33px"}}}} /-->

<!-- wp:heading -->
<h2 class="wp-block-heading">Boxed TOC Heading</h2>
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

	test( 'applies native typography settings on the frontend', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			title: 'SimpleTOC typography smoke test',
		} );

		await page.waitForFunction( () =>
			wp.blocks.getBlockType( 'simpletoc/toc' )
		);
		await editor.setContent( typographyPostContent );

		const postId = await editor.publishPost();
		expect( postId ).toBeTruthy();

		await page.goto( `/?p=${ postId }` );

		const toc = page.locator( '.simpletoc.has-simpletoc-typography' );
		await expect( toc ).toHaveClass( /has-large-font-size/ );
		await expect( toc ).toHaveAttribute( 'style', /line-height:\s*2/ );

		const typography = await toc.evaluate( ( element ) => {
			const title = element.querySelector( '.simpletoc-title' );
			const wrapperStyle = window.getComputedStyle( element );
			const titleStyle = window.getComputedStyle( title );

			return {
				fontSize: wrapperStyle.fontSize,
				lineHeight: wrapperStyle.lineHeight,
				titleFontSize: titleStyle.fontSize,
				titleLineHeight: titleStyle.lineHeight,
			};
		} );

		expect( typography.titleFontSize ).toBe( typography.fontSize );
		expect( typography.titleLineHeight ).toBe( typography.lineHeight );
	} );

	test( 'applies the Box style and native design supports', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			title: 'SimpleTOC native styles smoke test',
		} );

		await page.waitForFunction( () =>
			wp.blocks.getBlockType( 'simpletoc/toc' )
		);

		const blockType = await page.evaluate( () => {
			const { styles, supports } =
				wp.blocks.getBlockType( 'simpletoc/toc' );

			return { styles, supports };
		} );

		expect( blockType.styles ).toEqual(
			expect.arrayContaining( [
				expect.objectContaining( { name: 'boxed', label: 'Box' } ),
			] )
		);
		expect( blockType.supports ).toMatchObject( {
			color: {
				background: true,
				link: true,
				text: true,
			},
			spacing: {
				margin: [ 'top', 'bottom' ],
				padding: true,
			},
		} );

		await editor.setContent( boxedStylePostContent );

		const postId = await editor.publishPost();
		expect( postId ).toBeTruthy();

		await page.goto( `/?p=${ postId }` );

		const toc = page.locator( '.simpletoc.is-style-boxed' );
		await expect( toc ).toHaveClass( /has-contrast-background-color/ );
		await expect( toc ).toHaveClass( /has-base-color/ );
		await expect( toc ).toHaveAttribute( 'style', /margin-top:\s*10px/ );
		await expect( toc ).toHaveAttribute( 'style', /margin-bottom:\s*20px/ );
		await expect( toc ).toHaveAttribute( 'style', /padding-top:\s*30px/ );
		await expect( toc ).toHaveAttribute( 'style', /padding-right:\s*31px/ );
		await expect( toc ).toHaveAttribute(
			'style',
			/padding-bottom:\s*32px/
		);
		await expect( toc ).toHaveAttribute( 'style', /padding-left:\s*33px/ );

		const linkColor = await toc
			.locator( '.simpletoc-list a' )
			.first()
			.evaluate(
				( element ) => window.getComputedStyle( element ).color
			);
		expect( linkColor ).toBe( 'rgb(171, 205, 239)' );
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

		const postId = await editor.publishPost();
		expect( postId ).toBeTruthy();

		await page.goto( `/?p=${ postId }` );
		const tocWrapper = page.locator( '.wp-block-simpletoc-toc.simpletoc' );
		await expect( tocWrapper ).toHaveAttribute( 'role', 'navigation' );
		await expect( tocWrapper ).toHaveAttribute(
			'aria-label',
			'Table of Contents'
		);
		await expect( tocWrapper.locator( '.simpletoc-list' ) ).toBeVisible();
	} );
	test( 'tracks scrolling with CSS and JavaScript disabled', async ( {
		admin,
		editor,
		page,
		browser,
	} ) => {
		await admin.createNewPost( { title: 'SimpleTOC CSS scroll spy' } );
		const sections = [ 'First', 'Nested', 'Last' ]
			.map( ( title, index ) => {
				const level = index === 1 ? 3 : 2;
				return `<!-- wp:heading {"level":${ level }} -->
<h${ level } class="wp-block-heading">${ title }</h${ level }><!-- /wp:heading -->
<!-- wp:spacer {"height":"1200px"} --><div style="height:1200px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->`;
			} )
			.join( '\n' );
		await editor.setContent( '<!-- wp:simpletoc/toc /-->\n' + sections );
		await editor.canvas.locator( '[data-type="simpletoc/toc"]' ).click();
		await editor.openDocumentSettingsSidebar();
		await page
			.getByRole( 'button', { name: 'Advanced Features', exact: true } )
			.click();
		await page
			.getByRole( 'checkbox', {
				name: 'Highlight current section',
				exact: true,
			} )
			.check();
		const postId = await editor.publishPost();
		const context = await browser.newContext( {
			javaScriptEnabled: false,
		} );
		try {
			const frontend = await context.newPage();
			await frontend.goto(
				new URL( `/?p=${ postId }`, page.url() ).href
			);
			const toc = frontend.locator( '.wp-block-simpletoc-toc' );
			await expect( toc ).toHaveClass( /has-simpletoc-scroll-spy/ );
			await expect( toc ).toHaveCSS( 'scroll-target-group', 'auto' );
			for ( const title of [ 'First', 'Nested', 'Last', 'First' ] ) {
				await frontend
					.getByRole( 'heading', { name: title, exact: true } )
					.evaluate( ( heading ) =>
						window.scrollTo(
							0,
							heading.getBoundingClientRect().top +
								window.scrollY +
								10
						)
					);
				await expect( toc.locator( 'a:target-current' ) ).toHaveText(
					title
				);
				await expect( toc.locator( 'a:target-current' ) ).toHaveCSS(
					'text-decoration-line',
					'underline'
				);
			}
		} finally {
			await context.close();
		}
	} );
	test( 'saves the global scroll spy setting and enforces it in the editor', async ( {
		admin,
		editor,
		page,
	} ) => {
		const settingsPath = '/wp-admin/options-general.php?page=simpletoc';
		await page.goto( settingsPath );
		await page.locator( '#simpletoc_scroll_spy_enabled' ).check();
		await page.getByRole( 'button', { name: 'Save Changes' } ).click();
		await expect(
			page.locator( '#simpletoc_scroll_spy_enabled' )
		).toBeChecked();
		try {
			await admin.createNewPost( {
				title: 'Globally enabled scroll spy',
			} );
			await editor.setContent( postContent );
			await editor.canvas
				.locator( '[data-type="simpletoc/toc"]' )
				.click();
			await editor.openDocumentSettingsSidebar();
			await page
				.getByRole( 'button', {
					name: 'Advanced Features',
					exact: true,
				} )
				.click();
			const toggle = page.getByRole( 'checkbox', {
				name: 'Highlight current section',
				exact: true,
			} );
			await expect( toggle ).toBeChecked();
			await expect( toggle ).toBeDisabled();
			const postId = await editor.publishPost();
			await page.goto( `/?p=${ postId }` );
			await expect(
				page.locator( '.wp-block-simpletoc-toc' )
			).toHaveClass( /has-simpletoc-scroll-spy/ );
		} finally {
			await page.goto( settingsPath );
			await page.locator( '#simpletoc_scroll_spy_enabled' ).uncheck();
			await page.getByRole( 'button', { name: 'Save Changes' } ).click();
			await expect(
				page.locator( '#simpletoc_scroll_spy_enabled' )
			).not.toBeChecked();
		}
	} );
} );
