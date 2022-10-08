<?php

namespace Bitrix\Location\Entity\Location\Converter;

use Bitrix\Location\Entity;
use Bitrix\Location\Entity\Address\Normalizer\Builder;

/**
 * Class DbFieldConverter
 * @package Bitrix\Location\Entity\Location\Converter
 * @internal
 */
final class DbFieldConverter
{
	/**
	 * Convert Location to DB fields
	 *
	 * @param Entity\Location $location
	 * @return array
	 */
	public static function convertToDbFields(Entity\Location $location): array
	{
		$result = [];

		if (($location->getId() > 0))
		{
			$result['ID'] = $location->getId();
		}

		$latitude = $location->getLatitude();
		$longitude = $location->getLongitude();

		$result['CODE'] = $location->getCode();
		$result['EXTERNAL_ID'] = $location->getExternalId();
		$result['SOURCE_CODE'] = $location->getSourceCode();
		$result['TYPE'] = $location->getType();
		$result['LATITUDE'] = $latitude === '' ? null : (float)$latitude;
		$result['LONGITUDE'] = $longitude === '' ? null : (float)$longitude;

		return $result;
	}

	/**
	 * Convert LocationName to DB fields
	 *
	 * @param Entity\Location $location
	 * @return array
	 */
	public static function convertNameToDbFields(Entity\Location $location): array
	{
		$normalizer = Builder::build($location->getLanguageId());

		return [
			'NAME' => $location->getName(),
			'LANGUAGE_ID' => $location->getLanguageId(),
			'LOCATION_ID' => $location->getId(),
			'NAME_NORMALIZED' => $normalizer->normalize(
				$location->getName()
			)
		];
	}
}