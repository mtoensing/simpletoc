const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'simpletoc/toc', {
	title: __( 'Table of Contents', 'simpletoc' ),
	icon: 'list-view',
	category: 'layout',
	edit( { className } ) {
        return <p className={ className }>SimpleTOC</p>;
    },
	save: props => {
		return null;
	},
} );
