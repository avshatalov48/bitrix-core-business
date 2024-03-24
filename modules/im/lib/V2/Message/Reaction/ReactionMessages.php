<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Main\ORM;
use Bitrix\Im\Model\ReactionTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserShortPopupItem;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;

/**
 * @implements \IteratorAggregate<int,ReactionMessage>
 * @method ReactionMessage next()
 * @method ReactionMessage current()
 * @method ReactionMessage offsetGet($offset)
 */
class ReactionMessages extends Registry implements RestConvertible, PopupDataAggregatable
{
	use ContextCustomer;

	private bool $withOwnReactions;

	public function __construct(array $messageIds, bool $withOwnReactions = true)
	{
		parent::__construct();
		$this->withOwnReactions = $withOwnReactions;

		if (empty($messageIds))
		{
			return;
		}

		$this->fillCounters($messageIds)->fillUsers()->fillOwnReactions();
	}

	public static function initFromArray(array $reactionArray, bool $withOwnReactions = true): self
	{
		$reactions = new static([], $withOwnReactions);

		foreach ($reactionArray as $reaction)
		{
			$reactions->addReactionMessage($reaction);
		}

		return $reactions;
	}

	public function addReactionMessage(ReactionMessage $reaction): self
	{
		$this[$reaction->getMessageId()] = $reaction;

		return $this;
	}

	public function getReactionMessage(int $messageId): ReactionMessage
	{
		$this[$messageId] ??= new ReactionMessage($messageId);

		return $this[$messageId];
	}

	private function getMessageIds(): array
	{
		$messageIds = [];

		foreach ($this as $reactionMessage)
		{
			$messageIds[] = $reactionMessage->getMessageId();
		}

		return $messageIds;
	}

	private function getMessageIdsToFillOwnReaction(): array
	{
		$messageIds = [];

		foreach ($this as $reactionMessage)
		{
			if ($reactionMessage->needToFillOwnReactions())
			{
				$messageIds[] = $reactionMessage->getMessageId();
			}
		}

		return $messageIds;
	}

	public static function getRestEntityName(): string
	{
		return 'reactions';
	}

	public function toRestFormat(array $option = []): array
	{
		$rest = [];
		if (!isset($option['WITHOUT_OWN_REACTIONS']))
		{
			$option['WITHOUT_OWN_REACTIONS'] = !$this->withOwnReactions;
		}

		foreach ($this as $reactionMessage)
		{
			if ($reactionMessage->haveReactions())
			{
				$rest[] = $reactionMessage->toRestFormat($option);
			}
		}

		return $rest;
	}

	public function getUserIds(): array
	{
		$userIds = [];

		foreach ($this as $reactionMessage)
		{
			foreach ($reactionMessage->getUserIds() as $userId)
			{
				$userIds[$userId] = $userId;
			}
		}

		return $userIds;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return new PopupData([new UserShortPopupItem($this->getUserIds())], $excludedList);
	}

	private function fillCounters(array $messageIds): self
	{
		$result = ReactionTable::query()
			->setSelect(['MESSAGE_ID', 'REACTION', 'COUNT'])
			->whereIn('MESSAGE_ID', $messageIds)
			->fetchAll()
		;

		foreach ($result as $row)
		{
			$this->getReactionMessage((int)$row['MESSAGE_ID'])->addCounter($row['REACTION'], (int)$row['COUNT']);
		}

		return $this;
	}

	private function fillUsers(): self
	{
		$messagesNeedFillUsers = [];

		foreach ($this as $reactionMessage)
		{
			if ($reactionMessage->haveDisplayedUsers())
			{
				$messagesNeedFillUsers[] = $reactionMessage->getMessageId();
			}
		}

		if (empty($messagesNeedFillUsers))
		{
			return $this;
		}

		$derivedQuery = ReactionTable::query()
			->setSelect(['MESSAGE_ID', 'REACTION'])
			->setGroup(['MESSAGE_ID', 'REACTION'])
			->whereIn('MESSAGE_ID', $messagesNeedFillUsers)
			->having('COUNT', '<=', ReactionMessage::COUNT_DISPLAYED_USERS)
		;
		$entity = ORM\Entity::getInstanceByQuery($derivedQuery);

		$result = ReactionTable::query()
			->setSelect(['MESSAGE_ID', 'REACTION', 'USER_ID'])
			->registerRuntimeField(
				(new ORM\Fields\Relations\Reference(
					'USERS_GROUP',
					$entity,
					ORM\Query\Join::on('this.MESSAGE_ID', 'ref.MESSAGE_ID')
						->whereColumn('this.REACTION', 'ref.REACTION')
				))->configureJoinType(ORM\Query\Join::TYPE_INNER)
			)
			->exec()
		;

		while ($row = $result->fetch())
		{
			$reactionMessage = $this->getReactionMessage((int)$row['MESSAGE_ID'])->addUsers($row['REACTION'], [(int)$row['USER_ID']]);
			if ($this->withOwnReactions && $this->getContext()->getUserId() == (int)$row['USER_ID'])
			{
				$reactionMessage->addOwnReaction($row['REACTION']);
			}
		}

		return $this;
	}

	private function fillOwnReactions(): self
	{
		if (!$this->withOwnReactions)
		{
			return $this;
		}

		$messageIds = $this->getMessageIdsToFillOwnReaction();

		if (empty($messageIds))
		{
			return $this;
		}

		$result = ReactionTable::query()
			->setSelect(['MESSAGE_ID', 'REACTION'])
			->whereIn('MESSAGE_ID', $messageIds)
			->where('USER_ID', $this->getContext()->getUserId())
			->fetchAll()
		;

		foreach ($result as $row)
		{
			$this->getReactionMessage((int)$row['MESSAGE_ID'])->addOwnReaction($row['REACTION']);
		}

		return $this;
	}
}