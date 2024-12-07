<?php
IncludeModuleLangFile(__FILE__);

/**
 * @property int $ID
 * @property string $ACTIVE
 * @property int $SORT
 * @property string $READ_ONLY
 * @property string $SERVICE_ID
 * @property string $BUCKET
 * @property string $LOCATION
 * @property string $CNAME
 * @property int $FILE_COUNT
 * @property float $FILE_SIZE
 * @property int $LAST_FILE_ID
 * @property string $PREFIX
 * @property string $SETTINGS
 * @property string $FILE_RULES
 */
class CCloudStorageBucket
{
	protected/*.int.*/$_ID = 0;
	protected/*.array[string]string.*/$arBucket;
	protected $enabledFailover = true;
	protected/*.CCloudStorageBucket.*/$failoverBucket;
	protected $queueFlag = true;
	/** @var CCloudStorageService $service */
	protected/*.CCloudStorageService.*/ $service;
	protected static/*.array[int][string]string.*/$arBuckets;

	/**
	 * @param int $ID
	 * @param bool $enabledFailover
	 */
	public function __construct($ID, $enabledFailover = true)
	{
		$this->_ID = intval($ID);
		if (!$enabledFailover)
		{
			$this->disableFailOver();
		}
	}

	public function disableFailOver()
	{
		$this->enabledFailover = false;
	}

	/**
	 * @return bool
	 */
	public function isFailoverEnabled()
	{
		return $this->enabledFailover;
	}

	/**
	 * @param bool $queueFlag
	 */
	public function setQueueFlag($queueFlag = true)
	{
		$this->queueFlag = (bool)$queueFlag;
	}

	/**
	 * @return bool
	 */
	public function getQueueFlag()
	{
		return $this->queueFlag;
	}

	protected function GetActualBucketId()
	{
		if (
			$this->isFailoverEnabled() && CCloudFailover::IsEnabled()
			&& $this->FAILOVER_ACTIVE === 'Y'
			&& $this->FAILOVER_BUCKET_ID > 0
		)
		{
			return $this->FAILOVER_BUCKET_ID;
		}
		else
		{
			return $this->ID;
		}
	}

	/**
	 * @return array[string]string
	*/
	public function getBucketArray()
	{
		if (!isset($this->arBucket))
		{
			self::_init();
			$this->arBucket = self::$arBuckets[$this->_ID];
			if (
				$this->isFailoverEnabled() && CCloudFailover::IsEnabled()
				&& $this->arBucket['FAILOVER_ACTIVE'] === 'Y'
				&& $this->arBucket['FAILOVER_BUCKET_ID'] > 0
			)
			{
				$this->failoverBucket = new CCloudStorageBucket($this->FAILOVER_BUCKET_ID, false);
				if ($this->failoverBucket->Init())
				{
					$this->arBucket['SERVICE_ID'] = $this->failoverBucket->SERVICE_ID;
					$this->arBucket['BUCKET'] = $this->failoverBucket->BUCKET;
					$this->arBucket['LOCATION'] = $this->failoverBucket->LOCATION;
					$this->arBucket['CNAME'] = $this->failoverBucket->CNAME;
					$this->arBucket['PREFIX'] = $this->failoverBucket->PREFIX;
					$this->arBucket['SETTINGS'] = $this->failoverBucket->SETTINGS;
				}
				else
				{
					$this->failoverBucket = null;
				}
			}
		}

		return $this->arBucket;
	}

	/**
	 * @return CCloudStorageService
	*/
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @param string $str
	 * @return string
	*/
	private static function CompileModuleRule($str)
	{
		$res = [];
		$ar = explode(',', $str);
		foreach ($ar as $s)
		{
			$s = trim($s);
			if ($s !== '')
			{
				$res[$s] = preg_quote($s, '/');
			}
		}
		if (!empty($res))
		{
			return '/^(' . implode('|', $res) . ')$/';
		}
		else
		{
			return '';
		}
	}

	/**
	 * @param string $str
	 * @return string
	*/
	private static function CompileExtentionRule($str)
	{
		$res = [];
		$ar = explode(',', $str);
		foreach ($ar as $s)
		{
			$s = trim($s);
			if ($s !== '')
			{
				$res[$s] = preg_quote('.' . $s, '/');
			}
		}
		if (!empty($res))
		{
			return '/(' . implode('|', $res) . ')$/i';
		}
		else
		{
			return '';
		}
	}

	/**
	 * @param string $str
	 * @return float
	*/
	private static function ParseSize($str)
	{
		static $scale = [
			'' => 1.0,
			'K' => 1024.0,
			'M' => 1048576.0,
			'G' => 1073741824.0,
		];
		$str = mb_strtoupper(trim($str));
		if ($str !== '' && preg_match('/([0-9.]+)(|K|M|G)$/', $str, $match) > 0)
		{
			return doubleval($match[1]) * $scale[$match[2]];
		}
		else
		{
			return 0.0;
		}
	}

	/**
	 * @param string $str
	 * @return array[int][int]double
	*/
	private static function CompileSizeRule($str)
	{
		$res = /*.(array[int][int]double).*/[];
		$ar = explode(',', $str);
		foreach ($ar as $s)
		{
			$s = trim($s);
			if ($s !== '')
			{
				$arSize = explode('-', $s);
				if (count($arSize) == 1)
				{
					$res[] = [self::ParseSize($arSize[0]), self::ParseSize($arSize[0])];
				}
				else
				{
					$res[] = [self::ParseSize($arSize[0]), self::ParseSize($arSize[1])];
				}
			}
		}
		return $res;
	}

	/**
	 * @param array[int][string]string $arRules
	 * @return array[int][string]string
	*/
	private static function CompileRules($arRules)
	{
		$arCompiled = /*.(array[int][string]string).*/[];
		if (is_array($arRules))
		{
			foreach ($arRules as $rule)
			{
				if (is_array($rule))
				{
					$arCompiled[] = [
						'MODULE_MASK' => isset($rule['MODULE']) ? self::CompileModuleRule($rule['MODULE']) : '',
						'EXTENTION_MASK' => isset($rule['EXTENSION']) ? self::CompileExtentionRule($rule['EXTENSION']) : '',
						'SIZE_ARRAY' => isset($rule['SIZE']) ? self::CompileSizeRule($rule['SIZE']) : '',
					];
				}
			}
		}
		return $arCompiled;
	}

	/**
	 * @return void
	*/
	private static function _init()
	{
		global $DB, $CACHE_MANAGER;

		if (isset(self::$arBuckets))
		{
			return;
		}

		$cache_id = 'cloud_buckets_v2';
		if (
			CACHED_b_clouds_file_bucket !== false
			&& $CACHE_MANAGER->Read(CACHED_b_clouds_file_bucket, $cache_id, 'b_clouds_file_bucket')
		)
		{
			self::$arBuckets = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			self::$arBuckets = /*.(array[int]CCloudStorageBucket).*/[];

			$rs = $DB->Query('
				SELECT *
				FROM b_clouds_file_bucket
				ORDER BY SORT DESC, ID ASC
			');
			while (is_array($ar = $rs->Fetch()))
			{
				if ($ar['FILE_RULES'] != '')
				{
					$arRules = unserialize($ar['FILE_RULES'], ['allowed_classes' => false]);
				}
				else
				{
					$arRules = [];
				}

				$ar['FILE_RULES_COMPILED'] = self::CompileRules($arRules);

				if ($ar['SETTINGS'] != '')
				{
					$arSettings = unserialize($ar['SETTINGS'], ['allowed_classes' => false]);
				}
				else
				{
					$arSettings = [];
				}

				if (is_array($arSettings))
				{
					$ar['SETTINGS'] = $arSettings;
				}
				else
				{
					$ar['SETTINGS'] = [];
				}

				self::$arBuckets[intval($ar['ID'])] = $ar;
			}

			if (CACHED_b_clouds_file_bucket !== false)
			{
				$CACHE_MANAGER->Set($cache_id, self::$arBuckets);
			}
		}
	}

	/**
	 * @param string $name
	 * @return mixed
	*/
	public function __get($name)
	{
		$arBucket = $this->getBucketArray();
		if ($arBucket && array_key_exists($name, $arBucket))
		{
			return $arBucket[$name];
		}
		else
		{
			return null;
		}
	}

	/**
	 * @return bool
	*/
	public function Init()
	{
		if (is_object($this->service))
		{
			return true;
		}
		else
		{
			if ($this->SERVICE_ID)
			{
				$this->service = CCloudStorage::GetServiceByID($this->SERVICE_ID);
			}
			return is_object($this->service);
		}
	}

	/**
	 * @return bool
	*/
	public function RenewToken()
	{
		if ($this->service->tokenHasExpired)
		{
			$arBucket = $this->failoverBucket ? $this->failoverBucket->arBucket : $this->arBucket;
			$newSettings = false;
			foreach (GetModuleEvents('clouds', 'OnExpiredToken', true) as $arEvent)
			{
				$newSettings = ExecuteModuleEventEx($arEvent, [$arBucket]);
				if ($newSettings)
				{
					break;
				}
			}

			if ($newSettings)
			{
				if ($this->failoverBucket)
				{
					$updateResult = $this->failoverBucket->Update(['SETTINGS' => $newSettings]);
					$this->arBucket = null;
				}
				else
				{
					$updateResult = $this->Update(['SETTINGS' => $newSettings]);
				}

				if ($updateResult)
				{
					$this->service->tokenHasExpired = false;
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param array[string]string $arSettings
	 * @return bool
	*/
	public function CheckSettings(&$arSettings)
	{
		return $this->service->CheckSettings($this->arBucket, $arSettings);
	}

	/**
	 * @return bool
	*/
	public function CreateBucket()
	{
		return $this->service->CreateBucket($this->arBucket);
	}

	/**
	 * @param mixed $arFile
	 * @param bool $encoded
	 * @return string
	*/
	public function GetFileSRC($arFile, $encoded = true)
	{
		if (is_array($arFile) && isset($arFile['URN']))
		{
			return $this->service->GetFileSRC($this->arBucket, $arFile['URN'], $encoded);
		}
		else
		{
			return preg_replace("'(?<!:)/+'s", '/', $this->service->GetFileSRC($this->arBucket, $arFile, $encoded));
		}
	}

	/**
	 * @param string $filePath
	 * @return bool
	*/
	public function FileExists($filePath)
	{
		$result = $this->service->FileExists($this->arBucket, $filePath);
		if (!$result && $this->RenewToken())
		{
			$result = $this->service->FileExists($this->getBucketArray(), $filePath);
		}
		return $result;
	}

	/**
	 * @param mixed $arFile
	 * @param string $filePath
	 * @return bool
	*/
	public function DownloadToFile($arFile, $filePath)
	{
		$result = $this->service->DownloadToFile($this->arBucket, $arFile, $filePath);
		return $result;
	}

	/**
	 * @param string $filePath
	 * @param mixed $arFile
	 * @return bool
	*/
	public function SaveFile($filePath, $arFile)
	{
		$result = $this->service->SaveFile($this->arBucket, $filePath, $arFile);
		if (!$result && $this->RenewToken())
		{
			$result = $this->service->SaveFile($this->getBucketArray(), $filePath, $arFile);
		}

		if ($result)
		{
			if ($this->queueFlag)
			{
				CCloudFailover::queueCopy($this, $filePath);
			}

			foreach (GetModuleEvents('clouds', 'OnAfterSaveFile', true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$this, $arFile, $filePath]);
			}
		}
		return $result;
	}

	/**
	 * @param string $filePath
	 * @return bool
	*/
	public function DeleteFile($filePath, $fileSize = null)
	{
		$result = $this->service->DeleteFile($this->arBucket, $filePath);
		if (!$result && $this->RenewToken())
		{
			$result = $this->service->DeleteFile($this->getBucketArray(), $filePath);
		}

		if ($result)
		{
			if ($this->queueFlag)
			{
				CCloudFailover::queueDelete($this, $filePath);
			}

			$eventData = [
				'del' => 'Y',
				'size' => $fileSize,
			];
			foreach (GetModuleEvents('clouds', 'OnAfterDeleteFile', true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$this, $eventData, $filePath]);
			}
		}
		return $result;
	}

	/**
	 * @param mixed $arFile
	 * @param string $filePath
	 * @return bool
	*/
	public function FileCopy($arFile, $filePath)
	{
		$result = $this->service->FileCopy($this->arBucket, $arFile, $filePath);
		if (!$result && $this->RenewToken())
		{
			$result = $this->service->FileCopy($this->getBucketArray(), $arFile, $filePath);
		}

		if ($result)
		{
			if ($this->queueFlag)
			{
				CCloudFailover::queueCopy($this, $filePath);
			}

			foreach (GetModuleEvents('clouds', 'OnAfterCopyFile', true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$this, $arFile, $filePath]);
			}
		}
		return $result;
	}

	/**
	 * @param string $sourcePath
	 * @param string $targetPath
	 * @param bool $overwrite
	 * @return bool
	*/
	public function FileRename($sourcePath, $targetPath, $overwrite = true)
	{
		$result = $this->service->FileRename($this->arBucket, $sourcePath, $targetPath, $overwrite);
		if ($result)
		{
			if ($this->queueFlag)
			{
				CCloudFailover::queueRename($this, $sourcePath, $targetPath);
			}

			foreach (GetModuleEvents('clouds', 'OnAfterRenameFile', true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$this, $sourcePath, $targetPath]);
			}
		}
		return $result;
	}

	/**
	 * @param string $filePath
	 * @param bool $bRecursive
	 * @param int $pageSize
	 * @param string $pageMarker
	 * @return array[string][int]string
	*/
	public function ListFiles($filePath = '/', $bRecursive = false, $pageSize = 0, $pageMarker = '')
	{
		$result = $this->service->ListFiles($this->arBucket, $filePath, $bRecursive, $pageSize, $pageMarker);
		if (!$result && $this->RenewToken())
		{
			$result = $this->service->ListFiles($this->getBucketArray(), $filePath, $bRecursive, $pageSize, $pageMarker);
		}
		return $result;
	}

	/**
	 * @param string $filePath
	 * @return array|false
	*/
	public function GetFileInfo($filePath)
	{
		$DIR_NAME = mb_substr($filePath, 0, mb_strrpos($filePath, '/') + 1);
		$FILE_NAME = mb_substr($filePath, mb_strlen($DIR_NAME));

		$arFileInfo = $this->service->GetFileInfo($this->arBucket, $filePath);
		if ($arFileInfo === false && $this->RenewToken())
		{
			$arFileInfo = $this->service->GetFileInfo($this->getBucketArray(), $filePath);
		}

		if ($arFileInfo === null)
		{
			$arListing = $this->service->ListFiles($this->arBucket, $DIR_NAME, false);
			if (is_array($arListing))
			{
				foreach ($arListing['file'] as $i => $name)
				{
					if ($name === $FILE_NAME)
					{
						return [
							'name' => $name,
							'size' => $arListing['file_size'][$i],
							'mtime' => $arListing['file_mtime'][$i],
							'hash' => $arListing['file_hash'][$i],
						];
					}
				}
			}
		}
		elseif ($arFileInfo)
		{
			$arFileInfo['name'] = $FILE_NAME;
			return $arFileInfo;
		}
		return false;
	}

	/**
	 * @param string $filePath
	 * @return float
	*/
	public function GetFileSize($filePath)
	{
		$fileInfo = $this->GetFileInfo($filePath);
		if ($fileInfo)
		{
			return doubleval($fileInfo['size']);
		}
		else
		{
			return 0.0;
		}
	}

	/**
	 * @return array[int][string]string
	*/
	public static function GetAllBuckets()
	{
		self::_init();
		return self::$arBuckets;
	}

	/**
	 * @param array[string]string $arFields
	 * @param int $ID
	 * @return bool
	*/
	public function CheckFields(&$arFields, $ID)
	{
		global $APPLICATION;
		$aMsg = [];

		if (array_key_exists('ACTIVE', $arFields))
		{
			$arFields['ACTIVE'] = $arFields['ACTIVE'] === 'N' ? 'N' : 'Y';
		}

		if (array_key_exists('READ_ONLY', $arFields))
		{
			$arFields['READ_ONLY'] = $arFields['READ_ONLY'] === 'Y' ? 'Y' : 'N';
		}

		$arServices = CCloudStorage::GetServiceList();
		if (isset($arFields['SERVICE_ID']))
		{
			if (!array_key_exists($arFields['SERVICE_ID'], $arServices))
			{
				$aMsg[] = ['id' => 'SERVICE_ID', 'text' => GetMessage('CLO_STORAGE_WRONG_SERVICE')];
			}
		}

		if (isset($arFields['BUCKET']))
		{
			$arFields['BUCKET'] = trim($arFields['BUCKET']);

			$bBadLength = false;
			if (mb_strpos($arFields['BUCKET'], '.') !== false)
			{
				$arName = explode('.', $arFields['BUCKET']);
				$bBadLength = false;
				foreach ($arName as $str)
				{
					if (mb_strlen($str) < 2 || mb_strlen($str) > 63)
					{
						$bBadLength = true;
					}
				}
			}

			if ($arFields['BUCKET'] == '')
			{
				$aMsg[] = ['id' => 'BUCKET', 'text' => GetMessage('CLO_STORAGE_EMPTY_BUCKET')];
			}
			if (preg_match('/[^a-z0-9._-]/', $arFields['BUCKET']) > 0)
			{
				$aMsg[] = ['id' => 'BUCKET', 'text' => GetMessage('CLO_STORAGE_BAD_BUCKET_NAME')];
			}
			if (mb_strlen($arFields['BUCKET']) < 2 || mb_strlen($arFields['BUCKET']) > 63)
			{
				$aMsg[] = ['id' => 'BUCKET', 'text' => GetMessage('CLO_STORAGE_WRONG_BUCKET_NAME_LENGTH')];
			}
			if ($bBadLength)
			{
				$aMsg[] = ['id' => 'BUCKET', 'text' => GetMessage('CLO_STORAGE_WRONG_BUCKET_NAME_LENGTH2')];
			}
			if (!preg_match('/^[a-z0-9].*[a-z0-9]$/', $arFields['BUCKET']))
			{
				$aMsg[] = ['id' => 'BUCKET', 'text' => GetMessage('CLO_STORAGE_BAD_BUCKET_NAME2')];
			}
			if (preg_match('/(-\\.|\\.-)/', $arFields['BUCKET']) > 0)
			{
				$aMsg[] = ['id' => 'BUCKET', 'text' => GetMessage('CLO_STORAGE_BAD_BUCKET_NAME3')];
			}

			if ($arFields['BUCKET'] <> '')
			{
				$rsBucket = self::GetList([], [
					'=SERVICE_ID' => $arFields['SERVICE_ID'],
					'=BUCKET' => $arFields['BUCKET'],
				]);
				$arBucket = $rsBucket->Fetch();
				if (is_array($arBucket) && $arBucket['ID'] != $ID)
				{
					$aMsg[] = ['id' => 'BUCKET', 'text' => GetMessage('CLO_STORAGE_BUCKET_ALREADY_EXISTS')];
				}
			}
		}

		if (array_key_exists('FAILOVER_ACTIVE', $arFields))
		{
			$arFields['FAILOVER_ACTIVE'] = $arFields['FAILOVER_ACTIVE'] === 'Y' ? 'Y' : 'N';
		}

		if (isset($arFields['FAILOVER_BUCKET_ID']) && $arFields['FAILOVER_BUCKET_ID'] == $ID)
		{
			unset($arFields['FAILOVER_BUCKET_ID']);
		}

		if (array_key_exists('FAILOVER_COPY', $arFields))
		{
			$arFields['FAILOVER_COPY'] = $arFields['FAILOVER_COPY'] === 'Y' ? 'Y' : 'N';
		}

		if (array_key_exists('FAILOVER_DELETE', $arFields))
		{
			$arFields['FAILOVER_DELETE'] = $arFields['FAILOVER_DELETE'] === 'Y' ? 'Y' : 'N';
		}

		if (array_key_exists('FAILOVER_DELETE_DELAY', $arFields))
		{
			$arFields['FAILOVER_DELETE_DELAY'] = (int)$arFields['FAILOVER_DELETE_DELAY'];
		}

		if (array_key_exists('CNAME', $arFields))
		{
			$arFields['CNAME'] = preg_replace('#^https?://#i', '', $arFields['CNAME']);
		}

		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}

	/**
	 * @param array[string]string $arOrder
	 * @param array[string]string $arFilter
	 * @param array[string]string $arSelect
	 * @return CDBResult
	*/
	public static function GetList($arOrder=[], $arFilter=[], $arSelect=[])
	{
		global $DB;

		if (!is_array($arSelect))
		{
			$arSelect = /*.(array[string]string).*/[];
		}
		if (count($arSelect) < 1)
		{
			$arSelect = [
				'ID',
				'ACTIVE',
				'READ_ONLY',
				'SORT',
				'SERVICE_ID',
				'LOCATION',
				'BUCKET',
				'SETTINGS',
				'CNAME',
				'PREFIX',
				'FILE_COUNT',
				'FILE_SIZE',
				'LAST_FILE_ID',
				'FILE_RULES',
				'FAILOVER_ACTIVE',
				'FAILOVER_BUCKET_ID',
				'FAILOVER_COPY',
				'FAILOVER_DELETE',
				'FAILOVER_DELETE_DELAY',
			];
		}

		if (!is_array($arOrder))
		{
			$arOrder = /*.(array[string]string).*/[];
		}

		$arQueryOrder = [];
		foreach ($arOrder as $strColumn => $strDirection)
		{
			$strColumn = mb_strtoupper($strColumn);
			$strDirection = mb_strtoupper($strDirection) === 'ASC' ? 'ASC' : 'DESC';
			switch ($strColumn)
			{
				case 'ID':
				case 'SORT':
					$arSelect[] = $strColumn;
					$arQueryOrder[$strColumn] = $strColumn . ' ' . $strDirection;
					break;
				default:
					break;
			}
		}

		$arQuerySelect = [];
		foreach ($arSelect as $strColumn)
		{
			$strColumn = mb_strtoupper($strColumn);
			switch ($strColumn)
			{
				case 'ID':
				case 'ACTIVE':
				case 'READ_ONLY':
				case 'SORT':
				case 'SERVICE_ID':
				case 'LOCATION':
				case 'BUCKET':
				case 'SETTINGS':
				case 'CNAME':
				case 'PREFIX':
				case 'FILE_COUNT':
				case 'FILE_SIZE':
				case 'LAST_FILE_ID':
				case 'FILE_RULES':
				case 'FAILOVER_ACTIVE':
				case 'FAILOVER_BUCKET_ID':
				case 'FAILOVER_COPY':
				case 'FAILOVER_DELETE':
				case 'FAILOVER_DELETE_DELAY':
					$arQuerySelect[$strColumn] = 's.' . $strColumn;
					break;
			}
		}
		if (count($arQuerySelect) < 1)
		{
			$arQuerySelect = ['ID' => 's.ID'];
		}

		$obQueryWhere = new CSQLWhere;
		$arFields = [
			'ID' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.ID',
				'FIELD_TYPE' => 'int',
			],
			'ACTIVE' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.ACTIVE',
				'FIELD_TYPE' => 'string',
			],
			'READ_ONLY' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.READ_ONLY',
				'FIELD_TYPE' => 'string',
			],
			'SERVICE_ID' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.SERVICE_ID',
				'FIELD_TYPE' => 'string',
			],
			'BUCKET' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.BUCKET',
				'FIELD_TYPE' => 'string',
			],
			'FAILOVER_ACTIVE' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.FAILOVER_ACTIVE',
				'FIELD_TYPE' => 'string',
			],
			'FAILOVER_BUCKET_ID' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.FAILOVER_BUCKET_ID',
				'FIELD_TYPE' => 'int',
			],
			'FAILOVER_COPY' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.FAILOVER_COPY',
				'FIELD_TYPE' => 'string',
			],
			'FAILOVER_DELETE' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.FAILOVER_DELETE',
				'FIELD_TYPE' => 'string',
			],
			'FAILOVER_DELETE_DELAY' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.FAILOVER_DELETE_DELAY',
				'FIELD_TYPE' => 'int',
			],
		];
		$obQueryWhere->SetFields($arFields);

		if (!is_array($arFilter))
		{
			$arFilter = /*.(array[string]string).*/[];
		}
		$strQueryWhere = $obQueryWhere->GetQuery($arFilter);

		$bDistinct = $obQueryWhere->bDistinctReqired;

		$strSql = '
			SELECT ' . ($bDistinct ? 'DISTINCT' : '') . '
			' . implode(', ', $arQuerySelect) . '
			FROM
				b_clouds_file_bucket s
			' . $obQueryWhere->GetJoins() . '
		';

		if ($strQueryWhere !== '')
		{
			$strSql .= '
				WHERE
				' . $strQueryWhere . '
			';
		}

		if (count($arQueryOrder) > 0)
		{
			$strSql .= '
				ORDER BY
				' . implode(', ', $arQueryOrder) . '
			';
		}

		return $DB->Query($strSql);
	}

	/**
	 * @param array[string]string $arFields
	 * @param bool $createBucket
	 * @return mixed
	*/
	public function Add($arFields, $createBucket = true)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER;
		$strError = '';
		$this->_ID = 0;

		if (!$this->CheckFields($arFields, 0))
		{
			return false;
		}

		$arFields['FILE_COUNT'] = 0;
		if (is_array($arFields['FILE_RULES']))
		{
			$arFields['FILE_RULES'] = serialize($arFields['FILE_RULES']);
		}
		else
		{
			$arFields['FILE_RULES'] = false;
		}

		$this->arBucket = $arFields;
		if ($this->Init())
		{
			if (!$this->CheckSettings($arFields['SETTINGS']))
			{
				return false;
			}
			$this->arBucket['SETTINGS'] = $arFields['SETTINGS'];

			if ($createBucket)
			{
				$creationResult = $this->CreateBucket();
			}
			else
			{
				$creationResult = true;
			}

			if ($creationResult)
			{
				$arFields['SETTINGS'] = serialize($arFields['SETTINGS']);
				$this->_ID = $DB->Add('b_clouds_file_bucket', $arFields);
				self::$arBuckets = null;
				$this->arBucket = null;
				if (CACHED_b_clouds_file_bucket !== false)
				{
					$CACHE_MANAGER->CleanDir('b_clouds_file_bucket');
				}
				return $this->_ID;
			}
			else
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
				{
					$strError = GetMessage('CLO_STORAGE_CLOUD_ADD_ERROR', ['#error_msg#' => $e->GetString()]);
				}
				else
				{
					$strError = GetMessage('CLO_STORAGE_CLOUD_ADD_ERROR', ['#error_msg#' => 'CSB42343']);
				}
			}
		}
		else
		{
			$strError = GetMessage('CLO_STORAGE_CLOUD_ADD_ERROR', ['#error_msg#' => GetMessage('CLO_STORAGE_UNKNOWN_SERVICE')]);
		}

		$APPLICATION->ResetException();
		$e = new CApplicationException($strError);
		$APPLICATION->ThrowException($e);
		return false;
	}

	/**
	 * @return bool
	*/
	public function Delete()
	{
		global $DB, $APPLICATION, $CACHE_MANAGER;
		$strError = '';

		if ($this->Init())
		{
			$isEmptyBucket = $this->service->IsEmptyBucket($this->arBucket);
			$forceDeleteTry = false;
			if (!$isEmptyBucket && is_object($APPLICATION->GetException()))
			{
				// The bucket was created within wrong s3 region
				if (
					$this->service->GetLastRequestStatus() == 301
					&& $this->service->GetLastRequestHeader('x-amz-bucket-region') !== ''
				)
				{
					$forceDeleteTry = true;
				}
			}

			if ($isEmptyBucket || $forceDeleteTry)
			{
				$isDeleted = $this->service->DeleteBucket($this->arBucket);
				$forceDelete = false;
				if (!$isDeleted && is_object($APPLICATION->GetException()))
				{
					// The bucket was created within wrong s3 region
					if (
						$this->service->GetLastRequestStatus() == 301
						&& $this->service->GetLastRequestHeader('x-amz-bucket-region') !== ''
					)
					{
						$forceDelete = true;
					}
				}

				if ($isDeleted || $forceDelete)
				{
					$res = $DB->Query('DELETE FROM b_clouds_file_bucket WHERE ID = ' . $this->_ID);
					if (CACHED_b_clouds_file_bucket !== false)
					{
						$CACHE_MANAGER->CleanDir('b_clouds_file_bucket');
					}
					if (is_object($res))
					{
						$this->arBucket = null;
						$this->_ID = 0;
						return true;
					}
					else
					{
						$strError = GetMessage('CLO_STORAGE_DB_DELETE_ERROR');
					}
				}
				else
				{
					$e = $APPLICATION->GetException();
					$strError = GetMessage('CLO_STORAGE_CLOUD_DELETE_ERROR', ['#error_msg#' => is_object($e) ? $e->GetString() : '']);
				}
			}
			else
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
				{
					$strError = GetMessage('CLO_STORAGE_CLOUD_DELETE_ERROR', ['#error_msg#' => $e->GetString()]);
				}
				else
				{
					$strError = GetMessage('CLO_STORAGE_CLOUD_BUCKET_NOT_EMPTY');
				}
			}
		}
		else
		{
			$strError = GetMessage('CLO_STORAGE_CLOUD_DELETE_ERROR', ['#error_msg#' => GetMessage('CLO_STORAGE_UNKNOWN_SERVICE')]);
		}

		$APPLICATION->ResetException();
		$e = new CApplicationException($strError);
		$APPLICATION->ThrowException($e);
		return false;
	}

	/**
	 * @param array[string]string $arFields
	 * @return mixed
	*/
	public function Update($arFields)
	{
		global $DB, $CACHE_MANAGER;

		if ($this->_ID <= 0)
		{
			return false;
		}

		unset($arFields['FILE_COUNT']);
		unset($arFields['SERVICE_ID']);
		unset($arFields['LOCATION']);
		unset($arFields['BUCKET']);

		if (
			array_key_exists('SETTINGS', $arFields)
			&& is_array($arFields['SETTINGS'])
			&& isset($arFields['SETTINGS']['MIGRATE_TO'])
			&& $arFields['SETTINGS']['MIGRATE_TO']
		)
		{
			$this->SERVICE_ID = $arFields['SERVICE_ID'] = $arFields['SETTINGS']['MIGRATE_TO'];
		}

		$this->service = CCloudStorage::GetServiceByID($this->SERVICE_ID);
		if (!is_object($this->service))
		{
			return false;
		}

		if (!$this->CheckFields($arFields, $this->_ID))
		{
			return false;
		}

		if (array_key_exists('FILE_RULES', $arFields))
		{
			if (is_array($arFields['FILE_RULES']))
			{
				$arFields['FILE_RULES'] = serialize($arFields['FILE_RULES']);
			}
			else
			{
				$arFields['FILE_RULES'] = false;
			}
		}

		if (array_key_exists('SETTINGS', $arFields))
		{
			if (!$this->CheckSettings($arFields['SETTINGS']))
			{
				return false;
			}
			$arFields['SETTINGS'] = serialize($arFields['SETTINGS']);
		}

		$strUpdate = $DB->PrepareUpdate('b_clouds_file_bucket', $arFields);
		if ($strUpdate <> '')
		{
			$strSql = '
				UPDATE b_clouds_file_bucket SET
				' . $strUpdate . '
				WHERE ID = ' . $this->_ID . '
			';
			if (!is_object($DB->Query($strSql)))
			{
				return false;
			}
		}

		self::$arBuckets = null;
		$this->arBucket = null;
		if (CACHED_b_clouds_file_bucket !== false)
		{
			$CACHE_MANAGER->CleanDir('b_clouds_file_bucket');
		}

		return $this->_ID;
	}

	/**
	 * @param array[string][int]string $arPOST
	 * @return array[int][string]string
	*/
	public static function ConvertPOST($arPOST)
	{
		$arRules = /*.(array[int][string]string).*/[];

		if (isset($arPOST['MODULE']) && is_array($arPOST['MODULE']))
		{
			foreach ($arPOST['MODULE'] as $i => $MODULE)
			{
				if (!isset($arRules[intval($i)]))
				{
					$arRules[intval($i)] = ['MODULE' => '', 'EXTENSION' => '', 'SIZE' => ''];
				}
				$arRules[intval($i)]['MODULE'] = $MODULE;
			}
		}

		if (isset($arPOST['EXTENSION']) && is_array($arPOST['EXTENSION']))
		{
			foreach ($arPOST['EXTENSION'] as $i => $EXTENSION)
			{
				if (!isset($arRules[intval($i)]))
				{
					$arRules[intval($i)] = ['MODULE' => '', 'EXTENSION' => '', 'SIZE' => ''];
				}
				$arRules[intval($i)]['EXTENSION'] = $EXTENSION;
			}
		}

		if (isset($arPOST['SIZE']) && is_array($arPOST['SIZE']))
		{
			foreach ($arPOST['SIZE'] as $i => $SIZE)
			{
				if (!isset($arRules[intval($i)]))
				{
					$arRules[intval($i)] = ['MODULE' => '', 'EXTENSION' => '', 'SIZE' => ''];
				}
				$arRules[intval($i)]['SIZE'] = $SIZE;
			}
		}

		return $arRules;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	*/
	public function setHeader($name, $value)
	{
		$this->service->SetHeader($name, $value);
	}

	/**
	 * @param float $file_size
	 * @param int $file_count
	 * @return CDBResult
	*/
	public function SetFileCounter($file_size, $file_count)
	{
		global $DB, $CACHE_MANAGER;
		$res = $DB->Query('
			UPDATE b_clouds_file_bucket
			SET FILE_COUNT = ' . intval($file_count) . '
			,FILE_SIZE = ' . roundDB($file_size) . '
			WHERE ID = ' . $this->GetActualBucketId() . '
		');

		if (CACHED_b_clouds_file_bucket !== false)
		{
			$CACHE_MANAGER->CleanDir('b_clouds_file_bucket');
		}
		return $res;
	}

	/**
	 * @param float $file_size
	 * @return CDBResult
	*/
	public function IncFileCounter($file_size = 0.0)
	{
		global $DB, $CACHE_MANAGER;
		$res = $DB->Query('
			UPDATE b_clouds_file_bucket
			SET FILE_COUNT = FILE_COUNT + 1
			,FILE_SIZE = FILE_SIZE + ' . roundDB($file_size) . '
			WHERE ID = ' . $this->GetActualBucketId() . '
		');

		if (defined('BX_CLOUDS_COUNTERS_DEBUG'))
		{
			\CCloudsDebug::getInstance()->endAction();
		}

		if ($file_size)
		{
			COption::SetOptionString('main_size', '~cloud', intval(COption::GetOptionString('main_size', '~cloud')) + $file_size);
		}

		if (CACHED_b_clouds_file_bucket !== false)
		{
			$CACHE_MANAGER->CleanDir('b_clouds_file_bucket');
		}
		return $res;
	}

	/**
	 * @param float $file_size
	 * @return CDBResult
	*/
	public function DecFileCounter($file_size = 0.0)
	{
		global $DB, $CACHE_MANAGER;
		$res = $DB->Query('
			UPDATE b_clouds_file_bucket
			SET FILE_COUNT = case when FILE_COUNT - 1 >= 0 then FILE_COUNT - 1 else 0 end
			,FILE_SIZE = case when FILE_SIZE - ' . roundDB($file_size) . ' >= 0 then FILE_SIZE - ' . roundDB($file_size) . ' else 0 end
			WHERE ID = ' . $this->GetActualBucketId() . '
		');

		if (defined('BX_CLOUDS_COUNTERS_DEBUG'))
		{
			\CCloudsDebug::getInstance()->endAction();
		}

		if ($file_size)
		{
			COption::SetOptionString('main_size', '~cloud', intval(COption::GetOptionString('main_size', '~cloud')) - $file_size);
		}

		if (CACHED_b_clouds_file_bucket !== false)
		{
			$CACHE_MANAGER->CleanDir('b_clouds_file_bucket');
		}
		return $res;
	}
}
