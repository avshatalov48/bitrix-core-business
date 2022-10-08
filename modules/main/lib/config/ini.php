<?php

namespace Bitrix\Main\Config;

class Ini
{
	public static function getBool(string $param): bool
	{
		$val = ini_get($param);
		return ($val == '1' || strtolower($val) == 'on');
	}

	public static function getInt(string $param): int
	{
		$val = ini_get($param);
		return static::unformatInt($val);
	}

	public static function unformatInt(string $str): int
	{
		$str = strtolower($str);
		$res = intval($str);

		$suffix = substr($str, -1);
		if ($suffix == "k")
		{
			$res *= 1024;
		}
		elseif ($suffix == "m")
		{
			$res *= 1048576;
		}
		elseif ($suffix == "g")
		{
			$res *= 1048576*1024;
		}
		elseif ($suffix == "b")
		{
			$res = self::unformatInt(substr($str, 0, -1));
		}

		return $res;
	}

	public static function adjustPcreBacktrackLimit(int $val): void
	{
		if ($val > 0)
		{
			$pcreBacktrackLimit = self::getInt('pcre.backtrack_limit');
			if ($pcreBacktrackLimit < $val)
			{
				@ini_set('pcre.backtrack_limit', $val);
			}
		}
	}
}
