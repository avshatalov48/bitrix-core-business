<?php

namespace Bitrix\Location\Entity\Address\Converter;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Model\EO_Address;
use Bitrix\Location\Model\EO_Address_Collection;
use Bitrix\Location\Model\EO_AddressLink;

/**
 * Class OrmConverter
 * @package Bitrix\Location\Entity\Address\Converter
 * @internal
 */
final class OrmConverter
{
	/**
	 * Convert ORM objects to Address
	 *
	 * @param EO_Address $ormAddress
	 * @return Address
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertFromOrm(EO_Address $ormAddress): Address
	{
		$result = new Address($ormAddress->getLanguageId());
		$result->setId($ormAddress->getId())
			->setLatitude($ormAddress->getLatitude())
			->setLongitude($ormAddress->getLongitude());

		/** @var Address\Field $field */
		foreach ($ormAddress->getFields() as $field)
		{
			$result->setFieldValue($field->getType(), $field->getValue());
		}

		if($ormLocation = $ormAddress->getLocation())
		{
			$location = \Bitrix\Location\Entity\Location\Converter\OrmConverter::createLocation(
				$ormLocation,
				$ormAddress->getLanguageId()
			);

			if($location)
			{
				$result->setLocation($location);
			}
		}

		if($links = $ormAddress->getLinks())
		{
			/** @var EO_AddressLink $link */
			foreach ($links as $link)
			{
				$result->addLink($link->getEntityId(), $link->getEntityType());
			}
		}

		return $result;
	}

	/**
	 * Convert ORM address collection to AddressCollection
	 *
	 * @param EO_Address_Collection $collection
	 * @return Address\AddressCollection
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertCollectionFromOrm(EO_Address_Collection $collection): Address\AddressCollection
	{
		$result = new Address\AddressCollection();

		/** @var  EO_Address $item */
		foreach ($collection as $item)
		{
			$result->addItem(self::convertFromOrm($item));
		}

		return $result;
	}
}
