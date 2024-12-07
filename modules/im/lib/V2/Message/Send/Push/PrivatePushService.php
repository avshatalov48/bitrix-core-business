<?php

namespace Bitrix\Im\V2\Message\Send\Push;

use Bitrix\Im\V2\Message\Send\PushService;

class PrivatePushService extends PushService
{
	public function sendPush(array $counters = []): void
	{
		if (!$this->isPullEnable() || !$this->sendingConfig->addRecent())
		{
			return;
		}

		$pullMessages = $this->getPullMessages($counters);

		foreach ($pullMessages as $userId => $pullMessage)
		{
			\Bitrix\Pull\Event::add($userId, $pullMessage);
			$this->mobilePush->sendForPrivateMessage($userId, $pullMessage);
		}
	}

	protected function getPullMessages(array $counters): array
	{
		$chat = $this->message->getChat();
		$fromUserId = $this->message->getAuthorId();
		$toUserId = $chat->getCompanion($fromUserId)->getId();
		$basePullMessage = $this->getBasePullMessage();

		if ($fromUserId === $toUserId)
		{
			return [$toUserId => $this->getPullMessage($basePullMessage, $toUserId, $toUserId, $counters)];
		}

		return [
			$toUserId => $this->getPullMessage($basePullMessage, $toUserId, $fromUserId, $counters),
			$fromUserId => $this->getPullMessage($basePullMessage, $fromUserId, $toUserId, $counters),
		];
	}

	protected function getBasePullMessage(): array
	{
		return [
			'module_id' => 'im',
			'command' => 'message',
			'params' => $this->pushFormatter->format(),
			'extra' => \Bitrix\Im\Common::getPullExtra(),
		];
	}

	protected function getPullMessage(array $basePullMessage, int $userId, int $opponentId, array $counters): array
	{
		$basePullMessage['params']['dialogId'] = $opponentId;
		$basePullMessage['params']['counter'] = $counters[$userId] ?? 0;

		return $basePullMessage;
	}
}