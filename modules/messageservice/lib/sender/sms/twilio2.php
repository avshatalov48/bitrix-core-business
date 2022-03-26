<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

class Twilio2 extends Twilio
{
	public const ID = 'twilio2';

	public static function isSupported()
	{
		return (
			ModuleManager::isModuleInstalled('b24network')
			|| Option::get('messageservice', 'twilio2_enabled', 'N') === 'Y'
		);
	}

	public function getName()
	{
		return parent::getName() . ' (2)';
	}

	public function getShortName()
	{
		return parent::getShortName() . ' (2)';
	}

	protected function getCallbackUrl()
	{
		return null;
	}

	public function getConfigComponentTemplatePageName(): string
	{
		return parent::getId();
	}
}
