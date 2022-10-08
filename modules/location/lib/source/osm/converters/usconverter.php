<?php

namespace Bitrix\Location\Source\Osm\Converters;

/**
 * Class UsConverter
 * @package Bitrix\Location\Source\Osm\Converters
 * @internal
 *
 * @see https://wiki.openstreetmap.org/wiki/Tag:boundary%3Dadministrative
 * @see https://en.wikipedia.org/wiki/Political_divisions_of_the_United_States#:~:text=Political%20divisions%20of%20the%20United%20States%20are%20the%20various%20recognized,Columbia%2C%20territories%20and%20Indian%20reservations.
 */
final class UsConverter extends BaseConverter
{
	/** @var int */
	private const STATE_ADMIN_LEVEL = 4;

	/** @var int */
	private const COUNTY_ADMIN_LEVEL = 6;

	/**
	 * @var int
	 *
	 * @see https://www.openstreetmap.org/relation/61320
	 */
	private const NY_STATE_RELATION_ID = 61320;

	/** @var int */
	private const NY_CITY_ADMIN_LEVEL = 5;

	/**
	 * @inheritDoc
	 */
	protected function getAdminLevel1(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($this->isAdministrativeBoundary($addressComponent)
				&& $addressComponent['admin_level'] === static::STATE_ADMIN_LEVEL
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
	protected function getAdminLevel2(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($this->isAdministrativeBoundary($addressComponent)
				&& $addressComponent['admin_level'] === static::COUNTY_ADMIN_LEVEL
			)
			{
				return $addressComponent;
			}
		}

		return null;
	}

	/**
	 * @return array|null
	 */
	protected function getLocalityConcrete(): ?array
	{
		if ($this->isCityState())
		{
			foreach ($this->addressComponents as $addressComponent)
			{
				if ($addressComponent['class'] === 'boundary'
					&& $addressComponent['type'] === 'administrative'
					&& $addressComponent['admin_level'] === static::NY_CITY_ADMIN_LEVEL
				)
				{
					return $addressComponent;
				}
			}
		}

		return parent::getLocalityConcrete();
	}

	/**
	 * @inheritDoc
	 */
	protected function getCityStateRelationIds(): array
	{
		return [
			static::NY_STATE_RELATION_ID,
		];
	}
}
