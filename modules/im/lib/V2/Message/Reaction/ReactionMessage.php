<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\V2\Entity\User\UserShortPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Engine\Response\Converter;

class ReactionMessage implements RestConvertible, PopupDataAggregatable
{
	private int $messageId;
	/**
	 * @var array<string,int> [REACTION => COUNTER, ...]
	 */
	private array $reactionCounters = [];
	/**
	 * @var array<string,int[]> [REACTION => [USER_ID1, USER_ID2], ...]
	 */
	private array $reactionUsers = [];
	/**
	 * @var string[]
	 */
	private array $ownReactions = [];

	public function __construct(int $messageId)
	{
		$this->messageId = $messageId;
	}

	public static function getByMessageId(int $messageId): self
	{
		return (new ReactionMessages([$messageId]))->getReactionMessage($messageId);
	}

	public function addCounter(string $reaction, int $counter): self
	{
		$this->reactionCounters[$reaction] = $counter;

		return $this;
	}

	public function addUsers(string $reaction, array $users): self
	{
		$this->reactionUsers[$reaction] = $users;

		return $this;
	}

	public function addOwnReaction(string $reaction): self
	{
		$this->ownReactions[] = $reaction;

		return $this;
	}

	public function getMessageId(): int
	{
		return $this->messageId;
	}

	public function haveReactions(): bool
	{
		return !empty($this->reactionCounters);
	}

	public function haveDisplayedUsers(int $countDisplayedUsers): bool
	{
		foreach ($this->reactionCounters as $reactionCounter)
		{
			if ($reactionCounter <= $countDisplayedUsers)
			{
				return true;
			}
		}

		return false;
	}

	public function toRestFormat(array $option = []): array
	{
		$converter = new Converter(Converter::KEYS | Converter::VALUES | Converter::TO_LOWER | Converter::LC_FIRST);
		$rest = [
			'messageId' => $this->messageId,
			'reactionCounters' => $converter->process($this->reactionCounters),
			'reactionUsers' => $converter->process($this->reactionUsers),
		];

		if (!isset($option['WITHOUT_OWN_REACTIONS']) || $option['WITHOUT_OWN_REACTIONS'] === false)
		{
			$rest['ownReactions'] = $converter->process($this->ownReactions);
		}

		return $rest;
	}

	public static function getRestEntityName(): string
	{
		return 'reaction';
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return new PopupData([new UserShortPopupItem($this->getUserIds())], $excludedList);
	}

	/**
	 * @return array<int,int>
	 */
	public function getUserIds(): array
	{
		$userIds = [];

		foreach ($this->reactionUsers as $reaction)
		{
			foreach ($reaction as $user)
			{
				$userIds[$user] = $user;
			}
		}

		return $userIds;
	}
}