<?php
namespace Bitrix\MessageService\Update;

use \Bitrix\Main\Loader;

final class Agent
{
	public static function resetCallbackAgent(): string
	{
		if (Loader::IncludeModule('messageservice'))
		{
			$sender = \Bitrix\Messageservice\Sender\SmsManager::getSenderById(\Bitrix\Messageservice\Providers\Edna\WhatsApp\Constants::ID);
			if ($sender instanceof \Bitrix\MessageService\Sender\Sms\Ednaru)
			{
				$sender->resetCallback();
			}
		}

		return '';
	}
}
