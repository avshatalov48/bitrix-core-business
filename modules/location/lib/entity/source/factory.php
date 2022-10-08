<?php

namespace Bitrix\Location\Entity\Source;

use Bitrix\Location\Entity\Source;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Source\Google\GoogleSource;
use Bitrix\Location\Source\Osm\OsmSource;

/**
 * Class Factory
 * @package Bitrix\Location\Entity\Source
 * @internal
 */
final class Factory
{
	public const GOOGLE_SOURCE_CODE = 'GOOGLE';
	public const OSM_SOURCE_CODE = 'OSM';

	/**
	 * @param string $code
	 * @return Source
	 */
	public static function makeSource(string $code): Source
	{
		$class = null;

		switch ($code)
		{
			case static::GOOGLE_SOURCE_CODE:
				$class = GoogleSource::class;
				break;
			case static::OSM_SOURCE_CODE:
				$class = OsmSource::class;
				break;
		}

		if (is_null($class))
		{
			throw new RuntimeException(sprintf('Unexpected source code - %s', $code));
		}

		/** @var Source $source */
		return (new $class())->setCode($code);
	}
}
