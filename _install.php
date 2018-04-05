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
if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$new_version = $core->plugins->moduleInfo('smiliesEditor','version');
$old_version = $core->getVersion('smiliesEditor');
 
if (version_compare($old_version,$new_version,'>=')) {
	return;
}

$core->blog->settings->addNamespace('smilieseditor');
$s =& $core->blog->settings->smilieseditor; 
// New settings
$s->put('smilies_bar_flag',false,'boolean','Show smilies toolbar',true,true);
$s->put('smilies_preview_flag',false,'boolean','Show smilies on preview',true,true);
$s->put('smilies_toolbar','','string','Smilies displayed in toolbar',true,true);
$s->put('smilies_public_text',__('Smilies'),'string','Smilies displayed in toolbar',true,true);

$core->setVersion('smiliesEditor',$new_version);
return true;