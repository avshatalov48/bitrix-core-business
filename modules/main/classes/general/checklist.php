<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Authentication\Policy;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

class CCheckList
{
	function __construct($ID = false)
	{
		$this->current_result = false;
		$this->started = false;
		$this->report_id = false;
		$this->report_info = "";
		$this->checklist_path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/checklist_structure.php";
		if (file_exists($this->checklist_path))
			$arCheckList = include($this->checklist_path);
		else
			return;
		//bind custom checklist
		foreach(GetModuleEvents('main', 'OnCheckListGet', true) as $arEvent)
		{
			$arCustomChecklist = ExecuteModuleEventEx($arEvent, array($arCheckList));

			if (is_array($arCustomChecklist["CATEGORIES"]))
			{
				foreach($arCustomChecklist["CATEGORIES"] as $section_id => $arSectionFields)
				{
					if (!$arCheckList["CATEGORIES"][$section_id])
					{
						$arCheckList["CATEGORIES"][$section_id] = $arSectionFields;
					}
				}
			}
			if (is_array($arCustomChecklist["POINTS"]))
			{
				foreach($arCustomChecklist["POINTS"] as $point_id => $arPointFields)
				{
					$parent = $arCustomChecklist["POINTS"][$point_id]["PARENT"];
					if (!$arCheckList["POINTS"][$point_id] && array_key_exists($parent, $arCheckList["CATEGORIES"]))
					{
						$arCheckList["POINTS"][$point_id] = $arPointFields;
					}
				}
			}
		}
		//end bind custom checklist
		$this->checklist = $arCheckList;
		$arFilter["REPORT"] = "N";
		if (intval($ID)>0)
		{
			$arFilter["ID"] = $ID;
			$arFilter["REPORT"] = "Y";
		}

		$dbResult = CCheckListResult::GetList(array(), $arFilter);
		if ($arCurrentResult = $dbResult->Fetch())
		{
			$this->current_result = unserialize($arCurrentResult["STATE"], ['allowed_classes' => false]);
			if (intval($ID)>0)
			{
				$this->report_id = intval($ID);
				unset($arCurrentResult["STATE"]);
				$this->report_info = $arCurrentResult;
			}

			foreach($arCheckList["POINTS"] as $key => $arFields)
			{
				if (empty($this->current_result[$key]))
				{
					if ($this->report_id)
						unset($this->checklist["POINTS"][$key]);
					else
						$this->current_result[$key] = array(
							"STATUS" => "W");
				}
				////$this->current_result[$key] = array("STATUS" => "A");
			}
		}
		if ($this->current_result != false && $this->report_id == false)
			$this->started = true;
	}

	function GetSections()
	{
		$arSections = $this->checklist["CATEGORIES"];
		$arResult = array();
		foreach($arSections as $key => $arFields)
		{
			$arResult[$key] = array_merge($this->GetDescription($key), $arFields);
			$arResult[$key]["STATS"] = $this->GetSectionStat($key);
		}
		return $arResult;
	}

	//getting sections statistic
	function GetSectionStat($ID = false)
	{
		$arResult = array(
			"CHECK" => 0,
			"CHECK_R" => 0,
			"FAILED" => 0,
			"WAITING" => 0,
			"TOTAL" => 0,
			"REQUIRE_CHECK" => 0,
			"REQUIRE_SKIP" => 0,
			"NOT_REQUIRE_CHECK"=>0,
			"NOT_REQUIRE_SKIP"=>0,
			"CHECKED" => "N",
			"REQUIRE" => 0,
		);

		if (($ID!=false && array_key_exists($ID, $this->checklist["CATEGORIES"])) || $ID == false)
		{
			$arPoints = $this->GetPoints($ID);
			$arSections = $this->checklist["CATEGORIES"];
			if (!empty($arPoints))
				foreach ($arPoints as $arPointFields)
				{
					if ($arPointFields["STATE"]["STATUS"] == "A")
					{
						$arResult["CHECK"]++;
						if (isset($arPointFields['REQUIRE']) && $arPointFields['REQUIRE']=='Y')
							$arResult["CHECK_R"]++;
					}
					if ($arPointFields["STATE"]["STATUS"] == "F")
						$arResult["FAILED"]++;
					if ($arPointFields["STATE"]["STATUS"] == "W")
						$arResult["WAITING"]++;
					if (isset($arPointFields["REQUIRE"]) && $arPointFields["REQUIRE"] == "Y")
					{
						$arResult["REQUIRE"]++;
						if ($arPointFields["STATE"]["STATUS"] == "A")
							$arResult["REQUIRE_CHECK"]++;
						elseif($arPointFields["STATE"]["STATUS"] == "S")
							$arResult["REQUIRE_SKIP"]++;
					}
					else
					{
						if ($arPointFields["STATE"]["STATUS"] == "A")
							$arResult["NOT_REQUIRE_CHECK"]++;
						elseif($arPointFields["STATE"]["STATUS"] == "S")
							$arResult["NOT_REQUIRE_SKIP"]++;
					}
				}
			$arResult["TOTAL"] = count($arPoints);

			if ($ID)
			{
				foreach($arSections as $key => $arFields)
				{
					if (isset($arFields["PARENT"]) && $arFields["PARENT"] == $ID)
					{
						$arSubSectionStat = $this->GetSectionStat($key);
						$arResult["TOTAL"]+=$arSubSectionStat["TOTAL"];
						$arResult["CHECK"]+=$arSubSectionStat["CHECK"];
						$arResult["FAILED"]+=$arSubSectionStat["FAILED"];
						$arResult["WAITING"]+=$arSubSectionStat["WAITING"];
						$arResult["REQUIRE"]+=$arSubSectionStat["REQUIRE"];
						$arResult["REQUIRE_CHECK"]+=$arSubSectionStat["REQUIRE_CHECK"];
						$arResult["REQUIRE_SKIP"]+=$arSubSectionStat["REQUIRE_SKIP"];
					}
				}
			}
			if (
				($arResult["REQUIRE"] > 0 && $arResult["FAILED"] == 0 && $arResult["REQUIRE"] == $arResult["REQUIRE_CHECK"])
				|| ($arResult["REQUIRE"] == 0 && $arResult["FAILED"] == 0 && $arResult["TOTAL"] > 0)
				|| ($arResult["CHECK"] == $arResult["TOTAL"] && $arResult["TOTAL"] > 0)
			)
			{
					$arResult["CHECKED"] = "Y";
			}
		}

		return $arResult;
	}

	function GetPoints($arSectionCode = false)
	{
		$arCheckList = $this->GetCurrentState();
		$arResult = array();
		if (is_array($arCheckList) && !empty($arCheckList))
		{
			foreach ($arCheckList["POINTS"] as $key => $arFields)
			{
				$arFields = array_merge($this->GetDescription($key), $arFields);

				if ($arFields["PARENT"] == $arSectionCode || $arSectionCode  == false)
					$arResult[$key] = $arFields;
				if (isset($arResult[$key]["STATE"]['COMMENTS']) && is_array($arResult[$key]["STATE"]['COMMENTS']))
					$arResult[$key]["STATE"]['COMMENTS_COUNT'] = count($arResult[$key]["STATE"]['COMMENTS']);
			}
		}

		return $arResult;
	}

	function GetStructure()
	{ //build checklist stucture with section statistic & status info
		$arSections = $this->GetSections();
		foreach ($arSections as $key => $arSectionFields)
		{
			if (empty($arSectionFields["CATEGORIES"]))
			{
				$arSections[$key]["CATEGORIES"] = array();
				$arSectionFields["CATEGORIES"] = array();
			}
			if (empty($arSectionFields["PARENT"]))
			{
				$arSections[$key]["POINTS"] = $this->GetPoints($key);
				$arSections[$key] = array_merge($arSections[$key], $this->GetSectionStat($key));
				continue;
			}

			$arFields = $arSectionFields;
			$arFields["POINTS"] = $this->GetPoints($key);
			$arFields = array_merge($arFields, $this->GetSectionStat($key));
			$arSections[$arFields["PARENT"]]["CATEGORIES"][$key] = $arFields;
			unset($arSections[$key]);
		}

		$arResult["STRUCTURE"] = $arSections;
		$arResult["STAT"] = $this->GetSectionStat();
		return $arResult;
	}



	function PointUpdate($arTestID, $arPointFields = array())
	{//update test info in the object property
		if (!$arTestID || empty($arPointFields) || $this->report_id)
			return false;

		$currentFields =
			is_array($this->current_result) && isset($this->current_result[$arTestID])
				? $this->current_result[$arTestID]
				: []
		;

		if (!$arPointFields["STATUS"])
			$arPointFields["STATUS"] = $currentFields["STATUS"];

		$this->current_result[$arTestID] = $arPointFields;

		return true;
	}

	function GetDescription($ID)
	{
		//getting description of sections and points
		$file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANG."/admin/checklist/".$ID.".html";
		$howTo = "";
		if (file_exists($file))
		{
			$howTo = file_get_contents($file);
		}

		$convertEncoding = \Bitrix\Main\Localization\Translation::needConvertEncoding(LANG);
		if ($convertEncoding)
		{
			$targetEncoding = \Bitrix\Main\Localization\Translation::getCurrentEncoding();
			$sourceEncoding = \Bitrix\Main\Localization\Translation::getSourceEncoding(LANG);
			if ($targetEncoding !== 'utf-8' || !preg_match('//u', $howTo))
			{
				$howTo = \Bitrix\Main\Text\Encoding::convertEncoding($howTo, $sourceEncoding, $targetEncoding);
			}
		}

		$arDesc = array(
			"NAME" => GetMessage("CL_".$ID),
			"DESC" => GetMessage("CL_".$ID."_DESC", array('#LANG#' => LANG)),
			"AUTOTEST_DESC" => GetMessage("CL_".$ID."_AUTOTEST_DESC"),
			"HOWTO" => ($howTo <> '')?(str_ireplace('#LANG#', LANG, $howTo)):"",
			"LINKS" => GetMessage("CL_".$ID."_LINKS")
		);

		return $arDesc;
	}


	function Save()
	{//saving current state
		if (!$this->report_id)
		{
			$res = CCheckListResult::Save(array("STATE" => $this->current_result));
			if(!is_array($res))
				CUserOptions::SetOption("checklist", "autotest_start", "Y", true, false);
			return $res;
		}
		return false;
	}

	function GetCurrentState()
	{//getting current state
		$arCheckList = $this->checklist;
		$currentState = $this->current_result;
		foreach ($arCheckList["POINTS"] as $testID => $arTestFields)
		{
			if (!empty($currentState[$testID]))
				$arCheckList["POINTS"][$testID]["STATE"] = $currentState[$testID];
			else
				$arCheckList["POINTS"][$testID]["STATE"] = array(
					"STATUS" => "W"
				);
		}

		return $arCheckList;
	}

	function AutoCheck($arTestID, $arParams = array())
	{//execute point autotest
		$arParams["TEST_ID"] = $arTestID;
		$arPoints = $this->GetPoints();
		$arPoint = $arPoints[$arTestID];
		$result = false;
		if (!$arPoint || $arPoint["AUTO"] !="Y")
			return false;
		if (isset($arPoints[$arTestID]["PARAMS"]) && is_array($arPoints[$arTestID]["PARAMS"]))
			$arParams = array_merge($arParams, $arPoints[$arTestID]["PARAMS"]);
		$arClass = 	$arPoint["CLASS_NAME"];
		$arMethod = $arPoint["METHOD_NAME"];

		if (!empty($arPoint["FILE_PATH"]) && file_exists($_SERVER["DOCUMENT_ROOT"].$arPoint["FILE_PATH"]))
			include($_SERVER["DOCUMENT_ROOT"].$arPoint["FILE_PATH"]);

		if(is_callable(array($arClass, $arMethod)))
			$result = call_user_func_array(array($arClass, $arMethod), array($arParams));

		$arResult = array();
		if ($result && is_array($result))
		{
			if (array_key_exists("STATUS", $result))
			{
				$arFields["STATUS"] = "F";
				if ($result['STATUS'] == "true")
					$arFields["STATUS"] = "A";

				$arFields["COMMENTS"] = $arPoint["STATE"]["COMMENTS"] ?? [];
				$arFields["COMMENTS"]["SYSTEM"] = array();
				if (isset($result["MESSAGE"]["PREVIEW"]))
					$arFields["COMMENTS"]["SYSTEM"]["PREVIEW"]= $result["MESSAGE"]["PREVIEW"];
				if (isset($result["MESSAGE"]["DETAIL"]))
					$arFields["COMMENTS"]["SYSTEM"]["DETAIL"]= $result["MESSAGE"]["DETAIL"];

				if ($this->PointUpdate($arTestID, $arFields))
					if ($this->Save())
					{
						$arResult = array(
							"STATUS" => $arFields["STATUS"],
							"COMMENTS_COUNT" => count($arFields["COMMENTS"] ?? []),
							"ERROR" => $result["ERROR"] ?? null,
							"SYSTEM_MESSAGE" => $arFields["COMMENTS"]["SYSTEM"] ?? '',
						);

					}
			}
			elseif($result["IN_PROGRESS"] == "Y")
			{
				$arResult= array(
						"IN_PROGRESS" => "Y",
						"PERCENT" => $result["PERCENT"]
					);
			}
		}
		else
			$arResult = array("STATUS" => "W");

		return $arResult;
	}

	function AddReport($arReportFields = array(), $errorCheck = false)
	{//saving current state to a report
		if ($this->report_id)
			return false;

		if ($errorCheck && !$arReportFields["TESTER"] && !$arReportFields["COMPANY_NAME"])
			return array("ERROR" => GetMessage("EMPTY_NAME"));

		$arStats = $this->GetSectionStat();
		$arFields = array(
				"TESTER" => $arReportFields["TESTER"],
				"COMPANY_NAME" => $arReportFields["COMPANY_NAME"],
				"PHONE" => $arReportFields["PHONE"],
				"EMAIL" => $arReportFields["EMAIL"],
				"PICTURE" => $arReportFields["PICTURE"],
				"REPORT_COMMENT" => $arReportFields["COMMENT"],
				"STATE" => $this->current_result,
				"TOTAL" => $arStats["TOTAL"],
				"SUCCESS" => $arStats["CHECK"],
				"SUCCESS_R" => $arStats["CHECK_R"],
				"FAILED" => $arStats["FAILED"],
				"PENDING" => $arStats["WAITING"],
				"REPORT" => true
			);

			$arReportID = CCheckListResult::Save($arFields);
			if ($arReportID>0)
			{
				$dbres = CCheckListResult::GetList(array(), array("REPORT" => "N"));
				if ($res = $dbres->Fetch())
				{
					CCheckListResult::Delete($res["ID"]);
					CUserOptions::SetOption("checklist", "autotest_start", "N", true, false);
				}
				return $arReportID;
			}

			return false;
	}

	function GetReportInfo()
	{//getting report information
		if ($this->report_id)
		{
			$checklist = new CCheckList($this->report_id);
			if ($checklist->current_result == false)
				return false;
			$arResult = $checklist->GetStructure();

			//removing empty sections
			/*foreach($arResult["STRUCTURE"] as $key => $rFields)
			{
				$arsCategories = array();
				foreach ($rFields["CATEGORIES"] as $skey => $sFields)
				{
					if (count($sFields["POINTS"])>0)
						$arsCategories[$skey] = $sFields;
				}
				if (count($arsCategories)>0)
				{
					$rFields["CATEGORIES"] = $arsCategories;
					$arTmpStructure[$key] = $rFields;
				}
			}
			$arResult["STRUCTURE"] = $arTmpStructure;*/
			$arResult["POINTS"] = $checklist->GetPoints();
			$arResult["INFO"] = $checklist->report_info;

			return $arResult;
		}
		return false;
	}
}

class CCheckListResult
{
	public static function Save($arFields = array())
	{
		global $DB;

		$arResult = array();
		if ($arFields["STATE"] && is_array($arFields["STATE"]))
			$arFields["STATE"] = serialize($arFields["STATE"]);
		else
			$arResult["ERRORS"][] = GetMessage("ERROR_DATA_RECEIVED");

		$currentState = false;
		if (!isset($arFields["REPORT"]) || $arFields["REPORT"] != true)
		{
			$arFields["REPORT"] = "N";
			$db_result = $DB->Query("SELECT ID FROM b_checklist WHERE REPORT <> 'Y'");
			$currentState = $db_result->Fetch();
		}
		else
			$arFields["REPORT"] = "Y";

		if (!empty($arResult["ERRORS"]))
			return $arResult;

		if ($currentState)
		{
			$strUpdate = $DB->PrepareUpdate("b_checklist", $arFields);
			$strSql = "UPDATE b_checklist SET ".$strUpdate." WHERE ID=".$currentState["ID"];
		}
		else
		{
			$arInsert = $DB->PrepareInsert("b_checklist", $arFields);
			$strSql ="INSERT INTO b_checklist(".$arInsert[0].", DATE_CREATE) ".
					"VALUES(".$arInsert[1].", '".ConvertTimeStamp(time(), "FULL")."')";
		}

		$arBinds = array(
				"STATE" => $arFields["STATE"],
			);
		$arResult = $DB->QueryBind($strSql, $arBinds);

		return $arResult;

	}

	public static function GetList($arOrder = array(), $arFilter = array())
	{
		global $DB;

		$arSqlWhereStr = '';
		if (is_array($arFilter) && !empty($arFilter))
		{
			$arSqlWhere = array();
			$arSqlFields = array("ID", "REPORT", "HIDDEN", "SENDED_TO_BITRIX");
			foreach($arFilter as $key => $value)
			{
				if (in_array($key, $arSqlFields))
				{
					$arSqlWhere[] = $key."='".$DB->ForSql($value)."'";
				}
			}
			$arSqlWhereStr = GetFilterSqlSearch($arSqlWhere);
		}

		$strSql = "SELECT * FROM b_checklist";
		if ($arSqlWhereStr <> '')
		{
			$strSql.= " WHERE ".$arSqlWhereStr;
		}
		$strSql.= " ORDER BY ID desc";
		$arResult = $DB->Query($strSql);

		return $arResult;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		$strUpdate = $DB->PrepareUpdate("b_checklist", $arFields);

		$strSql =
			"UPDATE b_checklist SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);
		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		if (!$ID>0)
			return false;
		$strSql = "DELETE FROM b_checklist where ID=".$ID;
		if ($arResult = $DB->Query($strSql))
			return true;
		return false;
	}

}

class CAutoCheck
{
	public static function CheckCustomComponents($arParams)
	{
		$arResult["STATUS"] = false;
		$arComponentFolders = array(
			"/bitrix/components",
			"/local/components"
		);
		$components = array();
		foreach($arComponentFolders as $componentFolder)
		{
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$componentFolder) && ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$componentFolder)))
			{
				while (($file = readdir($handle)) !== false)
				{
					if ($file == "bitrix" || $file ==".." || $file == ".")
						continue;

					$dir = $componentFolder."/".$file;
					if (is_dir($_SERVER['DOCUMENT_ROOT'].$dir))
					{
						if(CComponentUtil::isComponent($dir))
						{
							$components[] = array(
								"path" => $dir,
								"name" => $file,
							);
						}
						elseif($comp_handle = opendir($_SERVER['DOCUMENT_ROOT'].$dir))
						{
							while (($subdir = readdir($comp_handle)) !== false)
							{
								if ($subdir == ".." || $subdir == "." || $subdir == ".svn")
									continue;

								if(CComponentUtil::isComponent($dir."/".$subdir))
								{
									$components[] = array(
										"path" => $dir."/".$subdir,
										"name" => $file.":".$subdir,
									);
								}
							}
							closedir($comp_handle);
						}
					}
				}
				closedir($handle);
			}
		}
		if (isset($arParams["ACTION"]) && $arParams["ACTION"] == "FIND")
		{
			foreach($components as $component)
			{
				$arResult["MESSAGE"]["DETAIL"] .= $component["name"]." \n";
			}

			if ($arResult["MESSAGE"]["DETAIL"] == '')
			{
				$arResult["MESSAGE"]["PREVIEW"] = GetMessage("CL_HAVE_NO_CUSTOM_COMPONENTS");
			}
			else
			{
				$arResult = array(
					"STATUS" => true,
					"MESSAGE" => array(
						"PREVIEW" => GetMessage("CL_HAVE_CUSTOM_COMPONENTS")." (".count($components).")",
						"DETAIL" => $arResult["MESSAGE"]["DETAIL"]
					)
				);
			}
		}
		else
		{
			foreach($components as $component)
			{
				$desc = $_SERVER['DOCUMENT_ROOT'].$component["path"]."/.description.php";
				if (!file_exists($desc) || filesize($desc) === 0)
					$arResult["MESSAGE"]["DETAIL"] .= GetMessage("CL_EMPTY_DESCRIPTION")." ".$component["name"]." \n";
			}

			if (!isset($arResult["MESSAGE"]["DETAIL"]) || $arResult["MESSAGE"]["DETAIL"] == '')
			{
				$arResult["STATUS"] = true;
				$arResult["MESSAGE"]["PREVIEW"] = GetMessage("CL_HAVE_CUSTOM_COMPONENTS_DESC");
			}
			else
			{
				$arResult = array(
					"STATUS" => false,
					"MESSAGE" => array(
						"PREVIEW" => GetMessage("CL_ERROR_FOUND_SHORT"),
						"DETAIL" => $arResult["MESSAGE"]["DETAIL"]
					)
				);
			}
		}
		return $arResult;
	}

	public static function CheckBackup()
	{
		$arCount = 0;
		$arResult = array();
		$arResult["STATUS"] = false;
		$bBitrixCloud = function_exists('openssl_encrypt') && CModule::IncludeModule('bitrixcloud') && CModule::IncludeModule('clouds');

		$site = CSite::GetSiteByFullPath($_SERVER['DOCUMENT_ROOT']);
		$path = BX_ROOT."/backup";
		$arTmpFiles = array();
		$arFilter = array();
		GetDirList(array($site, $path), $arDir, $arTmpFiles, $arFilter, array('sort' => 'asc'), "F");

		foreach($arTmpFiles as $ar)
		{
			if (mb_strpos($ar['NAME'], ".enc.gz") || mb_strpos($ar['NAME'], ".tar.gz") || mb_strpos($ar['NAME'], ".tar") || mb_strpos($ar['NAME'], ".enc"))
				$arCount++;
		}

		if ($bBitrixCloud)
		{
			$backup = CBitrixCloudBackup::getInstance();
			try
			{
				foreach($backup->listFiles() as $ar)
				{
					if (mb_strpos($ar['FILE_NAME'], ".enc.gz") || mb_strpos($ar['FILE_NAME'], ".tar.gz") || mb_strpos($ar['FILE_NAME'], ".tar") || mb_strpos($ar['FILE_NAME'], ".enc"))
						$arCount++;
				}
			}
			catch (Exception $e)
			{
			}
		}
		if ($arCount>0)
		{
			$arResult["STATUS"] = true;
			$arResult["MESSAGE"]["PREVIEW"] = GetMessage("CL_FOUND_BACKUP", array("#count#" => $arCount));
		}
		else
		{
			$arResult["MESSAGE"]["PREVIEW"] = GetMessage("CL_NOT_FOUND_BACKUP");
		}
		return $arResult;
	}

	public static function CheckTemplates()
	{
		$arFolders = array(
			$_SERVER['DOCUMENT_ROOT']."/bitrix/templates",
			$_SERVER['DOCUMENT_ROOT']."/local/templates",
		);
		$arResult["STATUS"] = false;
		$arCount = 0;
		$arRequireFiles = array("header.php", "footer.php");
		$arFilter = array(".svn", ".", "..");
		$arMessage = '';
		foreach($arFolders as $folder)
		{
			if (file_exists($folder) && ($arTemplates = scandir($folder)))
			{
				foreach ($arTemplates as $dir)
				{
					$arTemplateFolder = $folder."/".$dir;
					if (in_array($dir, $arFilter) || !is_dir($arTemplateFolder))
						continue;
					$arRequireFilesTmp = $arRequireFiles;

					foreach($arRequireFilesTmp as $k => $file)
					{
						if (!file_exists($arTemplateFolder."/".$file))
						{
							$arMessage .= GetMessage("NOT_FOUND_FILE", array("#template#" => $dir, "#file_name#" => $file))."\n";
							unset($arRequireFilesTmp[$k]);
						}
					}

					if (in_array("header.php", $arRequireFilesTmp))
					{
						if (file_exists($arTemplateFolder . '/header.php'))
						{
							$header = file_get_contents($arTemplateFolder . '/header.php');

							if ($header != '')
							{
								preg_match('/\$APPLICATION->ShowHead\(/im', $header, $arShowHead);
								preg_match('/\$APPLICATION->ShowTitle\(/im', $header, $arShowTitle);
								preg_match('/\$APPLICATION->ShowPanel\(/im', $header, $arShowPanel);
								if (!in_array($dir, array('mail_join')) && empty($arShowHead))
								{
									preg_match_all('/\$APPLICATION->(ShowCSS|ShowHeadScripts|ShowHeadStrings)\(/im', $header, $arShowHead);
									if (!$arShowHead[0] || count($arShowHead[0]) != 3)
									{
										$arMessage .= GetMessage("NO_SHOWHEAD", array("#template#" => $dir))."\n";
									}
								}
								if (!in_array($dir, array('empty', 'mail_join')) && empty($arShowTitle))
								{
									$arMessage .= GetMessage("NO_SHOWTITLE", array("#template#" => $dir))."\n";
								}
								if (!in_array($dir, array('mobile_app', 'desktop_app', 'empty', 'learning_10_0_0', 'call_app', 'mail_join')) && empty($arShowPanel))
								{
									$arMessage .= GetMessage("NO_SHOWPANEL", array("#template#" => $dir))."\n";
								}
							}
						}
					}

					$arCount++;
				}
			}
		}

		if ($arCount == 0)
		{
			$arResult["MESSAGE"]["PREVIEW"] = GetMessage("NOT_FOUND_TEMPLATE");
		}
		elseif ($arMessage == '')
		{
			$arResult["STATUS"] = true;
		}

		$arResult["MESSAGE"] = array (
			"PREVIEW" => GetMessage("TEMPLATE_CHECK_COUNT", array("#count#" => $arCount)),
			"DETAIL" => $arMessage
		);

		return $arResult;
	}

	public static function CheckKernel($arParams)
	{
		global $DB;

		$startTime = time();
		$installFilesMapping = array(
			"install/components/bitrix/" => "/bitrix/components/bitrix/",
			"install/js/" => "/bitrix/js/",
			"install/activities/" => "/bitrix/activities/",
			"install/admin/" => "/bitrix/admin/",
			"install/wizards/" => "/bitrix/wizards/",
		);

		$events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("main", "onKernelCheckInstallFilesMappingGet");
		foreach ($events as $event)
		{
			$pathList = ExecuteModuleEventEx($event);
			if(is_array($pathList))
			{
				foreach ($pathList as $pathFrom=>$pathTo)
				{
					if(!array_key_exists($pathFrom, $installFilesMapping))
					{
						$installFilesMapping[$pathFrom] = $pathTo;
					}
				}
			}
		}

		if(empty(\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"][$arParams["TEST_ID"]]))
			\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"][$arParams["TEST_ID"]] = array();
		$NS = &\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"][$arParams["TEST_ID"]];
		if ($arParams["STEP"] == false)
		{
			$NS = array();
			$rsInstalledModules = CModule::GetList();
			while ($ar = $rsInstalledModules->Fetch())
			{
				if (!mb_strpos($ar["ID"], "."))
					$NS["MLIST"][] = $ar["ID"];
			}
			$NS["MNUM"] = 0;
			$NS["FILE_LIST"] = array();
			$NS["FILES_COUNT"] = 0;
			$NS["MODFILES_COUNT"] = 0;
		}

		$arError = false;
		$moduleId = $NS["MLIST"][$NS["MNUM"]];
		$moduleFolder = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleId."/";
		$dbtype = mb_strtolower($DB->type);
		$fileCount = 0;
		$modifiedFileCount = 0;
		$state = array();
		$skip = false;

		$ver = \Bitrix\Main\ModuleManager::getVersion($moduleId);
		if ($ver === false)
		{
			$state = array(
				"STATUS" => false,
				"MESSAGE" =>  GetMessage("CL_MODULE_VERSION_ERROR", array("#module_id#" => $moduleId))."\n"
			);
			$arError = true;
		}
		else
		{
			if(empty($NS["FILE_LIST"]))
			{
				$sHost = COption::GetOptionString("main", "update_site", "www.bitrixsoft.com");
				$proxyAddr = COption::GetOptionString("main", "update_site_proxy_addr", "");
				$proxyPort = COption::GetOptionString("main", "update_site_proxy_port", "");
				$proxyUserName = COption::GetOptionString("main", "update_site_proxy_user", "");
				$proxyPassword = COption::GetOptionString("main", "update_site_proxy_pass", "");

				$http = new \Bitrix\Main\Web\HttpClient();
				$http->setProxy($proxyAddr, $proxyPort, $proxyUserName, $proxyPassword);

				$data = $http->get("https://".$sHost."/bitrix/updates/checksum.php?check_sum=Y&module_id=".$moduleId."&ver=".$ver."&dbtype=".$dbtype."&mode=2");

				$result = unserialize(gzinflate($data), ['allowed_classes' => false]);
				if (is_array($result))
				{
					$NS["FILE_LIST"] = $result;
				}
				$NS["MODULE_FILES_COUNT"] = count($NS["FILE_LIST"]);
			}
			else
			{
				$result = $NS["FILE_LIST"];
			}

			$message = "";
			$timeout = COption::GetOptionString("main", "update_load_timeout", "30");
			if (is_array($result) && empty($result["error"]))
			{
				foreach($result as $file => $checksum)
				{
					$filePath = $moduleFolder.$file;
					unset($NS["FILE_LIST"][$file]);

					if (!file_exists($filePath))
						continue;

					$fileCount++;
					if (md5_file($filePath)!=$checksum)
					{
						$message.= str_replace(array("//", "\\\\"), array("/", "\\"), $filePath)."\n";
						$modifiedFileCount++;
					}

					foreach ($installFilesMapping as $key => $value)
					{
						if (mb_strpos($file, $key) === 0)
						{
							$filePath = str_replace($key, $_SERVER["DOCUMENT_ROOT"].$value, $file);
							if (file_exists($filePath) && md5_file($filePath)!=$checksum)
							{
								$modifiedFileCount++;
								$message.= str_replace(array("//", "\\\\"), array("/", "\\"), $filePath)."\n";
							}
							$fileCount++;
						}
					}

					if ((time()-$startTime)>=$timeout)
						break;
				}
				if ($message <> '')
				{
					$state = array(
						"MESSAGE" => $message,
						"STATUS" => false
					);
				}
			}
			else
			{
				if($result["error"]!= "unknow module id")
				{
					$state["MESSAGE"] = GetMessage("CL_CANT_CHECK", array("#module_id#" => $moduleId))."\n";
					$arError = true;
				}
				else
					$skip = true;
			}
		}
		if (!empty($state["MESSAGE"]))
		{
			if (!isset($NS["MESSAGE"][$moduleId]))
			{
				$NS["MESSAGE"][$moduleId] = '';
			}

			$NS["MESSAGE"][$moduleId].=$state["MESSAGE"];
		}
		if (!$arError && !$skip)
		{
			if (empty($NS["FILE_LIST"]))
			{
				if (!isset($NS["MESSAGE"][$moduleId]) || $NS["MESSAGE"][$moduleId] == '')
					$NS["MESSAGE"][$moduleId] = GetMessage("CL_NOT_MODIFIED", array("#module_id#" => $moduleId))."\n";
				else
					$NS["MESSAGE"][$moduleId] = GetMessage("CL_MODIFIED_FILES", array("#module_id#" => $moduleId))."\n".$NS["MESSAGE"][$moduleId];
			}
			$NS["FILES_COUNT"]+=$fileCount;
			$NS["MODFILES_COUNT"]+=$modifiedFileCount;
		}
		if ((isset($state["STATUS"]) && $state["STATUS"] === false) || $arError == true || $skip)
		{
			if ((isset($state["STATUS"]) && $state["STATUS"] === false) || $arError == true)
				$NS["STATUS"] = false;
			$NS["FILE_LIST"] = array();
			$NS["MODULE_FILES_COUNT"] = 0;
		}

		if (($NS["MNUM"]+1)>=(count($NS["MLIST"])) && empty($NS["LAST_FILE"]))
		{
			$arDetailReport = "";
			foreach($NS["MESSAGE"] as $module_message)
				$arDetailReport.="<div class=\"checklist-dot-line\"></div>".$module_message;
			$arResult = array(
				"MESSAGE" => array(
					"PREVIEW" => GetMessage("CL_KERNEL_CHECK_FILES").$NS["FILES_COUNT"]."\n".
					GetMessage("CL_KERNEL_CHECK_MODULE").count($NS["MLIST"])."\n".
					GetMessage("CL_KERNEL_CHECK_MODIFIED").$NS["MODFILES_COUNT"],
					"DETAIL" => $arDetailReport
					),
				"STATUS" => ($NS["STATUS"] === false?false:true)
			);

		}
		else
		{
			$percent =  round(($NS["MNUM"])/(count($NS["MLIST"])*0.01), 0);
			$module_percent = 0;
			if ($NS["MODULE_FILES_COUNT"]>0)
				$module_percent =  (1/(count($NS["MLIST"])*0.01))*((($NS["MODULE_FILES_COUNT"]-count($NS["FILE_LIST"]))/($NS["MODULE_FILES_COUNT"]*0.01))*0.01);
			$percent += $module_percent;
			$arResult = array(
				"IN_PROGRESS" => "Y",
				"PERCENT" => number_format($percent, 2),
			);
			if (empty($NS["FILE_LIST"]))
			{
				$NS["MNUM"]++;
				$NS["MODULE_FILES_COUNT"] = 0;
			}
		}
		return $arResult;
	}

	public static function CheckSecurity($arParams)
	{
		global $DB;
		$err = 0;
		$arResult['STATUS'] = false;
		$arMessage = '';
		switch ($arParams["ACTION"])
		{
			case "SECURITY_LEVEL":
				if (CModule::IncludeModule("security"))
				{
						if ($arMask = CSecurityFilterMask::GetList()->Fetch())
							$arMessage .= (++$err).". ".GetMessage("CL_FILTER_EXEPTION_FOUND")."\n";
						if(!CSecurityFilter::IsActive())
							$arMessage .= (++$err).". ".GetMessage("CL_FILTER_NON_ACTIVE")."\n";
						if(COption::GetOptionString("main", "captcha_registration", "N") == "N")
							$arMessage .= (++$err).". ".GetMessage("CL_CAPTCHA_NOT_USE")."\n";

					if (CCheckListTools::AdminPolicyLevel() != "high")
						$arMessage .= (++$err).". ".GetMessage("CL_ADMIN_SECURITY_LEVEL")."\n";
					if (COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE) != (E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE) && COption::GetOptionString("main","error_reporting","") != 0)
						$arMessage .= (++$err).". ".GetMessage("CL_ERROR_REPORTING_LEVEL")."\n";
					if($DB->debug)
						$arMessage .= (++$err).". ".GetMessage("CL_DBDEBUG_TURN_ON")."\n";
					if ($arMessage)
					{
						$arResult["STATUS"] = false;
						$arResult["MESSAGE"]=array(
								"PREVIEW" => GetMessage("CL_MIN_LEVEL_SECURITY"),
								"DETAIL" => GetMessage("CL_ERROR_FOUND")."\n".$arMessage
						);
					}
					else
					{
						$arResult["STATUS"] = true;
						$arResult["MESSAGE"]=array(
								"PREVIEW" => GetMessage("CL_LEVEL_SECURITY")."\n"
						);
					}
				}
				else
					$arResult = array(
						"STATUS" => false,
						"MESSAGE" => array(
							"PREVIEW" => GetMessage("CL_SECURITY_MODULE_NOT_INSTALLED")."\n"
						)
					);
			break;
			case "ADMIN_POLICY":
				if (CCheckListTools::AdminPolicyLevel() != "high")
					$arResult["MESSAGE"]["PREVIEW"] = GetMessage("CL_ADMIN_SECURITY_LEVEL")."\n";
				else
					$arResult = array(
						"STATUS" => true,
						"MESSAGE" => array(
							"PREVIEW" => GetMessage("CL_ADMIN_SECURITY_LEVEL_IS_HIGH")."\n"
						)
					);
			break;
		}

		return $arResult;
	}

	public static function CheckErrorReport()
	{
		global $DBDebug;
		$err = 0;
		$arResult["STATUS"] = true;
		$arMessage = '';
		if ($DBDebug)
			$arMessage .= (++$err).". ".GetMessage("CL_DBDEBUG_TURN_ON")."\n";
		if (COption::GetOptionString("main", "error_reporting", "")!=0 && ini_get("display_errors"))
			$arMessage .= (++$err).". ".GetMessage("CL_ERROR_REPORT_TURN_ON")."\n";

		if($arMessage)
		{
			$arResult["STATUS"] = false;
			$arResult["MESSAGE"] = array(
				"PREVIEW" => GetMessage("CL_ERROR_FOUND_SHORT")."\n",
				"DETAIL" => $arMessage
			);
		}
		return $arResult;
	}

	public static function IsCacheOn()
	{
		$arResult["STATUS"] = true;
		if (COption::GetOptionString("main", "component_cache_on", "Y") == "N")
		{
			$arResult["STATUS"] = false;
			$arResult["MESSAGE"]=array(
				"PREVIEW" => GetMessage("CL_TURNOFF_AUTOCACHE")."\n"
			);
		}
		else
			$arResult["MESSAGE"]=array(
				"PREVIEW" => GetMessage("CL_TURNON_AUTOCACHE")."\n"
			);

		return $arResult;
	}

	public static function CheckDBPassword()
	{
		$err = 0;
		$arMessage = "";
		$sign = ",.#!*%$:-^@{}[]()'\"-+=<>?`&;";
		$dit = "1234567890";
		$have_sign = false;
		$have_dit = false;
		$arResult["STATUS"] = true;

		$connection = Main\Application::getInstance()->getConnection();
		$password = $connection->getPassword();

		if ($password == '')
		{
			$arMessage.=GetMessage("CL_EMPTY_PASS")."\n";
		}
		else
		{
			if ($password == mb_strtolower($password))
				$arMessage .= (++$err).". ".GetMessage("CL_SAME_REGISTER")."\n";

			for($j=0, $c = mb_strlen($password); $j<$c; $j++)
			{
				if (mb_strpos($sign, $password[$j]) !== false)
					$have_sign = true;
				if (mb_strpos($dit, $password[$j]) !== false)
					$have_dit = true;
				if ($have_dit == true && $have_sign == true)
					break;
			}

			if (!$have_dit)
				$arMessage .= (++$err).". ".GetMessage("CL_HAVE_NO_DIT")."\n";
			if (!$have_sign)
				$arMessage .= (++$err).". ".GetMessage("CL_HAVE_NO_SIGN")."\n";
			if (mb_strlen($password) < 8)
				$arMessage .= (++$err).". ".GetMessage("CL_LEN_MIN")."\n";
		}
		if($arMessage)
		{
			$arResult["STATUS"] = false;
			$arResult["MESSAGE"]=array(
				"PREVIEW" => GetMessage("CL_ERROR_FOUND_SHORT"),
				"DETAIL" => $arMessage
			);
		}
		else
			$arResult["MESSAGE"]=array(
				"PREVIEW" => GetMessage("CL_NO_ERRORS"),
			);
		return $arResult;
	}

	public static function CheckPerfomance($arParams)
	{
		if (!IsModuleInstalled("perfmon"))
			return array(
				"STATUS" => false,
				"MESSAGE" => array(
					"PREVIEW" => GetMessage("CL_CHECK_PERFOM_NOT_INSTALLED")
				)
			);
		$arResult = array(
			"STATUS" => true
		);
		switch($arParams["ACTION"])
		{
			case "PHPCONFIG":
				if(COption::GetOptionString("perfmon", "mark_php_is_good", "N") == "N")
				{
					$arResult["STATUS"] = false;
					$arResult["MESSAGE"]=array(
						"PREVIEW" => GetMessage("CL_PHP_NOT_OPTIMAL", array("#LANG#" => LANG))."\n"
					);
				}
				else
				{
					$arResult["MESSAGE"]=array(
						"PREVIEW" => GetMessage("CL_PHP_OPTIMAL")."\n"
					);
				}
			break;
			case "PERF_INDEX":
			$arPerfIndex = COption::GetOptionString("perfmon", "mark_php_page_rate", "N");
			if($arPerfIndex == "N")
			{
				$arResult["STATUS"] = false;
				$arResult["MESSAGE"]=array(
					"PREVIEW" => GetMessage("CL_CHECK_PERFOM_FAILED", array("#LANG#" => LANG))."\n"
				);
			}
			elseif($arPerfIndex<15)
			{
				$arResult["STATUS"] = false;
				$arResult["MESSAGE"]=array(
					"PREVIEW" => GetMessage("CL_CHECK_PERFOM_LOWER_OPTIMAL", array("#LANG#" => LANG))."\n"
				);
			}
			else
			{
				$arResult["MESSAGE"]=array(
					"PREVIEW" => GetMessage("CL_CHECK_PERFOM_PASSED")."\n"
				);
			}
			break;
		}
		return $arResult;
	}

	public static function CheckQueryString($arParams = array())
	{
		$time = time();
		$arPath = array(
			$_SERVER["DOCUMENT_ROOT"],
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/components/"
		);
		$arExept = array(
			"FOLDERS" => array("images", "bitrix", "upload", ".svn"),
			"EXT" => array("php"),
			"FILES" => array(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/dbconn.php",
				"after_connect.php"
			)
		);

		$arParams["STEP"] = (intval($arParams["STEP"])>=0)?intval($arParams["STEP"]):0;
		if (!\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"] || $arParams["STEP"] == 0)
		{
			\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"] = array(
				"LAST_FILE" => "",
				"FOUND" => "",
				"PERCENT" => 0,
			);
			$files = array();
			$arPathTmp = $arPath;
			foreach($arPathTmp as $path)
			{
				CCheckListTools::__scandir($path, $files, $arExept);
			}
			\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["COUNT"] = count($files);
		}

		$arFileNum = 0;
		foreach ($arPath as $namespace)
		{
			$files = array();
			CCheckListTools::__scandir($namespace, $files, $arExept);
			foreach($files as $file)
			{
				$arFileNum++;
				//this is not first step?
				if (\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["LAST_FILE"] <> '')
				{
					if (\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["LAST_FILE"] == $file)
						\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["LAST_FILE"] = "";
					continue;
				}

				if ($content = file_get_contents($file))
				{
					$queries = [];
					preg_match('/((?:mysql_query|mysqli_query|odbc_exec|oci_execute|odbc_execute)\(.*\))/ism', $content, $queries);

					if ($queries && !empty($queries[0]))
					{
						\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["FOUND"] .= str_replace(array("//", "\\\\"), array("/", "\\"), $file)."\n";
					}
				}

				if (time()-$time>=20)
				{
					\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["LAST_FILE"] = $file;
					return array(
						"IN_PROGRESS" => "Y",
						"PERCENT" => round($arFileNum/(\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["COUNT"]*0.01), 2)
					);
				}
			}
		}
		$arResult = array("STATUS" => true);
		if (\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["FOUND"] <> '')
		{
			$arResult["STATUS"] = false;
			$arResult["MESSAGE"]=array(
				"PREVIEW" => GetMessage("CL_KERNEL_CHECK_FILES").$arFileNum.".\n".GetMessage("CL_ERROR_FOUND_SHORT")."\n",
				"DETAIL" => GetMessage("CL_DIRECT_QUERY_TO_DB")."\n".\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]["FOUND"],
			);
		}
		else
		{
			$arResult["MESSAGE"]=array(
				"PREVIEW" => GetMessage("CL_KERNEL_CHECK_FILES").$arFileNum."\n"
			);
		}
		unset(\Bitrix\Main\Application::getInstance()->getSession()["BX_CHECKLIST"]);
		return $arResult;
	}

	public static function KeyCheck()
	{
		$arResult = array("STATUS" => false);
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");
		$arUpdateList = CUpdateClient::GetUpdatesList($errorMessage, LANG);
		if(array_key_exists("CLIENT", $arUpdateList)&&$arUpdateList["CLIENT"][0]["@"]["RESERVED"] == "N")
		{
			$arResult = array(
				"STATUS" => true,
				"MESSAGE" => array("PREVIEW" => GetMessage("CL_LICENSE_KEY_ACTIVATE"))
			);
		}
		else
			$arResult["MESSAGE"] = array("PREVIEW" => GetMessage("CL_LICENSE_KEY_NONE_ACTIVATE", array("#LANG#" => LANG)));

		return $arResult;
	}

	public static function CheckVMBitrix(){
		$arResult = array();
		$arResult["STATUS"] = false;

		$http = new \Bitrix\Main\Web\HttpClient();
		$ver = $http->get("https://www.1c-bitrix.ru/download/vm_bitrix.ver");

		if (version_compare(getenv('BITRIX_VA_VER'), $ver) >= 0)
		{
			$arResult["STATUS"] = true;
			$arResult["MESSAGE"] = array(
				'PREVIEW' => GetMessage("CL_VMBITRIX_ACTUAL"),
			);
		}
		else
		{
			$arResult["MESSAGE"] = array(
				'PREVIEW' => GetMessage("CL_VMBITRIX_NOT_ACTUAL"),
			);
		}

		return $arResult;
	}

	public static function CheckSiteCheckerStatus(){
		$arResult = array();
		$arResult["STATUS"] = false;

		$checkerStatus = COption::GetOptionString('main', 'site_checker_success', 'N');
		if ($checkerStatus == 'Y')
		{
			$arResult["STATUS"] = true;
			$arResult["MESSAGE"] = array(
				'PREVIEW' => GetMessage("CL_SITECHECKER_OK", array("#LANG#" => LANG)),
			);
		}
		else
		{
			$arResult["MESSAGE"] = array(
				'PREVIEW' => GetMessage("CL_SITECHECKER_NOT_OK", array("#LANG#" => LANG)),
			);
		}

		return $arResult;
	}

	public static function CheckSecurityScannerStatus(){
		$arResult = array();
		$arResult["STATUS"] = false;

		if (!Loader::includeModule('security'))
		{
			return $arResult;
		}

		$lastTestingInfo = CSecuritySiteChecker::getLastTestingInfo();
		$criticalResultsCount = CSecuritySiteChecker::calculateCriticalResults($lastTestingInfo["results"] ?? []);

		if ( (time()-MakeTimeStamp($lastTestingInfo['test_date'] ?? '', FORMAT_DATE)) > 60*60*24*30 )
		{
			$arResult["MESSAGE"] = array(
				'PREVIEW' => GetMessage("CL_SECURITYSCANNER_OLD", array("#LANG#" => LANG)),
			);
		}
		elseif ($criticalResultsCount === 0)
		{
			$arResult["STATUS"] = true;
			$arResult["MESSAGE"] = array(
				'PREVIEW' => GetMessage("CL_SECURITYSCANNER_OK"),
			);
		}
		else
		{
			$arResult["MESSAGE"] = array(
				'PREVIEW' => GetMessage("CL_SECURITYSCANNER_NOT_OK", array("#LANG#" => LANG)),
			);
		}

		return $arResult;
	}
}

class CCheckListTools
{
	public static function __scandir($pwd, &$arFiles, $arExept = false)
	{
		if(file_exists($pwd))
		{
			$dir = scandir($pwd);
			foreach ($dir as $file)
			{
				if ($file == ".." || $file == ".")
					continue;
				if (is_dir($pwd."$file"))
				{
					if (!in_array($file, $arExept["FOLDERS"]))
						CCheckListTools::__scandir($pwd."$file/", $arFiles, $arExept);
				}
				elseif(in_array(mb_substr(strrchr($file, '.'), 1), $arExept["EXT"])
					&& !in_array($pwd.$file, $arExept["FILES"])
					&& !in_array($file, $arExept["FILES"])
					)
				{
					$arFiles[] = $pwd."/$file";
				}
			}
		}
	}

	/**
	 * @return string 'low', 'middle', 'high'
	 */
	public static function AdminPolicyLevel()
	{
		$policy = CUser::getPolicy(1);

		$preset = Policy\RulesCollection::createByPreset(Policy\RulesCollection::PRESET_MIDDLE);
		if ($policy->compare($preset))
		{
			// middle preset is stronger than the current
			return 'low';
		}

		$preset = Policy\RulesCollection::createByPreset(Policy\RulesCollection::PRESET_HIGH);
		if ($policy->compare($preset))
		{
			// high preset is stronger than the current
			return 'middle';
		}

		return 'high';
	}
}
