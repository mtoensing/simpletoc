const { __ } = wp.i18n;
const el = wp.element.createElement;
const registerBlockType = wp.blocks.registerBlockType;
const BlockControls = wp.blockEditor.BlockControls;
const ServerSideRender = wp.serverSideRender;
const Toolbar = wp.components.Toolbar;
const IconButton = wp.components.Button;
const simpletocicon = wp.element.createElement('svg', { width: 20, height: 20 },
  el('path', {
    d: "M5.5 7C4.67 7 4 6.33 4 5.5 4 4.68 4.67 4 5.5 4 6.32 4 7 4.68 7 5.5 7 6.33 6.32 7 5.5 7zM8 5h9v1H8V5zm-2.5 7c-.83 0-1.5-.67-1.5-1.5C4 9.68 4.67 9 5.5 9c.82 0 1.5.68 1.5 1.5 0 .83-.68 1.5-1.5 1.5zM8 10h9v1H8v-1zm-2.5 7c-.83 0-1.5-.67-1.5-1.5 0-.82.67-1.5 1.5-1.5.82 0 1.5.68 1.5 1.5 0 .83-.68 1.5-1.5 1.5zM8 15h9v1H8v-1z"
  })
);

function sendfakeAttribute(props) {
  // this acuallty triggers the ServerSideRender again ¯\_(ツ)_/¯
  props.setAttributes({ updated: Date.now()});
}

registerBlockType('simpletoc/toc', {
  title: __('SimpleTOC', 'simpletoc'),
  icon: simpletocicon,
  category: 'layout',
  edit: function(props) {
    return (
    <div>
    <BlockControls>
      <Toolbar>
        <IconButton
          className="components-icon-button components-toolbar__control"
          label={__('Update table of contents', 'simpletoc')}
          onClick={function() {
            sendfakeAttribute(props)
          }}
          icon="update"
        />
      </Toolbar>
  </BlockControls>
  <p className={props.className}>
    <ServerSideRender block={props.name} attributes={props.attributes} />
  </p>
  </div>
    )
  },
  save: props => {
    return null;
  },
});
