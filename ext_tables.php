<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA("tx_powermail_fields");
$TCA["tx_powermail_fields"]["columns"]["formtype"]["config"]["items"][] = array('Select box with limit', 'selectlimit');
$TCA["tx_powermail_fields"]["columns"]["flexform"]["config"]["ds"]["selectlimit"] = 'FILE:EXT:powermail_limit/lib/def/def_field_selectlimit.xml';


$tempColumns = array (
	'tx_powermaillimit_postlimit' => array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail_limit/locallang_db.xml:tt_content.tx_powermaillimit_postlimit',		
		'config' => array (
			'type'     => 'input',
			'size'     => '4',
			'max'      => '4',
			'eval'     => 'int',
			'checkbox' => '0',
			'range'    => array (
				'upper' => '1000000',
				'lower' => '1'
			),
			'default' => 0
		)
	),
	'tx_powermaillimit_postlimit_error' => array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail_limit/locallang_db.xml:tt_content.tx_powermaillimit_postlimit_error',		
		'config' => array (
			'type' => 'text',
			'cols' => '30',
			'rows' => '5',
		)
	),
);


t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);
$TCA['tt_content']['types']['powermail_pi1']['showitem'] .= ',--div--;LLL:EXT:powermail_limit/locallang_db.xml:tx_powermail_forms.limittab,tx_powermaillimit_postlimit;;;;1-1-1, tx_powermaillimit_postlimit_error;;;richtext[]:rte_transform[mode=ts_css|imgpath=uploads/tx_powermaillimit/rte/]';

t3lib_extMgm::addLLrefForTCAdescr('tt_content','EXT:powermail_limit/lang/locallang_csh_tt_content.php');


?>