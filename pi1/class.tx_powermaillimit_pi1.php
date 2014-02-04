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
	var $prefixId = 'tx_powermaillimit_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_powermaillimit_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'powermail_limit'; // The extension key.
	var $pi_checkCHash = TRUE;

	public function PM_FieldHook($xml, $title, $type, $uid, $markerArray, $piVarsFromSession, $obj) {
		$this->conf = $conf;
		$this->content = $content;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// config
		$this->xml = $xml;
		$this->uid = $uid;
		$this->obj = $obj;
		$this->piVarsFromSession = $piVarsFromSession;
		$content = '';
		$content_item = '';
		$this->tmpl = array();
		$this->tmpl['all'] = tslib_cObj::fileResource('EXT:powermail_limit/template/tx_powermaillimit_fieldwrap.html');
		//tslib_cObj::fileResource($this->conf['template.']['fieldWrap'])

		// let's go
		if ($type == 'selectlimit') {
			$content = $this->html_selectlimit(); // generate selectorbox <select><option>...

			if (!empty($content)) return $content;
		}
	}

	public function PM_MandatoryHookBefore(&$error, $markerArray, &$sessionfields, $obj) {
		$this->obj = $obj;

		//$fieldPid = $GLOBALS['TSFE']->id;
		$session = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'tx_powermaillimit_selectlimit');

		foreach ($sessionfields as $field => $value) {
			if (isset($session['limits'][$field][$value])) {
				$fieldUid = $session['limits'][$field][$value]['fieldUid'];
				$option = $session['limits'][$field][$value]['option'];

				if ($this->isLimitExceeded($fieldUid, $option)) {
					$error = 1;
					$sessionfields['ERROR'][3][] = $session['limits'][$field][$value]['errorlimit'];
				}
			}
		}
	}

	public function PM_FormWrapMarkerHook(&$outerMarkerArray, &$subpartArray, &$conf, &$obj) {

		if ($this->isPostLimitExceeded($obj)) {
			$errorMessage = $obj->cObj->data['tx_powermaillimit_postlimit_error'];
			$subpartArray['###POWERMAIL_CONTENT###'] = $obj->pi_RTEcssText($errorMessage);
			return;
		}

	}

	public function PM_SubmitBeforeMarkerHook(&$obj, &$markerArray, &$sessiondata) {

		if ($this->isPostLimitExceeded($obj)) {
			$errorMessage = $obj->cObj->data['tx_powermaillimit_postlimit_error'];
			$renderErrorMessage = $obj->pi_RTEcssText($errorMessage);
			return $renderErrorMessage;
		}

	}


	/**
	 * Function html_select() returns HTML tag for selectorbox
	 *
	 * @return    string    $content
	 */
	public function html_selectlimit() {
		$this->tmpl['html_selectlimit']['all'] = tslib_cObj::getSubpart($this->tmpl['all'], '###POWERMAIL_FIELDWRAP_HTML_SELECTLIMIT###'); // work on subpart 1
		$this->tmpl['html_selectlimit']['item'] = tslib_cObj::getSubpart($this->tmpl['html_selectlimit']['all'], '###ITEM###'); // work on subpart 2

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

				// check if limit is already exceeded
				if ($this->isLimitExceeded($this->uid, $options[$i])) {
					if ($optionlimit) {
						$markerArray['###LABEL###'] .= $optionlimit;
						$markerArray['###SELECTED###'] .= ' disabled="disabled"';
					} else {
						// skip option if no message defined
						continue;
					}
				}

				// Store values in session (easier usage in PM_MandatoryHookBefore
				if (isset($options[$i][3])) { // limit set
					$value = $options[$i][1] ? $options[$i][1] : $options[$i][0];
					$limit = intval($options[$i][3]);

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

	public function isLimitExceeded($fieldUid, $option) {
		if (!isset($option[3])) { // no limit set
			return FALSE;
		}

		$value = $option[1] ? $option[1] : $option[0];
		$limit = intval($option[3]);

		$count = $this->countStoredMailField($fieldUid, $value);

		//echo $value.': '.$count.'/'.$limit.'<br/>';

		$this->session['limits']['uid' . $fieldUid][$value] = Array('fieldUid' => $fieldUid, 'option' => $option);

		return ($count >= $limit);
	}


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
				if ($xml_value == $value) {
					$count++;
				}
			}
		}
		return $count;
	}

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


	public function isPostLimitExceeded(&$obj) {

		$limit = $obj->cObj->data['tx_powermaillimit_postlimit'];

		if (!empty($limit)) {

			$mailCount = $this->countMails();

			if ($mailCount >= $limit) {
				return TRUE;
			}

		}

	}


	// from class "tx_powermail_submit" Line 22
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