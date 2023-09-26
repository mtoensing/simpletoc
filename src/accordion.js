const buttons = document.querySelectorAll('button.simpletoc-collapsible');

buttons.forEach((button) => {
	button.addEventListener('click', function () {
		this.classList.toggle('active');
		const content = this.parentElement.nextElementSibling;
		content.style.display =
			content.style.display === 'block' ? 'none' : 'block';

		// Toggle aria-expanded attribute on the button
		const ariaExpanded = this.getAttribute('aria-expanded');
		if (ariaExpanded === 'true') {
			this.setAttribute('aria-expanded', 'false');
		} else {
			this.setAttribute('aria-expanded', 'true');
		}
	});
});
