const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'simpletoc/toc', {
	title: __( 'Table of Contents', 'simpletoc' ),
	icon: 'list-view',
	category: 'layout',
	edit( { className } ) {
        return <p className={ className }>
				<ul>
					<li>SimpleTOC</li>
					<li>SimpleTOC</li>
					<li>SimpleTOC</li>
				</ul>
				</p>;
    },
	save: props => {
		return null;
	},
} );
