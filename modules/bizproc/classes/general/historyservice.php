<?
IncludeModuleLangFile(__FILE__);

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/runtimeservice.php");

class CBPAllHistoryService
	extends CBPRuntimeService
{
	protected $useGZipCompression = false;

	public function __construct()
	{
		$this->useGZipCompression = \CBPWorkflowTemplateLoader::useGZipCompression();
	}

	protected function ParseFields(&$arFields, $id = 0)
	{
		global $DB;

		$id = intval($id);
		$updateMode = ($id > 0 ? true : false);
		$addMode = !$updateMode;

		if ($addMode && !is_set($arFields, "DOCUMENT_ID"))
			throw new CBPArgumentNullException("DOCUMENT_ID");

		if (is_set($arFields, "DOCUMENT_ID") || $addMode)
		{
			$arDocumentId = CBPHelper::ParseDocumentId($arFields["DOCUMENT_ID"]);
			$arFields["MODULE_ID"] = $arDocumentId[0];
			if (strlen($arFields["MODULE_ID"]) <= 0)
				$arFields["MODULE_ID"] = false;
			$arFields["ENTITY"] = $arDocumentId[1];
			$arFields["DOCUMENT_ID"] = $arDocumentId[2];
		}

		if (is_set($arFields, "NAME") || $addMode)
		{
			$arFields["NAME"] = (string) $arFields["NAME"];
			if (strlen($arFields["NAME"]) <= 0)
				throw new CBPArgumentNullException("NAME");
		}

		if (is_set($arFields, "DOCUMENT"))
		{
			if ($arFields["DOCUMENT"] == null)
			{
				$arFields["DOCUMENT"] = false;
			}
			elseif (is_array($arFields["DOCUMENT"]))
			{
				if (count($arFields["DOCUMENT"]) > 0)
					$arFields["DOCUMENT"] = $this->GetSerializedForm($arFields["DOCUMENT"]);
				else
					$arFields["DOCUMENT"] = false;
			}
			else
			{
				throw new CBPArgumentTypeException("DOCUMENT");
			}
		}

		unset($arFields["MODIFIED"]);
	}

	private function GetSerializedForm($arTemplate)
	{
		$buffer = serialize($arTemplate);
		if ($this->useGZipCompression)
			$buffer = gzcompress($buffer, 9);
		return $buffer;
	}

	public static function Add($arFields)
	{
		$h = new CBPHistoryService();
		return $h->AddHistory($arFields);
	}

	public static function Update($id, $arFields)
	{
		$h = new CBPHistoryService();
		return $h->UpdateHistory($id, $arFields);
	}

	private static function GenerateFilePath($documentId)
	{
		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$dest = "/bizproc/";
		if (strlen($arDocumentId[0]) > 0)
			$dest .= preg_replace("/[^a-zA-Z0-9._]/i", "_", $arDocumentId[0]);
		else
			$dest .= "NA";
		$documentIdMD5 = md5($arDocumentId[2]);
		$dest .= "/".preg_replace("/[^a-zA-Z0-9_]/i", "_", $arDocumentId[1])."/".substr($documentIdMD5, 0, 3)."/".$documentIdMD5;

		return $dest;
	}

	public function DeleteHistory($id, $documentId = null)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			throw new Exception("id");

		$arFilter = array("ID" => $id);
		if ($documentId != null)
			$arFilter["DOCUMENT_ID"] = $documentId;

		$db = $this->GetHistoryList(
			array(),
			$arFilter,
			false,
			false,
			array("ID", "MODULE_ID", "ENTITY", "DOCUMENT_ID")
		);
		if ($ar = $db->Fetch())
		{
			$deleteFile = true;
			foreach(GetModuleEvents("bizproc", "OnBeforeDeleteFileFromHistory", true) as $event)
			{
				if(ExecuteModuleEventEx($event, array($id, $documentId)) !== true)
				{
					$deleteFile = false;
					break;
				}
			}

			if ($deleteFile)
			{
				$dest = self::GenerateFilePath($ar["DOCUMENT_ID"]);
				DeleteDirFilesEx("/".(COption::GetOptionString("main", "upload_dir", "upload")).$dest."/".$ar["ID"]);
				if(CModule::IncludeModule('clouds'))
					CCloudStorage::DeleteDirFilesEx($dest."/".$ar["ID"]);
			}

			$DB->Query("DELETE FROM b_bp_history WHERE ID = ".intval($id)." ", true);
		}
	}

	public static function Delete($id, $documentId = null)
	{
		$h = new CBPHistoryService();
		$h->DeleteHistory($id, $documentId);
	}

	public function DeleteHistoryByDocument($documentId)
	{
		global $DB;

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$dest = self::GenerateFilePath($documentId);
		DeleteDirFilesEx("/".(COption::GetOptionString("main", "upload_dir", "upload")).$dest);
		if(CModule::IncludeModule('clouds'))
			CCloudStorage::DeleteDirFilesEx($dest);

		$DB->Query(
			"DELETE FROM b_bp_history ".
			"WHERE DOCUMENT_ID = '".$DB->ForSql($arDocumentId[2])."' ".
			"	AND ENTITY = '".$DB->ForSql($arDocumentId[1])."' ".
			"	AND MODULE_ID ".((strlen($arDocumentId[0]) > 0) ? "= '".$DB->ForSql($arDocumentId[0])."'" : "IS NULL")." ",
			true
		);
	}

	public static function DeleteByDocument($documentId)
	{
		$h = new CBPHistoryService();
		$h->DeleteHistoryByDocument($documentId);
	}

	public static function GetById($id)
	{
		$id = intval($id);
		if ($id <= 0)
			throw new CBPArgumentNullException("id");

		$h = new CBPHistoryService();
		$db = $h->GetHistoryList(array(), array("ID" => $id));
		return $db->GetNext();
	}

	public static function GetList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		$h = new CBPHistoryService();
		return $h->GetHistoryList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
	}

	public static function RecoverDocumentFromHistory($id)
	{
		$arHistory = self::GetById($id);
		if (!$arHistory)
			throw new Exception(str_replace("#ID#", intval($id), GetMessage("BPCGHIST_INVALID_ID")));

		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($arHistory["DOCUMENT_ID"]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "RecoverDocumentFromHistory"), array($documentId, $arHistory["DOCUMENT"]));

		return false;
	}

	public static function PrepareFileForHistory($documentId, $arFileId, $historyIndex)
	{
		$dest = self::GenerateFilePath($documentId);

		$fileParameterIsArray = true;
		if (!is_array($arFileId))
		{
			$arFileId = array($arFileId);
			$fileParameterIsArray = false;
		}

		$result = array();

		foreach ($arFileId as $fileId)
		{
			if($ar = CFile::GetFileArray($fileId))
			{
				$newFilePath = CFile::CopyFile($fileId, false, $dest."/".$historyIndex."/".$ar["FILE_NAME"]);
				if ($newFilePath)
					$result[] = $newFilePath;
			}
		}

		if (!$fileParameterIsArray)
		{
			if (count($result) > 0)
				$result = $result[0];
			else
				$result = "";
		}

		return $result;
	}

	public static function MergeHistory($firstDocumentId, $secondDocumentId)
	{
		global $DB;

		$arFirstDocumentId = CBPHelper::ParseDocumentId($firstDocumentId);
		$arSecondDocumentId = CBPHelper::ParseDocumentId($secondDocumentId);

		$DB->Query(
			"UPDATE b_bp_history SET ".
			"	DOCUMENT_ID = '".$DB->ForSql($arFirstDocumentId[2])."', ".
			"	ENTITY = '".$DB->ForSql($arFirstDocumentId[1])."', ".
			"	MODULE_ID = '".$DB->ForSql($arFirstDocumentId[0])."' ".
			"WHERE DOCUMENT_ID = '".$DB->ForSql($arSecondDocumentId[2])."' ".
			"	AND ENTITY = '".$DB->ForSql($arSecondDocumentId[1])."' ".
			"	AND MODULE_ID = '".$DB->ForSql($arSecondDocumentId[0])."' "
		);
	}

	public static function MigrateDocumentType($oldType, $newType, $workflowTemplateIds)
	{
		global $DB;

		$arOldType = CBPHelper::ParseDocumentId($oldType);
		$arNewType = CBPHelper::ParseDocumentId($newType);

		$DB->Query(
			"UPDATE b_bp_history SET ".
			"	ENTITY = '".$DB->ForSql($arNewType[1])."', ".
			"	MODULE_ID = '".$DB->ForSql($arNewType[0])."' ".
			"WHERE ENTITY = '".$DB->ForSql($arOldType[1])."' ".
			"	AND MODULE_ID = '".$DB->ForSql($arOldType[0])."' ".
			"	AND DOCUMENT_ID IN (SELECT t.DOCUMENT_ID FROM b_bp_workflow_state t WHERE t.WORKFLOW_TEMPLATE_ID in (".implode(",", $workflowTemplateIds).") and t.MODULE_ID='".$DB->ForSql($arOldType[0])."' and t.ENTITY='".$DB->ForSql($arOldType[1])."') "
		);
	}

	public function AddHistory($arFields)
	{
		global $DB;

		self::ParseFields($arFields, 0);

		$arInsert = $DB->PrepareInsert("b_bp_history", $arFields);

		$strSql =
			"INSERT INTO b_bp_history (".$arInsert[0].", MODIFIED) ".
			"VALUES(".$arInsert[1].", ".$DB->CurrentTimeFunction().")";
		$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		$arEventParams = array(
			"ID" => $ID,
			"DOCUMENT_ID" => array($arFields['MODULE_ID'], $arFields['ENTITY'], $arFields['DOCUMENT_ID']),
		);
		foreach (GetModuleEvents('bizproc', 'OnAddToHistory', true) as $arEvent)
			$result = ExecuteModuleEventEx($arEvent, array($arEventParams));

		return $ID;
	}

	public function UpdateHistory($id, $arFields)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			throw new CBPArgumentNullException("id");

		self::ParseFields($arFields, $id);

		$strUpdate = $DB->PrepareUpdate("b_bp_history", $arFields);

		$strSql =
			"UPDATE b_bp_history SET ".
			"	".$strUpdate.", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = ".intval($id)." ";
		$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $id;
	}

	public function GetHistoryList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "MODULE_ID", "ENTITY", "DOCUMENT_ID", "NAME", "DOCUMENT", "MODIFIED", "USER_ID");

		if (count(array_intersect($arSelectFields, array("MODULE_ID", "ENTITY", "DOCUMENT_ID"))) > 0)
		{
			if (!in_array("MODULE_ID", $arSelectFields))
				$arSelectFields[] = "MODULE_ID";
			if (!in_array("ENTITY", $arSelectFields))
				$arSelectFields[] = "ENTITY";
			if (!in_array("DOCUMENT_ID", $arSelectFields))
				$arSelectFields[] = "DOCUMENT_ID";
		}

		if (array_key_exists("DOCUMENT_ID", $arFilter))
		{
			$d = CBPHelper::ParseDocumentId($arFilter["DOCUMENT_ID"]);
			$arFilter["MODULE_ID"] = $d[0];
			$arFilter["ENTITY"] = $d[1];
			$arFilter["DOCUMENT_ID"] = $d[2];
		}

		static $arFields = array(
			"ID" => Array("FIELD" => "H.ID", "TYPE" => "int"),
			"MODULE_ID" => Array("FIELD" => "H.MODULE_ID", "TYPE" => "string"),
			"ENTITY" => Array("FIELD" => "H.ENTITY", "TYPE" => "string"),
			"DOCUMENT_ID" => Array("FIELD" => "H.DOCUMENT_ID", "TYPE" => "string"),
			"NAME" => Array("FIELD" => "H.NAME", "TYPE" => "string"),
			"DOCUMENT" => Array("FIELD" => "H.DOCUMENT", "TYPE" => "string"),
			"MODIFIED" => Array("FIELD" => "H.MODIFIED", "TYPE" => "datetime"),
			"USER_ID" => Array("FIELD" => "H.USER_ID", "TYPE" => "int"),

			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (H.USER_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (H.USER_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (H.USER_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (H.USER_ID = U.ID)"),
		);

		$arSqls = CBPHelper::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_bp_history H ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_bp_history H ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_bp_history H ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (strlen($arSqls["GROUPBY"]) <= 0)
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// only for MySQL
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$dbRes = new CBPHistoryResult($dbRes, $this->useGZipCompression);
		return $dbRes;
	}
}

class CBPHistoryResult extends CDBResult
{
	private $useGZipCompression = false;

	public function __construct($res, $useGZipCompression)
	{
		$this->useGZipCompression = $useGZipCompression;
		parent::CDBResult($res);
	}

	private function GetFromSerializedForm($value)
	{
		if (strlen($value) > 0)
		{
			if ($this->useGZipCompression)
				$value = gzuncompress($value);

			$value = unserialize($value);
			if (!is_array($value))
				$value = array();
		}
		else
		{
			$value = array();
		}
		return $value;
	}

	function Fetch()
	{
		$res = parent::Fetch();

		if ($res)
		{
			if (array_key_exists("DOCUMENT_ID", $res))
				$res["DOCUMENT_ID"] = array($res["MODULE_ID"], $res["ENTITY"], $res["DOCUMENT_ID"]);
			if (array_key_exists("DOCUMENT", $res))
				$res["DOCUMENT"] = $this->GetFromSerializedForm($res["DOCUMENT"]);
		}

		return $res;
	}
}

//Compatibility
class CBPHistoryService extends CBPAllHistoryService {}