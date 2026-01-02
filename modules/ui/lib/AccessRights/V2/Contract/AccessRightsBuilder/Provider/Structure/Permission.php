<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure;

interface Permission
{
	public function getAction(): Action;

	public function getControl(): Control;
}
