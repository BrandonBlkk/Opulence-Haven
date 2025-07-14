import { showError, hideError, showAlert } from './alertFunc.js';

// Sign Up
document.addEventListener("DOMContentLoaded", () => {
    // Add keyup event listeners for real-time validation
    const usernameInput = document.getElementById("username");
    const emailInput = document.getElementById("emailInput");
    const passwordInput = document.getElementById("passwordInput");
    const phoneInput = document.getElementById("phone");

    if (usernameInput) usernameInput.addEventListener("keyup", validateUsername);
    if (emailInput) emailInput.addEventListener("keyup", validateEmail);
    if (passwordInput) passwordInput.addEventListener("keyup", validatePassword);
    if (phoneInput) phoneInput.addEventListener("keyup", validatePhone);

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
            
            fetch('../User/user_signup.php', {
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
                    // Successful sign-up
                    if (loader) loader.style.display = 'flex';

                    // Send email
                    fetch('../Mail/send_welcome_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Redirect after email is sent
                            setTimeout(() => {
                                if (loader) loader.style.display = 'none';
                                window.location.href = '../User/home_page.php';
                            }, 1000);
                        } else {
                            // Handle email sending error
                            if (loader) loader.style.display = 'none';
                            showAlert('Account created but failed to send welcome email. Please contact support.' , true);
                            setTimeout(() => {
                                window.location.href = '../User/home_page.php';
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        if (loader) loader.style.display = 'none';
                        showAlert('Account created but failed to send welcome email. Please contact support.', true);
                        setTimeout(() => {
                            window.location.href = '../User/home_page.php';
                        }, 3000);
                    });
                } else {
                    // Show error message
                    showAlert(data.message || 'Sign-up failed. Please try again.', true);
                }
            })
            .catch(error => {
                // Hide loader on error
                if (loader) loader.style.display = 'none';
                showAlert('An error occurred. Please try again.', true);
                console.error('Error:', error);
            });
        });
    }
});

// Sign In
document.addEventListener("DOMContentLoaded", () => {
    // Add keyup event listeners for real-time validation
    document.getElementById("emailInput")?.addEventListener("keyup", validateEmail);
    document.getElementById("passwordInput2")?.addEventListener("keyup", validatePasswordSignIn);

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
            
            fetch('../User/user_signin.php', {
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
                    window.location.href = '../User/home_page.php';
                } else if (data.locked) {
                    // Account locked
                    window.location.href = '../User/waiting_room.php';
                } else {
                    // Show error message
                    showAlert(data.message || 'Sign-in failed. Please try again.', true);
                }
            })
            .catch(error => {
                // Hide loader on error
                if (loader) loader.style.display = 'none';
                showAlert('An error occurred. Please try again.', true);
                console.error('Error:', error);
            });
        });
    }
});

// Reset Password Form Handling
document.addEventListener("DOMContentLoaded", () => {
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const loader = document.getElementById('loader');

    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[name="email"]');
            const email = emailInput.value.trim();
            
            if (!email) {
                showAlert('Please enter your email address', true);
                return;
            }

            if (loader) loader.style.display = 'flex';
            
            const formData = new FormData();
            formData.append('email', email);
            formData.append('reset', 'true');

            fetch('../User/forget_password.php', {
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
                if (loader) loader.style.display = 'none';
                
                if (data.success) {
                    emailInput.value = '';
                    showAlert('Password reset link has been sent to your email. The link will expire in 1 hour.');
                } else {
                    showAlert(data.message || 'Invalid email address. Please try again.', true);
                }
            })
            .catch(error => {
                if (loader) loader.style.display = 'none';
                showAlert('An error occurred. Please try again.', true);
                console.error('Error:', error);
            });
        });
    }
});

// Full form validation function
const validateSignUpForm = () => {
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();
    const isPhoneValid = validatePhone();

    return isUsernameValid && isEmailValid && isPasswordValid && isPhoneValid;
};

const validateSignInForm = () => {
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePasswordSignIn();

    return isEmailValid && isPasswordValid;
};

// Individual validation functions
const validateUsername = () => {
    const username = document.getElementById("username").value.trim();
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
    const email = document.getElementById("emailInput").value.trim();
    const emailError = document.getElementById("emailError");

    const getEmailError = (email) => {
        if (!email) return "Email is required.";
        return null; 
    };

    const errorMessage = getEmailError(email);

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
    const password = document.getElementById("passwordInput").value.trim();
    const passwordError = document.getElementById("passwordError");

    const getPasswordError = (password) => {
        if (!password) return "Password is required.";
        if (password.length < 8) return "Minimum 8 characters.";
        if (!password.match(/\d/)) return "At least one number.";
        if (!password.match(/[A-Z]/)) return "At least one uppercase letter.";
        if (!password.match(/[a-z]/)) return "At least one lowercase letter.";
        if (!password.match(/[^\w\s]/)) return "At least one special character.";
        return null; 
    };

    const errorMessage = getPasswordError(password);

    switch (true) {
        case errorMessage !== null:
            showError(passwordError, errorMessage);
            return false;
        default:
            hideError(passwordError);
            return true;
    }
};

const validatePasswordSignIn = () => {
    const password = document.getElementById("passwordInput2").value.trim();
    const passwordError2 = document.getElementById("passwordError2");

    if (!password) {
        showError(passwordError2, "Password is required.");
        return false;
    } else {
        hideError(passwordError2);
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
    { input: document.getElementById('passwordInput'), toggle: document.getElementById('togglePassword') },
    { input: document.getElementById('passwordInput2'), toggle: document.getElementById('togglePassword2') },
    { input: document.getElementById('resetpasswordInput'), toggle: document.getElementById('resettogglePassword') },
    { input: document.getElementById('newpasswordInput'), toggle: document.getElementById('newtogglePassword') },
    { input: document.getElementById('confirmpasswordInput'), toggle: document.getElementById('confirmtogglePassword') },
    { input: document.getElementById('new_password'), toggle: document.getElementById('confirmtogglePassword3') },
    { input: document.getElementById('confirm_password'), toggle: document.getElementById('confirmtogglePassword4') }
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



