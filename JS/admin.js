import { showError, hideError, showAlert } from './alertFunc.js';

const menu_toggle = document.getElementById('menu-toggle');
if (menu_toggle) {
    menu_toggle.addEventListener('click', () => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
        }
    });
}

const overlay = document.getElementById('overlay');
if (overlay) {
    overlay.addEventListener('click', () => {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        overlay.classList.add('hidden');
    });
}

// Logout Modal
const logoutBtn = document.getElementById('logoutBtn');
const confirmModal = document.getElementById('confirmModal');
const cancelBtn = document.getElementById('cancelBtn');
const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
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
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        menubar.classList.remove('-rotate-90');
    });

    // Handle Logout Action
    confirmLogoutBtn.addEventListener('click', () => {
        confirmModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        confirmModal.classList.remove('opacity-100', 'translate-y-0');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        menubar.classList.remove('-rotate-90');
        loader.style.display = 'flex';
    
        // Notify the server to destroy the session
        fetch('AdminLogout.php', { method: 'POST' })
            .then(() => {
                // Redirect after logout
                setTimeout(() => {
                    window.location.href = 'AdminDashboard.php';
                }, 1000);
            })
            .catch((error) => console.error('Logout failed:', error));
    });
}

// Add Product Type Form
document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addProductTypeSuccess = document.getElementById('addProductTypeSuccess').value === 'true';

    if (addProductTypeSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('You have successfully added the product type.');
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("productTypeInput").addEventListener("keyup", validateProductType);
    document.getElementById("descriptionInput").addEventListener("keyup", validateDescription);

    const productTypeForm = document.getElementById("productTypeForm");
    if (productTypeForm) {
        productTypeForm.addEventListener("submit", (e) => {
            if (!validateProductTypeForm()) {
                e.preventDefault();
            }
        });
    }
});

// Add Role Form
document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addRoleSuccess = document.getElementById('addRoleSuccess').value === 'true';

    if (addRoleSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('You have successfully added the role.');
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("roleInput").addEventListener("keyup", validateRole);
    document.getElementById("roleDescriptionInput").addEventListener("keyup", validateRoleDescription);

    const roleForm = document.getElementById("roleForm");
    if (roleForm) {
        roleForm.addEventListener("submit", (e) => {
            if (!validateRoleForm()) {
                e.preventDefault();
            }
        });
    }
});

// Add Supplier Form
document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addSupplierSuccess = document.getElementById('addSupplierSuccess').value === 'true';

    if (addSupplierSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('You have successfully added the supplier.');
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("supplierNameInput").addEventListener("keyup", validateSupplierName);
    document.getElementById("companyNameInput").addEventListener("keyup", validateCompanyName);
    document.getElementById("emailInput").addEventListener("keyup", validateEmail);
    document.getElementById("contactNumberInput").addEventListener("keyup", validateContactNumber);
    document.getElementById("addressInput").addEventListener("keyup", validateAddress);
    document.getElementById("cityInput").addEventListener("keyup", validateCity);
    document.getElementById("stateInput").addEventListener("keyup", validateState);
    document.getElementById("postalCodeInput").addEventListener("keyup", validatePostalCode);
    document.getElementById("countryInput").addEventListener("keyup", validateCountry);

    const supplierForm = document.getElementById("supplierForm");
    if (supplierForm) {
        supplierForm.addEventListener("submit", (e) => {
            if (!validateSupplierForm()) {
                e.preventDefault();
            }
        });
    }
});

// Full form validation function
const validateProductTypeForm = () => {
    const isTypeValid = validateProductType();
    const isDescriptionValid = validateDescription();

    return isTypeValid && isDescriptionValid;
};

const validateSupplierForm = () => {
    const isSupplierNameValid = validateSupplierName();
    const isCompanyNameValid = validateCompanyName();
    const isEmailValid = validateEmail();
    const isContactNumberValid = validateContactNumber();
    const isAddressValid = validateAddress();
    const isCityValid = validateCity();
    const isStateValid = validateState();
    const isPostalCodeValid = validatePostalCode();
    const isCountryValid = validateCountry();

    return isSupplierNameValid && isCompanyNameValid && isEmailValid && isContactNumberValid 
    && isAddressValid && isCityValid && isStateValid && isPostalCodeValid && isCountryValid;
};

const validateRoleForm = () => {
    const isRoleValid = validateRole();
    const isRoleDescriptionValid = validateRoleDescription();

    return isRoleValid && isRoleDescriptionValid;
}

// Individual validation functions
const validateProductType = () => {
    const productTypeInput = document.getElementById("productTypeInput").value.trim();
    const productTypeError = document.getElementById("productTypeError");

    const getUserNameError = (productTypeInput) => {
        if (!productTypeInput) return "Type is required.";
        return null;
    };

    const errorMessage = getUserNameError(productTypeInput);

    switch (true) {
        case errorMessage !== null:
            showError(productTypeError, errorMessage);
            return false;
        default:
            hideError(productTypeError);
            return true;
    }
};

const validateDescription = () => {
    const descriptionInput = document.getElementById("descriptionInput").value.trim();
    const descriptionError = document.getElementById("descriptionError");

    const getEmailError = (descriptionInput) => {
        if (!descriptionInput) return "Description is required.";
        return null;
    };

    const errorMessage = getEmailError(descriptionInput);

    switch (true) {
        case errorMessage !== null:
            showError(descriptionError, errorMessage);
            return false;
        default:
            hideError(descriptionError);
            return true;
    }
};

const validateSupplierName = () => {
    const supplierNameInput = document.getElementById("supplierNameInput").value.trim();
    const supplierNameError = document.getElementById("supplierNameError");

    const getUserNameError = (supplierNameInput) => {
        if (!supplierNameInput) return "Supplier name is required.";
        return null;
    };

    const errorMessage = getUserNameError(supplierNameInput);

    switch (true) {
        case errorMessage !== null:
            showError(supplierNameError, errorMessage);
            return false;
        default:
            hideError(supplierNameError);
            return true;
    }
}

const validateCompanyName = () => {
    const companyNameInput = document.getElementById("companyNameInput").value.trim();
    const companyNameError = document.getElementById("companyNameError");

    const getUserNameError = (companyNameInput) => {
        if (!companyNameInput) return "Company name is required.";
        return null;
    };

    const errorMessage = getUserNameError(companyNameInput);

    switch (true) {
        case errorMessage !== null:
            showError(companyNameError, errorMessage);
            return false;
        default:
            hideError(companyNameError);
            return true;
    }
}

const  validateEmail = () => {
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
} 

const validateContactNumber = () => {
    const contactNumberInput = document.getElementById("contactNumberInput").value.trim();
    const contactNumberError = document.getElementById("contactNumberError");

    const getUserNameError = (contactNumberInput) => {
        if (!contactNumberInput) return "Contact number is required.";
        return null;
    };

    const errorMessage = getUserNameError(contactNumberInput);

    switch (true) {
        case errorMessage !== null:
            showError(contactNumberError, errorMessage);
            return false;
        default:
            hideError(contactNumberError);
            return true;
    }
}

const validateAddress = () => {
    const addressInput = document.getElementById("addressInput").value.trim();
    const addressError = document.getElementById("addressError");

    const getUserNameError = (addressInput) => {
        if (!addressInput) return "Address is required.";
        return null;
    };

    const errorMessage = getUserNameError(addressInput);

    switch (true) {
        case errorMessage !== null:
            showError(addressError, errorMessage);
            return false;
        default:
            hideError(addressError);
            return true;
    }
}

const validateCity = () => {
    const cityInput = document.getElementById("cityInput").value.trim();
    const cityError = document.getElementById("cityError");

    const getUserNameError = (cityInput) => {
        if (!cityInput) return "City is required.";
        return null;
    };

    const errorMessage = getUserNameError(cityInput);

    switch (true) {
        case errorMessage !== null:
            showError(cityError, errorMessage);
            return false;
        default:
            hideError(cityError);
            return true;
    }
}

const validateState = () => {
    const stateInput = document.getElementById("stateInput").value.trim();
    const stateError = document.getElementById("stateError");

    const getUserNameError = (stateInput) => {
        if (!stateInput) return "State is required.";
        return null;
    };

    const errorMessage = getUserNameError(stateInput);

    switch (true) {
        case errorMessage !== null:
            showError(stateError, errorMessage);
            return false;
        default:
            hideError(stateError);
            return true;
    }
}

const  validatePostalCode = () => {
    const postalCodeInput = document.getElementById("postalCodeInput").value.trim();
    const postalCodeError = document.getElementById("postalCodeError");

    const getUserNameError = (postalCodeInput) => {
        if (!postalCodeInput) return "Postal code is required.";
        return null;
    };

    const errorMessage = getUserNameError(postalCodeInput);

    switch (true) {
        case errorMessage !== null:
            showError(postalCodeError, errorMessage);
            return false;
        default:
            hideError(postalCodeError);
            return true;
    }
} 

const validateCountry = () => {
    const countryInput = document.getElementById("countryInput").value.trim();
    const countryError = document.getElementById("countryError");

    const getUserNameError = (countryInput) => {
        if (!countryInput) return "Country is required.";
        return null;
    };

    const errorMessage = getUserNameError(countryInput);

    switch (true) {
        case errorMessage !== null:
            showError(countryError, errorMessage);
            return false;
        default:
            hideError(countryError);
            return true;
    }
}

const validateRole = () => {
    const roleInput = document.getElementById("roleInput").value.trim();
    const roleError = document.getElementById("roleError");

    const getUserNameError = (roleInput) => {
        if (!roleInput) return "Role is required.";
        return null;
    };

    const errorMessage = getUserNameError(roleInput);

    switch (true) {
        case errorMessage !== null:
            showError(roleError, errorMessage);
            return false;
        default:
            hideError(roleError);
            return true;
    }
}

const validateRoleDescription = () => {
    const roleDescriptionInput = document.getElementById("roleDescriptionInput").value.trim();
    const roleDescriptionError = document.getElementById("roleDescriptionError");

    const getEmailError = (roleDescriptionInput) => {
        if (!roleDescriptionInput) return "Description is required.";
        return null;
    };

    const errorMessage = getEmailError(roleDescriptionInput);

    switch (true) {
        case errorMessage !== null:
            showError(roleDescriptionError, errorMessage);
            return false;
        default:
            hideError(roleDescriptionError);
            return true;
    }
}





