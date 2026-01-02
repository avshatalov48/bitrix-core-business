<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Relation;

class RelationChangeSet
{
	private array $newRelations = [];
	private array $newMembers = [];

	public function addNewRelation(int $userId, bool $isHidden): void
	{
		$this->newRelations[$userId] = $userId;
		if (!$isHidden)
		{
			$this->newMembers[$userId] = $userId;
		}
	}

	public function addNewMembers(int $userId): void
	{
		$this->newMembers[$userId] = $userId;
	}

	public function getNewRelations(): array
	{
		return $this->newRelations;
	}

	public function getNewMembers(): array
	{
		return $this->newMembers;
	}

	public function getAll(): array
	{
		return array_unique(array_merge($this->newRelations, $this->newMembers));
	}

	public function isEmpty(): bool
	{
		return empty($this->newRelations) && empty($this->newMembers);
	}
}