<?php use_helper('Javascript', 'sfAsset') ?>

<?php if ($folder->isRoot()): ?>
<div class="form-row">
  <?php echo image_tag('/sfAssetsLibraryPlugin/images/images.png', 'align=top') ?>
  <?php echo link_to(__('Mass upload', null, 'sfAsset'), 'sfAsset/massUpload') ?>
</div>
<?php endif ?>

<?php echo form_tag('sfAsset/addQuick', 'method=post multipart=true') ?>
<?php echo input_hidden_tag('parent_folder', $sf_params->get('dir')) ?>
<div class="form-row">
  <label for="new_file">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/image_add.png', 'align=top') ?>
    <?php echo link_to_function(__('Upload a file here', null, 'sfAsset'), 'document.getElementById("input_new_file").style.display="block"') ?>
  </label>
  <div class="content" id="input_new_file" style="display:none">
    <?php echo input_file_tag('new_file', 'size=7') ?> <?php echo submit_tag(__('Add', null, 'sfAsset')) ?>
  </div>
</div>
</form>

<?php echo form_tag('sfAsset/createFolder', 'method=post') ?>
<?php echo input_hidden_tag('parent_folder', $sf_params->get('dir')) ?>
<div class="form-row">
  <label for="new_directory">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_add.png', 'align=top') ?>
    <?php echo link_to_function(__('Add a subfolder', null, 'sfAsset'), 'document.getElementById("input_new_directory").style.display="block"') ?>
  </label>
  <div class="content" id="input_new_directory" style="display:none">
    <?php echo input_tag('name', null, 'size=17') ?> <?php echo submit_tag(__('Create', null, 'sfAsset')) ?>
  </div>
</div>
</form>

<?php if (!$folder->isRoot()): ?>
<?php echo form_tag('sfAsset/renameFolder', 'method=post') ?>
<?php echo input_hidden_tag('id', $folder->getId()) ?>
<div class="form-row">
  <label for="new_folder">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_edit.png', 'align=top') ?>
    <?php echo link_to_function(__('Rename folder', null, 'sfAsset'), 'document.getElementById("input_new_name").style.display="block";document.getElementById("new_name").focus()') ?>
  </label>
  <div class="content" id="input_new_name" style="display:none">
    <?php echo input_tag('new_name', $folder->getName(), 'size=17') ?>
    <?php echo submit_tag(__('Ok', null, 'sfAsset')) ?>
  </div>
</div>
</form>

<?php echo form_tag('sfAsset/moveFolder', 'method=post') ?>
<?php echo input_hidden_tag('id', $folder->getId()) ?>
<div class="form-row">
  <label for="new_folder">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_go.png', 'align=top') ?>
    <?php echo link_to_function(__('Move folder', null, 'sfAsset'), 'document.getElementById("input_move_folder").style.display="block"') ?>
  </label>
  <div class="content" id="input_move_folder" style="display:none">
    <?php echo select_tag('new_folder', options_for_select(sfAssetFolderPeer::getAllNonDescendantsPaths($folder), $folder->getParentPath()), 'style=width:170px') ?>
    <?php echo submit_tag(__('Ok', null, 'sfAsset')) ?>
  </div>
</div>
</form>

<div class="form-row">
  <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_delete.png', 'align=top') ?>
  <?php echo link_to(__('Delete folder', null, 'sfAsset'), 'sfAsset/deleteFolder?id='.$folder->getId(), array(
    'post' => true,
    'confirm' => __('Are you sure?', null, 'sfAsset'),
  )) ?>
</div>
<?php endif; ?>
