<?
namespace Sale\Handlers\Delivery\Spsr;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Sale\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Comparator\Mapper;
use Bitrix\Sale\Location\Comparator\TmpTable;
use Bitrix\Sale\Location\Comparator\MapResult;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses(
	'sale',
	array(
		'Sale\Handlers\Delivery\Spsr\Replacement' => 'handlers/delivery/spsr/replacement/ru/replacement.php'
	)
);

final class Location extends Mapper
{
	const EXTERNAL_SERVICE_CODE = 'SPSR';
	const CSV_FILE_PATH = '/bitrix/modules/sale/handlers/delivery/spsr/location.csv';

	protected $tmpTable = null;
	protected $serviceId = 0;

	public function __construct()
	{
		$this->serviceId = $this->getExternalServiceId();
		$this->tmpTable = new TmpTable($this->serviceId);
	}

	protected function getLocationsRequest($cityName = '', $countryName = '')
	{
		set_time_limit(0);
		$result = new Result();

		$requestData = '
			<root xmlns="http://spsr.ru/webapi/Info/GetCities/1.0">
				<p:Params Name="WAGetCities" Ver="1.0" xmlns:p="http://spsr.ru/webapi/WA/1.0" />
				<GetCities CityName="'.mb_strtolower($cityName).'" CountryName="'.mb_strtolower($countryName).'" />
			</root>';

		$request = new Request();
		$res = $request->send($requestData);

		if($res->isSuccess())
		{
			$data = $res->getData();
			$xmlAnswer = new \SimpleXMLElement($data[0]);
			$cities = array();

			foreach($xmlAnswer->City->Cities as $city)
			{
				$cities[(string)$city['City_ID']."|".(string)$city['City_owner_ID']] = array(
					'City_ID' => (string)$city['City_ID'],
					'City_owner_ID' => (string)$city['City_owner_ID'],
					'CityName' => self::utfDecode(
						(string)$city['CityName']
					),
					'RegionName' => self::utfDecode(
						(string)$city['RegionName']
					)
				);
			}

			if(!empty($cities))
			{
				$result->setData($cities);
			}
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	protected function mapByNames($startId = 0, $timeout = 0)
	{
		$startTime = mktime(true);
		$result = new MapResult;
		\Bitrix\Sale\Location\Comparator::setVariants(Replacement::getVariants());
		$dbRes = $this->tmpTable->getUnmappedLocations($startId);

		while($loc = $dbRes->fetch())
		{
			/**
			 * Extract city name and subregion name from
			 * Abramtsevo (Balashihinskiy)
			 * Abramtsevo (Dmitrovskiy,  141880)
			 * Aborino
			 */
			$matches = array();
			preg_match('/([^(]*)(\(([^\,\s]*)(\s*\,\s*\d*){0,1}\)){0,1}/i', $loc['CityName'], $matches);

			if(empty($matches[1]))
			{
				if($this->collectNotFound)
					$result->addNotFound($loc['XML_ID'], $loc['CityName'].' : '.$loc['RegionName']);

				continue;
			}

			$cityName = !empty($matches[1]) ? trim($matches[1]) : '';
			$subRegionName = !empty($matches[3]) ? trim($matches[3]) : '';
			$locId = 0;

			if($cityName <> '')
			{
				$locId = self::getLocationIdByNames($cityName, "", $subRegionName, $loc['RegionName'], "", true);

				if(intval($locId) <= 0)
					$locId = self::getLocationIdByNames($cityName, "", $subRegionName, $loc['RegionName'], "", false);
			}

			if(intval($locId) > 0)
			{
				$res = self::setExternalLocation2($this->serviceId, $locId, $loc['XML_ID'], false);

				if($res->isSuccess())
				{
					if($this->collectMapped)
						$result->addMapped($loc['XML_ID'], $loc['CityName'].', '.$loc['RegionName'], $locId);

					$this->tmpTable->markMapped($locId, $loc['XML_ID']);
				}
				elseif($this->collectDuplicated)
				{
					foreach($res->getErrors() as $error)
					{
						if($error->getCode() == 'EXTERNAL_LOCATION_EXISTS')
						{
							$result->addDuplicated($loc['XML_ID'], $loc['CityName'].':'.$loc['RegionName'], $locId);
							break;
						}
					}
				}
			}
			else
			{
				if($this->collectNotFound)
					$result->addNotFound($loc['XML_ID'], $loc['CityName'].':'.$loc['RegionName']);
			}

			$result->setLastProcessedId($loc['ID']);

			if($timeout > 0 && (mktime(true)-$startTime) >= $timeout)
				return $result;
		}

		return $result;
	}

	public static function install()
	{
		return new Result();
	}

	public function mapStepless()
	{
		set_time_limit(0);
		$result = new Result();

		$res = $this->getLocationsRequest('', Loc::getMessage('SALE_DLV_SRV_SPSR_RUSSIA'));

		if(!$res->isSuccess())
			return $res;

		$locationsData = $res->getData();
		$locationsCount = count($locationsData);

		if($this->tmpTable->isExist())
			$this->tmpTable->drop();

		$this->tmpTable->create($locationsData);
		$tmpImported = $this->tmpTable->saveData($locationsData);
		unset($locationsData);

		if($tmpImported <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLV_SRV_SPSR_TMP_TBL_ERROR')));
			return $result;
		}

		$this->importFromCsv($_SERVER["DOCUMENT_ROOT"].self::CSV_FILE_PATH);
		$this->fillNormalizedTable();
		$this->tmpTable->markAllMapped();
		$mapRes = $this->mapByNames();
		$mapRes->setSupportedCount($locationsCount);
		$this->tmpTable->drop();
		$result->setData(array('MAP_RESULT' => $mapRes));
		return $result;
	}

	public function map($stage, $step = '', $progress = 0, $timeout = 0)
	{
		$result = new Result();
		set_time_limit(0);

		switch($stage)
		{
			case 'start':

				$res = self::getLocationsRequest('', Loc::getMessage('SALE_DLV_SRV_SPSR_RUSSIA'));

				if(!$res->isSuccess())
					return $res;

				$data = $res->getData();

				if($this->tmpTable->isExist())
					$this->tmpTable->drop();

				$this->tmpTable->create($data);
				$tmpImported = $this->tmpTable->saveData($data);

				if($tmpImported <= 0)
				{
					$result->addError(new Error(Loc::getMessage('SALE_DLV_SRV_SPSR_TMP_TBL_ERROR')));
					return $result;
				}

				$_SESSION['SALE_HNDL_SPSR_DLV_TMP_MAX_ID'] = $this->tmpTable->getMaxId();

				$res = \Bitrix\Sale\Location\LocationTable::getList(array(
					'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('MAX', 'MAX(ID)')),
					'select' => array('MAX')
				));

				if($loc = $res->fetch())
					$_SESSION['SALE_HNDL_SPSR_DLV_LOC_MAX_ID'] = $loc['MAX'];
				else
					$_SESSION['SALE_HNDL_SPSR_DLV_LOC_MAX_ID'] = 0;

				$result->setData(array(
					'STAGE' => 'import_from_csv',
					'MESSAGE' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_CSV'),
					'PROGRESS' => $progress + 5
				));

				break;

			case 'import_from_csv':

				$this->importFromCsv($_SERVER["DOCUMENT_ROOT"].self::CSV_FILE_PATH);

				$result->setData(array(
					'STAGE' => 'fill_normalized_table',
					'MESSAGE' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_NORM'),
					'PROGRESS' => $progress + 5
				));

				break;

			case 'fill_normalized_table':

				$lastId = self::fillNormalizedTable($step, $timeout);
				$progress = $this->calculateProgress($lastId, $_SESSION['SALE_HNDL_SPSR_DLV_LOC_MAX_ID'], $progress, 11, 30);

				if($lastId > 0 && $lastId <  $_SESSION['SALE_HNDL_SPSR_DLV_LOC_MAX_ID'])
				{
					$result->setData(array(
						'STAGE' => 'fill_normalized_table',
						'STEP' => $lastId,
						'MESSAGE' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_NORM'),
						'PROGRESS' => $progress
					));
				}
				else
				{
					unset($_SESSION['SALE_HNDL_SPSR_DLV_LOC_MAX_ID']);

					$result->setData(array(
						'STAGE' => 'mark_unmapped',
						'MESSAGE' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_CHECK_MAPPED'),
						'PROGRESS' => 30
					));
				}
				break;

			case 'mark_unmapped':

				$this->tmpTable->markAllMapped();
				$result->setData(array(
					'STAGE' => 'map_by_names',
					'MESSAGE' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_MAP_BY_NAME'),
					'PROGRESS' => $progress + 5
				));
				break;

			case 'map_by_names':

				$mapResult = $this->mapByNames($step, $timeout);
				$lastProcessedId = $mapResult->getLastProcessedId();

				if($lastProcessedId > 0 && $lastProcessedId < $_SESSION['SALE_HNDL_SPSR_DLV_TMP_MAX_ID'])
				{
					$progress = $this->calculateProgress($lastProcessedId, $_SESSION['SALE_HNDL_SPSR_DLV_TMP_MAX_ID'], $progress, 36, 100);

					$result->setData(array(
						'STAGE' => 'map_by_names',
						'STEP' => $lastProcessedId,
						'MESSAGE' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_MAP_BY_NAME'),
						'PROGRESS' => $progress,
						'MAP_RESULT' => $mapResult
					));
				}
				else
				{
					unset($_SESSION['SALE_HNDL_SPSR_DLV_TMP_MAX_ID']);
					$this->tmpTable->drop();

					$result->setData(array(
						'STAGE' => 'finish',
						'MESSAGE' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_MAP_FINISHED'),
						'PROGRESS' => 100,
						'MAP_RESULT' => $mapResult
					));
				}

				break;

			default:
				$result->addError(new Error(Loc::getMessage('SALE_DLV_SRV_SPSR_LOC_MAP_STAGE_ERROR')));
		}

		return $result;
	}

	protected function calculateProgress($id, $maxId, $progress, $minProgress, $maxProgress)
	{
		if($maxId <= 0)
		{
			$progress = $progress < $maxProgress ? ($progress + 1) : $progress;
		}
		elseif($id >= $maxId)
		{
			$progress = $maxProgress;
		}
		else
		{
			$progress = $minProgress + round(($maxProgress-$minProgress) * $id / $maxId);

			if($progress >= $maxProgress && $id < $maxId)
				$progress--;
		}

		return $progress;
	}
}