<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Relation\Provider;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Main\Validation\Rule\PositiveNumber;

class RelationCursor implements RestConvertible
{
	public function __construct(
		#[InArray([Chat::ROLE_MEMBER, Chat::ROLE_MANAGER, Chat::ROLE_OWNER])]
		public readonly string $role,
		#[PositiveNumber]
		public readonly int $relationId,
	){}

	public static function getRestEntityName(): string
	{
		return 'nextCursor';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'role' => mb_strtolower($this->role),
			'relationId' => $this->relationId,
		];
	}

	public static function createFromArray(array $parameters): static
	{
		return new static(mb_strtoupper($parameters['role'] ?? ''), (int)($parameters['relationId'] ?? 0));
	}

	public static function getNext(RelationCollection $relations, int $limit): ?static
	{
		if ($relations->count() < $limit)
		{
			return null;
		}

		$maxRole = null;
		$maxRolePriority = 0;
		$maxId = 0;

		foreach ($relations as $relation)
		{
			$rolePriority = RelationProvider::ROLE_PRIORITY_MAP[$relation->getRole()];
			if ($rolePriority > $maxRolePriority)
			{
				$maxRole = $relation->getRole();
				$maxRolePriority = $rolePriority;
				$maxId = $relation->getId();
			}
			if ($relation->getId() > $maxId)
			{
				$maxId = $relation->getId();
			}
		}

		if ($maxRole === null || $maxId === null)
		{
			return null;
		}

		return new RelationCursor($maxRole, $maxId);
	}
}