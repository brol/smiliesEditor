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

$_menu['Blog']->addItem(__('Smilies Editor'),
		'plugin.php?p=smiliesEditor','index.php?pf=smiliesEditor/icon.png',
		preg_match('/plugin.php\?p=smiliesEditor(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));

//$core->addBehavior('adminCurrentThemeDetails','smilies_editor_details');
$core->addBehavior('adminPreferencesForm',array('smiliesEditorAdminBehaviors','adminUserForm'));
$core->addBehavior('adminUserForm',array('smiliesEditorAdminBehaviors','adminUserForm'));
$core->addBehavior('adminBeforeUserCreate',array('smiliesEditorAdminBehaviors','setSmiliesDisplay'));
$core->addBehavior('adminBeforeUserOptionsUpdate',array('smiliesEditorAdminBehaviors','setSmiliesDisplay'));

function smilies_editor_details($core,$id)
{
	if ($core->auth->isSuperAdmin()) {
		return '<p><a href="plugin.php?p=smiliesEditor" class="button">'.__('Smilies Editor').'</a></p>';
	}
}

if ($core->auth->getOption('smilies_editor_admin')) {
	$core->addBehavior('adminPostHeaders',array('smiliesEditorAdminBehaviors','adminPostHeaders'));
	$core->addBehavior('adminPageHeaders',array('smiliesEditorAdminBehaviors','adminPostHeaders'));
	$core->addBehavior('adminRelatedHeaders',array('smiliesEditorAdminBehaviors','adminPostHeaders'));
	$core->addBehavior('adminDashboardHeaders',array('smiliesEditorAdminBehaviors','adminPostHeaders'));
}

class smiliesEditorAdminBehaviors
{
	public static function adminUserForm($args)
	{
		if ($args instanceof dcCore) {
			$opts = $args->auth->getOptions();
		}
		elseif ($args instanceof record) {
			$opts = $args->options();
		}
		else {
			$opts = array();
		}
		
		
		$value = array_key_exists('smilies_editor_admin',$opts) ? $opts['smilies_editor_admin'] : false;
		
		echo
		'<p><label class="classic">'.
		form::checkbox('smilies_editor_admin','1',$value).__('Display smilies on toolbar').
		'</label></p>';
	}
	
	public static function setSmiliesDisplay($cur,$user_id = null)
	{
		if (!is_null($user_id)) { 
			$cur->user_options['smilies_editor_admin'] = $_POST['smilies_editor_admin'];
		}
	}
	
	public static function adminPostHeaders()
	 {
		global $core;
		$res = '<script type="text/javascript">'."\n".
		"//<![CDATA[\n";
		
		$sE = new smiliesEditor($core);
		$smilies = $sE->getSmilies();
		foreach ($smilies as $id => $smiley) {
			if ($smiley['onSmilebar']) {
				$res .= "jsToolBar.prototype.elements.smilieseditor_s".$id." = {type: 'button', title: '".html::escapeJS($smiley['code'])."', fn:{} }; ".
					//"jsToolBar.prototype.elements.smilieseditor_s".$id.".context = 'post'; ".
					"jsToolBar.prototype.elements.smilieseditor_s".$id.".icon = '".html::escapeJS($core->blog->host.$sE->smilies_base_url.$smiley['name'])."'; ".
					"jsToolBar.prototype.elements.smilieseditor_s".$id.".fn.wiki = function() { this.encloseSelection('".html::escapeJS($smiley['code'])."  ',''); }; ".
					"jsToolBar.prototype.elements.smilieseditor_s".$id.".fn.xhtml = function() { this.encloseSelection('".html::escapeJS($smiley['code'])."  ',''); }; ".
					"jsToolBar.prototype.elements.smilieseditor_s".$id.".fn.wysiwyg = function() {
						smiley = document.createTextNode('".html::escapeJS($smiley['code'])." ');
						this.insertNode(smiley);
					};\n";
				}
			}
		$res .= "//]]></script>\n";
		return $res;
	}
}

$core->addBehavior('adminDashboardFavorites','smiliesEditorDashboardFavorites');

function smiliesEditorDashboardFavorites($core,$favs)
{
	$favs->register('smiliesEditor', array(
		'title' => __('Smilies Editor'),
		'url' => 'plugin.php?p=smiliesEditor',
		'small-icon' => 'index.php?pf=smiliesEditor/icon.png',
		'large-icon' => 'index.php?pf=smiliesEditor/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}