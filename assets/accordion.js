/**
 * SimpleTOC load function.
 */
const simpletocLoad = function () {
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
};

// Allow others to call function if needed.
window.simpletocLoad = simpletocLoad;

// Check to see if the document is already loaded.
if ( document.readyState === 'complete' || document.readyState !== 'loading' ) {
	simpletocLoad();
} else {
	// Fallback event if the document is not loaded.
	document.addEventListener( 'DOMContentLoaded', () => {
		simpletocLoad();
	} );
}
