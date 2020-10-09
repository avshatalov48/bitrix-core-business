<?php
IncludeModuleLangFile(__FILE__);

/**
 * @property integer $ID
 * @property string $ACTIVE
 * @property integer $SORT
 * @property string $READ_ONLY
 * @property string $SERVICE_ID
 * @property string $BUCKET
 * @property string $LOCATION
 * @property string $CNAME
 * @property integer $FILE_COUNT
 * @property float $FILE_SIZE
 * @property integer $LAST_FILE_ID
 * @property string $PREFIX
 * @property string $SETTINGS
 * @property string $FILE_RULES
 */
class CCloudStorageBucket extends CAllCloudStorageBucket
{
	protected/*.array[string]string.*/$arBucket;
	protected $enabledFailover = true;
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
			$this->disableFailOver();
	}
	public function disableFailOver()
	{
		$this->enabledFailover = false;
	}
	/**
	 * @return boolean
	 */
	public function isFailoverEnabled()
	{
		return $this->enabledFailover;
	}
	/**
	 * @param boolean $queueFlag
	 */
	public function setQueueFlag($queueFlag = true)
	{
		$this->queueFlag = (bool)$queueFlag;
	}
	/**
	 * @return boolean
	 */
	public function getQueueFlag()
	{
		return $this->queueFlag;
	}
	/**
	 * @return array[string]string
	*/
	public function getBucketArray()
	{
		if(!isset($this->arBucket))
		{
			self::_init();
			$this->arBucket = self::$arBuckets[$this->_ID];
			if (
				$this->isFailoverEnabled() && CCloudFailover::IsEnabled()
				&& $this->arBucket["FAILOVER_ACTIVE"] === 'Y'
				&& $this->arBucket["FAILOVER_BUCKET_ID"] > 0
			)
			{
				$failoverBucket = new CCloudStorageBucket($this->FAILOVER_BUCKET_ID, false);
				if ($failoverBucket->Init())
				{
					$this->arBucket["SERVICE_ID"] = $failoverBucket->SERVICE_ID;
					$this->arBucket["BUCKET"] = $failoverBucket->BUCKET;
					$this->arBucket["LOCATION"] = $failoverBucket->LOCATION;
					$this->arBucket["CNAME"] = $failoverBucket->CNAME;
					$this->arBucket["PREFIX"] = $failoverBucket->PREFIX;
					$this->arBucket["SETTINGS"] = $failoverBucket->SETTINGS;
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
		$res = array();
		$ar = explode(",", $str);
		foreach($ar as $s)
		{
			$s = trim($s);
			if($s !== '')
				$res[$s] = preg_quote($s, '/');
		}
		if(!empty($res))
			return "/^(".implode("|", $res).")\$/";
		else
			return "";
	}
	/**
	 * @param string $str
	 * @return string
	*/
	private static function CompileExtentionRule($str)
	{
		$res = array();
		$ar = explode(",", $str);
		foreach($ar as $s)
		{
			$s = trim($s);
			if($s !== '')
				$res[$s] = preg_quote(".".$s, '/');
		}
		if(!empty($res))
			return "/(".implode("|", $res).")\$/i";
		else
			return "";
	}
	/**
	 * @param string $str
	 * @return double
	*/
	private static function ParseSize($str)
	{
		static $scale = array(
			'' => 1.0,
			'K' => 1024.0,
			'M' => 1048576.0,
			'G' => 1073741824.0,
		);
		$str = mb_strtoupper(trim($str));
		if($str !== '' && preg_match("/([0-9.]+)(|K|M|G)\$/", $str, $match) > 0)
		{
			return doubleval($match[1])*$scale[$match[2]];
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
		$res = /*.(array[int][int]double).*/array();
		$ar = explode(",", $str);
		foreach($ar as $s)
		{
			$s = trim($s);
			if($s !== '')
			{
				$arSize = explode("-", $s);
				if(count($arSize) == 1)
					$res[] = array(self::ParseSize($arSize[0]), self::ParseSize($arSize[0]));
				else
					$res[] = array(self::ParseSize($arSize[0]), self::ParseSize($arSize[1]));
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
		$arCompiled = /*.(array[int][string]string).*/array();
		if(is_array($arRules))
		{
			foreach($arRules as $rule)
			{
				if(is_array($rule))
				{
					$arCompiled[] = array(
						"MODULE_MASK" => isset($rule["MODULE"])? self::CompileModuleRule($rule["MODULE"]): "",
						"EXTENTION_MASK" => isset($rule["EXTENSION"])? self::CompileExtentionRule($rule["EXTENSION"]): "",
						"SIZE_ARRAY" => isset($rule["SIZE"])? self::CompileSizeRule($rule["SIZE"]): "",
					);
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

		if(isset(self::$arBuckets))
			return;

		$cache_id = "cloud_buckets_v2";
		if(
			CACHED_b_clouds_file_bucket !== false
			&& $CACHE_MANAGER->Read(CACHED_b_clouds_file_bucket, $cache_id, "b_clouds_file_bucket")
		)
		{
			self::$arBuckets = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			self::$arBuckets = /*.(array[int]CCloudStorageBucket).*/array();

			$rs = $DB->Query("
				SELECT *
				FROM b_clouds_file_bucket
				ORDER BY SORT DESC, ID ASC
			");
			while(is_array($ar = $rs->Fetch()))
			{
				if($ar["FILE_RULES"] != "")
					$arRules = unserialize($ar["FILE_RULES"]);
				else
					$arRules = array();

				$ar["FILE_RULES_COMPILED"] = self::CompileRules($arRules);

				if($ar["SETTINGS"] != "")
					$arSettings = unserialize($ar["SETTINGS"]);
				else
					$arSettings = array();

				if(is_array($arSettings))
					$ar["SETTINGS"] = $arSettings;
				else
					$ar["SETTINGS"] = array();

				self::$arBuckets[intval($ar['ID'])] = $ar;
			}

			if(CACHED_b_clouds_file_bucket !== false)
				$CACHE_MANAGER->Set($cache_id, self::$arBuckets);
		}
	}
	/**
	 * @param string $name
	 * @return mixed
	*/
	function __get($name)
	{
		$arBucket = $this->getBucketArray();
		if($arBucket && array_key_exists($name, $arBucket))
			return $arBucket[$name];
		else
			return null;
	}
	/**
	 * @return bool
	*/
	function Init()
	{
		if(is_object($this->service))
		{
			return true;
		}
		else
		{
			if($this->SERVICE_ID)
				$this->service = CCloudStorage::GetServiceByID($this->SERVICE_ID);
			return is_object($this->service);
		}
	}
	/**
	 * @return bool
	*/
	function RenewToken()
	{
		if ($this->service->tokenHasExpired)
		{
			$newSettings = false;
			foreach(GetModuleEvents("clouds", "OnExpiredToken", true) as $arEvent)
			{
				$newSettings = ExecuteModuleEventEx($arEvent, array($this->arBucket));
				if ($newSettings)
					break;
			}
			if ($newSettings)
			{
				$updateResult = $this->Update(array("SETTINGS" => $newSettings));
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
	function CheckSettings(&$arSettings)
	{
		return $this->service->CheckSettings($this->arBucket, $arSettings);
	}
	/**
	 * @return bool
	*/
	function CreateBucket()
	{
		return $this->service->CreateBucket($this->arBucket);
	}
	/**
	 * @param mixed $arFile
	 * @return string
	*/
	function GetFileSRC($arFile)
	{
		if(is_array($arFile) && isset($arFile["URN"]))
			return $this->service->GetFileSRC($this->arBucket, $arFile["URN"]);
		else
			return preg_replace("'(?<!:)/+'s", "/", $this->service->GetFileSRC($this->arBucket, $arFile));
	}
	/**
	 * @param string $filePath
	 * @return bool
	*/
	function FileExists($filePath)
	{
		return $this->service->FileExists($this->arBucket, $filePath);
	}
	/**
	 * @param mixed $arFile
	 * @param string $filePath
	 * @return bool
	*/
	function DownloadToFile($arFile, $filePath)
	{
		return $this->service->DownloadToFile($this->arBucket, $arFile, $filePath);
	}
	/**
	 * @param string $filePath
	 * @param mixed $arFile
	 * @return bool
	*/
	function SaveFile($filePath, $arFile)
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

			foreach(GetModuleEvents("clouds", "OnAfterSaveFile", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($this, $arFile, $filePath));
			}
		}
		return $result;
	}
	/**
	 * @param string $filePath
	 * @return bool
	*/
	function DeleteFile($filePath)
	{
		$result = $this->service->DeleteFile($this->arBucket, $filePath);
		if ($result)
		{
			if ($this->queueFlag)
			{
				CCloudFailover::queueDelete($this, $filePath);
			}

			foreach(GetModuleEvents("clouds", "OnAfterDeleteFile", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($this, array('del' => 'Y'), $filePath));
			}
		}
		return $result;
	}
	/**
	 * @param mixed $arFile
	 * @param string $filePath
	 * @return bool
	*/
	function FileCopy($arFile, $filePath)
	{
		$result = $this->service->FileCopy($this->arBucket, $arFile, $filePath);
		if ($result)
		{
			if ($this->queueFlag)
			{
				CCloudFailover::queueCopy($this, $filePath);
			}

			foreach(GetModuleEvents("clouds", "OnAfterCopyFile", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($this, $arFile, $filePath));
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
	function FileRename($sourcePath, $targetPath, $overwrite = true)
	{
		$result = $this->service->FileRename($this->arBucket, $sourcePath, $targetPath, $overwrite);
		if ($result)
		{
			if ($this->queueFlag)
			{
				CCloudFailover::queueRename($this, $sourcePath, $targetPath);
			}

			foreach(GetModuleEvents("clouds", "OnAfterRenameFile", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($this, $sourcePath, $targetPath));
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
	function ListFiles($filePath = "/", $bRecursive = false, $pageSize = 0, $pageMarker = '')
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
	function GetFileInfo($filePath)
	{
		$DIR_NAME = mb_substr($filePath, 0, mb_strrpos($filePath, "/") + 1);
		$FILE_NAME = mb_substr($filePath, mb_strlen($DIR_NAME));

		$arListing = $this->service->ListFiles($this->arBucket, $DIR_NAME, false);
		if(is_array($arListing))
		{
			foreach($arListing["file"] as $i => $name)
			{
				if($name === $FILE_NAME)
				{
					return array(
						"name" => $name,
						"size" => $arListing["file_size"][$i],
						"mtime" => $arListing["file_mtime"][$i],
						"hash" => $arListing["file_hash"][$i],
					);
				}
			}
		}
		return false;
	}
	/**
	 * @param string $filePath
	 * @return double
	*/
	function GetFileSize($filePath)
	{
		$fileInfo = $this->GetFileInfo($filePath);
		if ($fileInfo)
		{
			return doubleval($fileInfo["size"]);
		}
		else
		{
			return 0.0;
		}
	}
	/**
	 * @return array[int][string]string
	*/
	static function GetAllBuckets()
	{
		self::_init();
		return self::$arBuckets;
	}
	/**
	 * @param array[string]string $arFields
	 * @param int $ID
	 * @return bool
	*/
	function CheckFields(&$arFields, $ID)
	{
		global $APPLICATION;
		$aMsg = array();

		if(array_key_exists("ACTIVE", $arFields))
			$arFields["ACTIVE"] = $arFields["ACTIVE"] === "N"? "N": "Y";

		if(array_key_exists("READ_ONLY", $arFields))
			$arFields["READ_ONLY"] = $arFields["READ_ONLY"] === "Y"? "Y": "N";

		$arServices = CCloudStorage::GetServiceList();
		if(isset($arFields["SERVICE_ID"]))
		{
			if(!array_key_exists($arFields["SERVICE_ID"], $arServices))
				$aMsg[] = array("id" => "SERVICE_ID", "text" => GetMessage("CLO_STORAGE_WRONG_SERVICE"));
		}

		if(isset($arFields["BUCKET"]))
		{
			$arFields["BUCKET"] = trim($arFields["BUCKET"]);

			$bBadLength = false;
			if(mb_strpos($arFields["BUCKET"], ".") !== false)
			{
				$arName = explode(".", $arFields["BUCKET"]);
				$bBadLength = false;
				foreach($arName as $str)
					if(mb_strlen($str) < 2 || mb_strlen($str) > 63)
						$bBadLength = true;
			}

			if($arFields["BUCKET"] == '')
				$aMsg[] = array("id" => "BUCKET", "text" => GetMessage("CLO_STORAGE_EMPTY_BUCKET"));
			if(preg_match("/[^a-z0-9._-]/", $arFields["BUCKET"]) > 0)
				$aMsg[] = array("id" => "BUCKET", "text" => GetMessage("CLO_STORAGE_BAD_BUCKET_NAME"));
			if(mb_strlen($arFields["BUCKET"]) < 2 || mb_strlen($arFields["BUCKET"]) > 63)
				$aMsg[] = array("id" => "BUCKET", "text" => GetMessage("CLO_STORAGE_WRONG_BUCKET_NAME_LENGTH"));
			if($bBadLength)
				$aMsg[] = array("id" => "BUCKET", "text" => GetMessage("CLO_STORAGE_WRONG_BUCKET_NAME_LENGTH2"));
			if(!preg_match("/^[a-z0-9].*[a-z0-9]\$/", $arFields["BUCKET"]))
				$aMsg[] = array("id" => "BUCKET", "text" => GetMessage("CLO_STORAGE_BAD_BUCKET_NAME2"));
			if(preg_match("/(-\\.|\\.-)/", $arFields["BUCKET"]) > 0)
				$aMsg[] = array("id" => "BUCKET", "text" => GetMessage("CLO_STORAGE_BAD_BUCKET_NAME3"));

			if($arFields["BUCKET"] <> '')
			{
				$rsBucket = self::GetList(array(), array(
					"=SERVICE_ID" => $arFields["SERVICE_ID"],
					"=BUCKET" => $arFields["BUCKET"],
				));
				$arBucket = $rsBucket->Fetch();
				if(is_array($arBucket) && $arBucket["ID"] != $ID)
					$aMsg[] = array("id" => "BUCKET", "text" => GetMessage("CLO_STORAGE_BUCKET_ALREADY_EXISTS"));
			}
		}

		if(array_key_exists("FAILOVER_ACTIVE", $arFields))
		{
			$arFields["FAILOVER_ACTIVE"] = $arFields["FAILOVER_ACTIVE"] === "Y"? "Y": "N";
		}

		if(isset($arFields["FAILOVER_BUCKET_ID"]) && $arFields["FAILOVER_BUCKET_ID"] == $ID)
		{
			unset($arFields["FAILOVER_BUCKET_ID"]);
		}

		if(array_key_exists("FAILOVER_COPY", $arFields))
		{
			$arFields["FAILOVER_COPY"] = $arFields["FAILOVER_COPY"] === "Y"? "Y": "N";
		}

		if(array_key_exists("FAILOVER_DELETE", $arFields))
		{
			$arFields["FAILOVER_DELETE"] = $arFields["FAILOVER_DELETE"] === "Y"? "Y": "N";
		}

		if(array_key_exists("FAILOVER_DELETE_DELAY", $arFields))
		{
			$arFields["FAILOVER_DELETE_DELAY"] = (int)$arFields["FAILOVER_DELETE_DELAY"];
		}

		if(!empty($aMsg))
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
	static function GetList($arOrder=array(), $arFilter=array(), $arSelect=array())
	{
		global $DB;

		if(!is_array($arSelect))
			$arSelect =/*.(array[string]string).*/array();
		if(count($arSelect) < 1)
			$arSelect = array(
				"ID",
				"ACTIVE",
				"READ_ONLY",
				"SORT",
				"SERVICE_ID",
				"LOCATION",
				"BUCKET",
				"SETTINGS",
				"CNAME",
				"PREFIX",
				"FILE_COUNT",
				"FILE_SIZE",
				"LAST_FILE_ID",
				"FILE_RULES",
				"FAILOVER_ACTIVE",
				"FAILOVER_BUCKET_ID",
				"FAILOVER_COPY",
				"FAILOVER_DELETE",
				"FAILOVER_DELETE_DELAY",
			);

		if(!is_array($arOrder))
			$arOrder =/*.(array[string]string).*/array();

		$arQueryOrder = array();
		foreach($arOrder as $strColumn => $strDirection)
		{
			$strColumn = mb_strtoupper($strColumn);
			$strDirection = mb_strtoupper($strDirection) === "ASC"? "ASC": "DESC";
			switch($strColumn)
			{
				case "ID":
				case "SORT":
					$arSelect[] = $strColumn;
					$arQueryOrder[$strColumn] = $strColumn." ".$strDirection;
					break;
				default:
					break;
			}
		}

		$arQuerySelect = array();
		foreach($arSelect as $strColumn)
		{
			$strColumn = mb_strtoupper($strColumn);
			switch($strColumn)
			{
				case "ID":
				case "ACTIVE":
				case "READ_ONLY":
				case "SORT":
				case "SERVICE_ID":
				case "LOCATION":
				case "BUCKET":
				case "SETTINGS":
				case "CNAME":
				case "PREFIX":
				case "FILE_COUNT":
				case "FILE_SIZE":
				case "LAST_FILE_ID":
				case "FILE_RULES":
				case "FAILOVER_ACTIVE":
				case "FAILOVER_BUCKET_ID":
				case "FAILOVER_COPY":
				case "FAILOVER_DELETE":
				case "FAILOVER_DELETE_DELAY":
					$arQuerySelect[$strColumn] = "s.".$strColumn;
					break;
			}
		}
		if(count($arQuerySelect) < 1)
			$arQuerySelect = array("ID"=>"s.ID");

		$obQueryWhere = new CSQLWhere;
		$arFields = array(
			"ID" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.ID",
				"FIELD_TYPE" => "int",
			),
			"ACTIVE" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.ACTIVE",
				"FIELD_TYPE" => "string",
			),
			"READ_ONLY" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.READ_ONLY",
				"FIELD_TYPE" => "string",
			),
			"SERVICE_ID" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.SERVICE_ID",
				"FIELD_TYPE" => "string",
			),
			"BUCKET" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.BUCKET",
				"FIELD_TYPE" => "string",
			),
			"FAILOVER_ACTIVE" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.FAILOVER_ACTIVE",
				"FIELD_TYPE" => "string",
			),
			"FAILOVER_BUCKET_ID" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.FAILOVER_BUCKET_ID",
				"FIELD_TYPE" => "int",
			),
			"FAILOVER_COPY" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.FAILOVER_COPY",
				"FIELD_TYPE" => "string",
			),
			"FAILOVER_DELETE" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.FAILOVER_DELETE",
				"FIELD_TYPE" => "string",
			),
			"FAILOVER_DELETE_DELAY" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.FAILOVER_DELETE_DELAY",
				"FIELD_TYPE" => "int",
			),
		);
		$obQueryWhere->SetFields($arFields);

		if(!is_array($arFilter))
			$arFilter =/*.(array[string]string).*/array();
		$strQueryWhere = $obQueryWhere->GetQuery($arFilter);

		$bDistinct = $obQueryWhere->bDistinctReqired;

		$strSql = "
			SELECT ".($bDistinct? "DISTINCT": "")."
			".implode(", ", $arQuerySelect)."
			FROM
				b_clouds_file_bucket s
			".$obQueryWhere->GetJoins()."
		";

		if($strQueryWhere != "")
		{
			$strSql .= "
				WHERE
				".$strQueryWhere."
			";
		}

		if(count($arQueryOrder) > 0)
		{
			$strSql .= "
				ORDER BY
				".implode(", ", $arQueryOrder)."
			";
		}

		return $DB->Query($strSql);
	}
	/**
	 * @param array[string]string $arFields
	 * @param boolean $createBucket
	 * @return mixed
	*/
	function Add($arFields, $createBucket = true)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER;
		$strError = '';
		$this->_ID = 0;

		if(!$this->CheckFields($arFields, 0))
			return false;

		$arFields["FILE_COUNT"] = 0;
		if(is_array($arFields["FILE_RULES"]))
			$arFields["FILE_RULES"] = serialize($arFields["FILE_RULES"]);
		else
			$arFields["FILE_RULES"] = false;

		$this->arBucket = $arFields;
		if($this->Init())
		{

			if(!$this->CheckSettings($arFields["SETTINGS"]))
				return false;
			$this->arBucket["SETTINGS"] = $arFields["SETTINGS"];

			if ($createBucket)
				$creationResult = $this->CreateBucket();
			else
				$creationResult = true;

			if ($creationResult)
			{
				$arFields["SETTINGS"] = serialize($arFields["SETTINGS"]);
				$this->_ID = $DB->Add("b_clouds_file_bucket", $arFields);
				self::$arBuckets = null;
				$this->arBucket = null;
				if (CACHED_b_clouds_file_bucket !== false)
					$CACHE_MANAGER->CleanDir("b_clouds_file_bucket");
				return $this->_ID;
			}
			else
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
					$strError = GetMessage("CLO_STORAGE_CLOUD_ADD_ERROR", array("#error_msg#" => $e->GetString()));
				else
					$strError = GetMessage("CLO_STORAGE_CLOUD_ADD_ERROR", array("#error_msg#" => 'CSB42343'));
			}
		}
		else
		{
			$strError = GetMessage("CLO_STORAGE_CLOUD_ADD_ERROR", array("#error_msg#" => GetMessage("CLO_STORAGE_UNKNOWN_SERVICE")));
		}

		$APPLICATION->ResetException();
		$e = new CApplicationException($strError);
		$APPLICATION->ThrowException($e);
		return false;
	}
	/**
	 * @return bool
	*/
	function Delete()
	{
		global $DB, $APPLICATION, $CACHE_MANAGER;
		$strError = '';

		if($this->Init())
		{
			$isEmptyBucket = $this->service->IsEmptyBucket($this->arBucket);
			$forceDeleteTry = false;
			if (!$isEmptyBucket && is_object($APPLICATION->GetException()))
			{
				// The bucket was created within wrong s3 region
				if (
					$this->service->GetLastRequestStatus() == 301
					&& $this->service->GetLastRequestHeader('x-amz-bucket-region') != ''
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
						&& $this->service->GetLastRequestHeader('x-amz-bucket-region') != ''
					)
					{
						$forceDelete = true;
					}
				}

				if($isDeleted || $forceDelete)
				{
					$res = $DB->Query("DELETE FROM b_clouds_file_bucket WHERE ID = ".$this->_ID);
					if(CACHED_b_clouds_file_bucket !== false)
						$CACHE_MANAGER->CleanDir("b_clouds_file_bucket");
					if(is_object($res))
					{
						$this->arBucket = null;
						$this->_ID = 0;
						return true;
					}
					else
					{
						$strError = GetMessage("CLO_STORAGE_DB_DELETE_ERROR");
					}
				}
				else
				{
					$e = $APPLICATION->GetException();
					$strError = GetMessage("CLO_STORAGE_CLOUD_DELETE_ERROR", array("#error_msg#" => is_object($e)? $e->GetString(): ''));
				}
			}
			else
			{
				$e = $APPLICATION->GetException();
				if(is_object($e))
					$strError = GetMessage("CLO_STORAGE_CLOUD_DELETE_ERROR", array("#error_msg#" => $e->GetString()));
				else
					$strError = GetMessage("CLO_STORAGE_CLOUD_BUCKET_NOT_EMPTY");
			}
		}
		else
		{
			$strError = GetMessage("CLO_STORAGE_CLOUD_DELETE_ERROR", array("#error_msg#" => GetMessage("CLO_STORAGE_UNKNOWN_SERVICE")));
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
	function Update($arFields)
	{
		global $DB, $CACHE_MANAGER;

		if($this->_ID <= 0)
			return false;

		$this->service = CCloudStorage::GetServiceByID($this->SERVICE_ID);
		if(!is_object($this->service))
			return false;

		unset($arFields["FILE_COUNT"]);
		unset($arFields["SERVICE_ID"]);
		unset($arFields["LOCATION"]);
		unset($arFields["BUCKET"]);

		if(!$this->CheckFields($arFields, $this->_ID))
			return false;

		if(array_key_exists("FILE_RULES", $arFields))
		{
			if(is_array($arFields["FILE_RULES"]))
				$arFields["FILE_RULES"] = serialize($arFields["FILE_RULES"]);
			else
				$arFields["FILE_RULES"] = false;
		}

		if(array_key_exists("SETTINGS", $arFields))
		{
			if(!$this->CheckSettings($arFields["SETTINGS"]))
				return false;
			$arFields["SETTINGS"] = serialize($arFields["SETTINGS"]);
		}

		$strUpdate = $DB->PrepareUpdate("b_clouds_file_bucket", $arFields);
		if($strUpdate <> '')
		{
			$strSql = "
				UPDATE b_clouds_file_bucket SET
				".$strUpdate."
				WHERE ID = ".$this->_ID."
			";
			if(!is_object($DB->Query($strSql)))
				return false;
		}

		self::$arBuckets = null;
		$this->arBucket = null;
		if(CACHED_b_clouds_file_bucket !== false)
			$CACHE_MANAGER->CleanDir("b_clouds_file_bucket");

		return $this->_ID;
	}
	/**
	 * @param array[string][int]string $arPOST
	 * @return array[int][string]string
	*/
	static function ConvertPOST($arPOST)
	{
		$arRules =/*.(array[int][string]string).*/array();

		if(isset($arPOST["MODULE"]) && is_array($arPOST["MODULE"]))
		{
			foreach($arPOST["MODULE"] as $i => $MODULE)
			{
				if(!isset($arRules[intval($i)]))
					$arRules[intval($i)] = array("MODULE" => "", "EXTENSION" => "", "SIZE" => "");
				$arRules[intval($i)]["MODULE"] = $MODULE;
			}
		}

		if(isset($arPOST["EXTENSION"]) && is_array($arPOST["EXTENSION"]))
		{
			foreach($arPOST["EXTENSION"] as $i => $EXTENSION)
			{
				if(!isset($arRules[intval($i)]))
					$arRules[intval($i)] = array("MODULE" => "", "EXTENSION" => "", "SIZE" => "");
				$arRules[intval($i)]["EXTENSION"] = $EXTENSION;
			}
		}

		if(isset($arPOST["SIZE"]) && is_array($arPOST["SIZE"]))
		{
			foreach($arPOST["SIZE"] as $i => $SIZE)
			{
				if(!isset($arRules[intval($i)]))
					$arRules[intval($i)] = array("MODULE" => "", "EXTENSION" => "", "SIZE" => "");
				$arRules[intval($i)]["SIZE"] = $SIZE;
			}
		}

		return $arRules;
	}
	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	*/
	function setHeader($name, $value)
	{
		$this->service->setHeader($name, $value);
	}
}
