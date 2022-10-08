<?php

namespace Bitrix\Location\Source\Osm\Converters;

/**
 * Class DeConverter
 * @package Bitrix\Location\Source\Osm\Converters
 * @internal
 *
 * @see https://wiki.openstreetmap.org/wiki/Tag:boundary%3Dadministrative
 * @see https://en.wikipedia.org/wiki/States_of_Germany
 */
final class DeConverter extends BaseConverter
{
	/** @var int */
	private const LAND_ADMIN_LEVEL = 4;

	/**
	 * @var int
	 *
	 * @see https://www.openstreetmap.org/relation/62422
	 */
	private const BERLIN_LAND_RELATION_ID = 62422;

	/**
	 * @var int
	 *
	 * @see https://www.openstreetmap.org/relation/62782
	 */
	private const HAMBURG_LAND_RELATION_ID = 62782;

	/**
	 * @inheritDoc
	 */
	protected function getAdminLevel1(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($this->isAdministrativeBoundary($addressComponent)
				&& $addressComponent['admin_level'] === static::LAND_ADMIN_LEVEL
			)
			{
				return $addressComponent;
			}
		}

		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getCityStateRelationIds(): array
	{
		return [
			static::BERLIN_LAND_RELATION_ID,
			static::HAMBURG_LAND_RELATION_ID,
		];
	}
}
