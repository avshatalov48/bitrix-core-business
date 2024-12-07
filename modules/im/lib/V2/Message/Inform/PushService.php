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

	public function sendInformPushPrivateChat(Message $message): void
	{
		if (!$this->isPullEnable())
		{
			return;
		}

		$fromUserId = $message->getAuthorId();
		$toUserId = $message->getChat()->getCompanion()->getId();

		$pushFormat = new PushFormat($message);

		$pullMessage = [
			'module_id' => 'im',
			'command' => 'informMessage',
			'params' => $pushFormat->format(),
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