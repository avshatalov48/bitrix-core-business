<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
use CPullWatch;

class PushService
{
	protected function  isPullEnable(): bool
	{
		return \Bitrix\Main\Loader::includeModule('pull');
	}


	public function sendPull(Message $message, ?array $extraParams = null): void
	{
		if (!$this->isPullEnable())
		{
			return;
		}

		$chat = $message->getChat();
		$params = $message->getEnrichedParams();

		$pullParams = $this->getPullFormatParams($message);
		$users = $chat->getRelations()->getUserIds();

		if ($chat->getType() === IM_MESSAGE_PRIVATE)
		{
			$pullParams['params']['toUserId'] = $chat->getCompanion($message->getAuthorId())->getId();
			$pullParams['params']['fromUserId'] = $message->getAuthorId();
		}
		else
		{
			$pullParams['params']['senderId'] = $message->getAuthorId();

			if ($chat->getEntityType() === 'LINES')
			{
				$users = $this->getRecipients($chat);
			}
		}

		$messageParams = $params->toPullFormat($extraParams);
		$pullParams['params']['params'] = $messageParams;

		if ($chat->getType() === Chat::IM_TYPE_COMMENT)
		{
			CPullWatch::AddToStack('IM_PUBLIC_COMMENT_' . $chat->getParentChatId(), $pullParams);
		}
		else
		{
			\Bitrix\Pull\Event::add($users, $pullParams);
		}

		if ($chat->needToSendPublicPull())
		{
			CPullWatch::AddToStack('IM_PUBLIC_' . $chat->getId(), $pullParams);
		}
		if ($chat->getType() === Chat::IM_TYPE_OPEN_CHANNEL && $message->getId() === $chat->getLastMessageId())
		{
			Chat\OpenChannelChat::sendSharedPull($pullParams);
		}
	}

	protected function getPullFormatParams(Message $message): array
	{
		return [
			'module_id' => 'im',
			'command' => 'messageParamsUpdate',
			'params' => [
				'id' => $message->getId(),
				'type' => $message->getChat()->getType() === IM_MESSAGE_PRIVATE ? 'private': 'chat',
				'chatId' => $message->getChat()->getId(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra(),
		];
	}

	protected function getRecipients(Chat $chat): array
	{
		$users = $chat->getRelations()->getUserIds();

			foreach ($chat->getRelations()->getUsers() as $user)
			{
				if ($user->getExternalAuthId() === 'imconnector')
				{
					unset($users[$user->getId()]);
				}
			}

			return $users;
	}
}