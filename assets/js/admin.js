document.addEventListener("DOMContentLoaded", () => {
    console.log("Admin panel loaded");

    // Sidebar link AJAX loader
    const sidebarLinks = document.querySelectorAll(".sidebar a[href^='index.php?page=']");
    const mainContent = document.querySelector("main.dashboard");

    sidebarLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            const pageParam = this.getAttribute("href").split("page=")[1];
            if (!pageParam) return;

            // Fade out effect
            mainContent.style.opacity = 0.3;

            // Fetch page via AJAX
            fetch(`pages/${pageParam}.php`)
                .then(response => {
                    if (!response.ok) throw new Error("Page not found");
                    return response.text();
                })
                .then(data => {
                    setTimeout(() => {
                        mainContent.innerHTML = data;
                        mainContent.style.opacity = 1;
                        window.history.pushState({}, '', `index.php?page=${pageParam}`);
                    }, 200);
                })
                .catch(err => {
                    console.error("Failed to load page:", err);
                    mainContent.innerHTML = <p style='color: red;'>❌ ${err.message}</p>;
                    mainContent.style.opacity = 1;
                });
        });
    });

    // Handle back/forward browser navigation
    window.addEventListener("popstate", () => {
        const params = new URLSearchParams(window.location.search);
        const page = params.get("page");
        if (page) {
            fetch(`pages/${page}.php`)
                .then(res => res.text())
                .then(data => {
                    mainContent.innerHTML = data;
                });
        }
    });
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