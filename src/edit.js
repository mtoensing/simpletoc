import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	BlockControls,
	useBlockProps,
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
	SelectControl,
	ToolbarButton,
	ToggleControl,
	TextControl,
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

export default function Edit( { attributes, setAttributes } ) {
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
			{ ! ( attributes.no_title || attributes.accordion ) && (
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
							label={ __(
								'Hide in accordion menu',
								'simpletoc'
							) }
							help={ __(
								'Adds minimal JavaScript and css styles.',
								'simpletoc'
							) }
							checked={ attributes.accordion }
							onChange={ () =>
								setAttributes( {
									accordion: ! attributes.accordion,
								} )
							}
						/>
					</PanelRow>
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
							label={ __( 'Wrapper div', 'simpletoc' ) }
							help={ __(
								'Additionally adds the role "navigation" and ARIA attributes.',
								'simpletoc'
							) }
							checked={ attributes.wrapper }
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
