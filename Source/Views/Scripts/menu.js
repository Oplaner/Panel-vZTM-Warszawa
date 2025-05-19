window.addEventListener("DOMContentLoaded", () => {
    const menuButton = document.getElementById("menuButton");
    const menu = document.getElementById("menu");
    const menuAnimationDuration = 250;
    let isMenuOpen = false;

    hideElement(menu);

    menuButton.onclick = () => {
        menuButton.classList.toggle("active");

        if (isMenuOpen) {
            menu.classList.remove("active");
            setTimeout(() => hideElement(menu), menuAnimationDuration);
        } else {
            showElement(menu, "flex");
            requestAnimationFrame(() => {
                menu.classList.add("active");
            });
        }

        isMenuOpen = !isMenuOpen;
    }
});

function hideElement(element) {
    element.style.display = "none";
}

function showElement(element, displayStyle) {
    element.style.display = displayStyle;
}