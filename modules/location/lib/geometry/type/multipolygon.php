<?php

namespace Bitrix\Location\Geometry\Type;

class MultiPolygon extends Collection
{
	/**
	 * @inheritdoc
	 */
	public function contains(BaseGeometry $geometry): bool
	{
		foreach ($this->components as $polygon)
		{
			if ($polygon->contains($geometry))
			{
				return true;
			}
		}

		return false;
	}
}
