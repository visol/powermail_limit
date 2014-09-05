<?php
namespace Visol\PowermailLimit\ViewHelpers;

use Visol\PowermailLimit\Utility\PowermailUtility;

class CheckboxWithLimitPreRendererViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \Visol\PowermailLimit\Service\AnswerLimitationService
	 * @inject
	 */
	protected $answerLimitationService;

	/**
	 * mailRepository
	 *
	 * @var \In2code\Powermail\Domain\Repository\MailRepository
	 * @inject
	 */
	protected $mailRepository;

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('fieldAnswerValue', 'string', 'Answer value for a field.', TRUE);
		$this->registerArgument('fieldUid', 'integer', 'UID of the field belonging to the checkboxes.', TRUE);
		$this->registerArgument('limit', 'integer', 'Maximum times this checkbox can be checked.', TRUE);
	}

	/**
	 * @return array
	 */
	public function render() {
		$settings = $this->templateVariableContainer->get('settings');
		$mails = $this->mailRepository->findAllInPid(PowermailUtility::getCurrentPid($settings));

		$isSelectedAnswerAvailable = $this->answerLimitationService->isSelectedAnswerAvailableForLimitedField($this->arguments['fieldAnswerValue'], $this->arguments['limit'], $this->arguments['fieldUid'], $mails);

		if ($isSelectedAnswerAvailable) {
			// answer available => render checkbox
			return array(
				'renderCheckbox' => TRUE
			);
		} else {
			// the optionLimitMessage is displayed besides disabled options
			// if it is not set, the whole checkbox isn't rendered
			$optionLimitMessage = $this->answerLimitationService->getLimitMessageForFieldValue($this->arguments['fieldUid']);

			if (!empty($optionLimitMessage)) {
				// answer not available but option limit message => render disabled checkbox
				return array(
					'renderCheckbox' => TRUE,
					'optionLimitMessage' => $optionLimitMessage
				);
			} else {
				// answer and option limit message not available => don't render checkbox
				return array(
					'renderCheckbox' => FALSE
				);
			}

		}
	}
}
