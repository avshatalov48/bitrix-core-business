<?php
namespace Bitrix\Main\Text;


use Bitrix\Main\SystemException;

class GeoHash
{
	const MAX_LENGTH = 15;

	protected static $latitudeInterval = array(-90.0, 90.0);
	protected static $longitudeInterval = array(-180.0, 180.0);
	protected static $bits = array(16, 8, 4, 2, 1);
	protected static $base32Chars = array(
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'b', 'c', 'd', 'e', 'f', 'g',
		'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
	);

	public static function encode(array $coordinate, $length = self::MAX_LENGTH)
	{
		$latitudeInterval = static::$latitudeInterval;
		$longitudeInterval = static::$longitudeInterval;

		$isEven = true;
		$bit = 0;
		$charIndex = 0;

		$geohash = '';

		while(mb_strlen($geohash) < $length)
		{
			if($isEven)
			{
				$middle = ($longitudeInterval[0] + $longitudeInterval[1]) / 2;
				if($coordinate[1] > $middle)
				{
					$charIndex |= static::$bits[$bit];
					$longitudeInterval[0] = $middle;
				}
				else
				{
					$longitudeInterval[1] = $middle;
				}
			}
			else
			{
				$middle = ($latitudeInterval[0] + $latitudeInterval[1]) / 2;
				if($coordinate[0] > $middle)
				{
					$charIndex |= static::$bits[$bit];
					$latitudeInterval[0] = $middle;
				}
				else
				{
					$latitudeInterval[1] = $middle;
				}
			}
			if($bit < 4)
			{
				$bit++;
			}
			else
			{
				$geohash .= static::$base32Chars[$charIndex];
				$bit = 0;
				$charIndex = 0;
			}
			$isEven = $isEven ? false : true;
		}

		return $geohash;
	}

	public static function decode($geohash)
	{
		$base32DecodeMap = array_flip(static::$base32Chars);

		$latitudeInterval = static::$latitudeInterval;
		$longitudeInterval = static::$longitudeInterval;

		$isEven = true;
		$geohashLength = mb_strlen($geohash);
		for($i = 0; $i < $geohashLength; $i++)
		{
			if(!isset($base32DecodeMap[$geohash[$i]]))
			{
				throw new SystemException('This geo hash is invalid.');
			}
			$currentChar = $base32DecodeMap[$geohash[$i]];
			$bitsTotal = count(static::$bits);
			for($j = 0; $j < $bitsTotal; $j++)
			{
				$mask = static::$bits[$j];
				if($isEven)
				{
					if(($currentChar & $mask) !== 0)
					{
						$longitudeInterval[0] = ($longitudeInterval[0] + $longitudeInterval[1]) / 2;
					}
					else
					{
						$longitudeInterval[1] = ($longitudeInterval[0] + $longitudeInterval[1]) / 2;
					}
				}
				else
				{
					if(($currentChar & $mask) !== 0)
					{
						$latitudeInterval[0] = ($latitudeInterval[0] + $latitudeInterval[1]) / 2;
					}
					else
					{
						$latitudeInterval[1] = ($latitudeInterval[0] + $latitudeInterval[1]) / 2;
					}
				}
				$isEven = $isEven ? false : true;
			}
		}

		return array(
			($latitudeInterval[0] + $latitudeInterval[1]) / 2,
			($longitudeInterval[0] + $longitudeInterval[1]) / 2
		);
	}
}