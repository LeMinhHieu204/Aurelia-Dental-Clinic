import '../css/aurelia.css';

const menuToggle = document.querySelector('#menu-toggle');
const mainNav = document.querySelector('#main-nav');

menuToggle?.addEventListener('click', () => {
    const open = mainNav?.classList.toggle('open') ?? false;
    menuToggle.setAttribute('aria-expanded', String(open));
    menuToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    menuToggle.textContent = open ? '×' : '☰';
    document.body.classList.toggle('menu-open', open);
});

document.querySelectorAll('.main-nav a').forEach((link) => link.addEventListener('click', () => {
    mainNav?.classList.remove('open');
    document.body.classList.remove('menu-open');
    menuToggle?.setAttribute('aria-expanded', 'false');
    menuToggle?.setAttribute('aria-label', 'Open menu');
    if (menuToggle) menuToggle.textContent = '☰';
}));

const backTop = document.querySelector('#back-top');
window.addEventListener('scroll', () => backTop?.classList.toggle('visible', window.scrollY > 500), { passive: true });
backTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

document.querySelectorAll('form[data-booking-form]').forEach((form) => form.addEventListener('submit', (event) => {
    const date = form.querySelector('[name="preferred_date"]');
    if (date && date.value && date.value < new Date().toISOString().slice(0, 10)) {
        event.preventDefault();
        date.setCustomValidity('Please choose today or a future date.');
        date.reportValidity();
    } else if (date) {
        date.setCustomValidity('');
    }
}));

