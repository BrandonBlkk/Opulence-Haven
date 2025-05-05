import { showError, hideError, showAlert, validateField } from './alertFunc.js';

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
const adminLogoutBtn = document.getElementById('adminLogoutBtn');
const adminConfirmModal = document.getElementById('adminConfirmModal');
const cancelBtn = document.getElementById('cancelBtn');
const adminConfirmLogoutBtn = document.getElementById('adminConfirmLogoutBtn');
const darkOverlay2 = document.getElementById('darkOverlay2');

if (adminLogoutBtn && adminConfirmModal && cancelBtn && adminConfirmLogoutBtn && darkOverlay2) {
    // Show Modal
    adminLogoutBtn.addEventListener('click', () => {
        adminConfirmModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        adminConfirmModal.classList.add('opacity-100', 'translate-y-0');
        darkOverlay2.classList.remove('opacity-0', 'invisible');
        darkOverlay2.classList.add('opacity-100');
    });

    // Close Modal on Cancel
    cancelBtn.addEventListener('click', () => {
        adminConfirmModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        adminConfirmModal.classList.remove('opacity-100', 'translate-y-0');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        menubar.classList.remove('-rotate-90');
    });

    // Handle Logout Action
    adminConfirmLogoutBtn.addEventListener('click', () => {
        adminConfirmModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        adminConfirmModal.classList.remove('opacity-100', 'translate-y-0');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        loader.style.display = 'flex';

        // Notify the server to destroy the session
        fetch('AdminLogout.php', { method: 'POST' })
            .then(() => {
                // Redirect after logout
                setTimeout(() => {
                    window.location.href = 'AdminSignin.php';
                }, 1000);
            })
            .catch((error) => console.error('Logout failed:', error));
    });
}

// Add Role Form
document.addEventListener("DOMContentLoaded", () => {
    const addRoleModal = document.getElementById('addRoleModal');
    const addRoleBtn = document.getElementById('addRoleBtn');
    const addRoleCancelBtn = document.getElementById('addRoleCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addRoleSuccess = document.getElementById('addRoleSuccess').value === 'true';

    if (addRoleModal && addRoleBtn && addRoleCancelBtn) {
        // Show modal
        addRoleBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRoleModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addRoleCancelBtn.addEventListener('click', () => {
            addRoleModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
            hideError(document.getElementById('roleError'));
            hideError(document.getElementById('roleDescriptionError'));
        });
    }

    if (addRoleSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new role has been successfully added.');
            setTimeout(() => {
                window.location.href = 'RoleManagement.php';
            }, 5000);
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
    const addSupplierModal = document.getElementById('addSupplierModal');
    const addSupplierBtn = document.getElementById('addSupplierBtn');
    const addSupplierCancelBtn = document.getElementById('addSupplierCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addSupplierSuccess = document.getElementById('addSupplierSuccess').value === 'true';

    if (addSupplierModal && addSupplierBtn && addSupplierCancelBtn) {
        // Show modal
        addSupplierBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addSupplierModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addSupplierCancelBtn.addEventListener('click', () => {
            addSupplierModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['supplierNameError', 'companyNameError', 'emailError', 'contactNumberError', 'addressError', 'cityError', 'stateError', 'postalCodeError', 'countryError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });

            document.getElementById('productType').value = '';
        });
    }

    if (addSupplierSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new supplier has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddSupplier.php';
            }, 5000);
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

// Supplier Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateSupplierModal = document.getElementById('updateSupplierModal');
    const modalCancelBtn = document.getElementById('supplierModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateSupplierSuccess = document.getElementById('updateSupplierSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateSupplierModal && modalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const supplierId = this.getAttribute('data-supplier-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch supplier details
                fetch(`../Admin/AddSupplier.php?action=getSupplierDetails&id=${supplierId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with supplier data
                            document.getElementById('updateSupplierID').value = supplierId;
                            document.querySelector('[name="updatesuppliername"]').value = data.supplier.SupplierName;
                            document.querySelector('[name="updatecompanyName"]').value = data.supplier.SupplierCompany;
                            document.querySelector('[name="updateemail"]').value = data.supplier.SupplierEmail;
                            document.querySelector('[name="updatecontactNumber"]').value = data.supplier.SupplierContact;
                            document.querySelector('[name="updateaddress"]').value = data.supplier.Address;
                            document.querySelector('[name="updatecity"]').value = data.supplier.City;
                            document.querySelector('[name="updatestate"]').value = data.supplier.State;
                            document.querySelector('[name="updatepostalCode"]').value = data.supplier.PostalCode;
                            document.querySelector('[name="updatecountry"]').value = data.supplier.Country;
                            document.querySelector('[name="updateproductType"]').value = data.supplier.ProductTypeID;

                            // Show the modal
                            updateSupplierModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load supplier details');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
        // Close modal when cancel button is clicked
        modalCancelBtn.addEventListener('click', () => {
            updateSupplierModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateSupplierNameError', 'updateCompanyNameError', 'updateEmailError', 'updateContactNumberError', 'updateAddressError', 'updateCityError', 'updateStateError', 'updatePostalCodeError', 'updateCountryError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateSupplierSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The supplier has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddSupplier.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }

        // Add keyup event listeners for real-time validation
        document.getElementById("updateSupplierNameInput").addEventListener("keyup", validateUpdateSupplierName);
        document.getElementById("updateCompanyNameInput").addEventListener("keyup", validateUpdateCompanyName);
        document.getElementById("updateEmailInput").addEventListener("keyup", validateUpdateEmail);
        document.getElementById("updateContactNumberInput").addEventListener("keyup", validateUpdateContactNumber);
        document.getElementById("updateAddressInput").addEventListener("keyup", validateUpdateAddress);
        document.getElementById("updateCityInput").addEventListener("keyup", validateUpdateCity);
        document.getElementById("updateStateInput").addEventListener("keyup", validateUpdateState);
        document.getElementById("updatePostalCodeInput").addEventListener("keyup", validateUpdatePostalCode);
        document.getElementById("updateCountryInput").addEventListener("keyup", validateUpdateCountry);

        const updateSupplierForm = document.getElementById("updateSupplierForm");
        if (updateSupplierForm) {
            updateSupplierForm.addEventListener("submit", (e) => {
                if (!validateUpdateSupplier()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Supplier Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const supplierConfirmDeleteModal = document.getElementById('supplierConfirmDeleteModal');
    const supplierCancelDeleteBtn = document.getElementById('supplierCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteSupplierSuccess = document.getElementById('deleteSupplierSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (supplierConfirmDeleteModal && supplierCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const supplierId = this.getAttribute('data-supplier-id');

                // Fetch supplierr details
                fetch(`../Admin/AddSupplier.php?action=getSupplierDetails&id=${supplierId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteSupplierID').value = supplierId;
                            document.getElementById('supplierDeleteName').textContent = data.supplier.SupplierName;
                        } else {
                            console.error('Failed to load product type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                supplierConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        supplierCancelDeleteBtn.addEventListener('click', () => {
            supplierConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteSupplierSuccess) {
            // Show Alert
            showAlert('The supplier has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddSupplier.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Product Type Form
document.addEventListener("DOMContentLoaded", () => {
    const addProductTypeModal = document.getElementById('addProductTypeModal');
    const addProductTypeBtn = document.getElementById('addProductTypeBtn');
    const addProductTypeCancelBtn = document.getElementById('addProductTypeCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addProductTypeSuccess = document.getElementById('addProductTypeSuccess').value === 'true';

    if (addProductTypeModal && addProductTypeBtn && addProductTypeCancelBtn) {
        // Show modal
        addProductTypeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addProductTypeCancelBtn.addEventListener('click', () => {
            addProductTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['productTypeError', 'descriptionError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });
    }

    if (addProductTypeSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new product type has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddProductType.php';
            }, 5000);
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

// Product Type Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateProductTypeModal = document.getElementById('updateProductTypeModal');
    const updateProductTypeModalCancelBtn = document.getElementById('updateProductTypeModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateProductTypeSuccess = document.getElementById('updateProductTypeSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateProductTypeModal && updateProductTypeModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const productTypeId = this.getAttribute('data-producttype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product type details
                fetch(`../Admin/AddProductType.php?action=getProductTypeDetails&id=${productTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product type data
                            document.getElementById('updateProductTypeID').value = productTypeId;
                            document.querySelector('[name="updateproducttype"]').value = data.producttype.ProductType;
                            document.querySelector('[name="updatedescription"]').value = data.producttype.Description;
                            // Show the modal
                            updateProductTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateProductTypeModalCancelBtn.addEventListener('click', () => {
            updateProductTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateProductTypeError', 'updateProductTypeDescriptionError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateProductTypeSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The product type has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddProductType.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateProductTypeInput").addEventListener("keyup", validateUpdateProductTypeInput);
        document.getElementById("updateProductTypeDescription").addEventListener("keyup", validateUpdateDescription);

        const updateProductTypeForm = document.getElementById("updateProductTypeForm");
        if (updateProductTypeForm) {
            updateProductTypeForm.addEventListener("submit", (e) => {
                if (!validateUpdateProductType()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Product Type Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const productTypeDeleteModal = document.getElementById('productTypeConfirmDeleteModal');
    const productTypeDeleteModalCancelBtn = document.getElementById('productTypeCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteProductTypeSuccess = document.getElementById('deleteProductTypeSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (productTypeDeleteModal && productTypeDeleteModalCancelBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const productTypeId = this.getAttribute('data-producttype-id');

                // Fetch product type details
                fetch(`../Admin/AddProductType.php?action=getProductTypeDetails&id=${productTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductTypeID').value = productTypeId;
                            document.getElementById('productTypeDeleteName').textContent = data.producttype.ProductType;
                        } else {
                            console.error('Failed to load product type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                productTypeDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        productTypeDeleteModalCancelBtn.addEventListener('click', () => {
            productTypeDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteProductTypeSuccess) {
            // Show Alert
            showAlert('The product type has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddProductType.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Product Form
document.addEventListener("DOMContentLoaded", () => {
    const addProductModal = document.getElementById('addProductModal');
    const addProductBtn = document.getElementById('addProductBtn');
    const addProductCancelBtn = document.getElementById('addProductCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addProductSuccess = document.getElementById('addProductSuccess').value === 'true';

    if (addProductModal && addProductBtn && addProductCancelBtn) {
        // Show modal
        addProductBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addProductCancelBtn.addEventListener('click', () => {
            addProductModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['productTitleError', 'brandError', 'productDescriptionError', 'specificationError', 'informationError', 'deliveryError', 'priceError', 'discountPriceError', 'stockError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });
    }

    if (addProductSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new product has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddProduct.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("productTitleInput").addEventListener("keyup", validateProductTitle);
    document.getElementById("brandInput").addEventListener("keyup", validateProductBrand);
    document.getElementById("productDescriptionInput").addEventListener("keyup", validateProductDescription);
    document.getElementById("specificationInput").addEventListener("keyup", validateProductSpecification);
    document.getElementById("informationInput").addEventListener("keyup", validateProductInformation);
    document.getElementById("deliveryInput").addEventListener("keyup", validateProductDelivery);
    document.getElementById("priceInput").addEventListener("keyup", validateProductPrice);
    document.getElementById("discountPriceInput").addEventListener("keyup", validateProductDiscountPrice);

    const productForm = document.getElementById("productForm");
    if (productForm) {
        productForm.addEventListener("submit", (e) => {
            if (!validateProductForm()) {
                e.preventDefault();
            }
        });
    }
});

// Product Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateProductModal = document.getElementById('updateProductModal');
    const updateProductModalCancelBtn = document.getElementById('updateProductModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateProductSuccess = document.getElementById('updateProductSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateProductModal && updateProductModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const productId = this.getAttribute('data-product-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product type details
                fetch(`../Admin/AddProduct.php?action=getProductDetails&id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product type data
                            document.getElementById('updateProductID').value = productId;
                            document.querySelector('[name="updateproductTitle"]').value = data.product.Title;
                            document.querySelector('[name="updatebrand"]').value = data.product.Brand;
                            document.querySelector('[name="updatedescription"]').value = data.product.Description;
                            document.querySelector('[name="updatespecification"]').value = data.product.Specification;
                            document.querySelector('[name="updateinformation"]').value = data.product.Information;
                            document.querySelector('[name="updatedelivery"]').value = data.product.DeliveryInfo;
                            document.querySelector('[name="updateprice"]').value = data.product.Price;
                            document.querySelector('[name="updatediscountPrice"]').value = data.product.DiscountPrice;
                            document.querySelector('[name="updatesellingfast"]').value = data.product.SellingFast;
                            document.querySelector('[name="updateproductType"]').value = data.product.ProductTypeID;

                            // Show the modal
                            updateProductModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateProductModalCancelBtn.addEventListener('click', () => {
            updateProductModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateProductTitleError', 'updateBrandError', 'updateDescriptionError', 'updateSpecificationError', 'updateInformationError', 'updateDeliveryError', 'updatePriceError', 'updateDiscountPriceError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateProductSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The product has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddProduct.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateProductTitleInput").addEventListener("keyup", validateUpdateProductTitle);
        document.getElementById("updateBrandInput").addEventListener("keyup", validateUpdateBrand);
        document.getElementById("updateDescriptionInput").addEventListener("keyup", validateUpdateProductDescription);
        document.getElementById("updateSpecificationInput").addEventListener("keyup", validateUpdateSpecification);
        document.getElementById("updateInformationInput").addEventListener("keyup", validateUpdateInformation);
        document.getElementById("updateDeliveryInput").addEventListener("keyup", validateUpdateDelivery);
        document.getElementById("updatePriceInput").addEventListener("keyup", validateUpdatePrice);
        document.getElementById("updateDiscountPriceInput").addEventListener("keyup", validateUpdateDiscountPrice);

        const updateProductForm = document.getElementById("updateProductForm");
        if (updateProductForm) {
            updateProductForm.addEventListener("submit", (e) => {
                if (!validateUpdateProduct()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Product Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const productDeleteModal = document.getElementById('productConfirmDeleteModal');
    const productDeleteModalCancelBtn = document.getElementById('productCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteProductSuccess = document.getElementById('deleteProductSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (productDeleteModal && productDeleteModalCancelBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const productId = this.getAttribute('data-product-id');

                // Fetch product type details
                fetch(`../Admin/AddProduct.php?action=getProductDetails&id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductID').value = productId;
                            document.getElementById('productDeleteName').textContent = data.product.Title;
                        } else {
                            console.error('Failed to load product details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                productDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        productDeleteModalCancelBtn.addEventListener('click', () => {
            productDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteProductSuccess) {
            // Show Alert
            showAlert('The product has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddProduct.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Room Type Form
document.addEventListener("DOMContentLoaded", () => {
    const addRoomTypeModal = document.getElementById('addRoomTypeModal');
    const addRoomTypeBtn = document.getElementById('addRoomTypeBtn');
    const addRoomTypeCancelBtn = document.getElementById('addRoomTypeCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addRoomTypeSuccess = document.getElementById('addRoomTypeSuccess').value === 'true';

    if (addRoomTypeModal && addRoomTypeBtn && addRoomTypeCancelBtn) {
        // Show modal
        addRoomTypeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRoomTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addRoomTypeCancelBtn.addEventListener('click', () => {
            addRoomTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['roomTypeError', 'roomTypeDescriptionError', 'roomCapacityError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });
    }

    if (addRoomTypeSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new room type has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddRoomType.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("roomTypeInput").addEventListener("keyup", validateRoomType);
    document.getElementById("roomTypeDescriptionInput").addEventListener("keyup", validateRoomTypeDescription);
    document.getElementById("roomCapacityInput").addEventListener("keyup", validateRoomCapacity);

    const roomTypeForm = document.getElementById("roomTypeForm");
    if (roomTypeForm) {
        roomTypeForm.addEventListener("submit", (e) => {
            if (!validateRoomTypeForm()) {
                e.preventDefault();
            }
        });
    }
});

// Room Type Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateRoomTypeModal = document.getElementById('updateRoomTypeModal');
    const updateRoomTypeModalCancelBtn = document.getElementById('updateRoomTypeModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateRoomTypeSuccess = document.getElementById('updateRoomTypeSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateRoomTypeModal && updateRoomTypeModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const roomTypeId = this.getAttribute('data-roomtype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product type details
                fetch(`../Admin/AddRoomType.php?action=getRoomTypeDetails&id=${roomTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product type data
                            document.getElementById('updateRoomTypeID').value = roomTypeId;
                            document.querySelector('[name="updateroomtype"]').value = data.roomtype.RoomType;
                            document.querySelector('[name="updateroomtypedescription"]').value = data.roomtype.RoomDescription;
                            document.querySelector('[name="updateroomcapacity"]').value = data.roomtype.RoomCapacity;
                            // Show the modal
                            updateRoomTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load room type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateRoomTypeModalCancelBtn.addEventListener('click', () => {
            updateRoomTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateRoomTypeError', 'updateRoomTypeDescriptionError', 'updateRoomCapacityError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateRoomTypeSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The room type has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddRoomType.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateRoomTypeInput").addEventListener("keyup", validateUpdateRoomType);
        document.getElementById("updateRoomTypeDescriptionInput").addEventListener("keyup", validateUpdateRoomTypeDescription);
        document.getElementById("updateRoomCapacityInput").addEventListener("keyup", validateUpdateRoomCapacity);

        const updateRoomTypeForm = document.getElementById("updateRoomTypeForm");
        if (updateRoomTypeForm) {
            updateRoomTypeForm.addEventListener("submit", (e) => {
                if (!validateUpdateRoomTypeForm()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Room Type Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const roomTypeConfirmDeleteModal = document.getElementById('roomTypeConfirmDeleteModal');
    const roomTypeCancelDeleteBtn = document.getElementById('roomTypeCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteRoomTypeSuccess = document.getElementById('deleteRoomTypeSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (roomTypeConfirmDeleteModal && roomTypeCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const roomTypeId = this.getAttribute('data-roomtype-id');

                // Fetch product type details
                fetch(`../Admin/AddRoomType.php?action=getRoomTypeDetails&id=${roomTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteRoomTypeID').value = roomTypeId;
                            document.getElementById('roomTypeDeleteName').textContent = data.roomtype.RoomType;
                        } else {
                            console.error('Failed to load room type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                roomTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        roomTypeCancelDeleteBtn.addEventListener('click', () => {
            roomTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteRoomTypeSuccess) {
            // Show Alert
            showAlert('The room type has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddRoomType.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Room Form
document.addEventListener("DOMContentLoaded", () => {
    const addRoomModal = document.getElementById('addRoomModal');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const addRoomCancelBtn = document.getElementById('addRoomCancelBtn');
    // const loader = document.getElementById('loader');
    // const alertMessage = document.getElementById('alertMessage').value;
    // const addRoomSuccess = document.getElementById('addRoomSuccess').value === 'true';

    if (addRoomModal && addRoomBtn && addRoomCancelBtn) {
        // Show modal
        addRoomBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRoomModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addRoomCancelBtn.addEventListener('click', () => {
            addRoomModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            // const errors = ['roomTypeError', 'roomTypeDescriptionError', 'roomCapacityError'];
            // errors.forEach(error => {
            //     hideError(document.getElementById(error));
            // });
        });
    }

    // if (addRoomSuccess) {
    //     loader.style.display = 'flex';

    //     // Show Alert
    //     setTimeout(() => {
    //         loader.style.display = 'none';
    //         showAlert('A new room type has been successfully added.');
    //         setTimeout(() => {
    //             window.location.href = 'AddRoomType.php';
    //         }, 5000);
    //     }, 1000);
    // } else if (alertMessage) {
    //     // Show Alert
    //     showAlert(alertMessage);
    // }

    // Add keyup event listeners for real-time validation
    // document.getElementById("roomTypeInput").addEventListener("keyup", validateRoomType);
    // document.getElementById("roomTypeDescriptionInput").addEventListener("keyup", validateRoomTypeDescription);
    // document.getElementById("roomCapacityInput").addEventListener("keyup", validateRoomCapacity);

    // const roomForm = document.getElementById("roomForm");
    // if (roomForm) {
    //     roomForm.addEventListener("submit", (e) => {
    //         if (!validateRoomTypeForm()) {
    //             e.preventDefault();
    //         }
    //     });
    // }
});

// Add Facility Type Form
document.addEventListener("DOMContentLoaded", () => {
    const addFacilityTypeModal = document.getElementById('addFacilityTypeModal');
    const addFacilityTypeBtn = document.getElementById('addFacilityTypeBtn');
    const addFacilityTypeCancelBtn = document.getElementById('addFacilityTypeCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addFacilityTypeSuccess = document.getElementById('addFacilityTypeSuccess').value === 'true';

    if (addFacilityTypeModal && addFacilityTypeBtn && addFacilityTypeCancelBtn) {
        // Show modal
        addFacilityTypeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addFacilityTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addFacilityTypeCancelBtn.addEventListener('click', () => {
            addFacilityTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
     
            // Clear error messages
            const errors = ['facilityTypeError', 'facilityTypeIconError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });
    }

    if (addFacilityTypeSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new facility type has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddFacilityType.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("facilityTypeInput").addEventListener("keyup", validateFacilityType);
    document.getElementById("facilityTypeIconInput").addEventListener("keyup", validateFacilityTypeIcon);

    const facilityTypeForm = document.getElementById("facilityTypeForm");
    if (facilityTypeForm) {
        facilityTypeForm.addEventListener("submit", (e) => {
            if (!validateFacilityTypeForm()) {
                e.preventDefault();
            }
        });
    }
});

// Facility Type Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateFacilityTypeModal = document.getElementById('updateFacilityTypeModal');
    const updateFacilityTypeModalCancelBtn = document.getElementById('updateFacilityTypeModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateFacilityTypeSuccess = document.getElementById('updateFacilityTypeSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateFacilityTypeModal && updateFacilityTypeModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const facilityTypeId = this.getAttribute('data-facilitytype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product type details
                fetch(`../Admin/AddFacilityType.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product type data
                            document.getElementById('updateFacilityTypeID').value = facilityTypeId;
                            document.querySelector('[name="updatefacilitytype"]').value = data.facilitytype.FacilityType;
                            document.querySelector('[name="updatefacilitytypeicon"]').value = data.facilitytype.FacilityTypeIcon;
                            document.querySelector('[name="updatefacilitytypeiconsize"]').value = data.facilitytype.IconSize;
                            // Show the modal
                            updateFacilityTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateFacilityTypeModalCancelBtn.addEventListener('click', () => {
            updateFacilityTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateFacilityTypeError', 'updateFacilityTypeIconError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateFacilityTypeSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The facility type has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddFacilityType.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateFacilityTypeInput").addEventListener("keyup", validateUpdateFacilityType);
        document.getElementById("updateFacilityTypeIconInput").addEventListener("keyup", validateUpdateFacilityTypeIcon);

        const updateFacilityTypeForm = document.getElementById("updateFacilityTypeForm");
        if (updateFacilityTypeForm) {
            updateFacilityTypeForm.addEventListener("submit", (e) => {
                if (!validateUpdateFacilityTypeForm()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Facility Type Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const facilityTypeConfirmDeleteModal = document.getElementById('facilityTypeConfirmDeleteModal');
    const facilityTypeCancelDeleteBtn = document.getElementById('facilityTypeCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteFacilityTypeSuccess = document.getElementById('deleteFacilityTypeSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (facilityTypeConfirmDeleteModal && facilityTypeCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const facilityTypeId = this.getAttribute('data-facilitytype-id');

                // Fetch product type details
                fetch(`../Admin/AddFacilityType.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteFacilityTypeID').value = facilityTypeId;
                            document.getElementById('facilityTypeDeleteName').textContent = data.facilitytype.FacilityType;
                        } else {
                            console.error('Failed to load facility type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                facilityTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        facilityTypeCancelDeleteBtn.addEventListener('click', () => {
            facilityTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteFacilityTypeSuccess) {
            // Show Alert
            showAlert('The facility type has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddFacilityType.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Facility Form
document.addEventListener("DOMContentLoaded", () => {
    const addFacilityModal = document.getElementById('addFacilityModal');
    const addFacilityBtn = document.getElementById('addFacilityBtn');
    const addFacilityCancelBtn = document.getElementById('addFacilityCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addFacilitySuccess = document.getElementById('addFacilitySuccess').value === 'true';

    if (addFacilityModal && addFacilityBtn && addFacilityCancelBtn) {
        // Show modal
        addFacilityBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addFacilityModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addFacilityCancelBtn.addEventListener('click', () => {
            addFacilityModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['facilityError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });

            document.getElementById('FacilityType').value = '';
        });
    }

    if (addFacilitySuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new facility type has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddFacility.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("facilityInput").addEventListener("keyup", validateFacility);

    const facilityForm = document.getElementById("facilityForm");
    if (facilityForm) {
        facilityForm.addEventListener("submit", (e) => {
            if (!validateFacilityForm()) {
                e.preventDefault();
            }
        });
    }
});

// Facility Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateFacilityModal = document.getElementById('updateFacilityModal');
    const updateFacilityModalCancelBtn = document.getElementById('updateFacilityModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateFacilitySuccess = document.getElementById('updateFacilitySuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateFacilityModal && updateFacilityModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const facilityId = this.getAttribute('data-facility-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product  details
                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product  data
                            document.getElementById('updateFacilityID').value = facilityId;
                            document.querySelector('[name="updatefacility"]').value = data.facility.Facility;
                            document.querySelector('[name="updatefacilityicon"]').value = data.facility.FacilityIcon;
                            document.querySelector('[name="updatefacilityiconsize"]').value = data.facility.IconSize;
                            document.querySelector('[name="updateadditionalcharge"]').value = data.facility.AdditionalCharge;
                            document.querySelector('[name="updatepopular"]').value = data.facility.Popular;
                            document.querySelector('[name="updatefacilitytype"]').value = data.facility.FacilityTypeID;
                            // Show the modal
                            updateFacilityModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateFacilityModalCancelBtn.addEventListener('click', () => {
            updateFacilityModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateFacilityError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateFacilitySuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The facility has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddFacility.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateFacilityInput").addEventListener("keyup", validateUpdateFacility);

        const updateFacilityForm = document.getElementById("updateFacilityForm");
        if (updateFacilityForm) {
            updateFacilityForm.addEventListener("submit", (e) => {
                if (!validateFacilityUpdateForm()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Facility Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const facilityConfirmDeleteModal = document.getElementById('facilityConfirmDeleteModal');
    const facilityCancelDeleteBtn = document.getElementById('facilityCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteFacilitySuccess = document.getElementById('deleteFacilitySuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (facilityConfirmDeleteModal && facilityCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const facilityId = this.getAttribute('data-facility-id');

                // Fetch fafcility details
                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteFacilityID').value = facilityId;
                            document.getElementById('facilityDeleteName').textContent = data.facility.Facility;
                        } else {
                            console.error('Failed to load facility details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                facilityConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        facilityCancelDeleteBtn.addEventListener('click', () => {
            facilityConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteFacilitySuccess) {
            // Show Alert
            showAlert('The facility has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddFacility.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Rule Form
document.addEventListener("DOMContentLoaded", () => {
    const addRuleModal = document.getElementById('addRuleModal');
    const addRuleBtn = document.getElementById('addRuleBtn');
    const addRuleCancelBtn = document.getElementById('addRuleCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addProductSizeSuccess = document.getElementById('addProductSizeSuccess').value === 'true';

    if (addRuleModal && addRuleBtn && addRuleCancelBtn) {
        // Show modal
        addRuleBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRuleModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addRuleCancelBtn.addEventListener('click', () => {
            addRuleModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['ruleTitleError', 'ruleError', 'ruleIconError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });
    }

    if (addProductSizeSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new rule has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddRule.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("ruleTitleInput").addEventListener("keyup", validateRuleTitle);
    document.getElementById("ruleInput").addEventListener("keyup", validateRule);
    document.getElementById("ruleIconInput").addEventListener("keyup", validateRuleIcon);

    const ruleForm = document.getElementById("ruleForm");
    if (ruleForm) {
        ruleForm.addEventListener("submit", (e) => {
            if (!validateRuleForm()) {
                e.preventDefault();
            }
        });
    }
});

// Rule Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateRuleModal = document.getElementById('updateRuleModal');
    const updateRuleModalCancelBtn = document.getElementById('updateRuleModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateRuleSuccess = document.getElementById('updateRuleSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateRuleModal && updateRuleModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const ruleId = this.getAttribute('data-rule-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product  details
                fetch(`../Admin/AddRule.php?action=getRuleDetails&id=${ruleId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product  data
                            document.getElementById('updateRuleID').value = ruleId;
                            document.querySelector('[name="updateruletitle"]').value = data.rule.RuleTitle;
                            document.querySelector('[name="updaterule"]').value = data.rule.Rule;
                            document.querySelector('[name="updateruleicon"]').value = data.rule.RuleIcon;
                            document.querySelector('[name="updateruleiconsize"]').value = data.rule.IconSize;
                            // Show the modal
                            updateRuleModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load rule details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateRuleModalCancelBtn.addEventListener('click', () => {
            updateRuleModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateRuleTitleError', 'updateRuleError', 'updateRuleIconError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateRuleSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The rule has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddRule.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateRuleTitleInput").addEventListener("keyup", validateUpdateRuleTitle);
        document.getElementById("updateRuleInput").addEventListener("keyup", validateUpdateRule);
        document.getElementById("updateRuleIconInput").addEventListener("keyup", validateUpdateRuleIcon);

        const updateRuleForm = document.getElementById("updateRuleForm");
        if (updateRuleForm) {
            updateRuleForm.addEventListener("submit", (e) => {
                if (!validateRuleUpdateForm()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Rule Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const ruleConfirmDeleteModal = document.getElementById('ruleConfirmDeleteModal');
    const ruleCancelDeleteBtn = document.getElementById('ruleCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteRuleSuccess = document.getElementById('deleteRuleSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (ruleConfirmDeleteModal && ruleCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const ruleId = this.getAttribute('data-rule-id');

                // Fetch fafcility details
                fetch(`../Admin/AddRule.php?action=getRuleDetails&id=${ruleId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteRuleID').value = ruleId;
                            document.getElementById('ruleDeleteName').textContent = data.rule.RuleTitle;
                        } else {
                            console.error('Failed to load rule details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                ruleConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        ruleCancelDeleteBtn.addEventListener('click', () => {
            ruleConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteRuleSuccess) {
            // Show Alert
            showAlert('The rule has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddRule.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Product Image Form
document.addEventListener("DOMContentLoaded", () => {
    const addProductImageModal = document.getElementById('addProductImageModal');
    const addProductImageBtn = document.getElementById('addProductImageBtn');
    const addProductImageCancelBtn = document.getElementById('addProductImageCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addProductImageSuccess = document.getElementById('addProductImageSuccess').value === 'true';

    if (addProductImageModal && addProductImageBtn && addProductImageCancelBtn) {
        // Show modal
        addProductImageBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductImageModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addProductImageCancelBtn.addEventListener('click', () => {
            addProductImageModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['productImageAltError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });

            document.getElementById('product').value = '';
        });
    }

    if (addProductImageSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new product image has been successfully added.');
            setTimeout(() => {
                window.location.href = 'ProductImage.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("productImageAltInput").addEventListener("keyup", validateProductImageAlt);
    const productImageForm = document.getElementById("productImageForm");
    if (productImageForm) {
        productImageForm.addEventListener("submit", (e) => {
            if (!validateProductImageForm()) {
                e.preventDefault();
            }
        });
    }
});

// Product Image Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateProductImageModal = document.getElementById('updateProductImageModal');
    const updateProductImageModalCancelBtn = document.getElementById('updateProductImageModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateProductImageSuccess = document.getElementById('updateProductImageSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateProductImageModal && updateProductImageModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const ImageId = this.getAttribute('data-productimage-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product  details
                fetch(`../Admin/ProductImage.php?action=getProductImageDetails&id=${ImageId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product  data
                            document.getElementById('updateProductImageID').value = ImageId;
                            document.getElementById('updateimagepath').src = data.productimage.ImageAdminPath;
                            document.querySelector('[name="updateimagealt"]').value = data.productimage.ImageAlt;
                            document.querySelector('[name="updateproduct"]').value = data.productimage.ProductID;
                            document.querySelector('[name="updateprimary"]').value = data.productimage.PrimaryImage;
                            document.querySelector('[name="updatesecondary"]').value = data.productimage.SecondaryImage;
                            // Show the modal
                            updateProductImageModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product image details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateProductImageModalCancelBtn.addEventListener('click', () => {
            updateProductImageModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateProductImageAltError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateProductImageSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The product image has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'ProductImage.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateProductImageAltInput").addEventListener("keyup", validateUpdateProductImageAlt);

        const updateProductImageForm = document.getElementById("updateProductImageForm");
        if (updateProductImageForm) {
            updateProductImageForm.addEventListener("submit", (e) => {
                if (!validateProductImageUpdateForm()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Product Image Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const productImageConfirmDeleteModal = document.getElementById('productImageConfirmDeleteModal');
    const productImageCancelDeleteBtn = document.getElementById('productImageCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteProductImageSuccess = document.getElementById('deleteProductImageSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (productImageConfirmDeleteModal && productImageCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const ImageId = this.getAttribute('data-productimage-id');

                // Fetch fafcility details
                fetch(`../Admin/ProductImage.php?action=getProductImageDetails&id=${ImageId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductImageID').value = ImageId;
                            document.getElementById('deleteImagePath').src = data.productimage.ImageAdminPath;
                        } else {
                            console.error('Failed to load productimage details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                productImageConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        productImageCancelDeleteBtn.addEventListener('click', () => {
            productImageConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteProductImageSuccess) {
            // Show Alert
            showAlert('The product image has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'ProductImage.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Add Product Size Form
document.addEventListener("DOMContentLoaded", () => {
    const addProductSizeModal = document.getElementById('addProductSizeModal');
    const addProductSizeBtn = document.getElementById('addProductSizeBtn');
    const addProductSizeCancelBtn = document.getElementById('addProductSizeCancelBtn');
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const addProductSizeSuccess = document.getElementById('addProductSizeSuccess').value === 'true';

    if (addProductSizeModal && addProductSizeBtn && addProductSizeCancelBtn) {
        // Show modal
        addProductSizeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductSizeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addProductSizeCancelBtn.addEventListener('click', () => {
            addProductSizeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['sizeError', 'priceModifierError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });

            document.getElementById('product').value = '';
        });
    }

    if (addProductSizeSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('A new product size has been successfully added.');
            setTimeout(() => {
                window.location.href = 'AddSize.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("sizeInput").addEventListener("keyup", validateProductSize);
    document.getElementById("priceModifierInput").addEventListener("keyup", validatePriceModifier);

    const productSizeForm = document.getElementById("productSizeForm");
    if (productSizeForm) {
        productSizeForm.addEventListener("submit", (e) => {
            if (!validateProductSizeForm()) {
                e.preventDefault();
            }
        });
    }
});

// Product Size Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const updateProductSizeModal = document.getElementById('updateProductSizeModal');
    const updateProductSizeModalCancelBtn = document.getElementById('updateProductSizeModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const updateProductSizeSuccess = document.getElementById('updateProductSizeSuccess').value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (updateProductSizeModal && updateProductSizeModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const productSizeId = this.getAttribute('data-productsize-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch product  details
                fetch(`../Admin/AddSize.php?action=getProductSizeDetails&id=${productSizeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product  data
                            document.getElementById('updateProductSizeID').value = productSizeId;
                            document.querySelector('[name="updatesize"]').value = data.productsize.Size;
                            document.querySelector('[name="updateprice"]').value = data.productsize.PriceModifier;
                            document.querySelector('[name="updateproduct"]').value = data.productsize.ProductID;
                            // Show the modal
                            updateProductSizeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product size details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        updateProductSizeModalCancelBtn.addEventListener('click', () => {
            updateProductSizeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            // Clear error messages
            const errors = ['updateSizeError', 'updatePriceModifierError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        if (updateProductSizeSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The product size has been successfully updated.');
                setTimeout(() => {
                    window.location.href = 'AddSize.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
        // Add keyup event listeners for real-time validation
        document.getElementById("updateSizeInput").addEventListener("keyup", validateUpdateProductSize);
        document.getElementById("updatePriceModifierInput").addEventListener("keyup", validateUpdateProductModifier);

        const updateProductSizeForm = document.getElementById("updateProductSizeForm");
        if (updateProductSizeForm) {
            updateProductSizeForm.addEventListener("submit", (e) => {
                if (!validateProductSizeUpdateForm()) {
                    e.preventDefault();
                }
            });
        }
    }
});

// Product Size Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const productSizeConfirmDeleteModal = document.getElementById('productSizeConfirmDeleteModal');
    const productSizeCancelDeleteBtn = document.getElementById('productSizeCancelDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteProductSizeSuccess = document.getElementById('deleteProductSizeSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (productSizeConfirmDeleteModal && productSizeCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const productSizeId = this.getAttribute('data-productsize-id');

                // Fetch fafcility details
                fetch(`../Admin/AddSize.php?action=getProductSizeDetails&id=${productSizeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductSizeID').value = productSizeId;
                            document.getElementById('productSizeDeleteName').textContent = data.productsize.Size;
                        } else {
                            console.error('Failed to load product size details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                productSizeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        productSizeCancelDeleteBtn.addEventListener('click', () => {
            productSizeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (deleteProductSizeSuccess) {
            // Show Alert
            showAlert('The product size has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'AddSize.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Admin Delete Modal
document.addEventListener('DOMContentLoaded', () => {
    const adminConfirmDeleteModal = document.getElementById('adminConfirmDeleteModal');
    const adminCancelDeleteBtn = document.getElementById('adminCancelDeleteBtn');
    const deleteAdminConfirmInput = document.getElementById('deleteAdminConfirmInput');
    const confirmAdminDeleteBtn = document.getElementById('confirmAdminDeleteBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const deleteAdminSuccess = document.getElementById('deleteAdminSuccess').value === 'true';

    // Get all delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');

    if (adminConfirmDeleteModal && adminCancelDeleteBtn && deleteBtns) {
        // Add click event to each delete button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const adminId = this.getAttribute('data-admin-id');

                // Fetch admin details
                fetch(`../Admin/RoleManagement.php?action=getAdminDetails&id=${adminId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteAdminID').value = adminId;
                            document.getElementById('adminDeleteEmail').textContent = data.admin.AdminEmail;
                            document.getElementById('adminDeleteUsername').textContent = data.admin.UserName;
                            
                            // Handle both cases (with profile image and without)
                            if (data.admin.AdminProfile) {
                                // If admin has profile image
                                document.getElementById('adminDeleteProfile').src = data.admin.AdminProfile;
                                // Show image container and hide text container
                                document.getElementById('adminDeleteProfile').parentElement.parentElement.style.display = 'block';
                                document.getElementById('adminDeleteProfileText').parentElement.style.display = 'none';
                            } else {
                                // If admin doesn't have profile image
                                document.getElementById('adminDeleteProfileText').textContent = data.admin.UserName.charAt(0).toUpperCase();
                                // Show text container and hide image container
                                document.getElementById('adminDeleteProfileText').parentElement.style.backgroundColor = data.admin.ProfileBgColor;
                                document.getElementById('adminDeleteProfileText').parentElement.style.display = 'block';
                                document.getElementById('adminDeleteProfile').parentElement.parentElement.style.display = 'none';
                            }
                        } else {
                            console.error('Failed to load admin details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
                
                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                adminConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        adminCancelDeleteBtn.addEventListener('click', () => {
            adminConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        // Enable Delete Button only if input matches "DELETE"
        deleteAdminConfirmInput.addEventListener("input", () => {
            const isMatch = deleteAdminConfirmInput.value.trim() === "DELETE";
            confirmAdminDeleteBtn.disabled = !isMatch;

            // Toggle the 'cursor-not-allowed' class
            if (isMatch) {
                confirmAdminDeleteBtn.classList.remove("cursor-not-allowed");
            } else {
                confirmAdminDeleteBtn.classList.add("cursor-not-allowed");
            }
        });

        if (deleteAdminSuccess) {
            // Show Alert
            showAlert('The admin account has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'RoleManagement.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Contact Date Filter Modal
document.addEventListener('DOMContentLoaded', () => {
    const contactDateFilterModal = document.getElementById('contactDateFilterModal');
    const contactDateFilterBtn = document.getElementById('contactDateFilterBtn');

    if (contactDateFilterModal && contactDateFilterBtn) {
        const toggleModal = () => {
            contactDateFilterModal.classList.toggle('opacity-0');
            contactDateFilterModal.classList.toggle('invisible');
            contactDateFilterModal.classList.toggle('-translate-y-5');
        };

        contactDateFilterBtn.addEventListener('click', toggleModal);
    }
});

// Contact Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const confirmContactModal = document.getElementById('confirmContactModal');
    const confirmContactModalCancelBtn = document.getElementById('confirmContactModalCancelBtn');
    const alertMessage = document.getElementById('alertMessage')?.value || '';
    const confirmContactSuccess = document.getElementById('confirmContactSuccess')?.value === 'true';

    // Get all details buttons
    const detailsBtns = document.querySelectorAll('.details-btn');

    if (confirmContactModal && confirmContactModalCancelBtn && detailsBtns) {
        // Add click event to each button
        detailsBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const contactId = this.getAttribute('data-contact-id');
                const darkOverlay2 = document.getElementById('darkOverlay2');

                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch contact details
                fetch(`../Admin/UserContact.php?action=getContactDetails&id=${contactId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with contact data
                            document.getElementById('confirmContactID').value = contactId;
                            document.getElementById('contactMessage').textContent = data.contact.ContactMessage;
                            document.getElementById('username').textContent = data.contact.FullName;
                            document.getElementById('useremail').textContent = data.contact.UserEmail;
                            document.getElementById('userphone').textContent = data.contact.UserPhone;
                            document.getElementById('usercountry').textContent = data.contact.Country;

                            // Show the modal
                            confirmContactModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load contact details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        confirmContactModalCancelBtn.addEventListener('click', () => {
            confirmContactModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            document.getElementById('darkOverlay2').classList.add('opacity-0', 'invisible');
            document.getElementById('darkOverlay2').classList.remove('opacity-100');
        });

        if (confirmContactSuccess) {
            // Show Alert
            setTimeout(() => {
                showAlert('The contact has been successfully responded.');
                setTimeout(() => {
                    window.location.href = 'UserContact.php';
                }, 5000);
            }, 500);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Change Password Modal
const changePasswordBtn = document.getElementById("changePasswordBtn");
const changePasswordModal = document.getElementById("changePasswordModal");
const cancelChangeBtn = document.getElementById("cancelChangeBtn");

if (changePasswordBtn && changePasswordModal && cancelChangeBtn && darkOverlay2) {
    // Show Change Password Modal
    changePasswordBtn.addEventListener("click", (e) => {
        e.preventDefault();
        changePasswordModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
        changePasswordModal.classList.add("opacity-100", "translate-y-0");
        darkOverlay2.classList.remove("opacity-0", "invisible");
        darkOverlay2.classList.add("opacity-100");
    });

    // Close Change Password Modal on Cancel
    cancelChangeBtn.addEventListener("click", () => {
        changePasswordModal.classList.add("opacity-0", "invisible", "-translate-y-5");
        changePasswordModal.classList.remove("opacity-100", "translate-y-0");
        darkOverlay2.classList.add("opacity-0", "invisible");
        darkOverlay2.classList.remove("opacity-100");
        
        // Clear inputs and errors
        document.getElementById("oldPasswordInput").value = "";
        document.getElementById("newPasswordInput").value = "";
        document.getElementById("confirmPasswordInput").value = "";
        hideError(document.getElementById("oldPasswordError"));
        hideError(document.getElementById("newPasswordError"));
        hideError(document.getElementById("confirmPasswordError"));
    });

    const urlParams = new URLSearchParams(window.location.search);
    const passwordChangeSuccess = urlParams.get('passwordChangeSuccess') === 'true';
    const alertMessage = urlParams.get('alertMessage');
    
    if (passwordChangeSuccess) {
        showAlert('You have successfully changed a password.');
    
        // Remove the query params after showing the alert
        urlParams.delete('passwordChangeSuccess');
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.replaceState({}, '', newUrl);
    } else if (alertMessage) {
        showAlert(decodeURIComponent(alertMessage));
        
        // Remove error message after displaying
        urlParams.delete('alertMessage');
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.replaceState({}, '', newUrl);
    }
    
    // Add keyup event listeners for real-time validation
    document.getElementById("oldPasswordInput").addEventListener("keyup", validateOldPassword);
    document.getElementById("newPasswordInput").addEventListener("keyup", validateNewPassword);
    document.getElementById("confirmPasswordInput").addEventListener("keyup", validateConfirmPassword);

    const changePasswordForm = document.getElementById("changePasswordForm");
    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", (e) => {
            if (!validateChangePasswordForm()) {
                e.preventDefault();
            }
        });
    }
}

// Profile Delete Modal
const adminProfileDeleteBtn = document.getElementById("adminProfileDeleteBtn");
const confirmDeleteModal = document.getElementById("confirmDeleteModal");
const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
const deleteConfirmInput = document.getElementById("deleteConfirmInput");

if (adminProfileDeleteBtn && confirmDeleteModal && cancelDeleteBtn && confirmDeleteBtn && deleteConfirmInput) {
    // Show Delete Modal
    adminProfileDeleteBtn.addEventListener("click", () => {
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
        fetch("AdminAccountDelete.php", {
            method: "POST",
        })
            .then(() => {
                // Redirect after account deletion
                setTimeout(() => {
                    window.location.href = "AdminSignin.php";
                }, 1000);
            })
            .catch((error) => console.error("Account deletion failed:", error));
    });
}

// Profile Update Form Validation
document.addEventListener("DOMContentLoaded", () => {
    const alertMessage = document.getElementById('alertMessage').value;
    const profileUpdate = document.getElementById('profileUpdate').value === 'true';

    if (profileUpdate) {
        // Show Alert
        showAlert('You have successfully changed a profile.');
        setTimeout(() => {
            window.location.href = 'AdminProfileEdit.php';
        }, 5000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    document.getElementById("firstnameInput").addEventListener("keyup", validateFirstName);
    document.getElementById("lastnameInput").addEventListener("keyup", validateLastName);
    document.getElementById("usernameInput").addEventListener("keyup", validateUsername);
    document.getElementById("emailInput").addEventListener("keyup", validateEmail);
    document.getElementById("phoneInput").addEventListener("keyup", validatePhone);

    // Add submit event listener for form validation
    const updateAdminProfileForm = document.getElementById("updateAdminProfileForm");
    if (updateAdminProfileForm) {
        updateAdminProfileForm.addEventListener("submit", (e) => {
            if (!validateProfileUpdateForm()) {
                e.preventDefault();
            }
        });
    }
});

// Admin Reset Password Modal
document.addEventListener('DOMContentLoaded', () => {
    const resetAdminPasswordModal = document.getElementById('resetAdminPasswordModal');
    const adminResetPasswordCancelBtn = document.getElementById('adminResetPasswordCancelBtn');
    const alertMessage = document.getElementById('alertMessage').value;
    const resetAdminPasswordSuccess = document.getElementById('resetAdminPasswordSuccess').value === 'true';

    // Get all reset buttons
    const resetBtns = document.querySelectorAll('.reset-btn');

    if (resetAdminPasswordModal && adminResetPasswordCancelBtn && resetBtns) {
        // Add click event to each delete button
        resetBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const adminId = this.getAttribute('data-admin-id');

                // Fetch admin details
                fetch(`../Admin/RoleManagement.php?action=getAdminDetails&id=${adminId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('resetAdminID').value = adminId;
                            document.getElementById('adminResetEmail').textContent = data.admin.AdminEmail;
                        } else {
                            console.error('Failed to load admin details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                resetAdminPasswordModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });
        });

        // Cancel button functionality
        adminResetPasswordCancelBtn.addEventListener('click', () => {
            resetAdminPasswordModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        if (resetAdminPasswordSuccess) {
            // Show Alert
            showAlert('The admin password has been successfully reset.');
            setTimeout(() => {
                window.location.href = 'RoleManagement.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }
});

// Product Purchase 
document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const purchaseSuccess = document.getElementById('purchaseSuccess').value === 'true';

    if (purchaseSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
            showAlert('Purchase completed successfully! Stock increased.');
            setTimeout(() => {
                window.location.href = '../Admin/ProductPurchase.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }
});

// Full form validation function
const validateProductTypeForm = () => {
    const isTypeValid = validateProductType();
    const isDescriptionValid = validateDescription();

    return isTypeValid && isDescriptionValid;
};

const validateUpdateProductType = () => {
    const isTypeValid = validateUpdateProductTypeInput();
    const isDescriptionValid = validateUpdateDescription();

    return isTypeValid && isDescriptionValid;
};

const validateProductForm = () => {
    const isProductTitleValid = validateProductTitle();
    const isProductBrandValid = validateProductBrand();
    const isProductDescriptionValid = validateProductDescription();
    const isProductSpecificationValid = validateProductSpecification();
    const isProductInformationValid = validateProductInformation();
    const isProductDeliveryValid = validateProductDelivery();
    const isProductPriceValid = validateProductPrice();
    const isProductDiscountPriceValid = validateProductDiscountPrice();

    return isProductTitleValid && isProductBrandValid && isProductDescriptionValid && isProductSpecificationValid && isProductInformationValid && isProductDeliveryValid && isProductPriceValid && isProductDiscountPriceValid;
}

const validateUpdateProduct = () => {
    const isProductTitleValid = validateUpdateProductTitle();
    const isProductBrandValid = validateUpdateBrand();
    const isProductDescriptionValid = validateUpdateProductDescription();
    const isProductSpecificationValid = validateUpdateSpecification();
    const isProductInformationValid = validateUpdateInformation();
    const isProductDeliveryValid = validateUpdateDelivery();
    const isProductPriceValid = validateUpdatePrice();
    const isProductDiscountPriceValid = validateUpdateDiscountPrice();

    return isProductTitleValid && isProductBrandValid && isProductDescriptionValid && isProductSpecificationValid && isProductInformationValid && isProductDeliveryValid && isProductPriceValid && isProductDiscountPriceValid;
}

const validateProductImageForm = () => {
    const isProductImageAltValid = validateProductImageAlt();

    return isProductImageAltValid;
}

const validateProductImageUpdateForm = () => {
    const isProductImageAltValid = validateUpdateProductImageAlt();

    return isProductImageAltValid;
}

const validateProductSizeForm = () => {
    const isProductSizeValid = validateProductSize();
    const isProductPriceModifierValid = validatePriceModifier();

    return isProductSizeValid && isProductPriceModifierValid;
}

const validateProductSizeUpdateForm = () => {
    const isProductSizeValid = validateUpdateProductSize();
    const isProductPriceModifierValid = validateUpdateProductModifier();

    return isProductSizeValid && isProductPriceModifierValid;
}

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

const validateUpdateSupplier = () => {
    const isSupplierNameValid = validateUpdateSupplierName();
    const isCompanyNameValid = validateUpdateCompanyName();
    const isEmailValid = validateUpdateEmail();
    const isContactNumberValid = validateUpdateContactNumber();
    const isAddressValid = validateUpdateAddress();
    const isCityValid = validateUpdateCity();
    const isStateValid = validateUpdateState();
    const isPostalCodeValid = validateUpdatePostalCode();
    const isCountryValid = validateUpdateCountry();

    return isSupplierNameValid && isCompanyNameValid && isEmailValid && isContactNumberValid
        && isAddressValid && isCityValid && isStateValid && isPostalCodeValid && isCountryValid;
};

const validateRoleForm = () => {
    const isRoleValid = validateRole();
    const isRoleDescriptionValid = validateRoleDescription();

    return isRoleValid && isRoleDescriptionValid;
}

const validateRoomTypeForm = () => {
    const isRoomTypeValid = validateRoomType();
    const isRoomDescriptionValid = validateRoomTypeDescription();
    const isRoomCapacityValid = validateRoomCapacity();

    return isRoomTypeValid && isRoomDescriptionValid && isRoomCapacityValid;
}

const validateUpdateRoomTypeForm = () => {
    const isRoomTypeValid = validateUpdateRoomType();
    const isRoomDescriptionValid = validateUpdateRoomTypeDescription();
    const isRoomCapacityValid = validateUpdateRoomCapacity();

    return isRoomTypeValid && isRoomDescriptionValid && isRoomCapacityValid;
}

const validateFacilityTypeForm = () => {
    const isFacilityTypeValid = validateFacilityType();
    const isFacilityTypeIconValid = validateFacilityTypeIcon();

    return isFacilityTypeValid && isFacilityTypeIconValid;
};

const validateUpdateFacilityTypeForm = () => {
    const isFacilityTypeValid = validateUpdateFacilityType();
    const isFacilityTypeIconValid = validateUpdateFacilityTypeIcon();

    return isFacilityTypeValid && isFacilityTypeIconValid;
}

const validateFacilityForm = () => {
    const isFacilityValid = validateFacility();

    return isFacilityValid;
};

const validateFacilityUpdateForm = () => {
    const isFacilityValid = validateUpdateFacility();

    return isFacilityValid;
};

const validateRuleForm = () => {
    const isRuleTitleValid = validateRuleTitle();
    const isRuleValid = validateRule();
    const isRuleIconValid = validateRuleIcon();

    return isRuleTitleValid && isRuleValid && isRuleIconValid;
}

const validateRuleUpdateForm = () => {
    const isRuleTitleValid = validateUpdateRuleTitle();
    const isRuleValid = validateUpdateRule();
    const isRuleIconValid = validateUpdateRuleIcon();

    return isRuleTitleValid && isRuleValid && isRuleIconValid;
}

const validateProfileUpdateForm = () => {
    const isFirstnameValid = validateFirstName();
    const isLastnameValid = validateLastName();
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPhoneValid = validatePhone();

    return isFirstnameValid && isLastnameValid && isUsernameValid && isEmailValid && isPhoneValid;
}

const validateChangePasswordForm = () => {
    const isCurrentPasswordValid = validateOldPassword();
    const isNewPasswordValid = validateNewPassword();
    const isConfirmPasswordValid = validateConfirmPassword();

    return isCurrentPasswordValid && isNewPasswordValid && isConfirmPasswordValid;
}

// Individual validation functions
const validateProductType = () => {
    return validateField(
        "productTypeInput",
        "productTypeError",
        (input) => (!input ? "Type is required." : null)
    );
};

const validateUpdateProductTypeInput = () => {
    return validateField(
        "updateProductTypeInput",
        "updateProductTypeError",
        (input) => (!input ? "Type is required." : null)
    );
};

const validateDescription = () => {
    return validateField(
        "descriptionInput",
        "descriptionError",
        (input) => (!input ? "Description is required." : null)
    );
};

const validateProductDescription = () => {
    return validateField(
        "productDescriptionInput",
        "productDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
}

const validateUpdateProductDescription = () => {
    return validateField(
        "updateDescriptionInput",
        "updateDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
}

const validateProductTitle = () => {
    return validateField(
        "productTitleInput",
        "productTitleError",
        (input) => (!input ? "Title is required." : null)
    );
}

const validateUpdateProductTitle = () => {
    return validateField(
        "updateProductTitleInput",
        "updateProductTitleError",
        (input) => (!input ? "Title is required." : null)
    );
}

const validateProductBrand = () => {
    return validateField(
        "brandInput",
        "brandError",
        (input) => (!input ? "Brand is required." : null)
    );
}

const validateUpdateBrand = () => {
    return validateField(
        "updateBrandInput",
        "updateBrandError",
        (input) => (!input ? "Brand is required." : null)
    );
}

const validateProductSpecification = () => {
    return validateField(
        "specificationInput",
        "specificationError",
        (input) => (!input ? "Specification is required." : null)
    );
}

const validateUpdateSpecification = () => {
    return validateField(
        "updateSpecificationInput",
        "updateSpecificationError",
        (input) => (!input ? "Specification is required." : null)
    );
}

const validateProductInformation = () => {
    return validateField(
        "informationInput",
        "informationError",
        (input) => (!input ? "Information is required." : null)
    );
}

const validateUpdateInformation = () => {
    return validateField(
        "updateInformationInput",
        "updateInformationError",
        (input) => (!input ? "Information is required." : null)
    );
}

const validateProductDelivery = () => {
    return validateField(
        "deliveryInput",
        "deliveryError",
        (input) => (!input ? "Delivery is required." : null)
    );
}

const validateUpdateDelivery = () => {
    return validateField(
        "updateDeliveryInput",
        "updateDeliveryError",
        (input) => (!input ? "Delivery is required." : null)
    );
}

const validateProductPrice = () => {
    return validateField(
        "priceInput",
        "priceError",
        (input) => (!input ? "Price is required." : null)
    );
}

const validateUpdatePrice = () => {
    return validateField(
        "updatePriceInput",
        "updatePriceError",
        (input) => (!input ? "Price is required." : null)
    );
}

const validateProductDiscountPrice = () => {
    return validateField(
        "discountPriceInput",
        "discountPriceError",
        (input) => (!input ? "Discount price is required." : null)
    );
}

const validateUpdateDiscountPrice = () => {
    return validateField(
        "updateDiscountPriceInput",
        "updateDiscountPriceError",
        (input) => (!input ? "Discount price is required." : null)
    );
}

const validateProductSize = () => {
    return validateField(
        "sizeInput",
        "sizeError",
        (input) => (!input ? "Size is required." : null)
    );
}

const validateUpdateProductSize = () => {
    return validateField(
        "updateSizeInput",
        "updateSizeError",
        (input) => (!input ? "Size is required." : null)
    );
}

const validatePriceModifier = () => {
    return validateField(
        "priceModifierInput",
        "priceModifierError",
        (input) => (!input ? "Price modifier is required." : null)
    );
}

const validateUpdateProductModifier = () => {
    return validateField(
        "updatePriceModifierInput",
        "updatePriceModifierError",
        (input) => (!input ? "Price modifier is required." : null)
    );
}

const validateProductImageAlt = () => {
    return validateField(
        "productImageAltInput",
        "productImageAltError",
        (input) => (!input ? "Image alt is required." : null)
    );
}

const validateUpdateProductImageAlt = () => {
    return validateField(
        "updateProductImageAltInput",
        "updateProductImageAltError",
        (input) => (!input ? "Image alt is required." : null)
    );
}

const validateUpdateDescription = () => {
    return validateField(
        "updateProductTypeDescription",
        "updateProductTypeDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
};

const validateSupplierName = () => {
    return validateField(
        "supplierNameInput",
        "supplierNameError",
        (input) => (!input ? "Supplier name is required." : null)
    );
}

const validateUpdateSupplierName = () => {
    return validateField(
        "updateSupplierNameInput",
        "updateSupplierNameError",
        (input) => (!input ? "Supplier name is required." : null)
    );
}

const validateCompanyName = () => {
    return validateField(
        "companyNameInput",
        "companyNameError",
        (input) => (!input ? "Company name is required." : null)
    );
}

const validateUpdateCompanyName = () => {
    return validateField(
        "updateCompanyNameInput",
        "updateCompanyNameError",
        (input) => (!input ? "Company name is required." : null)
    );
}

const validateFirstName = () => {
    return validateField(
        "firstnameInput",
        "firstnameError",
        (input) => (!input ? "First name is required." : null)
    );
}

const validateLastName = () => {
    return validateField(
        "lastnameInput",
        "lastnameError",
        (input) => (!input ? "Last name is required." : null)
    );
}

const validateUsername = () => {
    return validateField(
        "usernameInput",
        "usernameError",
        (input) => (!input ? "Username is required." : null)
    );
}

const validateEmail = () => {
    return validateField(
        "emailInput",
        "emailError",
        (input) => (!input ? "Email is required." : null)
    )
}

const validateUpdateEmail = () => {
    return validateField(
        "updateEmailInput",
        "updateEmailError",
        (input) => (!input ? "Email is required." : null)
    );
}

const validatePhone = () => {
    return validateField(
        "phoneInput",
        "phoneError",
        (input) => (!input ? "Phone number is required." : null)
    )
}

const validateContactNumber = () => {
    return validateField(
        "contactNumberInput",
        "contactNumberError",
        (input) => (!input ? "Contact number is required." : null)
    )
}

const validateUpdateContactNumber = () => {
    return validateField(
        "updateContactNumberInput",
        "updateContactNumberError",
        (input) => (!input ? "Contact number is required." : null)
    );
}

const validateAddress = () => {
    return validateField(
        "addressInput",
        "addressError",
        (input) => (!input ? "Address is required." : null)
    )
}

const validateUpdateAddress = () => {
    return validateField(
        "updateAddressInput",
        "updateAddressError",
        (input) => (!input ? "Address is required." : null)
    );
}

const validateCity = () => {
    return validateField(
        "cityInput",
        "cityError",
        (input) => (!input ? "City is required." : null)
    )
}

const validateUpdateCity = () => {
    return validateField(
        "updateCityInput",
        "updateCityError",
        (input) => (!input ? "City is required." : null)
    );
}

const validateState = () => {
    return validateField(
        "stateInput",
        "stateError",
        (input) => (!input ? "State is required." : null)
    )
}

const validateUpdateState = () => {
    return validateField(
        "updateStateInput",
        "updateStateError",
        (input) => (!input ? "State is required." : null)
    );
}

const validatePostalCode = () => {
    return validateField(
        "postalCodeInput",
        "postalCodeError",
        (input) => (!input ? "Postal code is required." : null)
    )
}

const validateUpdatePostalCode = () => {
    return validateField(
        "updatePostalCodeInput",
        "updatePostalCodeError",
        (input) => (!input ? "Postal code is required." : null)
    );
}

const validateCountry = () => {
    return validateField(
        "countryInput",
        "countryError",
        (input) => (!input ? "Country is required." : null)
    )
}

const validateUpdateCountry = () => {
    return validateField(
        "updateCountryInput",
        "updateCountryError",
        (input) => (!input ? "Country is required." : null)
    );
}

const validateRole = () => {
    return validateField(
        "roleInput",
        "roleError",
        (input) => (!input ? "Role is required." : null)
    );
}

const validateRoleDescription = () => {
    return validateField(
        "roleDescriptionInput",
        "roleDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
}

const validateRoomType = () => {
    return validateField(
        "roomTypeInput",
        "roomTypeError",
        (input) => (!input ? "Room type is required." : null)
    );
}

const validateUpdateRoomType = () => {
    return validateField(
        "updateRoomTypeInput",
        "updateRoomTypeError",
        (input) => (!input ? "Room type is required." : null)
    );
}

const validateRoomTypeDescription = () => {
    return validateField(
        "roomTypeDescriptionInput",
        "roomTypeDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
}

const validateUpdateRoomTypeDescription = () => {
    return validateField(
        "updateRoomTypeDescriptionInput",
        "updateRoomTypeDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
}

const validateRoomCapacity = () => {
    return validateField(
        "roomCapacityInput",
        "roomCapacityError",
        (input) => (!input ? "Room capacity is required." : null)
    );
}

const validateUpdateRoomCapacity = () => {
    return validateField(
        "updateRoomCapacityInput",
        "updateRoomCapacityError",
        (input) => (!input ? "Room capacity is required." : null)
    );
}

const validateFacilityType = () => {
    return validateField(
        "facilityTypeInput",
        "facilityTypeError",
        (input) => (!input ? "Facility type is required." : null)
    );
}

const validateUpdateFacilityType = () => {
    return validateField(
        "updateFacilityTypeInput",
        "updateFacilityTypeError",
        (input) => (!input ? "Facility type is required." : null)
    );
}

const validateFacilityTypeIcon = () => {
    return validateField(
        "facilityTypeIconInput",
        "facilityTypeIconError",
        (input) => (!input ? "Facility type icon is required." : null)
    );
}

const validateUpdateFacilityTypeIcon = () => {
    return validateField(
        "updateFacilityTypeIconInput",
        "updateFacilityTypeIconError",
        (input) => (!input ? "Facility type icon is required." : null)
    );
}

const validateFacility = () => {
    return validateField(
        "facilityInput",
        "facilityError",
        (input) => (!input ? "Facility is required." : null)
    );
}

const validateUpdateFacility = () => {
    return validateField(
        "updateFacilityInput",
        "updateFacilityError",
        (input) => (!input ? "Facility is required." : null)
    );
}

const validateRuleTitle = () => {
    return validateField(
        "ruleTitleInput",
        "ruleTitleError",
        (input) => (!input ? "Title is required." : null)
    );
}

const validateUpdateRuleTitle = () => {
    return validateField(
        "updateRuleTitleInput",
        "updateRuleTitleError",
        (input) => (!input ? "Title is required." : null)
    );
}

const validateRule = () => {
    return validateField(
        "ruleInput",
        "ruleError",
        (input) => (!input ? "Rule is required." : null)
    );
}

const validateUpdateRule = () => {
    return validateField(
        "updateRuleInput",
        "updateRuleError",
        (input) => (!input ? "Rule is required." : null)
    );
}

const validateRuleIcon = () => {
    return validateField(
        "ruleIconInput",
        "ruleIconError",
        (input) => (!input ? "Rule icon is required." : null)
    );
}

const validateUpdateRuleIcon = () => {
    return validateField(
        "updateRuleIconInput",
        "updateRuleIconError",
        (input) => (!input ? "Rule icon is required." : null)
    );
}

function validateOldPassword() {
    const oldPassword = document.getElementById("oldPasswordInput").value.trim();
    const errorElement = document.getElementById("oldPasswordError");
    
    if (!oldPassword) {
        showError(errorElement, "Old password is required");
        return false;
    }
    hideError(errorElement);
    return true;
}

function validateNewPassword() {
    const newPassword = document.getElementById("newPasswordInput").value.trim();
    const errorElement = document.getElementById("newPasswordError");
    
    if (!newPassword) {
        showError(errorElement, "New password is required");
        return false;
    } else if (newPassword.length < 8) {
        showError(errorElement, "Must be at least 8 characters");
        return false;
    } else if (!/[A-Z]/.test(newPassword)) {
        showError(errorElement, "Must contain an uppercase letter");
        return false;
    } else if (!/[0-9]/.test(newPassword)) {
        showError(errorElement, "Must contain a number");
        return false;
    } else if (!/[^A-Za-z0-9]/.test(newPassword)) {
        showError(errorElement, "Must contain a special character");
        return false;
    }
    hideError(errorElement);
    return true;
}

function validateConfirmPassword() {
    const newPassword = document.getElementById("newPasswordInput").value.trim();
    const confirmPassword = document.getElementById("confirmPasswordInput").value.trim();
    const errorElement = document.getElementById("confirmPasswordError");
    
    if (!confirmPassword) {
        showError(errorElement, "Please confirm your password");
        return false;
    } else if (confirmPassword !== newPassword) {
        showError(errorElement, "Passwords don't match");
        return false;
    }
    hideError(errorElement);
    return true;
}

const passwordInputs = [
    { input: document.getElementById('oldPasswordInput'), toggle: document.getElementById('togglePassword') },
    { input: document.getElementById('newPasswordInput'), toggle: document.getElementById('togglePassword2') },
    { input: document.getElementById('confirmPasswordInput'), toggle: document.getElementById('togglePassword3') },
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




