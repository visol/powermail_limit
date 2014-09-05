<?php
namespace Visol\PowermailLimit\Domain\Model;

class Field extends \In2code\Powermail\Domain\Model\Field {

	/**
	 * optionLimitMessage
	 *
	 * @var string
	 */
	protected $optionLimitMessage = '';

	/**
	 * limitErrorMessage
	 *
	 * @var string
	 */
	protected $limitErrorMessage = '';

	/**
	 * @return string
	 */
	public function getOptionLimitMessage() {
		return $this->optionLimitMessage;
	}

	/**
	 * @param string $optionLimitMessage
	 */
	public function setOptionLimitMessage($optionLimitMessage) {
		$this->optionLimitMessage = $optionLimitMessage;
	}

	/**
	 * @return string
	 */
	public function getLimitErrorMessage() {
		return $this->limitErrorMessage;
	}

	/**
	 * @param string $limitErrorMessage
	 */
	public function setLimitErrorMessage($limitErrorMessage) {
		$this->limitErrorMessage = $limitErrorMessage;
	}


}