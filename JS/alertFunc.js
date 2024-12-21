// alerts.js
export const showAlert = (message) => {
    const alertBox = document.getElementById("alertBox");
    const alertText = document.getElementById("alertText");
    alertText.textContent = message;

    alertBox.classList.remove("-bottom-1", "opacity-0");
    alertBox.classList.add("opacity-100", "bottom-3");

    setTimeout(() => {
        alertBox.classList.add("-bottom-1", "opacity-0");
        alertBox.classList.remove("opacity-100", "bottom-3");
    }, 5000);
};

export const showError = (element, message) => {
    element.textContent = message;
    element.classList.remove("opacity-0");
    element.classList.add("opacity-100");
};

export const hideError = (element) => {
    element.classList.remove("opacity-100");
    element.classList.add("opacity-0");
};
