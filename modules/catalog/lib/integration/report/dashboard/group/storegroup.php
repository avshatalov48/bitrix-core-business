<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard\Group;

use Bitrix\Main\Localization\Loc;

class StoreGroup implements Group
{
	public function getGroupKey(): string
	{
		return 'catalog_inventory_management';
	}

	public function getGroupTitle(): string
	{
		return Loc::getMessage('STORE_GROUP_TITLE') ?? '';
	}
}
