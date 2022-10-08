<?php

namespace Bitrix\Location\Entity\Address\Converter;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Model\EO_Address;
use Bitrix\Location\Model\EO_AddressField;
use Bitrix\Location\Model\EO_Address_Collection;
use Bitrix\Location\Model\EO_AddressField_Collection;
use Bitrix\Location\Model\EO_AddressLink;
use Bitrix\Location\Model\EO_AddressLink_Collection;

/**
 * Class OrmConverter
 * @package Bitrix\Location\Entity\Address\Converter
 * @internal
 */
final class OrmConverter
{
	/**
	 * Convert Address links to ORM collection
	 *
	 * @param Address $address
	 * @return EO_AddressLink_Collection
	 */
	public static function convertLinksToOrm(Address $address): EO_AddressLink_Collection
	{
		$result = new EO_AddressLink_Collection();

		/** @var Address\IAddressLink $link */
		foreach ($address->getLinks() as $link)
		{
			$result->add(
				(new EO_AddressLink())
					->setAddressId($address->getId())
					->setEntityId($link->getAddressLinkEntityId())
					->setEntityType($link->getAddressLinkEntityType())
			);
		}

		return $result;
	}

	/**
	 * Convert Address fields to ORM objects
	 *
	 * @param Address $address
	 * @return EO_AddressField_Collection
	 */
	public static function convertFieldsToOrm(Address $address): EO_AddressField_Collection
	{
		$result = new EO_AddressField_Collection();
		$normalizer = Address\Normalizer\Builder::build($address->getLanguageId());

		/** @var Address\Field $field */
		foreach ($address->getFieldCollection() as $field)
		{
			$value = $field->getValue();
			$result->add(
				(new EO_AddressField())
					->setType($field->getType())
					->setValue($field->getValue())
					->setAddressId($address->getId())
					->setValueNormalized( $normalizer->normalize($value))
			);
		}

		return $result;
	}

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