// Sign Up
document.addEventListener("DOMContentLoaded", () => {
    const alertBox = document.getElementById('alertBox');
    const alertText = document.getElementById('alertText');
    const loader = document.getElementById('loader');
    const emailExists = document.getElementById('emailExists').value;
    const signupSuccess = document.getElementById('signupSuccess').value === 'true';

    if (signupSuccess) {
        loader.style.display = 'flex';

        setTimeout(() => {
            loader.style.display = 'none';
            alertText.textContent = 'You have successfully created an account.';
            alertBox.classList.remove('-bottom-full');
            alertBox.classList.add('bottom-3');

            setTimeout(() => {
                alertBox.classList.add('-bottom-full');
                alertBox.classList.remove('bottom-3');
            }, 5000);
        }, 1000);
    } else if (emailExists) {
        alertText.textContent = emailExists;
        alertBox.classList.remove('-bottom-full');
        alertBox.classList.add('bottom-3');

        setTimeout(() => {
            alertBox.classList.add('-bottom-full');
            alertBox.classList.remove('bottom-3');
        }, 5000);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("username").addEventListener("keyup", validateUsername);
    document.getElementById("email").addEventListener("keyup", validateEmail);
    document.getElementById("passwordInput").addEventListener("keyup", validatePassword());
    document.getElementById("phone").addEventListener("keyup", validatePhone);

    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        signupForm.addEventListener("submit", (e) => {
            if (!validateSignUpForm()) {
                e.preventDefault();
            }
        });
    }
});

// Sign In
document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById('loader');
    const signInSuccess = document.getElementById('signinSuccess').value === 'true';

    if (signInSuccess) {
        loader.style.display = 'flex'; 

        setTimeout(() => {
            loader.style.display = 'none'; 
            window.location.href = 'UserHome.php';
        }, 1000); 
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("email").addEventListener("keyup", validateEmail);
    document.getElementById("passwordInput").addEventListener("keyup", validatePassword);

    const signinForm = document.getElementById("signinForm");
    if (signinForm) {
        signinForm.addEventListener("submit", (e) => {
            if (!validateSignInForm()) {
                e.preventDefault();
            }
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
    const isPasswordValid = validatePassword();

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
    const email = document.getElementById("email").value.trim();
    const emailError = document.getElementById("emailError");

    const getEmailError = (email) => {
        if (!email) return "Email is required.";
        return null; 
    };

    const errorMessage = getEmailError(email);

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
            passwordError.textContent = errorMessage;
            passwordError.classList.remove("opacity-0");
            passwordError.classList.add("opacity-100");
            return false;
        default:
            passwordError.classList.remove("opacity-100");
            passwordError.classList.add("opacity-0");
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

// Get the input and icon elements
const passwordInput = document.getElementById('passwordInput');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', () => {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;

    // Toggle the icon 
    togglePassword.classList.toggle('ri-eye-line');
    togglePassword.classList.toggle('ri-eye-off-line');
});