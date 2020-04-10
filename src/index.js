const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType( 'simpletoc/toc', {
	title: __( 'SimpleTOC', 'simpletoc' ),
	icon: 'list-view',
	category: 'layout',
	edit: function( props ) {
        return (
					<p className={ props.className }>
            <ServerSideRender
                block="simpletoc/toc"
                attributes={ props.attributes }
            />
					</p>
        );
    },
	save: props => {
		return null;
	},
} );
