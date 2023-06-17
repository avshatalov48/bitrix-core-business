<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\Model\ReactionTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserShortPopupItem;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;

/**
 * @method ReactionMessage next()
 * @method ReactionMessage current()
 * @method ReactionMessage offsetGet($offset)
 */
class ReactionMessages extends Registry implements RestConvertible, PopupDataAggregatable
{
	use ContextCustomer;

	private const COUNT_DISPLAYED_USERS = 5;

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

	public static function getRestEntityName(): string
	{
		return 'reactions';
	}

	public function toRestFormat(array $option = []): array
	{
		$rest = [];
		$option['WITHOUT_OWN_REACTIONS'] = !$this->withOwnReactions;

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
			if ($reactionMessage->haveDisplayedUsers(self::COUNT_DISPLAYED_USERS))
			{
				$messagesNeedFillUsers[] = $reactionMessage->getMessageId();
			}
		}

		if (empty($messagesNeedFillUsers))
		{
			return $this;
		}

		$result = ReactionTable::query()
			->setSelect(['MESSAGE_ID', 'REACTION', 'USERS_GROUP'])
			->whereIn('MESSAGE_ID', $messagesNeedFillUsers)
			->having('COUNT', '<=', self::COUNT_DISPLAYED_USERS)
			->fetchAll()
		;

		foreach ($result as $row)
		{
			$userIds = array_map('intval', explode(',', $row['USERS_GROUP']));
			$this->getReactionMessage((int)$row['MESSAGE_ID'])->addUsers($row['REACTION'], $userIds);
		}

		return $this;
	}

	private function fillOwnReactions(): self
	{
		if (!$this->withOwnReactions)
		{
			return $this;
		}

		$messageIds = $this->getMessageIds();

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