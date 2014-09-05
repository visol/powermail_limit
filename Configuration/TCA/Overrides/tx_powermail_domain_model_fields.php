 <?php
$tca = array(
	'columns' => array(
		'type' => array(
			'config' => array(
				'type' => 'select',
				'items' => array(
					80 => array(
						'LLL:EXT:powermail_limit/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_fields.type.limitspacer',
						'--div--'
					),
					81 => array(
						'LLL:EXT:powermail_limit/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_fields.type.81',
						'selectlimit'
					),
					82 => array(
						'LLL:EXT:powermail_limit/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_fields.type.82',
						'checklimit'
					),
				),
			),
		),
		'settings' => array(
			'displayCond' => 'FIELD:type:IN:select,check,radio,selectlimit,checklimit'
		),
		'mandatory' => array(
			'displayCond' => 'FIELD:type:IN:input,textarea,select,check,radio,date,password,selectlimit,checklimit'
		),
		'css' => array(
			'displayCond' => 'FIELD:type:IN:input,textarea,select,check,radio,submit,password,file,location,text,date,country,selectlimit,checklimit'
		),
// TODO multiselect needs additional checks
//		'multiselect' => array(
//			'displayCond' => 'FIELD:type:IN:select,file,selectlimit'
//		),
	),
);

$GLOBALS['TCA']['tx_powermail_domain_model_fields'] = array_replace_recursive($GLOBALS['TCA']['tx_powermail_domain_model_fields'], $tca);
