<?php
namespace Visol\PowermailLimit\Domain\Validator;

/**
 * Validate if the user is trying to post an answer of type checklimit or selectlimit that is no longer available,
 * e.g. because a mail submitted between rendering the form and submitting for the current user was posted or because
 * the user manipulated the disabled options
 */
class LimitationValidator extends \In2code\Powermail\Domain\Validator\CustomValidator {

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
	 * Validate select or check limit fields
	 *
	 * @param $mail \In2code\Powermail\Domain\Model\Mail
	 * @param $customValidator \In2code\Powermail\Domain\Validator\CustomValidator
	 * @return bool
	 */
	public function checkLimitation($mail, $customValidator) {

		foreach ($mail->getAnswers() as $answer) {
			/** @var \In2code\Powermail\Domain\Model\Answer $answer */
			if ($answer->getField()->getType() === 'checklimit' || $answer->getField()->getType() === 'selectlimit') {
				$mails = $this->mailRepository->findAllInPid(\Visol\PowermailLimit\Utility\PowermailUtility::getCurrentPid($this->settings));
				$limit = $this->answerLimitationService->getLimitValueForAnswer($answer->getField()->getSettings(), $answer->getValue());
				$answerValueArray = json_decode($answer->getValue());
				$isSelectedAnswerAvailableForLimitedField = $this->answerLimitationService->isSelectedAnswerAvailableForLimitedField($answerValueArray[0], $limit, $answer->getField()->getUid(), $mails);
				if (!$isSelectedAnswerAvailableForLimitedField) {
					$customValidator->setIsValid(FALSE);
					$errorMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('validation.optionNotAvailable', 'powermail_limit');
					$customValidator->setErrorAndMessage($answer->getField(), $errorMessage);
					return;
				}
			}
		}

	}

}