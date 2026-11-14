import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	BlockControls,
	useBlockProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import ServerSideRender from '@wordpress/server-side-render';
import {
	BaseControl,
	SelectControl,
	ToolbarButton,
	ToggleControl,
	TextControl,
	RadioControl,
	Panel,
	PanelBody,
	PanelRow,
	ExternalLink,
	Notice,
} from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import HeadingLevelDropdown from './heading-level-dropdown';
import { useSelect } from '@wordpress/data';
import metadata from './block.json';
import './editor.scss';
import './../assets/accordion.css';

const SERVER_SIDE_RENDER_ATTRIBUTE_NAMES = [
	...Object.entries( metadata.attributes || {} )
		.filter( ( [ , settings ] ) => settings.role !== 'local' )
		.map( ( [ name ] ) => name ),
	'align',
	'backgroundColor',
	'className',
	'fontSize',
	'style',
	'textColor',
];

function getServerSideRenderAttributes( attributes ) {
	return SERVER_SIDE_RENDER_ATTRIBUTE_NAMES.reduce( ( filtered, name ) => {
		if ( attributes[ name ] !== undefined ) {
			filtered[ name ] = attributes[ name ];
		}

		return filtered;
	}, {} );
}

function SimpleTOCLoadingResponsePlaceholder( { children } ) {
	const [ showStatus, setShowStatus ] = useState( false );

	useEffect( () => {
		const timeout = setTimeout( () => {
			setShowStatus( true );
		}, 1000 );

		return () => clearTimeout( timeout );
	}, [] );

	if ( ! showStatus ) {
		return <>{ children }</>;
	}

	return (
		<div className="simpletoc-loading-preview" aria-busy="true">
			<div className="simpletoc-loading-preview__content">
				{ children }
			</div>
			<div
				className="simpletoc-loading-preview__status"
				role="status"
				aria-live="polite"
			>
				{ __( 'Updating table of contents…', 'simpletoc' ) }
			</div>
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { hidden, accordion } = attributes;
	const serverSideRenderAttributes =
		getServerSideRenderAttributes( attributes );

	const blockProps = useBlockProps();

	const { autoupdate } = attributes;
	const editorSettings = useSelect( ( select ) => {
		return select( blockEditorStore ).getSettings() || {};
	}, [] );
	const settingsUrl =
		editorSettings.simpletocSettingsUrl ||
		window.simpletocEditorSettings?.settingsUrl ||
		'';

	const autoupdateOption = ! (
		editorSettings.simpletocAutoRefreshDisabled ??
		window.simpletocEditorSettings?.autoRefreshDisabled ??
		false
	);

	const scrollSpyEnabled =
		editorSettings.simpletocScrollSpyEnabled ??
		window.simpletocEditorSettings?.scrollSpyEnabled ??
		false;

	const accordionEnabled =
		editorSettings.simpletocAccordionEnabled ??
		window.simpletocEditorSettings?.accordionEnabled ??
		false;
	const smoothEnabled =
		editorSettings.simpletocSmoothEnabled ??
		window.simpletocEditorSettings?.smoothEnabled ??
		false;
	const absoluteUrlsEnabled =
		editorSettings.simpletocAbsoluteUrlsEnabled ??
		window.simpletocEditorSettings?.absoluteUrlsEnabled ??
		false;
	const boxStyleEnabled =
		editorSettings.simpletocBoxStyleEnabled ??
		window.simpletocEditorSettings?.boxStyleEnabled ??
		false;
	const hideTOC = accordionEnabled || hidden || accordion;
	const enabledGloballyHelp = __(
		'Enabled globally in SimpleTOC settings.',
		'simpletoc'
	);

	const [ serverSideRenderRefresh, setServerSideRenderRefresh ] =
		useState( 0 );
	const wasSavingPost = useRef( false );
	const { didPostSaveSucceed, isSavingPost } = useSelect( ( select ) => {
		const {
			didPostSaveRequestSucceed,
			isAutosavingPost,
			isSavingPost: getIsSavingPost,
		} = select( editorStore );
		const savingPost =
			typeof getIsSavingPost === 'function' ? getIsSavingPost() : false;
		const autosavingPost =
			typeof isAutosavingPost === 'function' ? isAutosavingPost() : false;

		return {
			didPostSaveSucceed:
				typeof didPostSaveRequestSucceed === 'function'
					? didPostSaveRequestSucceed()
					: undefined,
			isSavingPost: savingPost && ! autosavingPost,
		};
	} );

	useEffect( () => {
		if (
			autoupdateOption &&
			autoupdate &&
			wasSavingPost.current &&
			! isSavingPost &&
			didPostSaveSucceed !== false
		) {
			setServerSideRenderRefresh( ( refresh ) => refresh + 1 );
		}

		wasSavingPost.current = isSavingPost;
	}, [ autoupdate, autoupdateOption, didPostSaveSucceed, isSavingPost ] );

	const advpanelicon = 'settings';

	const controls = (
		<BlockControls group="block">
			{ ! (
				attributes.no_title ||
				accordionEnabled ||
				attributes.accordion ||
				attributes.hidden
			) && (
				<HeadingLevelDropdown
					selectedLevel={ attributes.title_level }
					onChange={ ( level ) =>
						setAttributes( {
							title_level: Number( level ),
						} )
					}
				/>
			) }
			<ToolbarButton
				icon="editor-ul"
				title={ __( 'Convert to unordered list', 'simpletoc' ) }
				describedBy={ __( 'Convert to unordered list', 'simpletoc' ) }
				isActive={ attributes.use_ol === false }
				onClick={ () => {
					setAttributes( { use_ol: false } );
				} }
			/>
			<ToolbarButton
				icon="editor-ol"
				title={ __( 'Convert to ordered list', 'simpletoc' ) }
				describedBy={ __( 'Convert to ordered list', 'simpletoc' ) }
				isActive={ attributes.use_ol === true }
				onClick={ () => {
					setAttributes( { use_ol: true } );
				} }
			/>
			<ToolbarButton
				icon="editor-outdent"
				title={ __( 'Indent list', 'simpletoc' ) }
				describedBy={ __( 'Indent list', 'simpletoc' ) }
				isActive={ attributes.remove_indent === true }
				onClick={ () => {
					setAttributes( { remove_indent: true } );
				} }
			/>
			<ToolbarButton
				icon="editor-indent"
				title={ __( 'Outdent list', 'simpletoc' ) }
				describedBy={ __( 'Outdent list', 'simpletoc' ) }
				isActive={ attributes.remove_indent === false }
				onClick={ () => {
					setAttributes( { remove_indent: false } );
				} }
			/>
			{ ( ! attributes.autoupdate || ! autoupdateOption ) && (
				<ToolbarButton
					icon="update"
					label={ __( 'Update table of contents', 'simpletoc' ) }
					onClick={ () => setAttributes( { updated: Date.now() } ) }
				/>
			) }
		</BlockControls>
	);

	const controlssidebar = (
		<InspectorControls>
			<Panel>
				<PanelBody>
					{ ! attributes.no_title && (
						<PanelRow>
							<TextControl
								label={ __( 'Heading Text', 'simpletoc' ) }
								help={
									__(
										'Set the heading text of the block.',
										'simpletoc'
									) +
									' ' +
									__( 'Default value', 'simpletoc' ) +
									': ' +
									__( 'Table of Contents', 'simpletoc' )
								}
								value={ attributes.title_text }
								onChange={ ( value ) =>
									setAttributes( {
										title_text:
											value ||
											__(
												'Table of Contents',
												'simpletoc'
											),
									} )
								}
							/>
						</PanelRow>
					) }
					<PanelRow>
						<ToggleControl
							label={ __( 'Remove heading', 'simpletoc' ) }
							checked={ attributes.no_title }
							onChange={ () =>
								setAttributes( {
									no_title: ! attributes.no_title,
								} )
							}
						/>
					</PanelRow>
					<PanelRow>
						<SelectControl
							label={ __( 'Minimum level', 'simpletoc' ) }
							help={ __(
								'Minimum depth of the headings.',
								'simpletoc'
							) }
							value={ attributes.min_level }
							options={ [
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H6',
									value: '6',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H5',
									value: '5',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H4',
									value: '4',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H3',
									value: '3',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H2',
									value: '2',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) +
										' H1 (' +
										__( 'default', 'simpletoc' ) +
										')',
									value: '1',
								},
							] }
							onChange={ ( level ) =>
								setAttributes( {
									min_level: Number( level ),
								} )
							}
						/>
					</PanelRow>
					<PanelRow>
						<SelectControl
							label={ __( 'Maximum level', 'simpletoc' ) }
							help={ __(
								'Maximum depth of the headings.',
								'simpletoc'
							) }
							value={ attributes.max_level }
							options={ [
								{
									label:
										__( 'Including', 'simpletoc' ) +
										' H6 (' +
										__( 'default', 'simpletoc' ) +
										')',
									value: '6',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H5',
									value: '5',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H4',
									value: '4',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H3',
									value: '3',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H2',
									value: '2',
								},
								{
									label:
										__( 'Including', 'simpletoc' ) + ' H1',
									value: '1',
								},
							] }
							onChange={ ( level ) =>
								setAttributes( {
									max_level: Number( level ),
								} )
							}
						/>
					</PanelRow>
				</PanelBody>
			</Panel>
			<Panel>
				<PanelBody
					title={ __( 'Advanced Features', 'simpletoc' ) }
					icon={ advpanelicon }
					initialOpen={ false }
				>
					<PanelRow>
						<div
							style={ {
								marginBottom: '1em',
								border: '1px solid rgba(0, 0, 0, 0.05)',
								padding: '0.5em',
								backgroundColor: '#f7f7f7',
							} }
						>
							<p>
								<strong>
									{ __(
										'Think about making a donation if you use any of these features.',
										'simpletoc'
									) }
								</strong>
							</p>
							<ExternalLink href="https://marc.tv/out/donate">
								{ __( 'Donate here!', 'simpletoc' ) }
							</ExternalLink>
						</div>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Hide SimpleTOC', 'simpletoc' ) }
							checked={ hideTOC }
							disabled={ accordionEnabled }
							help={
								accordionEnabled
									? enabledGloballyHelp
									: undefined
							}
							onChange={ ( value ) => {
								if ( ! value ) {
									setAttributes( {
										hidden: false,
										accordion: false,
									} );
								} else {
									setAttributes( {
										hidden: true,
										accordion: false,
									} );
								}
							} }
						/>
					</PanelRow>

					{ hideTOC && (
						<PanelRow>
							<div
								className={ `simpletoc-hide-options${
									accordionEnabled ? ' is-disabled' : ''
								}` }
							>
								<RadioControl
									label={ __( 'Type', 'simpletoc' ) }
									disabled={ accordionEnabled }
									selected={
										! accordionEnabled && attributes.hidden
											? 'hidden'
											: 'accordion'
									}
									options={ [
										{
											label: __(
												'Hide with a clickable dropdown (using <details> tag).',
												'simpletoc'
											),
											value: 'hidden',
										},
										{
											label: __(
												'Hide in accordion menu. Adds minimal JS and CSS.',
												'simpletoc'
											),
											value: 'accordion',
										},
									] }
									onChange={ ( value ) => {
										setAttributes( {
											hidden: value === 'hidden',
											accordion: value === 'accordion',
										} );
									} }
								/>
							</div>
						</PanelRow>
					) }
					<PanelRow>
						<ToggleControl
							label={ __(
								'Highlight current section',
								'simpletoc'
							) }
							help={
								scrollSpyEnabled
									? __(
											'Enabled globally in SimpleTOC settings.',
											'simpletoc'
									  )
									: __(
											'Underlines the current section link while scrolling in supported browsers. Other browsers show the normal table of contents.',
											'simpletoc'
									  )
							}
							checked={
								scrollSpyEnabled || attributes.scroll_spy
							}
							disabled={ scrollSpyEnabled }
							onChange={ ( value ) =>
								setAttributes( { scroll_spy: value } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __(
								'Smooth scrolling support',
								'simpletoc'
							) }
							help={
								smoothEnabled
									? enabledGloballyHelp
									: __(
											'Adds the following CSS to the HTML element: "scroll-behavior: smooth;"',
											'simpletoc'
									  )
							}
							checked={ smoothEnabled || attributes.add_smooth }
							disabled={ smoothEnabled }
							onChange={ () =>
								setAttributes( {
									add_smooth: ! attributes.add_smooth,
								} )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Use absolute urls', 'simpletoc' ) }
							help={
								absoluteUrlsEnabled
									? enabledGloballyHelp
									: __(
											'Adds the permalink url to the fragment.',
											'simpletoc'
									  )
							}
							checked={
								absoluteUrlsEnabled ||
								attributes.use_absolute_urls
							}
							disabled={ absoluteUrlsEnabled }
							onChange={ () =>
								setAttributes( {
									use_absolute_urls:
										! attributes.use_absolute_urls,
								} )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Automatic refresh', 'simpletoc' ) }
							help={
								! autoupdateOption
									? __(
											'Disabled globally in SimpleTOC settings.',
											'simpletoc'
									  )
									: __(
											'Automatic updating of the table of contents.',
											'simpletoc'
									  )
							}
							checked={
								autoupdateOption && attributes.autoupdate
							}
							disabled={ ! autoupdateOption }
							onChange={ () =>
								setAttributes( {
									autoupdate: ! attributes.autoupdate,
								} )
							}
						/>
					</PanelRow>
					{ boxStyleEnabled && (
						<Notice status="info" isDismissible={ false }>
							{ __(
								'The Box style and its default background are enabled globally in SimpleTOC settings.',
								'simpletoc'
							) }
						</Notice>
					) }
					{ settingsUrl && (
						<PanelRow>
							<BaseControl
								id="simpletoc-global-settings-link"
								help={ __(
									'Global SimpleTOC settings can enforce these options for all blocks.',
									'simpletoc'
								) }
							>
								<ExternalLink href={ settingsUrl }>
									{ __(
										'Open global SimpleTOC settings',
										'simpletoc'
									) }
								</ExternalLink>
							</BaseControl>
						</PanelRow>
					) }
				</PanelBody>
			</Panel>
		</InspectorControls>
	);

	return (
		<div { ...blockProps }>
			{ controls }
			{ controlssidebar }
			<ServerSideRender
				block="simpletoc/toc"
				attributes={ serverSideRenderAttributes }
				LoadingResponsePlaceholder={
					SimpleTOCLoadingResponsePlaceholder
				}
				urlQueryArgs={ {
					simpletoc_refresh: serverSideRenderRefresh,
				} }
			/>
		</div>
	);
}
