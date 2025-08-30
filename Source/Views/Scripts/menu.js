window.addEventListener("DOMContentLoaded", () => {
    const menuButton = document.getElementById("menuButton");
    const menu = document.getElementById("menu");
    const menuAnimationDuration = 250;
    let isMenuOpen = false;

    hideMenu();

    menuButton.onclick = () => {
        menuButton.classList.toggle("active");

        if (isMenuOpen) {
            menu.classList.remove("active");
            setTimeout(() => hideMenu(), menuAnimationDuration);
        } else {
            showMenu();
            requestAnimationFrame(() => {
                menu.classList.add("active");
            });
        }

        isMenuOpen = !isMenuOpen;
    }

    function hideMenu() {
        menu.style.display = "none";
    }
    
    function showMenu() {
        menu.style.display = "flex";
    }
});