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
            navbar.style.background = 'rgba(255, 255, 255, 0.7)';
            navbar.style.boxShadow = '0 1px 2px 0 rgba(60,64,67,0.3), 0 2px 6px 2px rgba(60,64,67,0.15)';
        }
    } else {
        navbar.classList.remove('scrolled');
        if (!document.body.classList.contains('dark-mode')) {
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
        toggleButton.innerHTML = '<svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-700q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/></svg>';
    }

    toggleButton.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        let theme = 'light';
        if (document.body.classList.contains('dark-mode')) {
            theme = 'dark';
            toggleButton.innerHTML = '<svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-700q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/></svg>';
            navbar.style.background = ''; // let css take over
            navbar.style.boxShadow = '';
        } else {
            toggleButton.innerHTML = '<svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>';
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 1px 2px 0 rgba(60,64,67,0.3), 0 2px 6px 2px rgba(60,64,67,0.15)';
            }
        }
        localStorage.setItem('theme', theme);
    });
}
