<?php
namespace Bitrix\Pull;

class Common
{
	public static function jsonEncode($params)
	{
		$option = null;
		if (version_compare(phpversion(), '5.4') >= 0)
		{
			$option = JSON_UNESCAPED_UNICODE;
		}

		array_walk_recursive($params, function(&$item, $key){
			if ($item instanceof \Bitrix\Main\Type\DateTime)
			{
				$item = date('c', $item->getTimestamp());
			}
		});

		return \Bitrix\Main\Web\Json::encode($params, $option);
	}

	/**
	 * Checks if input array contains a string with invalid unicode symbol(s). If array contains invalid symbols, returns
	 * path to the key with invalid string. If array is valid, returns FALSE.
	 *
	 * @param array $input Input array to validate.
	 * @param string $currentPath Current validation path (for recursion).
	 * @return string|false
	 */
	public static function findInvalidUnicodeSymbols(array $input, $currentPath = "")
	{
		if(!defined("BX_UTF"))
		{
			return false;
		}

		foreach ($input as $k => $v)
		{
			if(is_string($input[$k]))
			{
				if(!mb_check_encoding($input[$k]))
				{
					return $currentPath . "/" . $k;
				}
			}
			else if (is_array($input[$k]))
			{
				$subResult = static::findInvalidUnicodeSymbols($input[$k], $currentPath . "/" . $k);
				if($subResult)
				{
					return $subResult;
				}
			}
		}

		return false;
	}
}
