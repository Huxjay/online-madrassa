document.addEventListener("DOMContentLoaded", () => {
  console.log("Teacher dashboard loaded");
});

// Toggle sidebar visibility
function toggleSidebar() {
  const container = document.querySelector('.teacher-container');
  container.classList.toggle('collapsed');

  const sidebar = document.querySelector('.sidebar');
  const mainContent = document.querySelector('.main-content');

  if (container.classList.contains('collapsed')) {
    sidebar.style.width = '0';
    mainContent.style.marginLeft = '0';
  } else {
    sidebar.style.width = '250px';
    mainContent.style.marginLeft = '0'; // or set to sidebar width if fixed layout is used
  }
}

// Toggle dark mode styling
function toggleDarkMode() {
  document.body.classList.toggle('dark-mode');
}