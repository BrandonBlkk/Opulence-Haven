import { hideError, showAlert, showError, validateField } from './alertFunc.js';

// Move Right Loader
let moveRight = document.getElementById("move-right");

window.addEventListener('scroll', () => {
    let scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
    let scrollPercentage = (window.scrollY / scrollableHeight) * 100; 

    if (scrollPercentage >= 100) {
        moveRight.style.width = '100%';
    } else {
        moveRight.style.width = scrollPercentage + '%';
    }
});

// Handle remove room
document.addEventListener('DOMContentLoaded', function() {
    // Handle remove room with AJAX
    document.querySelectorAll('.remove-room-btn').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.edit-remove-room-form');
            const formData = new FormData(form);
            const roomCard = this.closest('.flex.flex-col.md\\:flex-row.rounded-md.shadow-sm.border');

            // Add the remove_room parameter to the form data
            formData.append('remove_room', 'true');

            // Show loading state
            this.disabled = true;
            this.textContent = 'Removing...';

            fetch('../User/reservation.php', {
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
                    if (data.success) {
                        // Remove the room card from the DOM
                        roomCard.remove();
                        showAlert('The room has been successfully removed from your reservation.');

                        document.getElementById('no-rooms-message').style.display = 'block';
                    } else {
                        // Show error message
                        alert('Failed to remove room. Please try again.');
                        button.disabled = false;
                        button.textContent = 'Remove Room';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    button.disabled = false;
                    button.textContent = 'Remove Room';
                });
        });
    });
});

// Membership Popup
document.addEventListener('DOMContentLoaded', function() {
    const membershipPopup = document.getElementById('membershipPopup');
    const membershipForm = document.getElementById('membershipForm');
    const closeBtn = document.getElementById('closePopup');
    const pointsBalanceDisplay = document.querySelector('.points-balance-display');

    // Only proceed if the main elements exist
    if (!membershipPopup || !membershipForm) {
        return; // Exit if essential elements don't exist
    }

    // Auto-show popup after 3 seconds
    setTimeout(() => {
        if (membershipPopup) {
            membershipPopup.classList.replace('right-[-320px]', 'right-0');
        }
    }, 3000);

    // Close popup if close button exists
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            membershipPopup.classList.replace('right-0', 'right-[-320px]');
        });
    }

    // Submit form and update membership using AJAX
    membershipForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(membershipForm);

        // Disable the submit button to prevent multiple submissions
        const submitButton = membershipForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = 'Processing...';
        }

        // Create AJAX request
        fetch('../User/home_page.php', {
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
                if (data.status === 'success') {
                    // Update points balance display in real-time if it exists
                    if (pointsBalanceDisplay) {
                        pointsBalanceDisplay.innerHTML = `${data.pointsBalance.toLocaleString()} ${data.pointsBalance > 1 ? 'Points' : 'Point'} Available to Redeem`;
                    }
                    
                    // Success - show animation
                    membershipForm.classList.add('hidden');
                    const confettiSuccess = document.getElementById('confettiSuccess');
                    if (confettiSuccess) {
                        confettiSuccess.classList.remove('hidden');
                    }

                    // Start confetti if function exists
                    if (typeof createConfetti === 'function') {
                        createConfetti();
                    }

                    // Auto-close popup after 4 seconds
                    setTimeout(() => {
                        membershipPopup.classList.replace('right-0', 'right-[-320px]');
                    }, 4000);
                } else {
                    throw new Error(data.message || 'Failed to update membership');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Join Now - Earn 500 Points';
                }
            });
    });
});

// Confetti Generator
function createConfetti() {
    const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#4CAF50', '#FF9800', '#FFC107'];
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.width = `${Math.random() * 8 + 4}px`;
        confetti.style.height = `${Math.random() * 8 + 4}px`;
        confetti.style.left = `${Math.random() * 100}%`;
        confetti.style.animationDuration = `${Math.random() * 2 + 2}s`;
        confetti.style.animationDelay = `${Math.random() * 0.5}s`;
        confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
        document.body.appendChild(confetti);
        confetti.addEventListener('animationend', () => confetti.remove());
    }
}

// Checkin Form scroll behavior
let checkin_form = document.getElementById('checkin-form');
let mobile_checkin_form = document.getElementById('mobile-checkin-form');
let lastScrollPosition = window.scrollY;
let isScrollingDown = false;
const darkOverlay2 = document.getElementById('darkOverlay2');

if (checkin_form && mobile_checkin_form) {
    checkin_form.classList.add('hidden', 'lg:flex');
    checkin_form.style.bottom = '32px';

    window.addEventListener('scroll', () => {
        const currentScrollPosition = window.scrollY;
        isScrollingDown = currentScrollPosition > lastScrollPosition;
        lastScrollPosition = currentScrollPosition;

        let scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
        let scrollPercentage = (window.scrollY / scrollableHeight) * 100;

        if (window.scrollY <= 10) {
            checkin_form.style.bottom = '32px';
        } else if (isScrollingDown) {
            if (scrollPercentage < 80) {
                checkin_form.style.bottom = '32px';
            } else {
                checkin_form.style.bottom = '-100%';
            }
        } else {
            checkin_form.style.bottom = '-100%';
        }
    });
}

// Handle mobile check-in buttons and forms 
document.querySelectorAll('#mobile-checkin-button').forEach(button => {
    button.addEventListener('click', (e) => {
        e.preventDefault();
        // Find the closest mobile form to this button
        const wrapper = button.closest('#mobileButtonsWrapper');
        if (wrapper) {
            const form = wrapper.nextElementSibling;
            if (form && form.id === 'mobile-checkin-form') {
                form.classList.remove('translate-y-full');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
            }
        }
    });
});

// Handle all close buttons for mobile forms
document.querySelectorAll('[id^="close-mobile"]').forEach(closeBtn => {
    closeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const form = closeBtn.closest('#mobile-checkin-form');
        if (form) {
            form.classList.add('translate-y-full');
            darkOverlay2.classList.remove('opacity-100');
            darkOverlay2.classList.add('opacity-0', 'invisible');
        }
    });
});

// Close overlay when clicking on it
if (darkOverlay2) {
    darkOverlay2.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('#mobile-checkin-form').forEach(form => {
            form.classList.add('translate-y-full');
            darkOverlay2.classList.remove('opacity-100');
            darkOverlay2.classList.add('opacity-0', 'invisible');
        });
    });
}

// Mobile filter sidebar toggle
const mobileFilterButton = document.getElementById('mobileFilterButton');
const mobileFilterSidebar = document.getElementById('mobileFilterSidebar');
const closeMobileFilter = document.getElementById('closeMobileFilter');
const sidebarContent = document.getElementById('sidebarContent');

if (mobileFilterButton && mobileFilterSidebar && closeMobileFilter && sidebarContent) {
    function openSidebar() {
        mobileFilterSidebar.classList.remove('-translate-x-full');
        mobileFilterSidebar.classList.add('translate-x-0');
        darkOverlay2.classList.remove('opacity-0', 'invisible');
        darkOverlay2.classList.add('opacity-100');
        sidebarContent.classList.remove('-translate-x-full');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        darkOverlay2.classList.remove('opacity-100');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        sidebarContent.classList.add('-translate-x-full');
        mobileFilterSidebar.classList.add('-translate-x-full');
        mobileFilterSidebar.classList.remove('translate-x-0');
        document.body.style.overflow = '';
    }

    mobileFilterButton.addEventListener('click', openSidebar);
    closeMobileFilter.addEventListener('click', closeSidebar);
    darkOverlay2.addEventListener('click', closeSidebar);
}

// Cookie Modal
const cookieModal = document.getElementById('cookieModal');
if (cookieModal) {
    const acceptBtn = document.getElementById('acceptBtn');
    const declineBtn = document.getElementById('declineBtn');

    // Check if cookie consent is already given
    if (!localStorage.getItem('cookieConsent')) {
        setTimeout(() => {
            cookieModal.classList.add('bottom-0');
            cookieModal.classList.remove('-bottom-full');
        }, 1000);
    }

    // Accept Button
    acceptBtn.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'true');
        cookieModal.classList.add('-bottom-full');
        cookieModal.classList.remove('bottom-0');
    });

    // Decline Button
    declineBtn.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'false');
        cookieModal.classList.add('-bottom-full');
        cookieModal.classList.remove('bottom-0');
    });
}

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

// Menu Bar
const menubar = document.getElementById('menubar');
if (menubar) {
    const closeBtn = document.getElementById('closeBtn');
    const aside = document.getElementById('aside');
    const darkOverlay = document.getElementById('darkOverlay');

    menubar.addEventListener('click', () => {
        aside.style.right = '0%';
        darkOverlay.classList.remove('hidden');
        darkOverlay.classList.add('flex');
        menubar.classList.add('-rotate-90');
    });

    closeBtn.addEventListener('click', () => {
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        menubar.classList.remove('-rotate-90');
    });

    darkOverlay.addEventListener('click', () => {
        aside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        menubar.classList.remove('-rotate-90');
    });
}

//Rooom Review
const viewAllReviews = document.getElementById('viewAllReviews');
if (viewAllReviews) {
    const reviewCloseBtn = document.getElementById('reviewCloseBtn');
    const rooomReview = document.getElementById('rooomReview');
    const darkOverlay = document.getElementById('darkOverlay');

    viewAllReviews.addEventListener('click', () => {
        rooomReview.style.top = '0%';
    });

    reviewCloseBtn.addEventListener('click', () => {
        rooomReview.style.top = '100%';
    });

    darkOverlay.addEventListener('click', () => {
        rooomReview.style.top = '100%';
    });
}

// Dining Reservation Modal
const diningBtn = document.getElementById('diningBtn');
if (diningBtn) {
    const diningCloseBtn = document.getElementById('diningCloseBtn');
    const diningAside = document.getElementById('diningAside');
    const darkOverlay = document.getElementById('darkOverlay');

    diningBtn.addEventListener('click', () => {
        diningAside.style.right = '0%';
        darkOverlay.classList.remove('hidden');
        darkOverlay.classList.add('flex');
    });

    diningCloseBtn.addEventListener('click', () => {
        diningAside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
    });

    darkOverlay.addEventListener('click', () => {
        diningAside.style.right = '-100%';
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
    });
}

// Dining Reservation Form
document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById('loader');

    // Add keyup event listeners for real-time validation
    document.getElementById("diningNameInput").addEventListener("keyup", validateDiningName);
    document.getElementById("diningEmailInput").addEventListener("keyup", validateDiningEmail);
    document.getElementById("diningPhoneInput").addEventListener("keyup", validateDiningPhone);

    const diningForm = document.getElementById("diningForm");
    if (diningForm) {
        diningForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateDiningForm()) return;

            loader.style.display = 'flex';

            const formData = new FormData(diningForm);
            formData.append('reserve', true);

            fetch('../User/dining.php', {
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
                if (data.success) {
                    showAlert("A dining table has been successfully reserved.");
                    diningForm.reset();

                    // Hide sidebar
                    diningAside.style.right = '-100%';
                    darkOverlay.classList.add('hidden');
                    darkOverlay.classList.remove('flex');
                } else {
                    showAlert(data.message || "Failed to reserve the table. Please try again.", true);
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                showAlert("Something went wrong. Please try again.", true);
                console.error(err);
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('time');
    const menuSelect = document.getElementById('menu');

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];

    // Set both min and default (value) to tomorrow
    dateInput.min = tomorrowStr;
    dateInput.value = tomorrowStr;

    // Function to convert time to minutes since midnight for easier comparison
    function timeToMinutes(time) {
        const [hours, minutes] = time.split(':').map(Number);
        return hours * 60 + minutes;
    }

    // Function to convert 24-hour time to 12-hour format with AM/PM
    function convertTo12Hour(time24h) {
        const [hours, minutes] = time24h.split(':').map(Number);
        let period = 'AM';
        let displayHours = hours;
        
        if (hours >= 12) {
            period = 'PM';
            displayHours = hours === 12 ? 12 : hours - 12;
        }
        if (hours === 0) {
            displayHours = 12;
        }
        
        return `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;
    }

    // Function to update time constraints based on selected menu
    function updateTimeConstraints() {
        const selectedOption = menuSelect.options[menuSelect.selectedIndex];
        const startTime = selectedOption.dataset.start;
        const endTime = selectedOption.dataset.end;

        if (startTime && endTime) {
            // Convert 12-hour format to 24-hour format for time input
            function convertTo24Hour(time12h) {
                const [time, modifier] = time12h.split(' ');
                let [hours, minutes] = time.split(':');

                if (modifier === 'PM' && hours !== '12') {
                    hours = parseInt(hours, 10) + 12;
                } else if (modifier === 'AM' && hours === '12') {
                    hours = '00';
                }

                return `${hours.toString().padStart(2, '0')}:${minutes}`;
            }

            // Convert times if they contain AM/PM
            const start24 = startTime.includes('AM') || startTime.includes('PM') ?
                convertTo24Hour(startTime) :
                startTime;
            const end24 = endTime.includes('AM') || endTime.includes('PM') ?
                convertTo24Hour(endTime) :
                endTime;

            // Set min and max attributes
            timeInput.min = start24;
            timeInput.max = end24;
            timeInput.value = start24;

            // Enable the time input
            timeInput.disabled = false;

            // Validate the time whenever it changes
            timeInput.addEventListener('change', function() {
                const selectedTime = this.value;
                const selectedMinutes = timeToMinutes(selectedTime);
                const startMinutes = timeToMinutes(start24);
                const endMinutes = timeToMinutes(end24);

                if (selectedMinutes < startMinutes || selectedMinutes > endMinutes) {
                    // Close sidebar
                    diningAside.style.right = '-100%';
                    darkOverlay.classList.add('hidden');
                    darkOverlay.classList.remove('flex');
                    
                    // Show alert in original format (AM/PM if that's what's in database)
                    const displayStart = startTime.includes('AM') || startTime.includes('PM') ? 
                        startTime : 
                        convertTo12Hour(start24);
                    const displayEnd = endTime.includes('AM') || endTime.includes('PM') ? 
                        endTime : 
                        convertTo12Hour(end24);
                    
                    showAlert(`Please select a time between ${displayStart} and ${displayEnd}`, true);
                    this.value = start24;
                }
            });
        } else {
            // Disable the time input if no valid menu is selected
            timeInput.disabled = true;
        }
    }

    // Add event listener for menu selection change
    menuSelect.addEventListener('change', updateTimeConstraints);

    // Initialize time constraints
    updateTimeConstraints();
});

// MoveUp Btn
const moveUpBtn = document.getElementById('moveUpBtn');
const mobileButtonsWrapper = document.getElementById('mobileButtonsWrapper');

if (moveUpBtn || mobileButtonsWrapper) {
    window.addEventListener('scroll', () => {
        // Check if screen size is larger than sm 
        const isLargeScreen = window.matchMedia('(min-width: 640px)').matches;
        
        if (window.scrollY > 1000) {
            moveUpBtn.classList.remove('-right-full');
            moveUpBtn.classList.add('right-0');
            
            // Only adjust mobile button position on larger screens
            if (isLargeScreen) {
                mobileButtonsWrapper.classList.remove('bottom-3');
                mobileButtonsWrapper.classList.add('bottom-[75px]');
            }
        } else {
            moveUpBtn.classList.remove('right-0');
            moveUpBtn.classList.add('-right-full');
            
            // Only adjust mobile button position on larger screens
            if (isLargeScreen) {
                mobileButtonsWrapper.classList.remove('bottom-[75px]');
                mobileButtonsWrapper.classList.add('bottom-3');
            }
        }
    });
}

// Phone Btn
const phoneBtn = document.getElementById('phoneBtn');
if (phoneBtn) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 1000) {
            phoneBtn.classList.remove('-right-full');
            phoneBtn.classList.add('right-2');
        } else {
            phoneBtn.classList.remove('right-2');
            phoneBtn.classList.add('-right-full');
        }
    });
}

// Logout Modal
const logoutBtn = document.getElementById('logoutBtn');
const confirmModal = document.getElementById('confirmModal');
const cancelBtn = document.getElementById('cancelBtn');
const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
const darkOverlay = document.getElementById('darkOverlay');

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
        darkOverlay.classList.add('hidden');
        darkOverlay.classList.remove('flex');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        aside.style.right = '-100%';
        menubar.classList.remove('-rotate-90');
    });

    // Handle Logout Action
    confirmLogoutBtn.addEventListener('click', () => {
        // Hide the modal and overlay
        confirmModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        confirmModal.classList.remove('opacity-100', 'translate-y-0');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
        aside.style.right = '-100%';
        menubar.classList.remove('-rotate-90');
    
        // Show the loader
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.display = 'flex';
        }
    
        // Notify the server to destroy the session
        fetch('../User/user_logout.php', { method: 'POST' })
            .then(() => {
                // Redirect after logout
                window.location.href = 'home_page.php';
            })
            .catch((error) => {
                console.error('Logout failed:', error);
                if (loader) {
                    loader.style.display = 'none'; 
                }
            });
    });
}

// Profile Delete Modal
const profileDeleteBtn = document.getElementById("profileDeleteBtn");
const confirmDeleteModal = document.getElementById("confirmDeleteModal");
const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
const deleteConfirmInput = document.getElementById("deleteConfirmInput");

if (profileDeleteBtn && confirmDeleteModal && cancelDeleteBtn && confirmDeleteBtn && deleteConfirmInput) {
    // Show Delete Modal
    profileDeleteBtn.addEventListener("click", () => {
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
        fetch("user_account_delete.php", {
            method: "POST",
        }) 
            .then(() => {
                // Redirect after account deletion
                window.location.href = "home_page.php";
            })
            .catch((error) => console.error("Account deletion failed:", error));
    });
}

//Contact Form
document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById('loader');
    const alertMessage = document.getElementById('alertMessage').value;
    const contactSuccess = document.getElementById('contactSuccess').value === 'true';

    if (contactSuccess) {
        loader.style.display = 'flex';

        // Show Alert
        setTimeout(() => {
            loader.style.display = 'none';
                showAlert('Your message has been successfully sent.');
            setTimeout(() => {
               window.location.href = '../User/contact.php';
            }, 5000);
        }, 1000);
    } else if (alertMessage) {
        // Show Alert
        showAlert(alertMessage);
    }

    // Add keyup event listeners for real-time validation
    document.getElementById("contactFullNameInput").addEventListener("keyup", validateFuillName);
    document.getElementById("contactPhoneInput").addEventListener("keyup", validateContactPhone);
    document.getElementById("countryFlag").addEventListener("keyup", validateCountryFlag);
    document.getElementById("contactMessageInput").addEventListener("keyup", validateContactMessage);

    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", (e) => {
            if (!validateContactForm()) {
                e.preventDefault();
            }
        });
    }
});

// Reset Password and Profile Update Form Validation
document.addEventListener("DOMContentLoaded", () => {
    // Add keyup event listeners for real-time validation
    document.getElementById("usernameInput").addEventListener("keyup", validateUsername);
    document.getElementById("phoneInput").addEventListener("keyup", validatePhone);

    document.getElementById("resetpasswordInput").addEventListener("keyup", validateResetPassword);
    document.getElementById("newpasswordInput").addEventListener("keyup", validateNewPassword);
    document.getElementById("confirmpasswordInput").addEventListener("keyup", validateConfirmPassword);

    // Add submit event listener for form validation
    const updateProfileForm = document.getElementById("updateProfileForm");
    if (updateProfileForm) {
        // In the updateProfileForm event listener
        updateProfileForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateProfileUpdateForm()) {
                return;
            }

            // Create FormData object
            const formData = new FormData(updateProfileForm);
            formData.append('modify', true);

            // AJAX request
            fetch('../User/profile_edit.php', {
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
                if (data.success) {
                    if (data.profileChanged) {
                        showAlert('You have successfully changed your profile.');
                    } else {
                        showAlert('No changes were made to your profile.');
                    }
                } else {
                    showAlert(data.message || 'Failed to update profile. Please try again.', true);
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', true);
            });
        });
    }
    
    // Add submit event listener for form validation
    const resetPasswordForm = document.getElementById("resetPasswordForm");
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateResetForm()) {
                return;
            }

            // Create FormData object
            const formData = new FormData(resetPasswordForm);
            formData.append('resetPassword', true);

            // Show loading state
            const submitBtn = resetPasswordForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            // AJAX request
            fetch('../User/profile_edit.php', {
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
                if (data.success) {
                    showAlert('You have successfully changed your password.');
                    resetPasswordForm.reset();
                } else {
                    showAlert(data.message || 'Failed to update password. Please try again.', true);
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', true);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            });
        });
    }
});

// Check-in and check-out date validation
document.addEventListener('DOMContentLoaded', () => {
    const checkInDateInput = document.getElementById('checkin-date');
    const mobileCheckInDateInput = document.getElementById('mobile-checkin-date');
    const checkOutDateInput = document.getElementById('checkout-date');
    const mobileCheckOutDateInput = document.getElementById('mobile-checkout-date');

    if (checkInDateInput && checkOutDateInput) {
        // Get today's and tomorrow's dates in YYYY-MM-DD format
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];

        const dayaftertomorrow = new Date();
        dayaftertomorrow.setDate(dayaftertomorrow.getDate() + 2);
        const dayaftertomorrowStr = dayaftertomorrow.toISOString().split('T')[0];

        if (window.location.pathname.includes('/User/home_page.php')) { 
            // Set default values
            checkInDateInput.value = tomorrowStr;
            checkOutDateInput.value = dayaftertomorrowStr;

            mobileCheckInDateInput.value = tomorrowStr;
            mobileCheckOutDateInput.value = dayaftertomorrowStr;
        }

        // Update checkout min date when checkin changes
        checkInDateInput.addEventListener('change', function() {
            if (this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayStr = nextDay.toISOString().split('T')[0];
                checkOutDateInput.min = nextDayStr;

                // If current checkout date is before new min date, update it
                if (checkOutDateInput.value < nextDayStr) {
                    checkOutDateInput.value = nextDayStr;
                }
            }
        });

        // Update checkout min date when checkin changes
        mobileCheckInDateInput.addEventListener('change', function() {
            if (this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayStr = nextDay.toISOString().split('T')[0];
                mobileCheckOutDateInput.min = nextDayStr;

                // If current checkout date is before new min date, update it
                if (mobileCheckOutDateInput.value < nextDayStr) {
                    mobileCheckOutDateInput.value = nextDayStr;
                }
            }
        });
    }
});

// Reservation form validation
document.addEventListener("DOMContentLoaded", () => {
    const reservationForm = document.getElementById("reservationForm");
    const submitButton = document.getElementById("submitButton");
    const buttonText = document.getElementById("buttonText");
    const buttonSpinner = document.getElementById("buttonSpinner");

    // Error message elements
    const firstNameError = document.getElementById("firstNameError");
    const phoneError = document.getElementById("phoneError");

    // Initially hide all error messages
    [firstNameError, phoneError].forEach(error => {
        error.style.opacity = "0";
    });

    // Form validation
    function validateForm() {
        let isValid = true;
        const firstName = reservationForm.querySelector("[name='first_name']").value.trim();
        const phone = reservationForm.querySelector("[name='phone']").value.trim();

        // Validate first name
        if (!firstName) {
            firstNameError.style.opacity = "1";
            isValid = false;
        } else {
            firstNameError.style.opacity = "0";
        }

        // Validate phone
        if (!phone) {
            phoneError.style.opacity = "1";
            isValid = false;
        } else {
            phoneError.style.opacity = "0";
        }

        return isValid;
    }

    // AJAX form submission
    if (reservationForm) {
        reservationForm.addEventListener("submit", function(e) {
            e.preventDefault();

            if (!validateForm()) return;

            const formData = new FormData(reservationForm);
            formData.append("submit_reservation", true);

            fetch("../User/reservation.php", {
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
                    // Disable button and show spinner
                    submitButton.disabled = true;
                    buttonText.textContent = "Processing...";
                    buttonSpinner.classList.remove("hidden");

                    // Redirect to payment page on success
                    window.location.href = `../User/stripe.php?reservation_id=${data.reservation_id}`;
                } else if (data.login_required) {
                    loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    const closeLoginModal = document.getElementById('closeLoginModal');
                    closeLoginModal.addEventListener('click', function() {
                        loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                        darkOverlay2.classList.add('opacity-0', 'invisible');
                        darkOverlay2.classList.remove('opacity-100');
                    })
                } else {
                    // Re-enable button and hide spinner
                    submitButton.disabled = false;
                    buttonText.textContent = "Continue to payment";
                    buttonSpinner.classList.add("hidden");
                    
                    // Show error message
                    showAlert(data.message, true);
                }
            })
            .catch(error => {
                // Re-enable button and hide spinner
                submitButton.disabled = false;
                buttonText.textContent = "Continue to payment";
                buttonSpinner.classList.add("hidden");
                
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
            });
        });
    }

    // Real-time validation for first name
    const firstNameInput = reservationForm.querySelector("[name='first_name']");
    if (firstNameInput) {
        firstNameInput.addEventListener("input", function() {
            if (this.value.trim()) {
                firstNameError.style.opacity = "0";
            }
        });
    }

    // Real-time validation for phone
    const phoneInput = reservationForm.querySelector("[name='phone']");
    if (phoneInput) {
        phoneInput.addEventListener("input", function() {
            if (this.value.trim()) {
                phoneError.style.opacity = "0";
            }
        });
    }
});

// Upcoming Reservation Detail Modal
const reservationDetailModal = document.getElementById('reservationDetailModal');
const closeReservationDetailModal = document.getElementById('closeReservationDetailModal');

const detailsBtn = document.querySelector('.details-btn');
if (detailsBtn && reservationDetailModal && closeReservationDetailModal) {
    detailsBtn.addEventListener('click', function() {
        reservationDetailModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.remove('opacity-0', 'invisible');
        darkOverlay2.classList.add('opacity-100');
    });

    closeReservationDetailModal.addEventListener('click', function() {
        reservationDetailModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
    });
}

// Full form validation function
const validateProfileUpdateForm = () => {
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPhoneValid = validatePhone();

    return isUsernameValid && isEmailValid && isPhoneValid;
};

const validateResetForm = () => {
    const isResetPasswordValid = validateResetPassword();
    const isNewPasswordValid = validateNewPassword();
    const isConfirmPasswordValid = validateConfirmPassword();


    return isResetPasswordValid && isNewPasswordValid && isConfirmPasswordValid;
};

const validateDiningForm = () => {
    const isDiningNameValid = validateDiningName();
    const isDiningEmailValid = validateDiningEmail();
    const isDiningPhoneValid = validateDiningPhone();


    return isDiningNameValid && isDiningEmailValid && isDiningPhoneValid;
};

const validateContactForm = () => {
    const isContactFullNameValid = validateFuillName();
    const isContactPhoneValid = validateContactPhone();
    const isContactCountryFlagValid = validateCountryFlag();
    const isContactMessageValid = validateContactMessage();

    return isContactFullNameValid && isContactPhoneValid && isContactCountryFlagValid && isContactMessageValid;
}

// Individual validation functions

const validateDiningName = () => {
    return validateField(
        "diningNameInput",
        "diningNameError",
        (input) => (!input ? "Name is required." : null)
    );
}

const validateDiningEmail = () => {
    return validateField(
        "diningEmailInput",
        "diningEmailError",
        (input) => (!input ? "Email is required." : null)
    );
}

const validateDiningPhone = () => {
    return validateField(
        "diningPhoneInput",
        "diningPhoneError",
        (input) => (!input ? "Phone is required." : null)
    );
}
const validateUsername = () => {
    const usernameInput = document.getElementById("usernameInput").value.trim();
    const usernameError = document.getElementById("usernameError");

    const getUserNameError = (usernameInput) => {
        if (!usernameInput) return "Username is required.";
        if (usernameInput.length > 14) return "Username should not exceed 14 characters.";
        return null; 
    };

    const errorMessage = getUserNameError(usernameInput);

    switch (true) {
        case errorMessage !== null:
            showError(usernameError, errorMessage);
            return false;
        default:
            hideError(usernameError);
            return true;
    }
};

const validateFuillName = () => {
    const contactFullNameInput = document.getElementById("contactFullNameInput").value.trim();
    const contactFullNameError = document.getElementById("contactFullNameError");

    const getPhoneError = (contactFullNameInput) => {
        if (!contactFullNameInput) return "Full name is required.";
        return null; 
    };

    const errorMessage = getPhoneError(contactFullNameInput);

    switch (true) {
        case errorMessage !== null:
            showError(contactFullNameError, errorMessage);
            return false;
        default:
            hideError(contactFullNameError);
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

const validatePhone = () => {
    const phoneInput = document.getElementById("phoneInput").value.trim();
    const phoneError = document.getElementById("phoneError");

    const getPhoneError = (phoneInput) => {
        if (!phoneInput) return "Phone is required.";
        if (!phoneInput.match(/^\d+$/)) return "Phone number is invalid. Only digits are allowed.";
        if (phoneInput.length < 8 || phoneInput.length > 11) return "Phone number must be between 8 and 11 digits.";
        return null; 
    };

    const errorMessage = getPhoneError(phoneInput);

    switch (true) {
        case errorMessage !== null:
            showError(phoneError, errorMessage);
            return false;
        default:
            hideError(phoneError);
            return true;
    }
};

const validateContactPhone = () => {
    const contactPhoneInput = document.getElementById("contactPhoneInput").value.trim();
    const contactPhoneError = document.getElementById("contactPhoneError");

    const getPhoneError = (contactPhoneInput) => {
        if (!contactPhoneInput) return "Phone is required.";
        if (!contactPhoneInput.match(/^\d+$/)) return "Phone number is invalid. Only digits are allowed.";
        if (contactPhoneInput.length < 8 || contactPhoneInput.length > 11) return "Phone number must be between 8 and 11 digits.";
        return null; 
    };

    const errorMessage = getPhoneError(contactPhoneInput);

    switch (true) {
        case errorMessage !== null:
            showError(contactPhoneError, errorMessage);
            return false;
        default:
            hideError(contactPhoneError);
            return true;
    }
};

const validateContactMessage = () => {
    const contactMessageInput = document.getElementById("contactMessageInput").value.trim();
    const contactMessageError = document.getElementById("contactMessageError");

    const getMessageError = (contactMessageInput) => {
        if (!contactMessageInput) return "Message is required.";
        return null; 
    };

    const errorMessage = getMessageError(contactMessageInput);

    switch (true) {
        case errorMessage !== null:
            showError(contactMessageError, errorMessage);
            return false;
        default:
            hideError(contactMessageError);
            return true;
    }
}

const validateResetPassword = () => {
    const resetpassword = document.getElementById("resetpasswordInput").value.trim();
    const resetpasswordError = document.getElementById("resetpasswordError");

    const getPasswordError = (resetpassword) => {
        if (!resetpassword) return "Password is required.";
        return null; 
    };

    const errorMessage = getPasswordError(resetpassword);

    switch (true) {
        case errorMessage !== null:
            showError(resetpasswordError, errorMessage);
            return false;
        default:
            hideError(resetpasswordError);
            return true;
    }
};
const validateNewPassword = () => {
    const newpassword = document.getElementById("newpasswordInput").value.trim();
    const newpasswordError = document.getElementById("newpasswordError");

    const getPasswordError = (newpassword) => {
        if (!newpassword) return "New Password is required.";
        if (newpassword.length < 8) return "Minimum 8 characters.";
        if (!newpassword.match(/\d/)) return "At least one number.";
        if (!newpassword.match(/[A-Z]/)) return "At least one uppercase letter.";
        if (!newpassword.match(/[a-z]/)) return "At least one lowercase letter.";
        if (!newpassword.match(/[^\w\s]/)) return "At least one special character.";
        return null; 
    };

    const errorMessage = getPasswordError(newpassword);

    switch (true) {
        case errorMessage !== null:
            showError(newpasswordError, errorMessage);
            return false;
        default:
            hideError(newpasswordError);
            return true;
    }
};
const validateConfirmPassword = () => {
    const confirmpassword = document.getElementById("confirmpasswordInput").value.trim();
    const confirmpasswordError = document.getElementById("confirmpasswordError");

    const getPasswordError = (confirmpassword) => {
        if (!confirmpassword) return "Confirm Password is required.";
        return null; 
    };

    const errorMessage = getPasswordError(confirmpassword);

    switch (true) {
        case errorMessage !== null:
            showError(confirmpasswordError, errorMessage);
            return false;
        default:
            hideError(confirmpasswordError);
            return true;
    }
};

// Toggle Password for Grouped Inputs
const passwordInputs = [
    { input: document.getElementById('resetpasswordInput'), toggle: document.getElementById('resettogglePassword') },
    { input: document.getElementById('newpasswordInput'), toggle: document.getElementById('newtogglePassword') },
    { input: document.getElementById('confirmpasswordInput'), toggle: document.getElementById('confirmtogglePassword') }
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

// Get Year
const getDate = new Date();
const getYear = getDate.getFullYear();

document.getElementById('year').textContent = getYear;

// // Animation
// ScrollReveal().reveal('#fade-in-section', {
//     duration: 500, // Duration of the animation
//     easing: 'ease-in-out', // Smooth easing
//     opacity: 0, // Start fully transparent
//     delay: 50, // Delay before animation starts
//     reset: true // Reset animation on scroll up
// });

// ScrollReveal().reveal('#fade-in-section-once', {
//     duration: 500, // Duration of the animation
//     easing: 'ease-in-out', // Smooth easing
//     opacity: 0, // Start fully transparent
//     delay: 50, // Delay before animation starts
//     reset: false // Reset animation on scroll up
// });

// ScrollReveal().reveal('#fade-in-section-top', {
//     origin: 'top', // The text will come from the top
//     distance: '50px', // Distance it moves when revealed
//     duration: 500, // Duration of the animation
//     easing: 'ease-out', // Easing function
//     opacity: 0, // Start with opacity 0 for a fade-in effect
//     delay: 50, // Optional delay before starting the animation
//     reset: true // Reset the animation when the element is out of view
// });

// ScrollReveal().reveal('#image-section', {
//     origin: 'right',
//     distance: '20px',
//     duration: 500,
//     easing: 'ease-out',
//     opacity: 0,
//     delay: 50,
//     reset: true
// });
