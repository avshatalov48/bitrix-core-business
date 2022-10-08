<?php

namespace Bitrix\Location\Geometry\Type;

abstract class BaseGeometry
{
	/**
	 * @return string
	 */
	public function getGeometryType(): string
	{
		return (new \ReflectionClass($this))->getShortName();
	}

	/**
	 * @param BaseGeometry $coordinate
	 * @return bool|null
	 */
	public function contains(BaseGeometry $geometry): ?bool
	{
		return null;
	}

	/**
	 * @return array
	 */
	abstract public function asArray(): array;
}
