<input type="hidden" id="alertMessage" value="<?php echo htmlspecialchars($alertMessage); ?>">
<input type="hidden" id="signupSuccess" value="<?php echo $signupSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="signinSuccess" value="<?php echo $signinSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="resetSuccess" value="<?php echo $resetSuccess ? 'true' : 'false'; ?>">
<input type="hidden" id="profileUpdate" value="<?php echo $profileUpdate ? 'true' : 'false'; ?>">
<input type="hidden" id="isAccountLocked" value="<?php echo $isAccountLocked ? 'true' : 'false'; ?>">

<div id="alertBox" class="fixed -bottom-1 opacity-0 right-3 items-center rounded-md shadow-lg border z-40 p-3 transition-all duration-200 <?php echo $alertMessage ? 'border-red-300 bg-red-50' : 'border-green-300 bg-green-50'; ?>">
    <div class="flex items-center justify-center gap-2">
        <div>
            <i class="text-2xl <?php echo $alertMessage ? 'ri-error-warning-line text-red-400' : 'ri-checkbox-circle-fill text-green-400'; ?>"></i>
        </div>
        <p class="font-semibold <?php echo $alertMessage ? 'text-red-400' : 'text-green-400'; ?>" id="alertText"></p>
    </div>
</div>