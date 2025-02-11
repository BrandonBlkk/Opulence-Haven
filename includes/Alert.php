<input type="hidden" id="alertMessage" value="<?php echo htmlspecialchars($alertMessage); ?>">
<input type="hidden" id="signupSuccess" value="<?php echo $signupSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="signinSuccess" value="<?php echo $signinSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="resetSuccess" value="<?php echo $resetSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="resetAdminPasswordSuccess" value="<?php echo $resetAdminPasswordSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="profileUpdate" value="<?php echo $profileUpdate ? 'true' : 'false'; ?>">
<input type="hidden" id="isAccountLocked" value="<?php echo $isAccountLocked ? 'true' : 'false'; ?>">
<input type="hidden" id="addProductTypeSuccess" value="<?php echo $addProductTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateProductTypeSuccess" value="<?php echo $updateProductTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteProductTypeSuccess" value="<?php echo $deleteProductTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addProductSuccess" value="<?php echo $addProductSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateProductSuccess" value="<?php echo $updateProductSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteProductSuccess" value="<?php echo $deleteProductSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addProductImageSuccess" value="<?php echo $addProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateProductImageSuccess" value="<?php echo $updateProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteProductImageSuccess" value="<?php echo $deleteProductImageSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addProductSizeSuccess" value="<?php echo $addProductSizeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateProductSizeSuccess" value="<?php echo $updateProductSizeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteProductSizeSuccess" value="<?php echo $deleteProductSizeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addSupplierSuccess" value="<?php echo $addSupplierSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateSupplierSuccess" value="<?php echo $updateSupplierSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteSupplierSuccess" value="<?php echo $deleteSupplierSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addRoleSuccess" value="<?php echo $addRoleSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addRoomTypeSuccess" value="<?php echo $addRoomTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateRoomTypeSuccess" value="<?php echo $updateRoomTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteRoomTypeSuccess" value="<?php echo $deleteRoomTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addFacilityTypeSuccess" value="<?php echo $addFacilityTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateFacilityTypeSuccess" value="<?php echo $updateFacilityTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteFacilityTypeSuccess" value="<?php echo $deleteFacilityTypeSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addFacilitySuccess" value="<?php echo $addFacilitySuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateFacilitySuccess" value="<?php echo $updateFacilitySuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteFacilitySuccess" value="<?php echo $deleteFacilitySuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="addRuleSuccess" value="<?php echo $addRuleSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="updateRuleSuccess" value="<?php echo $updateRuleSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteRuleSuccess" value="<?php echo $deleteRuleSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="deleteAdminSuccess" value="<?php echo $deleteAdminSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="confirmContactSuccess" value="<?php echo $confirmContactSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="contactSuccess" value="<?php echo $contactSuccess ? 'true' : 'false'; ?>">

<div id="alertBox" class="fixed -bottom-1 opacity-0 right-3 items-center rounded-md shadow-lg z-40 p-3 transition-all duration-200 <?php echo $alertMessage ? 'bg-red-400' : 'bg-green-400'; ?>">
    <div class="flex items-center justify-center gap-2 select-none">
        <div>
            <i class="text-2xl text-white <?php echo $alertMessage ? 'ri-error-warning-line' : 'ri-checkbox-circle-fill'; ?>"></i>
        </div>
        <p class="font-semibold text-white <?php echo $alertMessage ? 'bg-red-400' : 'bg-green-400'; ?>" id="alertText"></p>
    </div>
</div>