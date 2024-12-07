<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Main\Engine\Response\Converter;

class PushService
{
	private const ADD_REACTION_EVENT = 'addReaction';
	private const DELETE_REACTION_EVENT = 'deleteReaction';

	public function add(ReactionItem $reaction): void
	{
		$this->send(self::ADD_REACTION_EVENT, $reaction);
	}

	public function delete(ReactionItem $reaction): void
	{
		$this->send(self::DELETE_REACTION_EVENT, $reaction);
	}

	private function send(string $eventName, ReactionItem $reaction): void
	{
		$converter = new Converter(Converter::KEYS | Converter::VALUES | Converter::TO_LOWER | Converter::LC_FIRST);
		$messageId = $reaction->getMessageId();
		$reactionMessage = (new ReactionMessages([$messageId], false))->getReactionMessage($messageId);
		$params = [
			'userId' => $reaction->getUserId(),
			'reaction' => $converter->process($reaction->getReaction()),
			'actualReactions' => (new RestAdapter($reactionMessage))->toRestFormat(),
			'dialogId' => Chat::getInstance($reaction->getChatId())->getDialogId(),
		];
		$chat = Chat::getInstance($reaction->getChatId());

		if ($chat instanceof Chat\PrivateChat)
		{
			$this->sendToPrivateChat($params, $eventName, $reaction);

			return;
		}

		$pull = [
			'module_id' => 'im',
			'command' => $eventName,
			'params' => $params,
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		if ($chat->getType() === Chat::IM_TYPE_COMMENT)
		{
			\CPullWatch::AddToStack('IM_PUBLIC_COMMENT_'.$chat->getParentChatId(), $pull);
		}
		else
		{
			\Bitrix\Pull\Event::add($this->getRecipient($reaction), $pull);
		}

		if ($chat->needToSendPublicPull())
		{
			\CPullWatch::AddToStack('IM_PUBLIC_'.$chat->getChatId(), $pull);
		}
	}

	private function sendToPrivateChat(array $params, string $eventName, ReactionItem $reaction): void
	{
		/** @var Chat\PrivateChat $chat */
		$chat = Chat::getInstance($reaction->getChatId());
		foreach ($this->getRecipient($reaction) as $recipient)
		{
			$params['dialogId'] = $chat->getCompanion($recipient)->getId();
			\Bitrix\Pull\Event::add($recipient, [
				'module_id' => 'im',
				'command' => $eventName,
				'params' => $params,
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}
	}

	private function getRecipient(ReactionItem $reaction): array
	{
		$userIds = [];
		$relations = Chat::getInstance($reaction->getChatId())->getRelations();

		foreach ($relations as $relation)
		{
			if ($relation->getStartId() <= $reaction->getMessageId())
			{
				$userIds[] = $relation->getUserId();
			}
		}

		return $userIds;
	}
}