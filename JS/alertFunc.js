// Show Alert Function
let alertTimeoutId = null;

export const showAlert = (message, isError = false) => {
    const alertBox = document.getElementById('alertBox');
    const alertText = document.getElementById('alertText');
    const alertIcon = document.getElementById('alertIcon');
    
    // Clear any existing timeout to prevent premature hiding
    if (alertTimeoutId) {
        clearTimeout(alertTimeoutId);
        alertTimeoutId = null;
    }
    
    // Reset any ongoing animations
    alertBox.style.transition = 'none';
    alertBox.classList.remove('opacity-100', 'bottom-3', 'bg-red-400', 'bg-green-400');
    alertBox.classList.add('opacity-0', '-bottom-20');
    
    // Force reflow to reset the element
    void alertBox.offsetHeight;
    
    // Restore transition
    alertBox.style.transition = '';
    
    // Set alert content
    alertText.textContent = message;
    
    // Set alert style based on type
    if (isError) {
        alertBox.classList.add('bg-red-400');
        alertIcon.className = 'text-2xl text-white ri-error-warning-line';
    } else {
        alertBox.classList.add('bg-green-400');
        alertIcon.className = 'text-2xl text-white ri-checkbox-circle-fill';
    }
    
    // Show alert with animation
    setTimeout(() => {
        alertBox.classList.remove('opacity-0', '-bottom-20');
        alertBox.classList.add('opacity-100', 'bottom-3');
    }, 10);
    
    // Hide alert after 5 seconds
    alertTimeoutId = setTimeout(() => {
        alertBox.classList.remove('opacity-100', 'bottom-3');
        alertBox.classList.add('opacity-0', '-bottom-20');
        alertTimeoutId = null;
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
