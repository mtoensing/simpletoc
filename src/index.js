const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;

const blockStyle = {
	backgroundColor: '#900',
	color: '#fff',
	padding: '20px',
};

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
