const coll = document.getElementsByClassName('simpletoc-collapsible');

for (const element of coll) {
  element.addEventListener('click', function() {
    this.classList.toggle('active');
    const content = this.nextElementSibling;
    content.style.display = content.style.display === 'block' ? 'none' : 'block';
  });
}