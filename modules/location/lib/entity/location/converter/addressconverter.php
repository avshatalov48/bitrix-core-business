<?php

namespace Bitrix\Location\Entity\Location\Converter;

use Bitrix\Location\Entity;
use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Location;

/**
 * Class AddressConverter
 * @package Bitrix\Location\Entity\Location\Converter
 * @internal
 */
final class AddressConverter
{
	/**
	 * Convert Location to Address
	 *
	 * @param Location $location
	 * @return Address
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertToAddress(Entity\Location $location): Address
	{
		$type = $location->getType() === Location\Type::UNKNOWN ? Address\FieldType::ADDRESS_LINE_2 : $location->getType();

		$result = (new Address($location->getLanguageId()))
			->setLatitude($location->getLatitude())
			->setLongitude($location->getLongitude())
			->setFieldValue($type, $location->getName());

		if($parents = $location->getParents())
		{
			/** @var Location $parent */
			foreach ($parents as $parent)
			{
				$result->setFieldValue($parent->getType(), $parent->getName());
			}
		}

		if($fields = $location->getAllFieldsValues())
		{
			foreach($fields as $type => $value)
			{
				if(!$result->isFieldExist($type))
				{
					$result->setFieldValue($type, $value);
				}
			}
		}

		return $result;
	}
}