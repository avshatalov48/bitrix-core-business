<?php

namespace Bitrix\Location\Entity\Location\Converter;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Model\EO_Hierarchy;
use Bitrix\Location\Model\EO_Hierarchy_Collection;
use Bitrix\Location\Model\EO_Location;
use Bitrix\Location\Model\EO_Location_Collection;
use Bitrix\Location\Model\EO_LocationField_Collection;
use Bitrix\Location\Model\EO_LocationName;
use Bitrix\Location\Model\LocationFieldTable;

/**
 * Class OrmConverter
 * @package Bitrix\Location\Entity\Location\Converter
 * @internal
 */
final class OrmConverter
{
	/**
	 * Convert Location fields to ORM collection
	 *
	 * @param Location $location
	 * @return EO_LocationField_Collection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertFieldsToOrm(Location $location): EO_LocationField_Collection
	{
		$result = LocationFieldTable::createCollection();

		/** @var Location\Field $field */
		foreach ($location->getFieldCollection() as $field)
		{
			$result->add(
				(LocationFieldTable::createObject())
					->setType($field->getType())
					->setValue($field->getValue())
					->setLocationId($location->getId())
			);
		}

		return $result;
	}

	/**
	 * Convert EO_Location to Location
	 *
	 * @param EO_Location $ormLocation
	 * @param string $languageId
	 * @return Location
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function createLocation(EO_Location $ormLocation, string $languageId): Location
	{
		$result = (new Location())
			->setId($ormLocation->getId())
			->setCode($ormLocation->getCode())
			->setExternalId($ormLocation->getExternalId())
			->setSourceCode($ormLocation->getSourceCode())
			->setType(($ormLocation->getType()))
			->setLatitude($ormLocation->getLatitude())
			->setLongitude($ormLocation->getLongitude());

		if($fields = $ormLocation->getFields())
		{
			/** @var Location\Field $field */
			foreach($fields as $field)
			{
				$result->setFieldValue($field->getType(), $field->getValue());
			}
		}

		/** @var  EO_LocationName $ormName */
		foreach($ormLocation->getName() as $ormName)
		{
			if($ormName->getLanguageId() === $languageId || $ormName->getLanguageId() === '')
			{
				$result->setName($ormName->getName());
				$result->setLanguageId($ormName->getLanguageId());

				if($ormName->getLanguageId() === $languageId)
				{
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Convert EO_Location_Collection to Location\Collection
	 *
	 * @param EO_Location_Collection $collection
	 * @param string $language
	 * @return Location\Collection
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function createCollection(EO_Location_Collection $collection, string $language): Location\Collection
	{
		$result = new Location\Collection();

		/** @var EO_Location $item */
		foreach ($collection as $item)
		{
			$result->addItem(
				self::createLocation($item, $language)
			);
		}
		return $result;
	}

	/**
	 * Convert EO_Hierarchy_Collection to Location\Parents
	 *
	 * @param EO_Hierarchy_Collection $ormHierarchy
	 * @param string $languageId
	 * @return Location\Parents
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function createParentCollection(EO_Hierarchy_Collection $ormHierarchy, string $languageId): Location\Parents
	{
		$result = new Location\Parents();

		/** @var EO_Hierarchy $item */
		foreach ($ormHierarchy as $item)
		{
			$result->addItem(
				self::createLocation(
					$item->getAncestor(),
					$languageId
				)
			);
		}

		return $result;
	}
}