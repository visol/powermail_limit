<?php
namespace Visol\PowermailLimit\Service;

class AnswerLimitationService {

	/**
	 * answerRepository
	 *
	 * @var \In2code\Powermail\Domain\Repository\AnswerRepository
	 * @inject
	 */
	protected $answerRepository;

	/**
	 * Iterates through all given mails to find out how many times the same answer was already given for a field.
	 * If the number of answers equals the limitation for this answer, it is then returned as unavailable
	 *
	 * @param string $fieldAnswerValue
	 * @param int $limit
	 * @param int $fieldUid
	 * @param $mails
	 * @return bool
	 */
	public function isSelectedAnswerAvailableForLimitedField($fieldAnswerValue, $limit, $fieldUid, $mails) {

		$valueSelectedCounter = 0;
		foreach ($mails as $mail) {
			/** @var $mail \In2code\Powermail\Domain\Model\Mail */
			$answer = $this->answerRepository->findByFieldAndMail($fieldUid, $mail->getUid());
			if ($answer instanceof \In2code\Powermail\Domain\Model\Answer) {
				// Answer values in mails are encoded as JSON
				if (is_string($answer->getValue())) {
					$decodedMailAnswerValue = json_decode($answer->getValue());
					$mailAnswerValue = $decodedMailAnswerValue[0];
					if ($mailAnswerValue === $fieldAnswerValue) {
						$valueSelectedCounter++;
					}
				}
			}
		}
		if ($valueSelectedCounter >= $limit) {
			return FALSE;
		}

		return TRUE;

	}

	/**
	 * Processes the raw settings field of a select limit or check limit field
	 * Each option/checkbox is seleted by a line feed, each setting for an option/checkbox by a pipe
	 *
	 * @param $rawSettings
	 * @return array
	 */
	public function getSettingsArrayFromRawSettings($rawSettings) {
		$settingsArray = array();
		$iterator = 0;

		$rawSettingsArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(PHP_EOL, $rawSettings);
		foreach ($rawSettingsArray as $rawFieldOption) {
			if (!empty($rawFieldOption)) {
				$tempFieldOptions = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $rawFieldOption);
				$settingsArray[$iterator] = array(
					'label' => $tempFieldOptions[0],
					'value' => isset($tempFieldOptions[1]) && !empty($tempFieldOptions[1]) ? $tempFieldOptions[1] : $tempFieldOptions[0],
					'selected' => isset($tempFieldOptions[2]) ? $tempFieldOptions[2] : '',
					'limit' => isset($tempFieldOptions[3]) ? $tempFieldOptions[3] : '',
				);
				$iterator++;
			}
		}
		return $settingsArray;
	}

	/**
	 * Get the limit for a specific field option by the (JSON encoded) value submitted
	 *
	 * @param $rawFieldSettings
	 * @param $answerValue
	 * @return int
	 */
	public function getLimitValueForAnswer($rawFieldSettings, $answerValue) {
		$settingsArray = $this->getSettingsArrayFromRawSettings($rawFieldSettings);
		foreach ($settingsArray as $setting) {
			$answerValueArray = json_decode($answerValue);
			if ($setting['value'] === $answerValueArray[0]) {
				return $setting['limit'];
			}
		}
		// if no limit was specified, we return "infinite"
		return 999999999;
	}

	/**
	 * Get the optionLimitMessage (message that is displayed besides an option or a checkbox if this option is unavailable
	 * because of the limit)
	 *
	 * The best solution would have been to extend to Field Model of Powermail to get the value via Extbase persistence,
	 * but this broke the PHP based validation. So we do it the old-fashioned way.
	 *
	 * @param integer $fieldUid
	 * @return string
	 */
	public function getLimitMessageForFieldValue($fieldUid) {
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
		$db = $GLOBALS['TYPO3_DB'];
		$whereClause = 'uid = ' . $fieldUid;
		$row = $db->exec_SELECTgetSingleRow('option_limit_message', 'tx_powermail_domain_model_fields', $whereClause);
		return $row['option_limit_message'];
	}


}