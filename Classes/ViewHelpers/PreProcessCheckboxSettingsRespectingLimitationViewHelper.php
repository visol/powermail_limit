<?php
namespace Visol\PowermailLimit\ViewHelpers;

class PreProcessCheckboxSettingsRespectingLimitationViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \Visol\PowermailLimit\Service\AnswerLimitationService
	 * @inject
	 */
	protected $answerLimitationService;

	/**
	 * @return array
	 */
	public function render() {
		$settings = $this->renderChildren();
		$preProcessedSettings = $this->answerLimitationService->getSettingsArrayFromRawSettings($settings);
		return $preProcessedSettings;
	}
}
