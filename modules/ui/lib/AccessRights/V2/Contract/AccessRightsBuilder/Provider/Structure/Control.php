<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightModel;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem\Type;

interface Control
{
	public function getType(): Type;

	public function convertUIValueToModelValue(int|string $value): mixed;

	public function convertModelValueToUIValue(RightModel $model): int|string;
}
