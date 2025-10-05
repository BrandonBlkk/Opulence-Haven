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

// Room Favorite Multiple Forms
document.addEventListener('DOMContentLoaded', function () {
    const loginModal = document.getElementById('loginModal');
    const darkOverlay2 = document.getElementById('darkOverlay2');
    const sparkleColors = [
        'bg-amber-500', 'bg-red-500', 'bg-pink-500', 'bg-yellow-400', 'bg-white', 'bg-blue-300'
    ];

    // Handle multiple favorite forms (.favoriteForm)
    const favoriteForms = document.querySelectorAll('.favoriteForm');
    if (favoriteForms.length > 0) {
        favoriteForms.forEach(favoriteForm => {
            const favoriteBtn = favoriteForm.querySelector('.favoriteBtn');
            const heartIcon = favoriteForm.querySelector('.heartIcon');
            const heartParticles = favoriteForm.querySelector('.heartParticles');

            if (!favoriteBtn || !heartIcon || !heartParticles) return;

            favoriteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const wasFavorited = heartIcon.classList.contains('text-red-500');

                heartIcon.classList.add('animate-bounce');
                favoriteBtn.disabled = true;

                fetch('../User/favorite_handler.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'added') {
                            heartIcon.classList.remove('text-slate-400', 'hover:text-red-300');
                            heartIcon.classList.add('text-red-500', 'hover:text-red-600');
                            createSparkleEffectMultiple(heartParticles);
                        } else if (data.status === 'removed') {
                            heartIcon.classList.remove('text-red-500', 'hover:text-red-600');
                            heartIcon.classList.add('text-slate-400', 'hover:text-red-300');
                        } else if (data.status === 'not_logged_in') {
                            if (loginModal && darkOverlay2) {
                                loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                                darkOverlay2.classList.remove('opacity-0', 'invisible');
                                darkOverlay2.classList.add('opacity-100');

                                const closeLoginModal = document.getElementById('closeLoginModal');
                                if (closeLoginModal) {
                                    closeLoginModal.addEventListener('click', function () {
                                        loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                                        darkOverlay2.classList.add('opacity-0', 'invisible');
                                        darkOverlay2.classList.remove('opacity-100');
                                    });
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        setTimeout(() => {
                            heartIcon.classList.remove('animate-bounce');
                            favoriteBtn.disabled = false;
                        }, 500);
                    });
            });

            function createSparkleEffectMultiple(heartParticles) {
                heartParticles.innerHTML = '';
                for (let i = 0; i < 5; i++) {
                    const sparkle = document.createElement('div');
                    const randomColor = sparkleColors[Math.floor(Math.random() * sparkleColors.length)];
                    sparkle.className = `absolute w-1.5 h-1.5 ${randomColor} rounded-full opacity-0`;
                    sparkle.style.left = `${30 + Math.random() * 40}%`;
                    sparkle.style.top = `${30 + Math.random() * 40}%`;

                    sparkle.animate([
                        { transform: 'translate(0, 0) scale(0.5)', opacity: 0 },
                        {
                            transform: `translate(${(Math.random() - 0.5) * 10}px, ${(Math.random() - 0.5) * 10}px) scale(1.8)`,
                            opacity: 0.9,
                            offset: 0.5
                        },
                        {
                            transform: `translate(${(Math.random() - 0.5) * 20}px, ${(Math.random() - 0.5) * 20}px) scale(0.2)`,
                            opacity: 0
                        }
                    ], {
                        duration: 1000,
                        delay: i * 150,
                        easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
                    });

                    heartParticles.appendChild(sparkle);
                    setTimeout(() => sparkle.remove(), 1150 + i * 150);
                }
            }
        });
    }

    // Handle single favorite form (#favoriteForm)
    const singleFavoriteForm = document.getElementById('favoriteForm');
    if (singleFavoriteForm) {
        const favoriteBtn = document.getElementById('favoriteBtn');
        const heartIcon = document.getElementById('heartIcon');
        const heartParticles = document.getElementById('heartParticles');

        singleFavoriteForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const wasFavorited = heartIcon.classList.contains('text-red-500');

            heartIcon.classList.add('animate-bounce');
            favoriteBtn.disabled = true;

            fetch('../User/favorite_handler.php', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'added') {
                        heartIcon.classList.remove('text-slate-400', 'hover:text-red-300');
                        heartIcon.classList.add('text-red-500', 'hover:text-red-600');
                        animateHeartChange(true, wasFavorited);
                        createSparkleEffectSingle();
                    } else if (data.status === 'removed') {
                        heartIcon.classList.remove('text-red-500', 'hover:text-red-600');
                        heartIcon.classList.add('text-slate-400', 'hover:text-red-300');
                        animateHeartChange(false, wasFavorited);
                    } else if (data.status === 'not_logged_in') {
                        if (loginModal && darkOverlay2) {
                            loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            darkOverlay2.classList.remove('opacity-0', 'invisible');
                            darkOverlay2.classList.add('opacity-100');

                            const closeLoginModal = document.getElementById('closeLoginModal');
                            if (closeLoginModal) {
                                closeLoginModal.addEventListener('click', function () {
                                    loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                                    darkOverlay2.classList.add('opacity-0', 'invisible');
                                    darkOverlay2.classList.remove('opacity-100');
                                });
                            }
                        }
                    } else if (data.error) {
                        console.error('Error:', data.error);
                        revertHeartState(wasFavorited);
                        alert('An error occurred: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    revertHeartState(wasFavorited);
                    alert('An error occurred. Please try again.');
                })
                .finally(() => {
                    setTimeout(() => {
                        heartIcon.classList.remove('animate-bounce');
                        favoriteBtn.disabled = false;
                    }, 500);
                });
        });

        function animateHeartChange(isNowFavorited, wasFavorited) {
            if (isNowFavorited === wasFavorited) return;
        }

        function revertHeartState(wasFavorited) {
            if (wasFavorited) {
                heartIcon.classList.add('text-red-500', 'hover:text-red-600');
                heartIcon.classList.remove('text-slate-400', 'hover:text-red-300');
            } else {
                heartIcon.classList.add('text-slate-400', 'hover:text-red-300');
                heartIcon.classList.remove('text-red-500', 'hover:text-red-600');
            }
        }

        function createSparkleEffectSingle() {
            heartParticles.innerHTML = '';
            for (let i = 0; i < 5; i++) {
                const sparkle = document.createElement('div');
                const randomColor = sparkleColors[Math.floor(Math.random() * sparkleColors.length)];
                sparkle.className = `absolute w-1.5 h-1.5 ${randomColor} rounded-full opacity-0`;
                sparkle.style.left = `${30 + Math.random() * 40}%`;
                sparkle.style.top = `${30 + Math.random() * 40}%`;

                sparkle.animate([
                    { transform: 'translate(0, 0) scale(0.5)', opacity: 0 },
                    {
                        transform: `translate(${(Math.random() - 0.5) * 10}px, ${(Math.random() - 0.5) * 10}px) scale(1.8)`,
                        opacity: 0.9,
                        offset: 0.5
                    },
                    {
                        transform: `translate(${(Math.random() - 0.5) * 20}px, ${(Math.random() - 0.5) * 20}px) scale(0.2)`,
                        opacity: 0
                    }
                ], {
                    duration: 1000,
                    delay: i * 150,
                    easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
                });

                heartParticles.appendChild(sparkle);
                setTimeout(() => sparkle.remove(), 1150 + i * 150);
            }
        }
    }
});

//Room Review
const viewAllReviews = document.getElementById('viewAllReviews');
if (viewAllReviews) {
    const reviewCloseBtn = document.getElementById('reviewCloseBtn');
    const roomReview = document.getElementById('roomReview');
    const darkOverlay = document.getElementById('darkOverlay');

    viewAllReviews.addEventListener('click', () => {
        roomReview.style.top = '0%';
    });

    reviewCloseBtn.addEventListener('click', () => {
        roomReview.style.top = '100%';

        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
        });
    });

    darkOverlay.addEventListener('click', () => {
        roomReview.style.top = '100%';
    });
}

// Room Type Review
document.addEventListener("DOMContentLoaded", () => {
    const travellerTypeSelect = document.getElementById("travellerTypeSelect");
    const reviewInput = document.getElementById("reviewInput");

    if (reviewInput) reviewInput.addEventListener("keyup", validateRoomTypeReview);
    if (travellerTypeSelect) travellerTypeSelect.addEventListener("change", validateTravellerType);

    document.querySelectorAll('.star-rating').forEach(star => {
        star.addEventListener('click', () => {
            hideError(document.getElementById('ratingError'));
        });
    });

    const writeReviewModal = document.getElementById("writeReviewModal");
    const reviewForm = document.getElementById("reviewForm");

    if (reviewForm) {
        reviewForm.addEventListener("submit", async function(e) {
            e.preventDefault();

            if (!validateRoomTypeReviewForm()) return;

            try {
                const formData = new FormData(this);
                const ratingValue = document.getElementById('ratingValue').value;
                formData.append('rating', ratingValue);

                const response = await fetch('../User/room_details.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

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
                } else {
                    showAlert(data.message || 'Failed to submit review', true);
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', true);
                console.error('Error:', error);
            }
        });
    }
});

// Room Type Reaction & Filter Initialization
document.addEventListener('DOMContentLoaded', function() {
    const loginModal = document.getElementById('loginModal');
    const roomReview = document.getElementById('roomReview');
    const darkOverlay2 = document.getElementById('darkOverlay2');

    function initializeReactions() {
        const forms = document.querySelectorAll('.roomtype-reaction-form');
        if (!forms.length) return;

        forms.forEach(form => {
            if (form.dataset.bound === "true") return;
            form.dataset.bound = "true";

            const reviewID = form.querySelector('input[name="review_id"]').value;
            const roomTypeID = form.querySelector('input[name="roomTypeID"]').value;
            const checkin_date = form.querySelector('input[name="checkin_date"]').value;
            const checkout_date = form.querySelector('input[name="checkout_date"]').value;
            const adults = form.querySelector('input[name="adults"]').value;
            const children = form.querySelector('input[name="children"]').value;

            const likeBtn = form.querySelector('.like-btn');
            const dislikeBtn = form.querySelector('.dislike-btn');
            const likeIcon = likeBtn.querySelector('i');
            const dislikeIcon = dislikeBtn.querySelector('i');
            const likeCountSpan = likeBtn.querySelector('.like-count');
            const dislikeCountSpan = dislikeBtn.querySelector('.dislike-count');

            likeBtn.addEventListener('click', () => sendReaction('like'));
            dislikeBtn.addEventListener('click', () => sendReaction('dislike'));

            function sendReaction(type) {
                const body = new URLSearchParams({
                    review_id: reviewID,
                    roomTypeID: roomTypeID,
                    checkin_date,
                    checkout_date,
                    adults,
                    children,
                    reaction_type: type
                });

                fetch('reaction_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'not_logged_in') {
                        if (loginModal && darkOverlay2) {
                            loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            darkOverlay2.classList.remove('opacity-0', 'invisible');
                            darkOverlay2.classList.add('opacity-100');
                            if (roomReview) roomReview.
                            style.top = '100%';

                        const closeLoginModal = document.getElementById('closeLoginModal');
                            if (closeLoginModal) {
                                closeLoginModal.addEventListener('click', function () {
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
                            likeIcon.classList.toggle('ri-thumb-up-fill');
                            likeIcon.classList.toggle('ri-thumb-up-line');
                            likeBtn.classList.toggle('text-gray-500');

                            dislikeIcon.classList.replace('ri-thumb-down-fill', 'ri-thumb-down-line');
                            dislikeBtn.classList.remove('text-gray-500');
                        } else {
                            dislikeIcon.classList.toggle('ri-thumb-down-fill');
                            dislikeIcon.classList.toggle('ri-thumb-down-line');
                            dislikeBtn.classList.toggle('text-gray-500');

                            likeIcon.classList.replace('ri-thumb-up-fill', 'ri-thumb-up-line');
                            likeBtn.classList.remove('text-gray-500');
                        }
                    }
                })
                .catch(err => console.error(err));
            }
        });

        initializeEditDelete();
    }

    function initializeEditDelete() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.removeEventListener('click', editHandler);
            btn.addEventListener('click', editHandler);
        });

        function editHandler() {
            const reviewId = this.getAttribute('data-review-id');
            const comment = this.getAttribute('data-comment');
            const form = document.querySelector(`.edit-form[data-review-id="${reviewId}"]`);
            const reviewText = document.querySelector(`.review[data-review-id="${reviewId}"]`);
            if (!form || !reviewText) return;
            const textarea = form.querySelector('textarea');
            if (textarea) textarea.value = comment;
            reviewText.classList.add('hidden');
            form.classList.remove('hidden');
        }

        // Cancel edit buttons
        document.querySelectorAll('.cancel-edit').forEach(btn => {
            btn.removeEventListener('click', cancelHandler);
            btn.addEventListener('click', cancelHandler);
        });

        function cancelHandler() {
            const editForm = this.closest('.edit-form');
            const reviewId = editForm.getAttribute('data-review-id');
            const reviewText = document.querySelector(`.review[data-review-id="${reviewId}"]`);
            if (editForm && reviewText) {
                editForm.classList.add('hidden');
                reviewText.classList.remove('hidden');
            }
        }

        // AJAX SUBMIT EDIT FORM
        document.querySelectorAll('.edit-form').forEach(form => {
            form.removeEventListener('submit', editSubmitHandler);
            form.addEventListener('submit', editSubmitHandler);
        });

        async function editSubmitHandler(e) {
            e.preventDefault();
            const form = e.currentTarget;
            const formData = new FormData(form);

            try {
                const response = await fetch('../User/room_details.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    // Update review text
                    const reviewId = form.querySelector('input[name="review_id"]').value;
                    const reviewText = document.querySelector(`.review[data-review-id="${reviewId}"] p`);
                    reviewText.textContent = `"${formData.get('updated_comment')}"`;
                    form.classList.add('hidden');
                    reviewText.closest('.review').classList.remove('hidden');
                    showAlert(data.message, false);
                } else {
                    showAlert(data.message || 'Failed to update review', true);
                }
            } catch (err) {
                console.error(err);
                showAlert('An error occurred. Please try again.', true);
            }
        }

        // AJAX SUBMIT DELETE FORM
        document.querySelectorAll('.delete-form').forEach(form => {
            form.removeEventListener('submit', deleteSubmitHandler);
            form.addEventListener('submit', deleteSubmitHandler);
        });

        async function deleteSubmitHandler(e) {
            e.preventDefault();
            const form = e.currentTarget;
            const formData = new FormData(form);

            try {
                const response = await fetch('../User/room_details.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    const reviewId = form.querySelector('input[name="review_id"]').value;
                    const reviewCard = form.closest('.review-card');
                    if (reviewCard) reviewCard.remove();
                    showAlert(data.message, false);
                } else {
                    showAlert(data.message || 'Failed to delete review', true);
                }
            } catch (err) {
                console.error(err);
                showAlert('An error occurred. Please try again.', true);
            }
        }
    }

    function initializeReviewFilters() {
        document.querySelectorAll('.auto-submit').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const showLoading = this.dataset.clicked === 'false';
                if (showLoading) this.dataset.clicked = 'true';
                submitReviewFilterForm(showLoading);
            });
        });
    }

    function submitReviewFilterForm(showLoading) {
        const formData = new URLSearchParams();
        document.querySelectorAll('input[name="ratings[]"]:checked').forEach(cb => formData.append('ratings[]', cb.value));
        document.querySelectorAll('input[name="traveller_type[]"]:checked').forEach(cb => formData.append('traveller_type[]', cb.value));
        document.querySelectorAll('input[name="roomtypes[]"]:checked').forEach(cb => formData.append('roomtypes[]', cb.value));

        const currentParams = new URLSearchParams(window.location.search);
        currentParams.forEach((value, key) => {
            if (!['ratings[]','traveller_type[]','roomtypes[]'].includes(key)) formData.append(key, value);
        });

        formData.append('ajax_request', '1');
        if (showLoading) showReviewLoadingState();
        fetchReviewResults(formData, showLoading);
    }

    function showReviewLoadingState() {
        const container = document.getElementById('reviews-container');
        if (!container) return;
        container.innerHTML = `<div class="w-[80%] space-y-4">${Array(3).fill().map(() => `<div class="bg-white p-4 animate-pulse"><div class="flex items-center mb-4"><div class="w-10 h-10 rounded-full bg-gray-200 mr-3"></div><div class="space-y-2 flex-1"><div class="h-4 bg-gray-200 rounded w-1/3"></div><div class="h-3 bg-gray-200 rounded w-1/4"></div></div></div><div class="space-y-2"><div class="h-4 bg-gray-200 rounded w-full"></div><div class="h-4 bg-gray-200 rounded w-5/6"></div><div class="h-4 bg-gray-200 rounded w-3/4"></div></div></div>`).join('')}</div>`;
    }

    function fetchReviewResults(formData, shouldDelay) {
        const url = window.location.pathname + '?' + formData.toString();
        fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
        .then(res => res.text())
        .then(data => {
            const processData = () => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;
                const newContent = tempDiv.querySelector('#reviews-container');
                if (newContent) {
                    document.getElementById('reviews-container').innerHTML = newContent.innerHTML;
                    window.history.pushState({ path: url.toString() }, '', url.toString());
                    fetchCountryNames();
                    document.dispatchEvent(new Event('reviewsUpdated'));
                }
            };
            if (shouldDelay) setTimeout(processData, 1000);
            else processData();
        })
        .catch(err => console.error(err));
    }

    function fetchCountryNames() {
        document.querySelectorAll('.country-name').forEach(el => {
            const countryCode = el.getAttribute('data-country-code');
            if (!el._fetched) {
                el._fetched = true;
                fetch(`https://restcountries.com/v3.1/alpha/${countryCode}`)
                    .then(res => res.json())
                    .then(data => el.textContent = data[0]?.name?.common || countryCode)
                    .catch(() => el.textContent = countryCode);
            }
        });
    }

    initializeReactions();
    initializeReviewFilters();
    fetchCountryNames();

    document.addEventListener('reviewsUpdated', () => {
        initializeReactions();
    });
});

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

        // Hide all error messages
        const errors = ['diningNameError', 'diningEmailError', 'diningPhoneError'];
        errors.forEach(error => {
            hideError(document.getElementById(error));
        });
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
    const diningForm = document.getElementById("diningForm");

    const nameInput = document.getElementById("diningNameInput");
    const phoneInput = document.getElementById("diningPhoneInput");

    // Validation functions
    const validateDiningName = () => {
        return validateField(
            "diningNameInput",
            "diningNameError",
            (input) => {
                if (!input) {
                    return "Name is required.";
                }
                if (input.length < 3) {
                    return "Name must be at least 3 characters.";
                }
                if (input.length > 50) {
                    return "Name is too long.";
                }
                return null;
            }
        );
    };

    // Dining Phone Validation
    const validateDiningPhone = () => {
        return validateField(
            "diningPhoneInput",
            "diningPhoneError",
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
    };

    function validateDiningForm() {
        const isDiningNameValid = validateDiningName();
        const isvalidDiningPhone = validateDiningPhone();
        return isDiningNameValid && isvalidDiningPhone;

    }

    // Real-time validation
    nameInput.addEventListener("keyup", validateDiningName);
    phoneInput.addEventListener("keyup", validateDiningPhone);

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
        const loader = document.getElementById('loader');
        loader.style.display = "flex";

        fetch("user_account_delete.php", {
            method: "POST",
        }) 
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "home_page.php";
            } else {
                loader.style.display = "none";
                alert(data.message || "Account deletion failed. Please try again.");
            }
        })
        .catch(error => {
            console.error("Account deletion failed:", error);
            loader.style.display = "none";
            alert("Account deletion failed. Please try again.");
        });
    });
}

//Contact Form
document.addEventListener("DOMContentLoaded", () => {
    // Add keyup event listeners for real-time validation
    document.getElementById("contactFullNameInput").addEventListener("keyup", validateFuillName);
    document.getElementById("contactPhoneInput").addEventListener("keyup", validateContactPhone);
    document.getElementById("contactMessageInput").addEventListener("keyup", validateContactMessage);

    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (!validateContactForm()) {
                return;
            }
            
            // Check reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse || recaptchaResponse.length === 0) {
                showAlert("Please complete the reCAPTCHA.", true);
                return;
            }

            const formData = new FormData(contactForm);
            formData.append('contactSubmit', true); 
            const loader = document.getElementById('loader');
            
            // Show loader when request starts
            if (loader) loader.style.display = 'flex';
            
            fetch("../User/contact.php", {
                method: "POST",
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showAlert('Your message has been successfully sent.');
                    contactForm.reset(); 
                } else {
                    showAlert(data.message || "Failed to submit contact form.");
                }
            })
            .catch((error) => {
                console.error("Contact form submission failed:", error);
                showAlert("An error occurred while submitting the form. Please try again.");
            })
            .finally(() => {
                // Hide loader when request completes 
                if (loader) loader.style.display = 'none';
            });
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
const detailsBtns = document.querySelectorAll('.details-btn');

// Bind buttons after filtering
window.bindReservationButtons = function() {
    const detailsBtns = document.querySelectorAll('.details-btn');
    const reservationDetailModal = document.getElementById('reservationDetailModal');
    const darkOverlay2 = document.getElementById('darkOverlay2');
    const closeReservationDetailModal = document.getElementById('closeReservationDetailModal');

    detailsBtns.forEach((btn) => {
        btn.addEventListener('click', async function() {
            const reservationId = btn.dataset.reservationId;

            try {
                const res = await fetch(`upcoming_stays.php?id=${reservationId}&action=getReservationDetails`);
                const data = await res.json();

                if (data.success) {
                    const reservations = data.reservations;

                    // Find earliest check-in and latest checkout
                    let earliestCheckin = new Date(reservations[0].CheckInDate);
                    let latestCheckout = new Date(reservations[0].CheckOutDate);
                    let totalNights = 0;
                    let totalPrice = 0;

                    reservations.forEach(room => {
                        const checkin = new Date(room.CheckInDate);
                        const checkout = new Date(room.CheckOutDate);
                        const nights = (checkout - checkin) / (1000 * 60 * 60 * 24);
                        totalNights += nights;
                        totalPrice += parseFloat(room.Price) * nights;

                        if (checkin < earliestCheckin) earliestCheckin = checkin;
                        if (checkout > latestCheckout) latestCheckout = checkout;
                    });

                    // Group rooms by RoomTypeID
                    const groupedRooms = {};
                    reservations.forEach(room => {
                        if (!groupedRooms[room.RoomType]) groupedRooms[room.RoomType] = [];
                        groupedRooms[room.RoomType].push(room);
                    });

                    // Build modal HTML
                    let html = '';

                    // --- Reservation Status Notification ---
                    const today = new Date();
                    let statusMessage = '';
                    let statusClass = '';

                    if (today < earliestCheckin) {
                        statusMessage = `Upcoming Stay: Your reservation starts on ${earliestCheckin.toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'})}`;
                        statusClass = 'bg-blue-50 border-blue-400 text-blue-800';
                    } else if (today >= earliestCheckin && today <= latestCheckout) {
                        statusMessage = 'Ongoing Stay: Enjoy your current reservation!';
                        statusClass = 'bg-green-50 border-green-400 text-green-800';
                    } else if (today > latestCheckout) {
                        statusMessage = 'Past Stay: This reservation has been completed.';
                        statusClass = 'bg-gray-50 border-gray-400 text-gray-800';
                    }

                    // Room Information HTML
                    html += `<div class="bg-gray-50 p-4 rounded-lg">
        <div class="flex items-center gap-5 mb-3">
            <h4 class="font-medium text-gray-800">Room Information</h4>
            <h4 class="flex items-center gap-2 ${statusClass} border rounded-lg p-2">
                <span class="text-sm font-medium">${statusMessage}</span>
            </h4>
        </div>
        <div class="swiper roomTypeSwiper"><div class="swiper-wrapper">`;

                    for (let type in groupedRooms) {
                        const rooms = groupedRooms[type];
                        const firstRoom = rooms[0];
                        html += `<div class="swiper-slide">
            <div class="flex flex-col md:flex-row gap-4 py-2">
                <div class="md:w-1/3 select-none">
                    <div class="relative" style="height: 200px;">
                        <img src="../Admin/${firstRoom.RoomCoverImage}" alt="Room Image" class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                    </div>
                </div>
                <div class="md:w-2/3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h5 class="font-bold text-lg text-gray-800">${firstRoom.RoomType}</h5>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">${firstRoom.RoomDescription}</p>
                            <div class="mt-2 text-xs text-gray-500">
                                ${rooms.length} room${rooms.length > 1 ? 's' : ''} of this type
                                <div class="flex flex-wrap gap-2 mt-1">`;

                        rooms.forEach(room => {
                            html += `<div class="group relative">
                <span class="bg-gray-100 px-2 py-1 rounded text-gray-600 font-semibold text-xs cursor-default">Room #${room.RoomName}</span>
                <div class="absolute z-20 left-0 mt-1 w-64 bg-white p-3 rounded-lg shadow-sm border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                    <div class="flex items-center gap-2 text-sm mb-1">
                        <i class="ri-calendar-check-line text-orange-500"></i>
                        ${new Date(room.CheckInDate).toLocaleDateString('en-US', {month:'short', day:'numeric'})}
                        <span class="text-gray-400">→</span>
                        ${new Date(room.CheckOutDate).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})}
                    </div>
                    <div class="flex items-center gap-2 text-sm mb-2">
                        <i class="ri-user-line text-orange-500"></i>
                        ${room.Adult} Adult${room.Adult > 1 ? 's' : ''}
                        ${room.Children > 0 ? `+ ${room.Children} Child${room.Children > 1 ? 'ren' : ''}` : ''}
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">$${parseFloat(room.Price).toFixed(2)}</span>
                        <span class="text-gray-500">/night</span>
                    </div>
                </div>
            </div>`;
                        });

                        html += `</div></div></div></div>
                <a href="../User/room_details.php?roomTypeID=${firstRoom.RoomTypeID}&checkin_date=${firstRoom.CheckInDate}&checkout_date=${firstRoom.CheckOutDate}&adults=${firstRoom.Adult}&children=${firstRoom.Children}" class="mt-2 text-orange-600 hover:text-orange-700 font-medium inline-flex items-center text-xs bg-orange-50 px-3 py-1 rounded-full">
                    <i class="ri-information-line mr-1"></i> Room Details
                </a>
                </div></div></div>`;
                    }

                    html += `</div><div class="swiper-pagination"></div></div></div>`;

                    // Pricing Breakdown
                    html += `<div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-medium text-gray-800 mb-3">Pricing Breakdown</h4>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">Room Rate (${totalNights} night${totalNights>1?'s':''}):</span>
                <span class="text-sm font-medium text-gray-600">$${totalPrice.toFixed(2)}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600">Taxes & Fees:</span>
                <span class="text-sm font-medium text-gray-600">$${(totalPrice*0.1).toFixed(2)}</span>
            </div>
            <div class="border-t border-gray-200 pt-2 flex justify-between">
                <span class="font-medium text-gray-800">Total:</span>
                <span class="font-bold text-gray-600">$${(totalPrice*1.1).toFixed(2)}</span>
            </div>
        </div>
    </div>`;

                    // Cancellation Deadline
                    const cancellationDeadline = new Date(earliestCheckin);
                    cancellationDeadline.setDate(cancellationDeadline.getDate() - 1);
                    const deadlineFormatted = cancellationDeadline.toLocaleDateString("en-US", {
                        weekday: "short",
                        day: "numeric",
                        month: "short",
                        year: "numeric"
                    });

                    html += `<div class="bg-red-50 border-l-4 border-red-400 p-4 mt-4">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="ri-alert-line text-red-500 mt-1"></i></div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-red-800">Cancellation Policy</h4>
                                <p class="text-sm text-red-700 mt-1">
                                    Free cancellation is available until ${deadlineFormatted}.
                                    Cancellations made on or after check-in will incur a $50 fee.
                                </p>
                            </div>
                        </div>
                    </div>`;

                    // Insert HTML
                    document.querySelector('#reservationDetailModal .space-y-3').innerHTML = html;

                    // Show modal
                    reservationDetailModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    // Initialize Swiper
                    new Swiper('.roomTypeSwiper', {
                        slidesPerView: 1,
                        spaceBetween: 20,
                        centeredSlides: true,
                        pagination: { el: '.swiper-pagination', clickable: true },
                        breakpoints: { 768: { slidesPerView: 1, spaceBetween: 30 } }
                    });
                }
            } catch (error) {
                console.error('Error fetching reservation details:', error);
            }
        });
    });

    // Close modal
    closeReservationDetailModal.addEventListener('click', function() {
        reservationDetailModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.add('opacity-0', 'invisible');
        darkOverlay2.classList.remove('opacity-100');
    });
};

// Initial call
window.bindReservationButtons();

closeReservationDetailModal.addEventListener('click', function () {
    reservationDetailModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
    darkOverlay2.classList.add('opacity-0', 'invisible');
    darkOverlay2.classList.remove('opacity-100');
});

// Cancel Reservation Modal
const cancelButtons = document.querySelectorAll('.openCancelModalBtn');

cancelButtons.forEach(button => {
    button.addEventListener('click', function() {
        const reservationId = this.dataset.reservationId;
        const dates = this.dataset.dates;
        const totalPrice = parseFloat(this.dataset.totalPrice);

        const checkinDate = new Date(dates.split(' - ')[0]);
        const today = new Date();

        // ✅ Free cancellation deadline = 24 hours before check-in
        const freeCancellationDate = new Date(checkinDate);
        freeCancellationDate.setDate(checkinDate.getDate() - 1);

        // Format deadline to Wed, Sep 30, 2025
        const deadline = freeCancellationDate.toLocaleDateString("en-US", {
            weekday: "short",  // Wed
            day: "numeric",    // 30
            month: "short",    // Sep
            year: "numeric"    // 2025
        });

        // Determine cancellation fee
        let cancellationFee = 0;
        if (today >= freeCancellationDate) {
            cancellationFee = 50; // $50 fee if within 24h of check-in or later
        }

        // Update modal content
        document.getElementById('cancelReservationId').textContent = reservationId;
        document.getElementById('cancelReservationDates').textContent = dates;
        document.getElementById('refundAmount').textContent = '$' + totalPrice.toFixed(2);
        document.getElementById('cancellationFee').textContent = `$${cancellationFee.toFixed(2)}`;
        document.getElementById('totalRefund').textContent = `$${(totalPrice - cancellationFee).toFixed(2)}`;
        document.getElementById('cancelDeadlineText').textContent = 
            `Free cancellation is available until ${deadline}. After this date, a $50 cancellation fee applies.`;

        // Show modal and overlay
        const cancelModal = document.getElementById('cancelModal');
        cancelModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        darkOverlay2.classList.remove('opacity-0', 'invisible');
        darkOverlay2.classList.add('opacity-100');

        // Confirm cancellation
        document.getElementById('confirmCancelBtn').onclick = function() {
            cancelReservation(reservationId);
        };
    });
});

// Close cancel modal
document.getElementById('closeCancelModal').addEventListener('click', function() {
    const cancelModal = document.getElementById('cancelModal');
    cancelModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
    darkOverlay2.classList.add('opacity-0', 'invisible');
    darkOverlay2.classList.remove('opacity-100');
});

document.getElementById('cancelGoBackBtn').addEventListener('click', function() {
    const cancelModal = document.getElementById('cancelModal');
    cancelModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
    darkOverlay2.classList.add('opacity-0', 'invisible');
    darkOverlay2.classList.remove('opacity-100');
});

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

const validateContactForm = () => {
    const isContactFullNameValid = validateFuillName();
    const isContactPhoneValid = validateContactPhone();
    const isContactMessageValid = validateContactMessage();

    return isContactFullNameValid && isContactPhoneValid && isContactMessageValid;
}

const validateRoomTypeReviewForm = () => {
    const isTravellerTypeValid = validateTravellerType();
    const isRatingValid = validateRating();
    const isRoomTypeReviewValid = validateRoomTypeReview();

    return isTravellerTypeValid && isRatingValid && isRoomTypeReviewValid;
}

// Individual validation functions

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
        if (contactFullNameInput.length > 50) return "Full name is too long.";
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
        if (contactMessageInput.length < 10) return "Message must be at least 10 characters long.";
        if (contactMessageInput.length > 1000) return "Message cannot exceed 1000 characters.";
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

const validateTravellerType = () => {
    const travellerTypeSelect = document.getElementById("travellerTypeSelect").value.trim();
    const travellerTypeError = document.getElementById("travellerTypeError");

    const getMessageError = (travellerTypeSelect) => {
        if (!travellerTypeSelect) return "Traveller type is required.";
        return null; 
    };

    const errorMessage = getMessageError(travellerTypeSelect);

    switch (true) {
        case errorMessage !== null:
            showError(travellerTypeError, errorMessage);
            return false;
        default:
            hideError(travellerTypeError);
            return true;
    }
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

const validateRoomTypeReview = () => {
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
