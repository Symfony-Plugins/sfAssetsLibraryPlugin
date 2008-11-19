<?php

$app = "frontend";
include(dirname(__FILE__).'/../../../../test/bootstrap/functional.php');
$browser = new sfTestBrowser();
$browser->initialize();
$con = Propel::getConnection();

$con->begin();
try
{
  // run the test
  $t = new lime_test(20, new lime_output_color());
  $t->diag('sfAsset');
  
  $sfAsset = new sfAsset();
  $sfAsset->setsfAssetFolder(sfAssetFolderPeer::getRoot());
  $t->isa_ok($sfAsset->getsfAssetFolder(), 'sfAssetFolder', 'sfAsset can have root as folder');
  $sfAsset->setFilename('filename.jpg');
  $t->is($sfAsset->getRelativePath(), sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media').  DIRECTORY_SEPARATOR . 'filename.jpg', 'getRelativePath() gives correct result');
  
//  sfConfig::set('sf_web_dir', 'var/www/myproject');
  sfConfig::set('app_sfAssetsLibrary_upload_dir','medias');
  $t->is($sfAsset->getFullPath(), sfConfig::get('sf_web_dir'). DIRECTORY_SEPARATOR . sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media'). DIRECTORY_SEPARATOR .'filename.jpg','getFullPath() gives complete path'); 
  $t->is($sfAsset->getFullPath('large'), sfConfig::get('sf_web_dir'). DIRECTORY_SEPARATOR . sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media').DIRECTORY_SEPARATOR .'thumbnail/large_filename.jpg','getFullPath() gives correct thumbnail path'); 
  
  $t->is($sfAsset->getUrl(),DIRECTORY_SEPARATOR .sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media').DIRECTORY_SEPARATOR .'filename.jpg','getUrl() gives correct url');
  $t->is($sfAsset->getUrl('small'),DIRECTORY_SEPARATOR . sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media').DIRECTORY_SEPARATOR .'thumbnail/small_filename.jpg','getUrl() gives correctthumbnail url');

  $assets_path = dirname(__FILE__).'/../assets/';
  $test_asset = $assets_path . 'raikkonen.jpg';
  $t->ok(is_file($test_asset), 'test asset found');
  
  $sfAsset = new sfAsset();
  $sfAsset->setsfAssetFolder(sfAssetFolderPeer::getRoot());
  $sfAsset->create($test_asset, false);
  $t->is($sfAsset->getFilename(),'raikkonen.jpg', 'create() gives correct filename');
  $t->is($sfAsset->getFilesize(), 18, 'create() gives correct size');
  $t->ok($sfAsset->isImage(), 'create() gives correct type');
  $t->ok(is_file($sfAsset->getFullPath()), 'create() physically copies asset');
  $t->ok(is_file($sfAsset->getFullPath('large')), 'create() physically creates thumbnail');
  
  $old_path = $sfAsset->getFullPath();
  $old_thumb_path = $sfAsset->getFullPath('large');
  $sfAsset->move(sfAssetFolderPeer::getRoot(), 'raikkonen2.jpg');
  $t->is($sfAsset->getFilename(),'raikkonen2.jpg', 'move() changes filename');
  $t->ok(is_file($sfAsset->getFullPath()), 'move() physically moves asset');
  $t->ok(! is_file($old_path),'move() physically moves asset');
  $t->ok(is_file($sfAsset->getFullPath('large')), 'move() physically moves thumbnail');
  $t->ok(! is_file($old_thumb_path),'move() physically moves thumbnail');

  $old_path = $sfAsset->getFullPath();
  $old_thumb_path = $sfAsset->getFullPath('large');
  $old_id = $sfAsset->getId();
  $sfAsset->delete();
  $t->ok(! is_file($old_path),'delete() physically removes asset');
  $t->ok(! is_file($old_thumb_path),'delete() physically removes thumbnail');
  $null = sfAssetPeer::retrieveByPk($old_id);
  $t->ok(! $null,'delete() removes asset from DB');
}
catch (Exception $e)
{
  // do nothing
}

// reset DB
$con->rollback();

