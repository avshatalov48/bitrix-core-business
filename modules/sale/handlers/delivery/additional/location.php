<?
namespace Sale\Handlers\Delivery\Additional;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Result;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\ExternalLocationMap;

Loc::loadMessages(__FILE__);

/**
 * Class Location
 * Convert service locations to local and back
 * @package Sale\Handlers\Delivery\Additional
 * We explain that Location types codes are:
 * COUNTRY, REGION, SUBREGION, CITY, VILLAGE
 */
class Location extends ExternalLocationMap
{
	const EXTERNAL_SERVICE_CODE = 'ADD_DLV';
	//public path
	const ETHALON_LOCATIONS_PATH = '/bitrix/services/saleservices/locations.zip';

	public static function compareStepless()
	{
		set_time_limit(0);
		$result = new Result();

		$csvFilePath = self::getLocationsFilePath();

		if($csvFilePath == '')
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_ERROR')));
			return $result;
		}

		$tmpImported = static::saveCsvToTmpTable($csvFilePath);

		if($tmpImported <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_ERROR')));
			return $result;
		}

		$srvId = self::getExternalServiceId();

		if(intval($srvId) <=0)
			return $result;

		self::updateLinksInfo($srvId);
		self::mapByCodes($srvId);
		self::fillNormalizedTable();
		self::mapByNames($srvId);
		return $result;
	}

	public static function compare($stage, $step = '', $progress = 0, $timeout = 0)
	{
		$result = new Result();
		set_time_limit(0);
		$srvId = self::getExternalServiceId();

		if(intval($srvId) <=0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_ERROR_SID')));
			return $result;
		}

		switch($stage)
		{
			case 'start':

				$csvFilePath = self::getLocationsFilePath();

				if($csvFilePath == '')
				{
					$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_ERROR')));
					return $result;
				}

				$res = \Bitrix\Sale\Location\LocationTable::getList(array(
					'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('MAX', 'MAX(ID)')),
					'select' => array('MAX')
				));

				if($loc = $res->fetch())
				{
					$_SESSION['SALE_HNDL_ADD_DLV_LOC_MAX_ID'] = (int)$loc['MAX'];
				}
				else
				{
					$_SESSION['SALE_HNDL_ADD_DLV_LOC_MAX_ID'] = 0;
				}

				$result->setData(array(
					'STAGE' => 'create_ethalon_loc_tmp_table',
					'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_CREATE_TMP_TABLE'),
					'STEP' => $csvFilePath,
					'PROGRESS' => $progress + 5
				));

				break;

			case 'create_ethalon_loc_tmp_table':

				$csvFilePath = !empty($step) ? $step : '';

				if($csvFilePath == '')
				{
					$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_ERROR_PATH')));
					return $result;
				}

				$tmpImported = static::saveCsvToTmpTable($csvFilePath);

				if($tmpImported <= 0)
				{
					$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_ERROR_TMP_TABLE')));
					return $result;
				}

				$_SESSION['SALE_HNDL_ADD_DLV_ETH_LOC_LAST'] = self::getLastEthalonLoc();

				$result->setData(array(
					'STAGE' => 'update_links_info',
					'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_CHECK_COMPARED'),
					'PROGRESS' => $progress + 5
				));

				break;

			case 'update_links_info':

				self::updateLinksInfo($srvId);
				$result->setData(array(
					'STAGE' => 'map_by_codes',
					'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_COMP_BY_CODES'),
					'PROGRESS' => $progress + 5
				));
				break;

			case 'map_by_codes':

				self::mapByCodes($srvId);
				$result->setData(array(
					'STAGE' => 'create_normalized_loc_table',
					'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_NORM'),
					'PROGRESS' => $progress + 5
				));

				break;

			case 'create_normalized_loc_table':

				$lastId = self::fillNormalizedTable((int)$step, $timeout);

				if($lastId > 0 && $lastId < $_SESSION['SALE_HNDL_ADD_DLV_LOC_MAX_ID'])
				{
					$result->setData(array(
						'STAGE' => 'create_normalized_loc_table',
						'STEP' => $lastId,
						'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_NORM'),
						'PROGRESS' => $progress <= 25 ? $progress + 1 : $progress
					));
				}
				else
				{
					$result->setData(array(
						'STAGE' => 'map_by_names',
						'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_COMP_BY_NAMES'),
						'PROGRESS' => $progress + 5
					));
				}

				break;

			case 'map_by_names':

				$lastProcessedId = self::mapByNames($srvId, $step, $timeout);

				if($_SESSION['SALE_HNDL_ADD_DLV_ETH_LOC_LAST'] <= 0)
					$progress = $progress <= 90 ? $progress + 1 : 90;
				elseif($lastProcessedId <= 0 || $lastProcessedId == $_SESSION['SALE_HNDL_ADD_DLV_ETH_LOC_LAST'])
					$progress = 100;
				else
					$progress = 32 + round(60 * $lastProcessedId / $_SESSION['SALE_HNDL_ADD_DLV_ETH_LOC_LAST']);

				if($progress < 100)
				{
					$result->setData(array(
						'STAGE' => 'map_by_names',
						'STEP' => $lastProcessedId,
						'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_COMP_BY_NAMES'),
						'PROGRESS' => $progress
					));
				}
				else
				{
					$result->setData(array(
						'STAGE' => 'finish',
						'MESSAGE' => Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_COMP_COMPLETE'),
						'PROGRESS' => 100
					));
				}

				break;

			default:
				$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADDL_LOCATIONS_ERROR_STAGE')));
		}

		return $result;
	}

	protected static function getLastEthalonLoc()
	{
		$result = 0;

		$con = \Bitrix\Main\Application::getConnection();
		$res = $con->query("SELECT MAX(ID) AS MAX FROM b_sale_hdale");

		if($loc = $res->fetch())
			$result = $loc['MAX'];

		return $result;
	}

	protected static function getLocationsFilePath()
	{
		$archiveFileName = self::downloadLocations();

		if($archiveFileName == '')
			return '';

		return  self::unpackLocations($archiveFileName);
	}

	protected static function getReplacementClass()
	{
		$result = null;

		$replacementPath = Application::getDocumentRoot().
			'/bitrix/modules/sale/handlers/delivery/additional/location/'.
			LANGUAGE_ID.'/replacement.php';

		if(file_exists($replacementPath))
		{
			require_once($replacementPath);

			if(class_exists('\Sale\Handlers\Delivery\Additional\Location\Replacement'))
			{
				$result = '\Sale\Handlers\Delivery\Additional\Location\Replacement';
			}
		}

		return $result;
	}

	protected static function getCountryName()
	{
		$result = '';
		/** @var \Sale\Handlers\Delivery\Additional\Location\Replacement $relpacementClass */
		$relpacementClass = static::getReplacementClass();

		if($relpacementClass)
		{
			$result = $relpacementClass::getCountryName();
		}

		return $result;
	}

	protected static function mapByNames($srvId, $startId = 0, $timeout = 0)
	{
		$countryName = self::getCountryName();

		if($countryName == '')
		{
			return 0;
		}

		$startTime = mktime(true);
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();
		$imported = 0;

		$query = "
			SELECT
				TMP.*
			FROM
				b_sale_hdale AS TMP
			WHERE
				TMP.LOCATION_EXT_ID IS NULL
				AND TMP.PCOUNTRY = '".$sqlHelper->forSql(
					\Sale\Handlers\Delivery\Additional\Location\Replacement::getNameRussia()
				)."'
				AND TMP.LOCATION_EXT_ID IS NULL
		";

		if(intval($startId) > 0)
		{
			$query .= " AND TMP.ID > ".$sqlHelper->forSql(intval($startId));
		}

		$dbRes = $con->query($query);

		$lastLocationId = 0;

		while ($ethLoc = $dbRes->fetch())
		{
			$lastLocationId = (int)$ethLoc['ID'];
			$locationId = self::getLocationIdByNames($ethLoc['NAME'], $ethLoc['PCITY'], $ethLoc['PSUBREGION'], $ethLoc['PREGION'], $ethLoc['PCOUNTRY'], true);

			if(!$locationId)
				$locationId = self::getLocationIdByNames($ethLoc['NAME'], $ethLoc['PCITY'], $ethLoc['PSUBREGION'], $ethLoc['PREGION'], $ethLoc['PCOUNTRY'], false);

			if(intval($locationId) > 0)
			{
				$res = self::setExternalLocation($srvId, $locationId, $ethLoc['CODE']);

				if($res)
					$imported++;
			}

			if ($timeout > 0 && (mktime(true)-$startTime) >= $timeout)
			{
				return $lastLocationId;
			}
		}

		return $lastLocationId;
	}

	protected static function mapByCodes($srvId)
	{
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$con->queryExecute("
			INSERT INTO
				b_sale_loc_ext (SERVICE_ID, LOCATION_ID, XML_ID)
			SELECT
				".$sqlHelper->forSql($srvId).", TMP.LOCATION_ID, TMP.CODE
			FROM
				b_sale_hdale AS TMP
			WHERE
				TMP.LOCATION_ID > 0 AND TMP.LOCATION_EXT_ID IS NULL
		");
	}

	protected static function updateLinksInfo($srvId)
	{
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$con->queryExecute("
			UPDATE
				b_sale_hdale AS TMP
			INNER JOIN
				b_sale_location AS L ON TMP.CODE = L.CODE
			SET
			  TMP.LOCATION_ID = L.ID
		");

		$con->queryExecute("
			UPDATE
				b_sale_hdale AS TMP
			INNER JOIN
				b_sale_loc_ext AS E ON TMP.CODE = E.XML_ID AND E.SERVICE_ID = ".$sqlHelper->forSql($srvId)."
			SET
			  TMP.LOCATION_EXT_ID = E.ID
		");
	}

	protected static function saveCsvToTmpTable($path)
	{
		if($path == '')
			return false;

		$srvId = static::getExternalServiceId();

		if($srvId <= 0)
			return false;

		if(!File::isFileExists($path))
			return 0;

		set_time_limit(0);
		$content = File::getFileContents($path);

		if($content === false)
			return false;

		$lines = explode("\n", $content);

		if(!is_array($lines))
			return false;

		$con = \Bitrix\Main\Application::getConnection();

		if($con->isIndexExists('b_sale_hdale', array('LOCATION_ID')))
			$con->queryExecute("DROP INDEX IX_BSHDALE_LOCATION_ID".($con->getType() == "oracle" ? "" : " ON b_sale_hdale"));

		$con->queryExecute("DELETE FROM b_sale_hdale");

		$sqlHelper = $con->getSqlHelper();
		$imported = 0;
		$i = 0;
		$values = '';

		foreach($lines as $line)
		{
			$cols = explode(';', $line);

			if(!is_array($cols) || count($cols) != 6)
				continue;

			if($cols[0] == '' || $cols[1] == '')
				continue;

			if($values <> '')
				$values .= ', ';

			$values .= "('".$sqlHelper->forSql($cols[0])."', '".$sqlHelper->forSql($cols[1])."', '".$sqlHelper->forSql($cols[2])."', '".$sqlHelper->forSql($cols[3])."', '".$sqlHelper->forSql($cols[4])."', '".$sqlHelper->forSql($cols[5])."', ".($imported+1).")";

			if($i >= 100)
			{
				$con->queryExecute("INSERT INTO b_sale_hdale(CODE, NAME, PCITY, PSUBREGION, PREGION, PCOUNTRY, ID) VALUES ".$values);
				$i = 0;
				$values = '';
			}

			$imported++;
			$i++;
		}

		if($values <> '')
			$con->queryExecute("INSERT INTO b_sale_hdale(CODE, NAME, PCITY, PSUBREGION, PREGION, PCOUNTRY, ID) VALUES ".$values);

		$con->queryExecute("CREATE INDEX IX_BSHDALE_LOCATION_ID ON b_sale_hdale(LOCATION_ID)");

		return $imported;
	}

	protected static function unpackLocations($archivePath)
	{
		$sUnpackDir = \CTempFile::GetDirectoryName(24);
		$fileUnpackPath = $sUnpackDir.'locations.csv';
		CheckDirPath($sUnpackDir);
		$oArchiver = \CBXArchive::GetArchive($archivePath, "ZIP");
		$oArchiver->SetOptions(array("STEP_TIME" => 300));
		$res = $oArchiver->Unpack($sUnpackDir);
		unlink($archivePath);

		if(!$res || !file_exists($fileUnpackPath))
			return '';

		return  $fileUnpackPath;
	}

	protected static function downloadLocations()
	{
		$result = '';
		$client = new \Sale\Handlers\Delivery\Additional\RestClient();
		$host = $client->getServiceHost();
		$downloadUrl = $host.self::ETHALON_LOCATIONS_PATH;
		$tmpDir = \CTempFile::GetDirectoryName(24);
		CheckDirPath($tmpDir);
		$storePath = $tmpDir.'locations.zip';
		$httpClient = new HttpClient();

		if($httpClient->download($downloadUrl, $storePath))
			$result = $storePath;

		return $result;
	}
}
