import { showError, hideError, showAlert, validateField } from './alertFunc.js';
import { formatTimeForInput } from './timeUtils.js';

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
        fetch('admin_logout.php', { method: 'POST' })
            .then(() => {
                // Redirect after logout
                window.location.href = 'admin_signin.php';
            })
            .catch((error) => console.error('Logout failed:', error));
    });
}

// Add Role Form
document.addEventListener("DOMContentLoaded", () => {
    const addRoleModal = document.getElementById('addRoleModal');
    const addRoleBtn = document.getElementById('addRoleBtn');
    const addRoleCancelBtn = document.getElementById('addRoleCancelBtn');
    const roleForm = document.getElementById("roleForm");

    // Function to close the add modal
    const closeModal = () => {
        roleForm.reset();
        addRoleModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');

        const errors = ['roleError', 'roleDescriptionError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    };
    
    if (addRoleModal && addRoleBtn && addRoleCancelBtn) {
        // Show modal
        addRoleBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addRoleModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        addRoleCancelBtn.addEventListener('click', () => {
            closeModal();
        });
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("roleInput").addEventListener("keyup", validateRole);
    document.getElementById("roleDescriptionInput").addEventListener("keyup", validateRoleDescription);

    if (roleForm) {
        roleForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateRoleForm()) return;

            const formData = new FormData(roleForm);
            formData.append('addrole', true);

            fetch('../Admin/role_management.php', {
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
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('roleInput').value = '';
                    document.getElementById('roleDescriptionInput').value = '';
                    roleForm.reset();
                    closeModal();
                }
            })
            .catch(err => {
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }
});

// Role Management
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".roleUpdateForm select").forEach(select => {
        select.addEventListener("change", function() {
            const form = this.closest("form");
            const formData = new FormData(form);

            fetch("../includes/admin_table_components/role_management_results.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("Role updated successfully!");
                    } else {
                        showAlert("Error: " + data.message, true);
                    }
                })
                .catch(err => console.error("Fetch error:", err));
        });
    });
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
        let fetchUrl = `../Admin/add_supplier.php?supplierpage=${currentPage}`;
        
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
            const response = await fetch(`../Admin/add_supplier.php?action=getProductTypeName&id=${productTypeId}`);
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

                fetch(`../Admin/add_supplier.php?action=getSupplierDetails&id=${supplierId}`)
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

                fetch(`../Admin/add_supplier.php?action=getSupplierDetails&id=${supplierId}`)
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
            supplierForm.reset();
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

            const formData = new FormData(supplierForm);
            formData.append('addsupplier', true);

            fetch('add_supplier.php', {
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
                showAlert(data.message, !data.success);

                if (data.success) {                 
                    supplierForm.reset();
                    closeModal();
                    
                    // Fetch and render the updated suppliers with current pagination
                    fetchAndRenderSuppliers();
                }
            })
            .catch(err => {
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

                const formData = new FormData(updateSupplierForm);
                formData.append('editsupplier', true);

                fetch('add_supplier.php', {
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
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        updateSupplierForm.reset();
                        
                        updateSupplierModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');

                        // Fetch and render the updated suppliers with current pagination
                        fetchAndRenderSuppliers();
                    }
                })
                .catch(err => {
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

                fetch('../Admin/add_supplier.php', {
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

    // Bulk Delete Suppliers
    const supplierBulkDeleteBtn = document.getElementById('supplierBulkDeleteBtn');
    const selectAllSuppliers = document.getElementById('selectAllSuppliers');

    // Modal
    const supplierBulkDeleteModal = document.getElementById('supplierBulkDeleteModal');
    const supplierBulkDeleteCancelBtn = document.getElementById('supplierBulkDeleteCancelBtn');
    const supplierBulkDeleteConfirmBtn = document.getElementById('supplierBulkDeleteConfirmBtn');
    const supplierBulkDeleteCount = document.getElementById('supplierBulkDeleteCount');

    // Select all suppliers
    if (selectAllSuppliers) {
        selectAllSuppliers.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.supplierCheckbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleSupplierBulkDeleteBtn();
        });
    }

    // Toggle bulk delete button
    const toggleSupplierBulkDeleteBtn = () => {
        const selected = document.querySelectorAll('.supplierCheckbox:checked');
        if (selected.length > 0) {
            supplierBulkDeleteBtn.classList.remove('hidden');
            supplierBulkDeleteBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            supplierBulkDeleteBtn.classList.add('hidden');
        }
    };

    // Watch supplier checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('supplierCheckbox')) {
            toggleSupplierBulkDeleteBtn();
        }
    });

    // Bulk delete button click
    if (supplierBulkDeleteBtn) {
        supplierBulkDeleteBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.supplierCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) return;

            supplierBulkDeleteCount.textContent = selectedIds.length;
            supplierBulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            supplierBulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel
            supplierBulkDeleteCancelBtn.addEventListener('click', () => {
                supplierBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            // Confirm
            supplierBulkDeleteConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeletesuppliers', true);
                selectedIds.forEach(id => formData.append('supplierids[]', id));

                fetch('../Admin/add_supplier.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("Selected suppliers deleted successfully.");
                        fetchAndRenderSuppliers();
                    } else {
                        showAlert(data.message || "Failed to delete suppliers.", true);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showAlert("An error occurred.", true);
                })
                .finally(() => {
                    supplierBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                });
            };
        });
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
        let fetchUrl = `../Admin/add_producttype.php?producttypepage=${currentPage}`;
        
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

                fetch(`../Admin/add_producttype.php?action=getProductTypeDetails&id=${productTypeId}&producttypepage=${currentPage}`)
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

                fetch(`../Admin/add_producttype.php?action=getProductTypeDetails&id=${productTypeId}&producttypepage=${currentPage}`)
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
            productTypeForm.reset();
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

            const formData = new FormData(productTypeForm);
            formData.append('addproducttype', true);

            fetch('add_producttype.php', {
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

                const formData = new FormData(updateProductTypeForm);
                formData.append('editproducttype', true);
                // Include current page in the form data
                formData.append('producttypepage', currentPage);

                fetch('add_producttype.php', {
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

                fetch('../Admin/add_producttype.php', {
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

    // Bulk Delete Elements
    const bulkDeleteProductTypesBtn = document.getElementById('bulkDeleteProductTypesBtn');
    const selectAllProductTypes = document.getElementById('selectAllProductTypes');
    const bulkProductTypeConfirmDeleteModal = document.getElementById('bulkProductTypeConfirmDeleteModal');
    const bulkProductTypeCancelDeleteBtn = document.getElementById('bulkProductTypeCancelDeleteBtn');
    const bulkProductTypeCount = document.getElementById('bulkProductTypeCount');

    // Toggle button
    const toggleBulkDeleteProductTypesBtn = () => {
        const selected = document.querySelectorAll('.rowProductTypeCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteProductTypesBtn.classList.remove('hidden');
            bulkDeleteProductTypesBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteProductTypesBtn.classList.add('hidden');
            bulkDeleteProductTypesBtn.textContent = "Delete Selected";
        }
    };

    // Select all
    if (selectAllProductTypes) {
        selectAllProductTypes.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('.rowProductTypeCheckbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteProductTypesBtn();
        });
    }

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('rowProductTypeCheckbox')) {
            toggleBulkDeleteProductTypesBtn();
        }
    });

    // Open bulk delete modal
    if (bulkDeleteProductTypesBtn) {
        bulkDeleteProductTypesBtn.addEventListener('click', () => {
            const selected = document.querySelectorAll('.rowProductTypeCheckbox:checked');
            if (selected.length === 0) return;

            bulkProductTypeCount.textContent = selected.length;
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            bulkProductTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });
    }

    // Cancel bulk delete
    if (bulkProductTypeCancelDeleteBtn) {
        bulkProductTypeCancelDeleteBtn.addEventListener('click', () => {
            bulkProductTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });
    }

    // Submit bulk delete
    const bulkProductTypeDeleteForm = document.getElementById('bulkProductTypeDeleteForm');
    if (bulkProductTypeDeleteForm) {
        bulkProductTypeDeleteForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const selectedIds = Array.from(document.querySelectorAll('.rowProductTypeCheckbox:checked')).map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No product types selected.", true);
                return;
            }

            const formData = new FormData();
            formData.append('bulkdeleteproducttypes', true);
            selectedIds.forEach(id => formData.append('producttypeids[]', id));
            formData.append('producttypepage', currentPage);

            fetch('../Admin/add_producttype.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bulkProductTypeConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                    darkOverlay2.classList.add('opacity-0', 'invisible');
                    darkOverlay2.classList.remove('opacity-100');

                    fetchAndRenderProductTypes();
                    showAlert("Selected product types deleted successfully.");
                    bulkDeleteProductTypesBtn.classList.add('hidden');
                    selectAllProductTypes.checked = false;
                } else {
                    showAlert(data.message || "Failed to delete selected product types.", true);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showAlert("An error occurred. Please try again.", true);
            });
        });
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
        let fetchUrl = `../Admin/add_product.php?productpage=${currentPage}`;
        
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

                fetch(`../Admin/add_product.php?action=getProductDetails&id=${productId}`)
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

                fetch(`../Admin/add_product.php?action=getProductDetails&id=${productId}`)
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
            productForm.reset();
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

            const formData = new FormData(productForm);
            formData.append('addproduct', true);

            fetch('add_product.php', {
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

                const formData = new FormData(updateProductForm);
                formData.append('editproduct', true);

                fetch('add_product.php', {
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

                fetch('../Admin/add_product.php', {
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

    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    const bulkDeleteModal = document.getElementById('productBulkDeleteModal');
    const bulkDeleteCancelBtn = document.getElementById('bulkDeleteCancelBtn');
    const bulkDeleteConfirmBtn = document.getElementById('bulkDeleteConfirmBtn');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');

    // Select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            document.querySelectorAll('.rowCheckbox')
                .forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteBtn();
        });
    }

    // Toggle bulk delete button
    const toggleBulkDeleteBtn = () => {
        const selected = document.querySelectorAll('.rowCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
            bulkDeleteBtn.textContent = "Delete Selected";
        }
    };

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('rowCheckbox')) {
            toggleBulkDeleteBtn();
        }
    });

    // Bulk delete button click
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No products selected.", true);
                return;
            }

            bulkDeleteCount.textContent = selectedIds.length;
            bulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            bulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            bulkDeleteCancelBtn.addEventListener('click', () => {
                bulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                bulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            bulkDeleteConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeleteproducts', true);
                selectedIds.forEach(id => formData.append('productids[]', id));

                fetch('add_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchAndRenderProducts();
                        showAlert("Selected products deleted successfully.");
                        bulkDeleteBtn.classList.add('hidden');
                        selectAllCheckbox.checked = false;
                    } else {
                        showAlert(data.message || "Failed to delete selected products.", true);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showAlert("An error occurred. Please try again.", true);
                })
                .finally(() => {
                    bulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    bulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                });
            };
        });
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
        let fetchUrl = `../Admin/add_roomtype.php?roomtypepage=${currentPage}`;
        
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

                fetch(`../Admin/add_roomtype.php?action=getRoomTypeDetails&id=${roomTypeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateRoomTypeID').value = roomTypeId;
                            
                            // Handle cover image display
                            if (data.roomtype.RoomCoverImage) {
                                updateCoverPreview.src = data.roomtype.RoomCoverImage;
                                updateCoverPreviewContainer.classList.remove('hidden');
                                updateUploadArea.classList.add('hidden');
                            } else {
                                updateCoverPreviewContainer.classList.add('hidden');
                                updateUploadArea.classList.remove('hidden');
                            }
                            
                            document.getElementById('updateRoomTypeInput').value = data.roomtype.RoomType;
                            document.getElementById('updateRoomTypeDescriptionInput').value = data.roomtype.RoomDescription;
                            document.getElementById('updateRoomCapacityInput').value = data.roomtype.RoomCapacity;
                            document.getElementById('updateRoomPriceInput').value = data.roomtype.RoomPrice;
                            document.getElementById('updateRoomQuantityInput').value = data.roomtype.RoomQuantity;
                            
                            // Uncheck all facilities first
                            const allFacilityCheckboxes = document.querySelectorAll('#updateFacilitiesContainer input[type="checkbox"]');
                            allFacilityCheckboxes.forEach(checkbox => {
                                checkbox.checked = false;
                            });
                            
                            // Check the associated facilities
                            if (data.facilities && data.facilities.length > 0) {
                                data.facilities.forEach(facilityId => {
                                    const facilityCheckbox = document.getElementById(`update_facility_${facilityId}`);
                                    if (facilityCheckbox) {
                                        facilityCheckbox.checked = true;
                                    }
                                });
                            }
                            
                            // Display additional images
                            const additionalPreviewContainer = document.getElementById('update-additional-preview-container');
                            additionalPreviewContainer.innerHTML = ''; // Clear previous images
                            
                            if (data.additional_images && data.additional_images.length > 0) {
                                data.additional_images.forEach((imagePath, index) => {
                                    const imageDiv = document.createElement('div');
                                    imageDiv.className = 'relative group';
                                    imageDiv.innerHTML = `
                                        <img src="${imagePath}" class="w-full h-32 object-cover rounded-lg select-none">
                                        <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity" data-image-index="${index}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <input type="hidden" name="existing_additional_images[]" value="${imagePath}">
                                    `;
                                    additionalPreviewContainer.appendChild(imageDiv);
                                });
                            }
                            
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

                fetch(`../Admin/add_roomtype.php?action=getRoomTypeDetails&id=${roomTypeId}`)
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
            roomTypeForm.reset();
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

            fetch('add_roomtype.php', {
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

        document.getElementById("updateRoomTypeModalCancelBtn2")?.addEventListener("click", () => {
            updateRoomTypeModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
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

                fetch('add_roomtype.php', {
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
                        updateRoomTypeForm.reset();
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

                fetch('../Admin/add_roomtype.php', {
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

    // Bulk Delete Room Types
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    // Modal elements
    const bulkDeleteModal = document.getElementById('roomTypeBulkDeleteModal');
    const bulkDeleteCancelBtn = document.getElementById('bulkDeleteCancelBtn');
    const bulkDeleteConfirmBtn = document.getElementById('bulkDeleteConfirmBtn');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');

    // Handle select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteBtn();
        });
    }

    // Toggle bulk delete button visibility
    const toggleBulkDeleteBtn = () => {
        const selected = document.querySelectorAll('.rowCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
            bulkDeleteBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('rowCheckbox')) {
            toggleBulkDeleteBtn();
        }
    });

    // Handle bulk delete button
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No room types selected.", true);
                return;
            }

            // Show confirm modal
            bulkDeleteCount.textContent = selectedIds.length;
            bulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            bulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel button closes modal
            bulkDeleteCancelBtn.addEventListener('click', () => {
                bulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                bulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            // Confirm delete
            bulkDeleteConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeleteroomtypes', true);
                selectedIds.forEach(id => formData.append('roomtypeids[]', id));

                fetch('../Admin/add_roomtype.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchAndRenderRoomTypes();
                        showAlert("Selected room types deleted successfully.");
                        bulkDeleteBtn.classList.add('hidden');
                        selectAllCheckbox.checked = false;
                    } else {
                        showAlert(data.message || "Failed to delete selected room types.", true);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showAlert("An error occurred. Please try again.", true);
                })
                .finally(() => {
                    // Close modal after action
                    bulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    bulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                });
            };
        });
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
        let fetchUrl = `../Admin/add_room.php?roompage=${currentPage}`;
        
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

                fetch(`../Admin/add_room.php?action=getRoomDetails&id=${roomId}`)
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

                fetch(`../Admin/add_room.php?action=getRoomDetails&id=${roomId}&roompage=${currentPage}`)
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
            roomForm.reset();
        });
    }

    // Room Form Submission
    document.getElementById("roomNameInput")?.addEventListener("keyup", validateRoomName);
    
    const roomForm = document.getElementById("roomForm");
    if (roomForm) {
        roomForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateRoomForm()) return;

            const formData = new FormData(roomForm);
            formData.append('addroom', true);

            fetch('add_room.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.message, !data.success);

                if (data.success) {
                    document.getElementById('roomNameInput').value = '';
                    roomForm.reset();
                    closeModal();
                    fetchAndRenderRooms();
                }
            })
            .catch(err => {
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

                const formData = new FormData(updateRoomForm);
                formData.append('editroom', true);
                formData.append('roompage', currentPage);

                fetch('add_room.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        updateRoomModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderRooms();
                    }
                })
                .catch(err => {
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

                fetch('../Admin/add_room.php', {
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

    // Bulk Delete Rooms
    const bulkDeleteRoomsBtn = document.getElementById('bulkDeleteRoomsBtn');
    const selectAllRooms = document.getElementById('selectAllRooms');
    const roomBulkDeleteModal = document.getElementById('roomBulkDeleteModal');
    const roomBulkDeleteCancelBtn = document.getElementById('roomBulkDeleteCancelBtn');
    const roomBulkDeleteConfirmBtn = document.getElementById('roomBulkDeleteConfirmBtn');
    const roomBulkDeleteCount = document.getElementById('roomBulkDeleteCount');

    // Handle select all checkbox
    if (selectAllRooms) {
        selectAllRooms.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('.roomCheckbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteRoomsBtn();
        });
    }

    // Toggle bulk delete button
    const toggleBulkDeleteRoomsBtn = () => {
        const selected = document.querySelectorAll('.roomCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteRoomsBtn.classList.remove('hidden');
            bulkDeleteRoomsBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteRoomsBtn.classList.add('hidden');
            bulkDeleteRoomsBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('roomCheckbox')) {
            toggleBulkDeleteRoomsBtn();
        }
    });

    // Handle bulk delete button
    if (bulkDeleteRoomsBtn) {
        bulkDeleteRoomsBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.roomCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No rooms selected.", true);
                return;
            }

            // Show modal
            roomBulkDeleteCount.textContent = selectedIds.length;
            roomBulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            roomBulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel
            roomBulkDeleteCancelBtn.addEventListener('click', () => {
                roomBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                roomBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            // Confirm delete
            roomBulkDeleteConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeleterooms', true);
                selectedIds.forEach(id => formData.append('roomids[]', id));

                fetch('../Admin/add_room.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            fetchAndRenderRooms();
                            showAlert("Selected rooms deleted successfully.");
                            bulkDeleteRoomsBtn.classList.add('hidden');
                            selectAllRooms.checked = false;
                        } else {
                            showAlert(data.message || "Failed to delete selected rooms.", true);
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        showAlert("An error occurred. Please try again.", true);
                    })
                    .finally(() => {
                        roomBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                        roomBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                        darkOverlay2.classList.add("opacity-0", "invisible");
                        darkOverlay2.classList.remove("opacity-100");
                    });
            };
        });
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
        let fetchUrl = `../Admin/add_facilitytype.php?facilitytypepage=${currentPage}`;
        
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

                fetch(`../Admin/add_facilitytype.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
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

                fetch(`../Admin/add_facilitytype.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
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
            facilityTypeForm.reset();
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

            const formData = new FormData(facilityTypeForm);
            formData.append('addfacilitytype', true);

            fetch('add_facilitytype.php', {
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

                const formData = new FormData(updateFacilityTypeForm);
                formData.append('editfacilitytype', true);

                fetch('add_facilitytype.php', {
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

                fetch('../Admin/add_facilitytype.php', {
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

    // Bulk Delete
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    // Modal elements
    const bulkDeleteModal = document.getElementById('facilityTypeBulkDeleteModal');
    const bulkDeleteCancelBtn = document.getElementById('bulkDeleteCancelBtn');
    const bulkDeleteConfirmBtn = document.getElementById('bulkDeleteConfirmBtn');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');

    // Handle select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteBtn();
        });
    }

    // Toggle bulk delete button visibility
    const toggleBulkDeleteBtn = () => {
        const selected = document.querySelectorAll('.rowCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
            bulkDeleteBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('rowCheckbox')) {
            toggleBulkDeleteBtn();
        }
    });

    // Handle bulk delete button 
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No facility types selected.", true);
                return;
            }

            // Show confirm modal
            bulkDeleteCount.textContent = selectedIds.length;
            bulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            bulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel button closes modal
            bulkDeleteCancelBtn.addEventListener('click', () => {
                bulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                bulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            // Confirm delete
            bulkDeleteConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeletefacilitytypes', true);
                selectedIds.forEach(id => formData.append('facilitytypeids[]', id));

                fetch('../Admin/add_facilitytype.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchAndRenderFacilityTypes();
                        showAlert("Selected facility types deleted successfully.");
                        bulkDeleteBtn.classList.add('hidden');
                        selectAllCheckbox.checked = false;
                    } else {
                        showAlert(data.message || "Failed to delete selected facility types.", true);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showAlert("An error occurred. Please try again.", true);
                })
                .finally(() => {
                    // Close modal after action
                    bulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    bulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                });
            };
        });
    }

    // Initialize existing rows on page load
    initializeExistingRows();
});

// Facility Modal
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
        let fetchUrl = `../Admin/add_facility.php?facilitypage=${currentPage}`;
        
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

                fetch(`../Admin/add_facility.php?action=getFacilityDetails&id=${facilityId}`)
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

                fetch(`../Admin/add_facility.php?action=getFacilityDetails&id=${facilityId}`)
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
            facilityForm.reset();
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

            fetch('add_facility.php', {
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

                fetch('add_facility.php', {
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

                fetch('../Admin/add_facility.php', {
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

    // Bulk Delete Facilities
    const bulkFacilityDeleteBtn = document.getElementById('bulkDeleteFacilitiesBtn');
    const selectAllFacilityCheckbox = document.getElementById('selectAllCheckbox');

    const facilityBulkDeleteModal = document.getElementById('facilityBulkDeleteModal');
    const bulkFacilityDeleteCancelBtn = document.getElementById('bulkFacilityDeleteCancelBtn');
    const bulkFacilityDeleteConfirmBtn = document.getElementById('bulkFacilityDeleteConfirmBtn');
    const bulkFacilityDeleteCount = document.getElementById('bulkFacilityDeleteCount');

    // Handle select all checkbox
    if (selectAllFacilityCheckbox) {
        selectAllFacilityCheckbox.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('tbody tr input[type="checkbox"]');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkFacilityDeleteBtn();
        });
    }

    // Toggle bulk delete button visibility
    const toggleBulkFacilityDeleteBtn = () => {
        const selected = document.querySelectorAll('tbody tr input[type="checkbox"]:checked');
        if (selected.length > 0) {
            bulkFacilityDeleteBtn.classList.remove('hidden');
            bulkFacilityDeleteBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkFacilityDeleteBtn.classList.add('hidden');
            bulkFacilityDeleteBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.closest('tbody tr') && e.target.type === 'checkbox') {
            toggleBulkFacilityDeleteBtn();
        }
    });

    // Bulk delete button click
    if (bulkFacilityDeleteBtn) {
        bulkFacilityDeleteBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('tbody tr input[type="checkbox"]:checked'))
                .map(cb => cb.getAttribute('data-facility-id'))  // get directly from checkbox

            if (selectedIds.length === 0) {
                showAlert("No facilities selected.", true);
                return;
            }

            // Show confirm modal
            bulkFacilityDeleteCount.textContent = selectedIds.length;
            facilityBulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            facilityBulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel button closes modal
            bulkFacilityDeleteCancelBtn.addEventListener('click', () => {
                facilityBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                facilityBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            // Confirm delete
            bulkFacilityDeleteConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeletefacilities', true);
                selectedIds.forEach(id => formData.append('facilityids[]', id));

                fetch('../Admin/add_facility.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchAndRenderFacilities();
                        showAlert("Selected facilities deleted successfully.");
                        bulkFacilityDeleteBtn.classList.add('hidden');
                        if (selectAllFacilityCheckbox) selectAllFacilityCheckbox.checked = false;
                    } else {
                        showAlert(data.message || "Failed to delete selected facilities.", true);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showAlert("An error occurred. Please try again.", true);
                })
                .finally(() => {
                    // Close modal after action
                    facilityBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    facilityBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                });
            };
        });
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
        let fetchUrl = `../Admin/add_rule.php?rulepage=${currentPage}`;
        
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

                fetch(`../Admin/add_rule.php?action=getRuleDetails&id=${ruleId}`)
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

                fetch(`../Admin/add_rule.php?action=getRuleDetails&id=${ruleId}`)
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
            ruleForm.reset();
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

            const formData = new FormData(ruleForm);
            formData.append('addrule', true);

            fetch('add_rule.php', {
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

                fetch('add_rule.php', {
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

                fetch('../Admin/add_rule.php', {
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

    // Bulk Delete Rules
    const bulkDeleteRulesBtn = document.getElementById('bulkDeleteRulesBtn');
    const selectAllRules = document.getElementById('selectAllRules');
    const ruleBulkDeleteModal = document.getElementById('ruleBulkDeleteModal');
    const ruleBulkDeleteCancelBtn = document.getElementById('ruleBulkDeleteCancelBtn');
    const ruleBulkDeleteConfirmBtn = document.getElementById('ruleBulkDeleteConfirmBtn');
    const ruleBulkDeleteCount = document.getElementById('ruleBulkDeleteCount');

    // Handle select all checkbox
    if (selectAllRules) {
        selectAllRules.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('.ruleCheckbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteRulesBtn();
        });
    }

    // Toggle bulk delete button
    const toggleBulkDeleteRulesBtn = () => {
        const selected = document.querySelectorAll('.ruleCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteRulesBtn.classList.remove('hidden');
            bulkDeleteRulesBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteRulesBtn.classList.add('hidden');
            bulkDeleteRulesBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('ruleCheckbox')) {
            toggleBulkDeleteRulesBtn();
        }
    });

    // Handle bulk delete button
    if (bulkDeleteRulesBtn) {
        bulkDeleteRulesBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.ruleCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No rules selected.", true);
                return;
            }

            // Show modal
            ruleBulkDeleteCount.textContent = selectedIds.length;
            ruleBulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            ruleBulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel
            ruleBulkDeleteCancelBtn.addEventListener('click', () => {
                ruleBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                ruleBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            // Confirm delete
            ruleBulkDeleteConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeleterules', true);
                selectedIds.forEach(id => formData.append('ruleids[]', id));

                fetch('../Admin/add_rule.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            fetchAndRenderRules();
                            showAlert("Selected rules deleted successfully.");
                            bulkDeleteRulesBtn.classList.add('hidden');
                            selectAllRules.checked = false;
                        } else {
                            showAlert(data.message || "Failed to delete selected rules.", true);
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        showAlert("An error occurred. Please try again.", true);
                    })
                    .finally(() => {
                        ruleBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                        ruleBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                        darkOverlay2.classList.add("opacity-0", "invisible");
                        darkOverlay2.classList.remove("opacity-100");
                    });
            };
        });
    }

    // Initialize existing rows on page load
    initializeExistingRows();
});

// Add Product Image Form
document.addEventListener("DOMContentLoaded", () => {
    const addProductImageModal = document.getElementById('addProductImageModal');
    const addProductImageBtn = document.getElementById('addProductImageBtn');
    const addProductImageCancelBtn = document.getElementById('addProductImageCancelBtn');
    const updateProductImageModal = document.getElementById('updateProductImageModal');
    const updateProductImageModalCancelBtn = document.getElementById('updateProductImageModalCancelBtn');
    const productImageConfirmDeleteModal = document.getElementById('productImageConfirmDeleteModal');
    const productImageCancelDeleteBtn = document.getElementById('productImageCancelDeleteBtn');

    // Add Product Image Modal
    if (addProductImageBtn && addProductImageModal) {
        addProductImageBtn.addEventListener("click", () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addProductImageModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addProductImageCancelBtn.addEventListener("click", () => {
            addProductImageModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
            document.getElementById("productImageForm").reset();
        });

        const productImageForm = document.getElementById("productImageForm");
        productImageForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const formData = new FormData(productImageForm);
            formData.append('addproductimage', true);

            fetch('../Admin/product_image.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    showAlert(data.message, !data.success);
                    if (data.success) {
                        productImageForm.reset();
                        addProductImageModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderProductImages();
                    }
                });
        });
    }

    // Update Product Image Modal
    if (updateProductImageModal && updateProductImageModalCancelBtn) {
        updateProductImageModalCancelBtn.addEventListener("click", () => {
            updateProductImageModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const updateProductImageForm = document.getElementById("updateProductImageForm");
        updateProductImageForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const formData = new FormData(updateProductImageForm);
            formData.append('editproductimage', true);

            fetch('../Admin/product_image.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    showAlert(data.message, !data.success);
                    if (data.success) {
                        updateProductImageModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderProductImages();
                    }
                });
        });
    }

    // Delete Product Image Modal
    if (productImageConfirmDeleteModal && productImageCancelDeleteBtn) {
        productImageCancelDeleteBtn.addEventListener("click", () => {
            productImageConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const productImageDeleteForm = document.getElementById("productImageDeleteForm");
        productImageDeleteForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const formData = new FormData(productImageDeleteForm);
            formData.append('deleteproductimage', true);

            fetch('../Admin/product_image.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        productImageConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderProductImages();
                        showAlert('The product image has been successfully deleted.');
                    } else {
                        showAlert(data.message, true);
                    }
                });
        });
    }

    // Bind Action Buttons
    function bindActionButtons() {
        document.addEventListener('click', function (e) {
            // Update button click
            const detailsBtn = e.target.closest('.details-btn');
            if (detailsBtn && updateProductImageModal) {
                const ImageId = detailsBtn.getAttribute('data-productimage-id');
                fetch(`../Admin/product_image.php?action=getProductImageDetails&id=${ImageId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateProductImageID').value = ImageId;
                            document.getElementById('updateimagepath').src = data.productimage.ImageAdminPath;
                            document.querySelector('[name="updateimagealt"]').value = data.productimage.ImageAlt;
                            document.querySelector('[name="updateproduct"]').value = data.productimage.ProductID;
                            document.querySelector('[name="updateprimary"]').value = data.productimage.PrimaryImage;
                            document.querySelector('[name="updatesecondary"]').value = data.productimage.SecondaryImage;

                            darkOverlay2.classList.remove('opacity-0', 'invisible');
                            darkOverlay2.classList.add('opacity-100');
                            updateProductImageModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            showAlert('Failed to load product image details.', true);
                        }
                    });
            }

            // Delete button click
            const deleteBtn = e.target.closest('.delete-btn');
            if (deleteBtn && productImageConfirmDeleteModal) {
                const ImageId = deleteBtn.getAttribute('data-productimage-id');
                fetch(`../Admin/product_image.php?action=getProductImageDetails&id=${ImageId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteProductImageID').value = ImageId;
                            document.getElementById('deleteImagePath').src = data.productimage.ImageAdminPath;

                            darkOverlay2.classList.remove('opacity-0', 'invisible');
                            darkOverlay2.classList.add('opacity-100');
                            productImageConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            showAlert('Failed to load product image details.', true);
                        }
                    });
            }
        });
    }

    // Render updated product images
    function fetchAndRenderProductImages() {
        fetch('../Admin/product_image.php?productimagepage=1')
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                if (newTableBody) {
                    document.querySelector('tbody').innerHTML = newTableBody.innerHTML;
                    bindActionButtons();
                }
            });
    }

    // Bulk Delete Product Images
    const bulkDeleteProductImageBtn = document.getElementById('bulkDeleteProductImageBtn');
    const selectAllProductImages = document.getElementById('selectAllProductImages');

    const bulkDeleteProductImageModal = document.getElementById('productImageBulkDeleteModal');
    const bulkDeleteProductImageCancelBtn = document.getElementById('bulkDeleteProductImageCancelBtn');
    const bulkDeleteProductImageConfirmBtn = document.getElementById('bulkDeleteProductImageConfirmBtn');
    const bulkDeleteProductImageCount = document.getElementById('bulkDeleteProductImageCount');

    // Select All
    if (selectAllProductImages) {
        selectAllProductImages.addEventListener('change', function () {
            document.querySelectorAll('.productImageCheckbox').forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteProductImageBtn();
        });
    }

    // Toggle bulk delete button
    const toggleBulkDeleteProductImageBtn = () => {
        const selected = document.querySelectorAll('.productImageCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteProductImageBtn.classList.remove('hidden');
            bulkDeleteProductImageBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteProductImageBtn.classList.add('hidden');
            bulkDeleteProductImageBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('productImageCheckbox')) {
            toggleBulkDeleteProductImageBtn();
        }
    });

    // Handle bulk delete button
    if (bulkDeleteProductImageBtn) {
        bulkDeleteProductImageBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.productImageCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No product images selected.", true);
                return;
            }

            // Show modal
            bulkDeleteProductImageCount.textContent = selectedIds.length;
            bulkDeleteProductImageModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            bulkDeleteProductImageModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel button
            bulkDeleteProductImageCancelBtn.onclick = () => {
                bulkDeleteProductImageModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                bulkDeleteProductImageModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            };

            // Confirm delete
            bulkDeleteProductImageConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeleteproductimages', true);
                selectedIds.forEach(id => formData.append('productimageids[]', id));

                fetch('../Admin/product_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("Selected product images deleted successfully.");
                        bulkDeleteProductImageBtn.classList.add('hidden');
                        selectAllProductImages.checked = false;
                        // Reload table or re-fetch data
                        location.reload();
                    } else {
                        showAlert(data.message || "Failed to delete selected product images.", true);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showAlert("An error occurred. Please try again.", true);
                })
                .finally(() => {
                    bulkDeleteProductImageModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    bulkDeleteProductImageModal.classList.remove("opacity-100", "translate-y-0");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                });
            };
        });
    }

    // Initial binding
    bindActionButtons();
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
        let fetchUrl = `../Admin/add_size.php?productsizepage=${currentPage}`;
        
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
            const response = await fetch(`../Admin/add_size.php?action=getProductName&id=${productId}`);
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

                fetch(`../Admin/add_size.php?action=getProductSizeDetails&id=${productSizeId}`)
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

                fetch(`../Admin/add_size.php?action=getProductSizeDetails&id=${productSizeId}`)
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
            productSizeForm.reset();
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

            const formData = new FormData(productSizeForm);
            formData.append('addproductsize', true);

            fetch('add_size.php', {
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

                const formData = new FormData(updateProductSizeForm);
                formData.append('editproductsize', true);

                fetch('add_size.php', {
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

                fetch('../Admin/add_size.php', {
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

    // Bulk Delete
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const bulkDeleteModal = document.getElementById('productSizeBulkDeleteModal');
    const bulkDeleteCancelBtn = document.getElementById('productSizeBulkCancelDeleteBtn');
    const bulkDeleteForm = document.getElementById('productSizeBulkDeleteForm');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');

    // Handle select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteBtn();
        });
    }

    // Toggle bulk delete button visibility
    const toggleBulkDeleteBtn = () => {
        const selected = document.querySelectorAll('.rowCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
            bulkDeleteBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('rowCheckbox')) {
            toggleBulkDeleteBtn();
        }
    });

    // Open bulk delete modal
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) {
                showAlert("No product sizes selected.", true);
                return;
            }

            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            bulkDeleteCount.textContent = selectedIds.length;
            bulkDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

            // Attach IDs to form for submission
            bulkDeleteForm.dataset.ids = JSON.stringify(selectedIds);
        });
    }

    // Cancel bulk delete
    if (bulkDeleteCancelBtn) {
        bulkDeleteCancelBtn.addEventListener('click', () => {
            bulkDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });
    }

    // Confirm bulk delete
    if (bulkDeleteForm) {
        bulkDeleteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const selectedIds = JSON.parse(bulkDeleteForm.dataset.ids || "[]");

            const formData = new FormData();
            formData.append('bulkdeleteproductsizes', true);
            selectedIds.forEach(id => formData.append('productsizeids[]', id));

            fetch('../Admin/add_size.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bulkDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                    darkOverlay2.classList.add('opacity-0', 'invisible');
                    darkOverlay2.classList.remove('opacity-100');

                    fetchAndRenderProductSizes();
                    showAlert("Selected product sizes deleted successfully.");
                    bulkDeleteBtn.classList.add('hidden');
                    selectAllCheckbox.checked = false;
                } else {
                    showAlert(data.message || "Failed to delete selected product sizes.", true);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showAlert("An error occurred. Please try again.", true);
            });
        });
    }

    // Initialize existing rows on page load and update product names
    initializeExistingRows();
    updateProductNames();
});

// Menu Form and Modals
document.addEventListener("DOMContentLoaded", () => {
    // Add Menu Modal Elements
    const addMenuModal = document.getElementById('addMenuModal');
    const menuForm = document.getElementById("menuForm");
    const addMenuBtn = document.getElementById('addMenuBtn');
    const addMenuCancelBtn = document.getElementById('addMenuCancelBtn');
    const darkOverlay2 = document.getElementById('darkOverlay2'); 

    // Update Menu Modal Elements
    const updateMenuModal = document.getElementById('updateMenuModal');
    const updateMenuModalCancelBtn = document.getElementById('updateMenuModalCancelBtn');
    const updateMenuForm = document.getElementById('updateMenuForm');

    // Delete Menu Modal Elements
    const menuConfirmDeleteModal = document.getElementById('menuConfirmDeleteModal');
    const menuCancelDeleteBtn = document.getElementById('menuCancelDeleteBtn');

    // Get current pagination and search parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('menupage') || 1;
    const currentSearch = urlParams.get('menu_search') || '';
    const currentSort = urlParams.get('sort') || 'random';

    // Function to clear errors
    const clearErrors = () => {
        const errors = ['menuNameError', 'menuDescriptionError', 'updateMenuNameError', 'updateMenuDescriptionError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
    }

    // Function to close the add modal
    const closeModal = () => {
        // Hide elements with transitions
        const modals = (modal) => {
            modal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        };
        
        [addMenuModal, updateMenuModal].forEach(modals);
        
        // Hide overlay
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        
        menuForm.reset();
        clearErrors();
    };

    // Function to fetch and render menus with current pagination
    const fetchAndRenderMenu = () => {
        let fetchUrl = `../Admin/add_menu.php?menupage=${currentPage}`;
        
        if (currentSearch) {
            fetchUrl += `&menu_search=${encodeURIComponent(currentSearch)}`;
        }
        if (currentSort !== 'random') {
            fetchUrl += `&sort=${currentSort}`;
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
            .catch(error => console.error('Error fetching menus:', error));
    };

    // Function to attach event listeners to a row
    const attachEventListenersToRow = (row) => {
        // Details button
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const menuId = this.getAttribute('data-menu-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/add_menu.php?action=getMenuDetails&id=${menuId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateMenuID').value = menuId;
                            document.getElementById('updateMenuNameInput').value = data.menu.MenuName;
                            document.getElementById('updateMenuDescriptionInput').value = data.menu.Description;
                            document.getElementById('updateStartTime').value = formatTimeForInput(data.menu.StartTime);
                            document.getElementById('updateEndTime').value = formatTimeForInput(data.menu.EndTime);
                            document.getElementById('updateStatus').value = data.menu.Status;
                            updateMenuModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load menu details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }

        // Delete button
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const menuId = this.getAttribute('data-menu-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                document.getElementById('deleteMenuID').value = menuId;
                document.getElementById('menuDeleteName').textContent = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                menuConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
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

    // Add Menu Modal
    if (addMenuModal && addMenuBtn && addMenuCancelBtn) {
        addMenuBtn.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            addMenuModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        addMenuCancelBtn.addEventListener('click', () => {
            closeModal();
        });
    }

    // Menu Form Submission
    document.getElementById("menuNameInput")?.addEventListener("keyup", validateMenuName);
    document.getElementById("menuDescriptionInput")?.addEventListener("keyup", validateMenuDescription);

    // Menu Form Submission
    if (menuForm) {
        menuForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateMenuForm()) return;

            const formData = new FormData(menuForm);
            formData.append('addmenu', true);

            fetch('../Admin/add_menu.php', {
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
                showAlert(data.message, !data.success);

                if (data.success) {
                    menuForm.reset();
                    closeModal();
                    fetchAndRenderMenu();
                }
            })
            .catch(err => {
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }

    // Menu Update Form Submission
    document.getElementById("updateMenuNameInput")?.addEventListener("keyup", validateUpdateMenuName);
    document.getElementById("updateMenuDescriptionInput")?.addEventListener("keyup", validateUpdateMenuDescription);

    // Update Menu Modal 
    if (updateMenuModal && updateMenuModalCancelBtn) {
        updateMenuModalCancelBtn.addEventListener('click', () => {
            updateMenuModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
            updateMenuForm.reset();

            closeModal();
        });

        if (updateMenuForm) {
            updateMenuForm.addEventListener("submit", (e) => {
                e.preventDefault();

            if (!validateUpdateMenuForm()) return;

                const formData = new FormData(updateMenuForm);
                formData.append('editmenu', true);

                fetch('../Admin/add_menu.php', {
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
                    showAlert(data.message, !data.success);

                    if (data.success) {
                        updateMenuForm.reset();
                        updateMenuModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderMenu();
                    }
                })
                .catch(err => {
                    showAlert("Something went wrong. Please try again.", true);
                    console.error(err);
                });
            });
        }
    }

    // Delete Menu Modal
    if (menuConfirmDeleteModal && menuCancelDeleteBtn) {
        menuCancelDeleteBtn.addEventListener('click', () => {
            menuConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
        });

        const menuDeleteForm = document.getElementById('menuDeleteForm');
        if (menuDeleteForm) {
            menuDeleteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(menuDeleteForm);
                formData.append('deletemenu', true);

                fetch('../Admin/add_menu.php', {
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
                        menuConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                        fetchAndRenderMenu();
                        showAlert('The menu has been successfully deleted.');
                    } else {
                        showAlert(data.message || 'Failed to delete menu.', true);
                    }
                })
                .catch((err) => {
                    console.error('Error:', err);
                    showAlert('An error occurred. Please try again.', true);
                });
            });
        }
    }

    // Bulk Delete for Menus
    const bulkDeleteMenuBtn = document.getElementById('bulkDeleteMenuBtn');
    const selectAllMenuCheckbox = document.getElementById('selectAllMenuCheckbox');

    const menuBulkDeleteModal = document.getElementById('menuBulkDeleteModal');
    const bulkDeleteMenuCancelBtn = document.getElementById('bulkDeleteMenuCancelBtn');
    const bulkDeleteMenuConfirmBtn = document.getElementById('bulkDeleteMenuConfirmBtn');
    const menuBulkDeleteCount = document.getElementById('menuBulkDeleteCount');

    // Handle select all
    if (selectAllMenuCheckbox) {
        selectAllMenuCheckbox.addEventListener('change', function () {
            const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleMenuBulkDeleteBtn();
        });
    }

    // Toggle button
    const toggleMenuBulkDeleteBtn = () => {
        const selected = document.querySelectorAll('.rowCheckbox:checked');
        if (selected.length > 0) {
            bulkDeleteMenuBtn.classList.remove('hidden');
            bulkDeleteMenuBtn.textContent = `Delete Selected (${selected.length})`;
        } else {
            bulkDeleteMenuBtn.classList.add('hidden');
            bulkDeleteMenuBtn.textContent = "Delete Selected";
        }
    };

    // Watch row checkboxes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('rowCheckbox')) {
            toggleMenuBulkDeleteBtn();
        }
    });

    // Handle bulk delete button
    if (bulkDeleteMenuBtn) {
        bulkDeleteMenuBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert("No menus selected.", true);
                return;
            }

            // Show confirm modal
            menuBulkDeleteCount.textContent = selectedIds.length;
            menuBulkDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
            menuBulkDeleteModal.classList.add("opacity-100", "translate-y-0");
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");

            // Cancel
            bulkDeleteMenuCancelBtn.addEventListener('click', () => {
                menuBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                menuBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                darkOverlay2.classList.add("opacity-0", "invisible");
                darkOverlay2.classList.remove("opacity-100");
            });

            // Confirm
            bulkDeleteMenuConfirmBtn.onclick = () => {
                const formData = new FormData();
                formData.append('bulkdeletemenus', true);
                selectedIds.forEach(id => formData.append('menuids[]', id));

                fetch('../Admin/add_menu.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchAndRenderMenu(); 
                        showAlert("Selected menus deleted successfully.");
                        bulkDeleteMenuBtn.classList.add('hidden');
                        selectAllMenuCheckbox.checked = false;
                    } else {
                        showAlert(data.message || "Failed to delete selected menus.", true);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showAlert("An error occurred. Please try again.", true);
                })
                .finally(() => {
                    menuBulkDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
                    menuBulkDeleteModal.classList.remove("opacity-100", "translate-y-0");
                    darkOverlay2.classList.add("opacity-0", "invisible");
                    darkOverlay2.classList.remove("opacity-100");
                });
            };
        });
    }

    // Initialize existing rows on page load
    initializeExistingRows();
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
                fetch(`../Admin/role_management.php?action=getAdminDetails&id=${adminId}`)
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
                window.location.href = 'role_management.php';
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
    const loader = document.getElementById('loader');
    const confirmContactForm = document.getElementById('confirmContactForm');

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
                fetch(`../Admin/user_contact.php?action=getContactDetails&id=${contactId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Fill the modal form with contact data
                            document.getElementById('confirmContactID').value = contactId;
                            document.getElementById('contactDate').textContent = data.contact.ContactDate;
                            document.getElementById('contactMessage').textContent = data.contact.ContactMessage;
                            document.getElementById('username').value = data.contact.FullName;
                            document.getElementById('useremail').value = data.contact.UserEmail;
                            document.getElementById('contactMessageInput').value = data.contact.ContactMessage;
                            
                            // Display the values in the div elements
                            document.getElementById('displayUsername').textContent = data.contact.FullName;
                            document.getElementById('displayUseremail').textContent = data.contact.UserEmail;
                            document.getElementById('userphone').textContent = data.contact.UserPhone;
                            document.getElementById('usercountry').textContent = data.contact.Country;

                            // Show the modal
                            confirmContactModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load contact details');
                            showAlert('Failed to load contact details. Please try again.', true);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showAlert('Failed to load contact details. Please try again.', true);
                        // Hide the overlay if there's an error
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                    });
            });
        });

        confirmContactModalCancelBtn.addEventListener('click', () => {
            confirmContactModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            document.getElementById('darkOverlay2').classList.add('opacity-0', 'invisible');
            document.getElementById('darkOverlay2').classList.remove('opacity-100');

            // Clear the form
            document.getElementById('confirmContactForm').reset();
        });

        // Handle form submission with AJAX
        if (confirmContactForm) {
            confirmContactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(this);
                formData.append('respondcontact', '1');
                
                // First update the contact status
                fetch('../Admin/user_contact.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (loader) loader.style.display = 'flex';
                        
                        // If contact update was successful, send the email
                        return fetch('../Mail/contact.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'sendContactResponse'
                            })
                        });
                    } else {
                        throw new Error(data.message || 'Failed to update contact');
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (loader) loader.style.display = 'none';
                    
                    if (data.success) {
                        showAlert('Response sent successfully and email notification delivered.');
                        // Close the modal
                        confirmContactModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        document.getElementById('darkOverlay2').classList.add('opacity-0', 'invisible');
                        document.getElementById('darkOverlay2').classList.remove('opacity-100');
                    } else {
                        showAlert(`Response saved but failed to send email notification: ${data.message || 'Please try again.'}`, true);
                        console.error(data.message);
                    }
                })
                .catch(error => {
                    if (loader) loader.style.display = 'none';
                    console.error('Error:', error);
                    
                    // More detailed error message
                    let errorMsg = 'An error occurred. Please try again.';
                    if (error.message.includes('Failed to fetch')) {
                        errorMsg = 'Network error. Please check your connection.';
                    } else if (error.message.includes('HTTP error')) {
                        errorMsg = 'Server error. Please try again later.';
                    }
                    
                    showAlert(errorMsg, true);
                });
            });
        }

        if (alertMessage) {
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

            fetch('../Admin/admin_profile_edit.php', {
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
        fetch("../Admin/admin_account_delete.php", {
            method: "POST",
        })
            .then(() => {
                // Redirect after account deletion
                window.location.href = "admin_signin.php";
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
            fetch('../Admin/admin_profile_edit.php', {
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
    const resetBtns = document.querySelectorAll('.reset-btn button');

    if (resetAdminPasswordModal && adminResetPasswordCancelBtn && resetBtns) {
        resetBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const adminId = this.getAttribute('data-admin-id');

                // Fetch admin details
                fetch(`../Admin/role_management.php?action=getAdminDetails&id=${adminId}`)
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

        adminResetPasswordCancelBtn.addEventListener('click', () => {
            resetAdminPasswordModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            if (darkOverlay2) {
                darkOverlay2.classList.add('opacity-0', 'invisible');
                darkOverlay2.classList.remove('opacity-100');
            }
        });
    }
});

// Reset Password Form Handling
document.addEventListener("DOMContentLoaded", () => {
    const adminResetPasswordForm = document.getElementById('adminResetPasswordForm');
    const loader = document.getElementById('loader');

    if (adminResetPasswordForm) {
        adminResetPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (loader) loader.style.display = 'flex';
            
            const formData = new FormData(adminResetPasswordForm);

            fetch('../Admin/role_management.php', {
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
                    // Hide modal
                    resetAdminPasswordModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                    if (darkOverlay2) {
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                    }

                    showAlert(data.message);
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

// Product Purchase 
document.addEventListener('DOMContentLoaded', function() {
    var productSelect = document.getElementById('productID');
    var addToListBtn = document.getElementById('addToListBtn');
    var completePurchaseBtn = document.getElementById('completePurchaseBtn');

    // Function to update form fields
    function updateFormFields(selectedOption) {
        document.getElementById('producttitle').value = selectedOption.getAttribute('data-title') || '';
        document.getElementById('hidden_producttitle').value = selectedOption.getAttribute('data-title') || '';
        document.getElementById('productbrand').value = selectedOption.getAttribute('data-brand') || '';
        document.getElementById('productprice').value = selectedOption.getAttribute('data-price') || '';
        document.getElementById('hidden_productprice').value = selectedOption.getAttribute('data-price') || '';
        document.getElementById('productstock').value = selectedOption.getAttribute('data-stock') || '';
        document.getElementById('producttype').value = selectedOption.getAttribute('data-type') || '';
    }

    // Event listener for product selection change
    productSelect.addEventListener('change', function() {
        updateFormFields(this.options[this.selectedIndex]);
    });

    // If a product is preselected from URL, update the form fields
    if (productSelect.value) {
        updateFormFields(productSelect.options[productSelect.selectedIndex]);
    }

    // Add to list button click handler
    addToListBtn.addEventListener('click', function() {
        const productID = document.getElementById('productID').value;
        const productTitle = document.getElementById('hidden_producttitle').value;
        const quantity = document.getElementById('quantity').value;
        const productPrice = document.getElementById('hidden_productprice').value;

        if (!productID) {
            showAlert('Please select a product to purchase.', true);
            return;
        }

        if (!quantity || quantity <= 0) {
            showAlert('Choose a quantity greater than 0.', true);
            return;
        }

        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'add_to_cart');
        formData.append('productID', productID);
        formData.append('producttitle', productTitle);
        formData.append('quantity', quantity);
        formData.append('productprice', productPrice);

        fetch('../Admin/product_purchase.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, false);
                    // Update cart table
                    updateCartTable();
                    // Reset form
                    document.getElementById('quantity').value = '';
                } else {
                    showAlert(data.message, true);
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', true);
                console.error('Error:', error);
            });
    });

    // Remove item button handlers
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const index = e.target.getAttribute('data-index');
            removeCartItem(index);
        }
    });

    // Handle quantity changes dynamically
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cart-quantity')) {
            const index = e.target.getAttribute('data-index');
            let newQuantity = parseInt(e.target.value);

            if (isNaN(newQuantity) || newQuantity <= 0) {
                showAlert('Quantity must be at least 1.', true);
                e.target.value = 1;
                newQuantity = 1;
            }

            const formData = new FormData();
            formData.append('ajax', 'true');
            formData.append('action', 'update_quantity');
            formData.append('index', index);
            formData.append('quantity', newQuantity);

            fetch('../Admin/product_purchase.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart table and show message
                        updateCartTable();
                        showAlert(data.message, false);
                    } else {
                        showAlert(data.message, true);
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', true);
                    console.error('Error:', error);
                });
        }
    });

    // Complete purchase button click handler
    if (completePurchaseBtn) {
        completePurchaseBtn.addEventListener('click', function() {
            const supplierID = document.getElementById('supplierID').value;

            if (!supplierID) {
                showAlert('Please select a supplier.', true);
                return;
            }

            const formData = new FormData();
            formData.append('ajax', 'true');
            formData.append('action', 'complete_purchase');
            formData.append('supplierID', supplierID);

            fetch('../Admin/product_purchase.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, false);
                        // Update cart table and hide purchase form
                        updateCartTable();
                        productSelect.value = "";
                        document.getElementById('supplierID').value = "";
                        document.getElementById('addToListForm').reset();
                    } else {
                        showAlert(data.message, true);
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', true);
                    console.error('Error:', error);
                });
        });
    }

    // Function to update cart table via AJAX
    function updateCartTable() {
        fetch('../Admin/product_purchase.php?get_cart_table=true')
            .then(response => response.text())
            .then(html => {
                document.getElementById('cartTableContainer').innerHTML = html;
                // Show/hide purchase form based on cart content
                if (document.querySelectorAll('#cartTableContainer tr').length > 1) {
                    if (!document.getElementById('purchaseForm')) {
                        // Reload page to show purchase form
                    } else {
                        document.getElementById('purchaseForm').style.display = 'block';
                    }
                } else {
                    if (document.getElementById('purchaseForm')) {
                        document.getElementById('purchaseForm').style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
            });
    }

    // Function to remove cart item
    function removeCartItem(index) {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'remove_item');
        formData.append('removeIndex', index);

        fetch('../Admin/product_purchase.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, false);
                    // Update cart table
                    updateCartTable();
                } else {
                    showAlert(data.message, true);
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', true);
                console.error('Error:', error);
            });
    }
});

// Reservation Details Modal
document.addEventListener("DOMContentLoaded", () => {
    const reservationModal = document.getElementById('reservationModal');
    const closeReservationDetailButton = document.getElementById('closeReservationDetailButton');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    function formatDate(dateString, options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) {
        if (!dateString) return "N/A";
        return new Date(dateString).toLocaleDateString('en-US', options);
    }

    function relativeTimeFromNow(dateString) {
        if (!dateString) return '';
        const now = new Date();
        const then = new Date(dateString);
        const diffMs = then - now;
        const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
        if (diffMs <= 0) return '(expired)';
        if (diffDays === 0) return '(today)';
        if (diffDays === 1) return '(in 1 day)';
        return `(in ${diffDays} days)`;
    }

    let roomSwiper = null; // Keep Swiper instance

    // reservations: array of rooms (reservation.Rooms), nights: calculated from top-level CheckIn/CheckOut
    function initializeRoomSwiper(reservations, nights) {
        const swiperWrapper = document.querySelector('#roomContainer .swiper-wrapper');
        if (!swiperWrapper) return;
        swiperWrapper.innerHTML = '';

        // Destroy previous swiper if exists
        if (roomSwiper) {
            try { roomSwiper.destroy(true, true); } catch (e) { /* ignore */ }
            roomSwiper = null;
        }

        reservations.forEach(room => {
            const roomPrice = parseFloat(room.Price || 0); // stored as total for the stay
            const perNight = (nights > 0) ? (roomPrice / nights) : roomPrice;

            const slide = document.createElement('div');
            slide.className = 'swiper-slide';
            slide.innerHTML = `
                <div class="flex flex-col md:flex-row gap-4 py-2 px-3">
                    <div class="md:w-1/3 select-none">
                        <div class="relative h-48 md:h-40">
                            <img src="../Admin/${room.RoomCoverImage}" 
                                 alt="${room.RoomType}" 
                                 class="w-full h-full object-cover rounded-lg">
                        </div>
                    </div>
                    <div class="md:w-2/3">
                        <h5 class="font-bold text-lg text-gray-800">${room.RoomType}</h5>
                        <p class="text-sm text-gray-600 mt-1">
                            ${room.RoomDescription && room.RoomDescription.length > 150 
                                ? room.RoomDescription.substring(0, 150) + "..." 
                                : (room.RoomDescription || '')}
                        </p>
                        <div class="mt-2 text-xs text-gray-500">
                            <div>Room: <span class="font-medium">${room.RoomName}</span></div>
                            <div>Guests: <span class="font-medium">${room.Adult || 0} Adult${(room.Adult||0) > 1 ? 's' : ''}${(room.Children||0) > 0 ? `, ${room.Children} Child${(room.Children||0) > 1 ? 'ren' : ''}` : ''}</span></div>
                            <div>Price (stay): <span class="font-medium">$${roomPrice.toFixed(2)}</span></div>
                            <div class="text-xs text-gray-400">(${nights} night${nights > 1 ? 's' : ''} — $${perNight.toFixed(2)}/night)</div>
                        </div>
                    </div>
                </div>
            `;
            swiperWrapper.appendChild(slide);
        });

        // Initialize new swiper (or re-init)
        roomSwiper = new Swiper('.roomTypeSwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            centeredSlides: true,
            pagination: { el: '.swiper-pagination', clickable: true },
            breakpoints: { 768: { slidesPerView: 1, spaceBetween: 30 } }
        });
    }

    const attachReservationListenersToRow = (row) => {
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const reservationId = this.getAttribute('data-reservation-id');

                // Show modal
                if (darkOverlay2) {
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');
                }
                reservationModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

                // Fetch reservation details
                fetch(`../Admin/reservation.php?action=getReservationDetails&id=${reservationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success || !data.reservation) {
                            console.error('Failed to load reservation details');
                            return;
                        }

                        const reservation = data.reservation;

                        // User Info
                        document.getElementById('userName').textContent = `${reservation.Title ?? ''} ${reservation.FirstName ?? ''} ${reservation.LastName ?? ''}`.trim();
                        document.getElementById('userPhone').textContent = reservation.UserPhone ?? "N/A";

                        // Email with mailto link
                        const userEmailEl = document.getElementById('userEmail');
                        if (reservation.UserEmail) {
                            userEmailEl.textContent = reservation.UserEmail;
                            userEmailEl.href = `mailto:${reservation.UserEmail}`;
                        } else {
                            userEmailEl.textContent = "N/A";
                            userEmailEl.removeAttribute('href');
                        }

                        // Reservation date & expiry
                        document.getElementById('reservationDate').textContent = formatDate(reservation.ReservationDate);
                        if (reservation.ExpiryDate) {
                            const expiryText = `Expires on ${formatDate(reservation.CheckInDate, { year: 'numeric', month: 'short', day: 'numeric' })} ${relativeTimeFromNow(reservation.CheckInDate)}`;
                            const expiryEl = document.getElementById('reservationExpiry');
                            expiryEl.textContent = expiryText;

                            // highlight if < 2 days
                            const expiryDate = new Date(reservation.CheckInDate);
                            const diffDays = Math.ceil((expiryDate - new Date()) / (1000*60*60*24));
                            expiryEl.classList.remove('text-red-500', 'text-gray-500', 'text-yellow-600');
                            if (diffDays <= 2) expiryEl.classList.add('text-red-500');
                            else if (diffDays <= 7) expiryEl.classList.add('text-yellow-600');
                            else expiryEl.classList.add('text-gray-500');
                        } else {
                            document.getElementById('reservationExpiry').textContent = '';
                        }

                        // Rooms array
                        const rooms = Array.isArray(reservation.Rooms) ? reservation.Rooms : (reservation.Rooms ? [reservation.Rooms] : []);

                        // Determine nights — if different per room is needed, server must return per-room dates.
                        const checkIn = reservation.CheckInDate ? new Date(reservation.CheckInDate) : null;
                        const checkOut = reservation.CheckOutDate ? new Date(reservation.CheckOutDate) : null;
                        const nights = (checkIn && checkOut) ? Math.max(1, Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24))) : 1;

                        // Itemize each room and compute subtotal
                        const subtotal = rooms.reduce((sum, r) => sum + (parseFloat(r.Price) || 0), 0);
                        const taxesFees = subtotal * 0.10;
                        const pointsDiscount = parseFloat(reservation.PointsDiscount || 0) || 0;
                        const total = subtotal + taxesFees - pointsDiscount;

                        // Update UI: room breakdown (per room), subtotal, taxes, total
                        const roomRateLabel = document.getElementById('roomRateLabel');
                        const roomRateEl = document.getElementById('roomRate');
                        const taxesFeesEl = document.getElementById('taxesFees');
                        const totalPriceEl = document.getElementById('totalPrice');

                        roomRateLabel.textContent = `Rooms Subtotal (${rooms.length} room${rooms.length>1?'s':''}, ${nights} night${nights>1?'s':''}):`;
                        roomRateEl.textContent = `$ ${subtotal.toFixed(2)}`;
                        taxesFeesEl.textContent = `$ ${taxesFees.toFixed(2)}`;
                        totalPriceEl.textContent = `$ ${total.toFixed(2)}`;

                        // Points discount (show/hide)
                        if ((parseFloat(reservation.PointsRedeemed) || 0) > 0 || pointsDiscount > 0) {
                            document.getElementById('pointsDiscountContainer').classList.remove('hidden');
                            document.getElementById('pointsDiscount').textContent = `- $ ${pointsDiscount.toFixed(2)}`;
                        } else {
                            document.getElementById('pointsDiscountContainer').classList.add('hidden');
                        }

                        // Points earned (show/hide)
                        if ((parseFloat(reservation.PointsEarned) || 0) > 0) {
                            document.getElementById('pointsEarnedContainer').classList.remove('hidden');
                            document.getElementById('pointsEarned').textContent = `+ ${parseFloat(reservation.PointsEarned).toFixed(0)} points`;
                        } else {
                            document.getElementById('pointsEarnedContainer').classList.add('hidden');
                        }

                        // Create or update itemized room list under pricing
                        (function renderRoomBreakdown() {
                            // find the pricing container
                            const roomRateEl = document.getElementById('roomRate');
                            const pricingBlock = roomRateEl ? roomRateEl.closest('.space-y-3') : null;
                            if (!pricingBlock) return;

                            let breakdown = document.getElementById('roomBreakdown');
                            if (!breakdown) {
                                breakdown = document.createElement('div');
                                breakdown.id = 'roomBreakdown';
                                breakdown.className = 'space-y-1 text-sm text-gray-700';
                                // insert before taxes row (taxesFees's parent)
                                const taxesRow = document.getElementById('taxesFees')?.parentElement;
                                if (taxesRow) pricingBlock.insertBefore(breakdown, taxesRow);
                                else pricingBlock.appendChild(breakdown);
                            }
                            // fill breakdown
                            breakdown.innerHTML = rooms.map(r => {
                                const rPrice = parseFloat(r.Price || 0);
                                const perNight = nights > 0 ? (rPrice / nights) : rPrice;
                                return `<div class="flex justify-between"><span>${r.RoomName || 'Room'} <span class="text-xs text-gray-500">(${nights} night${nights>1?'s':''})</span></span><span class="font-medium">$${rPrice.toFixed(2)} <span class="text-xs text-gray-400">($${perNight.toFixed(2)}/night)</span></span></div>`;
                            }).join('');
                        })();

                        // Initialize swiper with rooms and nights so slide shows correct per-room totals
                        initializeRoomSwiper(rooms, nights);
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
            });
        }
    };

    closeReservationDetailButton.addEventListener('click', () => {
        reservationModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        if (darkOverlay2) {
            darkOverlay2.classList.remove('opacity-100');
            darkOverlay2.classList.add('opacity-0', 'invisible');
        }
    });

    // Load Swiper library if missing
    if (!document.querySelector('link[href*="swiper-bundle.min.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css';
        document.head.appendChild(link);
    }
    if (!window.Swiper) {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js';
        document.body.appendChild(script);
    }

    document.querySelectorAll('tbody tr').forEach(row => attachReservationListenersToRow(row));
});

// Order Details Modal
document.addEventListener("DOMContentLoaded", () => {
    const orderModal = document.getElementById('orderModal');
    const closeOrderDetailButton = document.getElementById('closeOrderDetailButton');
    const darkOverlay2 = document.getElementById('darkOverlay2');
    const shipOrderButton = document.getElementById('shipOrderButton');

    let currentOrderId = null;

    function formatDate(dateString) {
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }

    let orderSwiper = null;

    function initializeOrderProductSwiper(products) {
        const swiperWrapper = document.querySelector('#orderProductContainer .swiper-wrapper');
        swiperWrapper.innerHTML = '';

        if (orderSwiper) {
            orderSwiper.destroy(true, true);
            orderSwiper = null;
        }

        products.forEach(product => {
            const basePrice = parseFloat(product.Price) || 0;
            const markup = parseFloat(product.MarkupPercentage) || 0;
            const realPrice = basePrice + (basePrice * (markup / 100));

            const slide = document.createElement('div');
            slide.className = 'swiper-slide';
            slide.innerHTML = `
            <div class="flex flex-col md:flex-row gap-4 py-2 px-3">
                <div class="md:w-1/3 select-none">
                    <div class="relative h-48 md:h-40">
                        <img src="../Admin/${product.ImageAdminPath}" 
                                alt="${product.Title}" 
                                class="w-full h-full object-cover rounded-lg">
                    </div>
                </div>
                <div class="md:w-2/3">
                    <h5 class="font-bold text-lg text-gray-800">${product.Title}</h5>
                    <p class="text-sm text-gray-600 mt-1">${product.Description}</p>
                    <div class="mt-2 text-xs text-gray-500">
                        Quantity: <span class="font-medium">${product.OrderUnitQuantity}</span><br>
                        Price: <span class="font-medium">$${realPrice.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;
            swiperWrapper.appendChild(slide);
        });

        orderSwiper = new Swiper('.orderProductSwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            centeredSlides: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true
            },
            breakpoints: {
                768: {
                    slidesPerView: 1,
                    spaceBetween: 30
                }
            }
        });
    }

    const attachOrderListenersToRow = (row) => {
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                currentOrderId = orderId;

                if (darkOverlay2) {
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');
                }
                orderModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

                fetch(`../Admin/order.php?action=getOrderDetails&id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.order) {
                            const order = data.order;

                            document.getElementById('userFullName').textContent = order.FullName ?? "N/A";
                            document.getElementById('userName').textContent = order.UserName ? order.UserName.charAt(0).toUpperCase() : "N/A";
                            document.getElementById('profilePreview').style.backgroundColor = order.ProfileBgColor ?? "#999";
                            document.getElementById('userEmail').textContent = order.UserEmail ?? "N/A";
                            document.getElementById('userPhone').textContent = order.UserPhone ?? "N/A";
                            document.getElementById('userAddress').textContent = order.ShippingAddress ?? "N/A";
                            document.getElementById('userCity').textContent = order.City ?? "N/A";
                            document.getElementById('userState').textContent = order.State ?? "N/A";
                            document.getElementById('userZip').textContent = order.ZipCode ?? "N/A";
                            document.getElementById('orderDate').textContent = formatDate(order.OrderDate);

                            document.getElementById('orderSubtotal').textContent = `$ ${parseFloat(order.Subtotal ?? 0).toFixed(2)}`;
                            document.getElementById('orderTaxesFees').textContent = `$ ${((parseFloat(order.OrderTax) || 0) + 5).toFixed(2)}`;
                            document.getElementById('orderTotal').textContent = `$ ${parseFloat(order.TotalPrice ?? 0).toFixed(2)}`;

                            const products = Array.isArray(order.Products) ? order.Products : [];
                            initializeOrderProductSwiper(products);
                        } else {
                            console.error('Failed to load order details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        }
    };

    closeOrderDetailButton.addEventListener('click', () => {
        orderModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        if (darkOverlay2) {
            darkOverlay2.classList.remove('opacity-100');
            darkOverlay2.classList.add('opacity-0', 'invisible');
        }
    });

    const loader = document.getElementById('loader');   

    // Ship Order handler 
    shipOrderButton.addEventListener('click', () => {
        if (!currentOrderId) return;

        // Show loader at the start
        if (loader) loader.style.display = 'flex';

        fetch(`../Admin/order.php?action=shipOrder&id=${currentOrderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    orderModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                    if (darkOverlay2) {
                        darkOverlay2.classList.remove('opacity-100');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                    }

                    // Send Email
                    const sendEmail = async () => {
                        try {
                            const response = await fetch(`../Mail/send_order_shipped_email.php?id=${currentOrderId}`);
                            const data = await response.json();
                            if (data.success) {
                                showAlert("Order has been marked as Shipped and email has been sent.");
                            } else {
                                showAlert("Failed to update order status.", true);
                            }
                        } catch (error) {
                            showAlert("Failed to update order status.", true);
                        } finally {
                            // Hide loader after email is sent
                            if (loader) loader.style.display = 'none';
                        }
                    };
                    sendEmail();
                } else {
                    showAlert("Failed to update order status.", true);
                    if (loader) loader.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Ship error:', error);
                if (loader) loader.style.display = 'none';
            });
    });

    // Load Swiper if missing
    if (!document.querySelector('link[href*="swiper-bundle.min.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css';
        document.head.appendChild(link);
    }
    if (!window.Swiper) {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js';
        document.body.appendChild(script);
    }

    document.querySelectorAll('tbody tr').forEach(row => attachOrderListenersToRow(row));
});

// Purchase History
document.addEventListener('DOMContentLoaded', function() {
    const darkOverlay2 = document.getElementById('darkOverlay2');
    const purchaseDetailsModal = document.getElementById('purchaseDetailsModal');
    const closePurchaseDetailsBtn = document.getElementById('closePurchaseDetailsBtn');

    // helper to format date/time
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        try {
            return new Date(dateString).toLocaleDateString('en-US', options);
        } catch (e) {
            return dateString;
        }
    }

    function showModal() {
        if (darkOverlay2) {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
        }
        if (purchaseDetailsModal) {
            purchaseDetailsModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            purchaseDetailsModal.classList.add('opacity-100', 'visible', 'translate-y-0');
        }
    }

    function hideModal() {
        if (darkOverlay2) {
            darkOverlay2.classList.remove('opacity-100');
            darkOverlay2.classList.add('opacity-0', 'invisible');
        }
        if (purchaseDetailsModal) {
            purchaseDetailsModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            purchaseDetailsModal.classList.remove('opacity-100', 'visible', 'translate-y-0');
        }
    }

    closePurchaseDetailsBtn && closePurchaseDetailsBtn.addEventListener('click', hideModal);
    // Close modal if overlay clicked
    if (darkOverlay2) {
        darkOverlay2.addEventListener('click', hideModal);
    }

    // fetch items and render into table body #purchaseItems
    function fetchPurchaseItems(purchaseId) {
        fetch(`../Admin/purchase_history.php?action=getPurchaseItems&id=${encodeURIComponent(purchaseId)}`)
            .then(response => response.json())
            .then(data => {
                const itemsContainer = document.getElementById('purchaseItems');
                if (!itemsContainer) return;
                itemsContainer.innerHTML = '';

                if (data.success && Array.isArray(data.items) && data.items.length) {
                    data.items.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.className = 'border-b border-gray-200 hover:bg-gray-50';
                        const qty = parseFloat(item.Quantity || 0);
                        const unit = parseFloat(item.UnitPrice || 0);
                        const total = qty * unit;

                        tr.innerHTML = `
                            <td class="p-3">${item.ProductName || item.ProductID || 'Product'}</td>
                            <td class="p-3">${qty}</td>
                            <td class="p-3">${unit.toFixed(2)}</td>
                            <td class="p-3">${total.toFixed(2)}</td>
                        `;
                        itemsContainer.appendChild(tr);
                    });
                } else {
                    itemsContainer.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-gray-500">No items found</td></tr>';
                }
            })
            .catch(err => {
                console.error('Error fetching purchase items:', err);
                const itemsContainer = document.getElementById('purchaseItems');
                if (itemsContainer) {
                    itemsContainer.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-red-500">Error loading items</td></tr>';
                }
            });
    }

    // Attach click behavior for each row's details button
    function attachEventListenersToRow(row) {
        if (!row) return;
        const detailsBtn = row.querySelector('.details-btn');
        if (!detailsBtn) return;

        detailsBtn.addEventListener('click', function() {
            const purchaseId = this.getAttribute('data-purchase-id');
            if (!purchaseId) return;

            // show overlay immediately
            if (darkOverlay2) {
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
            }

            // fetch header info
            fetch(`../Admin/purchase_history.php?action=getPurchaseDetails&id=${encodeURIComponent(purchaseId)}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.purchase) {
                        console.error('Failed to load purchase details', data);
                        // Hide overlay in case of error
                        if (darkOverlay2) {
                            darkOverlay2.classList.remove('opacity-100');
                            darkOverlay2.classList.add('opacity-0', 'invisible');
                        }
                        return;
                    }

                    const p = data.purchase;

                    // Map data into UI (IDs must exist in DOM)
                    document.getElementById('detailPurchaseID').textContent = p.PurchaseID || '';
                    document.getElementById('detailPurchaseDate').textContent = formatDate(p.PurchaseDate || p.AddedDate || '');
                    document.getElementById('detailAdmin').textContent = ((p.FirstName || '') + ' ' + (p.LastName || '')).trim();
                    document.getElementById('detailAdminEmail').textContent = p.AdminEmail || '';
                    document.getElementById('detailSupplier').textContent = p.SupplierName || '';
                    document.getElementById('detailSupplierEmail').textContent = p.SupplierEmail || '';
                    document.getElementById('detailTotalAmount').textContent = p.TotalAmount !== null ? ('$' + parseFloat(p.TotalAmount).toFixed(2)) : 'N/A';
                    document.getElementById('detailTax').textContent = p.PurchaseTax !== null ? ('$' + parseFloat(p.PurchaseTax).toFixed(2)) : 'N/A';
                    document.getElementById('detailStatus').textContent = p.Status || '';

                    // fetch and render items
                    fetchPurchaseItems(purchaseId);

                    // show modal
                    showModal();
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    if (darkOverlay2) {
                        darkOverlay2.classList.remove('opacity-100');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                    }
                });
        });
    }

    // Initialize existing rows on page load
    function initializeExistingRows() {
        // If the purchases table is a specific table, narrow selector
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    initializeExistingRows();
});

// User Details Modal
document.addEventListener("DOMContentLoaded", () => {
    const userDetailsModal = document.getElementById("userDetailsModal");
    const closeUserDetailButton = document.getElementById("closeUserDetailButton");
    const closeUserDetailFooterBtn = document.getElementById("closeUserDetailFooterBtn");
    const darkOverlay2 = document.getElementById("darkOverlay2");

    // Delete modal elements
    const userConfirmDeleteModal = document.getElementById("userConfirmDeleteModal");
    const userDeleteForm = document.getElementById("userDeleteForm");
    const profileDeleteBtn = document.getElementById("profileDeleteBtn");
    const userCancelDeleteBtn = document.getElementById("userCancelDeleteBtn");
    const deleteUserID = document.getElementById("deleteUserID");
    const userDeleteName = document.getElementById("userDeleteName");

    let currentUserId = null;
    let currentUserName = null;

    // Only run if modal exists on the page
    if (!userDetailsModal) return;

    // Populate modal with data
    function populateUserDetails(user) {
        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || "N/A";
        };

        setText("userName", user.UserName ? user.UserName.charAt(0).toUpperCase() : "?");
        setText("userDetailsName", user.UserName);
        setText("userDetailsEmail", user.UserEmail);
        setText("userDetailsPhone", user.UserPhone);
        setText("userDetailsID", user.UserID);
        setText("userDetailsStatus", user.Status);
        setText("userDetailsSignup", user.SignupDate);
        setText("userDetailsLastSignIn", user.LastSignIn);
        setText("userDetailsMembership", user.Membership == 1 ? "VIP Email" : "Standard Email");
        setText("userDetailsPoints", user.PointsBalance ?? 0);

        // Profile background color
        const profileBg = document.getElementById("userProfileBg");
        if (profileBg) profileBg.style.backgroundColor = user.ProfileBgColor || "#ccc";

        // Extra fields if exist
        setText("userDetailsAddress", user.Address);
        setText("userDetailsRole", user.Role);
        setText("userDetailsNotes", user.Notes);

        // Store user ID + Name for deletion modal
        currentUserId = user.UserID;
        currentUserName = user.UserName;
    }

    // Open modal
    function openUserModal() {
        userDetailsModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
        userDetailsModal.classList.add("opacity-100");
        if (darkOverlay2) {
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");
        }
    }

    // Close modal
    function closeUserModal() {
        userDetailsModal.classList.add("opacity-0", "invisible", "-translate-y-5");
        userDetailsModal.classList.remove("opacity-100");
        if (darkOverlay2) {
            darkOverlay2.classList.add("opacity-0", "invisible");
            darkOverlay2.classList.remove("opacity-100");
        }
        userDeleteForm.reset();
    }

    // Open Delete Confirmation Modal
    function openDeleteModal() {
        if (!userConfirmDeleteModal) return;
        userConfirmDeleteModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
        userConfirmDeleteModal.classList.add("opacity-100");

        if (userDeleteName) userDeleteName.textContent = currentUserName || "Unknown";
        if (deleteUserID) deleteUserID.value = currentUserId || "";

        if (darkOverlay2) {
            darkOverlay2.classList.remove("opacity-0", "invisible");
            darkOverlay2.classList.add("opacity-100");
        }
    }

    // Close Delete Confirmation Modal
    function closeDeleteModal() {
        if (!userConfirmDeleteModal) return;
        userConfirmDeleteModal.classList.add("opacity-0", "invisible", "-translate-y-5");
        userConfirmDeleteModal.classList.remove("opacity-100");

        userDetailsModal.classList.remove("opacity-0", "invisible", "-translate-y-5");
        userDetailsModal.classList.add("opacity-100");

        darkOverlay2.classList.remove("opacity-0", "invisible");
        darkOverlay2.classList.add("opacity-100");
    }

    // Attach event listeners to all details buttons
    document.querySelectorAll(".details-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const userId = btn.getAttribute("data-user-id");
            if (!userId) return;

            try {
                const response = await fetch(`?action=getUserDetails&id=${encodeURIComponent(userId)}`);
                const data = await response.json();

                if (data.success && data.user) {
                    populateUserDetails(data.user);
                    openUserModal();
                } else {
                    showAlert("User details not found.", true);
                }
            } catch (err) {
                console.error("Error fetching user details:", err);
            }
        });
    });

    const confirmInput = document.getElementById("deleteUserConfirmInput");
    const deleteBtn = document.getElementById("confirmUserDeleteBtn");

    if (confirmInput && deleteBtn) {
        confirmInput.addEventListener("input", () => {
            deleteBtn.disabled = confirmInput.value.toUpperCase() !== "DELETE";
        });
    }

    // Delete User
    if (deleteBtn && deleteUserID) {
        deleteBtn.addEventListener("click", (e) => {
            e.preventDefault();

            const userId = deleteUserID.value;
            if (!userId) return;

            // Get reason inputs
            const reasonSelect = document.getElementById("deleteReason");
            const customReason = document.getElementById("customDeleteReason");

            // Validation check
            if (!reasonSelect.value) {
                showAlert("Please select a reason for deleting this user.", true);
                return;
            }
            if (reasonSelect.value === "Other" && !customReason.value.trim()) {
                showAlert("Please provide a custom reason for deletion.", true);
                return;
            }

            // Prepare reason (use custom if "Other" selected)
            const finalReason =
                reasonSelect.value === "Other" ? customReason.value.trim() : reasonSelect.value;

            // Show loader at the start
            if (loader) loader.style.display = 'flex';

            const handleUserDelete = async () => {
                try {
                    const response = await fetch("../Admin/user_details.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams({
                            deleteuser: "1",
                            userid: userId,
                            reason: finalReason
                        })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        const sendUserDeleteEmail = async () => {
                            try {
                                const emailResponse = await fetch(`../Mail/send_user_delete_email.php?email=${encodeURIComponent(data.userEmail)}&name=${encodeURIComponent(data.userName)}&reason=${encodeURIComponent(finalReason)}`);

                                if (!emailResponse.ok) throw new Error("Network response was not ok");

                                const emailData = await emailResponse.json();

                                // Remove user from table
                                const userRow = document.querySelector(`.details-btn[data-user-id="${userId}"]`)?.closest("tr");
                                if (userRow) {
                                    userRow.remove();
                                }

                                if (emailData.success) {
                                    closeDeleteModal();
                                    closeUserModal();
                                    showAlert("User deleted successfully!");
                                } else {
                                    closeDeleteModal();
                                    closeUserModal();
                                    showAlert("User deleted successfully but failed to send delete email. Please contact support.", true);
                                }
                            } catch (error) {
                                closeDeleteModal();
                                closeUserModal();
                                showAlert("User deleted successfully but failed to send delete email. Please contact support.", true);
                            } finally {
                                // Hide loader when request completes 
                                if (loader) loader.style.display = 'none';
                            }
                        };

                        sendUserDeleteEmail();
                    } else {
                        showAlert(data.message || "Failed to delete user.", true);
                    }
                } catch (error) {
                    console.error("AJAX delete error:", error);
                }
            }

            handleUserDelete();
        });
    }

    // Cancel button
    const cancelBtn = document.getElementById("userCancelDeleteBtn");
    const modal = document.getElementById("userConfirmDeleteModal");
    if (cancelBtn && modal) {
        cancelBtn.addEventListener("click", () => {
            modal.classList.add("opacity-0", "invisible");
            modal.classList.remove("opacity-100");
        });
    }

    // Close modal on button clicks
    if (closeUserDetailButton) closeUserDetailButton.addEventListener("click", closeUserModal);
    if (closeUserDetailFooterBtn) closeUserDetailFooterBtn.addEventListener("click", closeUserModal);

    // Delete button (inside user details modal) -> open confirm modal
    if (profileDeleteBtn) profileDeleteBtn.addEventListener("click", () => {
        closeUserModal(); // close user details first
        openDeleteModal(); // open delete confirmation
    });

    // Cancel delete
    if (userCancelDeleteBtn) userCancelDeleteBtn.addEventListener("click", closeDeleteModal);
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

const validateMenuForm = () => {
    const isMenuNameValid = validateMenuName();
    const isMenuDescriptionValid = validateMenuDescription();

    return isMenuNameValid && isMenuDescriptionValid;
}

const validateUpdateMenuForm = () => {
    const isMenuNameValid = validateUpdateMenuName();
    const isMenuDescriptionValid = validateUpdateMenuDescription();

    return isMenuNameValid && isMenuDescriptionValid;
}

// Individual validation functions
const validateProductType = () => {
    return validateField(
        "productTypeInput",
        "productTypeError",
        (input) => {
            if (!input) {
                return "Type is required.";
            }
            if (input.length > 30) {
                return "Type is too long.";
            }
            return null; 
        }
    );
};

const validateUpdateProductType = () => {
    return validateField(
        "updateProductTypeInput",
        "updateProductTypeError",
        (input) => {
            if (!input) {
                return "Type is required.";
            }
            if (input.length > 30) {
                return "Type is too long.";
            }
            return null; 
        }
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
        (input) => {
            if (!input) {
                return "Brand is required.";
            }
            if (input.length > 30) {
                return "Brand is too long.";
            }
            return null;
        }
    );
}

const validateUpdateBrand = () => {
    return validateField(
        "updateBrandInput",
        "updateBrandError",
        (input) => {
            if (!input) {
                return "Brand is required.";
            }
            if (input.length > 30) {
                return "Brand is too long.";
            }
            return null;
        }
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
        (input) => {
            if (!input) {
                return "Size is required.";
            }
            if (input.length > 30) {
                return "Size is too long.";
            }
            return null;
        }
    );
}

const validateUpdateProductSize = () => {
    return validateField(
        "updateSizeInput",
        "updateSizeError",
        (input) => {
            if (!input) {
                return "Size is required.";
            }
            if (input.length > 30) {
                return "Size is too long.";
            }
            return null;
        }
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
        (input) => {
            if (!input) {
                return "Supplier name is required.";
            }
            if (input.length > 30) {
                return "Supplier name is too long.";
            }
            return null; 
        }
    );
};

const validateUpdateSupplierName = () => {
    return validateField(
        "updateSupplierNameInput",
        "updateSupplierNameError",
        (input) => {
            if (!input) {
                return "Supplier name is required.";
            }
            if (input.length > 30) {
                return "Supplier name is too long.";
            }
            return null; 
        }
    );
}

const validateCompanyName = () => {
    return validateField(
        "companyNameInput",
        "companyNameError",
        (input) => {
            if (!input) {
                return "Company name is required.";
            }
            if (input.length > 30) {
                return "Company name is too long.";
            }
            return null;
        }
    );
}

const validateUpdateCompanyName = () => {
    return validateField(
        "updateCompanyNameInput",
        "updateCompanyNameError",
        (input) => {
            if (!input) {
                return "Company name is required.";
            }
            if (input.length > 30) {
                return "Company name is too long.";
            }
            return null;
        }
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
        (input) => {
            if (!input) {
                return "Email is required.";
            }
            if (input.length > 30) {
                return "Email is too long.";
            }
            return null;
        }
    )
}

const validateUpdateEmail = () => {
    return validateField(
        "updateEmailInput",
        "updateEmailError",
        (input) => {
            if (!input) {
                return "Email is required.";
            }
            if (input.length > 30) {
                return "Email is too long.";
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
    )
}

const validateContactNumber = () => {
    return validateField(
        "contactNumberInput",
        "contactNumberError",
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
    )
}

const validateUpdateContactNumber = () => {
    return validateField(
        "updateContactNumberInput",
        "updateContactNumberError",
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

const validateAddress = () => {
    return validateField(
        "addressInput",
        "addressError",
        (input) => {
            if (!input) {
                return "Address is required.";
            }
            if (input.length > 50) {
                return "Address is too long.";
            }
            return null;
        }
    )
}

const validateUpdateAddress = () => {
    return validateField(
        "updateAddressInput",
        "updateAddressError",
        (input) => {
            if (!input) {
                return "Address is required.";
            }
            if (input.length > 50) {
                return "Address is too long.";
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
    )
}

const validateUpdateCity = () => {
    return validateField(
        "updateCityInput",
        "updateCityError",
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
    )
}

const validateUpdateState = () => {
    return validateField(
        "updateStateInput",
        "updateStateError",
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

const validatePostalCode = () => {
    return validateField(
        "postalCodeInput",
        "postalCodeError",
        (input) => {
            if (!input) {
                return "Postal code is required.";
            }
            if (input.length > 15) {
                return "Postal code is too long.";
            }
            if (!input.match(/^\d+$/)) {
                return "Postal code is invalid. Only digits are allowed.";
            }
            return null;
        }
    )
}

const validateUpdatePostalCode = () => {
    return validateField(
        "updatePostalCodeInput",
        "updatePostalCodeError",
        (input) => {
            if (!input) {
                return "Postal code is required.";
            }
            if (input.length > 15) {
                return "Postal code is too long.";
            }
             if (!input.match(/^\d+$/)) {
                return "Postal code is invalid. Only digits are allowed.";
            }
            return null;
        }
    );
}

const validateCountry = () => {
    return validateField(
        "countryInput",
        "countryError",
        (input) => {
            if (!input) {
                return "Country is required.";
            }
            if (input.length > 30) {
                return "Country is too long.";
            }
            return null;
        }
    )
}

const validateUpdateCountry = () => {
    return validateField(
        "updateCountryInput",
        "updateCountryError",
        (input) => {
            if (!input) {
                return "Country is required.";
            }
            if (input.length > 30) {
                return "Country is too long.";
            }
            return null;
        }
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

const validateMenuName = () => {
    return validateField(
        "menuNameInput",
        "menuNameError",
        (input) => (!input ? "Menu is required." : null)
    );
}

const validateUpdateMenuName = () => {
    return validateField(
        "updateMenuNameInput",
        "updateMenuNameError",
        (input) => (!input ? "Menu is required." : null)
    );
}

const validateMenuDescription = () => {
    return validateField(
        "menuDescriptionInput",
        "menuDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
}

const validateUpdateMenuDescription = () => {
    return validateField(
        "updateMenuDescriptionInput",
        "updateMenuDescriptionError",
        (input) => (!input ? "Description is required." : null)
    );
}




