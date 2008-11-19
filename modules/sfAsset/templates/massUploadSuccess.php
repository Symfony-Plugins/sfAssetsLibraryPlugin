<?php use_helper('I18N') ?>

<h1><?php echo __('Mass upload files', null, 'sfAsset') ?></h1>

<?php include_partial('sfAsset/create_folder_header') ?>

<?php echo form_tag('sfAsset/massUpload', 'method=post multipart=true') ?>
  <fieldset>
    
    <div class="form-row">
      <label for="parent_folder"><?php echo __('Place under:', null, 'sfAsset') ?></label>
      <?php echo select_tag('parent_folder', options_for_select(sfAssetFolderPeer::getAllPaths(), $sf_params->get('parent_folder'))) ?>
    </div>
  
    <?php for($i = 1; $i <= sfConfig::get('app_sfAssetsLibrary_mass_upload_size', 5) ; $i++): ?>
    <div class="form-row">
      <label for="files_1"><?php echo __('File %nb%:', array('%nb%' => $i), 'sfAsset') ?></label>
      <?php echo input_file_tag('files['.$i.']') ?>
    </div>
    <?php endfor; ?>
  
  </fieldset>
  
  <?php include_partial('edit_actions') ?>
  
</form>

<?php include_partial('sfAsset/create_folder_footer') ?>