<input type="hidden" id="alertMessage" value="<?php echo htmlspecialchars($alertMessage); ?>">
<input type="hidden" id="resetAdminPasswordSuccess" value="<?php echo $resetAdminPasswordSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addProductImageSuccess" value="<?php echo $addProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateProductImageSuccess" value="<?php echo $updateProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteProductImageSuccess" value="<?php echo $deleteProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addRoleSuccess" value="<?php echo $addRoleSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteAdminSuccess" value="<?php echo $deleteAdminSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="confirmContactSuccess" value="<?php echo $confirmContactSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="contactSuccess" value="<?php echo $contactSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="reservationSuccess" value="<?php echo $reservationSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="purchaseSuccess" value="<?php echo $purchaseSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="resetPasswordSuccess" value="<?php echo $resetPasswordSuccess ? 'true' : 'false'; ?>">

<div id="alertBox" class="fixed -bottom-20 right-3 items-center rounded-md shadow-lg z-40 p-3 transition-all duration-150 ease-out transform opacity-0">
    <div class="flex items-center justify-center gap-2 select-none">
        <div>
            <i id="alertIcon" class="text-2xl text-white"></i>
        </div>
        <p class="font-semibold text-white" id="alertText"></p>
    </div>
</div>