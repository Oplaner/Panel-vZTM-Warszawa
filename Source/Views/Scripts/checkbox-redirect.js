window.addEventListener("DOMContentLoaded", () => {
    const checkboxes = document.querySelectorAll("input[type=checkbox");

    checkboxes.forEach(checkbox => {
        const redirectURL = checkbox.dataset.redirect;

        checkbox.addEventListener("change", () => {
            checkbox.disabled = true;
            window.location.href = redirectURL;
        });
    });
});