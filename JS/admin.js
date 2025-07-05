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
                window.location.href = 'AdminSignin.php';
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

// Supplier Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Supplier Modal Elements
    const addSupplierModal = document.getElementById('addSupplierModal');
    const addSupplierBtn = document.getElementById('addSupplierBtn');
    const addSupplierCancelBtn = document.getElementById('addSupplierCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2'); 

    // Update Supplier Modal Elements
    const updateSupplierModal = document.getElementById('updateSupplierModal');
    const updateSupplierModalCancelBtn = document.getElementById('supplierModalCancelBtn');

    // Delete Supplier Modal Elements
    const supplierConfirmDeleteModal = document.getElementById('supplierConfirmDeleteModal');
    const supplierCancelDeleteBtn = document.getElementById('supplierCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('supplierpage') || 1;
    const currentSearch = urlParams.get('supplier_search') || '';
    const currentSort = urlParams.get('sort') || 'random';

    // Function to close the add modal
    const closeModal = () => {
        addSupplierModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['supplierNameError', 'companyNameError', 'emailError', 'contactNumberError', 'addressError', 'cityError', 'stateError', 'postalCodeError', 'countryError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render suppliers with current pagination
    const fetchAndRenderSuppliers = () => {
        let fetchUrl = `../Admin/AddSupplier.php?supplierpage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&supplier_search=${encodeURIComponent(currentSearch)}`;
        }
        if (currentSort !== 'random') {
            fetchUrl += `&sort=${currentSort}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract the table body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Reattach event listeners to the new rows
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                }
            })
            .catch(error => console.error('Error fetching suppliers:', error));
    };

    // Function to fetch product type name by ID
    const fetchProductTypeName = async (productTypeId) => {
        try {
            const response = await fetch(`../Admin/AddSupplier.php?action=getProductTypeName&id=${productTypeId}`);
            const data = await response.json();
            return data.success ? data.productType : 'None';
        } catch (error) {
            console.error('Error fetching product type:', error);
            return 'None';
        }
    };

    // Function to update product type names in existing rows
    const updateProductTypeNames = async () => {
        const rows = document.querySelectorAll('tbody tr');
        for (const row of rows) {
            const productTypeCell = row.querySelector('td:nth-child(3)');
            if (productTypeCell) {
                const productTypeId = productTypeCell.getAttribute('data-product-type-id');
                if (productTypeId) {
                    const productTypeName = await fetchProductTypeName(productTypeId);
                    productTypeCell.textContent = productTypeName;
                }
            }
        }
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const supplierId = this.getAttribute('data-supplier-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddSupplier.php?action=getSupplierDetails&id=${supplierId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
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
                            updateSupplierModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load supplier details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const supplierId = this.getAttribute('data-supplier-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddSupplier.php?action=getSupplierDetails&id=${supplierId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteSupplierID').value = supplierId;
                            document.getElementById('supplierDeleteName').textContent = data.supplier.SupplierName;
                            supplierConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load supplier details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Supplier Modal
    if (addSupplierModal && addSupplierBtn && addSupplierCancelBtn) {
        addSupplierBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addSupplierModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addSupplierCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('supplierNameInput').value = '';
            document.getElementById('companyNameInput').value = '';
            document.getElementById('emailInput').value = '';
            document.getElementById('contactNumberInput').value = '';
            document.getElementById('addressInput').value = '';
            document.getElementById('cityInput').value = '';
            document.getElementById('stateInput').value = '';
            document.getElementById('postalCodeInput').value = '';
            document.getElementById('countryInput').value = '';
        });
    }

    // Supplier Form Submission
    document.getElementById("supplierNameInput")?.addEventListener("keyup", validateSupplierName);
    document.getElementById("companyNameInput")?.addEventListener("keyup", validateCompanyName);
    document.getElementById("emailInput")?.addEventListener("keyup", validateEmail);
    document.getElementById("contactNumberInput")?.addEventListener("keyup", validateContactNumber);
    document.getElementById("addressInput")?.addEventListener("keyup", validateAddress);
    document.getElementById("cityInput")?.addEventListener("keyup", validateCity);
    document.getElementById("stateInput")?.addEventListener("keyup", validateState);
    document.getElementById("postalCodeInput")?.addEventListener("keyup", validatePostalCode);
    document.getElementById("countryInput")?.addEventListener("keyup", validateCountry);
    
    const supplierForm = document.getElementById("supplierForm");
    if (supplierForm) {
        supplierForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateSupplierForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(supplierForm);
            formData.append('addsupplier', true);

            fetch('AddSupplier.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    // Clear form fields
                    document.getElementById('supplierNameInput').value = '';
                    document.getElementById('companyNameInput').value = '';
                    document.getElementById('emailInput').value = '';
                    document.getElementById('contactNumberInput').value = '';
                    document.getElementById('addressInput').value = '';
                    document.getElementById('cityInput').value = '';
                    document.getElementById('stateInput').value = '';
                    document.getElementById('postalCodeInput').value = '';
                    document.getElementById('countryInput').value = '';
                    
                    supplierForm.reset();
                    closeModal();
                    
                    // Fetch and render the updated suppliers with current pagination
                    fetchAndRenderSuppliers();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Supplier Modal 
    if (updateSupplierModal && updateSupplierModalCancelBtn) {
        updateSupplierModalCancelBtn.addEventListener('click', () => {
            updateSupplierModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateSupplierNameError', 'updateCompanyNameError', 'updateEmailError', 'updateContactNumberError', 'updateAddressError', 'updateCityError', 'updateStateError', 'updatePostalCodeError', 'updateCountryError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateSupplierNameInput")?.addEventListener("keyup", validateUpdateSupplierName);
        document.getElementById("updateCompanyNameInput")?.addEventListener("keyup", validateUpdateCompanyName);
        document.getElementById("updateEmailInput")?.addEventListener("keyup", validateUpdateEmail);
        document.getElementById("updateContactNumberInput")?.addEventListener("keyup", validateUpdateContactNumber);
        document.getElementById("updateAddressInput")?.addEventListener("keyup", validateUpdateAddress);
        document.getElementById("updateCityInput")?.addEventListener("keyup", validateUpdateCity);
        document.getElementById("updateStateInput")?.addEventListener("keyup", validateUpdateState);
        document.getElementById("updatePostalCodeInput")?.addEventListener("keyup", validateUpdatePostalCode);
        document.getElementById("updateCountryInput")?.addEventListener("keyup", validateUpdateCountry);

        const updateSupplierForm = document.getElementById("updateSupplierForm");
        if (updateSupplierForm) {
            updateSupplierForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateUpdateSupplier()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateSupplierForm);
                formData.append('editsupplier', true);

                fetch('AddSupplier.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        // Clear form and close modal
                        document.getElementById('updateSupplierNameInput').value = '';
                        document.getElementById('updateCompanyNameInput').value = '';
                        document.getElementById('updateEmailInput').value = '';
                        document.getElementById('updateContactNumberInput').value = '';
                        document.getElementById('updateAddressInput').value = '';
                        document.getElementById('updateCityInput').value = '';
                        document.getElementById('updateStateInput').value = '';
                        document.getElementById('updatePostalCodeInput').value = '';
                        document.getElementById('updateCountryInput').value = '';
                        updateSupplierForm.reset();
                        
                        updateSupplierModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        // Fetch and render the updated suppliers with current pagination
                        fetchAndRenderSuppliers();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Supplier Modal
    if (supplierConfirmDeleteModal && supplierCancelDeleteBtn) {
        supplierCancelDeleteBtn.addEventListener('click', () => {
            supplierConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const supplierDeleteForm = document.getElementById('supplierDeleteForm');
        if (supplierDeleteForm) {
            supplierDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(supplierDeleteForm);
                formData.append('deletesupplier', true);

                fetch('../Admin/AddSupplier.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        supplierConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        
                        // Fetch and render the updated suppliers with current pagination
                        fetchAndRenderSuppliers();
                        
                        showAlert('The supplier has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete supplier.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load and update product type names
    initializeExistingRows();
    updateProductTypeNames();
});

// Product Type Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Product Type Modal Elements
    const addProductTypeModal = document.getElementById('addProductTypeModal');
    const addProductTypeBtn = document.getElementById('addProductTypeBtn');
    const addProductTypeCancelBtn = document.getElementById('addProductTypeCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2'); 

    // Update Product Type Modal Elements
    const updateProductTypeModal = document.getElementById('updateProductTypeModal');
    const updateProductTypeModalCancelBtn = document.getElementById('updateProductTypeModalCancelBtn');

    // Delete Product Type Modal Elements
    const productTypeConfirmDeleteModal = document.getElementById('productTypeConfirmDeleteModal');
    const productTypeCancelDeleteBtn = document.getElementById('productTypeCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('producttypepage') || 1;
    const currentSearch = urlParams.get('producttype_search') || '';

    // Function to close the add modal
    const closeModal = () => {
        addProductTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['productTypeError', 'descriptionError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render product types with current pagination
    const fetchAndRenderProductTypes = () => {
        let fetchUrl = `../Admin/AddProductType.php?producttypepage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&producttype_search=${encodeURIComponent(currentSearch)}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract the table body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Reattach event listeners to the new rows
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                    
                    // Update pagination links to maintain current page
                    updatePaginationLinks();
                }
            })
            .catch(error => console.error('Error fetching product types:', error));
    };

    // Function to update pagination links to maintain current page
    const updatePaginationLinks = () => {
        const paginationLinks = document.querySelectorAll('.pagination-link');
        paginationLinks.forEach(link => {
            const href = new URL(link.href);
            const searchParams = new URLSearchParams(href.search);
            
            // Update or add producttypepage parameter
            searchParams.set('producttypepage', currentPage);
            
            // Maintain search parameter if it exists
            if (currentSearch) {
                searchParams.set('producttype_search', currentSearch);
            } else {
                searchParams.delete('producttype_search');
            }
            
            href.search = searchParams.toString();
            link.href = href.toString();
        });
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const productTypeId = this.getAttribute('data-producttype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddProductType.php?action=getProductTypeDetails&id=${productTypeId}&producttypepage=${currentPage}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateProductTypeID').value = productTypeId;
                            document.querySelector('[name="updateproducttype"]').value = data.producttype.ProductType;
                            document.querySelector('[name="updatedescription"]').value = data.producttype.Description;
                            updateProductTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const productTypeId = this.getAttribute('data-producttype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddProductType.php?action=getProductTypeDetails&id=${productTypeId}&producttypepage=${currentPage}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductTypeID').value = productTypeId;
                            document.getElementById('productTypeDeleteName').textContent = data.producttype.ProductType;
                            productTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Product Type Modal
    if (addProductTypeModal && addProductTypeBtn && addProductTypeCancelBtn) {
        addProductTypeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addProductTypeCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('productTypeInput').value = '';
            document.getElementById('descriptionInput').value = '';
        });
    }

    // Product Type Form Submission
    document.getElementById("productTypeInput")?.addEventListener("keyup", validateProductType);
    document.getElementById("descriptionInput")?.addEventListener("keyup", validateDescription);
    
    const productTypeForm = document.getElementById("productTypeForm");
    if (productTypeForm) {
        productTypeForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateProductTypeForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(productTypeForm);
            formData.append('addproducttype', true);

            fetch('AddProductType.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('productTypeInput').value = '';
                    document.getElementById('descriptionInput').value = '';
                    productTypeForm.reset();
                    closeModal();
                    
                    // Fetch and render the updated product types with current pagination
                    fetchAndRenderProductTypes();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Product Type Modal 
    if (updateProductTypeModal && updateProductTypeModalCancelBtn) {
        updateProductTypeModalCancelBtn.addEventListener('click', () => {
            updateProductTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateProductTypeError', 'updateDescriptionError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateProductTypeInput")?.addEventListener("keyup", validateUpdateProductType);
        document.getElementById("updateProductTypeDescription")?.addEventListener("keyup", validateUpdateDescription);

        const updateProductTypeForm = document.getElementById("updateProductTypeForm");
        if (updateProductTypeForm) {
            updateProductTypeForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateUpdateProductTypeForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateProductTypeForm);
                formData.append('editproducttype', true);
                // Include current page in the form data
                formData.append('producttypepage', currentPage);

                fetch('AddProductType.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        // Clear form and close modal
                        document.getElementById('updateProductTypeInput').value = '';
                        document.getElementById('updateProductTypeDescription').value = '';
                        updateProductTypeForm.reset();
                        
                        updateProductTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        // Fetch and render the updated product types with current pagination
                        fetchAndRenderProductTypes();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Product Type Modal
    if (productTypeConfirmDeleteModal && productTypeCancelDeleteBtn) {
        productTypeCancelDeleteBtn.addEventListener('click', () => {
            productTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const productTypeDeleteForm = document.getElementById('productTypeDeleteForm');
        if (productTypeDeleteForm) {
            productTypeDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(productTypeDeleteForm);
                formData.append('deleteproducttype', true);
                // Include current page in the form data
                formData.append('producttypepage', currentPage);

                fetch('../Admin/AddProductType.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        productTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        
                        // Fetch and render the updated product types with current pagination
                        fetchAndRenderProductTypes();
                        
                        showAlert('The product type has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete product type.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load
    initializeExistingRows();
});

// Product Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Product Modal Elements
    const addProductModal = document.getElementById('addProductModal');
    const addProductBtn = document.getElementById('addProductBtn');
    const addProductCancelBtn = document.getElementById('addProductCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2'); 

    // Update Product Modal Elements
    const updateProductModal = document.getElementById('updateProductModal');
    const updateProductModalCancelBtn = document.getElementById('updateProductModalCancelBtn');

    // Delete Product Modal Elements
    const productConfirmDeleteModal = document.getElementById('productConfirmDeleteModal');
    const productCancelDeleteBtn = document.getElementById('productCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('productpage') || 1;
    const currentSearch = urlParams.get('product_search') || '';
    const currentSort = urlParams.get('sort') || 'random';

    // Function to close the add modal
    const closeModal = () => {
        addProductModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['productTitleError', 'brandError', 'productDescriptionError', 'specificationError', 
                       'informationError', 'deliveryError', 'priceError', 'discountPriceError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render products with current pagination
    const fetchAndRenderProducts = () => {
        let fetchUrl = `../Admin/AddProduct.php?productpage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&product_search=${encodeURIComponent(currentSearch)}`;
        }
        if (currentSort !== 'random') {
            fetchUrl += `&sort=${currentSort}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract the table body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Reattach event listeners to the new rows
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                }
            })
            .catch(error => console.error('Error fetching products:', error));
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddProduct.php?action=getProductDetails&id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
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
                            updateProductModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddProduct.php?action=getProductDetails&id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductID').value = productId;
                            document.getElementById('productDeleteName').textContent = data.product.Title;
                            productConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Product Modal
    if (addProductModal && addProductBtn && addProductCancelBtn) {
        addProductBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addProductCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('productTitleInput').value = '';
        });
    }

    // Product Form Submission
    document.getElementById("productTitleInput")?.addEventListener("keyup", validateProductTitle);
    document.getElementById("brandInput")?.addEventListener("keyup", validateProductBrand);
    document.getElementById("productDescriptionInput")?.addEventListener("keyup", validateProductDescription);
    document.getElementById("specificationInput")?.addEventListener("keyup", validateProductSpecification);
    document.getElementById("informationInput")?.addEventListener("keyup", validateProductInformation);
    document.getElementById("deliveryInput")?.addEventListener("keyup", validateProductDelivery);
    document.getElementById("priceInput")?.addEventListener("keyup", validateProductPrice);
    document.getElementById("discountPriceInput")?.addEventListener("keyup", validateProductDiscountPrice);
    
    const productForm = document.getElementById("productForm");
    if (productForm) {
        productForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateProductForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(productForm);
            formData.append('addproduct', true);

            fetch('AddProduct.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('productTitleInput').value = '';
                    productForm.reset();
                    closeModal();
                    
                    // Fetch and render the updated products with current pagination
                    fetchAndRenderProducts();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Product Modal 
    if (updateProductModal && updateProductModalCancelBtn) {
        updateProductModalCancelBtn.addEventListener('click', () => {
            updateProductModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateProductTitleError', 'updateBrandError', 'updateDescriptionError', 
                          'updateSpecificationError', 'updateInformationError', 'updateDeliveryError', 
                          'updatePriceError', 'updateDiscountPriceError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateProductTitleInput")?.addEventListener("keyup", validateUpdateProductTitle);
        document.getElementById("updateBrandInput")?.addEventListener("keyup", validateUpdateBrand);
        document.getElementById("updateDescriptionInput")?.addEventListener("keyup", validateUpdateProductDescription);
        document.getElementById("updateSpecificationInput")?.addEventListener("keyup", validateUpdateSpecification);
        document.getElementById("updateInformationInput")?.addEventListener("keyup", validateUpdateInformation);
        document.getElementById("updateDeliveryInput")?.addEventListener("keyup", validateUpdateDelivery);
        document.getElementById("updatePriceInput")?.addEventListener("keyup", validateUpdatePrice);
        document.getElementById("updateDiscountPriceInput")?.addEventListener("keyup", validateUpdateDiscountPrice);

        const updateProductForm = document.getElementById("updateProductForm");
        if (updateProductForm) {
            updateProductForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateProductUpdateForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateProductForm);
                formData.append('editproduct', true);

                fetch('AddProduct.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        // Clear form and close modal
                        document.getElementById('updateProductTitleInput').value = '';
                        updateProductForm.reset();
                        
                        updateProductModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        // Fetch and render the updated products with current pagination
                        fetchAndRenderProducts();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Product Modal
    if (productConfirmDeleteModal && productCancelDeleteBtn) {
        productCancelDeleteBtn.addEventListener('click', () => {
            productConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const productDeleteForm = document.getElementById('productDeleteForm');
        if (productDeleteForm) {
            productDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(productDeleteForm);
                formData.append('deleteproduct', true);

                fetch('../Admin/AddProduct.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        productConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        
                        // Fetch and render the updated products with current pagination
                        fetchAndRenderProducts();
                        
                        showAlert('The product has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete product.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load
    initializeExistingRows();
});

// Room Type Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Room Type Modal Elements
    const addRoomTypeModal = document.getElementById('addRoomTypeModal');
    const addRoomTypeBtn = document.getElementById('addRoomTypeBtn');
    const addRoomTypeCancelBtn = document.getElementById('addRoomTypeCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    // Update Room Type Modal Elements
    const updateRoomTypeModal = document.getElementById('updateRoomTypeModal');
    const updateRoomTypeModalCancelBtn = document.getElementById('updateRoomTypeModalCancelBtn');

    // Delete Room Type Modal Elements
    const roomTypeConfirmDeleteModal = document.getElementById('roomTypeConfirmDeleteModal');
    const roomTypeCancelDeleteBtn = document.getElementById('roomTypeCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('roomtypepage') || 1;
    const currentSearch = urlParams.get('roomtype_search') || '';

    // Function to close the add modal
    const closeModal = () => {
        addRoomTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['roomTypeError', 'roomTypeDescriptionError', 'roomCapacityError', 'roomPriceError', 'roomQuantityError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render room types with current pagination
    const fetchAndRenderRoomTypes = () => {
        let fetchUrl = `../Admin/AddRoomType.php?roomtypepage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&roomtype_search=${encodeURIComponent(currentSearch)}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                }
            })
            .catch(error => console.error('Error fetching room types:', error));
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const roomTypeId = this.getAttribute('data-roomtype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddRoomType.php?action=getRoomTypeDetails&id=${roomTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateRoomTypeID').value = roomTypeId;
                            document.getElementById('updateRoomTypeInput').value = data.roomtype.RoomType;
                            document.getElementById('updateRoomTypeDescriptionInput').value = data.roomtype.RoomDescription;
                            document.getElementById('updateRoomCapacityInput').value = data.roomtype.RoomCapacity;
                            updateRoomTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load room type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const roomTypeId = this.getAttribute('data-roomtype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddRoomType.php?action=getRoomTypeDetails&id=${roomTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteRoomTypeID').value = roomTypeId;
                            document.getElementById('roomTypeDeleteName').textContent = data.roomtype.RoomType;
                            roomTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load room type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Room Type Modal
    if (addRoomTypeModal && addRoomTypeBtn && addRoomTypeCancelBtn) {
        addRoomTypeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRoomTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addRoomTypeCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('roomTypeInput').value = '';
            document.getElementById('roomTypeDescriptionInput').value = '';
            document.getElementById('roomCapacityInput').value = '';
            document.getElementById('roomPriceInput').value = '';
            document.getElementById('roomQuantityInput').value = '';
        });
    }

    // Room Type Form Submission
    document.getElementById("roomTypeInput")?.addEventListener("keyup", validateRoomType);
    document.getElementById("roomTypeDescriptionInput")?.addEventListener("keyup", validateRoomTypeDescription);
    document.getElementById("roomCapacityInput")?.addEventListener("keyup", validateRoomCapacity);
    document.getElementById("roomPriceInput")?.addEventListener("keyup", validateRoomPrice);
    document.getElementById("roomQuantityInput")?.addEventListener("keyup", validateRoomQuantity);
    
    const roomTypeForm = document.getElementById("roomTypeForm");
    if (roomTypeForm) {
        roomTypeForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateRoomTypeForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(roomTypeForm);
            formData.append('addroomtype', true);

            fetch('AddRoomType.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('roomTypeInput').value = '';
                    document.getElementById('roomTypeDescriptionInput').value = '';
                    document.getElementById('roomCapacityInput').value = '';
                    document.getElementById('roomPriceInput').value = '';
                    document.getElementById('roomQuantityInput').value = '';
                    roomTypeForm.reset();
                    closeModal();
                    fetchAndRenderRoomTypes();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Room Type Modal 
    if (updateRoomTypeModal && updateRoomTypeModalCancelBtn) {
        updateRoomTypeModalCancelBtn.addEventListener('click', () => {
            updateRoomTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateRoomTypeError', 'updateRoomTypeDescriptionError', 'updateRoomCapacityError', 'updateRoomPriceError', 'updateRoomQuantityError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateRoomTypeInput")?.addEventListener("keyup", validateUpdateRoomType);
        document.getElementById("updateRoomTypeDescriptionInput")?.addEventListener("keyup", validateUpdateRoomTypeDescription);
        document.getElementById("updateRoomCapacityInput")?.addEventListener("keyup", validateUpdateRoomCapacity);
        // document.getElementById("updateRoomPriceInput")?.addEventListener("keyup", validateUpdateRoomPrice);
        // document.getElementById("updateRoomQuantityInput")?.addEventListener("keyup", validateUpdateRoomQuantity);

        const updateRoomTypeForm = document.getElementById("updateRoomTypeForm");
        if (updateRoomTypeForm) {
            updateRoomTypeForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateUpdateRoomTypeForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateRoomTypeForm);
                formData.append('editroomtype', true);

                fetch('AddRoomType.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message);

                    if (data.success) {
                        updateRoomTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderRoomTypes();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Room Type Modal
    if (roomTypeConfirmDeleteModal && roomTypeCancelDeleteBtn) {
        roomTypeCancelDeleteBtn.addEventListener('click', () => {
            roomTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const roomTypeDeleteForm = document.getElementById('roomTypeDeleteForm');
        if (roomTypeDeleteForm) {
            roomTypeDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(roomTypeDeleteForm);
                formData.append('deleteroomtype', true);

                fetch('../Admin/AddRoomType.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        roomTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderRoomTypes();
                        showAlert('The room type has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete room type.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load
    initializeExistingRows();
});

// Room Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Room Modal Elements
    const addRoomModal = document.getElementById('addRoomModal');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const addRoomCancelBtn = document.getElementById('addRoomCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    // Update Room Modal Elements
    const updateRoomModal = document.getElementById('updateRoomModal');
    const updateRoomModalCancelBtn = document.getElementById('updateRoomModalCancelBtn');

    // Delete Room Modal Elements
    const roomConfirmDeleteModal = document.getElementById('roomConfirmDeleteModal');
    const roomCancelDeleteBtn = document.getElementById('roomCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('roompage') || 1;
    const currentSearch = urlParams.get('room_search') || '';

    // Function to close the add modal
    const closeModal = () => {
        addRoomModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['roomNameError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render rooms with current pagination
    const fetchAndRenderRooms = () => {
        let fetchUrl = `../Admin/AddRoom.php?roompage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&room_search=${encodeURIComponent(currentSearch)}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                    
                    // Update pagination links to maintain current page
                    updatePaginationLinks();
                }
            })
            .catch(error => console.error('Error fetching rooms:', error));
    };

    // Function to update pagination links to maintain current page
    const updatePaginationLinks = () => {
        const paginationLinks = document.querySelectorAll('.pagination-link');
        paginationLinks.forEach(link => {
            const href = new URL(link.href);
            const searchParams = new URLSearchParams(href.search);
            
            searchParams.set('roompage', currentPage);
            
            if (currentSearch) {
                searchParams.set('room_search', currentSearch);
            } else {
                searchParams.delete('room_search');
            }
            
            href.search = searchParams.toString();
            link.href = href.toString();
        });
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const roomId = this.getAttribute('data-room-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddRoom.php?action=getRoomDetails&id=${roomId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateRoomID').value = roomId;
                            document.querySelector('[name="updateroomname"]').value = data.room.RoomName;
                            document.querySelector('[name="updateroomstatus"]').value = data.room.RoomStatus;
                            document.querySelector('[name="updateroomtype"]').value = data.room.RoomTypeID;
                            updateRoomModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load room details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const roomId = this.getAttribute('data-room-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddRoom.php?action=getRoomDetails&id=${roomId}&roompage=${currentPage}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteRoomID').value = roomId;
                            document.getElementById('roomDeleteName').textContent = data.room.RoomName;
                            roomConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load room details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Room Modal
    if (addRoomModal && addRoomBtn && addRoomCancelBtn) {
        addRoomBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRoomModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addRoomCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('roomNameInput').value = '';
        });
    }

    // Room Form Submission
    document.getElementById("roomNameInput")?.addEventListener("keyup", validateRoomName);
    
    const roomForm = document.getElementById("roomForm");
    if (roomForm) {
        roomForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateRoomForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(roomForm);
            formData.append('addroom', true);

            fetch('AddRoom.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('roomNameInput').value = '';
                    roomForm.reset();
                    closeModal();
                    fetchAndRenderRooms();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Room Modal 
    if (updateRoomModal && updateRoomModalCancelBtn) {
        updateRoomModalCancelBtn.addEventListener('click', () => {
            updateRoomModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateRoomNameError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateRoomNameInput")?.addEventListener("keyup", validateUpdateRoomName);

        const updateRoomForm = document.getElementById("updateRoomForm");
        if (updateRoomForm) {
            updateRoomForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateRoomUpdateForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateRoomForm);
                formData.append('editroom', true);
                formData.append('roompage', currentPage);

                fetch('AddRoom.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        updateRoomModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderRooms();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Room Modal
    if (roomConfirmDeleteModal && roomCancelDeleteBtn) {
        roomCancelDeleteBtn.addEventListener('click', () => {
            roomConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const roomDeleteForm = document.getElementById('roomDeleteForm');
        if (roomDeleteForm) {
            roomDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(roomDeleteForm);
                formData.append('deleteroom', true);
                formData.append('roompage', currentPage);

                fetch('../Admin/AddRoom.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        roomConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderRooms();
                        showAlert('The room has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete room.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load
    initializeExistingRows();
});

// Facility Type Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Facility Type Modal Elements
    const addFacilityTypeModal = document.getElementById('addFacilityTypeModal');
    const addFacilityTypeBtn = document.getElementById('addFacilityTypeBtn');
    const addFacilityTypeCancelBtn = document.getElementById('addFacilityTypeCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2'); 

    // Update Facility Type Modal Elements
    const updateFacilityTypeModal = document.getElementById('updateFacilityTypeModal');
    const updateFacilityTypeModalCancelBtn = document.getElementById('updateFacilityTypeModalCancelBtn');

    // Delete Facility Type Modal Elements
    const facilityTypeConfirmDeleteModal = document.getElementById('facilityTypeConfirmDeleteModal');
    const facilityTypeCancelDeleteBtn = document.getElementById('facilityTypeCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('facilitytypepage') || 1;
    const currentSearch = urlParams.get('facilitytype_search') || '';

    // Function to close the add modal
    const closeModal = () => {
        addFacilityTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['facilityTypeError', 'facilityTypeIconError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render facility types with current pagination
    const fetchAndRenderFacilityTypes = () => {
        let fetchUrl = `../Admin/AddFacilityType.php?facilitytypepage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&facilitytype_search=${encodeURIComponent(currentSearch)}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract the table body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Reattach event listeners to the new rows
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                }
            })
            .catch(error => console.error('Error fetching facility types:', error));
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const facilityTypeId = this.getAttribute('data-facilitytype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddFacilityType.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateFacilityTypeID').value = facilityTypeId;
                            document.querySelector('[name="updatefacilitytype"]').value = data.facilitytype.FacilityType;
                            document.querySelector('[name="updatefacilitytypeicon"]').value = data.facilitytype.FacilityTypeIcon;
                            document.querySelector('[name="updatefacilitytypeiconsize"]').value = data.facilitytype.IconSize;
                            updateFacilityTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const facilityTypeId = this.getAttribute('data-facilitytype-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddFacilityType.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteFacilityTypeID').value = facilityTypeId;
                            document.getElementById('facilityTypeDeleteName').textContent = data.facilitytype.FacilityType;
                            facilityTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility type details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Facility Type Modal
    if (addFacilityTypeModal && addFacilityTypeBtn && addFacilityTypeCancelBtn) {
        addFacilityTypeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addFacilityTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addFacilityTypeCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('facilityTypeInput').value = '';
            document.getElementById('facilityTypeIconInput').value = '';
        });
    }

    // Facility Type Form Submission
    document.getElementById("facilityTypeInput")?.addEventListener("keyup", validateFacilityType);
    document.getElementById("facilityTypeIconInput")?.addEventListener("keyup", validateFacilityTypeIcon);
    
    const facilityTypeForm = document.getElementById("facilityTypeForm");
    if (facilityTypeForm) {
        facilityTypeForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateFacilityTypeForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(facilityTypeForm);
            formData.append('addfacilitytype', true);

            fetch('AddFacilityType.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('facilityTypeInput').value = '';
                    document.getElementById('facilityTypeIconInput').value = '';
                    facilityTypeForm.reset();
                    closeModal();
                    
                    // Fetch and render the updated facility types with current pagination
                    fetchAndRenderFacilityTypes();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Facility Type Modal 
    if (updateFacilityTypeModal && updateFacilityTypeModalCancelBtn) {
        updateFacilityTypeModalCancelBtn.addEventListener('click', () => {
            updateFacilityTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateFacilityTypeError', 'updateFacilityTypeIconError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateFacilityTypeInput")?.addEventListener("keyup", validateUpdateFacilityType);
        document.getElementById("updateFacilityTypeIconInput")?.addEventListener("keyup", validateUpdateFacilityTypeIcon);

        const updateFacilityTypeForm = document.getElementById("updateFacilityTypeForm");
        if (updateFacilityTypeForm) {
            updateFacilityTypeForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateUpdateFacilityTypeForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateFacilityTypeForm);
                formData.append('editfacilitytype', true);

                fetch('AddFacilityType.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        // Clear form and close modal
                        document.getElementById('updateFacilityTypeInput').value = '';
                        document.getElementById('updateFacilityTypeIconInput').value = '';
                        updateFacilityTypeForm.reset();
                        
                        updateFacilityTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        // Fetch and render the updated facility types with current pagination
                        fetchAndRenderFacilityTypes();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Facility Type Modal
    if (facilityTypeConfirmDeleteModal && facilityTypeCancelDeleteBtn) {
        facilityTypeCancelDeleteBtn.addEventListener('click', () => {
            facilityTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const facilityTypeDeleteForm = document.getElementById('facilityTypeDeleteForm');
        if (facilityTypeDeleteForm) {
            facilityTypeDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(facilityTypeDeleteForm);
                formData.append('deletefacilitytype', true);

                fetch('../Admin/AddFacilityType.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        facilityTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        
                        // Fetch and render the updated facility types with current pagination
                        fetchAndRenderFacilityTypes();
                        
                        showAlert('The facility type has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete facility type.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load
    initializeExistingRows();
});

document.addEventListener("DOMContentLoaded", () => {
    // Modal Elements
    const addFacilityModal = document.getElementById('addFacilityModal');
    const addFacilityBtn = document.getElementById('addFacilityBtn');
    const addFacilityCancelBtn = document.getElementById('addFacilityCancelBtn');
    const updateFacilityModal = document.getElementById('updateFacilityModal');
    const updateFacilityModalCancelBtn = document.getElementById('updateFacilityModalCancelBtn');
    const facilityConfirmDeleteModal = document.getElementById('facilityConfirmDeleteModal');
    const facilityCancelDeleteBtn = document.getElementById('facilityCancelDeleteBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('facilitypage') || 1;
    const currentSearch = urlParams.get('facility_search') || '';
    const currentSort = urlParams.get('sort') || 'random';

    // Function to close modals
    const closeModal = () => {
        addFacilityModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['facilityError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render facilities with current pagination
    const fetchAndRenderFacilities = () => {
        let fetchUrl = `../Admin/AddFacility.php?facilitypage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&facility_search=${encodeURIComponent(currentSearch)}`;
        }
        if (currentSort !== 'random') {
            fetchUrl += `&sort=${currentSort}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract the table body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Reattach event listeners to the new rows
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                }
            })
            .catch(error => console.error('Error fetching facilities:', error));
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const facilityId = this.getAttribute('data-facility-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateFacilityID').value = facilityId;
                            document.querySelector('[name="updatefacility"]').value = data.facility.Facility;
                            document.querySelector('[name="updatefacilityicon"]').value = data.facility.FacilityIcon;
                            document.querySelector('[name="updatefacilityiconsize"]').value = data.facility.IconSize;
                            document.querySelector('[name="updateadditionalcharge"]').value = data.facility.AdditionalCharge;
                            document.querySelector('[name="updatepopular"]').value = data.facility.Popular;
                            document.querySelector('[name="updatefacilitytype"]').value = data.facility.FacilityTypeID;
                            updateFacilityModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const facilityId = this.getAttribute('data-facility-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteFacilityID').value = facilityId;
                            document.getElementById('facilityDeleteName').textContent = data.facility.Facility;
                            facilityConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Facility Modal
    if (addFacilityModal && addFacilityBtn && addFacilityCancelBtn) {
        addFacilityBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addFacilityModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addFacilityCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('facilityInput').value = '';
        });
    }

    // Facility Form Submission
    document.getElementById("facilityInput")?.addEventListener("keyup", validateFacility);
    
    const facilityForm = document.getElementById("facilityForm");
    if (facilityForm) {
        facilityForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateFacilityForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(facilityForm);
            formData.append('addfacility', true);

            fetch('AddFacility.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('facilityInput').value = '';
                    facilityForm.reset();
                    closeModal();
                    fetchAndRenderFacilities();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Facility Modal 
    if (updateFacilityModal && updateFacilityModalCancelBtn) {
        updateFacilityModalCancelBtn.addEventListener('click', () => {
            updateFacilityModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateFacilityError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateFacilityInput")?.addEventListener("keyup", validateUpdateFacility);

        const updateFacilityForm = document.getElementById("updateFacilityForm");
        if (updateFacilityForm) {
            updateFacilityForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateFacilityUpdateForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateFacilityForm);
                formData.append('editfacility', true);

                fetch('AddFacility.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        document.getElementById('updateFacilityInput').value = '';
                        updateFacilityForm.reset();
                        
                        updateFacilityModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        fetchAndRenderFacilities();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Facility Modal
    if (facilityConfirmDeleteModal && facilityCancelDeleteBtn) {
        facilityCancelDeleteBtn.addEventListener('click', () => {
            facilityConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const facilityDeleteForm = document.getElementById('facilityDeleteForm');
        if (facilityDeleteForm) {
            facilityDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(facilityDeleteForm);
                formData.append('deletefacility', true);

                fetch('../Admin/AddFacility.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        facilityConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        
                        fetchAndRenderFacilities();
                        showAlert('The facility has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete facility.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }
});

// Rule Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Rule Modal Elements
    const addRuleModal = document.getElementById('addRuleModal');
    const addRuleBtn = document.getElementById('addRuleBtn');
    const addRuleCancelBtn = document.getElementById('addRuleCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2'); 

    // Update Rule Modal Elements
    const updateRuleModal = document.getElementById('updateRuleModal');
    const updateRuleModalCancelBtn = document.getElementById('updateRuleModalCancelBtn');

    // Delete Rule Modal Elements
    const ruleConfirmDeleteModal = document.getElementById('ruleConfirmDeleteModal');
    const ruleCancelDeleteBtn = document.getElementById('ruleCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('rulepage') || 1;
    const currentSearch = urlParams.get('rule_search') || '';

    // Function to close the add modal
    const closeModal = () => {
        addRuleModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['ruleTitleError', 'ruleError', 'ruleIconError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render rules with current pagination
    const fetchAndRenderRules = () => {
        let fetchUrl = `../Admin/AddRule.php?rulepage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&rule_search=${encodeURIComponent(currentSearch)}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract the table body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Reattach event listeners to the new rows
                    initializeExistingRows();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                }
            })
            .catch(error => console.error('Error fetching rules:', error));
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const ruleId = this.getAttribute('data-rule-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddRule.php?action=getRuleDetails&id=${ruleId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateRuleID').value = ruleId;
                            document.querySelector('[name="updateruletitle"]').value = data.rule.RuleTitle;
                            document.querySelector('[name="updaterule"]').value = data.rule.Rule;
                            document.querySelector('[name="updateruleicon"]').value = data.rule.RuleIcon;
                            document.querySelector('[name="updateruleiconsize"]').value = data.rule.IconSize;
                            updateRuleModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load rule details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const ruleId = this.getAttribute('data-rule-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddRule.php?action=getRuleDetails&id=${ruleId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteRuleID').value = ruleId;
                            document.getElementById('ruleDeleteName').textContent = data.rule.RuleTitle;
                            ruleConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load rule details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Rule Modal
    if (addRuleModal && addRuleBtn && addRuleCancelBtn) {
        addRuleBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRuleModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addRuleCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('ruleTitleInput').value = '';
            document.getElementById('ruleInput').value = '';
            document.getElementById('ruleIconInput').value = '';
        });
    }

    // Rule Form Submission
    document.getElementById("ruleTitleInput")?.addEventListener("keyup", validateRuleTitle);
    document.getElementById("ruleInput")?.addEventListener("keyup", validateRule);
    document.getElementById("ruleIconInput")?.addEventListener("keyup", validateRuleIcon);
    
    const ruleForm = document.getElementById("ruleForm");
    if (ruleForm) {
        ruleForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateRuleForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(ruleForm);
            formData.append('addrule', true);

            fetch('AddRule.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('ruleTitleInput').value = '';
                    document.getElementById('ruleInput').value = '';
                    document.getElementById('ruleIconInput').value = '';
                    ruleForm.reset();
                    closeModal();
                    
                    // Fetch and render the updated rules with current pagination
                    fetchAndRenderRules();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Rule Modal 
    if (updateRuleModal && updateRuleModalCancelBtn) {
        updateRuleModalCancelBtn.addEventListener('click', () => {
            updateRuleModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateRuleTitleError', 'updateRuleError', 'updateRuleIconError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateRuleTitleInput")?.addEventListener("keyup", validateUpdateRuleTitle);
        document.getElementById("updateRuleInput")?.addEventListener("keyup", validateUpdateRule);
        document.getElementById("updateRuleIconInput")?.addEventListener("keyup", validateUpdateRuleIcon);

        const updateRuleForm = document.getElementById("updateRuleForm");
        if (updateRuleForm) {
            updateRuleForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateRuleUpdateForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateRuleForm);
                formData.append('editrule', true);

                fetch('AddRule.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        // Clear form and close modal
                        document.getElementById('updateRuleTitleInput').value = '';
                        document.getElementById('updateRuleInput').value = '';
                        document.getElementById('updateRuleIconInput').value = '';
                        updateRuleForm.reset();
                        
                        updateRuleModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        // Fetch and render the updated rules with current pagination
                        fetchAndRenderRules();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Rule Modal
    if (ruleConfirmDeleteModal && ruleCancelDeleteBtn) {
        ruleCancelDeleteBtn.addEventListener('click', () => {
            ruleConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const ruleDeleteForm = document.getElementById('ruleDeleteForm');
        if (ruleDeleteForm) {
            ruleDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(ruleDeleteForm);
                formData.append('deleterule', true);

                fetch('../Admin/AddRule.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        ruleConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        
                        // Fetch and render the updated rules with current pagination
                        fetchAndRenderRules();
                        
                        showAlert('The rule has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete rule.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load
    initializeExistingRows();
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

// Product Size Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Product Size Modal Elements
    const addProductSizeModal = document.getElementById('addProductSizeModal');
    const addProductSizeBtn = document.getElementById('addProductSizeBtn');
    const addProductSizeCancelBtn = document.getElementById('addProductSizeCancelBtn');
    const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2'); 

    // Update Product Size Modal Elements
    const updateProductSizeModal = document.getElementById('updateProductSizeModal');
    const updateProductSizeModalCancelBtn = document.getElementById('updateProductSizeModalCancelBtn');

    // Delete Product Size Modal Elements
    const productSizeConfirmDeleteModal = document.getElementById('productSizeConfirmDeleteModal');
    const productSizeCancelDeleteBtn = document.getElementById('productSizeCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('productsizepage') || 1;
    const currentSearch = urlParams.get('size_search') || '';
    const currentSort = urlParams.get('sort') || 'random';

    // Function to close the add modal
    const closeModal = () => {
        addProductSizeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['sizeError', 'priceModifierError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };

    // Function to fetch and render product sizes with current pagination
    const fetchAndRenderProductSizes = () => {
        let fetchUrl = `../Admin/AddSize.php?productsizepage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&size_search=${encodeURIComponent(currentSearch)}`;
        }
        if (currentSort !== 'random') {
            fetchUrl += `&sort=${currentSort}`;
        }

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract the table body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.flex.justify-center.items-center.mt-1');
                
                if (newTableBody) {
                    const currentTableBody = document.querySelector('tbody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Reattach event listeners to the new rows
                    initializeExistingRows();
                    // Update product names in the new rows
                    updateProductNames();
                }

                if (newPagination) {
                    const currentPagination = document.querySelector('.flex.justify-center.items-center.mt-1');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else {
                        const tableContainer = document.querySelector('table').parentNode;
                        tableContainer.appendChild(newPagination);
                    }
                }
            })
            .catch(error => console.error('Error fetching product sizes:', error));
    };

    // Function to fetch product name by ID
    const fetchProductName = async (productId) => {
        try {
            const response = await fetch(`../Admin/AddSize.php?action=getProductName&id=${productId}`);
            const data = await response.json();
            return data.success ? data.productName : 'None';
        } catch (error) {
            console.error('Error fetching product name:', error);
            return 'None';
        }
    };

    // Function to update product name for a specific cell
    const updateProductNameForCell = async (cell) => {
        const productId = cell.getAttribute('data-product-id');
        if (productId) {
            const productName = await fetchProductName(productId);
            cell.textContent = `${productId} (${productName})`;
        }
    };

    // Function to update product names in existing rows
    const updateProductNames = async () => {
        const rows = document.querySelectorAll('tbody tr');
        for (const row of rows) {
            const productCell = row.querySelector('td:nth-child(4)');
            if (productCell) {
                await updateProductNameForCell(productCell);
            }
        }
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const productSizeId = this.getAttribute('data-productsize-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddSize.php?action=getProductSizeDetails&id=${productSizeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateProductSizeID').value = productSizeId;
                            document.querySelector('[name="updatesize"]').value = data.productsize.Size;
                            document.querySelector('[name="updateprice"]').value = data.productsize.PriceModifier;
                            document.querySelector('[name="updateproduct"]').value = data.productsize.ProductID;
                            updateProductSizeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product size details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const productSizeId = this.getAttribute('data-productsize-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddSize.php?action=getProductSizeDetails&id=${productSizeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductSizeID').value = productSizeId;
                            document.getElementById('productSizeDeleteName').textContent = data.productsize.Size;
                            productSizeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load product size details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    // Initialize event listeners for existing rows
    const initializeExistingRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    };

    // Add Product Size Modal
    if (addProductSizeModal && addProductSizeBtn && addProductSizeCancelBtn) {
        addProductSizeBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductSizeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addProductSizeCancelBtn.addEventListener('click', () => {
            closeModal();
            document.getElementById('sizeInput').value = '';
            document.getElementById('priceModifierInput').value = '';
        });
    }

    // Product Size Form Submission
    document.getElementById("sizeInput")?.addEventListener("keyup", validateProductSize);
    document.getElementById("priceModifierInput")?.addEventListener("keyup", validatePriceModifier);
    
    const productSizeForm = document.getElementById("productSizeForm");
    if (productSizeForm) {
        productSizeForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateProductSizeForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(productSizeForm);
            formData.append('addproductsize', true);

            fetch('AddSize.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                loader.style.display = 'none';
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('sizeInput').value = '';
                    document.getElementById('priceModifierInput').value = '';
                    productSizeForm.reset();
                    closeModal();
                    
                    // Fetch and render the updated product sizes with current pagination
                    fetchAndRenderProductSizes();
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Update Product Size Modal 
    if (updateProductSizeModal && updateProductSizeModalCancelBtn) {
        updateProductSizeModalCancelBtn.addEventListener('click', () => {
            updateProductSizeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');

            const errors = ['updateSizeError', 'updatePriceModifierError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });

        document.getElementById("updateSizeInput")?.addEventListener("keyup", validateUpdateProductSize);
        document.getElementById("updatePriceModifierInput")?.addEventListener("keyup", validateUpdateProductModifier);

        const updateProductSizeForm = document.getElementById("updateProductSizeForm");
        if (updateProductSizeForm) {
            updateProductSizeForm.addEventListener("submit", (e) => {
                e.preventDefault();

                if (!validateProductSizeUpdateForm()) return;

                const loader = document.getElementById('loader');
                loader.style.display = 'flex';

                const formData = new FormData(updateProductSizeForm);
                formData.append('editproductsize', true);

                fetch('AddSize.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        // Clear form and close modal
                        document.getElementById('updateSizeInput').value = '';
                        document.getElementById('updatePriceModifierInput').value = '';
                        updateProductSizeForm.reset();
                        
                        updateProductSizeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        // Fetch and render the updated product sizes with current pagination
                        fetchAndRenderProductSizes();
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Product Size Modal
    if (productSizeConfirmDeleteModal && productSizeCancelDeleteBtn) {
        productSizeCancelDeleteBtn.addEventListener('click', () => {
            productSizeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const productSizeDeleteForm = document.getElementById('productSizeDeleteForm');
        if (productSizeDeleteForm) {
            productSizeDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(productSizeDeleteForm);
                formData.append('deleteproductsize', true);

                fetch('../Admin/AddSize.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        productSizeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        
                        // Fetch and render the updated product sizes with current pagination
                        fetchAndRenderProductSizes();
                        
                        showAlert('The product size has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete product size.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Initialize existing rows on page load and update product names
    initializeExistingRows();
    updateProductNames();
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
    const loader = document.getElementById('loader'); // Make sure you have this element in your HTML

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
                            document.getElementById('contactDate').textContent = data.contact.ContactDate;
                            document.getElementById('contactMessage').textContent = data.contact.ContactMessage;
                            document.getElementById('username').textContent = data.contact.FullName;
                            document.getElementById('useremail').textContent = data.contact.UserEmail;
                            document.getElementById('userphone').textContent = data.contact.UserPhone;
                            document.getElementById('usercountry').textContent = data.contact.Country;

                            // Add hidden input for contact message if not exists
                            if (!document.getElementById('contactMessageInput')) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.id = 'contactMessageInput';
                                input.name = 'contactMessage';
                                input.value = data.contact.ContactMessage;
                                document.getElementById('confirmContactForm').appendChild(input);
                            } else {
                                document.getElementById('contactMessageInput').value = data.contact.ContactMessage;
                            }

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
            // Show loader
            if (loader) loader.style.display = 'flex';
            
            // Send email
            fetch('../Mail/Contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'sendContactResponse'
                })
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
                    showAlert('Response sent successfully and email notification delivered.', 'success');
                    window.location.href = '../Admin/UserContact.php';
                } else {
                    showAlert(`Response saved but failed to send email notification: ${data.message || 'Please try again.'}`, true);
                    window.location.href = '../Admin/UserContact.php';
                }
            })
            .catch(error => {
                if (loader) loader.style.display = 'none';
                showAlert(`Response saved but failed to send email notification: ${error.message}`, true);
                window.location.href = '../Admin/UserContact.php';
            });
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

    // Add keyup event listeners for real-time validation
    document.getElementById("oldPasswordInput").addEventListener("keyup", validateOldPassword);
    document.getElementById("newPasswordInput").addEventListener("keyup", validateNewPassword);
    document.getElementById("confirmPasswordInput").addEventListener("keyup", validateConfirmPassword);

    const changePasswordForm = document.getElementById("changePasswordForm");
    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateChangePasswordForm()) {
                return;
            }

            const loader = document.getElementById("loader");
            if (loader) loader.style.display = 'flex';

            const formData = new FormData(changePasswordForm);
            formData.append('changePassword', true);

            fetch('../Admin/AdminProfileEdit.php', {
                method: 'POST',
                body: formData
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
                    showAlert('You have successfully changed your password.');
                    
                    // Close modal and clear fields
                    changePasswordModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    changePasswordModal.classList.remove("opacity-100", "translate-y-0");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                    
                    document.getElementById("oldPasswordInput").value = "";
                    document.getElementById("newPasswordInput").value = "";
                    document.getElementById("confirmPasswordInput").value = "";
                } else {
                    showAlert(data.message || 'Failed to change password. Please try again.', true);
                }
            })
            .catch(error => {
                if (loader) loader.style.display = 'none';
                showAlert('An error occurred. Please try again.', true);
            });
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
        fetch("../Admin/AdminAccountDelete.php", {
            method: "POST",
        })
            .then(() => {
                // Redirect after account deletion
                window.location.href = "AdminSignin.php";
            })
            .catch((error) => console.error("Account deletion failed:", error));
    });
}

// Admin Profile Update 
document.addEventListener("DOMContentLoaded", () => {
    // Get form elements
    const updateAdminProfileForm = document.getElementById("updateAdminProfileForm");
    const alertMessage = document.getElementById('alertMessage')?.value;
    const profileUpdate = document.getElementById('profileUpdate')?.value === 'true';

    // Show initial alerts if any
    if (profileUpdate) {
        showAlert('You have successfully changed your profile.');
    } else if (alertMessage) {
        showAlert(alertMessage);
    }

    // Initialize form validation
    document.getElementById("firstnameInput")?.addEventListener("keyup", validateFirstName);
    document.getElementById("lastnameInput")?.addEventListener("keyup", validateLastName);
    document.getElementById("usernameInput")?.addEventListener("keyup", validateUsername);
    document.getElementById("emailInput")?.addEventListener("keyup", validateEmail);
    document.getElementById("phoneInput")?.addEventListener("keyup", validatePhone);

    // Handle form submission with AJAX
    if (updateAdminProfileForm) {
        updateAdminProfileForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateProfileUpdateForm()) {
                return;
            }

            // Create FormData object
            const formData = new FormData(updateAdminProfileForm);
            formData.append('modify', true);

            // AJAX request
            fetch('../Admin/AdminProfileEdit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.newUsername) {
                    const sidebarUsername = document.getElementById('adminUsername');
                    if (sidebarUsername) {
                        sidebarUsername.textContent = data.newUsername;
                    }
                }

                if (data.success) {
                    if (data.changesMade) {
                        showAlert('You have successfully changed your profile.');

                        // Update profile image in UI if changed
                        if (data.adminProfile) {
                            const profileImg = document.querySelector('.profile-image');
                            if (profileImg) {
                                // Add timestamp to prevent caching
                                profileImg.src = data.adminProfile + '?' + new Date().getTime();
                            }
                        } else if (data.removeProfile) {
                            const profileImg = document.querySelector('.profile-image');
                            if (profileImg) {
                                profileImg.src = 'path/to/default/image.jpg';
                            }
                        }
                    } else if (data.message) {
                        showAlert(data.message);
                    }
                } else {
                    showAlert(data.message || 'Failed to update profile. Please try again.', true);
                }
            })
            .catch(error => {
                if (loader) loader.style.display = 'none';
                showAlert('An error occurred. Please try again.', true);
            });
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

// Reservation Details Modal
document.addEventListener("DOMContentLoaded", () => {
    // Modal Elements
    const reservationModal = document.getElementById('reservationModal');
    const closeReservationDetailButton = document.getElementById('closeReservationDetailButton');
    // const loader = document.getElementById('loader');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    // Get current pagination and search parameters from URL
    // const urlParams = new URLSearchParams(window.location.search);
    // const currentPage = urlParams.get('facilitypage') || 1;
    // const currentSearch = urlParams.get('facility_search') || '';
    // const currentSort = urlParams.get('sort') || 'random';

    // Function to close modals
    // const closeModal = () => {
    //     addFacilityModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
    //     darkOverlay2.classList.add('opacity-0', 'invisible');
    //     darkOverlay2.classList.remove('opacity-100');

    //     const errors = ['facilityError'];
    //     errors.forEach(error => {
    //         hideError(document.getElementById(error));
    //     });
    // };

    // closeReservationDetailButton.addEventListener('click', () => {
    //     reservationModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
    //     darkOverlay2.classList.add('opacity-0', 'invisible');
    //     darkOverlay2.classList.remove('opacity-100');

    //     // const errors = ['updateFacilityError'];
    //     // errors.forEach(error => {
    //     //     hideError(document.getElementById(error));
    //     // });
    // });

    // Function to attach event listeners to a row
    // const attachEventListenersToRow = (row) => {
    //     // Details button
    //     const detailsBtn = row.querySelector('.details-btn');
    //     if (detailsBtn) {
    //         detailsBtn.addEventListener('click', function() {
    //             const reservationId = this.getAttribute('data-reservation-id');
    //             darkOverlay2.classList.remove('opacity-0', 'invisible');
    //             darkOverlay2.classList.add('opacity-100');

    //             fetch(`../Admin/Booking.php?action=getReservationDetails&id=${reservationId}`)
    //                 .then(response => response.json())
    //                 .then(data => {
    //                     if (data.success) {
    //                         document.getElementById('roomImage').src = data.reservation.RoomCoverImage;
    //                         document.getElementById('roomType').textContent = data.reservation.RoomType;
    //                         document.getElementById('roomDescription').textContent = data.reservation.RoomDescription;
    //                         document.getElementById('pointsEarned').textContent = data.reservation.PointsEarned;
    //                         document.getElementById('pointsRedeemed').textContent = data.reservation.PointsRedeemed;
    //                         document.getElementById('totalPrice').textContent = data.reservation.TotalPrice;
    //                         reservationModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
    //                     } else {
    //                         console.error('Failed to load reservation details');
    //                     }
    //                 })
    //                 .catch(error => console.error('Fetch error:', error));
    //         });
    //     }
    // };
    
    // Function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Function to attach event listeners to a row
const attachEventListenersToRow = (row) => {
    // Details button
    const detailsBtn = row.querySelector('.details-btn');
    if (detailsBtn) {
        detailsBtn.addEventListener('click', function() {
            const reservationId = this.getAttribute('data-reservation-id');
            
            // Make sure dark overlay exists and is shown
            if (darkOverlay2) {
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
            }
            
            // Show the modal immediately while loading data
            reservationModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

            fetch(`../Admin/Booking.php?action=getReservationDetails&id=${reservationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.reservation) {
                        const reservation = data.reservation;
                        
                        // Set user information
                        document.getElementById('userName').textContent = `${reservation.Title || ''} ${reservation.FirstName} ${reservation.LastName}`;
                        document.getElementById('userPhone').textContent = reservation.UserPhone;
                        document.getElementById('reservationDate').textContent = formatDate(reservation.ReservationDate);
                        
                        // Calculate nights
                        const checkInDate = new Date(reservation.CheckInDate);
                        const checkOutDate = new Date(reservation.CheckOutDate);
                        const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                        
                        // Set pricing information
                        const roomRate = reservation.Price * nights;
                        const taxesFees = roomRate * 0.1;
                        const totalPrice = roomRate + taxesFees - (reservation.PointsDiscount || 0);
                        
                        document.getElementById('roomRateLabel').textContent = `Room Rate (${nights} night${nights > 1 ? 's' : ''}):`;
                        document.getElementById('roomRate').textContent = `$ ${roomRate.toFixed(2)}`;
                        
                        if (reservation.PointsRedeemed > 0) {
                            document.getElementById('pointsDiscountContainer').classList.remove('hidden');
                            document.getElementById('pointsDiscountLabel').textContent = `Points Discount (${reservation.PointsRedeemed} points):`;
                            document.getElementById('pointsDiscount').textContent = `- $ ${reservation.PointsDiscount.toFixed(2)}`;
                        } else {
                            document.getElementById('pointsDiscountContainer').classList.add('hidden');
                        }
                        
                        document.getElementById('taxesFees').textContent = `$ ${taxesFees.toFixed(2)}`;
                        document.getElementById('totalPrice').textContent = `$ ${totalPrice.toFixed(2)}`;
                        
                        if (reservation.PointsEarned > 0) {
                            document.getElementById('pointsEarnedContainer').classList.remove('hidden');
                            document.getElementById('pointsEarned').textContent = `+ ${reservation.PointsEarned} points`;
                        } else {
                            document.getElementById('pointsEarnedContainer').classList.add('hidden');
                        }
                    
                        // Initialize Swiper with the room data
                        initializeRoomSwiper(reservation);
                        
                    } else {
                        console.error('Failed to load reservation details');
                    }
                })
                .catch(error => console.error('Fetch error:', error));
        });
    }
};

// Function to initialize Swiper with room data
function initializeRoomSwiper(reservation) {
    const roomContainer = document.getElementById('roomContainer');
    roomContainer.innerHTML = ''; // Clear previous content
    
    // Create a new slide for the room
    const slide = document.createElement('div');
    slide.className = 'swiper-slide';
    
    slide.innerHTML = `
        <div class="flex flex-col md:flex-row gap-4 py-2">
            <div class="md:w-1/3 select-none">
                <div class="relative" style="height: 200px;">
                    <img src="../Admin/${reservation.RoomCoverImage}" 
                         alt="Room Image"
                         class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                </div>
            </div>

            <div class="md:w-2/3">
                <div class="flex justify-between items-start">
                    <div>
                        <h5 class="font-bold text-lg text-gray-800">${reservation.RoomType}</h5>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">${reservation.RoomDescription}</p>
                        <div class="mt-2 text-xs text-gray-500">
                            1 room of this type
                            <div class="flex flex-wrap gap-2 mt-1">
                                <div class="group relative">
                                    <span class="bg-gray-100 px-2 py-1 rounded text-gray-600 font-semibold text-xs cursor-default">
                                        Room #${reservation.RoomName}
                                    </span>
                                    <div class="absolute z-20 left-0 mt-1 w-64 bg-white p-3 rounded-lg shadow-sm border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                        <div class="flex items-center gap-2 text-sm mb-1">
                                            <i class="ri-calendar-check-line text-orange-500"></i>
                                            ${new Date(reservation.CheckInDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} 
                                            <span class="text-gray-400">→</span> 
                                            ${new Date(reservation.CheckOutDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                        </div>
                                        <div class="flex items-center gap-2 text-sm mb-2">
                                            <i class="ri-user-line text-orange-500"></i>
                                            ${reservation.Adult} Adult${reservation.Adult > 1 ? 's' : ''}
                                            ${reservation.Children > 0 ? 
                                                `<span class="text-gray-400">+</span> ${reservation.Children} Child${reservation.Children > 1 ? 'ren' : ''}` : 
                                                ''}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">$${reservation.Price.toFixed(2)}</span>
                                            <span class="text-gray-500">/night</span>
                                        </div>
                                        <div class="bg-red-50 border-l-4 border-red-400 p-2 my-2 rounded-r">
                                            <div class="flex items-start">
                                                <i class="ri-alert-line text-red-500 mt-0.5 mr-1 text-sm"></i>
                                                <span class="text-xs text-red-700">$50 fee if cancelled within 48 hours</span>
                                            </div>
                                        </div>
                                        <button class="cancel-btn mt-1 w-full bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium py-2 px-3 rounded-md transition-colors duration-200 flex items-center justify-center select-none"
                                            data-reservation-id="${reservation.ReservationID}">
                                            <i class="ri-close-circle-line mr-1"></i> Cancel Reservation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="../User/RoomDetails.php?roomTypeID=${reservation.RoomTypeID}&checkin_date=${reservation.CheckInDate}&checkout_date=${reservation.CheckOutDate}&adults=${reservation.Adult}&children=${reservation.Children}"
                    class="mt-2 text-orange-600 hover:text-orange-700 font-medium inline-flex items-center text-xs bg-orange-50 px-3 py-1 rounded-full">
                    <i class="ri-information-line mr-1"></i> Room Details
                </a>
            </div>
        </div>
    `;
    
    roomContainer.appendChild(slide);
    
    // Initialize Swiper
    const swiper = new Swiper('.roomTypeSwiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        centeredSlides: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 1,
                spaceBetween: 30
            }
        }
    });
    
    // Add event listener to cancel button
    const cancelBtn = slide.querySelector('.cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            const reservationId = this.getAttribute('data-reservation-id');
            // Add your cancellation logic here
            alert(`Canceling reservation ${reservationId}`);
        });
    }
}

// Close modal button
document.getElementById('closeReservationDetailButton').addEventListener('click', function() {
    reservationModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
    if (darkOverlay2) {
        darkOverlay2.classList.remove('opacity-100');
        darkOverlay2.classList.add('opacity-0', 'invisible');
    }
});

// Make sure to initialize Swiper library
document.addEventListener('DOMContentLoaded', function() {
    // Load Swiper CSS if not already loaded
    if (!document.querySelector('link[href*="swiper-bundle.min.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css';
        document.head.appendChild(link);
    }
    
    // Load Swiper JS if not already loaded
    if (!window.Swiper) {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js';
        script.onload = function() {
            console.log('Swiper loaded successfully');
        };
        document.body.appendChild(script);
    }
});

    // Function to initialize action buttons for all rows
    function initializeReservationActionButtons() {
        document.querySelectorAll('tbody tr').forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    // Call this function after loading new content via AJAX
    initializeReservationActionButtons();
});

// Full form validation function
const validateProductTypeForm = () => {
    const isTypeValid = validateProductType();
    const isDescriptionValid = validateDescription();

    return isTypeValid && isDescriptionValid;
};

const validateUpdateProductTypeForm = () => {
    const isTypeValid = validateUpdateProductType();
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

const validateProductUpdateForm = () => {
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
    const isRoomPriceValid = validateRoomPrice();
    const validateRoomQuantityValid = validateRoomQuantity();

    return isRoomTypeValid && isRoomDescriptionValid && isRoomCapacityValid && isRoomPriceValid && validateRoomQuantityValid;
}

const validateUpdateRoomTypeForm = () => {
    const isRoomTypeValid = validateUpdateRoomType();
    const isRoomDescriptionValid = validateUpdateRoomTypeDescription();
    const isRoomCapacityValid = validateUpdateRoomCapacity();

    return isRoomTypeValid && isRoomDescriptionValid && isRoomCapacityValid;
}

const validateRoomForm = () => {
    const isRoomNameValid = validateRoomName();

    return isRoomNameValid;
}

const validateRoomUpdateForm = () => {
    const isRoomNameValid = validateUpdateRoomName();

    return isRoomNameValid;
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

const validateUpdateProductType = () => {
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

const validateRoomName = () => {
    return validateField(
        "roomNameInput",
        "roomNameError",
        (input) => (!input ? "Room name is required." : null)
    );
}

const validateUpdateRoomName = () => {
    return validateField(
        "updateRoomNameInput",
        "updateRoomNameError",
        (input) => (!input ? "Room name is required." : null)
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

const validateRoomPrice = () => {
    return validateField(
        "roomPriceInput",
        "roomPriceError",
        (input) => (!input ? "Price is required." : null)
    );
}

const validateRoomQuantity = () => {
    return validateField(
        "roomQuantityInput",
        "roomQuantityError",
        (input) => (!input ? "Room quantity is required." : null)
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




