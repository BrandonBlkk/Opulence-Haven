// Show Alert Function
let alertTimeoutId = null;
let loaderIntervalId = null;

export const showAlert = (message, isError = false) => {
    const alertBox = document.getElementById('alertBox');
    const alertText = document.getElementById('alertText');
    const alertIcon = document.getElementById('alertIcon');
    const closeAlert = document.getElementById('closeAlert');
    const alertLoaderBar = document.getElementById('alertLoaderBar');

    // Clear any existing timeout and interval
    if (alertTimeoutId) {
        clearTimeout(alertTimeoutId);
        alertTimeoutId = null;
    }
    if (loaderIntervalId) {
        clearInterval(loaderIntervalId);
        loaderIntervalId = null;
    }

    // Remove previous click listeners to prevent duplicates
    const newCloseAlert = closeAlert.cloneNode(true);
    closeAlert.parentNode.replaceChild(newCloseAlert, closeAlert);

    // Reset any ongoing animations
    alertBox.style.transition = 'none';
    alertBox.classList.remove('opacity-100', 'bottom-3', 'bg-red-400', 'bg-green-400');
    alertBox.classList.add('opacity-0', '-bottom-20');

    // Reset loader bar instantly without transition
    alertLoaderBar.style.transition = 'none';
    alertLoaderBar.style.width = '0%';
    alertLoaderBar.classList.remove('bg-green-500', 'bg-red-500');
    void alertLoaderBar.offsetHeight; // Force reflow

    // Restore loader bar transition for smooth fill
    alertLoaderBar.style.transition = 'width 5s linear';

    // Force reflow for alert box
    void alertBox.offsetHeight;

    // Restore alert box transition
    alertBox.style.transition = '';

    // Set alert content
    alertText.textContent = message;

    // Set alert style based on type
    if (isError) {
        alertBox.classList.add('bg-red-400');
        alertIcon.className = 'text-2xl text-white ri-error-warning-line';
        alertLoaderBar.classList.add('bg-red-500');
    } else {
        alertBox.classList.add('bg-green-400');
        alertIcon.className = 'text-2xl text-white ri-checkbox-circle-fill';
        alertLoaderBar.classList.add('bg-green-500');
    }

    // Show alert with animation
    setTimeout(() => {
        alertBox.classList.remove('opacity-0', '-bottom-20');
        alertBox.classList.add('opacity-100', 'bottom-3');

        // Start loader fill animation
        alertLoaderBar.style.width = '100%';
    }, 10);

    // Hide alert after 5 seconds
    alertTimeoutId = setTimeout(() => {
        alertBox.classList.remove('opacity-100', 'bottom-3');
        alertBox.classList.add('opacity-0', '-bottom-20');
        
        // Clear all references
        alertTimeoutId = null;
        if (loaderIntervalId) {
            clearInterval(loaderIntervalId);
            loaderIntervalId = null;
        }

        // Reset loader bar after alert hides
        setTimeout(() => {
            alertLoaderBar.style.transition = 'none';
            alertLoaderBar.style.width = '0%';
            alertLoaderBar.classList.remove('bg-green-500', 'bg-red-500');
        }, 150);
    }, 5000);

    // Add new click listener
    document.getElementById('closeAlert').addEventListener('click', () => {
        // Hide alert immediately
        alertBox.classList.remove('opacity-100', 'bottom-3');
        alertBox.classList.add('opacity-0', '-bottom-20');
        
        // Clear all timeouts and intervals
        if (alertTimeoutId) {
            clearTimeout(alertTimeoutId);
            alertTimeoutId = null;
        }
        if (loaderIntervalId) {
            clearInterval(loaderIntervalId);
            loaderIntervalId = null;
        }
        
        // Reset loader immediately
        alertLoaderBar.style.transition = 'none';
        alertLoaderBar.style.width = '0%';
        alertLoaderBar.classList.remove('bg-green-500', 'bg-red-500');
    });
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
