// Move Right Loader
let moveRight = document.getElementById("move-right");

window.addEventListener('scroll', () => {
    let scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
    let scrollPercentage = (window.scrollY / scrollableHeight) * 100; 

    if (scrollPercentage >= 100) {
        moveRight.style.width = '100%';
    } else {
        moveRight.style.width = scrollPercentage + '%';
    }
});

// Cookie Modal
const cookieModal = document.getElementById('cookieModal');
if (cookieModal) {
    const acceptBtn = document.getElementById('acceptBtn');
    const declineBtn = document.getElementById('declineBtn');

    // Check if cookie consent is already given
    if (!localStorage.getItem('cookieConsent')) {
        setTimeout(() => {
            cookieModal.classList.add('bottom-0');
            cookieModal.classList.remove('-bottom-full');
        }, 1000);
    }

    // Accept Button
    acceptBtn.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'true');
        cookieModal.classList.add('-bottom-full');
        cookieModal.classList.remove('bottom-0');
    });

    // Decline Button
    declineBtn.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'false');
        cookieModal.classList.add('-bottom-full');
        cookieModal.classList.remove('bottom-0');
    });
}

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

// Menu Bar
const menubar = document.getElementById('menubar');
if (menubar) {
    const closeBtn = document.getElementById('closeBtn');
    const aside = document.getElementById('aside');
    const darkOverlay = document.getElementById('darkOverlay');

    menubar.addEventListener('click', () => {
        aside.style.right = '0%';
        darkOverlay.classList.remove('hidden');
        darkOverlay.classList.add('flex');
        menubar.classList.add('-rotate-90');
    });

    closeBtn.addEventListener('click', () => {
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        menubar.classList.remove('-rotate-90');
    });

    darkOverlay.addEventListener('click', () => {
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        menubar.classList.remove('-rotate-90');
    });
}

// MoveUp Btn
const moveUpBtn = document.getElementById('moveUpBtn');
if (moveUpBtn) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 1000) {
            moveUpBtn.classList.remove('-right-full');
            moveUpBtn.classList.add('right-0');
        } else {
            moveUpBtn.classList.remove('right-0');
            moveUpBtn.classList.add('-right-full');
        }
    });
}

// Logout Modal
const logoutBtn = document.getElementById('logoutBtn');
const confirmModal = document.getElementById('confirmModal');
const cancelBtn = document.getElementById('cancelBtn');
const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
const darkOverlay = document.getElementById('darkOverlay');
const darkOverlay2 = document.getElementById('darkOverlay2');

if (logoutBtn && confirmModal && cancelBtn && confirmLogoutBtn && darkOverlay2) {
    // Show Modal
    logoutBtn.addEventListener('click', () => {
        confirmModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        confirmModal.classList.add('opacity-100', 'translate-y-0');
        darkOverlay2.classList.remove('opacity-0', 'invisible');
        darkOverlay2.classList.add('opacity-100');
    });

    // Close Modal on Cancel
    cancelBtn.addEventListener('click', () => {
        confirmModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        confirmModal.classList.remove('opacity-100', 'translate-y-0');
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        aside.style.right = '-100%';
        menubar.classList.remove('-rotate-90');
    });

    // Handle Logout Action
    confirmLogoutBtn.addEventListener('click', () => {
        confirmModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        confirmModal.classList.remove('opacity-100', 'translate-y-0');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        aside.style.right = '-100%';
        menubar.classList.remove('-rotate-90');
        loader.style.display = 'flex';
    
        // Notify the server to destroy the session
        fetch('UserLogout.php', { method: 'POST' })
            .then(() => {
                // Redirect after logout
                setTimeout(() => {
                    window.location.href = 'HomePage.php';
                }, 1000);
            })
            .catch((error) => console.error('Logout failed:', error));
    });
}

// Profile Delete Modal
const profileDeleteBtn = document.getElementById("profileDeleteBtn");
const confirmDeleteModal = document.getElementById("confirmDeleteModal");
const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
const deleteConfirmInput = document.getElementById("deleteConfirmInput");

if (profileDeleteBtn && confirmDeleteModal && cancelDeleteBtn && confirmDeleteBtn && deleteConfirmInput) {
    // Show Delete Modal
    profileDeleteBtn.addEventListener("click", () => {
        confirmDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
        confirmDeleteModal.classList.add("opacity-100", "translate-y-0");
        darkOverlay2.classList.remove("opacity-0", "invisible");
        darkOverlay2.classList.add("opacity-100");
    });

    // Close Delete Modal on Cancel
    cancelDeleteBtn.addEventListener("click", () => {
        confirmDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
        confirmDeleteModal.classList.remove("opacity-100", "translate-y-0");
        darkOverlay2.classList.add("opacity-0", "invisible");
        darkOverlay2.classList.remove("opacity-100");
        deleteConfirmInput.value = "";
    });

    // Enable Delete Button only if input matches "DELETE"
    deleteConfirmInput.addEventListener("input", () => {
        const isMatch = deleteConfirmInput.value.trim() === "DELETE";
        confirmDeleteBtn.disabled = !isMatch;

        // Toggle the 'cursor-not-allowed' class
        if (isMatch) {
            confirmDeleteBtn.classList.remove("cursor-not-allowed");
        } else {
            confirmDeleteBtn.classList.add("cursor-not-allowed");
        }
    });

    // Handle Delete Action
    confirmDeleteBtn.addEventListener("click", () => {                 
        confirmDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
        confirmDeleteModal.classList.remove("opacity-100", "translate-y-0");
        darkOverlay2.classList.add("opacity-0", "invisible");
        darkOverlay2.classList.remove("opacity-100");
        loader.style.display = "flex";

        // Notify the server to delete the account
        fetch("UserAccountDelete.php", {
            method: "POST",
        })
            .then(() => {
                // Redirect after account deletion
                setTimeout(() => {
                    window.location.href = "HomePage.php";
                }, 1000);
            })
            .catch((error) => console.error("Account deletion failed:", error));
    });
}

// Reset Password and Profile Update Form Validation
document.addEventListener("DOMContentLoaded", () => {
    const alertBox = document.getElementById('alertBox');
    const alertText = document.getElementById('alertText');
    const alertMessage = document.getElementById('alertMessage').value;
    const profileUpdate = document.getElementById('profileUpdate').value === 'true';
    const resetSuccess = document.getElementById('resetSuccess').value === 'true';

    if (resetSuccess) {
        // Show Alert
        alertText.textContent = 'You have successfully changed a password.';
        alertBox.classList.remove('-bottom-5');
        alertBox.classList.remove('opacity-0');
        alertBox.classList.add('opacity-100');
        alertBox.classList.add('bottom-3');

        // Hide Alert
        setTimeout(() => {
            alertBox.classList.add('-bottom-1');
            alertBox.classList.add('opacity-0');
            alertBox.classList.remove('opacity-100');
            alertBox.classList.remove('bottom-3');
            // window.location.href = 'ProfileEdit.php';
        }, 5000);
    } else if (profileUpdate)  {
        // Show Alert
        alertText.textContent = 'You have successfully changed a profile.';
        alertBox.classList.remove('-bottom-5');
        alertBox.classList.remove('opacity-0');
        alertBox.classList.add('opacity-100');
        alertBox.classList.add('bottom-3');

        // Hide Alert
        setTimeout(() => {
            alertBox.classList.add('-bottom-1');
            alertBox.classList.add('opacity-0');
            alertBox.classList.remove('opacity-100');
            alertBox.classList.remove('bottom-3');
            // window.location.href = 'ProfileEdit.php';
        }, 5000);
    }
    else if (alertMessage) {
        // Show Alert
        alertText.textContent = alertMessage;
        alertBox.classList.remove('-bottom-1');
        alertBox.classList.remove('opacity-0');
        alertBox.classList.add('opacity-100');
        alertBox.classList.add('bottom-3');

        // Hide Alert
        setTimeout(() => {
            alertBox.classList.add('-bottom-1');
            alertBox.classList.add('opacity-0');
            alertBox.classList.remove('opacity-100');
            alertBox.classList.remove('bottom-3');
        }, 5000);
    }

    document.getElementById("usernameInput").addEventListener("keyup", validateUsername);
    document.getElementById("emailInput").addEventListener("keyup", validateEmail);
    document.getElementById("phoneInput").addEventListener("keyup", validatePhone);

    document.getElementById("resetpasswordInput").addEventListener("keyup", validateResetPassword);
    document.getElementById("newpasswordInput").addEventListener("keyup", validateNewPassword);
    document.getElementById("confirmpasswordInput").addEventListener("keyup", validateConfirmPassword);

    // Add submit event listener for form validation
    const updateProfileForm = document.getElementById("updateProfileForm");
    if (updateProfileForm) {
        updateProfileForm.addEventListener("submit", (e) => {
            if (!validateProfileUpdateForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Add submit event listener for form validation
    const resetPasswordForm = document.getElementById("resetPasswordForm");
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener("submit", (e) => {
            if (!validateResetForm()) {
                e.preventDefault();
            }
        });
    }
});

// Full form validation function
const validateProfileUpdateForm = () => {
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPhoneValid = validatePhone();

    return isUsernameValid && isEmailValid && isPhoneValid;
};

const validateResetForm = () => {
    const isResetPasswordValid = validateResetPassword();
    const isNewPasswordValid = validateNewPassword();
    const isConfirmPasswordValid = validateConfirmPassword();


    return isResetPasswordValid && isNewPasswordValid && isConfirmPasswordValid;
};

// Individual validation functions
const validateUsername = () => {
    const usernameInput = document.getElementById("usernameInput").value.trim();
    const usernameError = document.getElementById("usernameError");

    const getUserNameError = (username) => {
        if (!usernameInput) return "Username is required.";
        if (usernameInput.length > 14) return "Username should not exceed 14 characters.";
        return null; 
    };

    const errorMessage = getUserNameError(usernameInput);

    switch (true) {
        case errorMessage !== null:
            usernameError.textContent = errorMessage;
            usernameError.classList.remove("opacity-0");
            usernameError.classList.add("opacity-100");
            return false;
        default:
            usernameError.classList.remove("opacity-100");
            usernameError.classList.add("opacity-0");
            return true;
    }
};

const validateEmail = () => {
    const emailInput = document.getElementById("emailInput").value.trim();
    const emailError = document.getElementById("emailError");

    const getEmailError = (emailInput) => {
        if (!emailInput) return "Email is required.";
        return null; 
    };

    const errorMessage = getEmailError(emailInput);

    switch (true) {
        case errorMessage !== null:
            emailError.textContent = errorMessage;
            emailError.classList.remove("opacity-0");
            emailError.classList.add("opacity-100");
            return false;
        default:
            emailError.classList.remove("opacity-100");
            emailError.classList.add("opacity-0");
            return true;
    }
};

const validatePhone = () => {
    const phoneInput = document.getElementById("phoneInput").value.trim();
    const phoneError = document.getElementById("phoneError");

    const getPhoneError = (phoneInput) => {
        if (!phoneInput) return "Phone is required.";
        if (!phoneInput.match(/^\d+$/)) return "Phone number is invalid. Only digits are allowed.";
        if (phoneInput.length < 8 || phoneInput.length > 11) return "Phone number must be between 8 and 11 digits.";
        return null; 
    };

    const errorMessage = getPhoneError(phoneInput);

    switch (true) {
        case errorMessage !== null:
            phoneError.textContent = errorMessage;
            phoneError.classList.remove("opacity-0");
            phoneError.classList.add("opacity-100");
            return false;
        default:
            phoneError.classList.remove("opacity-100");
            phoneError.classList.add("opacity-0");
            return true;
    }
};

const validateResetPassword = () => {
    const resetpassword = document.getElementById("resetpasswordInput").value.trim();
    const resetpasswordError = document.getElementById("resetpasswordError");

    const getPasswordError = (resetpassword) => {
        if (!resetpassword) return "Password is required.";
        return null; 
    };

    const errorMessage = getPasswordError(resetpassword);

    switch (true) {
        case errorMessage !== null:
            resetpasswordError.textContent = errorMessage;
            resetpasswordError.classList.remove("opacity-0");
            resetpasswordError.classList.add("opacity-100");
            return false;
        default:
            resetpasswordError.classList.remove("opacity-100");
            resetpasswordError.classList.add("opacity-0");
            return true;
    }
};
const validateNewPassword = () => {
    const newpassword = document.getElementById("newpasswordInput").value.trim();
    const newpasswordError = document.getElementById("newpasswordError");

    const getPasswordError = (newpassword) => {
        if (!newpassword) return "New Password is required.";
        if (newpassword.length < 8) return "Minimum 8 characters.";
        if (!newpassword.match(/\d/)) return "At least one number.";
        if (!newpassword.match(/[A-Z]/)) return "At least one uppercase letter.";
        if (!newpassword.match(/[a-z]/)) return "At least one lowercase letter.";
        if (!newpassword.match(/[^\w\s]/)) return "At least one special character.";
        return null; 
    };

    const errorMessage = getPasswordError(newpassword);

    switch (true) {
        case errorMessage !== null:
            newpasswordError.textContent = errorMessage;
            newpasswordError.classList.remove("opacity-0");
            newpasswordError.classList.add("opacity-100");
            return false;
        default:
            newpasswordError.classList.remove("opacity-100");
            newpasswordError.classList.add("opacity-0");
            return true;
    }
};
const validateConfirmPassword = () => {
    const confirmpassword = document.getElementById("confirmpasswordInput").value.trim();
    const confirmpasswordError = document.getElementById("confirmpasswordError");

    const getPasswordError = (confirmpassword) => {
        if (!confirmpassword) return "Confirm Password is required.";
        return null; 
    };

    const errorMessage = getPasswordError(confirmpassword);

    switch (true) {
        case errorMessage !== null:
            confirmpasswordError.textContent = errorMessage;
            confirmpasswordError.classList.remove("opacity-0");
            confirmpasswordError.classList.add("opacity-100");
            return false;
        default:
            confirmpasswordError.classList.remove("opacity-100");
            confirmpasswordError.classList.add("opacity-0");
            return true;
    }
};

// Toggle Password for Grouped Inputs
const passwordInputs = [
    { input: document.getElementById('resetpasswordInput'), toggle: document.getElementById('resettogglePassword') },
    { input: document.getElementById('newpasswordInput'), toggle: document.getElementById('newtogglePassword') },
    { input: document.getElementById('confirmpasswordInput'), toggle: document.getElementById('confirmtogglePassword') }
];
 
passwordInputs.forEach(({ input, toggle }) => {
    if (input && toggle) {
        toggle.addEventListener('click', () => {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;

            // Toggle the icon
            toggle.classList.toggle('ri-eye-line');
            toggle.classList.toggle('ri-eye-off-line');
        });
    }
});

// Get Year
const getDate = new Date();
const getYear = getDate.getFullYear();

document.getElementById('year').textContent = getYear;
