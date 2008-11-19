<?php

use_helper('Url');

function auto_wrap_text($text)
{
  return preg_replace('/([_\-\.])/', '<span class="wrap_space"> </span>$1<span class="wrap_space"> </span>', $text);
  return wordwrap($text, 2, '<span class="wrap_space"> </span>', true);
}

/**
 * Gives an image tag for an asset
 * 
 * @param sfAsset $asset
 * @param string $thumbnail_type
 * @param bool $file_system
 * @param array $options
 * @return string
 */
function asset_image_tag($asset, $thumbnail_type = 'full', $options = array(), $relative_path = null)
{
  $options = array_merge(array(
    'alt'   => $asset->getDescription() . ' ' . $asset->getCopyright(),
    'title' => $asset->getDescription() . ' ' . $asset->getCopyright()
  ), $options);
  
  if($asset->isImage())
  {
    $src = $asset->getUrl($thumbnail_type, $relative_path);
  }
  else
  {
    if($thumbnail_type == 'full')
    {
      throw new sfAssetException('Impossible to render a non-image asset in an image tag');
    }
    else
    {
      switch($asset->getType())
      {
        case 'txt':
          $src = '/sfAssetsLibraryPlugin/images/txt.png';
          break;
        case 'xls':
          $src = '/sfAssetsLibraryPlugin/images/xls.png';
          break;
        case 'doc':
          $src = '/sfAssetsLibraryPlugin/images/doc.png';
          break;
        case 'pdf':
          $src = '/sfAssetsLibraryPlugin/images/pdf.png';
          break;
        case 'html':
          $src = '/sfAssetsLibraryPlugin/images/html.png';
          break;
        case 'archive':
          $src = '/sfAssetsLibraryPlugin/images/archive.png';
          break;
        case 'bin':
          $src = '/sfAssetsLibraryPlugin/images/bin.png';
          break;
        default:
          $src = '/sfAssetsLibraryPlugin/images/unknown.png';
      }
    }
  }
  return image_tag($src, $options);
}

function link_to_asset($text, $path, $options = array())
{
  return str_replace('%2F', '/', link_to($text, $path, $options));
}

function link_to_asset_action($text, $asset)
{
  $user = sfContext::getInstance()->getUser();
  if ($user->hasAttribute('popup', 'sf_admin/sf_asset/navigation'))
  {
    switch($user->getAttribute('popup', null, 'sf_admin/sf_asset/navigation'))
    {
      case 1:
        // popup called from a Rich Text Editor (ex: TinyMCE)
        return link_to($text, "sfAsset/tinyConfigMedia?id=".$asset->getId(), 'title='.$asset->getFilename());
      case 2:
        // popup called from a simple form input (or via input_sf_asset_tag)
        return link_to_function($text, "setImageField('".$asset->getUrl()."')");
    }
  }
  else
  {
    // case : sf view (i.e. module sfAsset, view list)
    return link_to($text, "sfAsset/edit?id=".$asset->getId(), 'title='.$asset->getFilename());
  }
}

function init_asset_library()
{
  use_helper('Javascript');
  use_javascript('/sfAssetsLibraryPlugin/js/main', 'last');

  echo javascript_tag('sfAssetsLibrary.init(\''.url_for('sfAsset/list?popup=2').'\')');
}

function object_input_sf_asset_tag($object, $method, $options = array())
{
  $options = _parse_attributes($options);
  $name    = _convert_method_to_name($method, $options);
  $value   = _get_object_value($object, $method);

  return input_sf_asset_tag($name, $value, $options);
}

function input_sf_asset_tag($name, $value, $options = array())
{
  use_helper('Form', 'I18N');
  use_javascript('/sfAssetsLibraryPlugin/js/main', 'last');
  $options = _convert_options($options);
  $type = 'all';
  if (isset($options['images_only']))
  {
    $type = 'image';
    unset($options['images_only']);
  }
  if(!isset($options['id']))
  {
    $options['id'] = get_id_from_name($name);
  }

  $form_name = 'this.previousSibling.previousSibling.form.name';
  if (isset($options['form_name']))
  {
    $form_name = "'".$options['form_name']."'";
    unset($options['form_name']);
  }
  
  // The popup should open in the currently selected subdirectory
  $html  = input_tag($name, $value, $options) . '&nbsp;';
  $html .= image_tag('/sfAssetsLibraryPlugin/images/folder_open', array(
    'alt' => __('Insert Image'), 
    'style' => 'cursor: pointer; vertical-align: middle', 
    'onclick' => "
      initialDir = document.getElementById('".$options['id']."').value.replace(/\/[^\/]*$/, '');
      if(!initialDir) initialDir = '".sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media')."';
      sfAssetsLibrary.openWindow({
        form_name: ".$form_name.",
        field_name: '".$name."',
        type: '".$type."',
        url: '".url_for('sfAsset/list?dir=PLACEHOLDER')."?popup=2'.replace('PLACEHOLDER', initialDir),
        scrollbars: 'yes'
      });"
  ));

  return $html;
}

function init_assets_library_popup()
{
  use_javascript('/sfAssetsLibraryPlugin/js/main', 'last');

  return javascript_tag('sfAssetsLibrary.init(\''.url_for('sfAsset/list').'?popup=2'.'\')');
}

function assets_library_breadcrumb($path, $linkLast = false, $action = '')
{
  $action = $action ? $action : sfContext::getInstance()->getRequest()->getParameter('action');
  if($action == "edit")
  {
    $action = "list";
  }
  $html = '';
  $breadcrumb = explode("/" , $path);
  $nb_dirs = count($breadcrumb);
  $current_dir = '';
  $i = 0;
  foreach ($breadcrumb as $dir)
  {
    if(!$linkLast && ($i == $nb_dirs - 1))
    {
      $html .= $dir;
    }
    else
    {
      $current_dir .= $i ? '/' . $dir : $dir;
      $html .= link_to_asset($dir, 'sfAsset/'.$action.'?dir='.$current_dir) .'<span class="crumb">/</span>';
    }
    $i++;
  }
  return $html;
}