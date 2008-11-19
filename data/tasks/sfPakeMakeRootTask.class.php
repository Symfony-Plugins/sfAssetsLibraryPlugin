<?php

// Task description
pake_desc('create a root node for the asset library');
pake_task('sfassetlibrary-create-root', 'project_exists');
pake_task('sfassetlibrary-create-root', 'app_exists');
pake_alias('sfalcr', 'sfassetlibrary-create-root');

/**
 *
 * @param object $task
 * @param array $args
 */
function run_sfassetlibrary_create_root($task, $args)
{
  $app = $args[0];
  $env = empty($args[1]) ? 'dev' : $args[1];
  // define constants
  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP',         $app);
  define('SF_ENVIRONMENT', $env);
  define('SF_DEBUG',       true);
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
  
  // initialize database manager
  $databaseManager = new sfDatabaseManager();
  $databaseManager->initialize();
  $con = Propel::getConnection();
  
  if(sfAssetFolderPeer::getRoot())
  {
    throw new sfException('The asset library already has a root');
  }
  echo pakeColor::colorize(sprintf("Creating root node at %s...\n", sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media')), 'COMMENT');
  sfAssetFolderPeer::createRoot();
  echo pakeColor::colorize("Root Node Created\n", 'INFO');
}
