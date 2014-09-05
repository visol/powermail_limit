<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * Include TypoScript
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	$_EXTKEY, 'Configuration/TypoScript',
	'Select Limit and Check Limit Template'
);

$limitFieldColumns = array (
	'option_limit_message' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:powermail_limit/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_fields.option_limit_message',
		'config' => array (
			'type'     => 'input',
		),
		'displayCond' => 'FIELD:type:IN:selectlimit,checklimit'
	),
	'limit_error_message' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:powermail_limit/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_fields.limit_error_message',
		'config' => array (
			'type' => 'text',
			'eval' => 'required',
			'cols' => '30',
			'rows' => '5',
		),
		'displayCond' => 'FIELD:type:IN:selectlimit,checklimit'
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_powermail_domain_model_fields', $limitFieldColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_powermail_domain_model_fields', 'option_limit_message,limit_error_message', '', 'after:settings');

?>