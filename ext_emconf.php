<?php

########################################################################
# Extension Manager/Repository config file for ext "powermail_limit".
#
# Auto generated 21-06-2012 16:57
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'powermail limit',
	'description' => 'Offers limitation features for powermail (currently: number of form postings, selectbox limit)',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.5.0',
	'dependencies' => 'powermail',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Jonas Renggli',
	'author_email' => 'jonas.renggli@visol.ch',
	'author_company' => 'visol digitale Dienstleistungen GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'powermail' => '1.5.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:12:"ext_icon.gif";s:4:"39c9";s:17:"ext_localconf.php";s:4:"c0a6";s:14:"ext_tables.php";s:4:"04f5";s:14:"ext_tables.sql";s:4:"5a90";s:13:"locallang.xml";s:4:"e592";s:16:"locallang_db.xml";s:4:"af69";s:14:"doc/manual.sxw";s:4:"1b45";s:19:"doc/wizard_form.dat";s:4:"32ee";s:20:"doc/wizard_form.html";s:4:"a66b";s:33:"lang/locallang_csh_tt_content.php";s:4:"6ab9";s:33:"lib/def/def_field_selectlimit.xml";s:4:"e289";s:34:"lib/script/multifile_compressed.js";s:4:"2815";s:35:"pi1/class.tx_powermaillimit_pi1.php";s:4:"8307";s:17:"pi1/locallang.xml";s:4:"35e1";s:41:"template/tx_powermaillimit_fieldwrap.html";s:4:"b2e5";}',
	'suggests' => array(
	),
);

?>