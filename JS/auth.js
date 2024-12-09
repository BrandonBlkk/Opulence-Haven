// const loader = document.getElementById('loader');

// // Function to handle form submission
// const handleFormSubmit = (form) => {
//     form.addEventListener('submit', (e) => {
//         e.preventDefault();
//         loader.style.display = 'flex';

//         setTimeout(() => {
//             if (form.id === 'signinForm') {
//                 window.location.href = 'HomePage.php';
//             } else {
//                 form.submit();
//             }
//         }, 1000);
//     });
// };

// const forms = ['signinForm', 'signupForm'];

// forms.forEach((formId) => {
//     const form = document.getElementById(formId);
//     if (form) {
//         handleFormSubmit(form);
//     }
// });


// document.addEventListener("DOMContentLoaded", () => {
//     // Add keyup event listeners for real-time validation
//     document.getElementById("username").addEventListener("keyup", validateUsername);
//     document.getElementById("email").addEventListener("keyup", validateEmail);
//     document.getElementById("passwordInput").addEventListener("keyup", validatePassword);
//     document.getElementById("phone").addEventListener("keyup", validatePhone);
//     const loader = document.getElementById('loader');
//     const signupForm = document.getElementById("signupForm");

//     if (signupForm) {
//         // Add submit event listener for final validation
//         signupForm.addEventListener("submit", (e) => {
//             e.preventDefault(); 
//             if (validateForm()) {
//                 // Show loader if form is valid
//                 loader.style.display = 'flex';

//                 // Submit form data after a loader
//                 setTimeout(() => {
//                     signupForm.submit(); 
//                 }, 2000);
//             } else {
//                 loader.style.display = 'none'; 
//             }
//         });
//     }
// });

// // Full form validation function
// const validateForm = () => {
//     const isUsernameValid = validateUsername();
//     const isEmailValid = validateEmail();
//     const isPasswordValid = validatePassword();
//     const isPhoneValid = validatePhone();

//     return isUsernameValid && isEmailValid && isPasswordValid && isPhoneValid;
// }

// // Individual validation functions
// const validateUsername = () => {
//     const username = document.getElementById("username").value.trim();
//     const usernameError = document.getElementById("usernameError");

//     if (!username) {
//         usernameError.textContent = "Username is required.";
//         usernameError.classList.remove("opacity-0");
//         usernameError.classList.add("opacity-100");
//         return false;
//     } else if (username.length > 14) {
//         usernameError.textContent = "Username should not exceed 14 characters.";
//         usernameError.classList.remove("opacity-0");
//         usernameError.classList.add("opacity-100");
//         return false;
//     } else {
//         usernameError.classList.remove("opacity-100");
//         usernameError.classList.add("opacity-0");
//         return true;
//     }
// };

// const validateEmail = () => {
//     const email = document.getElementById("email").value.trim();
//     const emailError = document.getElementById("emailError");

//     if (!email) {
//         emailError.textContent = "Email is required.";
//         emailError.classList.remove("opacity-0");
//         emailError.classList.add("opacity-100");
//         return false;
//     } else {
//         emailError.classList.remove("opacity-100");
//         emailError.classList.add("opacity-0");
//         return true;
//     }
// };

// const validatePassword = () => {
//     const password = document.getElementById("passwordInput").value.trim();
//     const passwordError = document.getElementById("passwordError");

//     // Check if password is empty
//     if (!password) {
//         passwordError.textContent = "Password is required.";
//         passwordError.classList.remove("opacity-0");
//         passwordError.classList.add("opacity-100");
//         return false;
//     }
//     // Check if password has at least 8 characters
//     else if (password.length < 8) {
//         passwordError.textContent = "Minimum 8 characters.";
//         passwordError.classList.remove("opacity-0");
//         passwordError.classList.add("opacity-100");
//         return false;
//     }
//     // Check if password contains at least one number
//     else if (!password.match(/\d/)) {
//         passwordError.textContent = "At least one number.";
//         passwordError.classList.remove("opacity-0");
//         passwordError.classList.add("opacity-100");
//         return false;
//     }
//     // Check if password contains at least one uppercase letter
//     else if (!password.match(/[A-Z]/)) {
//         passwordError.textContent = "At least one uppercase letter.";
//         passwordError.classList.remove("opacity-0");
//         passwordError.classList.add("opacity-100");
//         return false;
//     }
//     // Check if password contains at least one lowercase letter
//     else if (!password.match(/[a-z]/)) {
//         passwordError.textContent = "At least one lowercase letter.";
//         passwordError.classList.remove("opacity-0");
//         passwordError.classList.add("opacity-100");
//         return false;
//     }
//     // Check if password contains at least one special character
//     else if (!password.match(/[^\w\s]/)) {
//         passwordError.textContent = "At least one special character.";
//         passwordError.classList.remove("opacity-0");
//         passwordError.classList.add("opacity-100");
//         return false;
//     }
//     // If all conditions are met, validation passes
//     else {
//         passwordError.classList.remove("opacity-100");
//         passwordError.classList.add("opacity-0");
//         return true;
//     }
// };

// const validatePhone = () => {
//     const phone = document.getElementById("phone").value.trim();
//     const phoneError = document.getElementById("phoneError");

//     // Check if the phone number is empty
//     if (!phone) {
//         phoneError.textContent = "Phone is required.";
//         phoneError.classList.remove("opacity-0");
//         phoneError.classList.add("opacity-100");
//         return false;
//     }
//     // Check if the phone number contains only digits
//     else if (!phone.match(/^\d+$/)) {
//         phoneError.textContent = "Phone number is invalid. Only digits are allowed.";
//         phoneError.classList.remove("opacity-0");
//         phoneError.classList.add("opacity-100");
//         return false;
//     }
//     // Check if the phone number length is between 8 and 11 digits
//     else if (phone.length < 8 || phone.length > 11) {
//         phoneError.textContent = "Phone number must be between 8 and 11 digits.";
//         phoneError.classList.remove("opacity-0");
//         phoneError.classList.add("opacity-100");
//         return false;
//     }
//     // If valid
//     else {
//         phoneError.classList.remove("opacity-100");
//         phoneError.classList.add("opacity-0");
//         return true;
//     }
// };

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
    document.getElementById("passwordInput").addEventListener("keyup", validatePassword);
    document.getElementById("phone").addEventListener("keyup", validatePhone);

    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        signupForm.addEventListener("submit", (e) => {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }
});

// Full form validation function
const validateForm = () => {
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();
    const isPhoneValid = validatePhone();

    return isUsernameValid && isEmailValid && isPasswordValid && isPhoneValid;
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