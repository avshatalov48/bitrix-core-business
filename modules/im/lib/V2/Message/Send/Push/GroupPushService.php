<?php

namespace Bitrix\Im\V2\Message\Send\Push;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\Send\PushService;

class GroupPushService extends PushService
{
	public function sendPush(array $counters = []): void
	{
		if (!$this->isPullEnable() || !$this->sendingConfig->addRecent())
		{
			return;
		}

		$chat = $this->message->getChat();

		if ($chat->getRelations()->hasUser($this->message->getAuthorId(), $chat->getId()))
		{
			\CPushManager::DeleteFromQueueBySubTag($this->message->getAuthorId(), 'IM_MESS');
		}

		$pullMessage = [
			'module_id' => 'im',
			'command' => 'messageChat',
			'params' => $this->pushFormatter->format(),
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		$watchPullMessage = $pullMessage;
		$watchPullMessage['params']['message']['params']['NOTIFY'] = 'N';
		$watchPullMessage['extra']['is_shared_event'] = true;
		if ($chat->needToSendPublicPull())
		{
			\CPullWatch::AddToStack('IM_PUBLIC_'. $chat->getChatId(), $watchPullMessage);
		}
		if ($chat->getType() === Chat::IM_TYPE_OPEN_CHANNEL)
		{
			Chat\OpenChannelChat::sendSharedPull($watchPullMessage);
		}
		if ($chat->getType() === \Bitrix\Im\V2\Chat::IM_TYPE_COMMENT)
		{
			\CPullWatch::AddToStack('IM_PUBLIC_COMMENT_' . $chat->getParentChatId(), $watchPullMessage);
		}

		$groups = self::getEventGroupsByCounters($pullMessage, $counters);
		foreach ($groups as $group)
		{
			\Bitrix\Pull\Event::add($group['users'], $group['event']);
			$this->mobilePush->sendForGroupMessage($group);
		}
	}
}
