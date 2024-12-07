<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Department;

final class Entity
{
	public function __construct(
		public readonly string $name,
		public readonly int $headUserID,
		public readonly ?int $id,
		public readonly ?int $depthLevel,
		public readonly ?int $parent,
		public readonly ?int $nodeId = null
	) {}
}
