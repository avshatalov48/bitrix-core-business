<?
namespace Bitrix\Sale\Delivery;

use Bitrix\Main\Error;
use Bitrix\Sale\Result;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\Comparator;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\ExternalServiceTable;

/**
 * Class ExternalLocationMap
 * @package Bitrix\Sale\Delivery
 * Helper class for locations mapping.
 */
class ExternalLocationMap
{
	//Dlivery idtifyer, stored in \Bitrix\Sale\Location\ExternalServiceTable : CODE
	const EXTERNAL_SERVICE_CODE = '';
	//Path to file (if exist) were we can get prepared locations map
	const CSV_FILE_PATH = '';
	const CITY_NAME_IDX = 0;
	const REGION_NAME_IDX = 1;
	const CITY_XML_ID_IDX = 2;

	/**
	 * Abstract.
	 * Must return in Result->data all locations from external delivery service.
	 * @return Result.
	 * @throws SystemException
	 */
	protected static function getAllLocations()
	{
		throw new SystemException('Must be impemented!');
	}

	/**
	 * Returns internal location id
	 * @param string $externalCode
	 * @return int
	 */
	public static function getInternalId($externalCode)
	{
		if($externalCode == '')
			return 0;

		$srvId = static::getExternalServiceId();

		if($srvId <= 0)
			return 0;

		$res = ExternalTable::getList(array(
			'filter' => array(
				'=XML_ID' => $externalCode,
				'=SERVICE_ID' => $srvId
			)
		));

		if($loc = $res->fetch())
			return $loc['ID'];

		return 0;
	}

	/**
	 * Returns external location id
	 * @param int $locationId
	 * @return int|string
	 */
	public static function getExternalId($locationId)
	{
		if($locationId == '')
			return '';

		$srvId = static::getExternalServiceId();

		if($srvId <= 0)
			return 0;

		$res = LocationTable::getList(array(
			'filter' => array(
				array(
					'LOGIC' => 'OR',
					'=CODE' => $locationId,
					'=ID' => $locationId
				),
				'=EXTERNAL.SERVICE_ID' => $srvId
			),
			'select' => array(
				'ID', 'CODE',
				'XML_ID' => 'EXTERNAL.XML_ID'
			)
		));

		$result = '';

		if($loc = $res->fetch())
			$result = $loc['XML_ID'];

		if($result == '')
			$result = self::getUpperCityExternalId($locationId, $srvId);

		return $result;
	}

	protected static function getUpperCityExternalId($locationId, $srvId)
	{
		$result = '';

		$res = LocationTable::getList(array(
			'filter' => array(
				array(
					'LOGIC' => 'OR',
					'=CODE' => $locationId,
					'=ID' => $locationId
				),
			),
			'select' => array(
				'ID', 'CODE', 'LEFT_MARGIN', 'RIGHT_MARGIN',
				'TYPE_CODE' => 'TYPE.CODE'
			)
		));

		if(!$loc = $res->fetch())
			return '';

		if($loc['TYPE_CODE'] == 'CITY')
			return '';

		$res = LocationTable::getList(array(
			'filter' => array(
				'<LEFT_MARGIN' => $loc['LEFT_MARGIN'],
				'>RIGHT_MARGIN' => $loc['RIGHT_MARGIN'],
				'TYPE.CODE' => 'CITY',
				'=EXTERNAL.SERVICE_ID' => $srvId
			),
			'select' => array(
				'ID', 'CODE', 'LEFT_MARGIN', 'RIGHT_MARGIN',
				'XML_ID' => 'EXTERNAL.XML_ID'
			)
		));

		if($locParent = $res->fetch())
			return $locParent['XML_ID'];

		return $result;
	}

	/**
	 * Returns external location city id
	 * @param int $locationId
	 * @return int|string
	 */
	public static function getCityId($locationId)
	{
		if($locationId == '')
			return 0;

		$res = LocationTable::getList(array(
			'filter' => array(
				array(
					'LOGIC' => 'OR',
					'=CODE' => $locationId,
					'=ID' => $locationId,
				),
				array(
					'=TYPE.CODE' => 'CITY',
					'=PARENTS.TYPE.CODE' => 'CITY'
				),
			),
			'select' => array(
				'ID', 'CODE',
				'TYPE_CODE' => 'TYPE.CODE',
				'PID' => 'PARENTS.ID',
			)
		));

		if($loc = $res->fetch())
		{
			return $loc['PID'];
		}

		return 0;
	}


	/**
	 * Install locations map.
	 * @return Result
	 */
	public static function install()
	{
		$result = new Result();

		if(static::isInstalled())
			return $result;

		$imported = static::importFromCsv($_SERVER['DOCUMENT_ROOT'].static::CSV_FILE_PATH);

		if(intval($imported) <= 0)
			$result = static::refresh();

		return $result;
	}

	/**
	 * Uninstall locations map.
	 * @return Result
	 * @throws \Exception
	 */
	public static function unInstall()
	{
		$result = new Result();

		if(!static::isInstalled())
			return $result;

		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();
		$srvId = $sqlHelper->forSql(static::getExternalServiceId());
		$con->queryExecute("DELETE FROM b_sale_loc_ext WHERE SERVICE_ID=".$srvId);
		ExternalServiceTable::delete($srvId);
		return $result;
	}

	/**
	 * Check locations map was sat.
	 * @return bool
	 */
	public static function isInstalled()
	{
		static $result = null;

		if($result === null)
		{
			$result = false;
			$res = ExternalServiceTable::getList(array(
				'filter' => array(
					'=CODE' => static::EXTERNAL_SERVICE_CODE,
					'!=EXTERNAL.ID' => false
				)
			));

			if($res->fetch())
				$result = true;
		}

		return $result;
	}

	/**
	 * Refresh locations map.
	 * @return Result
	 * @throws ArgumentNullException
	 */
	public static function refresh()
	{
		set_time_limit(0);
		$result = new Result();
		$res = static::getAllLocations();

		if($res->isSuccess())
		{
			$locations = $res->getData();

			if(is_array($locations) && !empty($locations))
			{
				$res = static::setMap($locations);

				if(!$res->isSuccess())
					$result->addErrors($res->getErrors());
			}
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return new Result();
	}

	/**
	 * Import locations map from csv file to database.
	 * @param string $path
	 * @return bool|int Quantity of mapped locations.
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public static function importFromCsv($path)
	{
		set_time_limit(0);

		if($path == '')
			return 0;

		if(!\Bitrix\Main\IO\File::isFileExists($path))
			return 0;

		$content = \Bitrix\Main\IO\File::getFileContents($path);

		if($content === false)
			return 0;

		$srvId = self::getExternalServiceId();

		if(intval($srvId) < 0)
			return 0;

		$lines = explode("\n", $content);

		if(!is_array($lines))
			return array();

		$result = 0;

		foreach($lines as $line)
		{
			$columns = explode(';', $line);

			if(!is_array($columns) || count($columns) != 2)
				continue;

			$res = LocationTable::getList(array(
					'filter' => array(
							'=CODE' => $columns[0],
					),
					'select' => array('ID')
			));

			if($loc = $res->fetch())
				if(self::setExternalLocation($srvId, $loc['ID'], $columns[1]))
					$result++;
		}

		return $result;
	}

	/**
	 * Export locations map from database to file, csv format.
	 * @param string $path
	 * @return bool|int
	 */
	public static function exportToCsv($path)
	{
		set_time_limit(0);
		$srvId = static::getExternalServiceId();

		if($srvId <= 0)
			return false;

		$res = LocationTable::getList(array(
			'filter' => array(
				'=EXTERNAL.SERVICE_ID' => $srvId
			),
			'select' => array(
				'CODE',
				'XML_ID' => 'EXTERNAL.XML_ID'
			)
		));

		$content = '';

		while($row = $res->fetch())
			if($row['CODE'] <> '')
				$content .= $row['CODE'].";".$row['XML_ID']."\n";

		return \Bitrix\Main\IO\File::putFileContents($path, $content);
	}

	/**
	 * If exist returns id, if not exist create it
	 * @return int External service Id
	 * @throws \Exception
	 */
	public static function getExternalServiceId()
	{
		if(static::EXTERNAL_SERVICE_CODE == '')
			throw new SystemException('EXTERNAL_SERVICE_CODE must be defined!');

		static $result = null;

		if($result !== null)
			return $result;

		$res = ExternalServiceTable::getList(array(
			'filter' => array('=CODE' => static::EXTERNAL_SERVICE_CODE)
		));

		if($srv = $res->fetch())
		{
			$result = $srv['ID'];
			return $result;
		}

		$res = ExternalServiceTable::add(array('CODE' => static::EXTERNAL_SERVICE_CODE));

		if(!$res->isSuccess())
		{
			$result = 0;
			return $result;
		}

		$result =  $res->getId();
		return $result;
	}

	/**
	 * Decodes data from utf8 if we need
	 * @param $str
	 * @return bool|string
	 */
	protected static function utfDecode($str)
	{
		if(mb_strtolower(SITE_CHARSET) != 'utf-8')
			$str = Encoding::convertEncoding($str, 'UTF-8', SITE_CHARSET);

		return $str;
	}

	/**
	 * Convert find location by city and region names and add mapping to base
	 * @param array $cities
	 * @return Result
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	protected static function setMap(array $cities)
	{
		$result = new Result();

		if(empty($cities))
			throw new ArgumentNullException('cities');

		$xmlIdExist = array();
		$locationIdExist = array();
		$xmlIds = array_keys($cities);
		$srvId = static::getExternalServiceId();

		$res = ExternalTable::getList(array(
				'filter' => array(
						'=SERVICE_ID' => $srvId
				)
		));

		while($map = $res->fetch())
		{
			$xmlIdExist[] = $map['XML_ID'];
			$locationIdExist[] = $map['LOCATION_ID'];

			//we already have this location
			if(in_array($map['XML_ID'], $xmlIds))
				unset($cities[$map['XML_ID']]);
		}

		//nothing to import
		if(empty($cities))
			return $result;

		foreach($cities as $city)
		{
			$xmlId = $city[self::CITY_XML_ID_IDX];
			$locId = static::getLocationIdByNames($city[static::CITY_NAME_IDX], '', '', $city[static::REGION_NAME_IDX]);

			if(intval($locId) > 0 && !in_array($xmlId, $xmlIdExist) && !in_array($locId, $locationIdExist))
			{
				ExternalTable::add(array(
					'SERVICE_ID' => $srvId,
					'LOCATION_ID' => $locId,
					'XML_ID' => $xmlId
				));

				$xmlIdExist[] = $xmlId;
				$locationIdExist[] = $locId;
			}

			unset($cities[$xmlId]);
		}

		return $result;
	}

	/**
	 * @param int $srvId
	 * @param int $locationId
	 * @param string $xmlId
	 * @param bool $updateExist
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\Result|\Bitrix\Main\Entity\UpdateResult
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public static function setExternalLocation2($srvId, $locationId, $xmlId, $updateExist = false)
	{
		if($xmlId == '')
			throw new ArgumentNullException('code');

		if($srvId == '')
			throw new ArgumentNullException('srvId');

		if(intval($locationId) <= 0)
			throw new ArgumentNullException('locationId');

		static $locCache = array();

		if(!isset($locCache[$srvId]))
		{
			$locCache[$srvId] = array();

			$eRes = ExternalTable::getList(array(
				'filter' => array(
					'=SERVICE_ID' => $srvId,
				),
				'select' => array('ID', 'SERVICE_ID', 'LOCATION_ID', 'XML_ID')
			));

			while($loc = $eRes->fetch())
				$locCache[$srvId][$loc['LOCATION_ID'].'##'.$loc['XML_ID']] = $loc['ID'];
		}

		if(!empty($locCache[$srvId][$locationId.'##'.$xmlId]))
		{
			if($updateExist)
			{
				$res = ExternalTable::update(
					$locCache[$srvId][$locationId.'##'.$xmlId],
					array(
						'SERVICE_ID' => $srvId,
						'XML_ID' => $xmlId,
						'LOCATION_ID' => $locationId
					));

				return $res;
			}
			else
			{
				$result = new \Bitrix\Main\Entity\UpdateResult();
				$result->addError(new Error('External location already exists', 'EXTERNAL_LOCATION_EXISTS'));
				return $result;
			}
		}
		else
		{
			$res = ExternalTable::add(array(
				'SERVICE_ID' => $srvId,
				'XML_ID' => $xmlId,
				'LOCATION_ID' => $locationId
			));

			$locCache[$srvId][$locationId.'##'.$xmlId] = $res->getId();
			return $res;
		}
	}

	/**
	 * @param int $srvId
	 * @param int $locationId
	 * @param string $xmlId
	 * @param bool $updateExist
	 * @return bool
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public static function setExternalLocation($srvId, $locationId, $xmlId, $updateExist = false)
	{
		$result = self::setExternalLocation2($srvId, $locationId, $xmlId, $updateExist);
		return $result->isSuccess();
	}

	protected static function isNormalizedTableFilled()
	{
		$count = 0;
		$con = \Bitrix\Main\Application::getConnection();
		$res = $con->query("SELECT COUNT(1) AS COUNT FROM b_sale_hdaln");

		if($row = $res->fetch())
			$count = intval($row['COUNT']);

		return $count > 0;
	}

	/**
	 * Fill table b_sale_hdaln with locations with normalized names
	 * @param int|bool $startId
	 * @param int $timeout
	 * @return int
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function fillNormalizedTable($startId = false, $timeout = 0)
	{
		set_time_limit(0);
		$startTime = mktime(true);
		$lastProcessedId = 0;
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		if(intval($startId) <= 0)
			$con->queryExecute("DELETE FROM b_sale_hdaln");

		$query = "SELECT
			  L.ID,
			  L.LEFT_MARGIN,
			  L.RIGHT_MARGIN,
			  N.NAME_UPPER
			FROM
			  b_sale_location AS L
				INNER JOIN b_sale_loc_name AS N ON L.ID = N.LOCATION_ID
				INNER JOIN b_sale_loc_type AS T ON L.TYPE_ID = T.ID		
			WHERE
			  N.LANGUAGE_ID = 'ru'
			  AND (T.CODE = 'VILLAGE' OR T.CODE = 'CITY')";

		if($startId !== false)
			$query .= " AND L.ID > ".strval(intval($startId));

		$query .= " ORDER BY ID ASC";
		$res = $con->query($query);

		while($loc = $res->fetch())
		{
			$con->queryExecute("
				INSERT INTO
					  b_sale_hdaln (LOCATION_ID, LEFT_MARGIN, RIGHT_MARGIN, NAME)
				VALUES(
					".intval($loc['ID']).",
					".intval($loc['LEFT_MARGIN']).",
					".intval($loc['RIGHT_MARGIN']).",
					'".$sqlHelper->forSql(
							preg_replace(
									'/\s*(\(.*\))/i'.BX_UTF_PCRE_MODIFIER,
									'',
									\Bitrix\Sale\Location\Comparator::flatten($loc['NAME_UPPER']))
					)."'
				)
			");

			$lastProcessedId = $loc['ID'];

			if($timeout > 0 && (mktime(true)-$startTime) >= $timeout)
				break;
		}

		return $lastProcessedId;
	}

	/**
	 * @param string $name Location name.
	 * @param string $city Citry name.
	 * @param string $subregion Subregions name.
	 * @param string $region Region name.
	 * @param string $country Country name.
	 * @param bool $exactOnly If we search exact name, or partly coincidence is enought
	 * @return int
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Exception
	 */
	public static function getLocationIdByNames($name, $city, $subregion, $region, $country = '', $exactOnly = false)
	{
		$nameNorm = Comparator::normalizeEntity($name, 'LOCALITY');
		$subregionNorm = null;
		$regionNorm = null;
		$cityNorm = null;
		$searchNames = array($name);

		if(!$exactOnly)
			$searchNames = array_merge($searchNames, \Bitrix\Sale\Location\Comparator::getLocalityNamesArray($nameNorm['NAME'], $nameNorm['TYPE']));

		$searchNames = array_map(array('\Bitrix\Sale\Location\Comparator', 'flatten'), $searchNames);
		$searchNames = array_map(function($name){return "'".$name."'";}, $searchNames);

		if(empty($searchNames))
			return 0;

		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();
		$margins = array();
		$res = $con->query("
			SELECT
				N.LOCATION_ID AS LOCATION_ID,
				N.LEFT_MARGIN AS LEFT_MARGIN,
				N.RIGHT_MARGIN AS RIGHT_MARGIN,
				N.NAME AS NAME
			FROM
				b_sale_hdaln AS N
				  LEFT JOIN b_sale_loc_ext AS E
					ON N.LOCATION_ID = E.LOCATION_ID AND E.SERVICE_ID = ".$sqlHelper->forSql(self::getExternalServiceId())."
			WHERE
				E.LOCATION_ID IS NULL
				AND NAME IN (".implode(', ', $searchNames).")");

		$results = array();
		$exact = array();

		while($loc = $res->fetch())
		{
			if(Comparator::isEntityEqual($loc['NAME'], $nameNorm, 'LOCALITY'))
			{
				$margins[] = array($loc['LOCATION_ID'], $loc['LEFT_MARGIN'], $loc['RIGHT_MARGIN'], $loc['NAME']);
				$results[$loc['LOCATION_ID']] = array('NAME' => true);

				if($loc['NAME'] == $nameNorm["NAME"])
					$exact[] = $loc['LOCATION_ID'];
			}
		}

		if(empty($margins))
			return 0;

		$marginFilter = array('LOGIC' => 'OR');

		foreach($margins as $v)
			$marginFilter[] = array('<LEFT_MARGIN' => $v[1], '>RIGHT_MARGIN' => $v[2]);

		$res = LocationTable::getList(array(
			'filter' => array(
				'=NAME.LANGUAGE_ID' => LANGUAGE_ID,
				'=TYPE.CODE' => array('SUBREGION', 'REGION', 'CITY'),
				$marginFilter
			),
			'select' => array(
				'ID',
				'PARENTS_NAME_UPPER' => 'NAME.NAME_UPPER',
				'PARENTS_TYPE_CODE' => 'TYPE.CODE',
				'LEFT_MARGIN', 'RIGHT_MARGIN'
			)
		));

		while($loc = $res->fetch())
		{
			$ids = self::getIdByMargin($loc['LEFT_MARGIN'], $loc['RIGHT_MARGIN'], $margins);

			foreach($ids as $id)
			{
				if(in_array(false, $results[$id], true))
					continue;

				$found = null;

				if($loc['PARENTS_TYPE_CODE'] == 'REGION' && $region <> '')
				{
					if(!is_array($regionNorm))
						$regionNorm = Comparator::normalizeEntity($region, 'REGION');

					$found = Comparator::isEntityEqual($loc['PARENTS_NAME_UPPER'], $regionNorm, 'REGION');
				}
				elseif($subregion <> '' && $loc['PARENTS_TYPE_CODE'] == 'SUBREGION')
				{
					if(!is_array($subregionNorm))
						$subregionNorm = Comparator::normalizeEntity($subregion, 'SUBREGION');

					$found = Comparator::isEntityEqual($loc['PARENTS_NAME_UPPER'], $subregionNorm, 'SUBREGION');
				}
				elseif($city <> '' && $loc['PARENTS_TYPE_CODE'] == 'CITY')
				{
					if(!is_array($cityNorm))
						$subregionNorm = Comparator::normalizeEntity($city, 'LOCALITY');

					$found = Comparator::isEntityEqual($loc['PARENTS_NAME_UPPER'], $cityNorm, 'LOCALITY');
				}

				if($found !== null)
				{
					$isInExact = in_array($id, $exact);
					$results[$id][$loc['PARENTS_TYPE_CODE']] = $found;

					if($results[$id]['REGION'] === true && $results[$id]['SUBREGION'] === true && $isInExact)
						return $id;

					if($found === false && $isInExact)
					{
						$key = array_search($id, $exact);

						if($key !== false)
							unset($exact[$key]);
					}
				}
			}
		}

		if(!empty($exact))
			foreach($exact as $e)
				if(!in_array(false, $results[$e], true))
					return $e;

		$resCandidates = array();

		foreach($results as $id => $result)
		{
			if(!in_array(false, $result, true))
			{
				$resCandidates[$id] = count($result);
			}
		}

		if(empty($resCandidates))
			return 0;

		if(count($resCandidates) > 1)
		{
			arsort($resCandidates);
			reset($resCandidates);
		}

		return key($resCandidates);
	}

	protected static function getIdByMargin($parentLeft, $parentRight, $lMargins)
	{
		$result = array();

		foreach($lMargins as $m)
		{
			if($m[1] > $parentLeft && $m[2] < $parentRight)
				$result[] = $m[0];
		}

		return $result;
	}

	protected static function getNameByMargin($parentLeft, $parentRight, $lMargins)
	{
		foreach($lMargins as $m)
		{
			if($m[1] > $parentLeft && $m[2] < $parentRight)
				return $m[3];
		}

		return 0;
	}
}