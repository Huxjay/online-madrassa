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

document.addEventListener("DOMContentLoaded", () => {
  const sidebarLinks = document.querySelectorAll(".sidebar a[href^='index.php?page=']");
  const mainContent = document.getElementById("mainContent");

  sidebarLinks.forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      const pageParam = this.getAttribute("href").split("page=")[1];
      if (!pageParam) return;

      // Fade out
      mainContent.style.opacity = 0.4;

      // Load page via AJAX
      fetch(`pageParam}.php`)
        .then(response => response.text())
        .then(data => {
          setTimeout(() => {
            mainContent.innerHTML = data;
            mainContent.style.opacity = 1;
            window.history.pushState({}, '', `index.php?page=${pageParam}`);
          }, 200);
        })
        .catch(err => {
          console.error("Failed to load page:", err);
          mainContent.innerHTML = "<p style='color: red'>⚠ Failed to load page.</p>";
          mainContent.style.opacity = 1;
        });
    });
  });
});