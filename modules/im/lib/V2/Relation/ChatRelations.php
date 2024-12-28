<?php

namespace Bitrix\Im\V2\Relation;

use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;

class ChatRelations
{
	/**
	 * @var self[]
	 */
	private static array $instances = [];

	private int $chatId;
	/**
	 * @var Relation[]
	 */
	private array $relationByUserId = [];
	/**
	 * @var RelationCollection[]
	 */
	private array $relationsByUserIds = [];
	private RelationCollection $fullRelations;

	private function __construct(int $chatId)
	{
		$this->chatId = $chatId;
	}

	public static function getInstance(int $chatId): self
	{
		self::$instances[$chatId] ??= new self($chatId);

		return self::$instances[$chatId];
	}

	public function forceRelations(RelationCollection $relations): void
	{
		$this->cleanCache();
		$this->fullRelations = $relations;
	}

	public function preloadUserRelation(int $userId, ?Relation $relation): void
	{
		$this->relationByUserId[$userId] = $relation ?? false;
	}

	public function get(): RelationCollection
	{
		if (!isset($this->fullRelations))
		{
			$this->fullRelations = RelationCollection::find(['CHAT_ID' => $this->chatId]);
		}

		return $this->fullRelations;
	}

	public function getManagerOnly(): RelationCollection
	{
		if (isset($this->fullRelations))
		{
			return $this->fullRelations->filter(fn (Relation $relation) => $relation->getManager());
		}

		return RelationCollection::find(['CHAT_ID' => $this->chatId, 'MANAGER' => 'Y']);
	}

	public function getByUserId(int $userId): ?Relation
	{
		if (isset($this->relationByUserId[$userId]))
		{
			return $this->relationByUserId[$userId] ?: null;
		}

		if (isset($this->fullRelations))
		{
			return $this->fullRelations->getByUserId($userId, $this->chatId);
		}

		$relations = RelationCollection::find(['CHAT_ID' => $this->chatId, 'USER_ID' => $userId]);
		$this->relationByUserId[$userId] = $relations->getByUserId($userId, $this->chatId) ?? false;

		return $this->relationByUserId[$userId] ?: null;
	}

	public function getByUserIds(array $userIds): RelationCollection
	{
		if (empty($userIds))
		{
			return new RelationCollection();
		}

		sort($userIds);

		$userIdsString = implode('|', $userIds);

		if (isset($this->relationsByUserIds[$userIdsString]))
		{
			return $this->relationsByUserIds[$userIdsString];
		}

		if (isset($this->fullRelations))
		{
			return $this->fullRelations->filter(fn (Relation $relation) => in_array($relation->getUserId(), $userIds, true));
		}

		$this->relationsByUserIds[$userIdsString] = RelationCollection::find(['CHAT_ID' => $this->chatId, 'USER_ID' => $userIds]);

		return $this->relationsByUserIds[$userIdsString];
	}

	public function getByReason(Reason $reason): RelationCollection
	{
		if (isset($this->fullRelations))
		{
			return $this->fullRelations->filter(fn (Relation $relation) => $relation->getReason() === $reason);
		}

		return RelationCollection::find(['CHAT_ID' => $this->chatId, 'REASON' => $reason->value]);
	}

	public function cleanCache(): void
	{
		unset($this->fullRelations);
		$this->relationByUserId = [];
		$this->relationsByUserIds = [];
	}

	public function onAfterRelationAdd(array $usersToAdd): void
	{
		//TODO: change to update cache for optimization
		$this->cleanCache();
	}

	public function onAfterRelationDelete(int $deletedUserId): void
	{
		$this->fullRelations->onAfterRelationDelete($this->chatId, $deletedUserId);
		unset($this->relationsByUserIds[$deletedUserId]);
		$this->relationsByUserIds = [];
	}

	public function getUserCount(): int
	{
		$fullRelations = $this->get();

		$count = 0;
		foreach ($fullRelations as $relation)
		{
			if (User::getInstance($relation->getUserId())->isActive())
			{
				$count++;
			}
		}

		return $count;
	}
}
