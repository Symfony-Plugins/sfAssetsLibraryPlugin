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
  $t = new lime_test(13, new lime_output_color());
  $t->diag('sfAssetFolder');
  
  $root = sfAssetFolderPeer::getRoot();
  $t->isa_ok($root, 'sfAssetFolder', 'root folder exists');
  $sfAssetFolder = new sfAssetFolder();
  $sfAssetFolder->insertAsFirstChildOf($root);
  $sfAssetFolder->setName('Test_Directory');
  $sfAssetFolder->save();
  $t->is($sfAssetFolder->getRelativePath(), $root->getRelativePath() . '/' . $sfAssetFolder->getName(),'getRelativePath() is updated on save');
  
  $sfAssetFolder2 = new sfAssetFolder();
  $sfAssetFolder2->insertAsFirstChildOf($sfAssetFolder);
  $sfAssetFolder2->setName('Test_Sub-directory');
  $sfAssetFolder2->save();
  $t->is($sfAssetFolder2->getRelativePath(), $sfAssetFolder->getRelativePath() . '/' . $sfAssetFolder2->getName(),'getRelativePath() is updated on save for subfolders');
  
  $assets_path = dirname(__FILE__).'/../assets/';
  $test_asset = $assets_path . 'raikkonen.jpg';
  $t->ok(is_file($test_asset), 'test asset found');  
  $sfAsset = new sfAsset();
  $sfAsset->setsfAssetFolder($sfAssetFolder2);
  $sfAsset->create($test_asset, false);
  $sfAsset->save();
  $t->ok(is_file($sfAsset->getFullPath()), 'asset found');
  $sf_asset_id = $sfAsset->getId();
  
  $sfAssetFolder3 = new sfAssetFolder();
  $sfAssetFolder3->insertAsFirstChildOf($sfAssetFolder2);
  $sfAssetFolder3->setName('Test_Sub-sub-directory');
  $sfAssetFolder3->save();
  $t->is($sfAssetFolder3->getRelativePath(), $sfAssetFolder2->getRelativePath() . '/' . $sfAssetFolder3->getName(),'getRelativePath() is updated on save for subfolders');
  $sfAsset2 = new sfAsset();
  $sfAsset2->setsfAssetFolder($sfAssetFolder3);
  $sfAsset2->setFilename('toto');
  $sfAsset2->create($test_asset, false);
  $sfAsset2->save();
  $t->ok(is_file($sfAsset2->getFullPath()), 'asset2 found');
  $sf_asset2_id = $sfAsset2->getId();  
  $id3 = $sfAssetFolder3->getId();

  $sfAssetFolder2->move($root);

  $sfAssetFolder3 = sfAssetFolderPeer::retrieveByPk($id3);

  $t->is($sfAssetFolder2->getParent()->getId(),$root->getId(),'move() gives the correct parent');
  $t->is($sfAssetFolder3->getParent()->getParent()->getId(),$root->getId(),'move() changes descendants grandparents');
  $t->is($sfAssetFolder2->getRelativePath(), $root->getRelativePath() . '/' . $sfAssetFolder2->getName(),'move() changes descendants relative paths');
  $t->is($sfAssetFolder3->getRelativePath(), $sfAssetFolder2->getRelativePath() . '/' . $sfAssetFolder3->getName(),'move() changes descendants relative paths');
  
  $sfAsset = sfAssetPeer::retrieveByPk($sf_asset_id);
  $sfAsset2 = sfAssetPeer::retrieveByPk($sf_asset2_id);
  $t->ok(is_file($sfAsset->getFullPath()), 'base asset of moved folder found');
  $t->ok(is_file($sfAsset2->getFullPath()), 'deep asset of moved folder found');
}
catch (Exception $e)
{
  echo $e->getMessage();
}

// reset DB
$con->rollback();

