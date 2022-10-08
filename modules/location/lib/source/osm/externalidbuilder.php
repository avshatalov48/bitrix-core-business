<?php

namespace Bitrix\Location\Source\Osm;

/**
 * Class ExternalIdBuilder
 * @package Bitrix\Location\Source\Osm
 * @internal
 */
final class ExternalIdBuilder
{
	/**
	 * @param string $osmType
	 * @param int $osmId
	 * @return string|null
	 */
	public static function buildExternalId(string $osmType, int $osmId): ?string
	{
		if (!$osmType || !$osmId)
		{
			return null;
		}

		return sprintf('%s%s', $osmType, $osmId);
	}

	/**
	 * @param string $externalId
	 * @return string
	 */
	public static function getOsmTypeByExternalId(string $externalId): ?string
	{
		return (string)mb_substr($externalId, 0, 1);
	}

	/**
	 * @param string $externalId
	 * @return int|null
	 */
	public static function getOsmIdByExternalId(string $externalId): ?int
	{
		return (int)mb_substr($externalId, 1);
	}
}
