<?php

namespace Bitrix\Pull\Push\Service;

use Bitrix\Pull\Push\Message\BaseMessage;
use Bitrix\Pull\Push\Message\FirebaseAndroidMessage;

class FirebaseAndroid extends BaseService
{

	function getMessageInstance(string $token): BaseMessage
	{
		return new FirebaseAndroidMessage($token);
	}

	static function shouldBeSent(array $messageRowData): bool
	{
		return true;
	}

	function getBatch(array $messages): string
	{
		$arGroupedMessages = self::getGroupedByAppID($messages);
		if (empty($arGroupedMessages))
		{
			return '';
		}

		return $this->getBatchWithModifier($arGroupedMessages, ";7;");
	}
}