<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models;

interface RightModel
{
	public function getValue(): string|int;
}
