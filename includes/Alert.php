<input type="hidden" id="emailExists" value="<?php echo htmlspecialchars($emailExists); ?>">
<input type="hidden" id="signupSuccess" value="<?php echo $signupSuccess ? 'true' : 'false'; ?>">

<div id="alertBox" class="fixed -bottom-full right-3 items-center bg-white rounded-sm shadow-lg border z-40 p-3 transition-all duration-500 <?php echo $emailExists ? 'border-red-300' : 'border-green-300'; ?>">
    <div class="flex items-center justify-center gap-2">
        <div>
            <i class="text-2xl <?php echo $emailExists ? 'ri-error-warning-line text-red-400' : 'ri-checkbox-circle-fill text-green-400'; ?>"></i>
        </div>
        <p class="text-slate-600 font-semibold" id="alertText"></p>
    </div>
</div>