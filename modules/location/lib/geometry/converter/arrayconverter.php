<?php

namespace Bitrix\Location\Geometry\Converter;

use Bitrix\Location\Geometry\Type\BaseGeometry;
use Bitrix\Location\Geometry\Type\LineString;
use Bitrix\Location\Geometry\Type\MultiLineString;
use Bitrix\Location\Geometry\Type\MultiPoint;
use Bitrix\Location\Geometry\Type\MultiPolygon;
use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Location\Geometry\Type\Polygon;

class ArrayConverter extends Converter
{
	/**
	 * @param $input
	 * @return BaseGeometry|null
	 */
	public function read($input): ?BaseGeometry
	{
		if (
			!is_array($input)
			|| !isset($input['type'])
			|| !is_string($input['type'])
		)
		{
			return null;
		}

		$method = 'arrayTo' . $input['type'];

		return method_exists($this, $method)
			? $this->$method($input['coordinates'])
			: null;
	}

	/**
	 * @inheritDoc
	 */
	public function write(BaseGeometry $geometry)
	{
		return $this->getArray($geometry);
	}

	/**
	 * @param BaseGeometry $geometry
	 * @return array
	 */
	private function getArray($geometry): array
	{
		return [
			'type' => $geometry->getGeometryType(),
			'coordinates' => $geometry->asArray(),
		];
	}

	/**
	 * @param array $array
	 * @return Point
	 */
	private function arrayToPoint(array $array): Point
	{
		return new Point($array[1], $array[0]);
	}

	/**
	 * @param array $array
	 * @return LineString
	 */
	private function arrayToLineString(array $array): LineString
	{
		$points = [];

		foreach ($array as $item)
		{
			$points[] = $this->arrayToPoint($item);
		}

		return new LineString($points);
	}

	/**
	 * @param array $array
	 * @return Polygon
	 */
	private function arrayToPolygon(array $array): Polygon
	{
		$lines = [];

		foreach ($array as $item)
		{
			$lines[] = $this->arrayToLineString($item);
		}

		return new Polygon($lines);
	}

	/**
	 * @param array $array
	 * @return MultiPoint
	 */
	private function arrayToMultiPoint(array $array): MultiPoint
	{
		$points = [];

		foreach ($array as $item)
		{
			$points[] = $this->arrayToPoint($item);
		}

		return new MultiPoint($points);
	}

	/**
	 * @param array $array
	 * @return MultiLineString
	 */
	private function arrayToMultiLineString(array $array): MultiLineString
	{
		$lines = [];

		foreach ($array as $item)
		{
			$lines[] = $this->arrayToLineString($item);
		}

		return new MultiLineString($lines);
	}

	/**
	 * @param array $array
	 * @return MultiPolygon
	 */
	private function arrayToMultiPolygon(array $array): MultiPolygon
	{
		$polys = [];

		foreach ($array as $item)
		{
			$polys[] = $this->arrayToPolygon($item);
		}

		return new MultiPolygon($polys);
	}
}
