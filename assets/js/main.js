// Initialize AOS
if (typeof AOS !== 'undefined') {
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
}

// Navbar scroll effect
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
        if (!document.body.classList.contains('dark-mode')) {
            navbar.style.background = '#ffffff';
            navbar.style.boxShadow = '0 1px 2px 0 rgba(60,64,67,0.3), 0 2px 6px 2px rgba(60,64,67,0.15)';
        }
    } else {
        navbar.classList.remove('scrolled');
        if (!document.body.classList.contains('dark-mode')) {
            navbar.style.background = '#ffffff';
            navbar.style.boxShadow = 'none';
        }
    }
});

// Dark Mode Toggle Logic
const toggleButton = document.getElementById('theme-toggle');
if (toggleButton) {
    // Check local storage for preference
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
        toggleButton.textContent = '☀️';
    }

    toggleButton.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        let theme = 'light';
        if (document.body.classList.contains('dark-mode')) {
            theme = 'dark';
            toggleButton.textContent = '☀️';
            navbar.style.background = ''; // let css take over
            navbar.style.boxShadow = '';
        } else {
            toggleButton.textContent = '🌙';
            navbar.style.background = '#ffffff';
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 1px 2px 0 rgba(60,64,67,0.3), 0 2px 6px 2px rgba(60,64,67,0.15)';
            }
        }
        localStorage.setItem('theme', theme);
    });
}
