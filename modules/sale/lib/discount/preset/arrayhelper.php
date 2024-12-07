<?php

namespace Bitrix\Sale\Discount\Preset;

use Bitrix\Main\ArgumentException;

final class ArrayHelper
{
	/**
	 * Returns value, that belongs to path.
	 *
	 * @param array|\ArrayAccess $array Target array.
	 * @param string $path Path. Example CONDITIONS.CHILDREN.0.DATA.Value
	 * @param null $defaultValue Default value
	 * @return array|\ArrayAccess|mixed|null
	 * @throws ArgumentException
	 */
	public static function getByPath($array, $path, $defaultValue = null)
	{
		if(!is_array($array) && !$array instanceof \ArrayAccess)
		{
			throw new ArgumentException("\$array is not array or don't implement ArrayAccess");
		}

		$pathItems = explode('.', $path);

		$lastArray = $array;
		foreach($pathItems as $pathItem)
		{
			if(!is_array($lastArray) && !$lastArray instanceof \ArrayAccess)
			{
				return $defaultValue;
			}

			if(!isset($lastArray[$pathItem]))
			{
				return $defaultValue;
			}

			$lastArray = $lastArray[$pathItem];
		}

		return $lastArray;
	}
}
