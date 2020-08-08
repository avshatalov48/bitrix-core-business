<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Sale\PaySystem,
	Bitrix\Sale\Cashbox;

/**
 * Class Factory
 * @package Bitrix\Sale\Internals\Analytics
 */
final class Factory
{
	/**
	 * @param string $type
	 * @return Provider|null
	 */
	public static function create(string $type): ?Provider
	{
		if ($type === PaySystem\Internals\Analytics\Provider::getCode())
		{
			return new PaySystem\Internals\Analytics\Provider();
		}

		if ($type === Cashbox\Internals\Analytics\Provider::getCode())
		{
			return new Cashbox\Internals\Analytics\Provider;
		}

		return null;
	}
}
