<?
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

		if($geoData)
			$fields = self::getLocationFields($geoData, $lang);

		return strlen($fields['CODE']) > 0 ? $fields['CODE'] : '';
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
			$result = strlen($data->getGeoData()->zipCode) > 0 ? $data->getGeoData()->zipCode : '';

		return $result;
	}

	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return Result.
	 */
	protected static function getData($ip, $lang)
	{
		Manager::useCookieToStoreInfo(true);
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

		if($geoData->cityName == \Bitrix\Main\Service\GeoIp\Manager::INFO_NOT_AVAILABLE)
		{
			return [];
		}

		$res = LocationTable::getList([
			'filter' => [
				'=NAME.NAME_UPPER' => ToUpper($geoData->cityName),
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

	protected static function specifyLocationByParents(Data $geoData, array $locations, $lang)
	{
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

		while($loc = $res->fetch())
		{
			if(self::isNameMatched($geoData, $loc['LOCATION_NAME_UPPER'], $lang))
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

	protected static function isNameMatched(Data $geoData, $name, $lang)
	{
		static $normalizer = null;

		if($normalizer === null)
		{
			$normalizer = Builder::build($lang);
		}

		$name = $normalizer->normalize($name);

		return $normalizer->normalize($geoData->countryName) == $name
			|| $normalizer->normalize($geoData->regionName) == $name
			|| $normalizer->normalize($geoData->subRegionName) == $name;
	}
}