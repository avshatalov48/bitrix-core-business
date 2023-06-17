<?php

namespace Bitrix\Pull\Push\Service;

use Bitrix\Pull\Push\Message\HuaweiPushKitMessage;

class HuaweiPushKit extends BaseService {

	function getMessageInstance($token): HuaweiPushKitMessage
	{
		return new HuaweiPushKitMessage($token);
	}

	public static function shouldBeSent($messageRowData): bool
	{
		return true;
	}

	public function getBatch(array $messages = []): string
	{
		$arGroupedMessages = self::getGroupedByAppID($messages);
		if (empty($arGroupedMessages))
		{
			return '';
		}

		return $this->getBatchWithModifier($arGroupedMessages, ";6;");
	}
}
