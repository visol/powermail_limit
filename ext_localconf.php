<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
$signalSlotDispatcher->connect('In2code\Powermail\Domain\Validator\CustomValidator', 'isValid', 'Visol\PowermailLimit\Domain\Validator\LimitationValidator', 'checkLimitation', FALSE);
