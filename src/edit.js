import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import {
	InspectorControls,
	BlockControls,
	useBlockProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import ServerSideRender from '@wordpress/server-side-render';
import {
	formatListBullets,
	formatOutdent,
	formatIndent,
	update,
	formatListNumbered,
} from '@wordpress/icons';
import {
	BaseControl,
	ColorPalette,
	SelectControl,
	ToolbarButton,
	ToggleControl,
	TextControl,
	RadioControl,
	Panel,
	PanelBody,
	PanelRow,
	ExternalLink,
	Spinner,
} from '@wordpress/components';
import HeadingLevelDropdown from './heading-level-dropdown';
import { useSelect } from '@wordpress/data';
import './editor.scss';
import './../assets/accordion.css';

const DEFAULT_BOX_COLOR = '#ebebeb';

export default function Edit( { attributes, setAttributes } ) {
	const { hideTOC, hidden, accordion } = attributes;

	// Effect to adjust hideTOC based on hidden or accordion attributes
	useEffect( () => {
		// If hideTOC is already set, no need to adjust
		if ( hideTOC !== undefined ) {
			return;
		}

		// Determine if we need to activate hideTOC based on hidden or accordion
		if ( hidden || accordion ) {
			setAttributes( { hideTOC: true } );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] ); // Empty dependency array ensures this runs once on mount

	const blockProps = useBlockProps();

	// Get the autoupdate option from WordPress php.
	const autoupdateOption = useSelect( ( select ) => {
		const optionValue =
			select( 'core' ).getSite()?.simpletoc_autoupdate_enabled;
		if ( Number( optionValue ) !== 1 ) {
			return true;
		}
		return false;
	}, [] );

	const { autoupdate } = attributes;
	const editorSettings = useSelect( ( select ) => {
		return select( blockEditorStore ).getSettings() || {};
	}, [] );
	const boxColors = editorSettings.colors || [];
	const selectedBoxColor = attributes.box_color;
	const wrapperEnabled = attributes.wrapper || attributes.box_style;
	const settingsUrl =
		editorSettings.simpletocSettingsUrl ||
		window.simpletocEditorSettings?.settingsUrl ||
		'';

	const { returnisSaving, returnisSavingNonPostEntityChanges } = useSelect(
		( select ) => {
			const { isSavingPost, isSavingNonPostEntityChanges } =
				select( editorStore );
			return {
				returnisSaving: isSavingPost(),
				returnisSavingNonPostEntityChanges:
					isSavingNonPostEntityChanges(),
			};
		}
	);

	const advpanelicon = 'settings';

	const controls = (
		<BlockControls group="block">
			{ ! (
				attributes.no_title ||
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
				icon={ formatListBullets }
				title={ __( 'Convert to unordered list', 'simpletoc' ) }
				describedBy={ __( 'Convert to unordered list', 'simpletoc' ) }
				isActive={ attributes.use_ol === false }
				onClick={ () => {
					setAttributes( { use_ol: false } );
				} }
			/>
			<ToolbarButton
				icon={ formatListNumbered }
				title={ __( 'Convert to ordered list', 'simpletoc' ) }
				describedBy={ __( 'Convert to ordered list', 'simpletoc' ) }
				isActive={ attributes.use_ol === true }
				onClick={ () => {
					setAttributes( { use_ol: true } );
				} }
			/>
			<ToolbarButton
				icon={ formatOutdent }
				title={ __( 'Indent list', 'simpletoc' ) }
				describedBy={ __( 'Indent list', 'simpletoc' ) }
				isActive={ attributes.remove_indent === true }
				onClick={ () => {
					setAttributes( { remove_indent: true } );
				} }
			/>
			<ToolbarButton
				icon={ formatIndent }
				title={ __( 'Outdent list', 'simpletoc' ) }
				describedBy={ __( 'Outdent list', 'simpletoc' ) }
				isActive={ attributes.remove_indent === false }
				onClick={ () => {
					setAttributes( { remove_indent: false } );
				} }
			/>
			{ ( ! attributes.autoupdate || ! autoupdateOption ) && (
				<ToolbarButton
					icon={ update }
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
							onChange={ ( value ) => {
								if ( ! value ) {
									// When turning off the "Hide SimpleTOC", reset both 'hidden' and 'accordion'
									setAttributes( {
										hideTOC: false,
										hidden: false,
										accordion: false,
									} );
								} else {
									// When turning on, set 'hidden' true by default (and 'accordion' remains false unless chosen otherwise)
									setAttributes( {
										hideTOC: true,
										hidden: true,
									} );
								}
							} }
						/>
					</PanelRow>

					{ hideTOC && (
						<PanelRow>
							<div className="simpletoc-hide-options">
								<RadioControl
									label={ __( 'Type', 'simpletoc' ) }
									selected={
										attributes.hidden
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
								'Smooth scrolling support',
								'simpletoc'
							) }
							help={ __(
								'Adds the following CSS to the HTML element: "scroll-behavior: smooth;"',
								'simpletoc'
							) }
							checked={ attributes.add_smooth }
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
							help={ __(
								'Adds the permalink url to the fragment.',
								'simpletoc'
							) }
							checked={ attributes.use_absolute_urls }
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
							label={ __( 'Box style', 'simpletoc' ) }
							help={ __(
								'Adds box spacing and title styling. Enables the wrapper markup automatically.',
								'simpletoc'
							) }
							checked={ attributes.box_style }
							onChange={ () =>
								setAttributes( {
									box_style: ! attributes.box_style,
									box_color:
										! attributes.box_style &&
										! attributes.box_color
											? DEFAULT_BOX_COLOR
											: attributes.box_color,
								} )
							}
						/>
					</PanelRow>
					{ attributes.box_style && boxColors.length > 0 && (
						<PanelRow>
							<BaseControl
								id="simpletoc-box-color"
								label={ __( 'Box color', 'simpletoc' ) }
								help={ __(
									'Uses the active editor color palette.',
									'simpletoc'
								) }
							>
								<ColorPalette
									colors={ boxColors }
									value={ selectedBoxColor }
									onChange={ ( colorValue ) =>
										setAttributes( {
											box_color: colorValue || '',
										} )
									}
									clearable={ false }
								/>
							</BaseControl>
						</PanelRow>
					) }
					<PanelRow>
						<ToggleControl
							label={ __( 'Wrapper div', 'simpletoc' ) }
							help={
								attributes.box_style
									? __(
											'Enabled automatically while box style is active.',
											'simpletoc'
									  )
									: __(
											'Additionally adds the role "navigation" and ARIA attributes.',
											'simpletoc'
									  )
							}
							checked={ wrapperEnabled }
							disabled={ attributes.box_style }
							onChange={ () =>
								setAttributes( {
									wrapper: ! attributes.wrapper,
								} )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Automatic refresh', 'simpletoc' ) }
							help={ __(
								'Automatic updating of the table of contents.',
								'simpletoc'
							) }
							checked={ attributes.autoupdate }
							onChange={ () =>
								setAttributes( {
									autoupdate: ! attributes.autoupdate,
								} )
							}
						/>
					</PanelRow>
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
			{ /* Conditional rendering based on autoupdate attribute */ }
			{ autoupdateOption &&
			autoupdate &&
			( returnisSaving || returnisSavingNonPostEntityChanges ) ? (
				<Spinner />
			) : (
				<ServerSideRender
					block="simpletoc/toc"
					attributes={ attributes }
				/>
			) }
		</div>
	);
}
