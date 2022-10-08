<?php

namespace Bitrix\Location\Geometry\Type;

use Bitrix\Main\SystemException;

/**
 * Collection: Abstract class for compound geometries
 *
 * A geometry is a collection if it is made up of other
 * component geometries. Therefore, everything but a Point
 * is a Collection. For example a LineString is a collection
 * of Points. A Polygon is a collection of LineStrings etc.
 */
abstract class Collection extends BaseGeometry
{
	/** @var BaseGeometry[] */
	protected $components = [];

	/**
	 * Constructor: Checks and sets component geometries
	 *
	 * @param array $components array of geometries
	 */
	public function __construct(array $components = [])
	{
		foreach ($components as $component)
		{
			if ($component instanceof BaseGeometry)
			{
				$this->components[] = $component;
			}
			else
			{
				throw new SystemException('Cannot create a collection with non-geometries');
			}
		}
	}

	/**
	 * @return BaseGeometry[]
	 */
	public function getComponents(): array
	{
		return $this->components;
	}

	/**
	 * @return int
	 */
	public function getComponentsCount(): int
	{
		return count($this->components);
	}

	/**
	 * @inheritDoc
	 */
	public function asArray(): array
	{
		$result = [];

		foreach ($this->components as $component)
		{
			$result[] = $component->asArray();
		}

		return $result;
	}
}
