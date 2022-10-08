<?php

namespace Bitrix\Location\Entity\Location\Factory;

use Bitrix\Location\Entity\Address\Converter\OrmConverter;
use Bitrix\Location\Entity\Location;
use Bitrix\Location\Model\EO_Hierarchy_Collection;
use Bitrix\Location\Model\EO_Location;
use Bitrix\Location\Model\EO_Location_Collection;

final class OrmFactory
{
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

		foreach($ormLocation->getName() as $ormName)
		{
			if($ormName->getLanguageId() === $languageId || $ormName->getLanguageId() == '')
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

	public static function createCollection(EO_Location_Collection $collection, string $language)
	{
		$result = new Location\Collection();

		foreach ($collection as $item)
		{
			$result->addItem(
				self::createLocation($item, $language)
			);
		}
		return $result;
	}

	public static function createParentCollection(EO_Hierarchy_Collection $ormHierarchy, string $languageId)
	{
		$result = new Location\Parents();

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