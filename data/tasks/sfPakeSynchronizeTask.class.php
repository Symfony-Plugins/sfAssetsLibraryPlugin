<?php

// Task description
pake_desc('synchronize a physical folder content with the asset library');
pake_task('sfassetlibrary-synchronize', 'project_exists');
pake_alias('sfals', 'sfassetlibrary-synchronize');

/**
 *
 * @param object $task
 * @param array $args
 */
function run_sfassetlibrary_synchronize($task, $args, $options)
{
  if (!count($args))
  {
    sfAssetsLibraryTools::log('Usage: php symfony sfassetlibrary-synchronize [app] [dirname] --notVerbose --removeOrphanAssets --removeOrphanFolders');
    return;
  }
  $app = $args[0];
  if (!is_dir(sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$app))
  {
    throw new Exception('The app "'.$app.'" does not exist.');
  }
  if (!isset($args[1]))
  {
    throw new Exception('You must define a sychronization folder');
  }
  $base_folder = $args[1];
  $env = isset($args[2]) ? $args[2] : 'dev';
  $verbose = array_key_exists('notVerbose', $options) ? false : true;
  $removeOrphanAssets = array_key_exists('removeOrphanAssets', $options) ? true : false;
  $removeOrphanFolders = array_key_exists('removeOrphanFolders', $options) ? true : false;
  
  // define constants
  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP', $app);
  define('SF_ENVIRONMENT', $env);
  define('SF_DEBUG',       true);
  // get configuration
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
  
  // initialize database manager
  $databaseManager = new sfDatabaseManager();
  $databaseManager->initialize();
  $con = Propel::getConnection();
  
  // synchronize
  sfAssetsLibraryTools::log(sprintf("Comparing files from %s with assets stored in the database...", $base_folder), 'green');
  $rootFolder = sfAssetFolderPeer::getRoot();
  try
  {
    $rootFolder->synchronizeWith($base_folder, $verbose, $removeOrphanAssets, $removeOrphanFolders);
  }
  catch (sfAssetException $e)
  {
    throw new sfException(strtr($e->getMessage(), $e->getMessageParams()));
  }

  echo pakeColor::colorize("Synchronization complete\n", 'INFO');
}
