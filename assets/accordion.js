document.addEventListener( 'DOMContentLoaded', () => {
	const buttons = document.querySelectorAll( 'button.simpletoc-collapsible' );

	buttons.forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			button.classList.toggle( 'active' );
			const content = button.parentElement.nextElementSibling;
			const isCollapsed =
				! content.style.maxHeight || content.style.maxHeight === '0px';

			button.setAttribute(
				'aria-expanded',
				isCollapsed ? 'true' : 'false'
			);
			content.style.maxHeight = isCollapsed
				? `${ content.scrollHeight }px`
				: '0px';
		} );
	} );
} );
