<?php
namespace Visol\PowermailLimit\ViewHelpers\Form;
use Visol\PowermailLimit\Utility\PowermailUtility;

/**
 * View Helper which creates a simple checkbox (<input type="checkbox">) respecting the
 * limitation setting of a Powermail field
 */
class CheckboxRespectingLimitationViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\CheckboxViewHelper {

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
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('fieldUid', 'integer', 'UID of the field belonging to the checkboxes.', TRUE);
		$this->registerArgument('limit', 'integer', 'Maximum times this checkbox can be checked.', TRUE);
	}

	/**
	 * Renders the checkbox.
	 *
	 * @param boolean $checked Specifies that the input element should be preselected
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return string
	 * @api
	 */
	public function render($checked = NULL) {
		$this->tag->addAttribute('type', 'checkbox');

		// the optionLimitMessage is displayed besides disabled options
		// if it is not set, the whole option isn't rendered
		$optionLimitMessage = $this->answerLimitationService->getLimitMessageForFieldValue($this->arguments['fieldUid']);

		$settings = $this->templateVariableContainer->get('settings');
		$mails = $this->mailRepository->findAllInPid(PowermailUtility::getCurrentPid($settings));
		$isSelectedAnswerAvailable = $this->answerLimitationService->isSelectedAnswerAvailableForLimitedField($this->getValue(), $this->arguments['limit'], $this->arguments['fieldUid'], $mails);

		if (!$isSelectedAnswerAvailable && empty($optionLimitMessage)) {
			// if the answer is unavailable and the optionLimitMessage is empty, we don't render the option at all
			return '';
		}


		$nameAttribute = $this->getName();
		$valueAttribute = $this->getValue();
		if ($this->isObjectAccessorMode()) {
			if ($this->hasMappingErrorOccurred()) {
				$propertyValue = $this->getLastSubmittedFormData();
			} else {
				$propertyValue = $this->getPropertyValue();
			}

			if ($propertyValue instanceof \Traversable) {
				$propertyValue = iterator_to_array($propertyValue);
			}
			if (is_array($propertyValue)) {
				if ($checked === NULL) {
					$checked = in_array($valueAttribute, $propertyValue);
				}
				$nameAttribute .= '[]';
			} elseif (($multiple = FALSE) === TRUE) {
				// @todo: implement correct as in Flow.Fluid
				$nameAttribute .= '[]';
			} elseif ($checked === NULL && $propertyValue !== NULL) {
				$checked = (boolean) $propertyValue === (boolean) $valueAttribute;
			}
		}
		$this->registerFieldNameForFormTokenGeneration($nameAttribute);
		$this->tag->addAttribute('name', $nameAttribute);
		$this->tag->addAttribute('value', $valueAttribute);
		if (!$isSelectedAnswerAvailable) {
			$this->tag->addAttribute('disabled', 'disabled');
		}
		if ($checked) {
			$this->tag->addAttribute('checked', 'checked');
		}
		$this->setErrorClassAttribute();
		$hiddenField = $this->renderHiddenFieldForEmptyValue();
		return $hiddenField . $this->tag->render();
	}
}
