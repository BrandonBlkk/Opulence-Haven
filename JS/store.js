import { showError, hideError, showAlert, validateField } from './alertFunc.js';

// Move Right Loader
let moveRight = document.getElementById("move-right");
const darkOverlay2 = document.getElementById('darkOverlay2');

window.addEventListener('scroll', () => {
    let scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
    let scrollPercentage = (window.scrollY / scrollableHeight) * 100; 

    if (scrollPercentage >= 100) {
        moveRight.style.width = '100%';
    } else {
        moveRight.style.width = scrollPercentage + '%';
    }
});

// Show maintenance alert on page load if mode is enabled
document.addEventListener('DOMContentLoaded', () => {
    const isMaintenanceMode = localStorage.getItem('maintenanceMode') === 'true';

    if (isMaintenanceMode) {
        showMaintenanceAlert();
    }
});

// Listen for localStorage changes
window.addEventListener('storage', (event) => {
    if (event.key === 'maintenanceMode') {
        const isMaintenanceMode = event.newValue === 'true';
        if (isMaintenanceMode) {
            showMaintenanceAlert();
        }
    }
    if (event.key === 'maintenanceMode') {
        const isAciveMode = event.newValue === 'false';
        if (isAciveMode) {
            closeAlert();
        }
    }
});

// Function to show the alert
function showMaintenanceAlert() {
    const maintenanceAlert = document.getElementById('maintenanceAlert');
    maintenanceAlert.classList.remove('opacity-0', 'invisible', '-translate-y-5');
    darkOverlay2.classList.remove('opacity-0', 'invisible');
    darkOverlay2.classList.add('opacity-100');
    document.body.style.overflow = 'hidden';
}

// Function to close the alert
function closeAlert() {
    const maintenanceAlert = document.getElementById('maintenanceAlert');
    maintenanceAlert.classList.add('opacity-0', 'invisible', '-translate-y-5');
    darkOverlay2.classList.add('opacity-0', 'invisible');
    darkOverlay2.classList.remove('opacity-100');
    document.body.style.overflow = 'auto';
}

// Search Bar
const storeMenubar = document.getElementById('storeMenubar');
const storeDarkOverlay = document.getElementById('storeDarkOverlay');

document.getElementById('search-icon').addEventListener('click', () => {
    const searchBar = document.getElementById('search-bar');
    searchBar.classList.toggle('top-0');
    storeDarkOverlay.classList.toggle('hidden');
});

storeDarkOverlay.addEventListener('click', () => {
    const searchBar = document.getElementById('search-bar');
    searchBar.classList.toggle('top-0');
    storeDarkOverlay.classList.toggle('hidden');
});

searchCloseBtn.addEventListener('click', () => {
    const searchBar = document.getElementById('search-bar');
    searchBar.classList.toggle('top-0');
    storeDarkOverlay.classList.toggle('hidden');
});

// Search Bar Close Btn
const closeBtn = document.getElementById('closeBtn');
const aside = document.getElementById('aside');
const darkOverlay = document.getElementById('darkOverlay');

storeMenubar.addEventListener('click', () => {
    aside.style.right = '0%';
    darkOverlay.classList.remove('hidden');
    darkOverlay.classList.add('flex');
    storeMenubar.classList.add('-rotate-90');
})

closeBtn.addEventListener('click', () => {
    aside.style.right = '-100%';
    darkOverlay.classList.add('hidden');
    darkOverlay.classList.remove('flex');
    storeMenubar.classList.remove('-rotate-90');
})

darkOverlay.addEventListener('click', () => {
    aside.style.right = '-100%';
    darkOverlay.classList.add('hidden');
    darkOverlay.classList.remove('flex');
    storeMenubar.classList.remove('-rotate-90');
});

// Product Size Select
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('addToBagForm');
    const sizeDropdown = document.getElementById('size');
    const sizeError = document.getElementById('sizeError');

    if (form && sizeDropdown && sizeError) {
        form.addEventListener('submit', function(e) {
            // Check if the submit button pressed was 'addtobag'
            const submitter = e.submitter;
            if (submitter && submitter.name === 'addtobag') {
                e.preventDefault(); // Prevent default form submission
                
                if (sizeDropdown.value === '') {
                    sizeError.classList.remove('hidden');
                    sizeDropdown.classList.add('border-red-500');
                    return; // Exit if no size selected
                }
                
                sizeError.classList.add('hidden');
                sizeDropdown.classList.remove('border-red-500');
                
                // Prepare form data
                const formData = new FormData(form);
                const stockDisplay = document.getElementById('stockDisplay');
                
                // AJAX request
                fetch('../Store/store_details.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {                    
                    if (data.success) {
                        showAlert('Product added to bag successfully!');
                        stockDisplay.textContent = data.stock;
                    } else if (data.outofstock) {
                        showAlert('Product is out of stock', true);
                    } else if (data.login_required) {
                        loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.remove('opacity-0', 'invisible');
                        darkOverlay2.classList.add('opacity-100');

                        const closeLoginModal = document.getElementById('closeLoginModal');
                        closeLoginModal.addEventListener('click', function() {
                            loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                            darkOverlay2.classList.add('opacity-0', 'invisible');
                            darkOverlay2.classList.remove('opacity-100');
                        })
                    } else {
                        showAlert(data.message || 'Failed to add product to bag', true);
                    }
                })
                .catch(error => {
                    if (loader) loader.style.display = 'none';
                    showAlert('An error occurred. Please try again.', true);
                });
            }
        });
    }
});

// Purchase step
const line = document.getElementById('line');
const step = document.getElementById('step');

if (line && step) {
    line.classList.remove('bg-gray-200');
    step.classList.remove('bg-gray-200');
    step.classList.toggle('text-white');
    line.classList.toggle('bg-amber-500');
    step.classList.toggle('bg-amber-500');
}

// Order form validation
document.addEventListener("DOMContentLoaded", () => {
    const paymentForm = document.getElementById("paymentForm");
    const submitButton = document.getElementById("submitButton");
    const buttonText = document.getElementById("buttonText");
    const buttonSpinner = document.getElementById("buttonSpinner");

    // Error message elements
    document.getElementById("firstnameInput").addEventListener("keyup", validateFirstname);
    document.getElementById("lastnameInput").addEventListener("keyup", validateLastname);
    document.getElementById("addressInput").addEventListener("keyup", validateAddress);
    document.getElementById("phoneInput").addEventListener("keyup", validatePhone);
    document.getElementById("cityInput").addEventListener("keyup", validateCity);
    document.getElementById("stateInput").addEventListener("keyup", validateState);
    document.getElementById("zipInput").addEventListener("keyup", validateZip);

    // AJAX form submission
    if (paymentForm) {
        paymentForm.addEventListener("submit", function(e) {
            e.preventDefault();

            if (!validateOrderForm()) {
                return;
            }

            // Disable button and show spinner
            submitButton.disabled = true;
            buttonText.textContent = "Processing...";
            buttonSpinner.classList.remove("hidden");

            const formData = new FormData(paymentForm);
            formData.append("submit_reservation", true);

            fetch("../Store/store_checkout.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Redirect to payment page on success
                    window.location.href = `../Store/stripe.php?reservation_id=${data.order_id}`;
                } else {
                    // Re-enable button and hide spinner
                    submitButton.disabled = false;
                    buttonText.textContent = "Continue to Payment";
                    buttonSpinner.classList.add("hidden");
                    
                    // Show error message
                    showAlert(data.message, true);
                }
            })
            .catch(error => {
                // Re-enable button and hide spinner
                submitButton.disabled = false;
                buttonText.textContent = "Continue to Payment";
                buttonSpinner.classList.add("hidden");
                
                console.log("Error:", error);
                showAlert("An error occurred. Please try again.", true);
            });
        });
    }
});

const validateOrderForm = () => {
    const isFirstnameValid = validateFirstname();
    const isLastnameValid = validateLastname();
    const isAddressValid = validateAddress();
    const isPhoneValid = validatePhone();
    const isCityValid = validateCity();
    const isStateValid = validateState();
    const isZipValid = validateZip();

    return isFirstnameValid && isLastnameValid && isAddressValid && isPhoneValid && isCityValid && isStateValid && isZipValid;
}

// Individual validation functions
const validateFirstname = () => {
    return validateField(
        "firstnameInput",
        "firstnameError",
        (input) => {
            if (!input) {
                return "Firstname is required.";
            }
            if (input.length > 15) {
                return "Firstname is too long.";
            }
            return null;
        }
    );
}

const validateLastname = () => {
    return validateField(
        "lastnameInput",
        "lastnameError",
        (input) => {
            if (!input) {
                return "Lastname is required.";
            }
            if (input.length > 15) {
                return "Lastname is too long.";
            }
            return null;
        }
    );
}

const validateAddress = () => {
    return validateField(
        "addressInput",
        "addressError",
        (input) => {
            if (!input) {
                return "Address is required.";
            }
            if (input.length > 100) {
                return "Address is too long.";
            }
            return null;
        }
    );
}

const validatePhone = () => {
    return validateField(
        "phoneInput",
        "phoneError",
        (input) => {
            if (!input) {
                return "Phone is required.";
            }
            if (!input.match(/^\d+$/)) {
                return "Phone number is invalid. Only digits are allowed.";
            }
            if (input.length < 9 || input.length > 11) {
                return "Phone number must be between 9 and 11 digits.";
            }
            return null;
        }
    );
}

const validateCity = () => {
    return validateField(
        "cityInput",
        "cityError",
        (input) => {
            if (!input) {
                return "City is required.";
            }
            if (input.length > 30) {
                return "City is too long.";
            }
            return null;
        }
    );
}

const validateState = () => {
    return validateField(
        "stateInput",
        "stateError",
        (input) => {
            if (!input) {
                return "State is required.";
            }
            if (input.length > 30) {
                return "State is too long.";
            }
            return null;
        }
    );
}

const validateZip = () => {
    return validateField(
        "zipInput",
        "zipError",
        (input) => {
            if (!input) {
                return "Zip is required.";
            }
            if (input.length > 10) {
                return "Zip is too long.";
            }
            if (!input.match(/^\d+$/)) {
                return "Zip code is invalid. Only digits are allowed.";
            }
            return null;
        }
    );
}
