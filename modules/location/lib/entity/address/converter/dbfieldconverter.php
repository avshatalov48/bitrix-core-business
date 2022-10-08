<?php

namespace Bitrix\Location\Entity\Address\Converter;

use Bitrix\Location\Entity;

/**
 * Class DbFieldConverter
 * @package Bitrix\Location\Entity\Address\Converter
 * @internal
 */
final class DbFieldConverter
{
	/**
	 * Convert Address to DB fields array
	 *
	 * @param Entity\Address $address
	 * @return array
	 */
	public static function convertToDbField(Entity\Address $address): array
	{
		$locationId = 0;

		if ($location = $address->getLocation())
		{
			$locationId = $location->getId();
		}

		$latitude = $address->getLatitude();
		$longitude = $address->getLongitude();

		return [
			'ID' => $address->getId(),
			'LOCATION_ID' => $locationId,
			'LANGUAGE_ID' => $address->getLanguageId(),
			'LATITUDE' => $latitude === '' ? null : (float)$latitude,
			'LONGITUDE' => $longitude === '' ? null : (float)$longitude,
		];
	}
}
