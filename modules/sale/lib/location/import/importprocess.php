<?php
/**
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Bitrix\Sale\Location\Import;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\IO;

use Bitrix\Sale\Location;
use Bitrix\Sale\Location\DB\BlockInserter;
use Bitrix\Sale\Location\Util\CSVReader;

final class ImportProcess extends Location\Util\Process
{
	const DISTRIBUTOR_HOST = 				'www.1c-bitrix.ru';
	const DISTRIBUTOR_PORT = 				80;
	//const REMOTE_PATH = 					'/locations_data/compiled/';
	const REMOTE_PATH = 					'/download/files/locations/pro/';
	const REMOTE_SETS_PATH = 				'bundles/';
	const REMOTE_LAYOUT_FILE = 				'bundles/layout.csv';
	const REMOTE_TYPE_GROUP_FILE = 			'typegroup.csv';
	const REMOTE_TYPE_FILE = 				'type.v2.csv';
	const REMOTE_EXTERNAL_SERVICE_FILE = 	'externalservice.csv';

	const PACK_STANDARD = 					'standard';
	const PACK_EXTENDED = 					'extended';

	const LOCAL_SETS_PATH = 				'bundles/';
	const LOCAL_LOCATION_FILE = 			'%s.csv';
	const LOCAL_LAYOUT_FILE = 				'layout.csv';
	const LOCAL_TYPE_GROUP_FILE = 			'typegroup.csv';
	const LOCAL_TYPE_FILE = 				'type.csv';
	const LOCAL_EXTERNAL_SERVICE_FILE = 	'externalservice.csv';

	const USER_FILE_DIRECTORY_SESSION_KEY = 'location_import_user_file';
	const USER_FILE_TEMP_NAME = 			'userfile.csv';

	const SOURCE_REMOTE = 					'remote';
	const SOURCE_FILE = 					'file';

	const MAX_CODE_FETCH_BLOCK_LEN = 		90;
	const INSERTER_MTU = 					99999;
	const INSERTER_MTU_ORACLE = 			9999;

	const DB_TYPE_MYSQL = 					'mysql';
	const DB_TYPE_MSSQL = 					'mssql';
	const DB_TYPE_ORACLE = 					'oracle';

	const TREE_REBALANCE_TEMP_BLOCK_LEN = 	99999;
	const TREE_REBALANCE_TEMP_BLOCK_LEN_O = 9999;
	const TREE_REBALANCE_TEMP_TABLE_NAME = 	'b_sale_location_rebalance';

	const DEBUG_MODE = 						false;

	protected $sessionKey = 				'location_import';
	protected $rebalanceInserter = 			false;
	protected $stat = 						array();
	protected $hitData = 					array();
	protected $useCache = 					true;

	protected $dbConnection =				null;
	protected $dbConnType = 				null;
	protected $dbHelper = 					null;

	public function __construct($options)
	{
		$options = $this->prepareImportProcessOptions($options);

		if($options['ONLY_DELETE_ALL'])
		{
			$this->addStage(array(
				'PERCENT' => 100,
				'CODE' => 'DELETE_ALL',
				'CALLBACK' => 'stageDeleteAll',
				'SUBPERCENT_CALLBACK' => 'getSubpercentForstageDeleteAll'
			));
		}
		else
		{
			$this->addStage(array(
				'PERCENT' => 5,
				'CODE' => 'DOWNLOAD_FILES',
				'CALLBACK' => 'stageDownloadFiles',
				'SUBPERCENT_CALLBACK' => 'getSubpercentForStageDownloadFiles'
			));

			if($options['REQUEST']['OPTIONS']['DROP_ALL'])
			{
				$this->addStage(array(
					'PERCENT' => 7,
					'CODE' => 'DELETE_ALL',
					'CALLBACK' => 'stageDeleteAll',
					'SUBPERCENT_CALLBACK' => 'getSubpercentForstageDeleteAll'
				));
			}

			$this->addStage(array(
				'PERCENT' => 10,
				'CODE' => 'DROP_INDEXES',
				'CALLBACK' => 'stageDropIndexes',
				'SUBPERCENT_CALLBACK' => 'getSubpercentForStageDropIndexes'
			));

			$this->addStage(array(
				'PERCENT' => 60,
				'STEP_SIZE' => 6000,
				'CODE' => 'PROCESS_FILES',
				'CALLBACK' => 'stageProcessFiles',
				'SUBPERCENT_CALLBACK' => 'getSubpercentForStageProcessFiles'
			));

			if($options['REQUEST']['OPTIONS']['INTEGRITY_PRESERVE'])
			{
				$this->addStage(array(
					'PERCENT' => 65,
					'STEP_SIZE' => 1,
					'CODE' => 'INTEGRITY_PRESERVE',
					'CALLBACK' => 'stageIntegrityPreserve'
				));
			}

			$this->addStage(array(
				'PERCENT' => 90,
				'STEP_SIZE' => 1,
				'CODE' => 'REBALANCE_WALK_TREE',
				'CALLBACK' => 'stageRebalanceWalkTree',
				'SUBPERCENT_CALLBACK' => 'getSubpercentForStageRebalanceWalkTree'
			));

			$this->addStage(array(
				'PERCENT' => 95,
				'STEP_SIZE' => 1,
				'CODE' => 'REBALANCE_CLEANUP_TEMP_TABLE',
				'CALLBACK' => 'stageRebalanceCleanupTempTable'
			));

			$this->addStage(array(
				'PERCENT' => 100,
				'STEP_SIZE' => 1,
				'CODE' => 'RESTORE_INDEXES',
				'CALLBACK' => 'stageRestoreIndexes',
				'SUBPERCENT_CALLBACK' => 'getSubpercentForStageRestoreIndexes'
			));
		}

		$this->dbConnection = Main\HttpApplication::getConnection();
		$this->dbConnType = $this->dbConnection->getType();
		$this->dbHelper = $this->dbConnection->getSqlHelper();

		parent::__construct($options);
	}

	protected function prepareImportProcessOptions($options): array
	{
		if (!is_array($options))
		{
			$options = [];
		}
		$options['ONLY_DELETE_ALL'] = (bool)($options['ONLY_DELETE_ALL'] ?? false);
		$options['LANGUAGE_ID'] = trim((string)($options['LANGUAGE_ID'] ?? LANGUAGE_ID));
		if ($options['LANGUAGE_ID'] === '')
		{
			$options['LANGUAGE_ID'] = LANGUAGE_ID;
		}

		$options['REQUEST'] ??= [];
		if (is_object($options['REQUEST']) && method_exists($options['REQUEST'], 'toArray'))
		{
			$options['REQUEST'] = $options['REQUEST']->toArray();
		}
		if (!is_array($options['REQUEST']))
		{
			$options['REQUEST'] = [];
		}
		$options['REQUEST']['OPTIONS'] ??= [];
		if (!is_array($options['REQUEST']['OPTIONS']))
		{
			$options['REQUEST']['OPTIONS'] = [];
		}

		$requestOptions = &$options['REQUEST']['OPTIONS'];
		$requestOptions['DROP_ALL'] ??= null;
		$requestOptions['INTEGRITY_PRESERVE'] ??= null;
		$requestOptions['EXCLUDE_COUNTRY_DISTRICT'] ??= null;
		$requestOptions['SOURCE'] ??= null;
		$requestOptions['TIME_LIMIT'] = (int)($requestOptions['TIME_LIMIT'] ?? 0);
		unset($requestOptions);

		$options['REQUEST']['ADDITIONAL'] ??= [];
		if (!is_array($options['REQUEST']['ADDITIONAL']))
		{
			$options['REQUEST']['ADDITIONAL'] = [];
		}

		$options['LOCATION_SETS'] ??= [];
		if (!is_array($options['LOCATION_SETS']))
		{
			$options['LOCATION_SETS'] = [];
		}

		return $options;
	}

	public function onBeforePerformIteration()
	{
		if ($this->options['ONLY_DELETE_ALL'])
		{
			return;
		}

		$this->data['inited'] ??= false;
		if (!$this->data['inited'])
		{
			if((string)($this->data['LOCAL_PATH'] ?? '') === '')
			{
				[$this->data['LOCAL_PATH'], $this->data['LOCAL_PATH_CREATED']] = $this->getTemporalDirectory();
			}

			$opts = $this->options['REQUEST']['OPTIONS'];

			if(!in_array($opts['SOURCE'], array(self::SOURCE_REMOTE, self::SOURCE_FILE)))
				throw new Main\SystemException('Unknown import type');

			$sets = array();
			if($opts['SOURCE'] == self::SOURCE_REMOTE)
			{
				$sets = $this->normalizeQueryArray($this->options['REQUEST']['LOCATION_SETS']);
				if(empty($sets))
					throw new Main\SystemException('Nothing to do (no sets selected)');
			}

			$this->data['settings'] = array(
				'sets' => $sets,
				'options' => $opts
			);

			if($opts['SOURCE'] == self::SOURCE_REMOTE)
			{
				$this->data['settings']['additional'] = is_array($this->options['REQUEST']['ADDITIONAL']) ? array_flip(array_values($this->options['REQUEST']['ADDITIONAL'])) : array();

				if(isset($this->data['settings']['additional']['ZIP']))
					$this->data['settings']['additional']['ZIP_LOWER'] = $this->data['settings']['additional']['ZIP'];
			}
			elseif($this->checkSource(self::SOURCE_FILE))
			{
				$this->data['settings']['additional'] = false; // means ANY
			}

			$this->buildTypeTable();
			$this->buildExternalSerivceTable();

			$this->data['inited'] = true;
		}

		if ($this->data['inited'] && $this->data['LOCAL_PATH_CREATED'])
		{
			$this->touchImportTmpFiles((string)$this->data['LOCAL_PATH']);
		}

		$timeLimit = $this->data['settings']['options']['TIME_LIMIT'];
		if ($timeLimit > 0)
		{
			$this->setTimeLimit($timeLimit);
		}
	}

	/**
	 * @param string $localPath
	 */
	private function touchImportTmpFiles(string $localPath): void
	{
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($localPath)
		);
		foreach ($iterator as $file) {

			if ($file->isDir())
			{
				continue;
			}

			touch($file->getPathname());
		}
	}

	/////////////////////////////////////
	// STAGE 1

	protected function stageDownloadFiles()
	{
		if($this->checkSource(self::SOURCE_FILE)) // user uploaded file
		{
			if((string) $_SESSION[self::USER_FILE_DIRECTORY_SESSION_KEY] == '')
				throw new Main\SystemException('User file was not uploaded properly');

			$srcFilePath = $_SESSION[self::USER_FILE_DIRECTORY_SESSION_KEY].'/'.static::USER_FILE_TEMP_NAME;
			$dstFilePath = $this->data['LOCAL_PATH'].self::getFileNameByIndex(0);

			// ensure directory exists
			$this->createDirectory($this->data['LOCAL_PATH'].'/'.self::LOCAL_SETS_PATH);

			if(!@copy($srcFilePath, $dstFilePath))
			{
				$lastError = error_get_last();
				throw new Main\SystemException($lastError['message']);
			}

			$this->data['files'] = array(
				array(
					'size' => filesize($dstFilePath),
					'memgroup' => 'static'
				)
			);

			$this->nextStage();
		}
		elseif($this->checkSource(self::SOURCE_REMOTE)) // get locations from remote server
		{
			if($this->getStep() == 0)
			{
				$this->data['files'] = array();

				$this->cleanWorkDirectory();

				// layout
				$this->determineLayoutToImport();

				// type groups
				$typeGroups = $this->getRemoteTypeGroups();

				// find out what groups we will include
				$this->data['requiredGroups'] = array();
				foreach($typeGroups as $code => $types)
				{
					if($code == 'LAYOUT') // layout is always included
						continue;

					foreach($types as $type)
					{
						if(isset($this->data['types']['allowed'][$type]))
						{
							$this->data['requiredGroups'][] = mb_strtolower($code);
							break;
						}
					}
				}
			}
			else
			{
				$packPath = self::REMOTE_SETS_PATH.($this->data['settings']['options']['PACK'] == self::PACK_EXTENDED ? self::PACK_EXTENDED : self::PACK_STANDARD).'/';
				//$packPath = self::REMOTE_SETS_PATH.'/';

				if($this->getStep() == 1) // get layout (root) file
				{
					$this->data['files'][0] = array(
						'size' => static::downloadFile(self::REMOTE_LAYOUT_FILE, self::getFileNameByIndex(0), false, $this->data['LOCAL_PATH']),
						'onlyThese' => array_flip($this->data['settings']['bundles']['allpoints']),
						'memgroup' => 'static'
					);

					$this->data['fileDownload']['currentEndPoint'] = 0;
					$this->data['fileDownload']['currentFileOffset'] = 1;
				}

				$i =& $this->data['fileDownload']['currentEndPoint'];
				$j =& $this->data['fileDownload']['currentFileOffset'];

				while($this->checkQuota() && isset($this->data['settings']['bundles']['endpoints'][$i])) // process as many bundles as possible
				{
					$ep = $this->data['settings']['bundles']['endpoints'][$i];

					foreach($this->data['requiredGroups'] as $code)
					{
						$name = self::getFileNameByIndex($j);
						$file = $packPath.$ep.'_'.$code.'.csv';
						try
						{
							$this->data['files'][$j] = array(
								'size' => static::downloadFile($file, $name, false, $this->data['LOCAL_PATH']),
								'memgroup' => $ep
							);
							$j++;
						}
						catch(Main\SystemException $e) // 404 or smth - just skip for now
						{
						}
					}
					$i++;
				}

				if(!isset($this->data['settings']['bundles']['endpoints'][$i])) // no more bundles to process, all files downloaded
				{
					unset($this->data['requiredGroups']);
					unset($this->data['settings']['bundles']['endpoints']);

					$this->nextStage();
					return;
				}
			}

			$this->nextStep();
		}
	}

	protected function getSubpercentForStageDownloadFiles()
	{
		$pRange = $this->getCurrentPercentRange();

		$currEp = (int)($this->data['fileDownload']['currentEndPoint'] ?? 0);

		if (!$currEp)
		{
			return 0;
		}

		return round($pRange * ($currEp / count($this->data['settings']['bundles']['endpoints'])));
	}

	/////////////////////////////////////
	// STAGE 2

	protected function stageDeleteAll()
	{
		switch($this->step)
		{
			case 0:
				$this->dbConnection->query('truncate table '.Location\LocationTable::getTableName());
				break;
			case 1:
				$this->dbConnection->query('truncate table '.Location\Name\LocationTable::getTableName());
				break;
			case 2:
				$this->dbConnection->query('truncate table '.Location\ExternalTable::getTableName());
				break;
			case 3:
				Location\GroupLocationTable::deleteAll();
				break;
			case 4:
				Location\SiteLocationTable::deleteAll();
				break;
		}

		$this->nextStep();

		if($this->step >= 5)
			$this->nextStage();
	}

	protected function getSubpercentForstageDeleteAll()
	{
		$pRange = $this->getCurrentPercentRange();
		$step = $this->getStep();

		$stepsCount = 5;

		if($step >= $stepsCount)
			return $pRange;
		else
		{
			return round($pRange * ($step / $stepsCount));
		}
	}

	/////////////////////////////////////
	// STAGE 2.5

	protected function stageDropIndexes()
	{
		$indexes = array(
			'IX_B_SALE_LOC_MARGINS',
			'IX_B_SALE_LOC_MARGINS_REV',
			'IX_B_SALE_LOC_PARENT',
			'IX_B_SALE_LOC_DL',
			'IX_B_SALE_LOC_TYPE',
			'IX_B_SALE_LOC_NAME_NAME_U',
			'IX_B_SALE_LOC_NAME_LI_LI',
			'IX_B_SALE_LOC_EXT_LID_SID',

			// old
			'IXS_LOCATION_COUNTRY_ID',
			'IXS_LOCATION_REGION_ID',
			'IXS_LOCATION_CITY_ID',
			'IX_B_SALE_LOCATION_1',
			'IX_B_SALE_LOCATION_2',
			'IX_B_SALE_LOCATION_3'
		);

		if(!isset($indexes[$this->getStep()]))
			$this->nextStage();
		else
		{
			$this->dropIndexes($indexes[$this->getStep()]);
			$this->logMessage('Index dropped: '.$indexes[$this->getStep()]);
			$this->nextStep();
		}
	}

	protected function getSubpercentForStageDropIndexes()
	{
		$pRange = $this->getCurrentPercentRange();
		$step = $this->getStep();

		$indexCount = 14;

		if($step >= $indexCount)
			return $pRange;
		else
		{
			return round($pRange * ($step / $indexCount));
		}
	}

	/////////////////////////////////////
	// STAGE 3

	protected function readBlockFromCurrentFile2()
	{
		$fIndex = 		$this->data['current']['fIndex'];
		$fName = 		self::getFileNameByIndex($fIndex);
		$onlyThese =& 	$this->data['files'][$fIndex]['onlyThese'];

		//$this->logMessage('READ FROM File: '.$fName.' seek to '.$this->data['current']['bytesRead']);

		if(!isset($this->hitData['csv']))
		{
			$file = $this->data['LOCAL_PATH'].$fName;

			if(!file_exists($file) || !is_readable($file))
				throw new Main\SystemException('Cannot open file '.$file.' for reading');

			$this->logMessage('Chargeing File: '.$fName);

			$this->hitData['csv'] = new CSVReader();
			$this->hitData['csv']->LoadFile($file);
			$this->hitData['csv']->AddEventCallback('AFTER_ASSOC_LINE_READ', array($this, 'provideEnFromRu'));
		}

		$block = $this->hitData['csv']->ReadBlockLowLevel($this->data['current']['bytesRead'], 100);

		$this->data['current']['linesRead'] += count($block);

		if(empty($block))
		{
			return array();
		}

		if($this->hitData['csv']->CheckFileIsLegacy())
		{
			$block = self::convertBlock($block);
		}

		if(is_array($onlyThese))
		{
			foreach($block as $i => $line)
			{
				if(is_array($onlyThese) && !isset($onlyThese[$line['CODE']]))
					unset($block[$i]);
			}
		}

		//$this->logMessage('Bytes read: '.$this->data['current']['bytesRead']);

		return $block;
	}

	protected static function checkLocationCodeExists($code)
	{
		if ($code == '')
			return false;

		$dbConnection = Main\HttpApplication::getConnection();

		$code = $dbConnection->getSqlHelper()->forSql($code);
		$res = $dbConnection->query("select ID from ".Location\LocationTable::getTableName()." where CODE = '".$code."'")->fetch();

		return $res['ID'] ?? false;
	}

	protected function importBlock(&$block)
	{
		if(empty($block))
			return;

		$gid = $this->getCurrentGid();

		// here must decide, which languages to import
		$langs = array_flip(
			array_map(
				function ($value)
				{
					return mb_strtoupper($value);
				},
				array_keys(Location\Admin\NameHelper::getLanguageList()))
		);

		foreach($block as $i => $data)
		{
			$code = $data['CODE'];

			// this spike is only for cutting off COUNTRY_DISTRICT
			// strongly need for the more generalized mechanism for excluding certain types
			if (!!($this->options['REQUEST']['OPTIONS']['EXCLUDE_COUNTRY_DISTRICT']))
			{
				if (!isset($this->data['COUNTRY_2_DISTRICT']))
				{
					$this->data['COUNTRY_2_DISTRICT'] = [];
				}

				if ($data['TYPE_CODE'] == 'COUNTRY')
				{
					$this->data['LAST_COUNTRY'] = $data['CODE'];
				}
				elseif ($data['TYPE_CODE'] == 'COUNTRY_DISTRICT')
				{
					$this->data['COUNTRY_2_DISTRICT'][$code] = $this->data['LAST_COUNTRY'];
					continue;
				}
				else
				{
					if (isset($this->data['COUNTRY_2_DISTRICT'][$data['PARENT_CODE']]))
					{
						$data['PARENT_CODE'] = $this->data['COUNTRY_2_DISTRICT'][$data['PARENT_CODE']];
					}
				}
			}

			// this spike is only for cutting off COUNTRY_DISTRICT
			// strongly need for the more generalized mechanism for excluding certain types
			if (!!($this->options['REQUEST']['OPTIONS']['EXCLUDE_COUNTRY_DISTRICT']))
			{
				if (!isset($this->data['COUNTRY_2_DISTRICT']))
				{
					$this->data['COUNTRY_2_DISTRICT'] = [];
				}

				if ($data['TYPE_CODE'] == 'COUNTRY')
				{
					$this->data['LAST_COUNTRY'] = $data['CODE'];
				}
				elseif ($data['TYPE_CODE'] == 'COUNTRY_DISTRICT')
				{
					$this->data['COUNTRY_2_DISTRICT'][$code] = $this->data['LAST_COUNTRY'];
					continue;
				}
				else
				{
					if (isset($this->data['COUNTRY_2_DISTRICT'][$data['PARENT_CODE']]))
					{
						$data['PARENT_CODE'] = $this->data['COUNTRY_2_DISTRICT'][$data['PARENT_CODE']];
					}
				}
			}

			if(isset($this->data['existedlocs']['static'][$code]) || isset($this->data['existedlocs'][$gid][$code])) // already exists
				continue;

			if(!isset($this->data['types']['allowed'][$data['TYPE_CODE']])) // disallowed
				continue;

			// have to check existence first
			if(!$this->data['TABLE_WERE_EMPTY'])
			{
				$existedId = $this->checkLocationCodeExists($code);

				if(intval($existedId))
				{
					$this->data['existedlocs'][$gid][$code] = $existedId;
					continue;
				}
			}

			///////////////////////////////////////////
			// transform parent
			if($data['PARENT_CODE'] <> '')
			{
				if(isset($this->data['existedlocs']['static'][$data['PARENT_CODE']]))
				{
					$data['PARENT_ID'] = $this->data['existedlocs']['static'][$data['PARENT_CODE']];
				}
				elseif(isset($this->data['existedlocs'][$gid][$data['PARENT_CODE']]))
				{
					$data['PARENT_ID'] = $this->data['existedlocs'][$gid][$data['PARENT_CODE']];
				}
				else
				{
					$data['PARENT_ID'] = 0;
				}
			}
			else
			{
				$data['PARENT_ID'] = 0;
			}

			unset($data['PARENT_CODE']);

			///////////////////////////////////////////
			// transform type
			$data['TYPE_ID'] = $this->data['types']['code2id'][$data['TYPE_CODE']];
			unset($data['TYPE_CODE']);

			///////////////////////////////////////////
			// add
			$names = $data['NAME'];
			unset($data['NAME']);

			$external = $data['EXT'];
			unset($data['EXT']);

			$data['LONGITUDE'] = (float)($data['LONGITUDE'] ?? 0);
			$data['LATITUDE'] = (float)($data['LATITUDE'] ?? 0);
			if(!$this->checkExternalServiceAllowed('GEODATA'))
			{
				$data['LONGITUDE'] = 0;
				$data['LATITUDE'] = 0;
			}

			$locationId = $this->hitData['HANDLES']['LOCATION']->insert($data);

			// store for further PARENT_CODE to PARENT_ID mapping
			//if(!strlen($this->data['types']['last']) || $this->data['types']['last'] != $data['TYPE_CODE'])
				$this->data['existedlocs'][$gid][$data['CODE']] = $locationId;

			///////////////////////////////////////////
			// add names
			if(is_array($names) && !empty($names))
			{
				if(is_array($langs))
				{
					foreach($langs as $lid => $f)
					{
						$lid = mb_strtolower($lid);
						$toAdd = static::getTranslatedName($names, $lid);

						$this->hitData['HANDLES']['NAME']->insert(array(
							'NAME' => $toAdd['NAME'],
							'NAME_UPPER' => mb_strtoupper($toAdd['NAME']),
							'LANGUAGE_ID' => $lid,
							'LOCATION_ID' => $locationId
						));
					}
				}
			}

			///////////////////////////////////////////
			// add external
			if(is_array($external) && !empty($external))
			{
				foreach($external as $sCode => $values)
				{
					if($this->checkExternalServiceAllowed($sCode))
					{
						$serviceId = $this->data['externalService']['code2id'][$sCode];

						if(!$serviceId)
							throw new Main\SystemException('Location import failed: external service doesnt exist');

						if($sCode == 'ZIP_LOWER')
						{
							if($values == '')
								continue;

							$values = explode(',', $values);

							if(!is_array($values))
								continue;

							$values = array_unique($values);
						}

						if(is_array($values))
						{
							foreach($values as $val)
							{
								if($val == '')
									continue;

								$this->hitData['HANDLES']['EXTERNAL']->insert(array(
									'SERVICE_ID' => 	$serviceId,
									'XML_ID' => 		$val,
									'LOCATION_ID' => 	$locationId
								));
							}
						}
					}
				}
			}
		}
	}

	protected function getCurrentGid()
	{
		return $this->data['files'][$this->data['current']['fIndex']]['memgroup'];
	}

	protected function stageProcessFiles()
	{
		if($this->dbConnType == self::DB_TYPE_ORACLE)
			$mtu = self::INSERTER_MTU_ORACLE;
		else
			$mtu = self::INSERTER_MTU;

		$this->hitData['HANDLES']['LOCATION'] = new BlockInserter(array(
			'entityName' => '\Bitrix\Sale\Location\LocationTable',
			'exactFields' => array('CODE', 'TYPE_ID', 'PARENT_ID', 'LATITUDE', 'LONGITUDE'),
			'parameters' => array(
				'autoIncrementFld' => 'ID',
				'mtu' => $mtu
			)
		));

		$this->hitData['HANDLES']['NAME'] = new BlockInserter(array(
			'entityName' => '\Bitrix\Sale\Location\Name\LocationTable',
			'exactFields' => array('NAME', 'NAME_UPPER', 'LANGUAGE_ID', 'LOCATION_ID'),
			'parameters' => array(
				'autoIncrementFld' => 'ID',
				'mtu' => $mtu
			)
		));

		$this->hitData['HANDLES']['EXTERNAL'] = new BlockInserter(array(
			'entityName' => '\Bitrix\Sale\Location\ExternalTable',
			'exactFields' => array('SERVICE_ID', 'XML_ID', 'LOCATION_ID'),
			'parameters' => array(
				'autoIncrementFld' => 'ID',
				'mtu' => $mtu
			)
		));

		if($this->getStep() == 0)
		{
			// set initial values
			$this->data['current'] = array(
				'fIndex' => 0,
				'bytesRead' => 0, // current file bytes read
				'linesRead' => 0
			);

			$this->hitData['HANDLES']['LOCATION']->resetAutoIncrementFromIndex(); // synchronize sequences, etc...

			// check if we are empty
			$this->data['TABLE_WERE_EMPTY'] = Location\LocationTable::getCountByFilter() == 0;

			$this->buildStaticLocationIndex();
		}

		while($this->checkQuota())
		{
			$block = $this->readBlockFromCurrentFile2();
			$this->importBlock($block);

			// clean memory
			$this->manageExistedLocationIndex(array($this->getCurrentGid()));

			// or the current file is completely exhausted
			if($this->checkFileCompletelyRead())
			{
				//$this->logMessage('Lines read: '.$this->data['current']['linesRead']);

				// charge next file
				unset($this->hitData['csv']);
				$this->data['current']['fIndex']++; // next file to go
				$this->data['current']['bytesRead'] = 0; // read counter from the beginning
				$this->data['current']['linesRead'] = 0;
				$this->data['current']['legacy'] = array(); // drop legacy data of the file, if were any. bye-bye

				// may be that is all?
				if($this->checkAllFilesRead())
				{
					unset($this->data['existedlocs']); // uff, remove that huge array at last

					$this->nextStage();
					break;
				}
			}

			$this->nextStep();
		}

		$this->hitData['HANDLES']['LOCATION']->flush();
		$this->hitData['HANDLES']['NAME']->flush();
		$this->hitData['HANDLES']['EXTERNAL']->flush();

		$this->logMessage('Inserted, go next: '.$this->getHitTimeString());

		$this->logMemoryUsage();
	}

	protected function getSubpercentForStageProcessFiles()
	{
		$pRange = $this->getStagePercent($this->stage) - $this->getStagePercent($this->stage - 1);

		$totalSize = 0;
		$fileBytesRead = 0;

		if(!isset($this->data['current']['fIndex']))
			return 0;

		$fIndex = $this->data['current']['fIndex'];

		$i = -1;
		foreach($this->data['files'] as $file)
		{
			$i++;

			if($i < $fIndex)
				$fileBytesRead += $file['size'];

			$totalSize += $file['size'];
		}

		if(!$totalSize)
			return 0;

		return round($pRange * (intval($fileBytesRead + $this->data['current']['bytesRead']) / $totalSize));
	}

	/////////////////////////////////////
	// STAGE 4

	protected function stageIntegrityPreserve()
	{
		$lay = $this->getRemoteLayout(true);

		$this->restoreIndexes('IX_B_SALE_LOC_PARENT');

		$res = Location\LocationTable::getList(array(
			'select' => array(
				'ID', 'CODE'
			),
			'filter' => array(
				'=PARENT_ID' => 0
			)
		));
		$relations = array();
		$code2id = array();
		while($item = $res->fetch())
		{
			if(isset($lay[$item['CODE']]) && ((string) $lay[$item['CODE']]['PARENT_CODE'] != '')/*except root*/)
				$relations[$item['CODE']] = $lay[$item['CODE']]['PARENT_CODE'];
			// relations is a match between codes from the layout file

			$code2id[$item['CODE']] = $item['ID'];
		}

		$parentCode2id = $this->getLocationCodeToIdMap($relations);

		foreach($code2id as $code => $id)
		{
			if (!isset($relations[$code]))
			{
				continue;
			}
			if ((string)($parentCode2id[$relations[$code]] ?? '') !== '') // parent really exists
			{
				$res = Location\LocationTable::update(
					$id,
					['PARENT_ID' => $parentCode2id[$relations[$code]]]
				);
				if (!$res->isSuccess())
				{
					throw new Main\SystemException('Cannot make element become a child of its legal parent');
				}
			}
		}

		$this->nextStage();
	}

	/////////////////////////////////////
	// STAGE 5

	protected function stageRebalanceWalkTree()
	{
		if(!isset($this->data['rebalance']['queue']))
		{
			$this->restoreIndexes('IX_B_SALE_LOC_PARENT');

			$this->logMessage('initialize Queue');

			$this->data['rebalance']['margin'] = -1;
			$this->data['processed'] = 0;
			$this->data['rebalance']['queue'] = array(array('I' => 'root', 'D' => 0));

			$tableName = Location\LocationTable::getTableName();
			$res = Main\HttpApplication::getConnection()->query("select count(*) as CNT from {$tableName}")->fetch();

			$this->data['rebalance']['cnt'] = intval($res['CNT']);
		}

		$i = -1;
		while(!empty($this->data['rebalance']['queue']) && $this->checkQuota())
		{
			$i++;

			$node =& $this->data['rebalance']['queue'][0];

			if(isset($node['L']))
			{
				// we have already been here
				array_shift($this->data['rebalance']['queue']);
				if($node['I'] != 'root') // we dont need for ROOT item in outgoing
				{
					$this->acceptRebalancedNode(array(
						'I' => $node['I'],
						'D' => $node['D'],
						'L' => $node['L'],
						'R' => ++$this->data['rebalance']['margin']
					));
				}
				else
					$this->data['rebalance']['margin']++;
			}
			else
			{
				$a = $this->getCachedBundle($node['I']);

				if(!empty($a))
				{
					// go deeper
					$node['L'] = ++$this->data['rebalance']['margin'];

					foreach($a as $id)
					{
						if($this->checkNodeIsParent($id))
						{
							array_unshift($this->data['rebalance']['queue'], array('I' => $id, 'D' => $node['D'] + 1));
						}
						else // we dont need to put it to the query
						{
							$this->acceptRebalancedNode(array(
								'I' => $id,
								'D' => $node['D'] + 1,
								'L' => ++$this->data['rebalance']['margin'],
								'R' => ++$this->data['rebalance']['margin']
							));
						}
					}
				}
				else
				{
					array_shift($this->data['rebalance']['queue']);
					$this->acceptRebalancedNode(array(
						'I' => $node['I'],
						'D' => $node['D'],
						'L' => ++$this->data['rebalance']['margin'],
						'R' => ++$this->data['rebalance']['margin']
					));
				}
			}
		}

		$this->logMessage('Q size is '.count($this->data['rebalance']['queue']).' already processed: '.$this->data['processed'].'/'.$this->data['rebalance']['cnt']);
		$this->logMemoryUsage();

		if(empty($this->data['rebalance']['queue']))
		{
			// last flush & then merge
			$this->mergeRebalancedNodes();

			$this->nextStage();
			return;
		}

		if($this->rebalanceInserter)
			$this->rebalanceInserter->flush();

		$this->nextStep();
	}

	protected function getSubpercentForStageRebalanceWalkTree()
	{
		$processed = $this->data['processed'] ?? 0;
		$cnt = $this->data['rebalance']['cnt'] ?? 0;
		if (!$processed || !$cnt)
			return 0;

		$pRange = $this->getCurrentPercentRange();
		$part = round($pRange * ($processed / $cnt));

		return min($part, $pRange);
	}

	/////////////////////////////////////
	// STAGE 6

	protected function stageRebalanceCleanupTempTable()
	{
		$this->dropTempTable();
		$this->nextStage();
	}

	/////////////////////////////////////
	// STAGE 7

	protected function stageRestoreIndexes()
	{
		$indexes = array(
			'IX_B_SALE_LOC_MARGINS',
			'IX_B_SALE_LOC_MARGINS_REV',
			//'IX_B_SALE_LOC_PARENT', // already restored at REBALANCE_WALK_TREE stage
			'IX_B_SALE_LOC_DL',
			'IX_B_SALE_LOC_TYPE',
			'IX_B_SALE_LOC_NAME_NAME_U',
			'IX_B_SALE_LOC_NAME_LI_LI',
			'IX_B_SALE_LOC_EXT_LID_SID',

			// legacy
			'IXS_LOCATION_COUNTRY_ID',
			'IXS_LOCATION_REGION_ID',
			'IXS_LOCATION_CITY_ID',
		);

		if(isset($indexes[$this->getStep()]))
		{
			$this->restoreIndexes($indexes[$this->getStep()]);
			$this->logMessage('Index restored: '.$indexes[$this->getStep()]);
			$this->nextStep();
		}
		else
		{
			Location\LocationTable::resetLegacyPath(); // for backward compatibility
			$this->nextStage();
		}
	}

	protected function getSubpercentForStageRestoreIndexes()
	{
		$pRange = $this->getCurrentPercentRange();
		$step = $this->getStep();

		$stepCount = 12;

		if($step >= $stepCount)
		{
			return $pRange;
		}
		else
		{
			return round($pRange * ($step / $stepCount));
		}
	}

	/////////////////////////////////////
	// about stage util functions

	protected function getLanguageId(): string
	{
		return $this->options['LANGUAGE_ID'];
	}

	/**
	 * @deprecated
	 */
	public function getTypes()
	{
		$result = array();
		$res = Location\TypeTable::getList(array(
			'select' => array(
				'CODE', 'TNAME' => 'NAME.NAME'
			),
			'filter' => array(
				'NAME.LANGUAGE_ID' => $this->getLanguageId()
			),
			'order' => array(
				'SORT' => 'asc',
				'NAME.NAME' => 'asc'
			)
		));
		while($item = $res->fetch())
			$result[$item['CODE']] = $item['TNAME'];

		return $result;
	}

	public function getStatisticsAll()
	{
		$this->getStatistics();

		return $this->stat;
	}

	public function getStatistics($type = 'TOTAL')
	{
		if(empty($this->stat))
		{
			$types = \Bitrix\Sale\Location\Admin\TypeHelper::getTypes(array('LANGUAGE_ID' => $this->getLanguageId()));

			$res = Location\LocationTable::getList(array(
				'select' => array(
					'CNT',
					'TCODE' => 'TYPE.CODE'
				),
				'group' => array(
					'TYPE_ID'
				)
			));
			$total = 0;
			$stat = array();
			while($item = $res->fetch())
			{
				$total += intval($item['CNT']);
				$stat[$item['TCODE']] = $item['CNT'];
			}

			foreach($types as $code => $data)
			{
				$this->stat[$code] = array(
					'NAME' => $data['NAME_CURRENT'],
					'CODE' => $code,
					'CNT' => isset($stat[$code]) ? intval($stat[$code]) : 0,
				);
			}

			$this->stat['TOTAL'] = array('CNT' => $total, 'CODE' => 'TOTAL');

			$res = Location\GroupTable::getList(array(
				'runtime' => array(
					'CNT' => array(
						'data_type' => 'integer',
						'expression' => array(
							'COUNT(*)'
						)
					)
				),
				'select' => array(
					'CNT'
				)
			))->fetch();

			$this->stat['GROUPS'] = array('CNT' => intval($res['CNT']), 'CODE' => 'GROUPS');
		}

		return intval($this->stat[$type]['CNT']);
	}

	public function determineLayoutToImport()
	{
		$lay = $this->getRemoteLayout(true);

		$parentness = array();
		foreach($lay as $data)
		{
			$parentCode = (string)($data['PARENT_CODE'] ?? '');
			if ($parentCode === '')
			{
				continue;
			}
			$parentness[$parentCode] ??= 0;
			$parentness[$parentCode]++;
		}

		$bundles = array_flip($this->data['settings']['sets']);

		$selectedLayoutParts = array();
		foreach($bundles as $bundle => $void)
		{
			if(!isset($lay[$bundle]))
				throw new Main\SystemException('Unknown bundle passed in request');

			// obtaining intermediate chain parts
			$chain = array();

			$currentBundle = $bundle;
			$i = -1;
			while($currentBundle)
			{
				$i++;

				if($i > 50) // smth is really bad
					throw new Main\SystemException('Too deep recursion got when building chains. Layout file is broken');

				if(isset($lay[$currentBundle]))
				{
					$chain[] = $currentBundle;
					if($lay[$currentBundle]['PARENT_CODE'] <> '')
					{
						$currentBundle = $lay[$currentBundle]['PARENT_CODE'];

						if(!isset($lay[$currentBundle]))
						{
							throw new Main\SystemException('Unknown parent bundle found ('.$currentBundle.'). Layout file is broken');
						}
					}
					else
					{
						$currentBundle = false;
					}
				}
			}

			if(is_array($chain) && !empty($chain))
			{
				$chain = array_reverse($chain);

				// find first occurance of selected bundle in the chain
				$subChain = array();
				foreach($chain as $i => $node)
				{
					if(isset($bundles[$node]))
					{
						$subChain = array_slice($chain, $i);
						break;
					}
				}

				if(!empty($subChain))
					$selectedLayoutParts = array_merge($selectedLayoutParts, $subChain);
			}
		}

		//$this->data['settings']['layout'] = $lay;
		$selectedLayoutParts = array_unique($selectedLayoutParts);

		$this->data['settings']['bundles'] = array('endpoints' => array(), 'allpoints' => $selectedLayoutParts);

		foreach($selectedLayoutParts as $bCode)
		{
			if(!isset($parentness[$bCode]))
				$this->data['settings']['bundles']['endpoints'][] = $bCode;
			//else
			//	$this->data['settings']['bundles']['middlepoints'][] = $bCode;
		}
		unset($this->data['settings']['sets']);
	}

	public function convertBlock($block)
	{
		$converted = array();

		foreach($block as $line)
		{
			if($line[0] == 'S')
				$typeCode = 'COUNTRY';
			elseif($line[0] == 'R')
				$typeCode = 'REGION';
			elseif($line[0] == 'T')
				$typeCode = 'CITY';
			else
				throw new Main\SystemException('Unknown type found in legacy file');

			$code = md5(implode(':', $line));

			if($typeCode == 'REGION')
				$parentCode = $this->data['current']['legacy']['lastCOUNTRY'];
			elseif($typeCode == 'CITY')
				$parentCode = $this->data['current']['legacy']['lastParent'];
			else
				$parentCode = '';

			if($typeCode != 'CITY')
			{
				$this->data['current']['legacy']['last'.$typeCode] = $code;
				$this->data['current']['legacy']['lastParent'] = $code;
			}

			$cLine = array(
				'CODE' => $code,
				'TYPE_CODE' => $typeCode,
				'PARENT_CODE' => $parentCode
			);

			$lang = false;
			$expectLang = true;
			$lineLen = count($line);
			for($k = 1; $k < $lineLen; $k++)
			{
				if($expectLang)
				{
					$lang = $line[$k];
				}
				else
				{
					$cLine['NAME'][mb_strtoupper($lang)]['NAME'] = $line[$k];
				}

				$expectLang = !$expectLang;
			}

			$converted[] = $cLine;
		}

		return $converted;
	}

	public function checkSource($sType)
	{
		return $this->data['settings']['options']['SOURCE'] == $sType;
	}

	// download layout from server
	public function getRemoteLayout($getFlat = false)
	{
		[$localPath, $tmpDirCreated] = $this->getTemporalDirectory();

		static::downloadFile(self::REMOTE_LAYOUT_FILE, self::LOCAL_LAYOUT_FILE, false, $localPath);

		$csv = new CSVReader();
		$csv->AddEventCallback('AFTER_ASSOC_LINE_READ', array($this, 'provideEnFromRu'));
		$res = $csv->ReadBlock($localPath.self::LOCAL_LAYOUT_FILE);

		$result = array();
		if($getFlat)
		{
			foreach($res as $line)
				$result[$line['CODE']] = $line;
			$csv->CloseFile();
			return $result;
		}

		$lang = $this->getLanguageId();

		foreach($res as $line)
		{
			$line['NAME'][mb_strtoupper($lang)] = static::getTranslatedName($line['NAME'], $lang);
			$result[$line['PARENT_CODE']][$line['CODE']] = $line;
		}
		$csv->CloseFile();

		if($tmpDirCreated)
		{
			$this->deleteDirectory($localPath);
		}

		return $result;
	}

	// download types from server
	public function getRemoteTypes()
	{
		if(!$this->useCache || !isset($this->data['settings']['remote']['types']))
		{
			[$localPath, $tmpDirCreated] = $this->getTemporalDirectory();

			static::downloadFile(self::REMOTE_TYPE_FILE, self::LOCAL_TYPE_FILE, false, $localPath);

			$csv = new CSVReader();
			$csv->AddEventCallback('AFTER_ASSOC_LINE_READ', array($this, 'provideEnFromRu'));
			$res = $csv->ReadBlock($localPath.self::LOCAL_TYPE_FILE);

			$result = array();
			foreach($res as $line)
				$result[$line['CODE']] = $line;

			$this->data['settings']['remote']['types'] = $result;
			$csv->CloseFile();
			if($tmpDirCreated)
			{
				$this->deleteDirectory($localPath);
			}
		}

		return $this->data['settings']['remote']['types'];
	}

	// download external services from server
	public function getRemoteExternalServices()
	{
		if(!$this->useCache || !isset($this->data['settings']['remote']['external_services']))
		{
			[$localPath, $tmpDirCreated] = $this->getTemporalDirectory();

			static::downloadFile(self::REMOTE_EXTERNAL_SERVICE_FILE, self::LOCAL_EXTERNAL_SERVICE_FILE, false, $localPath);

			$csv = new CSVReader();
			$res = $csv->ReadBlock($localPath.self::LOCAL_EXTERNAL_SERVICE_FILE);

			$result = array();
			foreach($res as $line)
				$result[$line['CODE']] = $line;

			$this->data['settings']['remote']['external_services'] = $result;
			$csv->CloseFile();
			if($tmpDirCreated)
			{
				$this->deleteDirectory($localPath);
			}
		}

		return $this->data['settings']['remote']['external_services'];
	}

	// download type groups from server
	public function getRemoteTypeGroups()
	{
		if(!$this->useCache || !isset($this->data['settings']['remote']['typeGroups']))
		{
			[$localPath, $tmpDirCreated] = $this->getTemporalDirectory();

			static::downloadFile(self::REMOTE_TYPE_GROUP_FILE, self::LOCAL_TYPE_GROUP_FILE, false, $localPath);

			$csv = new CSVReader();
			$res = $csv->ReadBlock($localPath.self::LOCAL_TYPE_GROUP_FILE);

			$result = array();
			foreach($res as $line)
			{
				$result[$line['CODE']] = explode(':', $line['TYPES']);
			}

			$this->data['settings']['remote']['typeGroups'] = $result;
			$csv->CloseFile();
			if($tmpDirCreated)
			{
				$this->deleteDirectory($localPath);
			}
		}

		return $this->data['settings']['remote']['typeGroups'];
	}

	public function getTypeLevels($langId = LANGUAGE_ID): array
	{
		$types = $this->getRemoteTypes();
		$levels = array();

		if(!isset($langId))
		{
			$langId = $this->getLanguageId();
		}

		foreach($types as $type)
		{
			if($type['SELECTORLEVEL'] = intval($type['SELECTORLEVEL']))
			{
				$name = static::getTranslatedName($type['NAME'], $langId);
				$levels[$type['SELECTORLEVEL']]['NAMES'][] = $name['NAME'];
				$levels[$type['SELECTORLEVEL']]['TYPES'][] = $type['CODE'];

				$levels[$type['SELECTORLEVEL']]['DEFAULT'] = ($type['DEFAULTSELECT'] == '1');
			}
		}

		foreach($levels as &$group)
			$group['NAMES'] = implode(', ', $group['NAMES']);

		ksort($levels, SORT_NUMERIC);

		return $levels;
	}

	public static function getSiteLanguages()
	{
		static $langs;

		if($langs == null)
		{
			$langs = array();

			$res = \Bitrix\Main\SiteTable::getList(array('filter' => array('ACTIVE' => 'Y'), 'select' => array('LANGUAGE_ID'), 'group' => array('LANGUAGE_ID')));
			while($item = $res->fetch())
			{
				$langs[mb_strtoupper($item['LANGUAGE_ID'])] = true;
			}

			$langs = array_unique(array_keys($langs)); // all active sites languages
		}

		return $langs;
	}

	public function getRequiredLanguages()
	{
		$required = array(mb_strtoupper($this->getLanguageId()));

		$langs = Location\Admin\NameHelper::getLanguageList();
		if(isset($langs['en']))
			$required[] = 'EN';

		return array_unique(array_merge($required, static::getSiteLanguages())); // current language plus for all active sites
	}

	// read type.csv and build type table
	protected function buildTypeTable()
	{
		$this->data['types_processed'] ??= false;
		if ($this->data['types_processed'])
		{
			return;
		}

		// read existed
		$existed = static::getExistedTypes();

		if($this->checkSource(self::SOURCE_REMOTE))
		{
			$rTypes = $this->getRemoteTypes();
			$this->getRemoteTypeGroups();

			$existed = static::createTypes($rTypes, $existed);

			if(intval($dl = $this->data['settings']['options']['DEPTH_LIMIT']))
			{
				// here we must find out what types we are allowed to read

				$typesGroupped = $this->getTypeLevels();

				if(!isset($typesGroupped[$dl]))
					throw new Main\SystemException('Unknow type level to limit');

				$allowed = [];
				foreach($typesGroupped as $gId => $group)
				{
					if($gId > $dl)
						break;

					foreach($group['TYPES'] as $type)
						$allowed[] = $type;
				}

				$this->data['types']['allowed'] = $allowed;
			}
			else
			{
				$allowed = [];
				foreach($rTypes as $type)
				{
					$allowed[] = $type['CODE'];
				}
				$this->data['types']['allowed'] = $allowed;
			}
		}
		elseif($this->checkSource(self::SOURCE_FILE))
		{
			$codes = [];
			if(!empty($existed) && is_array($existed))
			{
				$codes = array_keys($existed);
			}

			$this->data['types']['allowed'] = $codes;
		}

		if (empty($this->data['types']['allowed']))
		{
			$this->data['types']['last'] = null;
		}
		else
		{
			$this->data['types']['last'] = $this->data['types']['allowed'][count($this->data['types']['allowed']) - 1];
		}
		$this->data['types']['allowed'] = array_flip($this->data['types']['allowed']);

		$this->data['types']['code2id'] = $existed;
		$this->data['types_processed'] = true;
	}

	protected function checkExternalServiceAllowed($code)
	{
		if($this->data['settings']['additional'] === false)
			return true; // ANY

		if($code == 'ZIP_LOWER')
			$code = 'ZIP';

		return isset($this->data['settings']['additional'][$code]);
	}

	protected function buildExternalSerivceTable()
	{
		$this->data['external_processed'] ??= false;
		if ($this->data['external_processed'])
		{
			return;
		}

		// read existed
		$existed = static::getExistedServices();

		if($this->checkSource(self::SOURCE_REMOTE))
		{
			$external = $this->getRemoteExternalServices();
			foreach($external as $line)
			{
				if(!isset($existed[$line['CODE']]) && $this->checkExternalServiceAllowed($line['CODE']))
				{
					$existed[$line['CODE']] = static::createService($line);
				}
			}
			unset($this->data['settings']['remote']['external_services']);
		}

		$this->data['externalService']['code2id'] = $existed;
		$this->data['external_processed'] = true;
	}

	protected function buildStaticLocationIndex()
	{
		$parameters = array(
			'select' => array('ID', 'CODE')
		);

		// get static index, it will be always in memory
		$parameters['filter'] = array('TYPE.CODE' => array('COUNTRY', 'COUNTRY_DISTRICT', 'REGION')); // todo: from typegroup later

		$this->data['existedlocs'] = array('static' => array());
		$res = Location\LocationTable::getList($parameters);
		while($item = $res->fetch())
			$this->data['existedlocs']['static'][$item['CODE']] = $item['ID']; // get existed, "static" index
	}

	protected function getLocationCodeToIdMapQuery($buffer, &$result)
	{
		$res = Location\LocationTable::getList(array('filter' => array('CODE' => $buffer), 'select' => array('ID', 'CODE')));
		while($item = $res->fetch())
			$result[$item['CODE']] = $item['ID'];
	}

	protected function getLocationCodeToIdMap($codes)
	{
		if(empty($codes))
			return array();

		$i = -1;
		$buffer = array();
		$result = array();
		foreach($codes as $code)
		{
			$i++;

			if($i == self::MAX_CODE_FETCH_BLOCK_LEN)
			{
				$this->getLocationCodeToIdMapQuery($buffer, $result);

				$buffer = array();
				$i = -1;
			}

			if($code <> '')
			{
				$buffer[] = $code;
			}
		}

		// last iteration
		$this->getLocationCodeToIdMapQuery($buffer, $result);

		return $result;
	}

	protected function manageExistedLocationIndex($memGroups)
	{
		$before = implode(', ', array_keys($this->data['existedlocs']));

		$cleaned = false;
		foreach($this->data['existedlocs'] as $gid => $bundles)
		{
			if($gid == 'static' || in_array($gid, $memGroups))
				continue;

			$cleaned = true;

			$this->logMessage('Memory clean: REMOVING Group '.$gid);
			unset($this->data['existedlocs'][$gid]);
		}

		if($cleaned)
		{
			$this->logMessage('BEFORE memgroups: '.$before);
			$this->logMessage('Clear all but '.$memGroups[0]);

			$this->logMessage('AFTER memgroups: '.implode(', ', array_keys($this->data['existedlocs'])));
		}
	}

	/////////////////////////////////////
	// about file and network I/O

	protected function checkIndexExistsByName($indexName, $tableName)
	{
		$indexName = $this->dbHelper->forSql(trim($indexName));
		$tableName = $this->dbHelper->forSql(trim($tableName));

		if(!mb_strlen($indexName) || !mb_strlen($tableName))
			return false;

		if($this->dbConnType == self::DB_TYPE_MYSQL)
			$res = $this->dbConnection->query("show index from ".$tableName);
		elseif($this->dbConnType == self::DB_TYPE_ORACLE)
			$res = $this->dbConnection->query("SELECT INDEX_NAME as Key_name FROM USER_IND_COLUMNS WHERE TABLE_NAME = '".mb_strtoupper($tableName)."'");
		elseif($this->dbConnType == self::DB_TYPE_MSSQL)
		{
			$res = $this->dbConnection->query("SELECT si.name Key_name
				FROM sysindexkeys s
					INNER JOIN syscolumns c ON s.id = c.id AND s.colid = c.colid
					INNER JOIN sysobjects o ON s.id = o.Id AND o.xtype = 'U'
					LEFT JOIN sysindexes si ON si.indid = s.indid AND si.id = s.id
				WHERE o.name = '".mb_strtoupper($tableName)."'");
		}

		while($item = $res->fetch())
		{
			if (isset($item['Key_name']) && $item['Key_name'] === $indexName)
			{
				return true;
			}
			if (isset($item['KEY_NAME']) && $item['KEY_NAME'] === $indexName)
			{
				return true;
			}
		}

		return false;
	}

	protected function dropIndexByName($indexName, $tableName)
	{
		$indexName = $this->dbHelper->forSql(trim($indexName));
		$tableName = $this->dbHelper->forSql(trim($tableName));

		if(!mb_strlen($indexName) || !mb_strlen($tableName))
			return false;

		if(!$this->checkIndexExistsByName($indexName, $tableName))
			return false;

		if($this->dbConnType == self::DB_TYPE_MYSQL)
			$this->dbConnection->query("alter table {$tableName} drop index {$indexName}");
		elseif($this->dbConnType == self::DB_TYPE_ORACLE)
			$this->dbConnection->query("drop index {$indexName}");
		elseif($this->dbConnType == self::DB_TYPE_MSSQL)
			$this->dbConnection->query("drop index {$indexName} on {$tableName}");

		return true;
	}

	public static function getIndexMap(): array
	{
		$locationTable = Location\LocationTable::getTableName();
		$locationNameTable = Location\Name\LocationTable::getTableName();
		$locationExternalTable = Location\ExternalTable::getTableName();

		if (Main\HttpApplication::getConnection()->getType() === 'pgsql')
		{
			return [
				'ix_b_sale_location_left_margin_right_margin' => ['TABLE' => $locationTable, 'COLUMNS' => ['LEFT_MARGIN', 'RIGHT_MARGIN']],
				'ix_b_sale_location_right_margin_left_margin' => ['TABLE' => $locationTable, 'COLUMNS' => ['RIGHT_MARGIN', 'LEFT_MARGIN']],
				'ix_b_sale_location_parent_id' => ['TABLE' => $locationTable, 'COLUMNS' => ['PARENT_ID']],
				'ix_b_sale_location_depth_level' => ['TABLE' => $locationTable, 'COLUMNS' => ['DEPTH_LEVEL']],
				'ix_b_sale_location_type_id' => ['TABLE' => $locationTable, 'COLUMNS' => ['TYPE_ID']],
				'ix_b_sale_location_type_id_left_margin_right_margin' => ['TABLE' => $locationTable, 'COLUMNS' => ['TYPE_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN']],

				'ix_b_sale_loc_name_name_upper' => ['TABLE' => $locationNameTable, 'COLUMNS' => ['NAME_UPPER']],
				'ix_b_sale_loc_name_location_id_language_id' => ['TABLE' => $locationNameTable, 'COLUMNS' => ['LOCATION_ID', 'LANGUAGE_ID']],

				'ix_b_sale_loc_ext_location_id_service_id' => ['TABLE' => $locationExternalTable, 'COLUMNS' => ['LOCATION_ID', 'SERVICE_ID']],

				// legacy
				'ix_b_sale_location_country_id' => ['TABLE' => $locationTable, 'COLUMNS' => ['COUNTRY_ID']],
				'ix_b_sale_location_region_id' => ['TABLE' => $locationTable, 'COLUMNS' => ['REGION_ID']],
				'ix_b_sale_location_city_id' => ['TABLE' => $locationTable, 'COLUMNS' => ['CITY_ID']],

				// obsolete
				'ix_b_sale_location_1' => ['TABLE' => $locationTable, 'COLUMNS' => ['COUNTRY_ID'], 'DROP_ONLY' => true],
				'ix_b_sale_location_2' => ['TABLE' => $locationTable, 'COLUMNS' => ['REGION_ID'], 'DROP_ONLY' => true],
				'ix_b_sale_location_3' => ['TABLE' => $locationTable, 'COLUMNS' => ['CITY_ID'], 'DROP_ONLY' => true],
			];
		}

		return [
			'IX_SALE_LOCATION_MARGINS' => ['TABLE' => $locationTable, 'COLUMNS' => ['LEFT_MARGIN', 'RIGHT_MARGIN']],
			'IX_SALE_LOCATION_MARGINS_REV' => ['TABLE' => $locationTable, 'COLUMNS' => ['RIGHT_MARGIN', 'LEFT_MARGIN']],
			'IX_SALE_LOCATION_PARENT' => ['TABLE' => $locationTable, 'COLUMNS' => ['PARENT_ID']],
			'IX_SALE_LOCATION_DL' => ['TABLE' => $locationTable, 'COLUMNS' => ['DEPTH_LEVEL']],
			'IX_SALE_LOCATION_TYPE' => ['TABLE' => $locationTable, 'COLUMNS' => ['TYPE_ID']],
			'IX_SALE_L_NAME_NAME_UPPER' => ['TABLE' => $locationNameTable, 'COLUMNS' => ['NAME_UPPER']],
			'IX_SALE_L_NAME_LID_LID' => ['TABLE' => $locationNameTable, 'COLUMNS' => ['LOCATION_ID', 'LANGUAGE_ID']],
			'IX_B_SALE_LOC_EXT_LID_SID' => ['TABLE' => $locationExternalTable, 'COLUMNS' => ['LOCATION_ID', 'SERVICE_ID']],
			'IX_SALE_LOCATION_TYPE_MARGIN' => ['TABLE' => $locationTable, 'COLUMNS' => ['TYPE_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN']],

			// legacy
			'IXS_LOCATION_COUNTRY_ID' => ['TABLE' => $locationTable, 'COLUMNS' => ['COUNTRY_ID']],
			'IXS_LOCATION_REGION_ID' => ['TABLE' => $locationTable, 'COLUMNS' => ['REGION_ID']],
			'IXS_LOCATION_CITY_ID' => ['TABLE' => $locationTable, 'COLUMNS' => ['CITY_ID']],

			// obsolete
			'IX_B_SALE_LOCATION_1' => ['TABLE' => $locationTable, 'COLUMNS' => ['COUNTRY_ID'], 'DROP_ONLY' => true],
			'IX_B_SALE_LOCATION_2' => ['TABLE' => $locationTable, 'COLUMNS' => ['REGION_ID'], 'DROP_ONLY' => true],
			'IX_B_SALE_LOCATION_3' => ['TABLE' => $locationTable, 'COLUMNS' => ['CITY_ID'], 'DROP_ONLY' => true],
		];
	}

	protected function dropIndexes($certainIndex = false)
	{
		$map = static::getIndexMap();

		foreach($map as $index => $ixData)
		{
			if($certainIndex !== false && $certainIndex != $index)
				continue;

			$this->dropIndexByName($index, $ixData['TABLE']);
		}
	}

	public function restoreIndexes($certainIndex = false)
	{
		$map = $this->getIndexMap();

		foreach($map as $ixName => $ixData)
		{
			if (($ixData['DROP_ONLY'] ?? null) === true)
			{
				continue;
			}

			if($certainIndex !== false && $certainIndex != $ixName)
				continue;

			if($this->checkIndexExistsByName($ixName, $ixData['TABLE']))
				return false;

			$this->dbConnection->query('CREATE INDEX '.$ixName.' ON '.$ixData['TABLE'].' ('.implode(', ', $ixData['COLUMNS']).')');
		}

		return true;
	}

	private function getCachedBundle($id)
	{
		$locationTable = Location\LocationTable::getTableName();

		$bundle = array();
		$res = $this->dbConnection->query("select ID from {$locationTable} where PARENT_ID = ".($id == 'root' ? '0' : intval($id)));
		while($item = $res->fetch())
			$bundle[] = $item['ID'];

		return $bundle;
	}

	private function checkNodeIsParent($id)
	{
		$locationTable = Location\LocationTable::getTableName();

		$res = $this->dbConnection->query("select count(*) as CNT from {$locationTable} where PARENT_ID = ".($id == 'root' ? '0' : intval($id)))->fetch();

		return intval($res['CNT']);
	}

	private function mergeRebalancedNodes()
	{
		if($this->rebalanceInserter)
		{
			$this->logMessage('Finally, MERGE is in progress');

			$this->rebalanceInserter->flush();

			// merge temp table with location table
			// there should be more generalized method
			Location\LocationTable::mergeRelationsFromTemporalTable(self::TREE_REBALANCE_TEMP_TABLE_NAME, false, array('LEFT_MARGIN' => 'L', 'RIGHT_MARGIN' => 'R', 'DEPTH_LEVEL' => 'D', 'ID' => 'I'));
		}
	}

	private function acceptRebalancedNode($node)
	{
		$this->createTempTable();

		if(!$this->rebalanceInserter)
		{
			if($this->dbConnType == self::DB_TYPE_ORACLE)
				$mtu = self::TREE_REBALANCE_TEMP_BLOCK_LEN_O;
			else
				$mtu = self::TREE_REBALANCE_TEMP_BLOCK_LEN;

			$this->rebalanceInserter = new BlockInserter(array(
				'tableName' => self::TREE_REBALANCE_TEMP_TABLE_NAME,
				'exactFields' => array(
					'I' => array('data_type' => 'integer'),
					'L' => array('data_type' => 'integer'),
					'R' => array('data_type' => 'integer'),
					'D' => array('data_type' => 'integer'),
				),
				'parameters' => array(
					'mtu' => $mtu
				)
			));
		}

		$this->data['processed']++;

		$this->rebalanceInserter->insert($node);
	}

	private function dropTempTable()
	{
		if($this->dbConnection->isTableExists(self::TREE_REBALANCE_TEMP_TABLE_NAME))
			$this->dbConnection->query("drop table ".self::TREE_REBALANCE_TEMP_TABLE_NAME);
	}

	private function createTempTable()
	{
		$this->data['rebalance']['tableCreated'] ??= false;
		if ($this->data['rebalance']['tableCreated'])
		{
			return;
		}

		$tableName = self::TREE_REBALANCE_TEMP_TABLE_NAME;

		if ($this->dbConnection->isTableExists($tableName))
		{
			$this->dbConnection->query("truncate table {$tableName}");
		}
		else
		{

			if($this->dbConnType == self::DB_TYPE_ORACLE)
			{
				$this->dbConnection->query("create table {$tableName} (
					I NUMBER(18),
					L NUMBER(18),
					R NUMBER(18),
					D NUMBER(18)
				)");
			}
			else
			{
				$this->dbConnection->query("create table {$tableName} (
					I int,
					L int,
					R int,
					D int
				)");
			}

		}

		$this->data['rebalance']['tableCreated'] = true;
	}

	protected function checkFileCompletelyRead()
	{
		return $this->data['current']['bytesRead'] >= $this->data['files'][$this->data['current']['fIndex']]['size'];
	}

	protected function checkAllFilesRead()
	{
		return $this->data['current']['fIndex'] >= count($this->data['files']);
	}

	protected function checkBufferIsFull($bufferSize)
	{
		return $bufferSize >= $this->getCurrStageStepSize();
	}

	protected static function downloadFile($fileName, $storeAs, $skip404 = false, $storeTo = false)
	{
		// useless thing
		if(!$storeTo)
			$storeTo = \CTempFile::GetDirectoryName(1);

		$storeTo .= $storeAs;

		if(file_exists($storeTo))
		{
			if(!is_writable($storeTo))
				throw new Main\SystemException('Cannot remove previous '.$storeAs.' file');

			unlink($storeTo);
		}

		if(!defined('SALE_LOCATIONS_IMPORT_SOURCE_URL'))
			$query = 'https://'.self::DISTRIBUTOR_HOST.self::REMOTE_PATH.$fileName;
		else
			$query = 'http://'.SALE_LOCATIONS_IMPORT_SOURCE_URL.'/'.$fileName;

		$client = new HttpClient();

		if(!$client->download($query, $storeTo))
		{
			$eFormatted = array();
			foreach($client->getError() as $code => $desc)
				$eFormatted[] = trim($desc.' ('.$code.')');

			throw new Main\SystemException('File download failed: '.implode(', ', $eFormatted).' ('.$query.')');
		}

		$status = intval($client->getStatus());

		if($status != 200 && file_exists($storeTo))
			unlink($storeTo);

		$okay = $status == 200 || ($status == 404 && $skip404);

		// honestly we should check for all 2xx codes, but for now this is enough
		if(!$okay)
			throw new Main\SystemException('File download failed: http error '.$status.' ('.$query.')');

		return filesize($storeTo);
	}

	/**
	 * @deprecated
	 */
	protected static function cleanWorkDirectory()
	{
	}

	protected function getFileNameByIndex($i)
	{
		return self::LOCAL_SETS_PATH.sprintf(self::LOCAL_LOCATION_FILE, $i);
	}

	public function saveUserFile($inputName)
	{
		if(is_array($_FILES[$inputName]))
		{
			if($_FILES[$inputName]['error'] > 0)
				throw new Main\SystemException(self::explainFileUploadError($_FILES[$inputName]['error']));

			if(!in_array($_FILES[$inputName]['type'], array(
				'text/plain',
				'text/csv',
				'application/vnd.ms-excel',
				'application/octet-stream'
			)))
			{
				throw new Main\SystemException('Unsupported file type');
			}

			self::cleanWorkDirectory();

			[$localPath, $tmpDirCreated] = $this->getTemporalDirectory();
			$fileName = $localPath.'/'.static::USER_FILE_TEMP_NAME;

			if(!@copy($_FILES[$inputName]['tmp_name'], $fileName))
			{
				$lastError = error_get_last();
				throw new Main\SystemException($lastError['message']);
			}

			$_SESSION[static::USER_FILE_DIRECTORY_SESSION_KEY] = $localPath;
		}
		else
			throw new Main\SystemException('No file were uploaded');
	}

	protected static function explainFileUploadError($error)
	{
		switch ($error)
		{
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded';
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file were uploaded';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk';
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension';
				break;

			default:
				$message = 'Unknown upload error';
				break;
		}
		return $message;
	}

	// all this mess is only to get import work on Bitrix24 (which does not provide any temporal directory in a typical meaning)
	protected function getTemporalDirectory(): array
	{
		$dataLocalPath = trim((string)($this->data['LOCAL_PATH'] ?? ''));
		$wasCreated = false;
		if ($dataLocalPath !== '' && \Bitrix\Main\IO\Directory::isDirectoryExists($dataLocalPath))
		{
			$localPath = $dataLocalPath;
		}
		else
		{
			$wasCreated = true;
			$localPath = \CTempFile::GetDirectoryName(10);
			if (!\Bitrix\Main\IO\Directory::isDirectoryExists($localPath))
			{
				\Bitrix\Main\IO\Directory::createDirectory($localPath);
			}
		}

		return [
			$localPath,
			$wasCreated,
		];
	}

	protected static function createDirectory($path)
	{
		if(!\Bitrix\Main\IO\Directory::isDirectoryExists($path))
		{
			\Bitrix\Main\IO\Directory::createDirectory($path);
		}
	}

	protected static function deleteDirectory($path)
	{
		if(\Bitrix\Main\IO\Directory::isDirectoryExists($path))
		{
			\Bitrix\Main\IO\Directory::deleteDirectory($path);
		}
	}

	protected function normalizeQueryArray($value)
	{
		$result = array();
		if(is_array($value))
		{
			foreach($value as $v)
			{
				if($v <> '')
				{
					$result[] = $this->parseQueryCode($v);
				}
			}
		}

		$result = array_unique($result);
		sort($result, SORT_STRING);

		return $result;
	}

	protected static function parseQueryCode($value)
	{
		$value = mb_strtolower(trim($value));

		if(!preg_match('#^[a-z0-9]+$#i', $value))
			throw new Main\SystemException('Bad request parameter');

		return $value;
	}

	public function turnOffCache()
	{
		$this->useCache = false;
	}

	########################################################
	## static part is used in places like wizards, etc

	public static function getExistedTypes()
	{
		$existed = array();
		$res = Location\TypeTable::getList(array('select' => array('ID', 'CODE', 'SORT')));
		while($item = $res->fetch())
			$existed[$item['CODE']] = $item['ID'];

		return $existed;
	}

	public static function getExistedServices()
	{
		$existed = array();
		$res = Location\ExternalServiceTable::getList(array('select' => array('ID', 'CODE')));
		while($item = $res->fetch())
			$existed[$item['CODE']] = $item['ID'];

		return $existed;
	}

	public static function createTypes($types, $existed = false)
	{
		// read existed
		if($existed === false)
			$existed = static::getExistedTypes();

		// here we try to add type names for ALL languages
		$langs = Location\Admin\NameHelper::getLanguageList();

		foreach($types as $line)
		{
			// for sure
			unset($line['SELECTORLEVEL']);
			unset($line['DEFAULTSELECT']);

			$names = array();

			if(!is_array($line['NAME']))
				$line['NAME'] = array();

			if(is_array($langs))
			{
				foreach($langs as $lid => $f)
				{
					$names[mb_strtoupper($lid)] = static::getTranslatedName($line['NAME'], $lid);
				}
				$line['NAME'] = $names;
			}

			if(!isset($existed[$line['CODE']]))
			{
				$existed[$line['CODE']] = static::createType($line);
			}
			else
			{
				// ensure it has all appropriate translations
				// we can not use ::updateMultipleForOwner() here, because user may rename his types manually
				Location\Name\TypeTable::addAbsentForOwner($existed[$line['CODE']], $names);
			}
		}

		return $existed;
	}

	protected static function getTranslatedName($names, $languageId)
	{
		$languageIdMapped = mb_strtoupper(Location\Admin\NameHelper::mapLanguage($languageId));
		$languageId = mb_strtoupper($languageId);

		if ((string)($names[$languageId]['NAME'] ?? null) !== '')
		{
			return $names[$languageId];
		}

		if ((string)($names[$languageIdMapped]['NAME'] ?? null) !== '')
		{
			return $names[$languageIdMapped];
		}

		return $names['EN'];
	}

	public static function createType($type)
	{
		$map = Location\TypeTable::getMap($type);

		if(is_array($type))
		{
			foreach($type as $fld => $val)
			{
				if(!isset($map[$fld]))
				{
					unset($type[$fld]);
				}
			}
		}

		$res = Location\TypeTable::add($type);
		if(!$res->isSuccess())
			throw new Main\SystemException('Type creation failed: '.implode(', ', $res->getErrorMessages()));

		return $res->getId();
	}

	public static function createService($service)
	{
		$res = Location\ExternalServiceTable::add($service);
		if(!$res->isSuccess())
			throw new Main\SystemException('External service creation failed: '.implode(', ', $res->getErrorMessages()));

		return $res->getId();
	}

	public static function getTypeMap($file)
	{
		$csvReader = new CSVReader();
		$csvReader->LoadFile($file);

		$types = array();
		$i = 0;
		while($type = $csvReader->FetchAssoc())
		{
			if($i) // fix for CSVReader parent class bug
			{
				unset($type['SELECTORLEVEL']);
				unset($type['DEFAULTSELECT']);

				$types[$type['CODE']] = $type;
			}

			$i++;
		}

		return $types;
	}

	public static function getServiceMap($file)
	{
		$csvReader = new CSVReader();
		$csvReader->LoadFile($file);

		$services = array();
		while($service = $csvReader->FetchAssoc())
			$services[$service['CODE']] = $service;

		return $services;
	}

	// this is generally for e-shop installer
	public static function importFile(&$descriptior)
	{
		$timeLimit = ini_get('max_execution_time');
		if ($timeLimit < $descriptior['TIME_LIMIT']) set_time_limit($descriptior['TIME_LIMIT'] + 5);

		$endTime = time() + $descriptior['TIME_LIMIT'];

		if($descriptior['STEP'] == 'rebalance')
		{
			Location\LocationTable::resort();
			Location\LocationTable::resetLegacyPath();
			$descriptior['STEP'] = 'done';
		}

		if($descriptior['STEP'] == 'import')
		{
			if(!isset($descriptior['DO_SYNC']))
			{
				$res = \Bitrix\Sale\Location\LocationTable::getList(array('select' => array('CNT')))->fetch();
				$descriptior['DO_SYNC'] = intval($res['CNT'] > 0);
			}

			if(!isset($descriptior['TYPES']))
			{
				$descriptior['TYPE_MAP'] = static::getTypeMap($descriptior['TYPE_FILE']);
				$descriptior['TYPES'] = static::createTypes($descriptior['TYPE_MAP']);

				$descriptior['SERVICE_MAP'] = static::getServiceMap($descriptior['SERVICE_FILE']);
				$descriptior['SERVICES'] = static::getExistedServices();
			}

			$csvReader = new CSVReader();
			$csvReader->LoadFile($descriptior['FILE']);

			while(time() < $endTime)
			{
				$block = $csvReader->ReadBlockLowLevel($descriptior['POS']/*changed inside*/, 10);

				if(!count($block))
					break;

				foreach($block as $item)
				{
					if($descriptior['DO_SYNC'])
					{
						$id = static::checkLocationCodeExists($item['CODE']);
						if($id)
						{
							$descriptior['CODES'][$item['CODE']] = $id;
							continue;
						}
					}

					// type
					$item['TYPE_ID'] = $descriptior['TYPES'][$item['TYPE_CODE']];
					unset($item['TYPE_CODE']);

					// parent id
					if($item['PARENT_CODE'] <> '')
					{
						if(!isset($descriptior['CODES'][$item['PARENT_CODE']]))
						{
							$descriptior['CODES'][$item['PARENT_CODE']] = static::checkLocationCodeExists($item['PARENT_CODE']);
						}

						$item['PARENT_ID'] = $descriptior['CODES'][$item['PARENT_CODE']];
					}
					unset($item['PARENT_CODE']);

					// ext
					if(is_array($item['EXT']))
					{
						foreach($item['EXT'] as $code => $values)
						{
							if(!empty($values))
							{
								if(!isset($descriptior['SERVICES'][$code]))
								{
									$descriptior['SERVICES'][$code] = static::createService(array(
										'CODE' => $code
									));
								}

								if($code == 'ZIP_LOWER')
								{
									if($values[0] == '')
										continue;

									$values = explode(',', $values[0]);

									if(!is_array($values))
										continue;

									$values = array_unique($values);
								}

								if(is_array($values))
								{
									foreach($values as $value)
									{
										if($value == '')
											continue;

										$item['EXTERNAL'][] = array(
											'SERVICE_ID' => $descriptior['SERVICES'][$code],
											'XML_ID' => $value
										);
									}
								}
							}
						}
					}
					unset($item['EXT'], $item['ZIP_LOWER']);

					$res = Location\LocationTable::addExtended(
						$item,
						array(
							'RESET_LEGACY' => false,
							'REBALANCE' => false
						)
					);

					if(!$res->isSuccess())
						throw new Main\SystemException('Cannot create location');

					$descriptior['CODES'][$item['CODE']] = $res->getId();
				}
			}

			if(!count($block))
			{
				unset($descriptior['CODES']);
				$descriptior['STEP'] = 'rebalance';
			}
		}

		return $descriptior['STEP'] == 'done';
	}

	public function provideEnFromRu(&$data)
	{
		// restore at least "EN" translation
		if (!is_array($data))
		{
			return;
		}
		if (!isset($data['NAME']['RU']))
		{
			return;
		}
		if (!is_array($data['NAME']['RU']))
		{
			return;
		}
				$data['NAME']['EN'] ??= [];

		foreach ($data['NAME']['RU'] as $k => $v)
		{
			if ((string)($data['NAME']['EN'][$k] ?? '') === '')
			{
				$data['NAME']['EN'][$k] = Location\Admin\NameHelper::translitFromUTF8($data['NAME']['RU'][$k]);
			}
		}
	}
}
