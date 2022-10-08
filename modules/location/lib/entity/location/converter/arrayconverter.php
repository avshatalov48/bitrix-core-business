<?php

namespace Bitrix\Location\Entity\Location\Converter;

use Bitrix\Location\Entity;
use Bitrix\Location\Entity\Location;

/**
 * Class ArrayConverter
 * @package Bitrix\Location\Entity\Location\Converter
 * @internal
 */
final class ArrayConverter
{
	/**
	 * Convert Location to Array
	 *
	 * @param Location $location
	 * @return array
	 */
	public static function convertToArray(Entity\Location $location): array
	{
		if($address = $location->getAddress())
		{
			$address = Entity\Address\Converter\ArrayConverter::convertToArray($address, false);
		}

		return [
			'id' => $location->getId(),
			'code' => $location->getCode(),
			'externalId' => $location->getExternalId(),
			'sourceCode' => $location->getSourceCode(),
			'type' => $location->getType(),
			'name' => $location->getName(),
			'languageId' => $location->getLanguageId(),
			'latitude' => $location->getLatitude(),
			'longitude' => $location->getLongitude(),
			'fieldCollection' => self::convertFieldsToArray($location->getAllFieldsValues()),
			'address' => $address
		];
	}

	/**
	 * @param array $fieldsValues
	 * @return array
	 */
	private static function convertFieldsToArray(array $fieldsValues): array
	{
		$result = [];

		foreach ($fieldsValues as $type => $value)
		{
			$result[$type] = $value;
		}

		return $result;
	}

	/**
	 * Convert Parents to array
	 *
	 * @param Location\Parents $parents
	 * @return array
	 */
	public static function convertParentsToArray(Entity\Location\Parents $parents): array
	{
		$result = [];

		foreach ($parents as $location)
		{
			$result[] = ArrayConverter::convertToArray($location);
		}

		return $result;
	}

	/**
	 * Convert Array to Location
	 *
	 * @param array $data
	 * @return Location
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertFromArray(array $data): Location
	{
		$result = (new Location())
			->setId((int)$data['id'])
			->setCode((string)$data['code'])
			->setExternalId((string)$data['externalId'])
			->setSourceCode((string)$data['sourceCode'])
			->setType((int)$data['type'])
			->setName((string)$data['name'])
			->setLanguageId((string)$data['languageId'])
			->setLatitude((string)$data['latitude'])
			->setLongitude((string)$data['longitude']);

		if(is_array($data['fieldCollection']))
		{
			foreach ($data['fieldCollection'] as $itemType => $itemValue)
			{
				$result->setFieldValue($itemType, (string)$itemValue);
			}
		}

		return $result;
	}
}