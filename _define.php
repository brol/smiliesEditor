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

$this->registerModule(
	/* Name */			"smiliesEditor",
	/* Description*/		"Smilies Editor",
	/* Author */			"Osku and contributors",
	/* Version */			'0.7',
	/* Properties */
	array(
		'permissions' => 'contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.11',
		'support' => 'http://forum.dotclear.org/viewtopic.php?id=40929',
		'details' => 'http://plugins.dotaddict.org/dc2/details/smiliesEditor'
		)
);