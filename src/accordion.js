const coll = document.getElementsByClassName( 'simpletoc-collapsible' );

for ( let i = 0; i < coll.length; i++ ) {
	coll[ i ].addEventListener( 'click', function () {
		this.classList.toggle( 'active' );
		const content = this.nextElementSibling;
		if ( content.style.display === 'block' ) {
			content.style.display = 'none';
		} else {
			content.style.display = 'block';
		}
	} );
}
