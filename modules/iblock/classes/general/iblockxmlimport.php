<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class CIBlockXmlImport
{
	const ACTION_NOTHING = 'N';
	const ACTION_DEACTIVATE = 'A';
	const ACTION_REMOVE = 'D';

	const STEP_INIT_IMPORT_TABLES = 'INIT_IMPORT_TABLES';
	const STEP_READ_XML = 'READ_XML';
	const STEP_INDEX_IMPORT_TABLES = 'INDEX_IMPORT_TABLES';
	const STEP_IMPORT_METADATA = 'IMPORT_METADATA';
	const STEP_IMPORT_SECTIONS = 'IMPORT_SECTIONS';
	const STEP_MISSING_SECTIONS = 'MISSING_SECTIONS';
	const STEP_RESORT_SECTIONS = 'RESORT_SECTIONS';
	const STEP_IMPORT_ELEMENTS = 'IMPORT_ELEMENTS';
	const STEP_MISSING_ELEMENTS = 'MISSING_ELEMENTS';
	const STEP_IMPORT_PRODUCT_BUNDLES = 'IMPORT_PRODUCT_BUNDLES';
	const STEP_FINAL = 'FINAL';

	const RESULT_TYPE_SUCCESS = 0;
	const RESULT_TYPE_ERROR = 1;

	const TRANSLITERATION_ON_ADD = 0x0001;
	const TRANSLITERATION_ON_UPDATE = 0x0002;

	const SESSION_STORAGE_ID = 'BX_CML2_IMPORT';

	/** @var CIBlockCMLImport importer */
	private $xmlImport = null;

	private $fileHandler = null;
	private $fileParameters = [
		'PATH' => '',
		'ABSOLUTE_PATH' => '',
		'FILES_DIRECTORY' => '',
		'SIZE' => 0
	];

	private $parameters = [];

	private $config = [];

	private $stepList = [
		self::STEP_INIT_IMPORT_TABLES,
		self::STEP_READ_XML,
		self::STEP_INDEX_IMPORT_TABLES,
		self::STEP_IMPORT_METADATA,
		self::STEP_IMPORT_SECTIONS,
		self::STEP_MISSING_SECTIONS,
		self::STEP_RESORT_SECTIONS,
		self::STEP_IMPORT_ELEMENTS,
		self::STEP_IMPORT_PRODUCT_BUNDLES,
		self::STEP_MISSING_ELEMENTS,
		self::STEP_FINAL
	];

	private $stepId = null;
	private $stepParameters = null;
	private $final = false;
	private $iblockId = null;

	private $message = '';

	private $progressCounter = [];

	private $errors = [];

	private $startTime = null;

	public function __construct()
	{

	}

	public function __destruct()
	{
		$this->closeXmlFile();
		$this->destroyXmlImporter();
	}

	public function init(array $parameters, array $config = [])
	{
		$this->startTime = time();
		$this->final = false;
		$this->clearErrors();
		$this->setParameters($parameters);
		if (!$this->isSuccess())
			return;
		$this->setConfig($config);
		if (!$this->isSuccess())
			return;
		$this->initSessionStorage();
		if (!$this->isSuccess())
			return;
		$this->internalInit();
	}

	private function initSessionStorage()
	{
		if (!isset($_SESSION[self::SESSION_STORAGE_ID]) || !is_array($_SESSION[self::SESSION_STORAGE_ID]))
			$_SESSION[self::SESSION_STORAGE_ID] = [];
		if (!isset($_SESSION[self::SESSION_STORAGE_ID]['SECTIONS_MAP']))
			$_SESSION[self::SESSION_STORAGE_ID]['SECTIONS_MAP'] = null;
		if (!isset($_SESSION[self::SESSION_STORAGE_ID]['PRICES_MAP']))
			$_SESSION[self::SESSION_STORAGE_ID]['PRICES_MAP'] = null;
		$this->initStepParameters();
	}

	private function initStepParameters()
	{
		if (
			!isset($_SESSION[self::SESSION_STORAGE_ID]['STEP_ID'])
			|| (
				!isset($_SESSION[self::SESSION_STORAGE_ID]['STEP_PARAMETERS'])
				|| !is_array($_SESSION[self::SESSION_STORAGE_ID]['STEP_PARAMETERS'])
			)
		)
		{
			$_SESSION[self::SESSION_STORAGE_ID]['STEP_ID'] = reset($this->stepList);
			$_SESSION[self::SESSION_STORAGE_ID]['STEP_PARAMETERS'] = [];
		}
		if (
			array_search(
				$_SESSION[self::SESSION_STORAGE_ID]['STEP_ID'],
				$this->stepList
			) === false
		)
		{
			$this->addError(Loc::getMessage('IBLOCK_XML_IMPORT_ERR_BAD_STEP_ID'));
			return;
		}
		$this->stepId = &$_SESSION[self::SESSION_STORAGE_ID]['STEP_ID'];
		$this->stepParameters = &$_SESSION[self::SESSION_STORAGE_ID]['STEP_PARAMETERS'];
	}

	/**
	 * @return void
	 */
	private function internalInit()
	{
		$this->closeXmlFile();
		$this->destroyXmlImporter();
		$this->createXmlImporter();
	}

	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return $this->final;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$this->setXmlImporterParameters();
		$this->setMessage('');
		$this->clearProgressCounter();
		switch ($this->getCurrentStep())
		{
			case self::STEP_INIT_IMPORT_TABLES:
				$this->initTemporaryTablesAction();
				break;
			case self::STEP_READ_XML:
				$this->readXmlAction();
				break;
			case self::STEP_INDEX_IMPORT_TABLES:
				$this->indexTemporaryTablesAction();
				break;
			case self::STEP_IMPORT_METADATA:
				$this->importMetadataAction();
				break;
			case self::STEP_IMPORT_SECTIONS:
				$this->importSectionsAction();
				break;
			case self::STEP_MISSING_SECTIONS:
				$this->processMissingSectionsAction();
				break;
			case self::STEP_RESORT_SECTIONS:
				$this->resortSectionsAction();
				break;
			case self::STEP_IMPORT_ELEMENTS:
				$this->importElementsAction();
				break;
			case self::STEP_MISSING_ELEMENTS:
				$this->processMissingElementsAction();
				break;
			case self::STEP_IMPORT_PRODUCT_BUNDLES:
				$this->importProductBundlesAction();
				break;
			case self::STEP_FINAL:
				$this->finalAction();
				break;
		}
	}

	/**
	 * @return array
	 */
	public function getStepResult()
	{
		$result = [];
		if ($this->isSuccess())
		{
			$result['TYPE'] = self::RESULT_TYPE_SUCCESS;
			$result['MESSAGE'] = $this->getMessage();
			$result['IS_FINAL'] = 'Y';
			if (!$this->isFinal())
			{
				$result['IS_FINAL'] = 'N';
				$progress = $this->getProgressCounter();
				if (!empty($progress))
					$result['PROGRESS'] = $progress;
				unset($progress);
			}
		}
		else
		{
			$result['TYPE'] = self::RESULT_TYPE_ERROR;
			$result['ERROR'] = implode("\n", $this->getErrors());
			$result['IS_FINAL'] = 'Y';
		}
		return $result;
	}

	/**
	 * @return null|int
	 */
	public function getIblockId()
	{
		return $this->iblockId;
	}

	/**
	 * @return bool
	 */
	public function isSuccess()
	{
		return empty($this->errors);
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @return void
	 */
	public function clearErrors()
	{
		$this->errors = [];
	}

	/**
	 * @param string $error
	 * @return void
	 */
	private function addError($error)
	{
		$error = trim((string)$error);
		if ($error === '')
			return;
		$this->errors[] = $error;
	}

	/**
	 * @param array $parameters
	 * @return void
	 */
	private function setParameters(array $parameters)
	{
		$this->prepareParameters($parameters);
		if (!$this->isSuccess())
			return;
		$this->parameters = $parameters;
	}

	/**
	 * @param array &$parameters
	 * @return void
	 */
	private function prepareParameters(array &$parameters)
	{
		$parameters = array_filter($parameters, [__CLASS__, 'clearNull']);
		$parameters = array_merge($this->getDefaultParameters(), $parameters);

		$parameters['FILE'] = trim($parameters['FILE']);
		if ($parameters['FILE'] === '')
		{
			$this->addError(Loc::getMessage('IBLOCK_XML_IMPORT_ERR_PARAMETER_FILE_IS_EMPTY'));
		}
		else
		{
			$rawFilename = $parameters['FILE'];
			if(
				file_exists($rawFilename)
				&& is_file($rawFilename)
				&& (
					mb_substr($rawFilename, -4) === ".xml"
				)
			)
			{
				$this->fileParameters['PATH'] = mb_substr($rawFilename, mb_strlen($_SERVER['DOCUMENT_ROOT']));
				$this->fileParameters['ABSOLUTE_PATH'] = $rawFilename;
			}
			else
			{
				$rawFilename = trim(str_replace("\\", '/', $rawFilename), '/');
				$filename = rel2abs($_SERVER['DOCUMENT_ROOT'], '/'.$rawFilename);
				if (mb_strlen($filename) > 1 && $filename === '/'.$rawFilename)
				{
					$this->fileParameters['PATH'] = $filename;
					$this->fileParameters['ABSOLUTE_PATH'] = $_SERVER['DOCUMENT_ROOT'].$filename;
				}
				unset($filename, $rawFilename);
			}
			$this->fileParameters['FILES_DIRECTORY'] = mb_substr(
				$this->fileParameters['ABSOLUTE_PATH'],
				0,
				mb_strrpos($this->fileParameters['ABSOLUTE_PATH'], '/') + 1
			);
		}

		$parameters['IBLOCK_TYPE'] = trim($parameters['IBLOCK_TYPE']);

		if (!is_array($parameters['SITE_LIST']))
			$parameters['SITE_LIST'] = [$parameters['SITE_LIST']];

	}

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	private function getParameter($name)
	{
		$name = (string)$name;
		if ($name === '')
			return null;
		return (isset($this->parameters[$name]) ? $this->parameters[$name] : null);
	}

	/**
	 * @return array
	 */
	private function getDefaultParameters()
	{
		return [
			'FILE' => '',
			'IBLOCK_TYPE' => '',
			'SITE_LIST' => [],
			'MISSING_SECTION_ACTION' => self::ACTION_NOTHING,
			'MISSING_ELEMENT_ACTION' => self::ACTION_NOTHING,
			'INTERVAL' => 30
		];
	}

	/**
	 * @param array $config
	 * @return void
	 */
	private function setConfig(array $config)
	{
		$this->prepareConfig($config);
		if (!$this->isSuccess())
			return;
		$this->config = $config;
	}

	/**
	 * @param array &$config
	 * @return void
	 */
	private function prepareConfig(array &$config)
	{
		$config = array_filter($config, [__CLASS__, 'clearNull']);
		$config = array_merge($this->getDefaultConfig(), $config);
	}

	/**
	 * @return array
	 */
	private function getConfig()
	{
		return $this->config;
	}

	/**
	 * @param string $field
	 * @return mixed|null
	 */
	private function getConfigFieldValue($field)
	{
		$field = (string)$field;
		if ($field === '')
			return null;
		return (isset($this->config[$field]) ? $this->config[$field] : null);
	}

	/**
	 * @return array
	 */
	private function getDefaultConfig()
	{
		return [
			'USE_CRC' => true,
			'PREVIEW_PICTURE_SETTINGS' => false,
			'DETAIL_PICTURE_SETTINGS' => false,
			'USE_OFFERS' => false, // ?
			'FORCE_OFFERS' => false, // ?
			'USE_IBLOCK_TYPE_ID' => false, // ?
			'TRANSLITERATION' => [
				'MODE' => 0,
				'SETTINGS' => [
					'TRANS_LEN' => 255,
					'TRANS_CASE' => 'L',
					'TRANS_SPACE' => '-',
					'TRANS_OTHER' => '-',
					'TRANS_EAT' => 'Y',
				],
			],
			'SKIP_ROOT_SECTION' => false, // ?
			'DISABLE_CHANGE_PRICE_NAME' => false,
			'TABLE_NAME' => 'b_xml_tree',
			'READ_BLOCKSIZE' => 1024,
			'IBLOCK_CACHE_MODE' => \CIBlockCMLImport::IBLOCK_CACHE_FINAL
		];
	}

	/**
	 * @return mixed
	 */
	private function getCurrentStep()
	{
		return $this->stepId;
	}

	/**
	 * @param mixed $step
	 * @return void
	 */
	private function setCurrentStep($step)
	{
		$this->stepId = $step;
	}

	/**
	 * @return void
	 */
	private function nextStep()
	{
		$index = array_search($this->getCurrentStep(), $this->stepList);
		if (isset($this->stepList[$index+1]))
			$this->setCurrentStep($this->stepList[$index+1]);
		else
			$this->final = true;
	}

	/**
	 * @param string $message
	 * @return void
	 */
	private function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	private function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return void
	 */
	private function clearProgressCounter()
	{
		$this->progressCounter = [];
	}

	/**
	 * @param int $total
	 * @param int $current
	 * @return void
	 */
	private function setProgressCounter($total, $current)
	{
		$this->progressCounter = [
			'TOTAL' => $total,
			'CURRENT' => $current
		];
	}

	/**
	 * @return array|null
	 */
	private function getProgressCounter()
	{
		return (!empty($this->progressCounter) ? $this->progressCounter : null);
	}

	/**
	 * @return void
	 */
	private function setXmlImporterParameters()
	{
		$this->xmlImport->InitEx($this->stepParameters, $this->getXmlImporterConfig());
	}

	/**
	 * @return array
	 */
	private function getXmlImporterConfig()
	{
		$config = $this->getConfig();
		$result = [
			'files_dir' => $this->fileParameters['FILES_DIRECTORY'],
			'use_crc' => $config['USE_CRC'],
			'preview' => $config['PREVIEW_PICTURE_SETTINGS'],
			'detail' => $config['DETAIL_PICTURE_SETTINGS'],
			'use_offers' => $config['USE_OFFERS'],
			'force_offers' => $config['FORCE_OFFERS'],
			'use_iblock_type_id' => $config['USE_IBLOCK_TYPE_ID'],
			'skip_root_section' => $config['SKIP_ROOT_SECTION'],
			'disable_change_price_name' => $config['DISABLE_CHANGE_PRICE_NAME'],
			'table_name' => $config['TABLE_NAME']
		] + $this->getXmlImporterTransliterationSettings();
		unset($config);
		return $result;
	}

	/**
	 * @return void
	 */
	private function initTemporaryTablesAction()
	{
		$this->xmlImport->DropTemporaryTables();
		if (!$this->xmlImport->CreateTemporaryTables())
		{
			$this->addError(Loc::getMessage('IBLOCK_XML_IMPORT_ERR_CANNOT_CREATE_TEMPORARY_TABLES'));
		}
		$this->nextStep();
	}

	/**
	 * @return void
	 */
	private function readXmlAction()
	{
		$this->openXmlFile();
		if (!$this->isSuccess())
			return;
		if ($this->xmlImport->ReadXMLToDatabase(
			$this->fileHandler,
			$this->stepParameters,
			$this->getParameter('INTERVAL'),
			$this->getConfigFieldValue('READ_BLOCKSIZE')
		))
		{
			$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_XML_FILE_READ_COMPLETE'));
			$this->nextStep();
		}
		else
		{
			$this->setMessage(Loc::getMessage(
				'IBLOCK_XML_IMPORT_MESS_XML_FILE_READ_PROGRESS',
				['#PERCENT#' => $this->getXmlFileProgressPercent()]
			));
		}
		$this->closeXmlFile();
	}

	/**
	 * @return void
	 */
	private function indexTemporaryTablesAction()
	{
		if (!$this->xmlImport->IndexTemporaryTables())
		{
			$this->addError(Loc::getMessage('IBLOCK_XML_IMPORT_ERR_CANNOT_CREATE_TEMPORARY_TABLES_INDEX'));
			return;
		}
		$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_CREATE_TEMPORARY_TABLES_INDEX_COMPLETE'));
		$this->nextStep();
	}

	/**
	 * @return void
	 */
	private function importMetadataAction()
	{
		$result = $this->xmlImport->ImportMetaData(
			$this->xmlImport->GetRoot(),
			$this->getParameter('IBLOCK_TYPE'),
			$this->getParameter('SITE_LIST')
		);
		if ($result === true)
		{
			$this->nextStep();
			$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_METADATA_IMPORT_COMPLETE'));
		}
		else
		{
			if (is_array($result))
				$result = "\n".implode("\n", $result);
			$this->addError(Loc::getMessage(
				'IBLOCK_XML_IMPORT_ERR_METADATA_IMPORT_FAILURE',
				['#ERROR#' => $result]
			));
		}
		unset($result);
	}

	/**
	 * @return void
	 */
	private function importSectionsAction()
	{
		$this->xmlImport->freezeIblockCache();
		$result = $this->xmlImport->ImportSections();
		$this->xmlImport->unFreezeIblockCache();
		$this->xmlImport->clearIblockCacheOnHit();
		if ($result === true)
		{
			$this->nextStep();
			$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_IBLOCK_SECTIONS_IMPORT_COMPLETE'));
		}
		else
		{
			$this->addError(Loc::getMessage(
				'IBLOCK_XML_IMPORT_ERR_IBLOCK_SECTIONS_IMPORT_FAILURE',
				['#ERROR#' => $result]
			));
		}
	}

	/**
	 * @return void
	 */
	private function processMissingSectionsAction()
	{
		$this->xmlImport->freezeIblockCache();
		$this->xmlImport->DeactivateSections($this->getParameter('MISSING_SECTION_ACTION'));
		$this->xmlImport->SectionsResort();
		$this->xmlImport->unFreezeIblockCache();
		$this->xmlImport->clearIblockCacheOnHit();
		$this->nextStep();
		$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_PROCESS_MISSING_IBLOCK_SECTIONS_COMPLETE'));
	}

	/**
	 * @return void
	 */
	private function resortSectionsAction()
	{
		$this->xmlImport->freezeIblockCache();
		$this->xmlImport->SectionsResort();
		$this->xmlImport->unFreezeIblockCache();
		$this->xmlImport->clearIblockCacheOnHit();
		$this->nextStep();
		$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_IBLOCK_SECTIONS_RESORT_COMPLETE'));
	}

	/**
	 * @return void
	 */
	private function importElementsAction()
	{
		$this->xmlImport->freezeIblockCache();
		$result = $this->xmlImport->GetTotalCountElementsForImport();
		if (!$result)
		{
			$this->addError(Loc::getMessage(
				'IBLOCK_XML_IMPORT_ERR_ELEMENTS_IMPORT_FAILURE',
				['#ERROR#' => $this->xmlImport->LAST_ERROR]
			));
			return;
		}
		$this->xmlImport->ReadCatalogData(
			$_SESSION[self::SESSION_STORAGE_ID]['SECTIONS_MAP'],
			$_SESSION[self::SESSION_STORAGE_ID]['PRICES_MAP']
		);
		$result = $this->xmlImport->ImportElements(
			$this->startTime,
			$this->getParameter('INTERVAL')
		);
		$result = $this->xmlImport->updateCounters($result);
		$this->xmlImport->unFreezeIblockCache();
		$this->xmlImport->clearIblockCacheOnHit();
		if ($result == 0)
		{
			$this->nextStep();
			$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_IBLOCK_ELEMENTS_IMPORT_COMPLETE'));
		}
		else
		{
			$this->setMessage(Loc::getMessage(
				'IBLOCK_XML_IMPORT_MESS_IBLOCK_ELEMENTS_IMPORT_PROGRESS',
				[
					'#TOTAL#' => $this->stepParameters['DONE']['ALL'],
					'#DONE#' => $this->stepParameters['DONE']['CRC']
				]
			));
			$this->setProgressCounter(
				$this->stepParameters['DONE']['ALL'],
				$this->stepParameters['DONE']['CRC']
			);
		}
	}

	/**
	 * @return void
	 */
	private function processMissingElementsAction()
	{
		$this->xmlImport->freezeIblockCache();
		$result = $this->xmlImport->DeactivateElement(
			$this->getParameter('MISSING_ELEMENT_ACTION'),
			$this->startTime,
			$this->getParameter('INTERVAL')
		);
		$result = $this->xmlImport->updateCounters($result);
		$this->xmlImport->unFreezeIblockCache();
		$this->xmlImport->clearIblockCacheOnHit();
		if ($result == 0)
		{
			$this->nextStep();
			$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_PROCESS_MISSING_IBLOCK_ELEMENTS_COMPLETE'));
		}
		else
		{
			$this->setMessage(Loc::getMessage(
				'IBLOCK_XML_IMPORT_MESS_IBLOCK_ELEMENTS_IMPORT_PROGRESS',
				[
					'#TOTAL#' => $this->stepParameters['DONE']['ALL'],
					'#DONE#' => $this->stepParameters['DONE']['NON']
				]
			));
			$this->setProgressCounter(
				$this->stepParameters['DONE']['ALL'],
				$this->stepParameters['DONE']['NON']
			);
		}
	}

	/**
	 * @return void
	 */
	private function importProductBundlesAction()
	{
		$this->xmlImport->ImportProductSets();
		$this->nextStep();
		$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_PRODUCT_BUNDLES_IMPORT_COMPLETE'));
	}

	/**
	 * @return void
	 */
	private function finalAction()
	{
		$this->xmlImport->clearIblockCacheAfterFinal();
		$this->iblockId = $this->stepParameters['IBLOCK_ID'];
		$this->nextStep();
		$this->setMessage(Loc::getMessage('IBLOCK_XML_IMPORT_MESS_FINAL_SUCCESS'));
		$this->destroyXmlImporter();
		$this->destroySessionStorage();
	}

	/**
	 * @return void
	 */
	private function openXmlFile()
	{
		$this->closeXmlFile();

		if ($this->fileParameters['ABSOLUTE_PATH'] == '')
		{
			$this->addError(Loc::getMessage('IBLOCK_XML_IMPORT_ERR_OPEN_XML_FILE'));
			return;
		}
		$this->fileHandler = fopen($this->fileParameters['ABSOLUTE_PATH'], 'rb');
		if (!is_resource($this->fileHandler))
		{
			$this->addError(Loc::getMessage('IBLOCK_XML_IMPORT_ERR_OPEN_XML_FILE'));
			return;
		}
		$this->fileParameters['SIZE'] = (int)filesize($this->fileParameters['ABSOLUTE_PATH']);
		if ($this->fileParameters['SIZE'] <= 0)
		{
			$this->addError(Loc::getMessage('IBLOCK_XML_IMPORT_ERR_OPEN_XML_FILE'));
			return;
		}
	}

	/**
	 * @return void
	 */
	private function closeXmlFile()
	{
		if (!is_resource($this->fileHandler))
			return;
		fclose($this->fileHandler);
		$this->fileHandler = null;
	}

	/**
	 * @return float|int
	 */
	private function getXmlFileProgressPercent()
	{
		if (!is_resource($this->fileHandler))
			return 0;
		if ($this->fileParameters['SIZE'] <= 0)
			return 0;
		return round($this->xmlImport->GetFilePosition()*100/$this->fileParameters['SIZE'], 2);
	}

	/**
	 * @return void
	 */
	private function createXmlImporter()
	{
		$this->xmlImport = new CIBlockCMLImport();
	}

	/**
	 * @return void
	 */
	private function destroyXmlImporter()
	{
		if (is_object($this->xmlImport))
			$this->xmlImport = null;
	}

	/**
	 * @return void
	 */
	private function destroySessionStorage()
	{
		unset($this->stepId);
		unset($this->stepParameters);
		if (array_key_exists(self::SESSION_STORAGE_ID, $_SESSION))
			unset($_SESSION[self::SESSION_STORAGE_ID]);
	}

	/**
	 * @param int $currentValue
	 * @param int $mode
	 * @return bool
	 */
	private function checkTranslitMode($currentValue, $mode)
	{
		return ($currentValue & $mode) > 0;
	}

	/**
	 * @return array
	 */
	private function getXmlImporterTransliterationSettings()
	{
		$config = $this->getConfigFieldValue('TRANSLITERATION');
		$result = [
			'translit_on_add' => $this->checkTranslitMode(
				$config['MODE'],
				self::TRANSLITERATION_ON_ADD
			),
			'translit_on_update' => $this->checkTranslitMode(
				$config['MODE'],
				self::TRANSLITERATION_ON_UPDATE
			),
			'translit_params' => [
				'max_len' => $config['SETTINGS']['TRANS_LEN'],
				'change_case' => $config['SETTINGS']['TRANS_CASE'],
				'replace_space' => $config['SETTINGS']['TRANS_SPACE'],
				'replace_other' => $config['SETTINGS']['TRANS_OTHER'],
				'delete_repeat_replace' => $config['SETTINGS']['TRANS_EAT'] == 'Y'
			]
		];
		unset($config);
		return $result;
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	private static function clearNull($value)
	{
		return $value !== null;
	}
}