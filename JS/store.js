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