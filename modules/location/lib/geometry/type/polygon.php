<?php

namespace Bitrix\Location\Geometry\Type;

class Polygon extends Collection
{
	/**
	 * @inherit
	 */
	public function contains(BaseGeometry $geometry): ?bool
	{
		if (!$geometry instanceof Point)
		{
			return null;
		}

		if (!isset($this->components[0]))
		{
			return null;
		}

		/** @var LineString $outerRing */
		$outerRing = $this->components[0];
		if (!$this->isPointInsideClosedRing($outerRing, $geometry))
		{
			return false;
		}

		$innerRings = array_slice($this->components, 1);
		foreach ($innerRings as $innerRing)
		{
			if ($this->isPointInsideClosedRing($innerRing, $geometry))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param LineString $lineString
	 * @param Point $point
	 * @return bool
	 *
	 * @see https://github.com/mjaschen/phpgeo
	 */
	private function isPointInsideClosedRing(LineString $lineString, Point $point): bool
	{
		$polygonContainsPoint = false;

		$componentsCount = $lineString->getComponentsCount();

		$lineStringPoints = $lineString->getComponents();
		$lineStringLats = [];
		$lineStringLngs = [];

		/** @var Point $point */
		foreach ($lineStringPoints as $lineStringPoint)
		{
			$lineStringLats[] = $lineStringPoint->getLat();
			$lineStringLngs[] = $lineStringPoint->getLng();
		}

		for ($node = 0, $altNode = $componentsCount - 1; $node < $componentsCount; $altNode = $node++)
		{
			$condition = ($lineStringLngs[$node] > $point->getLng()) !== ($lineStringLngs[$altNode] > $point->getLng())
				&& ($point->getLat() < ($lineStringLats[$altNode] - $lineStringLats[$node])
					* ($point->getLng() - $lineStringLngs[$node])
					/ ($lineStringLngs[$altNode] - $lineStringLngs[$node]) + $lineStringLats[$node]);

			if ($condition)
			{
				$polygonContainsPoint = !$polygonContainsPoint;
			}
		}

		return $polygonContainsPoint;
	}
}
