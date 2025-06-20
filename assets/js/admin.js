document.addEventListener("DOMContentLoaded", () => {
    console.log("Admin panel loaded");
});

// Toggle sidebar
function toggleSidebar() {
    const container = document.querySelector('.admin-container');
    container.classList.toggle('collapsed');
}

// Toggle dark mode
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}