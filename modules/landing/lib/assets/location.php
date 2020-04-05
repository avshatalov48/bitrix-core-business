<?php

namespace Bitrix\Landing\Assets;

class Location
{
	public const LOCATION_BEFORE_ALL = 1;    // before all (critical). 1 to not equal with empty values (e.g. (int)null)
	public const LOCATION_KERNEL = 50;        // first
	public const LOCATION_TEMPLATE = 100;    // DEFAULT place
	public const LOCATION_AFTER_TEMPLATE = 150;    // last

	/**
	 * Return default location
	 * @return string
	 */
	public static function getDefaultLocation(): string
	{
		return self::LOCATION_TEMPLATE;
	}

	/**
	 * Available locations for assets adding
	 *
	 * @return array
	 */
	public static function getAllLocations(): array
	{
		return [
			self::LOCATION_BEFORE_ALL,
			self::LOCATION_KERNEL,
			self::LOCATION_TEMPLATE,
			self::LOCATION_AFTER_TEMPLATE,
		];
	}


	/**
	 * Check correctly location number
	 *
	 * @param mixed $location	- location to verify
	 * @return int
	 */
	public static function verifyLocation($location): int
	{
		if (
			!isset($location)
			|| !in_array($location, self::getAllLocations(), true)
		)
		{
			return self::getDefaultLocation();
		}

		return $location;
	}
}