<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main\SystemException,
	Bitrix\Sale\PaySystem,
	Bitrix\Sale\Cashbox,
	Bitrix\Sale\Delivery;

/**
 * Class Factory
 * @package Bitrix\Sale\Internals\Analytics
 * @internal
 */
final class Factory
{
	/**
	 * @param string $type
	 * @return Cashbox\Internals\Analytics\Provider|PaySystem\Internals\Analytics\Provider
	 * @throws SystemException
	 */
	public static function create(string $type)
	{
		$knowProvidersMap = self::getKnownProvidersMap();
		if (!isset($knowProvidersMap[$type]))
		{
			throw new SystemException("Provider with type \"{$type}\" not found");
		}

		return new $knowProvidersMap[$type]();
	}

	/**
	 * @return string[]
	 */
	private static function getKnownProvidersMap(): array
	{
		$result = [];

		$knownClasses = [
			PaySystem\Internals\Analytics\Provider::class,
			Cashbox\Internals\Analytics\Provider::class,
			Delivery\Internals\Analytics\Provider::class,
		];

		/** @var Provider $knownClass */
		foreach ($knownClasses as $knownClass)
		{
			$result[$knownClass::getCode()] = $knownClass;
		}

		return $result;
	}
}
