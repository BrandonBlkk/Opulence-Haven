import { showAlert, validateField } from './alertFunc.js';

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
            btn.addEventListener('click', function() {
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
        setTimeout(() => {
            window.location.href = 'AddProductType.php';
        }, 5000);
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
            btn.addEventListener('click', function() {
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
    document.getElementById("productSizeInput").addEventListener("keyup", validateProductSize);
    document.getElementById("stockInput").addEventListener("keyup", validateProductStock);

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
            btn.addEventListener('click', function() {
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
                            document.getElementById('updateimg1').src = data.product.AdminImg1;
                            document.getElementById('updateimg2').src = data.product.AdminImg2;
                            document.getElementById('updateimg3').src = data.product.AdminImg3;
                            document.querySelector('[name="updatebrand"]').value = data.product.Brand;
                            document.querySelector('[name="updatedescription"]').value = data.product.Description;
                            document.querySelector('[name="updatespecification"]').value = data.product.Specification;
                            document.querySelector('[name="updateinformation"]').value = data.product.Information;
                            document.querySelector('[name="updatedelivery"]').value = data.product.DeliveryInfo;
                            document.querySelector('[name="updateprice"]').value = data.product.Price;
                            document.querySelector('[name="updatediscountPrice"]').value = data.product.DiscountPrice;
                            document.querySelector('[name="updateproductsize"]').value = data.product.ProductSize;
                            document.querySelector('[name="updatestock"]').value = data.product.Stock;
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
        // document.getElementById("updateProductTypeInput").addEventListener("keyup", validateUpdateProductTypeInput);
        // document.getElementById("updateProductTypeDescription").addEventListener("keyup", validateUpdateDescription);

        // const updateProductTypeForm = document.getElementById("updateProductTypeForm");
        // if (updateProductTypeForm) {
        //     updateProductTypeForm.addEventListener("submit", (e) => {
        //         if (!validateUpdateProductType()) {
        //             e.preventDefault();
        //         }
        //     });
        // }
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
        setTimeout(() => {
            window.location.href = 'AddRoomType.php';
        }, 5000);
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
            btn.addEventListener('click', function() {
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

// Admin Details Modal
document.addEventListener('DOMContentLoaded', () => {
    const alertMessage = document.getElementById('alertMessage').value;
    const updateRoomTypeSuccess = document.getElementById('updateRoomTypeSuccess').value === 'true';

    // Get all details buttons
    const editsBtns = document.querySelectorAll('.edits-btn');

    if (updateRoomTypeModal && updateRoomTypeModalCancelBtn && editsBtns) {
        // Add click event to each button
        editsBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const adminId = this.getAttribute('data-admin-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                // Fetch admin details
                fetch(`../Admin/RoleManagement.php?action=getAdminDetails&id=${adminId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with product type data
                            document.getElementById('updateRoomTypeID').value = adminId;
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
                            document.getElementById('adminDeleteProfile').src = data.admin.AdminProfile;
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
            showAlert('The admin accouont has been successfully deleted.');
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
            btn.addEventListener('click', function() {
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

// Reset Password and Profile Update Form Validation
document.addEventListener("DOMContentLoaded", () => {
    const alertBox = document.getElementById('alertBox');
    const alertText = document.getElementById('alertText');
    const alertMessage = document.getElementById('alertMessage').value;
    const profileUpdate = document.getElementById('profileUpdate').value === 'true';

    if (profileUpdate)  {
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
    const isProductSizeValid = validateProductSize();
    const isProductStockValid = validateProductStock();

    return isProductTitleValid && isProductBrandValid && isProductDescriptionValid && isProductSpecificationValid && isProductInformationValid && isProductDeliveryValid && isProductPriceValid && isProductDiscountPriceValid && isProductSizeValid && isProductStockValid;
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

const validateProfileUpdateForm = () => {
    const isFirstnameValid = validateFirstName();
    const isLastnameValid = validateLastName();
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPhoneValid = validatePhone();

    return isFirstnameValid && isLastnameValid && isUsernameValid && isEmailValid && isPhoneValid;
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

const validateProductTitle = () => {
    return validateField(
        "productTitleInput",
        "productTitleError",
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

const validateProductSpecification = () => {
    return validateField(
        "specificationInput",
        "specificationError",
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

const validateProductDelivery = () => {
    return validateField(
        "deliveryInput",
        "deliveryError",
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

const validateProductDiscountPrice = () => {
    return validateField(
        "discountPriceInput",
        "discountPriceError",
        (input) => (!input ? "Discount price is required." : null)
    );
}

const validateProductSize = () => {
    return validateField(
        "productSizeInput",
        "productSizeError",
        (input) => (!input ? "Size is required." : null)
    );
}

const validateProductStock = () => {
    return validateField(
        "stockInput",
        "stockError",
        (input) => (!input ? "Stock is required." : null)
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




