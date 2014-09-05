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
	'description' => 'Offers limitation features for powermail (currently: selectbox and checkbox limit)',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.0.0',
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
	'author' => 'Jonas Renggli / Lorenz Ulrich',
	'author_email' => 'jonas.renggli@visol.ch / lorenz.ulrich@visol.ch',
	'author_company' => 'visol digitale Dienstleistungen GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-6.3.99',
			'powermail' => '2.1.0-3.99.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>