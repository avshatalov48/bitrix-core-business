<?php

namespace Bitrix\MessageService\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class Sender extends \Bitrix\Main\Engine\Controller
{
	public function getTemplatesAction(string $id, array $context = null): ?array
	{
		$sender = \Bitrix\MessageService\Sender\SmsManager::getSenderById($id);
		if (!$sender)
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('SENDER_TEMPLATES_WRONG_SENDER')));

			return null;
		}

		if (!$sender->canUse() || !$sender->isConfigurable() || !$sender->isTemplatesBased())
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('SENDER_TEMPLATES_CAN_NOT_USE')));

			return null;
		}

		return [
			'templates' => $sender->getTemplatesList($context)
		];
	}
}
