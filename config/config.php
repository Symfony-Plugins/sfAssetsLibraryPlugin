<?php
if (sfConfig::get('app_sfAssetsLibraryplugin_routes_register', true) && in_array('sfAsset', sfConfig::get('sf_enabled_modules', array())))
{
  $r = sfRouting::getInstance();
 	
  // preprend our routes
  $r->prependRoute(
    'sf_asset_library_dir', 
    '/sfAsset/dir/:dir', 
    array(
      'module'    => 'sfAsset',
      'action'    => 'list',
      'dir'       => sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media')
    ),
    array('dir' => '.*?')
  );
}
?>