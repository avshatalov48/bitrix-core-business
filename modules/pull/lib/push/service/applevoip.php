<?php

namespace Bitrix\Pull\Push\Service;

use Bitrix\Pull\Push\Message\AppleVoipMessage;

class AppleVoip extends Apple
{
	protected int $sandboxModifier = 4;
	protected int $productionModifier = 5;

	function getMessageInstance(string $token): AppleVoipMessage
	{
		return new AppleVoipMessage($token, 4096);
	}

	public static function shouldBeSent($messageRowData): bool
	{
		return true;
	}
}