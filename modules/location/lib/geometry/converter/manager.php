<?php

namespace Bitrix\Location\Geometry\Converter;

use Bitrix\Location\Geometry\Type\BaseGeometry;
use Bitrix\Main\SystemException;

class Manager
{
	public const FORMAT_GEOJSON = 'geojson';
	public const FORMAT_ARRAY = 'array';

	/**
	 * @param $input
	 * @param string $format
	 * @return BaseGeometry|null
	 */
	public static function read($input, string $format): ?BaseGeometry
	{
		return self::makeConverter($format)->read($input);
	}

	/**
	 * @param BaseGeometry $geometry
	 * @param string $format
	 * @return mixed
	 */
	public static function write(BaseGeometry $geometry, string $format)
	{
		return self::makeConverter($format)->write($geometry);
	}

	/**
	 * @param string $format
	 * @return Converter
	 * @throws SystemException
	 */
	public static function makeConverter(string $format): Converter
	{
		$map = [
			self::FORMAT_GEOJSON => GeoJsonConverter::class,
			self::FORMAT_ARRAY => ArrayConverter::class,
		];

		if (!isset($map[$format]))
		{
			throw new SystemException('Converter has not been found');
		}

		return new $map[$format];
	}
}
