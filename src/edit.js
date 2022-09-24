import { __ } from '@wordpress/i18n';
import { InspectorControls, BlockControls } from "@wordpress/block-editor";
import ServerSideRender from "@wordpress/server-side-render";
import {
  SelectControl,
  ToolbarGroup,
  ToolbarButton,
  ToggleControl,
  TextControl,
  Panel,
  PanelBody,
  PanelRow,
  ExternalLink
} from "@wordpress/components";
import { useBlockProps } from "@wordpress/block-editor";
import { select, subscribe } from '@wordpress/data';
import {useEffect, useState} from 'react';


export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();

  /* Update SimpleTOC if the post is saved successfully.          */
  /* Source: https://github.com/WordPress/gutenberg/issues/17632  */

  const { isSavingPost } = select( 'core/editor' );
  const [isSavingProcess, setSavingProcess] = useState(false);
  const advpanelicon = 'settings'; 
  const updatePost = function () {
    if( attributes.autorefresh === true ) {
      setAttributes({ updated: new Date().getTime() });
    }
  };

  subscribe(() => {
      if (isSavingPost()) {
          setSavingProcess(true);
      } else {
          setSavingProcess(false);
      }
  });

  useEffect(() => {
      if (isSavingProcess) {
          updatePost();
      }
  }, [isSavingProcess]);

  return (
    <div {...blockProps}>
      <InspectorControls>
        <Panel>
          <PanelBody>
          <PanelRow>
              <SelectControl
                label={__("Maximum level", "simpletoc")}
                help={__("Maximum depth of the headings.", "simpletoc")}
                value={attributes.max_level}
                options={[
                  {
                    label:
                      __("Including", "simpletoc") +
                      " H6 (" +
                      __("default", "simpletoc") +
                      ")",
                    value: "6",
                  },
                  {
                    label: __("Including", "simpletoc") + " H5",
                    value: "5",
                  },
                  {
                    label: __("Including", "simpletoc") + " H4",
                    value: "4",
                  },
                  {
                    label: __("Including", "simpletoc") + " H3",
                    value: "3",
                  },
                  {
                    label: __("Including", "simpletoc") + " H2",
                    value: "2",
                  },
                  {
                    label: __("Including", "simpletoc") + " H1",
                    value: "1",
                  },
                ]}
                onChange={(level) =>
                  setAttributes({ max_level: Number(level) })
                }
              />
              </PanelRow>
              <PanelRow>
              <SelectControl
                label={__("Minimum level", "simpletoc")}
                help={__("Minimum depth of the headings.", "simpletoc")}
                value={attributes.min_level}
                options={[
                  {
                    label: __("Including", "simpletoc") + " H6",
                    value: "6",
                  },
                  {
                    label: __("Including", "simpletoc") + " H5",
                    value: "5",
                  },
                  {
                    label: __("Including", "simpletoc") + " H4",
                    value: "4",
                  },
                  {
                    label: __("Including", "simpletoc") + " H3",
                    value: "3",
                  },
                  {
                    label: __("Including", "simpletoc") + " H2",
                    value: "2",
                  },
                  {
                    label: __("Including", "simpletoc") +
                    " H1 (" +
                    __("default", "simpletoc") +
                    ")",
                    value: "1",
                  },
                ]}
                onChange={(level) =>
                  setAttributes({ min_level: Number(level) })
                }
              />
            </PanelRow>
            <PanelRow>
              <ToggleControl
                label={__("Remove heading", "simpletoc")}
                help={__(
                  'Disable the "Table of contents" block heading and add your own heading block.',
                  "simpletoc"
                )}
                checked={attributes.no_title}
                onChange={() =>
                  setAttributes({ no_title: !attributes.no_title })
                }
              />
            </PanelRow>
              { ! attributes.no_title && <PanelRow>
              <TextControl
                label={__("Heading Text", "simpletoc")}
                help={__(
                  "Set the heading text of the block.",
                  "simpletoc"
                ) + " " +
                __("Default value", 
                "simpletoc"
                ) + ": " +
                __("Table of Contents", 
                "simpletoc"
                )}
                value={attributes.title_text}
                onChange={(value) =>
                  setAttributes({ title_text: value || __("Table of Contents", "simpletoc") })
                }
              />
            </PanelRow> }
            <PanelRow>
              <ToggleControl
                label={__("Use an ordered list", "simpletoc")}
                help={__(
                  "Replace the <ul> tag with an <ol> tag. This adds decimal numbers to each heading in the TOC.",
                  "simpletoc"
                )}
                checked={attributes.use_ol}
                onChange={() => setAttributes({ use_ol: !attributes.use_ol })}
              />
            </PanelRow>
            <PanelRow>
              <ToggleControl
                label={__("Remove list indent", "simpletoc")}
                help={__(
                  "No bullet points or numbers at the first level.",
                  "simpletoc"
                )}
                checked={attributes.remove_indent}
                onChange={() => setAttributes({ remove_indent: !attributes.remove_indent })}
              />
            </PanelRow>
            
          </PanelBody>
        </Panel>
        <Panel>
        <PanelBody title={ __("Advanced Features", "simpletoc") } icon={ advpanelicon } initialOpen={ false }>
          <PanelRow>
            <div style={{marginBottom: '1em',border: '1px solid rgba(0, 0, 0, 0.05)', padding: '0.5em', backgroundColor: '#f7f7f7'}}>
              <p><strong>{ __('Think about making a donation if you use any of these features.', "simpletoc") }</strong></p>
              <ExternalLink href="https://marc.tv/out/donate">{ __('Donate here!', "simpletoc") }</ExternalLink>
            </div>
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__("Smooth scrolling support", "simpletoc")}
              help={__(
                'Add the css class "smooth-scroll" to the links. This enables smooth scrolling in some themes like GeneratePress.',
                "simpletoc"
              )}
              checked={attributes.add_smooth}
              onChange={() =>
                setAttributes({
                  add_smooth: !attributes.add_smooth,
                })
              }
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__("Use absolute urls", "simpletoc")}
              help={__(
                "Adds the permalink url to the fragment.",
                "simpletoc"
              )}
              checked={attributes.use_absolute_urls}
              onChange={() =>
                setAttributes({
                  use_absolute_urls: !attributes.use_absolute_urls,
                })
              }
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__("Automatically refresh TOC", "simpletoc")}
              help={__(
                'Disable this to remove redudant changed content warning in editor.',
                "simpletoc"
              )}
              checked={attributes.autorefresh}
              onChange={() =>
                setAttributes({
                  autorefresh: !attributes.autorefresh,
                })
              }
            />
          </PanelRow>
        </PanelBody>
        </Panel>
      </InspectorControls>
      <BlockControls>
        <ToolbarGroup>
          <ToolbarButton
            className="components-icon-button components-toolbar__control"
            label={__("Update table of contents", "simpletoc")}
            onClick={() => setAttributes({ updated: new Date().getTime() })}
            icon="update"
          />
        </ToolbarGroup>
      </BlockControls>
      <ServerSideRender block="simpletoc/toc" attributes={attributes} />
    </div>
  );
}
