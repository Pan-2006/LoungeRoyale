document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector("[data-sidebar-toggle]");
    const sidebar = document.getElementById("adminSidebar");
    if (toggle && sidebar) {
        toggle.addEventListener("click", () => sidebar.classList.toggle("open"));
    }


    document.querySelectorAll("[data-confirm]").forEach((button) => {
        button.addEventListener("click", (event) => {
            if (!confirm(button.getAttribute("data-confirm"))) {
                event.preventDefault();
            }
        });
    });
});
