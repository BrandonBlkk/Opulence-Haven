import { showError, hideError, showAlert } from './alertFunc.js';

// Sign Up
document.addEventListener("DOMContentLoaded", () => {
    // Add keyup event listeners for real-time validation
    document.getElementById("firstnameInput").addEventListener("keyup", validateFirstname);
    document.getElementById("lastnameInput").addEventListener("keyup", validateLastname);
    document.getElementById("usernameInput").addEventListener("keyup", validateUsername);
    document.getElementById("emailInput").addEventListener("keyup", validateEmail);
    document.getElementById("signupPasswordInput").addEventListener("keyup", validatePassword);
    document.getElementById("phone").addEventListener("keyup", validatePhone);

    const signupForm = document.getElementById("signupForm");
    const loader = document.getElementById('loader');

    if (signupForm) {
        signupForm.addEventListener("submit", function(e) {
            e.preventDefault();
                
            if (!validateSignUpForm()) {
                return;
            }

            const formData = new FormData(this);
            
            // Show loader
            if (loader) loader.style.display = 'flex';
            
            fetch('../Admin/AdminSignUp.php', {
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
                // Hide loader
                if (loader) loader.style.display = 'none';
                
                if (data.success) {
                    // Successful sign-in
                    if (loader) loader.style.display = 'flex';
                    window.location.href = '../Admin/AdminDashboard.php';
                } else {
                    // Show error message
                    showAlert(data.message || 'Sign-up failed. Please try again.', 'error');
                }
            })
            .catch(error => {
                // Hide loader on error
                if (loader) loader.style.display = 'none';
                showAlert('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            });
        });
    }
});

// Sign In
document.addEventListener("DOMContentLoaded", () => {
    // Add keyup event listeners for real-time validation
    document.getElementById("emailInput").addEventListener("keyup", validateEmail);
    document.getElementById("signinPasswordInput").addEventListener("keyup", validatePasswordSignIn);

    const signinForm = document.getElementById("signinForm");
    const loader = document.getElementById('loader');
    
    if (signinForm) {
        signinForm.addEventListener("submit", function(e) {
            e.preventDefault();
                
            if (!validateSignInForm()) {
                return;
            }

            const formData = new FormData(this);
            
            // Show loader
            if (loader) loader.style.display = 'flex';
            
            fetch('../Admin/AdminSignIn.php', {
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
                // Hide loader
                if (loader) loader.style.display = 'none';
                
                if (data.success) {
                    // Successful sign-in
                    if (loader) loader.style.display = 'flex';
                    window.location.href = '../Admin/AdminDashboard.php';
                } else if (data.locked) {
                    // Account locked
                    window.location.href = '../User/WaitingRoom.php';
                } else {
                    // Show error message
                    showAlert(data.message || 'Sign-in failed. Please try again.', 'error');
                }
            })
            .catch(error => {
                // Hide loader on error
                if (loader) loader.style.display = 'none';
                showAlert('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            });
        });
    }
});

// Full form validation function
const validateSignUpForm = () => {
    const isFirstnameValid = validateFirstname();
    const isLastnameValid = validateLastname();
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();
    const isPhoneValid = validatePhone();

    return isFirstnameValid && isLastnameValid && isUsernameValid && isEmailValid && isPasswordValid && isPhoneValid;
};

const validateSignInForm = () => {
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePasswordSignIn();

    return isEmailValid && isPasswordValid;
};

// Individual validation functions
const validateFirstname = () => {
    const firstname = document.getElementById("firstnameInput").value.trim();
    const firstnameError = document.getElementById("firstnameError");

    const getUserNameError = (firstname) => {
        if (!firstname) return "Firstname is required.";
        if (firstname.length > 14) return "Username should not exceed 14 characters.";
        return null; 
    };

    const errorMessage = getUserNameError(firstname);

    switch (true) {
        case errorMessage !== null:
            showError(firstnameError, errorMessage);
            return false;
        default:
            hideError(firstnameError);
            return true;
    }
};

const validateLastname = () => {
    const lastname = document.getElementById("lastnameInput").value.trim();
    const lastnameError = document.getElementById("lastnameError");

    const getUserNameError = (lastname) => {
        if (!lastname) return "Lastname is required.";
        if (lastname.length > 14) return "Lastname should not exceed 14 characters.";
        return null; 
    };

    const errorMessage = getUserNameError(lastname);

    switch (true) {
        case errorMessage !== null:
            showError(lastnameError, errorMessage);
            return false;
        default:
            hideError(lastnameError);
            return true;
    }
};
const validateUsername = () => {
    const username = document.getElementById("usernameInput").value.trim();
    const usernameError = document.getElementById("usernameError");

    const getUserNameError = (username) => {
        if (!username) return "Username is required.";
        if (username.length > 14) return "Username should not exceed 14 characters.";
        return null; 
    };

    const errorMessage = getUserNameError(username);

    switch (true) {
        case errorMessage !== null:
            showError(usernameError, errorMessage);
            return false;
        default:
            hideError(usernameError);
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
            showError(emailError, errorMessage);
            return false;
        default:
            hideError(emailError);
            return true;
    }
};

const validatePassword = () => {
    const signupPasswordInput = document.getElementById("signupPasswordInput").value.trim();
    const signupPasswordError = document.getElementById("signupPasswordError");

    const getPasswordError = (signupPasswordInput) => {
        if (!signupPasswordInput) return "Password is required.";
        if (signupPasswordInput.length < 8) return "Minimum 8 characters.";
        if (!signupPasswordInput.match(/\d/)) return "At least one number.";
        if (!signupPasswordInput.match(/[A-Z]/)) return "At least one uppercase letter.";
        if (!signupPasswordInput.match(/[a-z]/)) return "At least one lowercase letter.";
        if (!signupPasswordInput.match(/[^\w\s]/)) return "At least one special character.";
        return null; 
    };

    const errorMessage = getPasswordError(signupPasswordInput);

    switch (true) {
        case errorMessage !== null:
            showError(signupPasswordError, errorMessage);
            return false;
        default:
            hideError(signupPasswordError);
            return true;
    }
};

const validatePasswordSignIn = () => {
    const signinPasswordInput = document.getElementById("signinPasswordInput").value.trim();
    const signinPasswordError = document.getElementById("signinPasswordError");

    if (!signinPasswordInput) {
        showError(signinPasswordError, "Password is required.");
        return false;
    } else {
        hideError(signinPasswordError);
        return true;
    }
};

const validatePhone = () => {
    const phone = document.getElementById("phone").value.trim();
    const phoneError = document.getElementById("phoneError");

    const getPhoneError = (phone) => {
        if (!phone) return "Phone is required.";
        if (!phone.match(/^\d+$/)) return "Phone number is invalid. Only digits are allowed.";
        if (phone.length < 8 || phone.length > 11) return "Phone number must be between 8 and 11 digits.";
        return null; 
    };

    const errorMessage = getPhoneError(phone);

    switch (true) {
        case errorMessage !== null:
            showError(phoneError, errorMessage);
            return false;
        default:
            hideError(phoneError);
            return true;
    }
};

// Toggle Password for Grouped Inputs
const passwordInputs = [
    { input: document.getElementById('signupPasswordInput'), toggle: document.getElementById('togglePassword') },
    { input: document.getElementById('signinPasswordInput'), toggle: document.getElementById('togglePassword2') },
    { input: document.getElementById('resetPasswordInput'), toggle: document.getElementById('toggleResetPassword') },
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



