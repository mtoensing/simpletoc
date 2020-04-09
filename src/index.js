const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'simpletoc/toc', {
	title: __( 'Table of Contents', 'simpletoc' ),
	icon: 'list-view',
	category: 'layout',
	edit: props => {
		console.info(props);
		return <div>Table of contents</div>;
	},
	save: props => {
		return null;
	},
} );
