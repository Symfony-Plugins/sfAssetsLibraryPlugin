<?php use_helper('Javascript') ?>
<div class="form-row">
  <?php echo image_tag('/sfAssetsLibraryPlugin/images/magnifier.png', 'align=top') ?>
  <?php echo link_to_function(
    __('Search', null, 'sfAsset'),
    'document.getElementById("sf_asset_search").style.display="block"'
  ) ?> 
</div>

<?php echo form_tag('sfAsset/search', array('method' => 'get', 'id' => 'sf_asset_search', 'style' => 'display:none')) ?>
    <div class="form-row">
    <label for="search_params_rel_path"><?php echo __('Folder:', null, 'sfAsset') ?></label>
    <div class="content">
        <?php echo select_tag('search_params[path]', '<option></option>'.options_for_select(sfAssetFolderPeer::getAllPaths(), isset($search_params['path']) ? $search_params['path'] : null), 'style=width:200px') ?>
    </div>
  </div>

  <div class="form-row">
  <label for="search_params_name"><?php echo __('Filename:', null, 'sfAsset') ?></label>
    <div class="content">
    <?php echo input_tag('search_params[name]', isset($search_params['name']) ? $search_params['name'] : null, 'size=20') ?>
    </div>
  </div>

  <div class="form-row">
    <label for="search_params_author"><?php echo __('Author:', null, 'sfAsset') ?></label>
    <div class="content">
    <?php echo input_tag('search_params[author]', isset($search_params['author']) ? $search_params['author'] : null, 'size=20') ?>
    </div>
  </div>

  <div class="form-row">
    <label for="search_params_copyright"><?php echo __('Copyright:', null, 'sfAsset') ?></label>
    <div class="content">
    <?php echo input_tag('search_params[copyright]', isset($search_params['copyright']) ? $search_params['copyright'] : null, 'size=20') ?>
    </div>
  </div>

  <div class="form-row">
    <label for="search_params_created_at"><?php echo __('Created on:', null, 'sfAsset') ?></label>
    <div class="content">
    <?php echo input_date_range_tag('search_params[created_at]', isset($search_params['created_at']) ? $search_params['created_at'] : null, array (
  'rich' => true,
  'withtime' => true,
  'calendar_button_img' => '/sf/sf_admin/images/date.png',
)) ?>
    </div>
  </div>

  <div class="form-row">
    <label for="search_params_description"><?php echo __('Description:', null, 'sfAsset') ?></label>
    <div class="content">
    <?php echo input_tag('search_params[description]', isset($search_params['description']) ? $search_params['description'] : null, 'size=20') ?>
    </div>
  </div>

  <?php include_partial('sfAsset/search_custom', array('search_params' => isset($search_params) ? $search_params : array())) ?>

  <ul class="sf_admin_actions">
    <li><?php echo submit_tag(__('Search', null, 'sfAsset'), 'name=search class=sf_admin_action_filter') ?></li>
  </ul>

</form>