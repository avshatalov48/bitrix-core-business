<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightId;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightIdConverter;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightModel;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Entity;
use Bitrix\UI\AccessRights\V2\Dto\AccessRightsBuilder\UserGroupModelDto;

interface Provider
{
	/**
	 * @return Entity[]
	 */
	public function loadEntities(): array;

	/**
	 * @return UserGroupModelDto[]
	 */
	public function loadUserGroupModels(): array;

	public function createRightModelByRightId(RightId $id, mixed $value): ?RightModel;

	public function getRightIdConverter(): RightIdConverter;
}
