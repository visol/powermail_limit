<?php
namespace Visol\PowermailLimit\ViewHelpers\Form;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Visol\PowermailLimit\Utility\PowermailUtility;

/**
 * View helper to generate select field respecting limitation
 */
class SelectFieldRespectingLimitationViewHelper extends \In2code\Powermail\ViewHelpers\Form\SelectFieldViewHelper {

	/**
	 * mailRepository
	 *
	 * @var \In2code\Powermail\Domain\Repository\MailRepository
	 * @inject
	 */
	protected $mailRepository;

	/**
	 * @var \Visol\PowermailLimit\Service\AnswerLimitationService
	 * @inject
	 */
	protected $answerLimitationService;

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->overrideArgument('options', 'string', 'String containing the raw option field to process limitation feature.', TRUE);
		$this->registerArgument('fieldUid', 'integer', 'UID of the field', TRUE);
	}

	/**
	 * Render the tag.
	 *
	 * @return string rendered tag.
	 * @api
	 */
	public function render() {
		$content = parent::render();

		return $content;
	}

	/**
	 * Render the option tags.
	 *
	 * @return array an associative array of options, key will be the value of the option tag
	 */
	protected function getOptions() {
		return $this->arguments['options'];
	}

	/**
	 * Render the option tags.
	 *
	 * @param string $options raw options
	 * @return string rendered tags.
	 */
	protected function renderOptionTags($options) {
		$output = '';

		// the optionLimitMessage is displayed besides disabled options
		// if it is not set, the whole option isn't rendered
		$optionLimitMessage = $this->answerLimitationService->getLimitMessageForFieldValue($this->arguments['fieldUid']);

		$settingsArray = $this->answerLimitationService->getSettingsArrayFromRawSettings($options);

		foreach ($settingsArray as $fieldOption) {

			$isSelectedAnswerAvailable = TRUE;
			if ((!empty($fieldOption['limit']) || $fieldOption['limit'] === '0')) {
				$settings = $this->templateVariableContainer->get('settings');
				$mails = $this->mailRepository->findAllInPid(PowermailUtility::getCurrentPid($settings));
				$isSelectedAnswerAvailable = $this->answerLimitationService->isSelectedAnswerAvailableForLimitedField($fieldOption['value'], $fieldOption['limit'], $this->arguments['fieldUid'], $mails);

				if (!$isSelectedAnswerAvailable && empty($optionLimitMessage)) {
					// if the answer is unavailable and the optionLimitMessage is empty, we don't render the option at all
					continue;
				}

			}

			$output .= '<option';
			$output .= !empty($fieldOption['value']) ? ' value="' . $fieldOption['value'] . '"' : '';
			$output .= !empty($fieldOption['selected']) ? ' selected' : '';

			if (!$isSelectedAnswerAvailable) {
				$output .= ' disabled';
			}
			$output .= '>';
			$output .= $fieldOption['label'];

			if (!$isSelectedAnswerAvailable) {
				$output .= ' ' . $optionLimitMessage;
			}

			$output .= '</option>';
			$output .= chr(10);
		}
		return $output;
	}


}