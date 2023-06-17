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
		];
		\Bitrix\Pull\Event::add($this->getRecipient($reaction), [
			'module_id' => 'im',
			'command' => $eventName,
			'params' => $params,
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);

		$chat = Chat::getInstance($reaction->getChatId());

		if ($chat->getType() === Chat::IM_TYPE_OPEN || $chat->getType() === Chat::IM_TYPE_OPEN_LINE)
		{
			\CPullWatch::AddToStack('IM_PUBLIC_'.$chat->getChatId(), Array(
				'module_id' => 'im',
				'command' => $eventName,
				'params' => $params,
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
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