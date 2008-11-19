<?php use_helper('Validation', 'I18N') ?>

<h1><?php echo __('Create a new folder', null, 'sfAsset') ?></h1>

<?php include_partial('sfAsset/create_folder_header') ?>

<?php echo form_tag('sfAsset/createFolder') ?>
  <fieldset>
    
    <div class="form-row">
      <label for="parent_folder"><?php echo __('Place under:', null, 'sfAsset') ?></label>
      <?php echo select_tag('parent_folder', options_for_select(sfAssetFolderPeer::getAllPaths(), $sf_params->get('parent_folder'))) ?>
    </div>
  
    <div class="form-row<?php if ($sf_request->hasError('name')): ?> form-error<?php endif; ?>">
      <?php if ($sf_request->hasError('name')): ?>
        <?php echo form_error('name', array('class' => 'form-error-msg')) ?>
      <?php endif; ?>
      <label for="name"><?php echo __('New folder name:', null, 'sfAsset') ?></label>
      <?php echo input_tag('name') ?>
    </div>
  
  </fieldset>
  
  <?php include_partial('edit_actions') ?>
  
</form>

<?php include_partial('sfAsset/create_folder_footer') ?>