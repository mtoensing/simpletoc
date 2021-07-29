import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, BlockControls } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { SelectControl, ToolbarGroup, ToolbarButton, ToggleControl, Panel, PanelBody, PanelRow } from '@wordpress/components';
const simpletocicon = <svg fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg"><path d="m365.983 78.248c2.72-32.415 29.921-57.908 63.029-57.908 34.91 0 63.253 28.342 63.253 63.252s-28.343 63.252-63.253 63.252v269.582c0 25.232-20.485 45.718-45.718 45.718h-313.62c-25.233 0-45.719-20.486-45.719-45.718v-292.46c0-25.233 20.486-45.718 45.719-45.718zm-245.943 324.857c-16.883 0-30.511-13.719-30.511-30.714 0-16.79 13.628-30.714 30.511-30.714 16.679 0 30.511 13.924 30.511 30.714 0 16.995-13.832 30.714-30.511 30.714zm50.851-40.952h183.063v20.476h-183.063zm-50.851-61.428c-16.883 0-30.511-13.719-30.511-30.714 0-16.79 13.628-30.714 30.511-30.714 16.679 0 30.511 13.924 30.511 30.714 0 16.995-13.832 30.714-30.511 30.714zm50.851-40.952h183.063v20.476h-183.063zm-50.851-61.428c-16.883 0-30.511-13.719-30.511-30.714 0-16.791 13.628-30.714 30.511-30.714 16.679 0 30.511 13.923 30.511 30.714 0 16.995-13.832 30.714-30.511 30.714zm50.851-40.952h183.063v20.476h-183.063zm253.007-44.649v-24.188h-24.008v-10.108h24.008v-24.008h10.229v24.008h24.008v10.108h-24.008v24.188z"/></svg>

registerBlockType('simpletoc/toc', {
  title: __('SimpleTOC', 'simpletoc'),
  icon: simpletocicon,
  category: 'layout',
  keywords: [ __( 'TOC' ), __( 'Table of Contents' )],
  attributes: {
		no_title: {
			type: 'boolean',
      default: false,
		},
    use_ol: {
			type: 'boolean',
      default: false,
		},
    add_smooth: {
			type: 'boolean',
      default: false,
		},
    use_absolute_urls: {
			type: 'boolean',
      default: false,
		},
		max_level: {
			type: 'integer',
      default: 6,
		},
	},
  edit: function(props) {
    return (
    <>
    <InspectorControls>
      <Panel>
        <PanelBody>
          <PanelRow>
            <ToggleControl
                label={__('Remove heading', 'simpletoc')}
                help={__('Disable the "Table of contents" block heading.', 'simpletoc')}
                checked={ props.attributes.no_title }
                onChange={ () => props.setAttributes( {  no_title: ! props.attributes.no_title } ) }
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
                label={__('Use an ordered list', 'simpletoc')}
                help={__('Replace the <ul> tag with an <ol> tag. This adds decimal numbers to each heading in the TOC.', 'simpletoc')}
                checked={ props.attributes.use_ol }
                onChange={ () => props.setAttributes( {  use_ol: ! props.attributes.use_ol } ) }
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
                label={__('Use absolute urls', 'simpletoc')}
                help={__('Adds the permalink url to the fragment.', 'simpletoc')}
                checked={ props.attributes.use_absolute_urls }
                onChange={ () => props.setAttributes( {  use_absolute_urls: ! props.attributes.use_absolute_urls } ) }
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
                label={__('Smooth scrolling support', 'simpletoc')}
                help={__('Add the css class "smooth-scroll" to the links. This enables smooth scrolling in some themes like GeneratePress.', 'simpletoc')}
                checked={ props.attributes.add_smooth }
                onChange={ () => props.setAttributes( {  add_smooth: ! props.attributes.add_smooth } ) }
            />
          </PanelRow>
          <PanelRow>
            <SelectControl
                label={__("Maximum Level", 'simpletoc')}
                help={__('Maximum depth of the headings.', 'simpletoc')}
                value= { props.attributes.max_level }
                options={ [
                    { label: __('Including', 'simpletoc') + ' H6 (' + __('Show all', 'simpletoc') + ')' , value: '6' },
                    { label: __('Including', 'simpletoc') + ' H5', value: '5' },
                    { label: __('Including', 'simpletoc') + ' H4', value: '4' },
                    { label: __('Including', 'simpletoc') + ' H3', value: '3' },
                    { label: __('Including', 'simpletoc') + ' H2', value: '2' },
                ] }
                onChange={ ( level ) => props.setAttributes( { max_level: Number(level) } ) }
            />
          </PanelRow>
        </PanelBody>
      </Panel>
    </InspectorControls>
    <BlockControls>
      <ToolbarGroup>
        <ToolbarButton
          className="components-icon-button components-toolbar__control"
          label={__('Update table of contents', 'simpletoc')}
          onClick={ () => props.setAttributes( { updated: Date.now()} ) }
          icon="update"
        />
      </ToolbarGroup>
  </BlockControls>
  <ServerSideRender block={props.name} attributes={props.attributes} />
  </>
  )
  },
  save: props => {
    return null;
  },
});
