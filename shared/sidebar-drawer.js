(function () {
    function initSidebarDrawer() {
        const sidebar = document.querySelector(".sidebar");
        if (!sidebar) {
            return;
        }

        const row = sidebar.closest(".row");
        let mainContent = null;
        if (row) {
            mainContent = Array.from(row.children).find((child) => child !== sidebar);
        }
        if (!mainContent) {
            mainContent = sidebar.nextElementSibling;
        }
        if (!mainContent) {
            return;
        }

        mainContent.classList.add("sidebar-drawer-main");

        if (!sidebar.id) {
            sidebar.id = "sidebarDrawer";
        }

        const desktopQuery = window.matchMedia("(min-width: 992px)");
        const toggle = document.createElement("button");
        toggle.type = "button";
        toggle.className = "btn btn-dark btn-sm sidebar-drawer-toggle";
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-controls", sidebar.id);
        toggle.textContent = "Menu";

        const backdrop = document.createElement("div");
        backdrop.className = "sidebar-drawer-backdrop";
        backdrop.setAttribute("aria-hidden", "true");

        document.body.prepend(backdrop);
        document.body.prepend(toggle);

        const closeMobileSidebar = () => {
            document.body.classList.remove("sidebar-open");
            toggle.setAttribute("aria-expanded", "false");
            backdrop.setAttribute("aria-hidden", "true");
        };

        toggle.addEventListener("click", function () {
            const isOpen = document.body.classList.toggle("sidebar-open");
            toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
            backdrop.setAttribute("aria-hidden", isOpen ? "false" : "true");
        });

        backdrop.addEventListener("click", closeMobileSidebar);

        document.querySelectorAll(".sidebar .nav-link").forEach((link) => {
            link.addEventListener("click", function () {
                if (!desktopQuery.matches) {
                    closeMobileSidebar();
                }
            });
        });

        desktopQuery.addEventListener("change", function (e) {
            if (e.matches) {
                closeMobileSidebar();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && document.body.classList.contains("sidebar-open")) {
                closeMobileSidebar();
            }
        });
    }

    document.addEventListener("DOMContentLoaded", initSidebarDrawer);
})();
