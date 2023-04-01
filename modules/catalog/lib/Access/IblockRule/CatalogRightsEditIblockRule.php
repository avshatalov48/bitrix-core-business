<?php

namespace Bitrix\Catalog\Access\IblockRule;

use Bitrix\Catalog\Access\ShopGroupAssistant;

class CatalogRightsEditIblockRule extends BaseIblockRule
{
	/**
	 * @return array
	 */
	protected function getShopIblockTypes(): array
	{
		return [ShopGroupAssistant::SHOP_ADMIN_USER_GROUP_CODE];
	}
}
