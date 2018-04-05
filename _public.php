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
if (!defined('DC_RC_PATH')) { return; }

$s =& $core->blog->settings->smilieseditor; 

$core->addBehavior('publicFooterContent',array('smiliesBehavior','publicFooterContent'));
$core->addBehavior('publicCommentFormAfterContent',array('smiliesBehavior','publicFormAfterContent'));
$core->addBehavior('publicAnswerFormAfterContent',array('smiliesBehavior','publicFormAfterContent'));
$core->addBehavior('publicEditFormAfter',array('smiliesBehavior','publicFormAfterContent'));
$core->addBehavior('publicEntryFormAfter',array('smiliesBehavior','publicFormAfterContent'));
$core->addBehavior('publicEditEntryFormAfter',array('smiliesBehavior','publicFormAfterContent'));

if ($s->smilies_preview_flag)
{
	$core->addBehavior('publicBeforeCommentPreview',array('smiliesBehavior','publicBeforeCommentPreview'));
	$core->addBehavior('publicBeforePostPreview',array('smiliesBehavior','publicBeforePostPreview'));
	$core->addBehavior('publicBeforeMessagePreview',array('smiliesBehavior','publicBeforeMessagePreview'));
}

class smiliesBehavior
{
	public static function publicFooterContent()
	{
		global $core;
		
		$use_smilies = (boolean) $core->blog->settings->system->use_smilies; 
		$smilies_bar_flag = (boolean) $core->blog->settings->smilieseditor->smilies_bar_flag;
		
		if ($smilies_bar_flag  && $use_smilies) {
			$js = html::stripHostURL($core->blog->getQmarkURL().'pf=smiliesEditor/js/smile.js');
			echo "\n".'<script type="text/javascript" src="'.$js.'"></script>'."\n";
		}
		else {
			return;
		}
	}
	
	public static function publicFormAfterContent()
	{
		global $core;
		
		$use_smilies = (boolean) $core->blog->settings->system->use_smilies; 
		$smilies_bar_flag = (boolean) $core->blog->settings->smilieseditor->smilies_bar_flag;
		$public_text = $core->blog->settings->smilieseditor->smilies_public_text;

		if (!$smilies_bar_flag || !$use_smilies) {
			return;
		}
		
		
		$sE = new smiliesEditor($core);
		$smilies = $sE->getSmilies();
		$field = '<p class="field smilies"><label>'.html::escapeHTML($public_text).'&nbsp;:</label><span>%s</span></p>';
		
		$res = '';
		foreach ($smilies as $smiley) {
			if ($smiley['onSmilebar']) {
				$res .= ' <img class="smiley" src="'.$sE->smilies_base_url.$smiley['name'].'" alt="'.
				html::escapeHTML($smiley['code']).'" title="'.html::escapeHTML($smiley['code']).'" onclick="javascript:InsertSmiley(\'c_content\', \''.
				html::escapeHTML($smiley['code']).' \');" style="cursor:pointer;" />';
			}
		}
		
		if ($res != '')
		{
			echo sprintf($field,$res);
		}
		
	}
	
	public static function publicBeforeCommentPreview()
	{
		$GLOBALS['__smilies'] = context::getSmilies($GLOBALS['core']->blog);
		$GLOBALS['_ctx']->comment_preview['content'] = 
			context::addSmilies($GLOBALS['_ctx']->comment_preview['content']);
	}
	
	public static function publicBeforePostPreview()
	{
		$GLOBALS['__smilies'] = context::getSmilies($GLOBALS['core']->blog);
		$GLOBALS['_ctx']->post_preview['content'] = 
			context::addSmilies($GLOBALS['_ctx']->post_preview['content']);
		$GLOBALS['_ctx']->post_preview['excerpt'] = 
			context::addSmilies($GLOBALS['_ctx']->post_preview['excerpt']);
	}
	
	public static function publicBeforeMessagePreview()
	{
		$GLOBALS['__smilies'] = context::getSmilies($GLOBALS['core']->blog);
		$GLOBALS['_ctx']->message_preview['content'] = 
			context::addSmilies($GLOBALS['_ctx']->message_preview['content']);
	}
}