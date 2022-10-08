<?php

namespace Bitrix\Location\Geometry\Converter;

use Bitrix\Location\Geometry\Type\BaseGeometry;
use Bitrix\Main\Web\Json;
use Bitrix\Main\ArgumentException;

class GeoJsonConverter extends ArrayConverter
{
	/**
	 * @param $input
	 * @return BaseGeometry|null
	 */
	public function read($input): ?BaseGeometry
	{
		try
		{
			$input = Json::decode($input);
		}
		catch (ArgumentException $ex)
		{
			return null;
		}

		if (!is_array($input))
		{
			return null;
		}

		return parent::read($input);
	}

	/**
	 * @inheritDoc
	 */
	public function write(BaseGeometry $geometry)
	{
		return Json::encode(
			parent::write($geometry)
		);
	}
}
