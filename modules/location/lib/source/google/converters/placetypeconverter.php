<?php

namespace Bitrix\Location\Source\Google\Converters;

use Bitrix\Location\Entity\Address\FieldType;

/**
 * Converts Google places types to one of \Bitrix\Location\Entity\Location\FieldType
 * Class PlaceTypes
 * @package Bitrix\Location\Source\Google\Converters
 */
final class PlaceTypeConverter
{
	protected static $types = [
		'country' => FieldType::COUNTRY,
		'locality' => FieldType::LOCALITY,
		'postal_town' => FieldType::LOCALITY,
		'route' => FieldType::STREET,
		'street_address' => FieldType::ADDRESS_LINE_1,
		'administrative_area_level_4' => FieldType::ADM_LEVEL_4,
		'administrative_area_level_3' => FieldType::ADM_LEVEL_3,
		'administrative_area_level_2' => FieldType::ADM_LEVEL_2,
		'administrative_area_level_1' => FieldType::ADM_LEVEL_1,
		'floor' => FieldType::FLOOR,
		'postal_code' => FieldType::POSTAL_CODE,
		'room' => FieldType::ROOM,
		'sublocality' => FieldType::SUB_LOCALITY,
		'sublocality_level_1' => FieldType::SUB_LOCALITY_LEVEL_1,
		'sublocality_level_2' => FieldType::SUB_LOCALITY_LEVEL_2,
		'street_number' => FieldType::BUILDING,
		'premise' => FieldType::BUILDING,
        'subpremise' => FieldType::ADDRESS_LINE_2,
	];

	/**
	 * @param string $type
	 * @return int LocationType
	 */
	public static function convert(string $type): int
	{
		return self::$types[$type] ?? FieldType::UNKNOWN;
	}
}
