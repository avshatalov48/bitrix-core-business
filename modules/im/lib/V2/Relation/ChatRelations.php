<?php

namespace Bitrix\Im\V2\Relation;

use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;

class ChatRelations
{
	protected const NEED_ADDITIONAL_FILTER_BY_ACCESS = false;

	/**
	 * @var self[]
	 */
	private static array $instances = [];

	protected int $chatId;
	/**
	 * @var Relation[]
	 */
	private array $relationByUserId = [];
	/**
	 * @var RelationCollection[]
	 */
	private array $relationsByUserIds = [];
	private RelationCollection $fullRelations;
	private RelationCollection $rawFullRelations;

	private function __construct(int $chatId)
	{
		$this->chatId = $chatId;
	}

	public static function getInstance(int $chatId): static
	{
		self::$instances[$chatId] ??= new static($chatId);

		return self::$instances[$chatId];
	}

	public function forceRelations(RelationCollection $relations): void
	{
		$this->cleanCache();
		$this->fullRelations = $relations;
	}

	public function filterUserIdsByAccess(array $userIds): array
	{
		return $userIds;
	}

	protected function filterRelationsByAccess(RelationCollection $relations): RelationCollection
	{
		if (!static::NEED_ADDITIONAL_FILTER_BY_ACCESS)
		{
			return $relations;
		}

		$usersWithAccess = $this->filterUserIdsByAccess($relations->getUserIds());

		return $relations->filter(fn (Relation $relation) => in_array($relation->getUserId(), $usersWithAccess, true));
	}

	public function preloadUserRelation(int $userId, ?Relation $relation): void
	{
		$this->relationByUserId[$userId] = $relation ?? false;
	}

	public function get(): RelationCollection
	{
		if (!isset($this->fullRelations))
		{
			$this->fullRelations = $this->loadFullRelations();
		}

		return $this->fullRelations;
	}

	public function getRawFullRelations(): RelationCollection
	{
		$this->rawFullRelations ??= RelationCollection::find(['CHAT_ID' => $this->chatId]);

		return $this->rawFullRelations;
	}

	protected function loadFullRelations(): RelationCollection
	{
		return $this->filterRelationsByAccess($this->getRawFullRelations());
	}

	public function getManagerOnly(): RelationCollection
	{
		if (isset($this->fullRelations))
		{
			return $this->fullRelations->filter(fn (Relation $relation) => $relation->getManager());
		}

		return $this->loadManagersOnly();
	}

	protected function loadManagersOnly(): RelationCollection
	{
		return $this->filterRelationsByAccess(RelationCollection::find(['CHAT_ID' => $this->chatId, 'MANAGER' => 'Y']));
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

		$relations = $this->loadByUserId($userId);
		$this->relationByUserId[$userId] = $relations->getByUserId($userId, $this->chatId) ?? false;

		return $this->relationByUserId[$userId] ?: null;
	}

	protected function loadByUserId(int $userId): RelationCollection
	{
		return $this->filterRelationsByAccess(RelationCollection::find(['CHAT_ID' => $this->chatId, 'USER_ID' => $userId]));
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

		$this->relationsByUserIds[$userIdsString] = $this->loadByUserIds($userIds);

		return $this->relationsByUserIds[$userIdsString];
	}

	protected function loadByUserIds(array $userIds): RelationCollection
	{
		if (empty($userIds))
		{
			return new RelationCollection();
		}

		return $this->filterRelationsByAccess(RelationCollection::find(['CHAT_ID' => $this->chatId, 'USER_ID' => $userIds]));
	}

	public function getByReason(Reason $reason): RelationCollection
	{
		if (isset($this->fullRelations))
		{
			return $this->fullRelations->filter(fn (Relation $relation) => $relation->getReason() === $reason);
		}

		return $this->loadByReason($reason);
	}

	protected function loadByReason(Reason $reason): RelationCollection
	{
		return $this->filterRelationsByAccess(RelationCollection::find(['CHAT_ID' => $this->chatId, 'REASON' => $reason->value]));
	}

	public function cleanCache(): void
	{
		unset($this->fullRelations, $this->rawFullRelations);
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
		$this->rawFullRelations->onAfterRelationDelete($this->chatId, $deletedUserId);
		unset($this->relationsByUserIds[$deletedUserId]);
		$this->relationsByUserIds = [];
	}

	public function getUserCount(): int
	{
		$fullRelations = $this->get();

		$count = 0;
		foreach ($fullRelations as $relation)
		{
			if (!$relation->isHidden() && $relation->getUser()->isActive())
			{
				$count++;
			}
		}

		return $count;
	}
}
