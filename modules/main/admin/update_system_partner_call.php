<?
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/
if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

	if(!$USER->CanDoOperation('install_updates') || !check_bitrix_sessid())
	{
		echo "*";
		die();
	}
}

@set_time_limit(0);
ini_set("track_errors", "1");
ignore_user_abort(true);

IncludeModuleLangFile(__FILE__);

$errorMessage = "";

$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");

$queryType = "M";

$arRequestedModules = array();
if (array_key_exists("reqm", $_REQUEST))
{
	$arRequestedModulesTmp = explode(",", $_REQUEST["reqm"]);
	for ($i = 0, $cnt = count($arRequestedModulesTmp); $i < $cnt; $i++)
		if (!in_array($arRequestedModulesTmp[$i], $arRequestedModules))
			$arRequestedModules[] = $arRequestedModulesTmp[$i];
}
else
{
	$arRequestedModules = CUpdateClientPartner::GetRequestedModules($_REQUEST["addmodule"]);
}

COption::SetOptionString("main", "update_system_update", Date($GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()));
/************************************/
$arUpdatedModulesList = array();
$loadResult = CUpdateClientPartner::LoadModulesUpdates($errorMessage, $arUpdateDescription, LANG, $stableVersionsOnly, $arRequestedModules, array_key_exists("reqm", $_REQUEST));

if ($loadResult == "S")
{
	CUpdateClientPartner::AddMessage2Log("LoadModulesUpdates-Step", "LMU01");

	$message = "";
	if (isset($arUpdateDescription["DATA"]["#"]["ITEM"]))
	{
		for ($i = 0, $cnt = count($arUpdateDescription["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
		{
			if ($message <> '')
				$message .= ", ";
			$message .= $arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["NAME"];
			if ($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"] <> '')
				$message .= " (".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"].")";
		}
	}

	die("STP0|".$message);
}
elseif ($loadResult == "E")
{
	if ($errorMessage == '')
		$errorMessage = "[CL02] ".GetMessage("SUPC_ME_PACK");
	CUpdateClientPartner::AddMessage2Log($errorMessage, "CL02");
}
elseif ($loadResult == "F")
{
	CUpdateClientPartner::AddMessage2Log("Finish - NOUPDATES", "STEP");
	die("FIN");
}

/*if (!CUpdateClientPartner::GetNextStepUpdates($errorMessage, LANG, $stableVersionsOnly, $arRequestedModules, array_key_exists("reqm", $_REQUEST)))
{
	$errorMessage .= "[CL01] ".GetMessage("SUPC_ME_LOAD").". ";
	CUpdateClientPartner::AddMessage2Log(GetMessage("SUPC_ME_LOAD"), "CL01");
}*/

if ($errorMessage == '')
{
	$temporaryUpdatesDir = "";
	if (!CUpdateClientPartner::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
	{
		$errorMessage .= "[CL02] ".GetMessage("SUPC_ME_PACK").". ";
		CUpdateClientPartner::AddMessage2Log(GetMessage("SUPC_ME_PACK"), "CL02");
	}
}

if ($errorMessage == '')
{
	if (!CUpdateClientPartner::CheckUpdatability($temporaryUpdatesDir, $errorMessage))
	{
		$errorMessage .= "[CL03] ".GetMessage("SUPC_ME_CHECK").". ";
		CUpdateClientPartner::AddMessage2Log(GetMessage("SUPC_ME_CHECK"), "CL03");
	}
}

$arStepUpdateInfo = $arUpdateDescription;
/*if (strlen($errorMessage) <= 0)
{
	$arStepUpdateInfo = CUpdateClientPartner::GetStepUpdateInfo($temporaryUpdatesDir, $errorMessage);
	//CUpdateClientPartner::AddMessage2Log(print_r($arStepUpdateInfo, true), "!!!!!");
}*/

if ($errorMessage == '')
{
	if (isset($arStepUpdateInfo["DATA"]["#"]["ERROR"]))
	{
		for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ERROR"]); $i < $cnt; $i++)
			$errorMessage .= "[".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["#"];
	}
}

$arItemsUpdated = array();
$arItemsUpdatedDescr = array();
if ($errorMessage == '')
{
	if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"]))
	{
		for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
		{
			$arItemsUpdated[$arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME"]] = $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"];
			$arItemsUpdatedDescr[$arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME"]] = $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["DESCR"];
		}
	}
}

if ($errorMessage == '')
{
	if (isset($arStepUpdateInfo["DATA"]["#"]["NOUPDATES"]))
	{
		CUpdateClientPartner::ClearUpdateFolder($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$temporaryUpdatesDir);
		CUpdateClientPartner::AddMessage2Log("Finish - NOUPDATES", "STEP");
		echo "FIN";
	}
	else
	{
		if ($errorMessage == '')
		{
			if (!CUpdateClientPartner::UpdateStepModules($temporaryUpdatesDir, $errorMessage))
			{
				$errorMessage .= "[CL04] ".GetMessage("SUPC_ME_UPDATE").". ";
				CUpdateClientPartner::AddMessage2Log(GetMessage("SUPC_ME_UPDATE"), "CL04");
			}
		}

		if ($errorMessage <> '')
		{
			CUpdateClientPartner::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
			echo "ERR".$errorMessage;
		}
		else
		{
			echo "STP";
			echo count($arItemsUpdated)."|";
			$bFirst = True;
			foreach ($arItemsUpdated as $key => $value)
			{
				$strModuleDescr = "";
				if ($arItemsUpdatedDescr[$key] <> '')
				{
					$strModuleDescr = "<br>".htmlspecialcharsback($arItemsUpdatedDescr[$key]);
					$strModuleDescr = preg_replace("#</?pre>#i", " ", $strModuleDescr);
					$strModuleDescr = preg_replace("/[\s\n\r]+/", " ", $strModuleDescr);
					$strModuleDescr = addslashes($strModuleDescr);
				}

				CUpdateClientPartner::AddMessage2Log("Updated: ".$key.(($value <> '') ? " (".$value.")" : "").$strModuleDescr, "UPD_SUCCESS");
				if(COption::GetOptionString("main", "event_log_marketplace", "Y") === "Y")
					CEventLog::Log("INFO", "MP_MODULE_DOWNLOADED", "main", $key, $value);

				echo ($bFirst ? "" : ", ").$key.(($value <> '') ? " (".$value.")" : "");
				$bFirst = False;
			}
		}
	}
}
else
{
	CUpdateClientPartner::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
	echo "ERR".$errorMessage;
}
/************************************/


if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
}
?>