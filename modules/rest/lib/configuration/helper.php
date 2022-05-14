<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Option;
use Bitrix\Security\Filter\Auditor;
use Bitrix\Rest\UsageStatTable;
use Bitrix\Rest\Configuration\DataProvider\Disk\ProxyDiskType;

class Helper
{
	const TYPE_SECTION_TOTAL = 'total';
	const STRUCTURE_FILES_NAME = 'files';
	const STRUCTURE_SMALL_FILES_NAME = 'small_files';
	const CONFIGURATION_FILE_EXTENSION = '.json';
	const DEFAULT_ARCHIVE_NAME = 'configuration';
	const DEFAULT_ARCHIVE_FILE_EXTENSIONS = 'zip';

	public const MODE_IMPORT = 'IMPORT';
	public const MODE_ROLLBACK = 'ROLLBACK';
	public const MODE_EXPORT = 'EXPORT';

	protected $prefixStatisticBasic = 'DEFAULT_';
	protected $prefixAppContext = 'app';
	protected $prefixUserContext = 'configuration';
	protected $optionEnableZipMod = 'enable_mod_zip';
	protected $optionMaxImportFileSize = 'import_max_size';
	protected $optionBasicAppList = 'uses_basic_app_list';
	protected $defaultMaxSizeImport = 250;
	protected $appConfigurationFolderBackup = 'appConfiguration';
	protected $basicManifest = [
		'vertical_crm'
	];

	/** @var Helper|null  */
	private static $instance = null;
	private $sanitizer = null;
	private function __construct()
	{

	}

	/**
	 * @return Helper
	 */
	public static function getInstance(): Helper
	{
		if (self::$instance === null)
		{
			self::$instance = new Helper();
		}

		return self::$instance;
	}

	/**
	 * Enable or not main option zip_mode nginx
	 * @return bool
	 */
	public function enabledZipMod()
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			return true;
		}
		else
		{
			return Option::get('rest', $this->optionEnableZipMod, 'N') == 'Y';
		}
	}

	/**
	 * @return integer
	 */
	public function getMaxFileSize()
	{
		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			$size = Option::get('rest', $this->optionMaxImportFileSize, '');
		}

		if (empty($size))
		{
			$size = $this->defaultMaxSizeImport;
		}

		return $size;
	}

	/**
	 * @param $postfix string a-zA-Z0-9_
	 *
	 * @return string
	 */
	public function getContextUser($postfix)
	{
		$result = $this->prefixUserContext;

		$postfix = preg_replace('/[^a-zA-Z0-9_]/', '', $postfix);

		global $USER;
		if ($USER->IsAuthorized())
		{
			$user = $USER->GetID();
		}
		else
		{
			$user = 0;
		}

		$result .= $user.$postfix;
		return $result;
	}

	/**
	 * Context uses action
	 * @param integer $appId
	 * @return string context
	 */
	public function getContextAction($appId = 0)
	{
		$result = 'external';
		$appId = intval($appId);
		if ($appId > 0)
		{
			$result = $this->prefixAppContext.$appId;
		}

		return $result;
	}

	/**
	 * Sanitize bad value.
	 * @param string $value Bad value.
	 * @param bool &$bad Return true, if value is bad.
	 * @param string $splitter Splitter for bad content.
	 * @return string Good value.
	 */
	public function sanitize($value, &$bad = false, $splitter = ' ')
	{
		if (!is_bool($bad))
		{
			$bad = false;
		}

		if ($this->sanitizer === null)
		{
			$this->sanitizer = false;
			if (Loader::includeModule('security'))
			{
				$this->sanitizer = new Auditor\Xss(
					$splitter
				);
			}
		}

		if ($this->sanitizer)
		{
			// bad value exists
			if (is_array($value))
			{
				foreach ($value as &$val)
				{
					$val = $this->sanitize($val, $bad, $splitter);
				}
				unset($val);
			}
			elseif ($this->sanitizer->process($value))
			{
				$bad = true;
				$value = $this->sanitizer->getFilteredValue();
			}
		}

		return $value;
	}

	public function getStorageBackupParam()
	{
		return [
			'NAME' => $this->appConfigurationFolderBackup,
			'MODULE_ID' => 'rest',
			'ENTITY_TYPE' => ProxyDiskType::className(),
			'ENTITY_ID' => 1,
		];
	}

	public function getStorageBackup()
	{
		$storage = false;
		if (Loader::includeModule('disk'))
		{
			$storage = \Bitrix\Disk\Driver::getInstance()->addStorageIfNotExist(
				$this->getStorageBackupParam()
			);
		}
		return $storage;
	}

	//uses configuration app
	/**
	 * @param $code string
	 *
	 * @return boolean
	 */
	public function isBasicManifest($code)
	{
		//todo: DONT PUSH!
		return false;
		//return (in_array($code, $this->basicManifest)) ? true : false;
	}

	/**
	 * @param $manifestCode string
	 *
	 * @return boolean|string
	 */
	public function getBasicApp($manifestCode)
	{
		$result = false;
		$appList = $this->getBasicAppList();
		if (isset($appList[$manifestCode]))
		{
			$result = $appList[$manifestCode];
		}

		return $result;
	}

	/**
	 * @return array [ manifestCode => appCode ]
	 */
	public function getBasicAppList()
	{
		$data = Option::get('rest', $this->optionBasicAppList);
		if ($data)
		{
			$data = Json::decode($data);
		}
		else
		{
			$data = [];
		}

		return $data;
	}

	/**
	 * @param $manifestCode string
	 * @param $appCode string
	 * @return boolean
	 */
	public function setBasicApp($manifestCode, $appCode)
	{
		$result = false;
		if ($this->isBasicManifest($manifestCode))
		{
			$appList = $this->getBasicAppList();
			$appList[$manifestCode] = $appCode;
			Option::set('rest', $this->optionBasicAppList, Json::encode($appList));
			$result = true;
		}

		return $result;
	}

	/**
	 * @param $manifestCode string
	 * @return boolean
	 */
	public function deleteBasicApp($manifestCode)
	{
		$result = false;
		if ($this->isBasicManifest($manifestCode))
		{
			$appList = $this->getBasicAppList();
			if (isset($appList[$manifestCode]))
			{
				unset($appList[$manifestCode]);
				Option::set('rest', $this->optionBasicAppList, Json::encode($appList));
			}
			$result = true;
		}

		return $result;
	}

	/**
	 * @return boolean
	 */
	public function sendStatistic()
	{
		$appList = $this->getBasicAppList();
		foreach ($appList as $manifest => $appCode)
		{
			UsageStatTable::logConfiguration($appCode, $this->prefixStatisticBasic . mb_strtoupper($manifest));
		}
		UsageStatTable::finalize();

		return true;
	}

	/**
	 * Every day send statistic basic configuration app
	 * @return string
	 */
	public static function sendStatisticAgent()
	{
		self::getInstance()->sendStatistic();

		return '\Bitrix\Rest\Configuration\Helper::sendStatisticAgent();';
	}

	/**
	 * @deprecated use Manifest::isEntityAvailable()
	 *
	 * check Event manifest[USES] intersect current entity[USES]
	 * @param array $params all event parameters
	 * @param array $uses all access uses in current entity
	 *
	 * @return bool
	 */
	public static function checkAccessManifest($params, $uses = []): bool
	{
		return Manifest::isEntityAvailable('', $params, $uses);
	}
}