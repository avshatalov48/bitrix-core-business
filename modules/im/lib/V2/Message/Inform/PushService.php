<?php

namespace Bitrix\Im\V2\Message\Inform;

use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\PushFormat;

class PushService
{
	public function isPullEnable(): bool
	{
		static $enable;
		if ($enable === null)
		{
			$enable = \Bitrix\Main\Loader::includeModule('pull');
		}
		return $enable;
	}

	public function sendInformPushPrivateChat(PrivateChat $chat, Message $message): void
	{
		if (!$this->isPullEnable())
		{
			return;
		}

		$fromUserId = $message->getAuthorId();
		$toUserId = $chat->getCompanion()->getId();

		$pushFormat = new PushFormat();

		$pullMessage = [
			'module_id' => 'im',
			'command' => 'informMessage',
			'params' => $pushFormat->formatPrivateMessage($message, $chat),
			'extra' => \Bitrix\Im\Common::getPullExtra(),
		];

		$pullMessageTo = $pullMessage;
		$pullMessageTo['params']['dialogId'] = $fromUserId;

		if ($fromUserId != $toUserId)
		{
			\Bitrix\Pull\Event::add($toUserId, $pullMessageTo);
		}
	}
}