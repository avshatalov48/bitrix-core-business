<?php

namespace Bitrix\Location\Entity\Address\Converter;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Location;

/**
 * Class ArrayConverter
 * @package Bitrix\Location\Entity\Address\Converter
 * @internal
 */
final class ArrayConverter
{
	/**
	 * Convert Address to Array
	 *
	 * @param Address $address
	 * @param bool $convertLocation
	 * @return array
	 */
	public static function convertToArray(Address $address, $convertLocation = true): array
	{
		$result = [
			'id' => $address->getId(),
			'latitude' => $address->getLatitude(),
			'longitude' => $address->getLongitude(),
			'languageId' => $address->getLanguageId(),
			'fieldCollection' => self::convertFieldsToArray($address->getAllFieldsValues()),
			'links' => self::convertLinksToArray($address)
		];

		if($convertLocation && $location = $address->getLocation())
		{
			$result['location'] = Location\Converter\ArrayConverter::convertToArray($location);
		}

		return $result;
	}

	/**
	 * Convert Array to Address
	 *
	 * @param array $data
	 * @return Address
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertFromArray(array $data): Address
	{
		$result = (new Address((string)$data['languageId']))
			->setId((int)$data['id'])
			->setLatitude((string)$data['latitude'])
			->setLongitude((string)$data['longitude']);

		if(is_array($data['fieldCollection']))
		{
			foreach ($data['fieldCollection'] as $itemType => $itemValue)
			{
				$result->setFieldValue((int)$itemType, (string)$itemValue);
			}
		}

		if(is_array($data['links']))
		{
			foreach ($data['links'] as $link)
			{
				$result->addLink((string)$link['entityId'], (string)$link['entityType']);
			}
		}

		if(isset($data['location']))
		{
			if($location = Location::fromArray($data['location']))
			{
				$result->setLocation($location);
			}
		}

		return $result;
	}

	/**
	 * @param array $fieldsValues
	 * @return array
	 */
	protected static function convertFieldsToArray(array $fieldsValues): array
	{
		$result = [];

		foreach ($fieldsValues as $type => $value)
		{
			$result[$type] = $value;
		}

		return $result;
	}

	/**
	 * @param Address $address
	 * @return array
	 */
	protected static function convertLinksToArray(Address $address): array
	{
		$result = [];

		foreach ($address->getLinks() as $link)
		{
			$result[] = [
				'entityId' => $link->getAddressLinkEntityId(),
				'entityType' => $link->getAddressLinkEntityType()
			];
		}

		return $result;
	}
}