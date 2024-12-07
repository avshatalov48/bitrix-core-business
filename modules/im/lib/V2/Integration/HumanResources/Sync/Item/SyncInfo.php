<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\Item;

use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\HumanResources\Item\NodeRelation;

class SyncInfo
{
	public function __construct(
		public readonly EntityType $entityType,
		public readonly int $entityId,
		public readonly int $nodeId,
		public readonly bool $withChildNodes,
		public readonly SyncDirection $direction,
	) {}

	public static function createFromRow(array $row): self
	{
		return new static(
			EntityType::tryFrom($row['ENTITY_TYPE']),
			(int)$row['ENTITY_ID'],
			(int)$row['NODE_ID'],
			$row['WITH_CHILD_NODES'] === 'Y',
			SyncDirection::tryFrom($row['DIRECTION']),
		);
	}

	public static function createFromNodeRelation(NodeRelation $node, SyncDirection $direction): self
	{
		return new static(
			EntityType::CHAT,
			$node->entityId,
			$node->node->id,
			$node->withChildNodes,
			$direction,
		);
	}

	public static function createFromNodeMember(NodeMember $member, SyncDirection $direction): self
	{
		return new static(
			EntityType::USER,
			$member->entityId,
			$member->nodeId,
			false,
			$direction,
		);
	}
}