document.addEventListener('DOMContentLoaded', (event) => {
    const buttons = document.querySelectorAll('button.simpletoc-collapsible');

    buttons.forEach((button) => {
        button.addEventListener('click', function () {
			this.classList.toggle('active');
            const content = this.parentElement.nextElementSibling;
            const isCollapsed = !content.style.maxHeight || content.style.maxHeight === '0px';
			const ariaExpanded = this.getAttribute( 'aria-expanded' );
			if ( ariaExpanded === 'true' ) {
				this.setAttribute( 'aria-expanded', 'false' );
			} else {
				this.setAttribute( 'aria-expanded', 'true' );
			}
			


            if (isCollapsed) {
                // Expand the content
                content.style.maxHeight = content.scrollHeight + 'px';
            } else {
                // Collapse the content
                content.style.maxHeight = '0px';
            }
        });
    });
});
