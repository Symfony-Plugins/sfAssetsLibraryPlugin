<?php

class BasesfAssetActions extends sfActions
{
  public function executeIndex()
  {
    $this->getUser()->getAttributeHolder()->remove('popup', 'sf_admin/sf_asset/navigation');
    $this->redirect('sfAsset/list');
  }
  
  public function executeList()
  {
    $folder = sfAssetFolderPeer::retrieveByPath($this->getRequestParameter('dir'));
    if(!$folder)
    {
      if ($this->getFlash('sfAsset_folder_not_found'))
      {
        throw new sfException('You must create a root folder. Use the `php symfony sfassetlibrary-create-root` command for that.');
      }
      else
      {
        if ($popup = $this->getRequestParameter('popup'))
        {
          $this->getUser()->setAttribute('popup', $popup, 'sf_admin/sf_asset/navigation');
        }
        $this->setFlash('sfAsset_folder_not_found', true);
        $this->redirect('sfAsset/list');
      }
    }
    
    $dirs = $folder->getChildren();
    $c = new Criteria();
    $c->add(sfAssetPeer::FOLDER_ID, $folder->getId());
    $this->processSort();
    $sortOrder = $this->getUser()->getAttribute('sort', 'name', 'sf_admin/sf_asset/sort');
    switch($sortOrder)
    {
      case 'date':
        $dirs = sfAssetFolderPeer::sortByDate($dirs);
        $c->addDescendingOrderByColumn(sfAssetPeer::CREATED_AT);
        break;
      default:
        $dirs = sfAssetFolderPeer::sortByName($dirs);
        $c->addAscendingOrderByColumn(sfAssetPeer::FILENAME);
        break;
    }
    $this->files = sfAssetPeer::doSelect($c);
    $this->nb_files = count($this->files);
    if($this->nb_files) 
    {
      $total_size = 0;
      foreach ($this->files as $file)
      {
        $total_size += $file->getFilesize();
      }
      $this->total_size = $total_size;
    }
    $this->dirs = $dirs;
    $this->nb_dirs = count($dirs);
    $this->folder = $folder;

    $this->removeLayoutIfPopup();

    return sfView::SUCCESS;
  }
  
  protected function processSort()
  {
    if ($this->getRequestParameter('sort'))
    {
      $this->getUser()->setAttribute('sort', $this->getRequestParameter('sort'), 'sf_admin/sf_asset/sort');
    }
  }
  
  public function executeSearch()
  {
    // We keep the search params in the session for easier pagination
    if ($this->getRequest()->hasParameter('search_params'))
    {
      $search_params = $this->getRequestParameter('search_params');
      if (isset($search_params['created_at']['from']) && $search_params['created_at']['from'] !== '')
      {
        $search_params['created_at']['from'] = sfI18N::getTimestampForCulture($search_params['created_at']['from'], $this->getUser()->getCulture());
      }
      if (isset($search_params['created_at']['to']) && $search_params['created_at']['to'] !== '')
      {
        $search_params['created_at']['to'] = sfI18N::getTimestampForCulture($search_params['created_at']['to'], $this->getUser()->getCulture());
      }

      $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/sf_asset/search_params');
      $this->getUser()->getAttributeHolder()->add($search_params, 'sf_admin/sf_asset/search_params');
    }
    
    $this->search_params = $this->getUser()->getAttributeHolder()->getAll('sf_admin/sf_asset/search_params');

    $c = $this->processSearch();
    
    $pager = new sfPropelPager('sfAsset', sfConfig::get('app_sfAssetsLibrary_search_pager_size', 20));
    $pager->setCriteria($c);
    $pager->setPage($this->getRequestParameter('page', 1));
    $pager->setPeerMethod('doSelectJoinsfAssetFolder');
    $pager->init();
    
    $this->pager = $pager;
    
    $this->removeLayoutIfPopup();
  }
  
  protected function processSearch()
  {
    $search_params = $this->search_params;
    $c = new Criteria();
    
    if (isset($search_params['path']) && $search_params['path'] !== '')
    {
      $folder = sfAssetFolderPeer::retrieveByPath($search_params['path']);
      $c->addJoin(sfAssetPeer::FOLDER_ID, sfAssetFolderPeer::ID);
      $c->add(sfAssetFolderPeer::TREE_LEFT, $folder->getTreeLeft(), Criteria::GREATER_EQUAL);
      $c->add(sfAssetFolderPeer::TREE_RIGHT, $folder->getTreeRIGHT(), Criteria::LESS_EQUAL);
    }
    if (isset($search_params['name_is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::FILENAME, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::FILENAME, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['name']) && $search_params['name'] !== '')
    {
      $c->add(sfAssetPeer::FILENAME, '%'.trim($search_params['name'], '*%').'%', Criteria::LIKE);
    }
    if (isset($search_params['author_is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::AUTHOR, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::AUTHOR, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['author']) && $search_params['author'] !== '')
    {
      $c->add(sfAssetPeer::AUTHOR, '%'.trim($search_params['author'], '*%').'%', Criteria::LIKE);
    }
    if (isset($search_params['copyright_is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::COPYRIGHT, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::COPYRIGHT, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['copyright']) && $search_params['copyright'] !== '')
    {
      $c->add(sfAssetPeer::COPYRIGHT, '%'.trim($search_params['copyright'], '*%').'%', Criteria::LIKE);
    }
    if (isset($search_params['created_at']))
    {
      if (isset($search_params['created_at']['from']) && $search_params['created_at']['from'] !== '')
      {
        $criterion = $c->getNewCriterion(sfAssetPeer::CREATED_AT, $search_params['created_at']['from'], Criteria::GREATER_EQUAL);
      }
      if (isset($search_params['created_at']['to']) && $search_params['created_at']['to'] !== '')
      {
        if (isset($criterion))
        {
          $criterion->addAnd($c->getNewCriterion(sfAssetPeer::CREATED_AT, $search_params['created_at']['to'], Criteria::LESS_EQUAL));
        }
        else
        {
          $criterion = $c->getNewCriterion(sfAssetPeer::CREATED_AT, $search_params['created_at']['to'], Criteria::LESS_EQUAL);
        }
      }
      if (isset($criterion))
      {
        $c->add($criterion);
      }
    }
    if (isset($search_params['description_is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::DESCRIPTION, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::DESCRIPTION, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['description']) && $search_params['description'] !== '')
    {
      $c->add(sfAssetPeer::DESCRIPTION, '%'.trim($search_params['description'], '*%').'%', Criteria::LIKE);
    }
    
    $this->processSort();
    $sortOrder = $this->getUser()->getAttribute('sort', 'name', 'sf_admin/sf_asset/sort');
    switch($sortOrder)
    {
      case 'date':
        $c->addDescendingOrderByColumn(sfAssetPeer::CREATED_AT);
        break;
      default:
        $c->addAscendingOrderByColumn(sfAssetPeer::FILENAME);
        break;
    }
    
    return $c;
  }
  
  public function validateCreateFolder()
  {
    $valid = true;
    $parentFolder = sfAssetFolderPeer::retrieveByPath($this->getRequestParameter('parent_folder'));
    if(!$parentFolder)
    {
      $this->getRequest()->setError('parent_folder', 'Yopu must provide a valid parent folder');
      $valid = false;
    }
    $name = $this->getRequestParameter('name');
    $children = $parentFolder->getChildren();
    foreach ($children as $dir)
    {
      if(sfConfig::get('app_sfAssetsLibrary_case_sensitive_filesystem', true))
      {
        $test = (strtolower($dir->getName()) == strtolower($name));
      }
      else
      {
        $test = ($dir->getName() == $name);
      }
      if($test)
      {
        $this->getRequest()->setError('name', 'A directory with this name already exists there');
        $valid = false;
      }
    }
    $this->parentFolder = $parentFolder;
    return $valid;
  }
  
  public function handleErrorCreateFolder()
  {
    return sfView::SUCCESS;
  }
  
  public function executeCreateFolder()
  {
    if($this->getRequest()->getMethod() == sfRequest::POST)
    {
      // Handle the form submission
      $parentFolder = sfAssetFolderPeer::retrieveByPath($this->getRequestParameter('parent_folder'));
      $this->forward404Unless($parentFolder);
      $folder = new sfAssetFolder();
      $folder->setName($this->getRequestParameter('name'));
      $folder->insertAsLastChildOf($this->parentFolder);
      $folder->save();
      
      $this->redirectToPath('sfAsset/list?dir='.$folder->getRelativePath());
    }
    else
    {
      // Display the form
      return sfView::SUCCESS;
    }
  }
  
  public function executeMoveFolder()
  {
    $this->forward404Unless($this->getRequest()->getMethod() == sfRequest::POST);
    $folder = sfAssetFolderPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($folder);
    $targetFolder = sfAssetFolderPeer::retrieveByPath($this->getRequestParameter('new_folder'));
    
    try
    {
      $folder->move($targetFolder);
      $this->setFlash('notice', 'The folder has been moved');
    }
    catch (sfAssetException $e)
    {
      $this->setFlash('warning_message', $e->getMessage());
      $this->setFlash('warning_params', $e->getMessageParams());
    }

    return $this->redirectToPath('sfAsset/list?dir=' . $folder->getRelativePath());
  }
  
  public function executeRenameFolder()
  {
    $this->forward404Unless($this->getRequest()->getMethod() == sfRequest::POST);
    $folder = sfAssetFolderPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($folder);
    $newName = $this->getRequestParameter('new_name');
    try
    {
      $folder->rename($newName);
      $this->setFlash('notice', 'The folder has been renamed');
    }
    catch (sfAssetException $e)
    {

      $this->setFlash('warning_message', $e->getMessage());
      $this->setFlash('warning_params', $e->getMessageParams());
    }

    return $this->redirectToPath('sfAsset/list?dir=' . $folder->getRelativePath());
  }
  
  public function executeDeleteFolder()
  {
    $this->forward404Unless($this->getRequest()->getMethod() == sfRequest::POST);
    $folder = sfAssetFolderPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($folder);
    try
    {
      $folder->delete();
      $this->setFlash('notice', 'The folder has been deleted');
    }
    catch (sfAssetException $e)
    {
      $this->setFlash('warning_message', $e->getMessage());
      $this->setFlash('warning_params', $e->getMessageParams());
    }

    return $this->redirectToPath('sfAsset/list?dir=' . $folder->getParentPath());
  }
  
  public function executeAddQuick()
  {
    $folder = sfAssetFolderPeer::retrieveByPath($this->getRequestParameter('parent_folder'));
    $this->forward404Unless($folder);
    try
    {
      $asset = new sfAsset();
      $asset->setsfAssetFolder($folder);
      $asset->setDescription($this->getRequest()->getFileName('new_file'));
      try
      {
        $asset->setAuthor($this->getUser()->getUsername());
      }
      catch(sfException $e)
      {
        // no getUsername() method in sfUser, all right: do nothing
      }
      $asset->setFilename($this->getRequest()->getFileName('new_file'));
      $asset->create($this->getRequest()->getFilePath('new_file'));
      $asset->save();
    }
    catch(sfAssetException $e)
    {
      $this->setFlash('warning_message', $e->getMessage());
      $this->setFlash('warning_params', $e->getMessageParams());
      $this->redirectToPath('sfAsset/list?dir='.$folder->getRelativePath());
    }
    
    if($this->getUser()->hasAttribute('popup', 'sf_admin/sf_asset/navigation'))
    {
      if($this->getUser()->getAttribute('popup', null, 'sf_admin/sf_asset/navigation') == 1)
      {
        $this->redirect('sfAsset/tinyConfigMedia?id='.$asset->getId());
      }
      else
      {
        $this->redirectToPath('sfAsset/list?dir='.$folder->getRelativePath());
      }
    }
    $this->redirect('sfAsset/edit?id='.$asset->getId());
  }
  
  public function executeMassUpload()
  {
    if($this->getRequest()->getMethod() == sfRequest::POST)
    {
      $folder = sfAssetFolderPeer::retrieveByPath($this->getRequestParameter('parent_folder'));
      $this->forward404Unless($folder);
      try
      {
        $nbFiles = 0;
        for ($i = 1; $i <= sfConfig::get('app_sfAssetsLibrary_mass_upload_size', 5) ; $i++)
        {
          if ($filename = $this->getRequest()->getFileName('files['.$i.']'))
          {
            $asset = new sfAsset();
            $asset->setsfAssetFolder($folder);
            $asset->setDescription($filename);
            try
            {
              $asset->setAuthor($this->getUser()->getUsername());
            }
            catch(sfException $e)
            {
              // no getUsername() method in sfUser, all right: do nothing
            }
            $asset->setFilename($filename);
            $asset->create($this->getRequest()->getFilePath('files['.$i.']'));
            $asset->save();
            $nbFiles++;
          }
        }
      }
      catch(sfAssetException $e)
      {
        $this->setFlash('warning_message', $e->getMessage());
        $this->setFlash('warning_params', $e->getMessageParams());
        $this->redirectToPath('sfAsset/list?dir='.$folder->getRelativePath());
      }
      $this->setFlash('notice', 'Files successfully uploaded');
      $this->redirectToPath('sfAsset/list?dir='.$folder->getRelativePath());
    }
  }
  
  public function executeDeleteAsset()
  {
    $sf_asset = sfAssetPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($sf_asset);
    $folderPath = $sf_asset->getFolderPath();
    try
    {
      $sf_asset->delete();
    }
    catch (PropelException $e)
    {
      $this->getRequest()->setError('delete', 'Impossible to delete asset, probably due to related records');
      return $this->forward('sfAsset', 'edit');
    }

    return $this->redirectToPath('sfAsset/list?dir='.$folderPath);
  }
  
  public function executeCreate()
  {
    return $this->forward('sfAsset', 'edit');
  }

  public function executeSave()
  {
    return $this->forward('sfAsset', 'edit');
  }
  
  public function handleErrorEdit()
  {
    $this->preExecute();
    $this->sf_asset = $this->getsfAssetOrCreate();
    $this->updatesfAssetFromRequest();

    $this->labels = $this->getLabels();

    return sfView::SUCCESS;
  }
  
  public function executeEdit()
  {
    $this->sf_asset = $this->getsfAssetOrCreate();

    if ($this->getRequest()->getMethod() == sfRequest::POST)
    {
      $this->updatesfAssetFromRequest();

      $this->sf_asset->save();

      $this->setFlash('notice', 'Your modifications have been saved');

      return $this->redirect('sfAsset/edit?id='.$this->sf_asset->getId());
    }
  }
  
  public function executeMoveAsset()
  {
    $this->forward404Unless($this->getRequest()->getMethod() == sfRequest::POST);
    $sf_asset = sfAssetPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($sf_asset);
    $folder = sfAssetFolderPeer::retrieveByPath($this->getRequestParameter('new_folder'));
    $this->forward404Unless($folder);
    if ($folder->getId() != $sf_asset->getFolderId())
    {
      try
      {
        $sf_asset->move($folder);
        $sf_asset->save();
        $this->setFlash('notice', 'The file has been moved');
      }
      catch(sfAssetException $e)
      {
        $this->setFlash('warning_message', $e->getMessage());
        $this->setFlash('warning_params', $e->getMessageParams());
      }
    }
    else
    {
      $this->setFlash('warning', 'The target folder is the same as the original folder. The asset has not been moved.');
    }
    
    return $this->redirect('sfAsset/edit?id='.$sf_asset->getId());
  }

  public function executeRenameAsset()
  {
    $this->forward404Unless($this->getRequest()->getMethod() == sfRequest::POST);
    $sf_asset = sfAssetPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($sf_asset);
    $name = $this->getRequestParameter('new_name');
    $this->forward404Unless($name);
    if ($sf_asset->getFilename() != $name)
    {
      try
      {
        $sf_asset->move($sf_asset->getsfAssetFolder(), $name);
        $sf_asset->save();
        $this->setFlash('notice', 'The file has been renamed');
      }
      catch(sfAssetException $e)
      {
        $this->setFlash('warning_message', $e->getMessage());
        $this->setFlash('warning_params', $e->getMessageParams());
      }
    }
    else
    {
      $this->setFlash('notice', 'The target name is the same as the original name. The asset has not been renamed.');
      
    }
    
    return $this->redirect('sfAsset/edit?id='.$sf_asset->getId());
  }

  public function executeReplaceAsset()
  {
    $this->forward404Unless($this->getRequest()->getMethod() == sfRequest::POST);
    $sf_asset = sfAssetPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($sf_asset);
    if ($uploaded_filename = $this->getRequest()->getFileName('new_file'))
    {
      // physically replace asset
      $sf_asset->destroy();
      $sf_asset->create($this->getRequest()->getFilePath('new_file'), true, false);
    }
    
    $this->setFlash('notice', 'The file has been replaced');
    
    return $this->redirect('sfAsset/edit?id='.$sf_asset->getId());
  }

  protected function updatesfAssetFromRequest()
  {
    $sf_asset = $this->getRequestParameter('sf_asset');
    if (isset($sf_asset['description']))
    {
      $this->sf_asset->setDescription($sf_asset['description']);
    }
    if (isset($sf_asset['author']))
    {
      $this->sf_asset->setAuthor($sf_asset['author']);
    }
    if (isset($sf_asset['copyright']))
    {
      $this->sf_asset->setCopyright($sf_asset['copyright']);
    }
    if (isset($sf_asset['type']))
    {
      $this->sf_asset->setType($sf_asset['type']);
    }
  }
  
  protected function removeLayoutIfPopup()
  {
    if ($popup = $this->getRequestParameter('popup'))
    {
      $this->getUser()->setAttribute('popup', $popup, 'sf_admin/sf_asset/navigation');
    }
    if($this->getUser()->hasAttribute('popup', 'sf_admin/sf_asset/navigation'))
    {
      $this->setLayout(sfLoader::getTemplateDir('sfAsset', 'popupLayout.php').DIRECTORY_SEPARATOR.'popupLayout');
      $this->popup = true;
    }
    else
    {
      $this->popup = false;
    }
  }
  
  protected function getsfAssetOrCreate($id = 'id')
  {
    if (!$this->getRequestParameter($id))
    {
      $sf_asset = new sfAsset();
    }
    else
    {
      $sf_asset = sfAssetPeer::retrieveByPk($this->getRequestParameter($id));

      $this->forward404Unless($sf_asset);
    }
    
    return $sf_asset;
  }
  
  protected function redirectToPath($path, $statusCode = 302)
  {
    $url = $this->getController()->genUrl($path, true);
    $url = str_replace('%2F', '/', $url);

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfAction} redirect to "'.$url.'"');
    }
    
    $this->getController()->redirect($url, 0, $statusCode);
    
    throw new sfStopException();
  }
  
  public function executeTinyConfigMedia()
  {
    $this->forward404Unless($this->hasRequestParameter('id'));
    $this->sf_asset = sfAssetPeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($this->sf_asset);

    $this->setLayout(sfLoader::getTemplateDir('sfAsset', 'popupLayout.php').'/popupLayout');

    return sfView::SUCCESS;
  }
}