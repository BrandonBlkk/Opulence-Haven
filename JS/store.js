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