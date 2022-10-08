<?php

namespace Bitrix\Location\Source\Osm\Converters;

/**
 * Class RuConverter
 * @package Bitrix\Location\Source\Osm\Converters
 * @internal
 *
 * @see https://wiki.openstreetmap.org/wiki/Tag:boundary%3Dadministrative
 * @see https://ru.wikipedia.org/wiki/%D0%A0%D0%B0%D0%B9%D0%BE%D0%BD%D1%8B_%D1%81%D1%83%D0%B1%D1%8A%D0%B5%D0%BA%D1%82%D0%BE%D0%B2_%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D0%B9%D1%81%D0%BA%D0%BE%D0%B9_%D0%A4%D0%B5%D0%B4%D0%B5%D1%80%D0%B0%D1%86%D0%B8%D0%B8#.D0.A1.D0.BF.D0.B8.D1.81.D0.BE.D0.BA_.D1.80.D0.B0.D0.B9.D0.BE.D0.BD.D0.BE.D0.B2
 */
final class RuConverter extends BaseConverter
{
	/** @var int */
	private const FEDERAL_SUBJECT_ADMIN_LEVEL = 4;

	/** @var int */
	private const FEDERAL_SUBJECT_MUNICIPAL_DISTRICT_ADMIN_LEVEL = 6;

	/** @var int */
	private const CITY_FEDERAL_SUBJECT_MUNICIPAL_DISTRICT_ADMIN_LEVEL = 8;

	/**
	 * @var int
	 *
	 * @see https://www.openstreetmap.org/relation/102269
	 */
	private const MOSCOW_STATE_RELATION_ID = 102269;

	/**
	 * @var int
	 *
	 * @see https://www.openstreetmap.org/relation/337422
	 */
	private const SAINT_PETERSBURG_STATE_RELATION_ID = 337422;

	/**
	 * @var int
	 *
	 * @see https://www.openstreetmap.org/relation/1574364
	 */
	private const SEVASTOPOL_STATE_RELATION_ID = 1574364;

	/**
	 * @inheritDoc
	 */
	protected function getAdminLevel1(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($this->isAdministrativeBoundary($addressComponent)
				&& $addressComponent['admin_level'] === static::FEDERAL_SUBJECT_ADMIN_LEVEL
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
		$municipalDistrictAdminLevel = $this->isCityState()
			? static::CITY_FEDERAL_SUBJECT_MUNICIPAL_DISTRICT_ADMIN_LEVEL
			: static::FEDERAL_SUBJECT_MUNICIPAL_DISTRICT_ADMIN_LEVEL;

		foreach ($this->addressComponents as $addressComponent)
		{
			if ($this->isAdministrativeBoundary($addressComponent)
				&& $addressComponent['admin_level'] === $municipalDistrictAdminLevel
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
			static::MOSCOW_STATE_RELATION_ID,
			static::SAINT_PETERSBURG_STATE_RELATION_ID,
			static::SEVASTOPOL_STATE_RELATION_ID,
		];
	}
}
