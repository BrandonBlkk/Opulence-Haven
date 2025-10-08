import { showError, hideError, showAlert, validateField } from './alertFunc.js';

// Move Right Loader
let moveRight = document.getElementById("move-right");
const darkOverlay2 = document.getElementById('darkOverlay2');

window.addEventListener('scroll', () => {
    let scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
    let scrollPercentage = (window.scrollY / scrollableHeight) * 100; 

    if (scrollPercentage >= 100) {
        moveRight.style.width = '100%';
    } else {
        moveRight.style.width = scrollPercentage + '%';
    }
});

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

// Search Bar
const storeMenubar = document.getElementById('storeMenubar');
const storeDarkOverlay = document.getElementById('storeDarkOverlay');
const closeBtn = document.getElementById('closeBtn');
const aside = document.getElementById('aside');
const darkOverlay = document.getElementById('darkOverlay');

if (storeMenubar && storeDarkOverlay && closeBtn && aside && darkOverlay) {
    document.getElementById('search-icon').addEventListener('click', () => {
    const searchBar = document.getElementById('search-bar');
    searchBar.classList.toggle('top-0');
    storeDarkOverlay.classList.toggle('hidden');
    });

    storeDarkOverlay.addEventListener('click', () => {
        const searchBar = document.getElementById('search-bar');
        searchBar.classList.toggle('top-0');
        storeDarkOverlay.classList.toggle('hidden');
    });

    searchCloseBtn.addEventListener('click', () => {
        const searchBar = document.getElementById('search-bar');
        searchBar.classList.toggle('top-0');
        storeDarkOverlay.classList.toggle('hidden');
    });

    storeMenubar.addEventListener('click', () => {
        aside.style.right = '0%';
        darkOverlay.classList.remove('hidden');
        darkOverlay.classList.add('flex');
        storeMenubar.classList.add('-rotate-90');
    })

    closeBtn.addEventListener('click', () => {
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        storeMenubar.classList.remove('-rotate-90');
    })

    darkOverlay.addEventListener('click', () => {
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        storeMenubar.classList.remove('-rotate-90');
    });
}

// Favorite handler
document.addEventListener('DOMContentLoaded', () => {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    const loginModal = document.getElementById('loginModal');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const productID = button.getAttribute('data-product-id');
            const action = button.getAttribute('data-action');

            fetch('../Store/favorite_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productID}&action=${action}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'not_logged_in') {
                    if (loginModal && darkOverlay2) {
                        loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.remove('opacity-0', 'invisible');
                        darkOverlay2.classList.add('opacity-100');
                    }
                    return;
                }

                if (data.success) {
                    const icon = button.querySelector('i');
                    const tooltip = button.querySelector('span');

                    if (data.action === 'added') {
                        button.setAttribute('data-action', 'remove');
                    } else if (data.action === 'removed') {
                        button.setAttribute('data-action', 'add');
                    }

                    if (icon) icon.className = `ri-heart${data.action === 'added' ? '-fill text-xl text-amber-500' : '-line text-xl text-gray-400'}`;
                    if (tooltip) tooltip.textContent = data.tooltip;
                }
            })
            .catch(err => console.error(err));
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('addToBagForm');
    const sizeDropdown = document.getElementById('size');
    const sizeError = document.getElementById('sizeError');

    // Function to update cart count & dropdown UI
    function updateCartUI() {
        fetch('../Store/cart_fragment.php', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('cartCount').textContent = data.countText;
            document.getElementById('cartDropdown').innerHTML = data.dropdownHTML;
        })
        .catch(err => console.error('Cart update error:', err));
    }

    if (form && sizeDropdown && sizeError) {
        form.addEventListener('submit', function(e) {
            const submitter = e.submitter;
            if (submitter && submitter.name === 'addtobag') {
                e.preventDefault();
                
                if (sizeDropdown.value === '') {
                    sizeError.classList.remove('hidden');
                    sizeDropdown.classList.add('border-red-500');
                    return;
                }
                
                sizeError.classList.add('hidden');
                sizeDropdown.classList.remove('border-red-500');
                
                const formData = new FormData(form);
                const stockDisplay = document.getElementById('stockDisplay');
                
                fetch('../Store/store_details.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {                    
                    if (data.success) {
                        showAlert('Product added to bag successfully!');
                        stockDisplay.textContent = data.stock;

                        // Update cart UI
                        updateCartUI();
                    } else if (data.outofstock) {
                        showAlert('Product is out of stock', true);
                    } else if (data.login_required) {
                        loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.remove('opacity-0', 'invisible');
                        darkOverlay2.classList.add('opacity-100');

                        document.getElementById('closeLoginModal').addEventListener('click', function() {
                            loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                            darkOverlay2.classList.add('opacity-0', 'invisible');
                            darkOverlay2.classList.remove('opacity-100');
                        })
                    } else {
                        showAlert(data.message || 'Failed to add product to bag', true);
                    }
                })
                .catch(() => {
                    showAlert('An error occurred. Please try again.', true);
                });
            }
        });
    }
});

// Purchase step
const line = document.getElementById('line');
const step = document.getElementById('step');

if (line && step) {
    line.classList.remove('bg-gray-200');
    step.classList.remove('bg-gray-200');
    step.classList.toggle('text-white');
    line.classList.toggle('bg-amber-500');
    step.classList.toggle('bg-amber-500');
}

// Order form validation
document.addEventListener("DOMContentLoaded", () => {
    const paymentForm = document.getElementById("paymentForm");
    const submitButton = document.getElementById("submitButton");
    const buttonText = document.getElementById("buttonText");
    const buttonSpinner = document.getElementById("buttonSpinner");

    // Error message elements
    document.getElementById("firstnameInput").addEventListener("keyup", validateFirstname);
    document.getElementById("lastnameInput").addEventListener("keyup", validateLastname);
    document.getElementById("addressInput").addEventListener("keyup", validateAddress);
    document.getElementById("phoneInput").addEventListener("keyup", validatePhone);
    document.getElementById("cityInput").addEventListener("keyup", validateCity);
    document.getElementById("stateInput").addEventListener("keyup", validateState);
    document.getElementById("zipInput").addEventListener("keyup", validateZip);

    // AJAX form submission
    if (paymentForm) {
        paymentForm.addEventListener("submit", function(e) {
            e.preventDefault();

            if (!validateOrderForm()) {
                return;
            }

            // Disable button and show spinner
            submitButton.disabled = true;
            buttonText.textContent = "Processing";
            buttonSpinner.classList.remove("hidden");

            const formData = new FormData(paymentForm);
            formData.append("submit_reservation", true);

            fetch("../Store/store_checkout.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Redirect to payment page on success
                    window.location.href = `../Store/stripe.php?reservation_id=${data.order_id}`;
                } else {
                    // Re-enable button and hide spinner
                    submitButton.disabled = false;
                    buttonText.textContent = "Continue to Payment";
                    buttonSpinner.classList.add("hidden");
                    
                    // Show error message
                    showAlert(data.message, true);
                }
            })
            .catch(error => {
                // Re-enable button and hide spinner
                submitButton.disabled = false;
                buttonText.textContent = "Continue to Payment";
                buttonSpinner.classList.add("hidden");
                
                console.log("Error:", error);
                showAlert("An error occurred. Please try again.", true);
            });
        });
    }
});

// Modify order
document.getElementById('modifyForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');

    let shippingChanged = false;
    let quantityChanged = false;

    // Detect shipping field changes
    const shippingFields = ['FullName','PhoneNumber','ShippingAddress','City','State','ZipCode'];
    for (let field of shippingFields) {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) continue;
        const originalValue = input.getAttribute('value');
        if (input.value !== originalValue) {
            shippingChanged = true;
            break;
        }
    }

    // Detect quantity changes
    const qtyInputs = form.querySelectorAll('input[name^="qty"]');
    qtyInputs.forEach(input => {
        const originalQty = input.getAttribute('value');
        if (input.value !== originalQty) {
            quantityChanged = true;
        }
    });

    // If no changes at all
    if (!shippingChanged && !quantityChanged) {
        showAlert('No changes detected, nothing to save.');
        return;
    }

    // Show loading on button
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving Changes
        </span>
    `;

    // Proceed with AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../Store/modify_order_save.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                const res = JSON.parse(xhr.responseText || '{}');
                if (xhr.status === 200 && res.success) {
                    // Redirect to Stripe only if additional amount is due AND quantity was changed
                    if (quantityChanged && res.additional_due > 0) {
                        window.location.href = '../Store/stripe_modified_order.php?order_id=' + encodeURIComponent(form.querySelector('input[name="order_id"]').value);
                    } else {
                        // Update saved state
                        shippingFields.forEach(f => {
                            const input = form.querySelector(`[name="${f}"]`);
                            if (input) input.setAttribute('value', input.value);
                        });
                        qtyInputs.forEach(input => input.setAttribute('value', input.value));

                        // Restore button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } else {
                    showAlert('Failed to update: ' + (res.message || 'Unknown error'), true);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (err) {
                showAlert('Unexpected response from server.', true);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        }
    };

    const data = new URLSearchParams(new FormData(form)).toString();
    xhr.send(data);
});

// Return item
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("orderIDInput").addEventListener("keyup", validateOrderID);
    document.getElementById("emailInput").addEventListener("keyup", validateEmail);

    const returnItemForm = document.getElementById("returnItemForm");
    const returnFormSection = document.getElementById("returnForm");
    const orderedProductsSection = document.getElementById("orderedProducts");
    const actionSection = document.getElementById("actionSection");
    const selectedProductContainer = document.getElementById("selectedProductContainer");
    const actionOptions = document.getElementById("actionOptions");
    const refundReasonSection = document.getElementById("refundReasonSection");
    let selectedProductID = null;
    let selectedProduct = null;
    let selectedQuantity = 1;
    let selectedActionType = null;

    // Restore step
    const savedStep = localStorage.getItem("returnStep");
    const savedProduct = localStorage.getItem("selectedProduct");
    const savedProducts = localStorage.getItem("orderedProducts");

    if (savedStep) {
        returnFormSection.classList.add("hidden");
        if (savedStep === "products" && savedProducts) {
            orderedProductsSection.classList.remove("hidden");
            orderedProductsSection.classList.add("flex");
            displayOrderedProducts(JSON.parse(savedProducts));
        } else if (savedStep === "action" && savedProduct) {
            selectedProduct = JSON.parse(savedProduct);
            selectedProductID = selectedProduct.ProductID;
            showSelectedProduct(selectedProduct);
            actionSection.classList.remove("hidden");
            actionSection.classList.add("flex");
        }
    }

    if (returnItemForm) {
        returnItemForm.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!validateReturnItemForm()) return;

            const formData = new FormData(this);

            fetch('../Store/return_item.php', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.products) {
                        returnFormSection.classList.add("hidden");
                        orderedProductsSection.classList.remove("hidden");
                        orderedProductsSection.classList.add("flex");
                        localStorage.setItem("returnStep", "products");
                        localStorage.setItem("orderedProducts", JSON.stringify(data.products));
                        displayOrderedProducts(data.products);
                    } else {
                        showAlert(data.message || 'No products found for this order.', true);
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', true);
                    console.error('Error:', error);
                });
        });
    }

    document.getElementById("backToFormButton").addEventListener("click", () => {
        orderedProductsSection.classList.add("hidden");
        returnFormSection.classList.remove("hidden");
        returnFormSection.classList.add("flex");
        localStorage.clear();
    });

    document.getElementById("nextButton").addEventListener("click", () => {
        if (!selectedProductID || !selectedProduct) {
            showAlert("Please select a product to continue.", true);
            return;
        }
        orderedProductsSection.classList.add("hidden");
        showSelectedProduct(selectedProduct);
        actionSection.classList.remove("hidden");
        actionSection.classList.add("flex");
        localStorage.setItem("returnStep", "action");
        localStorage.setItem("selectedProduct", JSON.stringify(selectedProduct));
    });

    document.getElementById("backButton").addEventListener("click", () => {
        actionSection.classList.add("hidden");
        orderedProductsSection.classList.remove("hidden");
        orderedProductsSection.classList.add("flex");
        localStorage.setItem("returnStep", "products");
    });

    // Action selection
    document.getElementById("actionForm").addEventListener("submit", (e) => {
        e.preventDefault();
        const selectedAction = document.querySelector('input[name="return_action"]:checked');
        if (!selectedAction) {
            showAlert('Please select an action (Exchange or Refund).', true);
            return;
        }

        selectedActionType = selectedAction.value;

        if (selectedAction.value === "refund") {
            actionOptions.classList.add("hidden");
            refundReasonSection.classList.remove("hidden");
            showRefundQuantity(selectedProduct);
            return;
        }

        if (selectedAction.value === "exchange") {
            fetch('../Store/process_return.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: selectedProductID,
                    order_id: selectedProduct.OrderID,
                    quantity: selectedQuantity,
                    remarks: "Customer requested exchange",
                    action_type: "exchange"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(`Exchange request submitted for Product ID: ${selectedProductID}, Quantity: ${selectedQuantity}`, false);

                    // Send return shipping email
                    fetch(`../Mail/send_return_email.php?id=${selectedProduct.OrderID}`)
                        .then(r => r.json())
                        .then(rData => {
                            if (rData.success) {
                                console.log("Return shipping email sent successfully.");
                            } else {
                                console.error("Failed to send return shipping email.");
                            }
                        });

                    // Reset UI back to form after successful exchange
                    actionSection.classList.add("hidden");
                    returnFormSection.classList.remove("hidden");
                    returnFormSection.classList.add("flex");
                    returnItemForm.reset();
                    localStorage.clear();

                } else {
                    showAlert(`${data.message}`, true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while submitting the exchange.', true);
            });
        }
    });

    document.getElementById("refundReasonForm").addEventListener("submit", (e) => {
        e.preventDefault();
        const selectedReason = document.querySelector('input[name="refund_reason"]:checked');
        if (!selectedReason) {
            showAlert('Please select a reason for your refund.', true);
            return;
        }

        fetch('../Store/process_return.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                product_id: selectedProductID,
                order_id: selectedProduct.OrderID,
                quantity: selectedQuantity,
                remarks: selectedReason.value,
                action_type: selectedActionType
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(`Request submitted: ${selectedActionType} for Product ID: ${selectedProductID}, Quantity: ${selectedQuantity}`, false);

                    // Send return shipping email
                    fetch(`../Mail/send_return_email.php?id=${selectedProduct.OrderID}`)
                        .then(r => r.json())
                        .then(rData => {
                            if (rData.success) {
                                console.log("Return shipping email sent successfully.");
                            } else {
                                console.error("Failed to send return shipping email.");
                            }
                        });

                    // Reset UI back to form after successful refund
                    refundReasonSection.classList.add("hidden");
                    returnFormSection.classList.remove("hidden");
                    returnFormSection.classList.add("flex");
                    returnItemForm.reset();
                    localStorage.clear();

                } else {
                    showAlert(`${data.message}`, true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while submitting the request.', true);
            });
    });

    document.getElementById("backToAction").addEventListener("click", () => {
        refundReasonSection.classList.add("hidden");
        actionOptions.classList.remove("hidden");
    });

    function displayOrderedProducts(products) {
        const container = document.getElementById("productsContainer");
        container.innerHTML = "";
        if (products.length === 0) {
            container.innerHTML = `<p class="text-gray-600 text-sm">No products found for this order.</p>`;
            return;
        }
        products.forEach(product => {
            const item = document.createElement("div");
            item.className = "flex items-center space-x-4 p-4 border rounded-md bg-white shadow-sm cursor-pointer hover:border-amber-400 transition";
            item.innerHTML = `
                <input type="radio" name="selectedProduct" value="${product.ProductID}" class="outline-none">
                <img src="${product.ImageUserPath || '../images/no-image.png'}" 
                     alt="${product.Title}" 
                     class="w-20 h-20 object-cover rounded-md select-none">
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-800">${product.Title}</h3>
                    <p class="text-sm text-gray-600">Size: ${product.Size}</p>
                    <p class="text-sm font-semibold text-amber-600">$${product.OrderUnitPrice}</p>
                </div>
            `;
            item.addEventListener("click", () => {
                selectedProductID = product.ProductID;
                selectedProduct = product;
                item.querySelector('input[type="radio"]').checked = true;
            });
            container.appendChild(item);
        });
    }

    function showSelectedProduct(product) {
        selectedProductContainer.innerHTML = `
            <div class="flex items-center space-x-4 p-4 border rounded-md bg-gray-50 shadow-sm mb-4">
                <img src="${product.ImageUserPath || '../images/no-image.png'}" 
                    alt="${product.Title}" 
                    class="w-20 h-20 object-cover rounded-md select-none">
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-800">${product.Title}</h3>
                    <p class="text-sm text-gray-600">Size: ${product.Size}</p>
                    <p class="text-sm font-semibold text-amber-600">$${product.OrderUnitPrice}</p>
                </div>
            </div>
        `;
    }

    function showRefundQuantity(product) {
        const existingSelector = document.getElementById("refundQuantitySelector");
        if (existingSelector) {
            existingSelector.parentElement.remove();
        }

        const container = document.createElement("div");
        container.className = "mb-3";
        container.innerHTML = `
            <label class="block mb-2 text-sm text-gray-700">Select quantity to refund</label>
            <select id="refundQuantitySelector" class="p-1 border rounded w-full max-w-xs outline-none">
                ${Array.from({ length: product.OrderUnitQuantity || 1 }, (_, i) => `<option value="${i + 1}">${i + 1}</option>`).join('')}
            </select>
        `;
        refundReasonSection.insertBefore(container, refundReasonSection.querySelector("form"));

        const qtySelector = document.getElementById("refundQuantitySelector");
        qtySelector.addEventListener("change", (e) => {
            selectedQuantity = parseInt(e.target.value);
        });

        selectedQuantity = parseInt(qtySelector.value);
    }
});

// Room Type Review
document.addEventListener("DOMContentLoaded", () => {
    const writeReviewModal = document.getElementById('writeReviewModal');
    const writeReview = document.getElementById('writeReview');
    const productForm = document.getElementById('productForm');
    const ReviewCancelBtn = document.getElementById('ReviewCancelBtn');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    if (writeReviewModal && writeReview && productForm && ReviewCancelBtn && darkOverlay2) {
        // Show modal
        writeReview.addEventListener('click', () => {
            darkOverlay2.classList.remove('opacity-0', 'invisible');
            darkOverlay2.classList.add('opacity-100');
            writeReviewModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        });

        // Cancel button functionality
        ReviewCancelBtn.addEventListener('click', () => {
            writeReviewModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
            darkOverlay2.classList.add('opacity-0', 'invisible');
            darkOverlay2.classList.remove('opacity-100');
            productForm.reset();

            const errors = ['ratingError', 'reviewError'];
            errors.forEach(error => {
                hideError(document.getElementById(error));
            });
        });
    }

    // Add keyup event listeners for real-time validation
    const reviewInput = document.getElementById("reviewInput");
    if (reviewInput) reviewInput.addEventListener("keyup", validateProductReview);

    document.querySelectorAll('.star-rating').forEach(star => {
        star.addEventListener('click', () => {
            hideError(document.getElementById('ratingError'));
        });
    });

    if (productForm) {
        productForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            
            if (!validateProductReviewForm()) {
                return;
            }
            
            try {
                const formData = new FormData(this);
                const ratingValue = document.getElementById('ratingValue').value;
                formData.append('rating', ratingValue);
                
                const response = await fetch('../Store/store_details.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                if (data.status) {
                    showAlert('Review submitted successfully!', false);
                    this.reset();
                    
                    document.querySelectorAll('.star-rating span').forEach(star => {
                        star.className = 'text-gray-300 hover:text-amber-400';
                    });
                    document.getElementById('ratingValue').value = '0';
                    
                    if (writeReviewModal) {
                        writeReviewModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                    }

                    const currentUrl = new URL(window.location.href);
                    const newParams = new URLSearchParams();
                    if (currentUrl.searchParams.get('product_ID')) {
                        newParams.set('product_ID', currentUrl.searchParams.get('product_ID'));
                    }
                    newParams.set('sort', currentUrl.searchParams.get('sort') || 'oldest');
                    newParams.set('ajax_request', '1');
                    currentUrl.search = newParams.toString();
                    const fetchUrl = currentUrl.toString();

                    showReviewLoadingState();
                    
                    fetchReviewResults(fetchUrl, true).then(() => {
                        initializeReviewEventListeners();
                        setupReviewEditDelete();
                        initializeReactionHandlers();

                        // Update review count
                        updateReviewCount();
                    });

                } else {
                    showAlert(data.message || 'Failed to submit review', true);
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', true);
                console.error('Error:', error);
            }
        });
    }

    // Filter reviews
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort') || 'oldest';
    const sortSelect = document.getElementById('sortReviews');
    if (sortSelect) {
        sortSelect.value = currentSort;

        const sortOptions = Array.from(sortSelect.options);
        sortOptions.forEach(option => {
            option.dataset.clicked = (option.value === currentSort) ? "true" : "false";
        });

        sortSelect.addEventListener('change', () => {
            const selectedOption = sortSelect.options[sortSelect.selectedIndex];
            const showLoading = selectedOption.dataset.clicked === 'false';
            if (showLoading) selectedOption.dataset.clicked = 'true';

            const currentUrl = new URL(window.location.href);
            const newParams = new URLSearchParams();
            if (currentUrl.searchParams.get('product_ID')) {
                newParams.set('product_ID', currentUrl.searchParams.get('product_ID'));
            }
            newParams.set('sort', sortSelect.value);
            newParams.set('ajax_request', '1');

            currentUrl.search = newParams.toString();
            const fetchUrl = currentUrl.toString();

            if (showLoading) showReviewLoadingState();
            fetchReviewResults(fetchUrl, showLoading).then(() => {
                initializeReviewEventListeners();
                setupReviewEditDelete();
                initializeReactionHandlers();

                // Update review count
                updateReviewCount();
            });
        });
    }

    function showReviewLoadingState() {
        const container = document.getElementById('review-results-container');
        if (container) {
            container.innerHTML = `
            <div class="space-y-4 py-4">
            ${Array(3).fill().map(() => `
            <div class="bg-white py-1 animate-pulse">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                <div class="space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-32"></div>
                    <div class="h-3 bg-gray-200 rounded w-24"></div>
                </div>
            </div>
            <div class="mt-3 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-full"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                <div class="h-4 bg-gray-200 rounded w-4/6"></div>
            </div>
            <div class="mt-3 flex gap-4">
                <div class="h-4 bg-gray-200 rounded w-16"></div>
                <div class="h-4 bg-gray-200 rounded w-16"></div>
            </div>
            </div>
            `).join('')}
            </div>
            `;
        }
    }

    function fetchReviewResults(url, shouldDelay) {
        return fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(data => {
            return new Promise((resolve) => {
                const processData = () => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const newContent = doc.getElementById('review-results-container');
                    if (newContent) {
                        document.getElementById('review-results-container').innerHTML = newContent.innerHTML;

                        const cleanUrl = new URL(url);
                        cleanUrl.searchParams.delete('ajax_request');
                        window.history.replaceState({ path: cleanUrl.toString() }, '', cleanUrl.toString());
                        resolve();
                    } else {
                        throw new Error('Invalid response format - review content not found');
                    }
                };
                
                if (shouldDelay) setTimeout(processData, 500);
                else processData();
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Update review count
    function updateReviewCount() {
        const reviewCountElement = document.getElementById("tab-review");
        const reviews = document.querySelectorAll("#review-results-container .review-container");
        if (reviewCountElement) {
            reviewCountElement.textContent = `Reviews (${reviews.length})`;
        }
    }

    // Review Edit and Delete with event delegation
    function setupReviewEditDelete() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-btn')) {
                const btn = e.target.closest('.edit-btn');
                let reviewId = btn.getAttribute('data-review-id');
                let comment = btn.getAttribute('data-comment');
                let form = document.querySelector(`.edit-form[data-review-id="${reviewId}"]`);
                if (form) {
                    form.querySelector('textarea').value = comment;
                    form.classList.remove('hidden');
                    btn.closest('.review').classList.add('hidden');
                }
            }
            if (e.target.closest('.cancel-edit')) {
                const btn = e.target.closest('.cancel-edit');
                btn.closest('.edit-form').classList.add('hidden');
                btn.closest('.review-container').querySelector('.review').classList.remove('hidden');
            }
        });
        
        document.addEventListener('submit', function(e) {
            if (e.target.closest('.edit-form')) {
                e.preventDefault();
                const form = e.target.closest('.edit-form');
                const formData = new FormData(form);
                formData.append("save_edit", true);
                const reviewContainer = form.closest('.review-container');
                
                fetch('../Store/store_details.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const updatedText = formData.get('updated_comment');
                        reviewContainer.querySelector('.truncated-comment').textContent = updatedText;
                        reviewContainer.querySelector('.full-comment').textContent = updatedText;
                        form.classList.add('hidden');
                        reviewContainer.querySelector('.review').classList.remove('hidden');
                        showAlert(data.message);
                    } else {
                        showAlert(data.message, true);
                    }
                });
            }
            
            if (e.target.closest('.delete-form')) {
                e.preventDefault();
                const form = e.target.closest('.delete-form');
                const formData = new FormData(form);
                formData.append("delete", true);
                
                fetch('../Store/store_details.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const currentUrl = new URL(window.location.href);
                        const newParams = new URLSearchParams();
                        
                        if (currentUrl.searchParams.get('product_ID')) {
                            newParams.set('product_ID', currentUrl.searchParams.get('product_ID'));
                        }
                        
                        if (currentUrl.searchParams.get('sort')) {
                            newParams.set('sort', currentUrl.searchParams.get('sort'));
                        }
                        
                        newParams.set('ajax_request', '1');
                        currentUrl.search = newParams.toString();
                        const fetchUrl = currentUrl.toString();
                        
                        showReviewLoadingState();
                        fetchReviewResults(fetchUrl, true).then(() => {
                            initializeReviewEventListeners();
                            setupReviewEditDelete();
                            initializeReactionHandlers();

                            // Update review count
                            updateReviewCount();
                        });
                        
                        showAlert(data.message);
                    } else {
                        showAlert(data.message, true);
                    }
                });
            }
        });
    }

    function initializeReviewEventListeners() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.read-more')) {
                const button = e.target.closest('.read-more');
                const reviewContainer = button.closest('.review-container');
                reviewContainer.querySelector('.truncated-comment').classList.add('hidden');
                reviewContainer.querySelector('.full-comment').classList.remove('hidden');
                reviewContainer.querySelector('.read-more').classList.add('hidden');
                reviewContainer.querySelector('.read-less').classList.remove('hidden');
                reviewContainer.querySelector('.read-less').classList.add('inline-block');
            }
            if (e.target.closest('.read-less')) {
                const button = e.target.closest('.read-less');
                const reviewContainer = button.closest('.review-container');
                reviewContainer.querySelector('.truncated-comment').classList.remove('hidden');
                reviewContainer.querySelector('.full-comment').classList.add('hidden');
                reviewContainer.querySelector('.read-more').classList.remove('hidden');
                reviewContainer.querySelector('.read-less').classList.add('hidden');
            }
            if (e.target.closest('.review-date-container')) {
                const container = e.target.closest('.review-date-container');
                const timeAgo = container.querySelector('.time-ago');
                const fullDate = container.querySelector('.full-date');
                timeAgo.classList.add('hidden');
                fullDate.classList.remove('hidden');
                setTimeout(() => {
                    timeAgo.classList.remove('hidden');
                    fullDate.classList.add('hidden');
                }, 2000);
            }
        });
    }

    function initializeReactionHandlers() {
        const forms = document.querySelectorAll('.reaction-form');
        const loginModal = document.getElementById('loginModal');
        const darkOverlay2 = document.getElementById('darkOverlay2');

        if (!forms.length) return;

        forms.forEach(form => {
            const reviewIDInput = form.querySelector('input[name="review_id"]');
            const productIDInput = form.querySelector('input[name="product_id"]');
            const likeBtn = form.querySelector('.like-btn');
            const dislikeBtn = form.querySelector('.dislike-btn');

            if (!reviewIDInput || !productIDInput || !likeBtn || !dislikeBtn) return;

            const reviewID = reviewIDInput.value;
            const productID = productIDInput.value;
            const likeIcon = likeBtn.querySelector('i');
            const dislikeIcon = dislikeBtn.querySelector('i');
            const likeCountSpan = likeBtn.querySelector('.like-count');
            const dislikeCountSpan = dislikeBtn.querySelector('.dislike-count');

            likeBtn.addEventListener('click', () => sendReaction('like'));
            dislikeBtn.addEventListener('click', () => sendReaction('dislike'));

            function sendReaction(type) {
                fetch('../Store/reaction_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `review_id=${reviewID}&product_id=${productID}&reaction_type=${type}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'not_logged_in') {
                        if (loginModal && darkOverlay2) {
                            loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            darkOverlay2.classList.remove('opacity-0', 'invisible');
                            darkOverlay2.classList.add('opacity-100');
                            const closeLoginModal = document.getElementById('closeLoginModal');
                            if (closeLoginModal) {
                                closeLoginModal.addEventListener('click', function() {
                                    loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                                    darkOverlay2.classList.add('opacity-0', 'invisible');
                                    darkOverlay2.classList.remove('opacity-100');
                                });
                            }
                        }
                        return;
                    }
                    if (data.success) {
                        likeCountSpan.textContent = data.likeCount;
                        dislikeCountSpan.textContent = data.dislikeCount;
                        if (type === 'like') {
                            if (likeIcon.classList.contains('ri-thumb-up-fill')) {
                                likeIcon.classList.replace('ri-thumb-up-fill', 'ri-thumb-up-line');
                                likeBtn.classList.remove('text-gray-500');
                            } else {
                                likeIcon.classList.replace('ri-thumb-up-line', 'ri-thumb-up-fill');
                                likeBtn.classList.add('text-gray-500');
                                dislikeIcon.classList.replace('ri-thumb-down-fill', 'ri-thumb-down-line');
                                dislikeBtn.classList.remove('text-gray-500');
                            }
                        } else {
                            if (dislikeIcon.classList.contains('ri-thumb-down-fill')) {
                                dislikeIcon.classList.replace('ri-thumb-down-fill', 'ri-thumb-down-line');
                                dislikeBtn.classList.remove('text-gray-500');
                            } else {
                                dislikeIcon.classList.replace('ri-thumb-down-line', 'ri-thumb-down-fill');
                                dislikeBtn.classList.add('text-gray-500');
                                likeIcon.classList.replace('ri-thumb-up-fill', 'ri-thumb-up-line');
                                likeBtn.classList.remove('text-gray-500');
                            }
                        }
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    setupReviewEditDelete();
    initializeReviewEventListeners();
    initializeReactionHandlers();
});

// Review toggle edit form
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    if (editBtns.length) {
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.getAttribute('data-review-id');
                const comment = this.getAttribute('data-comment');
                const form = document.querySelector(`.edit-form[data-review-id="${reviewId}"]`);
                if (!form) return;
                const textarea = form.querySelector('textarea');
                if (textarea) textarea.value = comment;
                form.classList.remove('hidden');
                document.querySelectorAll('.review').forEach(review => review.classList.add('hidden'));
            });
        });
    }
    const cancelBtns = document.querySelectorAll('.cancel-edit');
    if (cancelBtns.length) {
        cancelBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const editForm = this.closest('.edit-form');
                if (editForm) editForm.classList.add('hidden');
                document.querySelectorAll('.review').forEach(review => review.classList.remove('hidden'));
            });
        });
    }
});

const validateOrderForm = () => {
    const isFirstnameValid = validateFirstname();
    const isLastnameValid = validateLastname();
    const isAddressValid = validateAddress();
    const isPhoneValid = validatePhone();
    const isCityValid = validateCity();
    const isStateValid = validateState();
    const isZipValid = validateZip();

    return isFirstnameValid && isLastnameValid && isAddressValid && isPhoneValid && isCityValid && isStateValid && isZipValid;
}

const validateReturnItemForm = () => {
    const isOrderIDValid = validateOrderID();
    const isEmailValid = validateEmail();

    return isOrderIDValid && isEmailValid;
};

const validateProductReviewForm = () => {
    const isProductReviewValid = validateProductReview();
    const isRatingValid = validateRating();

    return isProductReviewValid && isRatingValid;
}

// Individual validation functions

const validateOrderID = () => {
    return validateField(
        "orderIDInput",
        "orderIDError",
        (input) => {
            if (!input) {
                return "Order ID is required.";
            }
            if (input.length > 20) {
                return "Order ID is too long.";
            }
            return null;
        }
    );
}

const validateFirstname = () => {
    return validateField(
        "firstnameInput",
        "firstnameError",
        (input) => {
            if (!input) {
                return "Firstname is required.";
            }
            if (input.length > 15) {
                return "Firstname is too long.";
            }
            return null;
        }
    );
}

const validateLastname = () => {
    return validateField(
        "lastnameInput",
        "lastnameError",
        (input) => {
            if (!input) {
                return "Lastname is required.";
            }
            if (input.length > 15) {
                return "Lastname is too long.";
            }
            return null;
        }
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
            if (input.length > 100) {
                return "Address is too long.";
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
    );
}

const validateZip = () => {
    return validateField(
        "zipInput",
        "zipError",
        (input) => {
            if (!input) {
                return "Zip is required.";
            }
            if (input.length > 10) {
                return "Zip is too long.";
            }
            if (!input.match(/^\d+$/)) {
                return "Zip code is invalid. Only digits are allowed.";
            }
            return null;
        }
    );
}

const validateRating = () => {
    const ratingValue = document.getElementById('ratingValue').value;
    const ratingErrorElement = document.getElementById('ratingError'); 
    
    const getMessageError = (ratingValue) => {
        if (ratingValue === '0' || ratingValue === '') {
            return "Rating is required.";
        }
        return null; 
    };

    const errorMessage = getMessageError(ratingValue);

    if (errorMessage) {
        showError(ratingErrorElement, errorMessage);
        return false;
    } else {
        hideError(ratingErrorElement);
        return true;
    }
}

const validateProductReview = () => {
    const reviewInput = document.getElementById("reviewInput").value.trim();
    const reviewError = document.getElementById("reviewError");

    const getMessageError = (reviewInput) => {
        if (!reviewInput) return "Review is required.";
        if (reviewInput.length < 10) return "Message must be at least 10 characters long.";
        if (reviewInput.length > 1000) return "Message cannot exceed 1000 characters.";
        return null; 
    };

    const errorMessage = getMessageError(reviewInput);

    switch (true) {
        case errorMessage !== null:
            showError(reviewError, errorMessage);
            return false;
        default:
            hideError(reviewError);
            return true;
    }
}
