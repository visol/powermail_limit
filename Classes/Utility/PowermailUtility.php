<?php

namespace Visol\PowermailLimit\Utility;

class PowermailUtility {

	/**
	 * Get the current Powermail PID
	 * Flexform takes precedence before TypoScript
	 * If not set, take the current PID (Powermail default)
	 *
	 * @param array $settings
	 * @return int
	 */
	public static function getCurrentPid($settings) {
		if (intval($settings['flexform']['main']['pid']) > 0) {
			return (int)$settings['flexform']['main']['pid'];
		}
		if (intval($settings['setup']['main']['pid']) > 0) {
			return (int)$settings['setup']['main']['pid'];
		}
		return $GLOBALS['TSFE']->id;
	}

}
