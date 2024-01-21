<?php

namespace Bitrix\Im\V2\Controller\Chat\Message;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Reaction\ReactionService;
use Bitrix\Im\V2\Rest\RestAdapter;

class Reaction extends BaseController
{
	/**
	 * @restMethod im.v2.Chat.Message.Reaction.add
	 */
	public function addAction(Message $message, string $reaction): ?array
	{
		$reaction = mb_strtoupper($reaction);
		$service = new ReactionService($message);
		$reactResult = $service->addReaction($reaction);

		if (!$reactResult->isSuccess())
		{
			$this->addErrors($reactResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.Reaction.delete
	 */
	public function deleteAction(Message $message, string $reaction): ?array
	{
		$reaction = mb_strtoupper($reaction);
		$service = new ReactionService($message);
		$reactResult = $service->deleteReaction($reaction);

		if (!$reactResult->isSuccess())
		{
			$this->addErrors($reactResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.Reaction.tail
	 */
	public function tailAction(Message $message, array $filter = [], array $order = [], int $limit = 50): ?array
	{
		$reaction = $filter['reaction'] ?? null;
		if ($reaction !== null)
		{
			$reaction = mb_strtoupper($reaction);
			$validateResult = Message\Reaction\ReactionItem::validateReaction($reaction);
			if (!$validateResult->isSuccess())
			{
				$this->addErrors($validateResult->getErrors());

				return null;
			}
		}

		$reactionFilter = [
			'LAST_ID' => $filter['lastId'] ?? null,
			'MESSAGE_ID' => $message->getMessageId(),
			'REACTION' => $reaction,
		];
		$reactionOrder = [
			'ID' => $order['id'] ?? 'DESC'
		];
		$reactionLimit = $this->getLimit($limit);

		$reactions = Message\Reaction\ReactionCollection::find($reactionFilter, $reactionOrder, $reactionLimit);

		return (new RestAdapter($reactions))->toRestFormat();
	}
}