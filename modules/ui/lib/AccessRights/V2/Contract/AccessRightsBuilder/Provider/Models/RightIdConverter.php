<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Entity;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Permission;

interface RightIdConverter
{
	public function buildUIId(Entity $entity, Permission $permission): string;

	public function parseUIId(string $uiId): ?RightId;

	public function parseRightModel(RightModel $model): ?RightId;
}
