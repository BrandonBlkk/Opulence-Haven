<a href="#" id="moveUpBtn" class="fixed hidden sm:flex bottom-5 -right-full bg-blue-900 py-2 px-5 rounded-l-md z-30 hover:bg-black transition-all duration-300">
    <i class="ri-arrow-up-s-line text-xl text-white"></i>
</a>

<?php
// Get the current file name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<?php if ($current_page === 'Dining.php'): ?>
    <a href="tel:+1234567890" id="phoneBtn" class="fixed flex items-center justify-center bottom-20 -right-full bg-black w-12 h-12 rounded-full z-30 animate-pulse hover:bg-black transition-all duration-300">
        <i class="ri-phone-fill text-xl text-white"></i>
    </a>
<?php endif; ?>