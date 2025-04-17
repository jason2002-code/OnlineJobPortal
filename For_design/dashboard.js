// Simple tab navigation
document.querySelectorAll('.dashboard-nav a').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('.dashboard-main section').forEach(section => {
            section.style.display = 'none';
        });
        document.querySelector(link.getAttribute('href')).style.display = 'block';
        document.querySelectorAll('.dashboard-nav a').forEach(a => {
            a.classList.remove('active');
        });
        link.classList.add('active');
    });
});
