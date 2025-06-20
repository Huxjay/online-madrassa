
// Smooth fade-in effect for welcome message
window.addEventListener('load', () => {
    const welcomeText = document.querySelector('.login-box p');
    welcomeText.style.opacity = '0';
    welcomeText.style.transition = 'opacity 2s ease-in-out, transform 2s ease-in-out';
    setTimeout(() => {
        welcomeText.style.opacity = '1';
        welcomeText.style.transform = 'translateY(0)';
    }, 500);
});

// Subtle button ripple effect
const buttons = document.querySelectorAll('.login-box button');
buttons.forEach(button => {
    button.addEventListener('click', (e) => {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        ripple.className = 'ripple';
        button.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});

