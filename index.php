<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of smiliesEditor, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2015 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$page_title = __('Smilies Editor');

$s =& $core->blog->settings->smilieseditor;
$theme = $core->blog->settings->system->theme;
$msg = $warning = '';

// Init 
$smg_writable =  false;
if ($core->auth->isSuperAdmin() && $theme !='default')
{
	$combo_action[__('Definition')] = array(
	__('update') => 'update',
	__('delete') => 'clear'
	);
}

$combo_action[__('Toolbar')] = array(
__('display') => 'display',
__('hide') => 'hide',
);

$smilies_bar_flag = (boolean)$s->smilies_bar_flag;
$smilies_preview_flag = (boolean)$s->smilies_preview_flag;
$smilies_public_text = $s->smilies_public_text;

// Get theme Infos
$core->themes = new dcThemes($core);
$core->themes->loadModules($core->blog->themes_path,null);
$T = $core->themes->getModules($theme);

// Get smilies code
$o = new smiliesEditor($core);
$smilies = $o->getSmilies();

// Try to create the subdirectory smilies
if (!empty($_POST['create_dir']) )
{
	try {
		$o->createDir();
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
		
	if (!$core->error->flag()) {
		http::redirect($p_url.'&creadir=1');
	}
}

// Init the filemanager
try 
{
	$smilies_files = $o->getFiles();
	$smg_writable = $o->filemanager->writable();
}
catch (Exception $e) {
	$warning = '<p class="form-note warn">'.$e->getMessage().'</p>';
}

if (!empty($_POST['saveconfig']))
{
	try
	{
		$show = (empty($_POST['smilies_bar_flag']))?false:true;
		$preview = (empty($_POST['smilies_preview_flag']))?false:true;
		$formtext = (empty($_POST['smilies_public_text']))? __('Smilies') : $_POST['smilies_public_text'];

		$s->put('smilies_bar_flag',$show,'boolean','Show smilies toolbar');
		$s->put('smilies_preview_flag',$preview,'boolean','Show smilies on preview');
		$s->put('smilies_public_text',$formtext,'string','Smilies displayed in toolbar');
		
		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1');

	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

// Create array of used smilies filename
$smileys_list = array();
foreach ($smilies as $k => $v) {
	$smileys_list= array_merge($smileys_list, array($v['name']=> $v['name']));
}

// Delete all unused images
if (!empty($_POST['rm_unused_img']) )
{
	if (!empty($o->images_list))
	{
		foreach ($o->images_list as $k => $v) 
		{ 
			if (!array_key_exists($v['name'],$smileys_list))
			{ 
				try {
					$o->filemanager->removeItem($v['name']);
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			}
		}
	}
			
	if (!$core->error->flag()) {
		http::redirect($p_url.'&dircleaned=1');
	}
}

if (!empty($_FILES['upfile']))
{
	try
	{
		files::uploadStatus($_FILES['upfile']);
		$file = $o->uploadSmile($_FILES['upfile']['tmp_name'],$_FILES['upfile']['name']);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	if (!$core->error->flag()) {
		if ($file) {
			http::redirect($p_url.'&upok='.$file);
		}
		else {
			http::redirect($p_url.'&upzipok=1');
		}
	}
}

// Create the combo of all images available in directory
if (!empty($o->images_list))
{
	$smileys_combo = array();
	foreach ($o->images_list as $k => $v) {
		$smileys_combo= array_merge($smileys_combo, array($v['name']=> $v['name']));
	}
}

$order = array();
if (empty($_POST['smilies_order']) && !empty($_POST['order'])) {
	$order = $_POST['order'];
	asort($order);
	$order = array_keys($order);
} elseif (!empty($_POST['smilies_order'])) {
	$order = explode(',',$_POST['smilies_order']);
}

if (!empty($_POST['actionsmilies']))
{
	$action = $_POST['actionsmilies'];
	
	if($action == 'clear' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{
			unset ($smilies[$v]);
			
			try {
				$o->setSmilies($smilies);
				$o->setConfig($smilies);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&remove=1');
		}
	} 

	elseif($action == 'update' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{
			$smilies[$v]['code'] = isset($_POST['code'][$v]) ? preg_replace('/[\s]+/','',$_POST['code'][$v]) : $smilies[$v]['code'] ;
			$smilies[$v]['name'] = isset($_POST['name'][$v]) ? $_POST['name'][$v] : $smilies[$v]['name'];
			
			try {
				$o->setSmilies($smilies);
				$o->setConfig($smilies);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&update=1');
		}
		
	} 
	
	elseif($action == 'display' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{ 
			$smilies[$v]['onSmilebar'] = true;
		}
		
		try {
			$o->setConfig($smilies);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());

		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&display=1');
		}
		
	} 
	
	elseif($action == 'hide' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{  
			$smilies[$v]['onSmilebar'] = false;
		}
		
		try {
			$o->setConfig($smilies);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());

		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&hide=1');
		}
		
	} 
}

if (!empty($_POST['saveorder']) && !empty($order))
{
	foreach ($order as $k => $v)
	{ 
		$new_smilies[$v] = $smilies[$v]; 
	}
	
	try {
		$o->setSmilies($new_smilies);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());

	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&neworder=1');
	}
} 

if (!empty($_POST['smilecode']) && !empty($_POST['smilepic']))
{
	$count = count($smilies);
	$smilies[$count]['code'] = preg_replace('/[\s]+/','',$_POST['smilecode']);
	$smilies[$count]['name'] = $_POST['smilepic'];

	try {
		$o->setSmilies($smilies);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
		
	if (!$core->error->flag()) {
		http::redirect($p_url.'&addsmile=1');
	}
}

# Zip download
if (!empty($_GET['zipdl']))
{
	try
	{
		@set_time_limit(300);
		$fp = fopen('php://output','wb');
		$zip = new fileZip($fp);
		//$zip->addExclusion('#(^|/).(.*?)_(m|s|sq|t).jpg$#');
		$zip->addDirectory($core->themes->moduleInfo($theme,'root').'/smilies','',true);
		header('Content-Disposition: attachment;filename=smilies-'.$theme.'.zip');
		header('Content-Type: application/x-zip');
		$zip->write();
		unset($zip);
		exit;
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>

<html>
<head>
  <title><?php echo $page_title; ?></title>
	<?php echo dcPage::jsLoad('index.php?pf=smiliesEditor/js/pic.js'); ?>
	<?php
		$core->auth->user_prefs->addWorkspace('accessibility');
	    	if (!$core->auth->user_prefs->accessibility->nodragdrop) {
	    	echo
	    		dcPage::jsLoad('js/jquery/jquery-ui.custom.js').
	    		dcPage::jsLoad('index.php?pf=smiliesEditor/js/smiliesEditor.js');
	    	}
	?>

	  <script type="text/javascript">
	  //<![CDATA[
	  <?php echo dcPage::jsVar('dotclear.smilies_base_url',$core->blog->host.$o->smilies_base_url);?>
	  dotclear.msg.confirm_image_delete = '<?php echo html::escapeJS(sprintf(__('Are you sure you want to remove these %s ?'),'images')) ?>';
	  $(function() {
	    $('#del_form').submit(function() {
	      return window.confirm(dotclear.msg.confirm_image_delete);
	    });
	  });
	  //]]>
	  </script>

	<style type="text/css">
		option[selected=selected] {color:#c00;}
		select {background-color:#FFF !important;}
		a.add {background:inherit url(images/plus.png) top left;}
		img.smiley {vertical-align : middle;}
		tr.line select {width:15em;}
		/*tr.offline {background-color : #f4f4ef;}*/
		tr td.smiley { text-align:center}
          #smilepic,#smilepic option,select.emote, select.emote option {background-color:transparent;
               background-repeat:no-repeat;background-position:4% 50%;
               padding:1px 1px 1px 30px;color:#444;height:26px;}
		option[selected=selected] {background-color:#E2DFCA !important;}
	</style>
</head>

<body>

<?php

echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));

if (!empty($_GET['config'])) {
  	dcPage::success(__('Configuration successfully updated.'));
	}
if (!empty($_GET['creadir'])) {
  	dcPage::success(__('The subfolder has been successfully created.'));
	}
if (!empty($_GET['dircleaned'])) {
  	dcPage::success(__('Unused images have been successfully removed.'));
	}
if (!empty($_GET['upok'])) {
		$msg = '<p class="success">'. sprintf(__('The image <em>%s</em> has been successfully uploaded.'),$_GET['upok']).'</p>';
}
if (!empty($_GET['upzipok'])) {
  	dcPage::success(__('A smilies zip package has been successfully installed.'));
	}
if (!empty($_GET['remove'])) {
  	dcPage::success(__('Smilies has been successfully removed.'));
	}
if (!empty($_GET['update'])) {
  	dcPage::success(__('Smilies has been successfully updated.'));
	}
if (!empty($_GET['neworder'])) {
  	dcPage::success(__('Order of smilies has been successfully changed.'));
	}
if (!empty($_GET['hide'])) {
  	dcPage::success(__('These selected smilies are now hidden on toolbar.'));
	}
if (!empty($_GET['display'])) {
  	dcPage::success(__('These selected smilies are now displayed on toolbar.'));
	}
if (!empty($_GET['addsmile'])) {
  	dcPage::success(__('A new smiley has been successfully created.'));
	}

if (!empty($o->images_list))
{
	$images_all = $o->images_list;
	foreach ($o->images_list as $k => $v)
	{
		if (array_key_exists($v['name'],$smileys_list))
			{
				unset ($o->images_list[$k]);
			}
	}
}
if ($msg) {echo $msg;}

echo
'<p>'.sprintf(__('Your <a href="blog_theme.php">current theme</a> on this blog is "%s".'),'<strong>'.html::escapeHTML($T['name']).'</strong>').'</p>';

if ($warning) {echo $warning;}

if (empty($smilies))
{
	if (!empty($o->filemanager))
	{
		echo '<br /><p class="form-note info ">'.__('No defined smiley yet.').'</p><br />';
	}
}
else
{
	echo
	'<div class="clear" id="smilies_options">'.
	'<form action="plugin.php" method="post" id="form_smilies_options">'.
			'<h3>'.__('Configuration').'</h3>'.
				'<div class="two-cols">'.
					'<p class="col">'.
						form::checkbox('smilies_bar_flag', '1', $smilies_bar_flag).
						'<label class="classic" for="smilies_bar_flag">'.__('Show toolbar smilies in comments form').'</label>'.
					'</p>'.
					'<p class="col">'.
						form::checkbox('smilies_preview_flag', '1', $smilies_preview_flag).
						'<label class=" classic" for="smilies_preview_flag">'.__('Show images on preview').'</label>'.
					'</p>'.
					
					'<p class="clear">'.
						'<label class="required classic" for="smilies_preview_flag">'.__('Comments form label:').'</label>&nbsp;&nbsp;'.
						form::field('smilies_public_text', 50,255,html::escapeHTML($smilies_public_text)).
					'</p>'.
					'<br /><p class="clear form-note">'.
						sprintf(__('Don\'t forget to <a href="%s">display smilies</a> on your blog configuration.'),'blog_pref.php').
					'</p>'.
					'<p class="clear">'.
						form::hidden(array('p'),'smiliesEditor').
						$core->formNonce().
						'<input type="submit" name="saveconfig" value="'.__('Save').'" />'.
					'</p>'.
				'</div>'.
	'</form></div>';

	$colspan = ($core->auth->isSuperAdmin() && $theme !='default') ? 3 : 2;
	echo
		'<form action="'.$p_url.'" method="post" id="smilies-form">'.
		'<h3>'.__('Smilies set').'</h3>'.
		'<table class="maximal dragable">'.
		'<thead>'.
		'<tr>'.
		'<th>'.__('Order').'</th>'.
		'<th colspan="2">'.__('Code').'</th>'.
		'<th>'.__('Image').'</th>'.
		//'<noscript><th>'.__('Filename').'</th></noscript>'.
		'</tr>'.
		'</thead>'.
	
	'<tbody id="smilies-list">';
	foreach ($smilies as $k => $v)
	{
		if($v['onSmilebar']) {
			$line = '';
			$status = '<img alt="'.__('displayed').'" title="'.__('displayed').'" src="images/check-on.png" />';
		}
		else
		{
			$line = 'offline';
			$status = '<img alt="'.__('undisplayed').'" title="'.__('undisplayed').'" src="images/check-wrn.png" />';
		}
		$disabled = ($core->auth->isSuperAdmin() && $theme !='default') ? false : true;
		echo
		'<tr class="line '.$line.'" id="l_'.($k).'">';
		if ($core->auth->isSuperAdmin() && $theme !='default') {echo  '<td class="handle minimal">'.form::field(array('order['.$k.']'),2,5,$k,'position','',false,'title="'.
		__('position').'"').'</td>' ;}
		echo
		'<td class="minimal status">'.form::checkbox(array('select[]'),$k).'</td>'.
		'<td class="minimal">'.form::field(array('code[]','c'.$k),20,255,html::escapeHTML($v['code']),'','',$disabled).'</td>'.
		//'<noscript><td class="minimal smiley"><img src="'.$core->blog->host.$o->smilies_base_url.$v['name'].'" alt="'.$v['code'].'" /></td></noscript>'.
		'<td class="nowrap status">'.form::combo(array('name[]','n'.$k),$smileys_combo,$v['name'],'emote','',$disabled).$status.'</td>'.
		'</tr>';
	}
	
	
	echo '</tbody></table>';
	
	echo '<div class="two-cols">
		<p class="col checkboxes-helpers"></p>';
	
	echo	'<p class="col right">'.__('Selected smilies action:').' '.
		form::hidden('smilies_order','').
		form::hidden(array('p'),'smiliesEditor').
		form::combo('actionsmilies',$combo_action).
		$core->formNonce().
		'<input type="submit" value="'.__('Ok').'" /></p>';
		
	if (($core->auth->isSuperAdmin() && $theme !='default')) { 
	echo '<p><input type="submit" name="saveorder" 
		value="'.__('Save order').'" 
		/></p>'; }
			
	echo '</div></form>';
	
}

echo '<br /><br /><div class="three-cols">';

if (empty($images_all))
{
	if (empty($o->filemanager))
	{
		echo '<div class="col"><form action="'.$p_url.'" method="post" id="dir_form"><p>'.form::hidden(array('p'),'smiliesEditor').
		$core->formNonce().
		'<input type="submit" name="create_dir" value="'.__('Initialize').'" /></p></form></div>';
	}
}
else
{
	if ($core->auth->isSuperAdmin() && $theme !='default')
	{
		$val = array_values($images_all);
		$preview_smiley = '<img class="smiley" src="'.$core->blog->host.$val[0]['url'].'" alt="'.$val[0]['name'].'" title="'.$val[0]['name'].'" id="smiley-preview" />';

		echo
			'<div class="col">'.
			'<form action="'.$p_url.'" method="post" id="add-smiley-form">'.
			'<h3>'.__('New smiley').'</h3>'.
			'<p><label for="smilepic" class="classic required">
			<abbr title="'.__('Required field').'">*</abbr>
			'.__('Image:').' '.
			form::combo('smilepic',$smileys_combo).'</label></p>'.

			'<p><label for="smilecode" class="classic required">
			<abbr title="'.__('Required field').'">*</abbr>
			'.__('Code:').' '.
			form::field('smilecode',20,255).'</label>'.

			form::hidden(array('p'),'smiliesEditor').
			$core->formNonce().
			'&nbsp; <input type="submit" name="add_message" value="'.__('Create').'" /></p>'.
			'</form></div>';
	}
}


if ($smg_writable && $core->auth->isSuperAdmin() && $theme !='default')
{
	echo
	'<div class="col"><form id="upl-smile-form" action="'.html::escapeURL($p_url).'" method="post" enctype="multipart/form-data">'.
	'<h3>'.__('New image').'</h3>'.
	'<p>'.form::hidden(array('MAX_FILE_SIZE'),DC_MAX_UPLOAD_SIZE).
	$core->formNonce().
	'<label>'.__('Choose a file:').
	' ('.sprintf(__('Maximum size %s'),files::size(DC_MAX_UPLOAD_SIZE)).')'.
	'<input type="file" name="upfile" size="20" />'.
	'</label></p>'.
	'<p><input type="submit" value="'.__('Send').'" />'.
	form::hidden(array('d'),null).'</p>'.
	//'<p class="form-note">'.__('Please take care to publish media that you own and that are not protected by copyright.').'</p>'.
	'</form></div>';
}

if (!empty($images_all) && $core->auth->isSuperAdmin() && $theme !='default')
{
	if (!empty($o->images_list))
	{
		echo '<div class="col"><form action="'.$p_url.'" method="post" id="del_form">'.
		'<h3>'.__('Unused smilies').'</h3>';
		
		echo '<p>';
		foreach ($o->images_list as $k => $v)
		{
			echo	'<img src="'.$core->blog->host.$v['url'].'" alt="'.$v['name'].'" title="'.$v['name'].'" />&nbsp;';
		}
		echo '</p>';
		
		echo	
		'<p>'.form::hidden(array('p'),'smiliesEditor').
		$core->formNonce().
		'<input type="submit" name="rm_unused_img" 
		value="'.__('Delete').'" 
		/></p></form></div>';
	}
}

echo '</div>';

if (!empty($images_all)) {
	echo  '<p class="zip-dl clear"><a href="'.html::escapeURL($p_url).'&amp;zipdl=1">'. 
		__('Download the smilies directory as a zip file').'</a></p>'; }
dcPage::helpBlock('smilieseditor');
?>
</body>
</html>