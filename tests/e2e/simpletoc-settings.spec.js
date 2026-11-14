const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const settingsPath = '/wp-admin/options-general.php?page=simpletoc';
const optionNames = [
	'accordion',
	'smooth',
	'absolute_urls',
	'autoupdate',
	'box_style',
	'scroll_spy',
];
const content = `<!-- wp:simpletoc/toc /-->
<!-- wp:heading --><h2>First section</h2><!-- /wp:heading -->
<!-- wp:heading {"level":3} --><h3>Nested section</h3><!-- /wp:heading -->
<!-- wp:heading {"level":4} --><h4>Deep section</h4><!-- /wp:heading -->`;

async function saveSettings( page, enabled = [] ) {
	await page.goto( settingsPath );
	for ( const name of optionNames ) {
		await page
			.locator( `#simpletoc_${ name }_enabled` )
			.setChecked( enabled.includes( name ) );
	}
	await page.getByRole( 'button', { name: 'Save Changes' } ).click();
	await page.reload();
	for ( const name of optionNames ) {
		await expect(
			page.locator( `#simpletoc_${ name }_enabled` )
		).toBeChecked( {
			checked: enabled.includes( name ),
		} );
	}
}

async function selectToc( page, editor ) {
	await page.evaluate( () => {
		const block = wp.data.select( 'core/block-editor' ).getBlocks()[ 0 ];
		wp.data.dispatch( 'core/block-editor' ).selectBlock( block.clientId );
	} );
	await editor.openDocumentSettingsSidebar();
	const advanced = page.getByRole( 'button', {
		name: 'Advanced Features',
		exact: true,
	} );
	if ( ( await advanced.getAttribute( 'aria-expanded' ) ) !== 'true' ) {
		await advanced.click();
	}
}

async function assertFrontend( page, name, enabled ) {
	const toc = page.locator( '.wp-block-simpletoc-toc' );
	await expect( toc ).toBeVisible();
	if ( name === 'accordion' ) {
		await expect( toc.locator( '.simpletoc-collapsible' ) ).toHaveCount(
			enabled ? 1 : 0
		);
		if ( enabled ) {
			const button = toc.getByRole( 'button' );
			await expect( button ).toHaveAttribute( 'aria-expanded', 'false' );
			await button.click();
			await expect( button ).toHaveAttribute( 'aria-expanded', 'true' );
			await expect( toc.locator( '.simpletoc-list' ) ).toBeVisible();
		}
	} else if ( name === 'smooth' ) {
		await expect( page.locator( 'html' ) ).toHaveCSS(
			'scroll-behavior',
			enabled ? 'smooth' : 'auto'
		);
	} else if ( name === 'absolute_urls' ) {
		await expect( toc.locator( 'a' ).first() ).toHaveAttribute(
			'href',
			enabled ? /^http.*#first-section$/ : '#first-section'
		);
	} else if ( name === 'box_style' ) {
		await expect( toc ).toHaveClass(
			enabled
				? /has-simpletoc-box-style/
				: /^(?!.*has-simpletoc-box-style)/
		);
		if ( enabled ) {
			await expect( toc ).toHaveCSS(
				'background-color',
				'rgb(235, 235, 235)'
			);
		}
	} else if ( name === 'scroll_spy' ) {
		await expect( toc ).toHaveClass(
			enabled
				? /has-simpletoc-scroll-spy/
				: /^(?!.*has-simpletoc-scroll-spy)/
		);
	}
}

test.describe( 'SimpleTOC settings audit', () => {
	test.beforeEach( async ( { requestUtils, page } ) => {
		await requestUtils.activatePlugin(
			'simpletoc-table-of-contents-block'
		);
		await saveSettings( page );
	} );
	test.afterEach( async ( { page, requestUtils } ) => {
		await saveSettings( page );
		await requestUtils.deleteAllPosts();
	} );

	for ( const name of optionNames ) {
		test( `global ${ name }: save, reload, effect, disable`, async ( {
			page,
			admin,
			editor,
		} ) => {
			await admin.createNewPost( { title: `Settings audit ${ name }` } );
			await editor.setContent( content );
			const postId = await editor.publishPost();
			for ( const enabled of [ false, true, false ] ) {
				await saveSettings( page, enabled ? [ name ] : [] );
				await admin.editPost( postId );
				await selectToc( page, editor );
				const labels = {
					accordion: 'Hide SimpleTOC',
					smooth: 'Smooth scrolling support',
					absolute_urls: 'Use absolute urls',
					autoupdate: 'Automatic refresh',
					scroll_spy: 'Highlight current section',
				};
				if ( labels[ name ] ) {
					const toggle = page.getByRole( 'checkbox', {
						name: labels[ name ],
						exact: true,
					} );
					await expect( toggle ).toBeChecked( {
						checked: name === 'autoupdate' ? ! enabled : enabled,
					} );
					if ( enabled ) {
						await expect( toggle ).toBeDisabled();
					} else {
						await expect( toggle ).toBeEnabled();
					}
				}
				if ( name === 'accordion' && enabled ) {
					const radio = page.getByRole( 'radio', {
						name: 'Hide in accordion menu. Adds minimal JS and CSS.',
						exact: true,
					} );
					await expect( radio ).toBeChecked();
					await expect( radio ).toBeDisabled();
					await expect(
						page.locator( '.simpletoc-hide-options' )
					).toHaveCSS( 'opacity', '0.5' );
				}
				if ( name === 'box_style' ) {
					const blockType = await page.evaluate( () => {
						const { styles, supports } =
							wp.blocks.getBlockType( 'simpletoc/toc' );
						return { styles, color: supports.color };
					} );
					expect( blockType.color.background ).toBe( ! enabled );
					expect( blockType.color.text ).toBe( true );
					expect( blockType.color.link ).toBe( true );
					expect( blockType.styles ).toHaveLength( enabled ? 0 : 2 );
					await expect(
						page.locator( '.components-notice__content' ).filter( {
							hasText:
								'The Box style and its default background are enabled globally in SimpleTOC settings.',
						} )
					).toHaveCount( enabled ? 1 : 0 );
				}
				if ( name === 'autoupdate' ) {
					await admin.editPost( postId );
					await selectToc( page, editor );
					await expect(
						page.getByRole( 'button', {
							name: 'Update table of contents',
							exact: true,
						} )
					).toHaveCount( enabled ? 1 : 0 );
					const before = await page.evaluate(
						() =>
							wp.data.select( 'core/editor' ).getCurrentPost()
								.title
					);
					const renders = [];
					const onRequest = ( request ) => {
						if (
							decodeURIComponent( request.url() ).includes(
								'block-renderer/simpletoc'
							)
						) {
							renders.push( request.url() );
						}
					};
					await expect(
						page.locator( '.simpletoc-list' )
					).toBeVisible();
					page.on( 'request', onRequest );
					await page.evaluate(
						( title ) =>
							wp.data
								.dispatch( 'core/editor' )
								.editPost( { title: title + ' saved' } ),
						before
					);
					await page.evaluate( () =>
						wp.data.dispatch( 'core/editor' ).savePost()
					);
					if ( enabled ) {
						await page.waitForTimeout( 1500 );
						expect( renders ).toHaveLength( 0 );
						await page
							.getByRole( 'button', {
								name: 'Update table of contents',
								exact: true,
							} )
							.click();
						await expect
							.poll( () => renders.length )
							.toBeGreaterThan( 0 );
					} else {
						await expect
							.poll( () => renders.length )
							.toBeGreaterThan( 0 );
					}
					page.off( 'request', onRequest );
				} else {
					await page.goto( `/?p=${ postId }` );
					await assertFrontend( page, name, enabled );
				}
			}
		} );
	}

	test( 'block controls persist and render independently of disabled global settings', async ( {
		admin,
		page,
		editor,
	} ) => {
		await admin.createNewPost( { title: 'Block settings audit' } );
		await editor.setContent( content );
		await selectToc( page, editor );
		await page
			.getByRole( 'textbox', { name: 'Heading Text', exact: true } )
			.fill( 'Custom contents' );
		await page
			.getByRole( 'combobox', { name: 'Minimum level', exact: true } )
			.selectOption( '3' );
		await page
			.getByRole( 'combobox', { name: 'Maximum level', exact: true } )
			.selectOption( '3' );
		for ( const name of [
			'Highlight current section',
			'Smooth scrolling support',
			'Use absolute urls',
		] ) {
			await page.getByRole( 'checkbox', { name, exact: true } ).check();
		}
		await page
			.getByRole( 'checkbox', { name: 'Automatic refresh', exact: true } )
			.uncheck();
		await page
			.getByRole( 'button', {
				name: 'Convert to ordered list',
				exact: true,
			} )
			.click();
		await page
			.getByRole( 'button', { name: 'Indent list', exact: true } )
			.click();
		const postId = await editor.publishPost();
		await admin.editPost( postId );
		await selectToc( page, editor );
		await expect(
			page.getByRole( 'textbox', { name: 'Heading Text', exact: true } )
		).toHaveValue( 'Custom contents' );
		await expect(
			page.getByRole( 'checkbox', {
				name: 'Smooth scrolling support',
				exact: true,
			} )
		).toBeChecked();
		await expect(
			page.getByRole( 'checkbox', {
				name: 'Automatic refresh',
				exact: true,
			} )
		).not.toBeChecked();
		await page.goto( `/?p=${ postId }` );
		const toc = page.locator( '.wp-block-simpletoc-toc' );
		await expect( toc.locator( '.simpletoc-title' ) ).toHaveText(
			'Custom contents'
		);
		await expect( toc.locator( 'ol.simpletoc-list' ) ).toHaveCSS(
			'padding-left',
			'0px'
		);
		await expect( toc.locator( 'a' ) ).toHaveCount( 1 );
		await expect( toc.locator( 'a' ) ).toHaveText( 'Nested section' );
		await expect( toc.locator( 'a' ) ).toHaveAttribute(
			'href',
			/^http.*#nested-section$/
		);
		await expect( toc ).toHaveClass( /has-simpletoc-scroll-spy/ );
		await expect( page.locator( 'html' ) ).toHaveCSS(
			'scroll-behavior',
			'smooth'
		);
	} );
	for ( const mode of [ 'details', 'accordion', 'no_title' ] ) {
		test( `block ${ mode }: control, persistence and frontend`, async ( {
			admin,
			page,
			editor,
		} ) => {
			await admin.createNewPost( { title: `Block mode ${ mode }` } );
			await editor.setContent( content );
			await selectToc( page, editor );
			if ( mode === 'no_title' ) {
				await page
					.getByRole( 'checkbox', {
						name: 'Remove heading',
						exact: true,
					} )
					.check();
			} else {
				await page
					.getByRole( 'checkbox', {
						name: 'Hide SimpleTOC',
						exact: true,
					} )
					.check();
				if ( mode === 'accordion' ) {
					await page
						.getByRole( 'radio', {
							name: 'Hide in accordion menu. Adds minimal JS and CSS.',
							exact: true,
						} )
						.check();
				}
			}
			const postId = await editor.publishPost();
			await admin.editPost( postId );
			await selectToc( page, editor );
			await expect(
				page.getByRole( 'checkbox', {
					name:
						mode === 'no_title'
							? 'Remove heading'
							: 'Hide SimpleTOC',
					exact: true,
				} )
			).toBeChecked();
			await page.goto( `/?p=${ postId }` );
			const toc = page.locator( '.wp-block-simpletoc-toc' );
			if ( mode === 'details' ) {
				await expect( toc.locator( 'details' ) ).toHaveCount( 1 );
				await expect(
					toc.locator( '.simpletoc-list' )
				).not.toBeVisible();
				await toc.locator( 'summary' ).click();
				await expect( toc.locator( '.simpletoc-list' ) ).toBeVisible();
			} else if ( mode === 'accordion' ) {
				await assertFrontend( page, 'accordion', true );
			} else {
				await expect( toc.locator( '.simpletoc-title' ) ).toHaveCount(
					0
				);
				await expect( toc.locator( '.simpletoc-list' ) ).toBeVisible();
			}
		} );
	}
	test( 'global accordion overrides saved details and restores it when disabled', async ( {
		admin,
		page,
		editor,
	} ) => {
		await admin.createNewPost( { title: 'Accordion precedence' } );
		await editor.setContent(
			content.replace(
				'wp:simpletoc/toc /',
				'wp:simpletoc/toc {"hidden":true} /'
			)
		);
		const postId = await editor.publishPost();
		await saveSettings( page, [ 'accordion' ] );
		await admin.editPost( postId );
		await selectToc( page, editor );
		await expect(
			page.getByRole( 'radio', {
				name: 'Hide in accordion menu. Adds minimal JS and CSS.',
				exact: true,
			} )
		).toBeChecked();
		const attributes = await page.evaluate(
			() =>
				wp.data.select( 'core/block-editor' ).getBlocks()[ 0 ]
					.attributes
		);
		expect( attributes.hidden ).toBe( true );
		expect( attributes.accordion ).toBe( false );
		await expect(
			page.locator( '.simpletoc-content details' )
		).toHaveCount( 0 );
		await page.goto( `/?p=${ postId }` );
		await expect(
			page.locator( '.wp-block-simpletoc-toc details' )
		).toHaveCount( 0 );
		await assertFrontend( page, 'accordion', true );
		await saveSettings( page );
		await admin.editPost( postId );
		await selectToc( page, editor );
		const radio = page.getByRole( 'radio', {
			name: 'Hide with a clickable dropdown (using <details> tag).',
			exact: true,
		} );
		await expect( radio ).toBeChecked();
		await expect( radio ).toBeEnabled();
		await page.goto( `/?p=${ postId }` );
		await expect(
			page.locator( '.wp-block-simpletoc-toc details' )
		).toHaveCount( 1 );
		await expect( page.locator( '.simpletoc-collapsible' ) ).toHaveCount(
			0
		);
	} );
} );
