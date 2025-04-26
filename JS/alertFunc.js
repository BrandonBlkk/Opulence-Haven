// Show Alert Function
export const showAlert = (message) => {
    const alertBox = document.getElementById("alertBox");
    const alertText = document.getElementById("alertText");
    alertText.textContent = message;

    setTimeout(() => {
        alertBox.classList.remove("-bottom-1", "opacity-0", "hidden");
        alertBox.classList.add("opacity-100", "bottom-3");
    }, 10);

    // Hide Alert
    setTimeout(() => {
        alertBox.classList.add("translate-x-full", "-right-full");
    }, 5000);
};

// Show Error Function
export const showError = (element, message) => {
    element.textContent = message;
    element.classList.remove("opacity-0");
    element.classList.add("opacity-100");
};

// Hide Error Function
export const hideError = (element) => {
    element.classList.remove("opacity-100");
    element.classList.add("opacity-0");
};

// Validate Field Function
export const validateField = (inputId, errorId, getErrorMessage) => {
    const input = document.getElementById(inputId).value.trim();
    const errorElement = document.getElementById(errorId);
    const errorMessage = getErrorMessage(input);

    if (errorMessage) {
        showError(errorElement, errorMessage);
        return false;
    } else {
        hideError(errorElement);
        return true;
    }
};
