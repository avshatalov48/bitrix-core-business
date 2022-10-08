<?php

namespace Bitrix\Location\Source\Osm\Converters;

/**
 * Class BrConverter
 * @package Bitrix\Location\Source\Osm\Converters
 * @internal
 *
 * @see https://wiki.openstreetmap.org/wiki/Tag:boundary%3Dadministrative
 * @see https://en.wikipedia.org/wiki/States_of_Brazil
 * @see https://pt.wikipedia.org/wiki/Subprefeitura
 * @see https://pt.wikipedia.org/wiki/Sub-bairro
 */
final class BrConverter extends BaseConverter
{
	/** @var int */
	private const STATE_ADMIN_LEVEL = 4;

	/** @var int */
	private const DISTRICT_ADMIN_LEVEL = 9;

	/** @var int */
	private const SUB_DISTRICT_ADMIN_LEVEL = 10;

	/**
	 * @inheritDoc
	 */
	protected function getAdminLevel1(): ?array
	{
		return $this->getBoundaryAdministrativeByLevel(static::STATE_ADMIN_LEVEL);
	}

	/**
	 * @inheritDoc
	 */
	protected function getSubLocality(): ?array
	{
		return $this->getBoundaryAdministrativeByLevel(static::DISTRICT_ADMIN_LEVEL);
	}

	/**
	 * @inheritDoc
	 */
	protected function getSubLocalityLevel1(): ?array
	{
		return $this->getBoundaryAdministrativeByLevel(static::SUB_DISTRICT_ADMIN_LEVEL);
	}
}
