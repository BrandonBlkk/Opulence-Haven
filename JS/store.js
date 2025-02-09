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

// Search Bar Close Btn
const closeBtn = document.getElementById('closeBtn');
const aside = document.getElementById('aside');
const darkOverlay = document.getElementById('darkOverlay');

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