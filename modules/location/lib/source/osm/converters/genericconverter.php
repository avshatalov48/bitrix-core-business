<?php

namespace Bitrix\Location\Source\Osm\Converters;

/**
 * Class GenericConverter
 * @package Bitrix\Location\Source\Osm\Converters
 * @internal
 *
 * @see https://wiki.openstreetmap.org/wiki/Tag:boundary%3Dadministrative
 */
final class GenericConverter extends BaseConverter
{
	/** @var int */
	private const LEVEL_1_ADMIN_LEVEL = 4;

	/**
	 * @inheritDoc
	 */
	protected function getAdminLevel1(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($this->isAdministrativeBoundary($addressComponent)
				&& $addressComponent['admin_level'] === static::LEVEL_1_ADMIN_LEVEL
			)
			{
				return $addressComponent;
			}
		}

		return null;
	}
}
