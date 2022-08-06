<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Security;
use Bitrix\Sender\Transport;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SenderConfigLimitsComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	protected function initParams()
	{
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::getInstance()->canModifySettings();
	}

	protected function prepareResult()
	{

		if (!$this->arParams['CAN_EDIT'])
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['CAN_TRACK_MAIL'] = Option::get('sender', 'track_mails') === 'Y';
		$this->arResult['USE_MAIL_CONSENT'] = Option::get('sender', 'mail_consent') === 'Y';
		$this->arResult['SENDING_TIME'] = Option::get('sender', 'sending_time') === 'Y';
		$this->arResult['SENDING_START'] = Option::get('sender', 'sending_start', '09:00');
		$this->arResult['SENDING_END'] = Option::get('sender', 'sending_end', '18:00');
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		$list = array();
		$transports = Transport\Factory::getTransports();
		foreach ($transports as $transport)
		{
			$transport = new Transport\Adapter($transport);
			if (!$transport->hasLimiters())
			{
				continue;
			}

			$helpUri = $helpCaption = null;
			$limits = array();
			foreach ($transport->getLimiters() as $limiter)
			{
				if ($limiter->isHidden())
				{
					continue;
				}

				/** @var Transport\CountLimiter $limiter */
				$isCountLimiter = $limiter instanceof Transport\CountLimiter;

				$current = $limiter->getCurrent() ?: 0;
				$limit = $limiter->getLimit() ?: 1;

				$available = $limit - $current;
				$available = $available > 0 ? $available : 0;

				$initialLimit = $isCountLimiter ? $limiter->getInitialLimit() : 0;
				$initialLimit = $initialLimit ?: 1;

				$percentage = $isCountLimiter ? ceil(($current / $initialLimit) * 100) : 0;
				$percentage = $percentage > 100 ? 100 : $percentage;

				$limits[] = array(
					'NAME' => $isCountLimiter ? $limiter->getName() : null,
					'AVAILABLE' => number_format($available, 0, '.', ' '),
					'CURRENT' => number_format($current, 0, '.', ' '),
					'CURRENT_PERCENTAGE' => $percentage,
					'LIMIT' => number_format($limit, 0, '.', ' '),
					'UNIT_NAME' => $limiter->getUnitName(),
					'CAPTION' => $limiter->getCaption(),
					'SETUP_URI' => $limiter->getParameter('setupUri'),
					'SETUP_CAPTION' => $limiter->getParameter('setupCaption'),
					'PERCENTAGE' => $limiter->getParameter('percentage'),
					'TEXT_VIEW' => $limiter->getParameter('textView'),
				);

				if ($limiter->getParameter('globalHelpUri'))
				{
					$helpUri = $limiter->getParameter('globalHelpUri');
				}
			}

			if (empty($limits))
			{
				continue;
			}

			$list[] = array(
				'CODE' => $transport->getCode(),
				'NAME' => $transport->getName(),
				'LIMITS' => $limits,
				'HELP_URI' => $helpUri,
				'HELP_CAPTION' => $helpCaption
			);
		}

		$this->arResult['LIST'] = $list;
		Bitrix\Sender\Integration\Bitrix24\Service::initLicensePopup();

		return true;
	}

	public function executeComponent()
	{
		parent::executeComponent();
		parent::prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}
}