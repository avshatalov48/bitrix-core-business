<?php

namespace Bitrix\Location\Infrastructure;

use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Main\Loader;

/**
 * Class UserLocation
 *
 * @package Bitrix\Location\Infrastructure
 */
final class UserLocation
{
	/**
	 * @return Point
	 */
	public static function getPoint(): Point
	{
		return self::getPointByPortalRegion();
	}

	/**
	 * @return Point
	 */
	private static function getPointByPortalRegion(): Point
	{
		$region = self::getCurrentRegion();

		$map = [
			'ru' => [55.751244, 37.618423],
			'eu' => [50.85045, 4.34878],
			'de' => [52.520008, 13.404954],
			'fr' => [48.864716, 2.349014],
			'it' => [41.902782, 12.496366],
			'pl' => [52.237049, 21.017532],
			'ua' => [50.431759, 30.517023],
			'by' => [53.893009, 27.567444],
			'kz' => [43.238949, 76.889709],
			'in' => [28.644800, 77.216721],
			'tr' => [39.925533, 32.866287],
			'id' => [-6.200000, 106.816666],
			'cn' => [39.916668, 116.383331],
			'vn' => [21.028511, 105.804817],
			'jp' => [35.652832, 139.839478],
			'com' => [47.751076, -120.740135],
			'es' => [19.432608, -99.133209],
			'br' => [-15.793889, -47.882778],
		];

		$coordinates = $map[$region] ?? [51.509865, -0.118092];

		return (new Point($coordinates[0], $coordinates[1]));
	}

	/**
	 * @return string
	 */
	private static function getCurrentRegion(): string
	{
		$result = null;

		if (Loader::includeModule('bitrix24'))
		{
			$licensePrefix = \CBitrix24::getLicensePrefix();
			if ($licensePrefix !== false)
			{
				$result = (string)$licensePrefix;
			}
		}
		elseif (Loader::includeModule('intranet'))
		{
			$result = (string)\CIntranetUtils::getPortalZone();
		}
		elseif (defined('LANGUAGE_ID'))
		{
			$result = LANGUAGE_ID;
		}

		if (!$result)
		{
			$result = 'en';
		}

		return $result;
	}
}
