<?php use_helper('Object', 'Date', 'sfAsset') ?>
<?php echo form_tag('sfAsset/save', array(
  'id'        => 'sf_admin_edit_form',
  'name'      => 'sf_admin_edit_form',
  'multipart' => true,
)) ?>

<?php echo object_input_hidden_tag($sf_asset, 'getId') ?>

<fieldset id="sf_fieldset_none" class="">

  <div class="form-row">
    <?php echo label_for('sf_asset[filepath]', __('Path:', null, 'sfAsset'), '') ?>
    <div class="content<?php if ($sf_request->hasError('sf_asset{filepath}')): ?> form-error<?php endif; ?>">
    <?php if (!$sf_asset->isNew()): ?>
      <?php echo assets_library_breadcrumb($sf_asset->getRelativePath(), 0);?>
    <?php endif; ?>
    </div>
  </div>

</fieldset>

<fieldset id="sf_fieldset_meta" class="">

  <h2><?php echo __('Metadata', null, 'sfAsset') ?></h2>

  <div class="form-row">
    <?php echo label_for('sf_asset[description]', __('Description:', null, 'sfAsset'), '') ?>
    <div class="content<?php if ($sf_request->hasError('sf_asset{description}')): ?> form-error<?php endif; ?>">
      <?php if ($sf_request->hasError('sf_asset{description}')): ?>
        <?php echo form_error('sf_asset{description}', array('class' => 'form-error-msg')) ?>
      <?php endif; ?>
      <?php echo object_textarea_tag($sf_asset, 'getDescription', array(
        'size' => '30x3',
        'control_name' => 'sf_asset[description]',
      )) ?>
    </div>
  </div>
  
  <div class="form-row">
    <?php echo label_for('sf_asset[author]', __('Author:', null, 'sfAsset'), '') ?>
    <div class="content<?php if ($sf_request->hasError('sf_asset{author}')): ?> form-error<?php endif; ?>">
      <?php if ($sf_request->hasError('sf_asset{author}')): ?>
        <?php echo form_error('sf_asset{author}', array('class' => 'form-error-msg')) ?>
      <?php endif; ?>
      <?php echo object_input_tag($sf_asset, 'getAuthor', array(
        'size' => 80,
        'control_name' => 'sf_asset[author]',
      )) ?>
    </div>
  </div>

  <div class="form-row">
    <?php echo label_for('sf_asset[copyright]', __('Copyright:', null, 'sfAsset'), '') ?>
    <div class="content<?php if ($sf_request->hasError('sf_asset{copyright}')): ?> form-error<?php endif; ?>">
      <?php if ($sf_request->hasError('sf_asset{copyright}')): ?>
        <?php echo form_error('sf_asset{copyright}', array('class' => 'form-error-msg')) ?>
      <?php endif; ?>

      <?php echo object_input_tag($sf_asset, 'getCopyright', array(
      'size' => 80,
      'control_name' => 'sf_asset[copyright]',
      )) ?>
    </div>
  </div>

  <div class="form-row">
    <?php echo label_for('sf_asset[type]', __('Type:', null, 'sfAsset'), '') ?>
    <div class="content<?php if ($sf_request->hasError('sf_asset{type}')): ?> form-error<?php endif; ?>">
      <?php if ($sf_request->hasError('sf_asset{type}')): ?>
        <?php echo form_error('sf_asset{type}', array('class' => 'form-error-msg')) ?>
      <?php endif; ?>
      <?php foreach (sfConfig::get('app_sfAssetsLibrary_types', array('image', 'txt', 'archive', 'pdf', 'xls', 'doc', 'ppt')) as $type): ?>
        <?php $options[$type] = $type; ?>
      <?php endforeach; ?>
      <?php echo select_tag('sf_asset[type]', options_for_select($options, $sf_asset->getType())) ?>
    </div>
  </div>

  <?php include_partial('sfAsset/edit_form_custom', array('sf_asset' => $sf_asset)) ?>

</fieldset>

<?php include_partial('edit_actions', array('sf_asset' => $sf_asset)) ?>

</form>