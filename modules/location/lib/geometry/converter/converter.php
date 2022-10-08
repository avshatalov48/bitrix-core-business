<?php

namespace Bitrix\Location\Geometry\Converter;

use Bitrix\Location\Geometry\Type\BaseGeometry;

abstract class Converter
{
	/**
	 * Read input and return a Geometry
	 *
	 * @return BaseGeometry|null
	 */
	abstract public function read($input): ?BaseGeometry;

	/**
	 * Write out a Geometry in the converter's format
	 *
	 * @return mixed
	 */
	abstract public function write(BaseGeometry $geometry);
}
