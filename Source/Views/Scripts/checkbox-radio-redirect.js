window.addEventListener("DOMContentLoaded", () => {
    const inputs = document.querySelectorAll("input[type=checkbox], input[type=radio]");

    inputs.forEach(input => {
        const redirectURL = input.dataset.redirect;

        input.addEventListener("change", () => {
            input.disabled = true;
            window.location.href = redirectURL;
        });
    });
});