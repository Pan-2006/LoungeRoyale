document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-links a');

    // Toggle Mobile Navigation Menu
    menuToggle.addEventListener('click', () => {
        menuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close Menu when clicking any navigation link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    // Subtle parallax effect on the background ribbon for dynamic desktop viewing
    window.addEventListener('scroll', () => {
        if (window.innerWidth > 768) {
            const scrolled = window.pageYOffset;
            const ribbon = document.querySelector('.gold-ribbon');
            if (ribbon) {
                ribbon.style.transform = `translateY(${scrolled * 0.15}px)`;
            }
        }
    });
});