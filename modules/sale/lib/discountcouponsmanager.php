<?php
namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DiscountCouponsManager extends DiscountCouponsManagerBase
{
	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}
}