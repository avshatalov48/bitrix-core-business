<?php

namespace Bitrix\Sale\Location;

use Bitrix\Main\Service\GeoIp\Data;
use Bitrix\Main\Service\GeoIp\Result;
use	Bitrix\Main\Service\GeoIp\Manager;
use Bitrix\Sale\Location\Normalizer\Builder;

class GeoIp
{
	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return int Location id.
	 */
	public static function getLocationId($ip = '', $lang = LANGUAGE_ID)
	{
		$fields = array();
		$geoData = self::getData($ip, $lang);

		if($geoData)
			$fields = self::getLocationFields($geoData, $lang);

		return intval($fields['ID']) > 0  ? intval($fields['ID']) : 0;
	}

	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string Location code.
	 */
	public static function getLocationCode($ip = '', $lang = LANGUAGE_ID)
	{
		$fields = array();
		$geoData = self::getData($ip, $lang);

		if ($geoData)
		{
			$fields = self::getLocationFields($geoData, $lang);
		}

		return isset($fields['CODE']) && $fields['CODE'] !== '' ? $fields['CODE'] : '';
	}

	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language.
	 * @return string Zip (postal) code.
	 */
	public static function getZipCode($ip, $lang = LANGUAGE_ID)
	{
		$data = self::getData($ip, $lang);

		if(!$data)
			$result = '';
		else
			$result = $data->getGeoData()->zipCode <> '' ? $data->getGeoData()->zipCode : '';

		return $result;
	}

	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return Result.
	 */
	protected static function getData($ip, $lang)
	{
		return Manager::getDataResult($ip, $lang, array('cityName'));
	}

	/**
	 * @param Result $geoIpData.
	 * @param string $lang
	 * @return array Location fields.
	 */
	protected static function getLocationFields(Result $geoIpData, $lang = LANGUAGE_ID)
	{
		if(!$geoIpData->isSuccess())
		{
			return [];
		}

		$geoData = $geoIpData->getGeoData();

		if($geoData->cityName == null)
		{
			return [];
		}

		$res = LocationTable::getList([
			'filter' => [
				'=NAME.NAME_UPPER' => mb_strtoupper($geoData->cityName),
				'=NAME.LANGUAGE_ID' => $lang
			],
			'select' => ['ID', 'CODE', 'LEFT_MARGIN', 'RIGHT_MARGIN']
		]);

		$locations = [];

		while($loc = $res->fetch())
		{
			$locations[$loc['ID']] = $loc;
		}

		$result = [];
		$locationsCount = count($locations);

		if($locationsCount == 1)
		{
			$result = current($locations);
		}
		elseif($locationsCount > 1)
		{
			$result = self::specifyLocationByParents($geoData, $locations, $lang);
		}

		return $result;
	}

	protected static function isParentsEmpty(Data $geoData)
	{
		return empty($geoData->countryName) && empty($geoData->subRegionName) && empty($geoData->regionName);
	}

	protected static function specifyLocationByParents(Data $geoData, array $locations, $lang)
	{
		if(empty($locations))
		{
			return [];
		}

		if(self::isParentsEmpty($geoData))
		{
			reset($locations);
			return current($locations);
		}

		$marginConditions = [
			'LOGIC' => 'OR'
		];

		foreach($locations as $location)
		{
			$marginConditions[] = [
				'LOGIC' => 'AND',
				'<LEFT_MARGIN' => $location['LEFT_MARGIN'],
				'>RIGHT_MARGIN' => $location['RIGHT_MARGIN']
			];
		}

		$params = [
			'filter' => [
				$marginConditions,
				'NAME.LANGUAGE_ID' => $lang,
			],
			'select' => [
				'ID', 'LEFT_MARGIN', 'RIGHT_MARGIN',
				'LOCATION_NAME_UPPER' => 'NAME.NAME_UPPER'
			]
		];

		$res = \Bitrix\Sale\Location\LocationTable::getList($params);
		$weight = [];
		$result = [];
		$normalizer = self::getNameNormalizer($lang);
		$country = $normalizer->normalize($geoData->countryName);
		$region = $normalizer->normalize($geoData->regionName);
		$subRegion = $normalizer->normalize($geoData->subRegionName);

		while($loc = $res->fetch())
		{
			$isNameMatched = self::isNormalizedNamesMatched(
				$normalizer->normalize($loc['LOCATION_NAME_UPPER']),
				$country,
				$region,
				$subRegion
			);

			if($isNameMatched)
			{
				$locationIds = self::getLocationIdsByMargins($locations, $loc['LEFT_MARGIN'], $loc['RIGHT_MARGIN']);

				foreach($locationIds as $locationId)
				{
					if(!isset($locationId))
					{
						$weight[$locationId] = 0;
					}

					$weight[$locationId]++;
				}
			}
		}

		if(!empty($weight))
		{
			arsort($weight);
			reset($weight);
			$id = key($weight);

			if(isset($locations[$id]))
			{
				$result = $locations[$id];
			}
		}

		return $result;
	}

	protected static function getLocationIdsByMargins(array $locations, $leftMargin, $rightMargin)
	{
		$result = [];

		foreach($locations as $locationId => $location)
		{
			if($location['LEFT_MARGIN'] >= $leftMargin && $location['RIGHT_MARGIN'] <= $rightMargin)
			{
				$result[] = $location['ID'];
			}
		}

		return $result;
	}

	/**
	 * @param string $lang
	 * @return Normalizer\INormalizer
	 */
	protected static function getNameNormalizer($lang)
	{
		return Builder::build($lang);
	}

	/**
	 * @param Data $geoData
	 * @param string $name
	 * @param string $lang
	 * @return bool
	 */
	protected static function isNameMatched(Data $geoData, $name, $lang)
	{
		static $normalizer = null;

		if($normalizer === null)
		{
			$normalizer = self::getNameNormalizer($lang);
		}

		$name = $normalizer->normalize($name);

		return $normalizer->normalize($geoData->countryName) == $name
			|| $normalizer->normalize($geoData->regionName) == $name
			|| $normalizer->normalize($geoData->subRegionName) == $name;
	}

	/**
	 * @param string $name
	 * @param string $country
	 * @param string $region
	 * @param string $subregion
	 * @return bool
	 *
	 * We are suggesting that names are already normalized for performance purposes.
	 */
	protected static function isNormalizedNamesMatched($name, $country, $region, $subregion)
	{
		if($name == '')
		{
			return true;
		}

		if($country == '' && $region == '' && $subregion == '')
		{
			return true;
		}

		$result = true;
		$checked = false;

		if($country !== '')
		{
			$result = $country === $name;
			$checked = true;
		}

		if((!$checked || !$result) && $region !== '')
		{
			$result = $region === $name;
			$checked = true;
		}

		if((!$checked || !$result) && $subregion !== '')
		{
			$result = $subregion === $name;
		}

		return $result;
	}
}
