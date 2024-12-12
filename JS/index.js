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

// MoveUp Btn
const moveUpBtn = document.getElementById('moveUpBtn');
if (moveUpBtn) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 1000) {
            moveUpBtn.classList.remove('-right-full');
            moveUpBtn.classList.add('right-0');
        } else {
            moveUpBtn.classList.remove('right-0');
            moveUpBtn.classList.add('-right-full');
        }
    });
}

const logoutBtn = document.getElementById('logoutBtn');
const confirmModal = document.getElementById('confirmModal');
const cancelBtn = document.getElementById('cancelBtn');
const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
const darkOverlay2 = document.getElementById('darkOverlay2');

if (logoutBtn && confirmModal && cancelBtn && confirmLogoutBtn) {
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
        fetch('UserLogout.php', { method: 'POST' })
            .then(() => {
                // Redirect after logout
                setTimeout(() => {
                    window.location.href = 'HomePage.php';
                }, 1000);
            })
            .catch((error) => console.error('Logout failed:', error));
    });
    
}

// Get Year
const getDate = new Date();
const getYear = getDate.getFullYear();

document.getElementById('year').textContent = getYear;
