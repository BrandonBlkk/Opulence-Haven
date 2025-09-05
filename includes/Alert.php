<input type="hidden" id="alertMessage" value="<?php echo htmlspecialchars($alertMessage); ?>">
<input type="hidden" id="resetAdminPasswordSuccess" value="<?php echo $resetAdminPasswordSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addProductImageSuccess" value="<?php echo $addProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateProductImageSuccess" value="<?php echo $updateProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteProductImageSuccess" value="<?php echo $deleteProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteAdminSuccess" value="<?php echo $deleteAdminSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="confirmContactSuccess" value="<?php echo $confirmContactSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="purchaseSuccess" value="<?php echo $purchaseSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="resetPasswordSuccess" value="<?php echo $resetPasswordSuccess ? 'true' : 'false'; ?>">

<div id="alertBox" class="fixed -bottom-20 right-3 left-3 sm:left-auto sm:w-auto sm:max-w-sm w-auto max-w-full items-center rounded-md shadow-lg z-40 px-4 py-3 transition-all duration-150 ease-out transform opacity-0">
    <i id="closeAlert" class="absolute top-0 right-1 cursor-pointer ri-close-line text-slate-50 hover:text-slate-100 transition-colors duration-200"></i>
    <div class="flex items-center justify-center gap-2 select-none">
        <div>
            <i id="alertIcon" class="text-2xl text-white"></i>
        </div>
        <p class="font-semibold text-white text-sm sm:text-base" id="alertText"></p>
    </div>
    <div id="alertLoader" class="absolute bottom-0 left-0 h-1 bg-white bg-opacity-30 rounded-b-md w-full overflow-hidden">
        <div id="alertLoaderBar" class="h-full bg-opacity-70 transition-all duration-[5000ms] ease-linear w-0"></div>
    </div>
</div>