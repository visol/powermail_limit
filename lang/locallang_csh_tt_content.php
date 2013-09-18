<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$LOCAL_LANG = Array(
	'default' => Array(
		'tx_powermaillimit_postlimit.description' => 'Limit the number of times a form can be posted.',
		'tx_powermaillimit_postlimit.details' => 'If this field is not set, there is no limit.
			If a positive integer (1-1000000) is set, the form is no longer displayed and instead an error message is displayed.',
		'tx_powermaillimit_postlimit_error.description' => 'Error message displayed when post limit is reached.',
		'tx_powermaillimit_postlimit_error.details' => 'This content is being displayed when the post limit is reached.',
	),
	'de' => Array(
		'tx_powermaillimit_postlimit.description' => 'Limitiert die Anzahl Postings des Formulars.',
		'tx_powermaillimit_postlimit.details' => 'Wenn dieses Feld nicht gesetzt ist, gibt es kein Posting-Limit.
			When eine positive Zahl (1-1000000) gesetzt ist, wird das Formular nicht mehr angezeigt, stattdessen wird eine Fehlermeldung angezeigt.',
		'tx_powermaillimit_postlimit_error.description' => 'Fehlermeldung beim Erreichen des Posting-Limits.',
		'tx_powermaillimit_postlimit_error.details' => 'Dieser Inhalt wird angezeigt, wenn das Posting-Limit erreicht wurde.',
	),
);
?>