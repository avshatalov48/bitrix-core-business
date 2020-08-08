<?
namespace Bitrix\Sale\Delivery\Pecom;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Sale\Result;
use Bitrix\Main\Text\Encoding;
use Bitrix\Sale\Location\Comparator;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Delivery\ExternalLocationMap;

Loader::registerAutoLoadClasses(
	'sale',
	array(
		'Bitrix\\Sale\\Delivery\\Pecom\\Replacement' => 'ru/delivery/pecom/replacement.php'
	)
);

/**
 * Class Location
 * @package Bitrix\Sale\Delivery\Pecom
 */
class Location extends ExternalLocationMap
{
	const EXTERNAL_SERVICE_CODE = 'PECOM';
	const CSV_FILE_PATH = '/bitrix/modules/sale/ru/delivery/pecom/location.csv';

	public static function compare($startKey = 0, $timeout = 0, $updateExist = false)
	{
		set_time_limit(0);
		$result = new Result();
		$srvId = self::getExternalServiceId();

		if(intval($srvId) <= 0)
			return $result;

		self::fillNormalizedTable();
		$res = static::getAllLocations();

		if($res->isSuccess())
		{
			$locations = $res->getData();

			if(is_array($locations) && !empty($locations))
			{
				$lastKey = static::mapByNames($locations, $srvId, $startKey, $timeout, $updateExist);
				$result->addData(array(
					'LAST_KEY' => $lastKey
				));
			}
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	/**
	 * @param array $locations
	 * @param int $srvId
	 * @param int $startKey
	 * @param int $timeout
	 * @param bool $updateExist
	 * @return int Last processed id.
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function mapByNames($locations, $srvId, $startKey = 0, $timeout = 0, $updateExist = false)
	{
		if(empty($locations))
			throw new ArgumentNullException('locations');

		$startTime = mktime(true);
		$imported = 0;
		$xmlIdExist = array();
		$locationIdExist = array();

		if(!$updateExist)
		{
			$res = ExternalTable::getList(array(
				'filter' => array(
					'=SERVICE_ID' => $srvId
				)
			));

			while($map = $res->fetch())
			{
				$xmlIdExist[] = $map['XML_ID'];
				$locationIdExist[] = $map['LOCATION_ID'];
			}
		}

		$key = 0;

		foreach($locations as $key => $location)
		{
			$xmlId = $location[self::CITY_XML_ID_IDX];

			if($startKey <= 0 || $key >= $startKey)
			{
				if(!in_array($xmlId, $xmlIdExist))
				{
					$cityName = $location[static::CITY_NAME_IDX];
					$districtName = self::extractDistrict($cityName);

					$locationId = static::getLocationIdByNames($cityName, '', $districtName, $location[static::REGION_NAME_IDX], '', true);

					if(!$locationId)
						$locationId = static::getLocationIdByNames($cityName, '', $districtName,$location[static::REGION_NAME_IDX], '', false);

					if(intval($locationId) > 0 && !in_array($locationId, $locationIdExist))
					{
						$res = self::setExternalLocation($srvId, $locationId, $xmlId, $updateExist);

						if($res)
							$imported++;
					}
				}
			}

			unset($locations[$key]);

			if($timeout > 0 && (mktime(true)-$startTime) >= $timeout)
				return intval($key);
		}

		return intval($key) > 0 ? intval($key) : 0;
	}

	protected static function setMap(array $cities)
	{
		self::mapByNames($cities, static::getExternalServiceId());
		return new Result();
	}
	/**
	 * @return Result
	 */
	protected static function getAllLocations()
	{
		$result = new Result();
		$http = new \Bitrix\Main\Web\HttpClient(array(
			"version" => "1.1",
			"socketTimeout" => 30,
			"streamTimeout" => 30,
			"redirect" => true,
			"redirectMax" => 5,
			"disableSslVerification" => true
		));

		$jsnData = $http->get("https://www.pecom.ru/ru/calc/towns.php");
		$errors = $http->getError();

		if (!$jsnData && !empty($errors))
		{
			foreach($errors as $errorCode => $errMes)
				$result->addError(new Error($errMes, $errorCode));

			return $result;
		}

		$data = json_decode($jsnData, true);

		if(is_array($data))
		{
			// City MOSKVA  Region MOSKVA
			$cityRegionSame = array();
			//City name contains (temeryazevskiy r-n)
			$precised = array();
			$emptyRegions = array();
			$other = array();

			if(mb_strtolower(SITE_CHARSET) != 'utf-8')
				$data = Encoding::convertEncoding($data, 'UTF-8', SITE_CHARSET, $em);

			$regions = self::getParents(array_keys($data));

			foreach($data as $regionCity => $city)
			{
				$regionCity = ToUpper($regionCity);
				$regionName = !empty($regions[$regionCity]['REGION']) ? $regions[$regionCity]['REGION'] : '';

				if($regionName == '')
				{
					foreach(Replacement::getRegionExceptions() as $cName => $rName)
					{
						if($regionCity == $cName)
						{
							$regionName = $rName;
							break;
						}
					}
				}

				foreach($city as $cityId => $cityName)
				{
					$cityName = ToUpper($cityName);
					$location = array($cityName, $regionName, $cityId);

					if(mb_strpos($cityName, '(') !== false && mb_strpos($cityName, ')') !== false)
						$precised[] = $location;
					elseif($cityName == $regionCity)
						$cityRegionSame[] = $location;
					elseif($regionCity == '' || $regionCity == '-' || $regionCity == '--')
						$emptyRegions = $location;
					else
						$other[] = $location;
				}
			}

			$result->addData(
				array_merge(
					$precised,
					$other,
					$cityRegionSame,
					$emptyRegions
				)
			);
		}
		else
		{
			$result->addError(new Error("Can decode pecom cities data!"));
		}

		return $result;
	}

	/**
	 * Find regions by city names.
	 * @param array $cityNames
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function getParents(array $cityNames)
	{
		if(empty($cityNames))
			return array();

		$result = array();

		foreach($cityNames as $key => $name)
			$cityNames[$key] = ToUpper($name);

		$res = LocationTable::getList(array(
			'filter' => array(
				'=NAME.NAME_UPPER' => $cityNames,
				'=NAME.LANGUAGE_ID' => LANGUAGE_ID,
				'=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID
			),
			'select' => array(
				'NAME_UPPER' => 'NAME.NAME_UPPER',
				'PARENTS_TYPE_CODE' => 'PARENTS.TYPE.CODE' ,
				'PARENTS_NAME_UPPER' => 'PARENTS.NAME.NAME_UPPER'
			)
		));

		while($loc = $res->fetch())
		{
			if(!isset($result[$loc['NAME_UPPER']]))
				$result[$loc['NAME_UPPER']] = array();

			$result[$loc['NAME_UPPER']][$loc['PARENTS_TYPE_CODE']] = $loc['PARENTS_NAME_UPPER'];
		}

		return $result;
	}

	/**
	 * SOKOLOVSKOE (GULKELIVICHSKIY R-N) => GULKELIVICHSKIY R-N
	 * @param $cityName
	 * @return string
	 */
	protected static function extractDistrict(&$cityName)
	{
		$result = '';
		$matches = array();

		if(preg_match('/(.*)\s*\((.*)\)/i'.BX_UTF_PCRE_MODIFIER, $cityName, $matches))
		{
			if(!empty($matches[2]))
			{
				$result = trim($matches[2]);
				$mark = Replacement::getDistrictMark();

				if(!preg_match('/('.$mark.'){1}/i'.BX_UTF_PCRE_MODIFIER, $result))
					$result = '';

				if(!empty($matches[1]))
					$cityName = trim($matches[1]);
			}
		}

		return $result;
	}
}