<?php

namespace Bitrix\MessageService\Update;

use Bitrix\Main\Update\Stepper;
use Bitrix\MessageService\Providers\Base\Option;
use Bitrix\MessageService\Providers\Encryptor;
use Bitrix\MessageService\Sender\SmsManager;

class SettingsEncryptor extends Stepper
{
	use Encryptor;

	protected static $moduleId = 'messageservice';

	function execute(array &$option)
	{
		foreach (SmsManager::getRegisteredSenderList() as $sender)
		{
			$optionManager = new Option($sender->getType(), $sender->getId());

			$providerOptions = $optionManager->getOptions();
			$providerOptions = serialize($providerOptions);

			$cryptoKey = mb_strtolower($sender->getType()) . '-' . $sender->getId();
			$providerOptions = self::encrypt($providerOptions, $cryptoKey);

			$dbOptionName = 'sender.' . mb_strtolower($sender->getType()) . '.' . $sender->getId();

			$data = [
				'crypto' => 'Y',
				'data' => $providerOptions
			];

			\Bitrix\Main\Config\Option::set('messageservice', $dbOptionName, serialize($data));
		}

		return false;
	}
}