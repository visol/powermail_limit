<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Mischa Hei�mann <typo3.2008@heissmann.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('powermail') . 'lib/class.tx_powermail_sessions.php'); // load session class


/**
 * Plugin 'powermail multiple upload' for the 'powermail_limit' extension.
 *
 * @author    Jonas Renggli <jonas.renggli@visol.ch>
 * @author  Lorenz Ulrich <lorenz.ulrich@visol.ch>
 * @package    TYPO3
 * @subpackage    tx_powermaillimit
 */
class tx_powermaillimit_pi1 extends tslib_pibase {
	var $prefixId = 'tx_powermail_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_powermaillimit_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'powermail_limit'; // The extension key.
	var $pi_checkCHash = TRUE;

	/** @var array */
	public $xml;

	/** @var tx_powermail_functions_div */
	public $div;

	/** @var tslib_cObj */
	public $cObj;

	/** @var tx_powermail_dynamicmarkers */
	public $dynamicMarkers;

	/** @var tx_powermail_html */
	public $obj;

	public $class_f;
	public $tmpl;
	public $uid;
	public $additionalCssToInputField;
	public $formtitle;
	public $type;
	public $piVarsFromSession;
	public $turnedtabindex;
	public $markerArray;
	public $newaccesskey;

	/**
	 * Use the FieldHook to add two additional field types selectlimit and checklimit
	 * The FieldHook is executed before rendering a field
	 *
	 * @param $xml
	 * @param $title
	 * @param $type
	 * @param $uid
	 * @param $markerArray
	 * @param $piVarsFromSession
	 * @param $obj
	 * @return string
	 */
	public function PM_FieldHook($xml, $title, $type, $uid, $markerArray, $piVarsFromSession, $obj) {
		$this->conf = $obj->conf;
		$this->content = $obj->content;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// initialize objects
		$this->div = t3lib_div::makeInstance('tx_powermail_functions_div');
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_powermail_dynamicmarkers'); // New object: TYPO3 marker function
		$this->obj = $obj;

		// values from parent object
		$this->uid = $obj->uid;
		$this->formtitle = $obj->type;
		$this->type = $obj->type;
		$this->class_f = $obj->class_f; // Get css class of current field
		$this->additionalCssToInputField = !!$this->conf['additionalCssToInputField'];
		$this->uid = $uid;
		$this->piVarsFromSession = $piVarsFromSession;
		$this->turnedtabindex = $obj->turnedtabindex;
		$this->markerArray = $markerArray;
		$this->newaccesskey = $obj->newaccesskey;

		// config
		$this->xml = $xml;

		$content = '';
		$this->tmpl = array();
		$this->tmpl['all'] = tslib_cObj::fileResource('EXT:powermail_limit/template/tx_powermaillimit_fieldwrap.html');

		switch ($type) {
			case 'selectlimit':
				$content = $this->html_selectlimit(); // generate selectorbox <select><option>...
				if (!empty($content)) return $content;
				break;
			case 'checklimit':
				$content = $this->html_checklimit(); // generate selectorbox <select><option>...
				if (!empty($content)) return $content;
				break;
		}

	}

	/**
	 * Using the MandatoryHookBefore to check if there is a limit error
	 * This hook is executed before the mandatory check after submitting a form
	 *
	 * @param $error
	 * @param $markerArray
	 * @param $sessionfields
	 * @param $obj
	 */
	public function PM_MandatoryHookBefore(&$error, $markerArray, &$sessionfields, $obj) {
		$this->obj = $obj;

		$selectLimit = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'tx_powermaillimit_selectlimit');
		foreach ($sessionfields as $field => $value) {
			if (isset($selectLimit['limits'][$field][$value])) {
				$fieldUid = $selectLimit['limits'][$field][$value]['fieldUid'];
				$option = $selectLimit['limits'][$field][$value]['option'];

				if ($this->isLimitReached($fieldUid, $option)) {
					$error = 1;
					$sessionfields['ERROR'][3][] = $selectLimit['limits'][$field][$value]['option'][0] . ': ' . $selectLimit['limits'][$field][$value]['errorlimit'];
				}
			}
		}

		$checkLimit = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'tx_powermaillimit_checklimit');
		foreach ($sessionfields as $field => $valueArray) {
			foreach ($valueArray as $value) {
				if (isset($checkLimit['limits'][$field][$value])) {
					$fieldUid = $checkLimit['limits'][$field][$value]['fieldUid'];
					$option = $checkLimit['limits'][$field][$value]['option'];

					if ($this->isLimitReached($fieldUid, $option)) {
						$error = 1;
						$sessionfields['ERROR'][3][] = $checkLimit['limits'][$field][$value]['option'][0] . ': ' . $checkLimit['limits'][$field][$value]['errorlimit'];
					}
				}
			}
		}

	}

	/**
	 * Function html_selectlimit() returns HTML tag for select box with limit
	 *
	 * @return    string    $content
	 */
	public function html_selectlimit() {
		$this->tmpl['html_selectlimit']['all'] = $this->cObj->getSubpart($this->tmpl['all'], '###POWERMAIL_FIELDWRAP_HTML_SELECTLIMIT###'); // work on subpart 1
		$this->tmpl['html_selectlimit']['item'] = $this->cObj->getSubpart($this->tmpl['html_selectlimit']['all'], '###ITEM###'); // work on subpart 2

		$optionlimit = $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'optionlimit');
		$errorlimit = $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'errorlimit');

		if ($this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'options')) { // Only if options are set
			$content_item = '';
			$options = $set = array(); // init
			$optionlines = t3lib_div::trimExplode("\n", $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'options'), 1); // Every row is a new option
			for ($i = 0; $i < count($optionlines); $i++) { // Every loop for every option
				$options[$i] = t3lib_div::trimExplode('|', $optionlines[$i], 0); // Every row is a new option
			}

			// preparing an array for preselection of multi fields
			if ($this->conf['prefill.']['uid' . $this->uid . '.']['selectedIndexes'] || is_array($this->conf['prefill.']['uid' . $this->uid . '.']['selectedIndexes.'])) { // if there are values in the array selectedIndexes.
				$selected = t3lib_div::intExplode(',', $this->cObj->stdWrap($this->conf['prefill.']['uid' . $this->uid . '.']['selectedIndexes'], $this->conf['prefill.']['uid' . $this->uid . '.']['selectedIndexes.']));
			} elseif ($this->conf['prefill.']['uid' . $this->uid . '.']['selectedValues'] || is_array($this->conf['prefill.']['uid' . $this->uid . '.']['selectedValues.'])) {
				$selected = t3lib_div::trimExplode(',', $this->cObj->stdWrap($this->conf['prefill.']['uid' . $this->uid . '.']['selectedValues'], $this->conf['prefill.']['uid' . $this->uid . '.']['selectedValues.']));
			} else {
				$selected = array();
			}


			for ($i = 0; $i < count($optionlines); $i++) { // One tag for every option

				// use lable as value if no separate string in 2nd part 
				$optionValue = $options[$i][1] ? $options[$i][1] : $options[$i][0];
				$markerArray['###LABEL###'] = $options[$i][0]; // fill label marker with label
				$markerArray['###VALUE###'] = $optionValue; // fill value marker with value

				// empty value if label contains "..." or "---" for mandatory check
				$markerArray['###VALUE###'] = preg_match("/\.\.\.|---/", $options[$i][0]) ? '' : $optionValue;

				// ###SELECTED###
				if (!is_array($this->piVarsFromSession['uid' . $this->uid])) { // no multiple
					if ($options[$i][2] == '*') $markerArray['###SELECTED###'] = ' selected="selected"'; // selected from backend
					else $markerArray['###SELECTED###'] = ''; // clear
					if (isset($this->piVarsFromSession['uid' . $this->uid])) { // if session was set
						if ($this->piVarsFromSession['uid' . $this->uid] == ($options[$i][1] ? $options[$i][1] : $options[$i][0])) $markerArray['###SELECTED###'] = 'selected="selected" '; // mark as selected
						else $markerArray['###SELECTED###'] = ''; // clear
					}
				} else { // multiple
					for ($j = 0; $j < count($this->piVarsFromSession['uid' . $this->uid]); $j++) {
						if ($this->piVarsFromSession['uid' . $this->uid][$j] == ($options[$i][1] ? $options[$i][1] : $options[$i][0])) {
							$markerArray['###SELECTED###'] = ' selected="selected"'; // mark as selected
							$set[$i] = 1;
						}
					}
					if (!$set[$i]) $markerArray['###SELECTED###'] = ''; // clear
				}

				// Preselection from typoscript
				if (!$set[$i] && !empty($this->conf['prefill.'])) {
					if ($this->obj->isPrefilled($i, $selected, ($options[$i][1] ? $options[$i][1] : $options[$i][0])) != FALSE) {
						$markerArray['###SELECTED###'] = ' selected="selected"'; // mark as selected
					} else {
						$markerArray['###SELECTED###'] = ''; // clear
					}
				}

				// check if limit is already reached
				if ($this->isLimitReached($this->uid, $options[$i])) {
					if ($optionlimit) {
						$markerArray['###LABEL###'] .= $optionlimit;
						$markerArray['###SELECTED###'] .= ' disabled="disabled"';
					} else {
						// skip option if no message defined
						continue;
					}
				}

				// Store values in session (easier usage in PM_MandatoryHookBefore)
				if (isset($options[$i][3])) { // limit set
					$value = $options[$i][1] ? $options[$i][1] : $options[$i][0];

					$session = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'tx_powermaillimit_selectlimit');

					$session['limits']['uid' . $this->uid][$value] = Array(
						'fieldUid' => $this->uid,
						'option' => $options[$i],
						'errorlimit' => $errorlimit
					);

					$GLOBALS["TSFE"]->fe_user->setKey('ses', 'tx_powermaillimit_selectlimit', $session);
					$GLOBALS["TSFE"]->storeSessionData();
				}

				//$this->obj->html_hookwithinfieldsinner($markerArray); // adds hook to manipulate the markerArray for any field
				$content_item .= $this->obj->cObj->substituteMarkerArrayCached($this->tmpl['html_selectlimit']['item'], $markerArray);
			}
		}
		$subpartArray['###CONTENT###'] = $content_item; // subpart 3
		if ($this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'multiple')) $this->markerArray['###NAME###'] = 'name="' . $this->prefixId . '[uid' . $this->uid . '][]" '; // overwrite name to markerArray like tx_powermail_pi1[55][]

		//$this->obj->html_hookwithinfields(); // adds hook to manipulate the markerArray for any field
		$content = $this->obj->cObj->substituteMarkerArrayCached($this->tmpl['html_selectlimit']['all'], $this->obj->markerArray, $subpartArray); // substitute Marker in Template
		$content = $this->obj->dynamicMarkers->main($this->obj->conf, $this->obj->cObj, $content); // Fill dynamic locallang or typoscript markers
		$content = preg_replace('|###.*?###|i', '', $content); // Finally clear not filled markers

		return $content; // return HTML
	}

	/**
	 * Function html_checklimit() returns HTML tag for checkboxes with limit
	 *
	 * @return	string	$content
	 */
	public function html_checklimit() {
		$this->tmpl['html_check']['all'] = $this->cObj->getSubpart($this->tmpl['all'], '###POWERMAIL_FIELDWRAP_HTML_CHECKLIMIT###');
		$this->tmpl['html_check']['item'] = $this->cObj->getSubpart($this->tmpl['html_check']['all'], '###ITEM###');

		$optionlimit = $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'optionlimit');
		$errorlimit = $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'errorlimit');

		if ($this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'options')) { // Only if options are set
			$content_item = ''; $options = array(); // init
			$optionlines = t3lib_div::trimExplode("\n", $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'options'), 1); // Every row is a new option
			for ($i=0; $i < count($optionlines); $i++) { // One tag for every option
				$options[$i] = t3lib_div::trimExplode('|', $optionlines[$i], 0); // Every row is a new option
				$markerArray['###NAME###'] = 'name="' . $this->prefixId . '[uid' . $this->uid . '][' . $i . ']" '; // add name to markerArray
				$markerArray['###LABEL###'] = $this->div->parseFunc($options[$i][0], $this->cObj, $this->conf['label.']['parse']);
				$markerArray['###LABEL###'] = ($this->conf['label.']['parse']) ? $markerArray['###LABEL###'] : htmlspecialchars($markerArray['###LABEL###']);
				$markerArray['###LABEL_NAME###'] = 'uid' . $this->uid . '_' . $i; // add labelname
				$markerArray['###ID###'] = 'id="uid' . $this->uid . '_' . $i . '" '; // add labelname
				//$markerArray['###VALUE###'] = 'value="' . (isset($options[$i][1]) ? htmlspecialchars($options[$i][1]) : htmlspecialchars($options[$i][0])) . '" ';
				$markerArray['###VALUE###'] = 'value="' . (!empty($options[$i][1]) ? $options[$i][1] : $options[$i][0]) . '" ';
				$markerArray['###CLASS###'] = 'class="'; // start class tag

				// Add required class if needed
				if($i == 0 && $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'mandatory') == 1){
					if(count($optionlines) > 1) {
						$markerArray['###CLASS###'] .= 'required_one  ';
					} else {
						$markerArray['###CLASS###'] .= 'required  ';
						$markerArray['###REQUIRED###'] = ' required="required"';
					}
				}

				$markerArray['###CLASS###'] .= 'powermail_' . $this->formtitle; // add form title
				$markerArray['###CLASS###'] .= ' powermail_' . $this->type; // add input type
				$markerArray['###CLASS###'] .= ' powermail_uid' . $this->uid; // add input uid
				$markerArray['###CLASS###'] .= ' powermail_subuid' . $this->uid . '_' . $i; // add input subuid
				$markerArray['###CLASS###'] .= ($this->class_f != '' && $this->additionalCssToInputField) ? ' ' . htmlspecialchars($this->class_f) : ''; // add manual class
				$markerArray['###CLASS###'] .= '" '; // close tag
				$markerArray['###HIDDENVALUE###'] = 'value="' . htmlspecialchars($this->piVarsFromSession['uid' . $this->uid][$i]) . '"'; // add value for hidden field to markerArray
				if ($this->pi_getFFvalue(t3lib_div::xml2array($this->xml),'mandatory') == 1) {
					$markerArray['###MANDATORY_SYMBOL###'] = $this->cObj->wrap($this->conf['mandatory.']['symbol'], $this->conf['mandatory.']['wrap'],'|'); // add mandatory symbol if current field is a mandatory field
				}
				$this->turnedtabindex[$this->uid . '_' . $i] !== '' ? $markerArray['###TABINDEX###'] = 'tabindex="' . ($this->turnedtabindex[$this->uid . '_' . $i] + 1) . '" ' : $markerArray['###TABINDEX###'] = ''; // tabindex for every checkbox
				isset($this->newaccesskey[$this->uid][$i]) ? $markerArray['###ACCESSKEY###'] = 'accesskey="' . $this->newaccesskey[$this->uid][$i] . '" ' : $markerArray['###ACCESSKEY###'] = ''; // accesskey for every checkbox

				// ###CHECKED###
				if ($options[$i][2] == '*')  {

					$markerArray['###CHECKED###'] = 'checked="checked" '; // checked from backend
					$markerArray['###HIDDENVALUE###'] = 'value="' . (isset($options[$i][1]) ? $options[$i][1] : $options[$i][0]) . '" ';

				} elseif (!empty($this->conf['prefill.']['uid' . $this->uid . '_' . $i])) { // prechecking with typoscript for current field enabled

					if ($this->cObj->cObjGetSingle($this->conf['prefill.']['uid' . $this->uid . '_' . $i], $this->conf['prefill.']['uid' . $this->uid . '_' . $i . '.']) == 1) {
						$markerArray['###CHECKED###'] = 'checked="checked" '; // checked from backend
						$markerArray['###HIDDENVALUE###'] = 'value="' . (isset($options[$i][1]) ? $options[$i][1] : $options[$i][0]) . '" ';
					} else $markerArray['###CHECKED###'] = ''; // clear

				}
				// AST end
				else $markerArray['###CHECKED###'] = ''; // clear
				if (isset($this->piVarsFromSession['uid' . $this->uid])) { // Preselection from session
					if (isset($this->piVarsFromSession['uid' . $this->uid][$i]) && $this->piVarsFromSession['uid' . $this->uid][$i] != '') {
						$markerArray['###CHECKED###'] = 'checked="checked" '; // mark as checked
						$markerArray['###HIDDENVALUE###'] = 'value="' . htmlspecialchars($this->piVarsFromSession['uid' . $this->uid][$i]) . '"'; // add value for hidden field to markerArray
					}

					else $markerArray['###CHECKED###'] = ''; // clear
				}

				// check if limit is already reached
				if ($this->isLimitReached($this->uid, $options[$i])) {
					if ($optionlimit) {
						$markerArray['###LABEL###'] .= $optionlimit;
						$markerArray['###CHECKED###'] .= ' disabled="disabled"';
					} else {
						// skip option if no message defined
						continue;
					}
				}

				// Store values in session (easier usage in PM_MandatoryHookBefore)
				if (isset($options[$i][3])) { // limit set
					$value = $options[$i][1] ? $options[$i][1] : $options[$i][0];

					$session = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'tx_powermaillimit_checklimit');

					$session['limits']['uid' . $this->uid][$value] = Array(
						'fieldUid' => $this->uid,
						'option' => $options[$i],
						'errorlimit' => $errorlimit
					);

					$GLOBALS["TSFE"]->fe_user->setKey('ses', 'tx_powermaillimit_checklimit', $session);
					$GLOBALS["TSFE"]->storeSessionData();
				}

				$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['html_check']['item'], $markerArray); // substitute Marker in Template (subpart 2)
			}

		}
		$subpartArray = array(); // init
		$subpartArray['###CONTENT###'] = $content_item; // subpart 3

		// Outer Marker array
		$this->markerArray['###LABEL_MAIN###'] = htmlspecialchars($this->title);
		$this->markerArray['###POWERMAIL_FIELD_UID###'] = $this->uid;

		$content = $this->cObj->substituteMarkerArrayCached($this->tmpl['html_check']['all'], $this->markerArray, $subpartArray); // substitute Marker in Template
		$content = $this->dynamicMarkers->main($this->conf, $this->cObj, $content); // Fill dynamic locallang or typoscript markers
		$content = preg_replace('|###.*?###|i', '', $content); // Finally clear not filled markers
		return $content; // return HTML
	}

	/**
	 * Check if the maximum number of selections of a select option is already reached
	 *
	 * @param $fieldUid
	 * @param $option
	 * @return bool
	 */
	public function isLimitReached($fieldUid, $option) {
		if (!isset($option[3])) { // no limit set
			return FALSE;
		}

		$value = $option[1] ? $option[1] : $option[0];
		$limit = intval($option[3]);
		$count = $this->countStoredMailField($fieldUid, strip_tags($value));

		$this->session['limits']['uid' . $fieldUid][$value] = Array('fieldUid' => $fieldUid, 'option' => $option);

		return ($count >= $limit);
	}

	/**
	 * Count the number of mails having a value set for a field id
	 *
	 * @param $fieldUid
	 * @param $value
	 * @return int
	 */
	public function countStoredMailField($fieldUid, $value) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'piVars',
			'tx_powermail_mails',
			$where_clause = 'pid = ' . $this->getSavePid() . ' AND hidden = 0 AND deleted = 0',
			$groupBy = '',
			$orderBy = '',
			$limit = ''
		);

		$count = 0;
		if ($res) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$xml = t3lib_div::xml2array($row['piVars']);
				$xml_value = $xml['uid' . $fieldUid];
				if (is_array($xml_value)) {
					foreach ($xml_value as $singleValue) {
						if ($singleValue == $value) {
							$count++;
						}
					}
				} else {
					if ($xml_value == $value) {
						$count++;
					}
				}
			}
		}
		return $count;
	}

	/**
	 * Use FormWrapMarkerHook to display a message if the number of posts for the (whole) form was reached
	 *
	 * @param $outerMarkerArray
	 * @param $subpartArray
	 * @param $conf
	 * @param $obj
	 */
	public function PM_FormWrapMarkerHook(&$outerMarkerArray, &$subpartArray, &$conf, &$obj) {
		if ($this->isPostLimitExceeded($obj)) {
			$errorMessage = $obj->cObj->data['tx_powermaillimit_postlimit_error'];
			$subpartArray['###POWERMAIL_CONTENT###'] = $obj->pi_RTEcssText($errorMessage);
			return;
		}
	}

	/**
	 * Use SubmitBeforeMarkerHook to count before submitting a form (in case another user made the form reach the limit in the meantime)
	 *
	 * @param $obj
	 * @param $markerArray
	 * @param $sessiondata
	 * @return mixed
	 */
	public function PM_SubmitBeforeMarkerHook(&$obj, &$markerArray, &$sessiondata) {
		if ($this->isPostLimitExceeded($obj)) {
			$errorMessage = $obj->cObj->data['tx_powermaillimit_postlimit_error'];
			$renderErrorMessage = $obj->pi_RTEcssText($errorMessage);
			return $renderErrorMessage;
		}
	}

	/**
	 * Check if the number of posts reaches/exceeds the limit
	 *
	 * @param $obj
	 * @return bool
	 */
	public function isPostLimitExceeded(&$obj) {
		$limit = $obj->cObj->data['tx_powermaillimit_postlimit'];
		if (!empty($limit)) {
			$mailCount = $this->countMails();
			if ($mailCount >= $limit) {
				return TRUE;
			}
		}
	}

	/**
	 * Count all mails on the current saving page
	 *
	 * @return mixed
	 */
	public function countMails() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_powermail_mails',
			$where_clause = 'pid = ' . $this->getSavePid() . ' AND hidden = 0 AND deleted = 0',
			$groupBy = '',
			$orderBy = '',
			$limit = ''
		);
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

		return $count;
	}

	/**
	 * Returns the pid for saving mails for the current form
	 *
	 * @return mixed
	 */
	public function getSavePid() {
		$savePID = $GLOBALS['TSFE']->id; // PID where to save: Take current page
		if (intval($this->obj->conf['PID.']['dblog']) > 0) $savePID = $this->obj->conf['PID.']['dblog']; // PID where to save: Get it from TS if set
		if (intval($this->obj->cObj->data['tx_powermail_pages']) > 0) $savePID = $this->obj->cObj->data['tx_powermail_pages']; // PID where to save: Get it from plugin

		return $savePID;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_limit/pi1/class.tx_powermaillimit_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_limit/pi1/class.tx_powermaillimit_pi1.php']);
}

?>