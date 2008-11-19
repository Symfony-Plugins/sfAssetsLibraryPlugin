<?php if ($sf_request->hasErrors()): ?>
<div class="form-errors">
<h2><?php echo __('The form is not valid because it contains some errors.', null, 'sfAsset') ?></h2>
</div>
<?php elseif ($sf_flash->has('notice')): ?>
<div class="save-ok">
<h2><?php echo __($sf_flash->get('notice'), null, 'sfAsset') ?></h2>
</div>
<?php elseif ($sf_flash->has('warning')): ?>
<div class="warning">
<h2><?php echo __($sf_flash->get('warning'), null, 'sfAsset') ?></h2>
</div>
<?php elseif ($sf_flash->has('warning_message') && $sf_flash->has('warning_params')): ?>
<div class="warning">
<h2><?php echo __($sf_flash->getRaw('warning_message'), $sf_flash->getRaw('warning_params'), 'sfAsset') ?></h2>
</div>
<?php endif; ?>
