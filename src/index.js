const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'gutentoc/toc', {
	title: __( 'Table of Contents', 'gutentoc' ),
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
