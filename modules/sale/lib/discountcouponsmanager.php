<?php

namespace Bitrix\Sale;

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
