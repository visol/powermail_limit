<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_powermaillimit_pi1.php','_pi1','',1);
t3lib_utility_Debug::debug('hier');
// Hook for using the plugin with powermail (Formwrap)
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FieldHook']['selectlimit'][] = 'EXT:powermail_limit/pi1/class.tx_powermaillimit_pi1.php:tx_powermaillimit_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FieldHook']['checklimit'][] = 'EXT:powermail_limit/pi1/class.tx_powermaillimit_pi1.php:tx_powermaillimit_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_MandatoryHookBefore'][] = 'EXT:powermail_limit/pi1/class.tx_powermaillimit_pi1.php:tx_powermaillimit_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FormWrapMarkerHook'][] = 'EXT:powermail_limit/pi1/class.tx_powermaillimit_pi1.php:tx_powermaillimit_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_SubmitBeforeMarkerHook'][] = 'EXT:powermail_limit/pi1/class.tx_powermaillimit_pi1.php:tx_powermaillimit_pi1';
 
?>