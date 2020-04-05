<?
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/
if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

	if(!$USER->CanDoOperation('install_updates'))
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

if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	if (!check_bitrix_sessid())
	{
		echo "ERR".GetMessage("ACCESS_DENIED");
		die();
	}
}

$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");

$queryType = $_REQUEST["query_type"];
if (!in_array($queryType, array("M", "L", "H")))
	$queryType = "M";

$arRequestedModules = array();
if (array_key_exists("requested_modules", $_REQUEST))
{
	$arRequestedModulesTmp = explode(",", $_REQUEST["requested_modules"]);
	for ($i = 0, $cnt = count($arRequestedModulesTmp); $i < $cnt; $i++)
		if (!in_array($arRequestedModulesTmp[$i], $arRequestedModules))
			$arRequestedModules[] = $arRequestedModulesTmp[$i];
}

$arRequestedLangs = array();
if (array_key_exists("requested_langs", $_REQUEST))
{
	$arRequestedLangsTmp = explode(",", $_REQUEST["requested_langs"]);
	for ($i = 0, $cnt = count($arRequestedLangsTmp); $i < $cnt; $i++)
		if (!in_array($arRequestedLangsTmp[$i], $arRequestedLangs))
			$arRequestedLangs[] = $arRequestedLangsTmp[$i];
}

$arRequestedHelps = array();
if (array_key_exists("requested_helps", $_REQUEST))
{
	$arRequestedHelpsTmp = explode(",", $_REQUEST["requested_helps"]);
	for ($i = 0, $cnt = count($arRequestedHelpsTmp); $i < $cnt; $i++)
		if (!in_array($arRequestedHelpsTmp[$i], $arRequestedHelps))
			$arRequestedHelps[] = $arRequestedHelpsTmp[$i];
}

COption::SetOptionString("main", "update_system_update", Date($GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()));

/************************************/
if ($queryType == "M")
{
	$arUpdatedModulesList = array();
	$loadResult = CUpdateClient::LoadModulesUpdates($errorMessage, $arUpdateDescription, LANG, $stableVersionsOnly, $arRequestedModules);
	if ($loadResult == "S")
	{
		CUpdateClient::AddMessage2Log("LoadModulesUpdates-Step", "LMU01");

		$message = "";
		if (isset($arUpdateDescription["DATA"]["#"]["ITEM"]))
		{
			for ($i = 0, $cnt = count($arUpdateDescription["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
			{
				if (strlen($message) > 0)
					$message .= ", ";
				$message .= $arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["NAME"];
				if (strlen($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"]) > 0)
					$message .= " (".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"].")";
			}
		}

		die("STP0|".$message);
	}
	elseif ($loadResult == "E")
	{
		if (strlen($errorMessage) <= 0)
			$errorMessage = "[CL02] ".GetMessage("SUPC_ME_PACK");
		CUpdateClient::AddMessage2Log($errorMessage, "CL02");
	}
	elseif ($loadResult == "F")
	{
		CUpdateClient::AddMessage2Log("Finish - NOUPDATES", "STEP");

		$bIntranet = CModule::IncludeModule('intranet');
		if ($bIntranet)
		{
			CAdminNotify::Add(array(
				'MODULE_ID' => 'main',
				'TAG' => 'checklist_cp',
				'MESSAGE' => GetMessage("SUPC_NOTIFY_CHECKLIST", array("#LANG#" => LANG)),
				'NOTIFY_TYPE' => CAdminNotify::TYPE_NORMAL,
				'PUBLIC_SECTION' => 'N',
			));
		}

		die("FIN");
	}

	if (strlen($errorMessage) <= 0)
	{
		$temporaryUpdatesDir = "";
		if (!CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
		{
			$errorMessage .= "[CL02] ".GetMessage("SUPC_ME_PACK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_PACK"), "CL02");
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (!CUpdateClient::CheckUpdatability($temporaryUpdatesDir, $errorMessage))
		{
			$errorMessage .= "[CL03] ".GetMessage("SUPC_ME_CHECK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_CHECK"), "CL03");
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (!CUpdateClient::UpdateStepModules($temporaryUpdatesDir, $errorMessage, defined("US_BITRIX24_MODE") && US_BITRIX24_MODE))
		{
			$errorMessage .= "[CL04] ".GetMessage("SUPC_ME_UPDATE").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_UPDATE"), "CL04");
		}
	}

	if (strlen($errorMessage) > 0)
	{
		CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
		echo "ERR".$errorMessage;
	}
	else
	{
		if (defined("US_BITRIX24_MODE") && US_BITRIX24_MODE)
		{
			COption::SetOptionString("main", "BITRIX24_VERSIONS", "");
			@unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/versions.php");
		}

		echo "STP";
		echo count($arUpdateDescription["DATA"]["#"]["ITEM"])."|";
		$bFirst = True;
		for ($i = 0, $cnt = count($arUpdateDescription["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
		{
			$strModuleDescr = "";
			if (strlen($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["DESCR"]) > 0)
			{
				$strModuleDescr = "<br>".htmlspecialcharsback($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["DESCR"]);
				$strModuleDescr = preg_replace("#</?pre>#i", " ", $strModuleDescr);
				$strModuleDescr = preg_replace("/[\s\n\r]+/", " ", $strModuleDescr);
				$strModuleDescr = addslashes($strModuleDescr);
			}

			CUpdateClient::AddMessage2Log("Updated: ".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["NAME"].(($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"] != "0") ? " (".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"].")" : "").$strModuleDescr, "UPD_SUCCESS");

			echo ($bFirst ? "" : ", ").$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["NAME"].(($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"] != "0") ? " (".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"].")" : "");
			$bFirst = False;
		}
	}
}
elseif ($queryType == "L")
{
	$loadResult = CUpdateClient::LoadLangsUpdates($errorMessage, $arUpdateDescription, LANG, $stableVersionsOnly, $arRequestedLangs);
	if ($loadResult == "S")
	{
		CUpdateClient::AddMessage2Log("LoadLangsUpdates-Step", "LLU01");

		$message = "";
		if (isset($arUpdateDescription["DATA"]["#"]["ITEM"]))
		{
			for ($i = 0, $cnt = count($arUpdateDescription["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
			{
				if (strlen($message) > 0)
					$message .= ", ";
				$message .= $arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["NAME"];
				if (strlen($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"]) > 0)
					$message .= " (".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"].")";
			}
		}

		die("STP0|".$message);
	}
	elseif ($loadResult == "E")
	{
		if (strlen($errorMessage) <= 0)
			$errorMessage = "[CL02] ".GetMessage("SUPC_ME_PACK");
		CUpdateClient::AddMessage2Log($errorMessage, "CL02");
	}
	elseif ($loadResult == "F")
	{
		CUpdateClient::AddMessage2Log("Finish - NOUPDATES", "STEP");
		die("FIN");
	}

	/*if (!CUpdateClient::GetNextStepLangUpdates($errorMessage, LANG, $arRequestedLangs))
	{
		$errorMessage .= "[CL01] ".GetMessage("SUPC_ME_LOAD").". ";
		CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_LOAD"), "CL01");
	}*/

	if (StrLen($errorMessage) <= 0)
	{
		$temporaryUpdatesDir = "";
		if (!CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
		{
			$errorMessage .= "[CL02] ".GetMessage("SUPC_ME_PACK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_PACK"), "CL02");
		}
	}

	$arStepUpdateInfo = $arUpdateDescription;
	/*if (strlen($errorMessage) <= 0)
	{
		$arStepUpdateInfo = CUpdateClient::GetStepUpdateInfo($temporaryUpdatesDir, $errorMessage);
	}*/

	if (StrLen($errorMessage) <= 0)
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ERROR"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ERROR"]); $i < $cnt; $i++)
				$errorMessage .= "[".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["#"];
		}
	}

	$arItemsUpdated = array();
	if (StrLen($errorMessage) <= 0)
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
				$arItemsUpdated[$arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME"]] = $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"];
		}
	}

	if (StrLen($errorMessage) <= 0)
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["NOUPDATES"]))
		{
			CUpdateClient::ClearUpdateFolder($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$temporaryUpdatesDir);
			CUpdateClient::AddMessage2Log("Finish - NOUPDATES", "STEP");
			echo "FIN";
		}
		else
		{
			if (strlen($errorMessage) <= 0)
			{
				if (!CUpdateClient::UpdateStepLangs($temporaryUpdatesDir, $errorMessage))
				{
					$errorMessage .= "[CL04] ".GetMessage("SUPC_LE_UPD").". ";
					CUpdateClient::AddMessage2Log(GetMessage("SUPC_LE_UPD"), "CL04");
				}
			}

			if (StrLen($errorMessage) > 0)
			{
				CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
				echo "ERR".$errorMessage;
			}
			else
			{
				if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"]))
				{
					$ar = array();
					$dbRes = CLanguage::GetList(($by="sort"), ($order="asc"), array("ACTIVE" => "Y"));
					while ($arRes = $dbRes->Fetch())
						$ar[] = $arRes["ID"];

					for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
					{
						if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ID"])
							&& !in_array($arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ID"], $ar))
						{
							$cultureId = false;
							if(class_exists('\Bitrix\Main\Localization\CultureTable'))
							{
								$culture = \Bitrix\Main\Localization\CultureTable::getRow(array('filter'=>array(
									"=FORMAT_DATE" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["FORMAT_DATE"],
									"=FORMAT_DATETIME" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["FORMAT_DATETIME"],
									"=CHARSET" => (defined('BX_UTF')? "utf-8" : $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ENC"]),
									"=DIRECTION" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["DIRECTION"],
								)));

								if($culture)
								{
									$cultureId = $culture["ID"];
								}
								else
								{
									$addResult = \Bitrix\Main\Localization\CultureTable::add(array(
										"NAME" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ID"],
										"CODE" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ID"],
										"FORMAT_DATE" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["FORMAT_DATE"],
										"FORMAT_DATETIME" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["FORMAT_DATETIME"],
										"FORMAT_NAME" => "#NAME# #LAST_NAME#",
										"CHARSET" => (defined('BX_UTF')? "utf-8" : $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ENC"]),
										"DIRECTION" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["DIRECTION"],
									));
									$cultureId = $addResult->getId();
								}
							}

							$arF = array(
								"LID" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ID"],
								"NAME" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME_EN"],
								"FORMAT_DATE" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["FORMAT_DATE"],
								"FORMAT_DATETIME" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["FORMAT_DATETIME"],
								"CHARSET" => (defined('BX_UTF')? "utf-8" : $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ENC"]),
								"DIRECTION" => $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["DIRECTION"],
								"ACTIVE" => "Y",
								"CULTURE_ID" => $cultureId,
							);

							$l = new CLanguage();
							$l->Add($arF);
						}
					}
				}

				echo "STP";
				echo count($arItemsUpdated)."|";
				$bFirst = True;
				foreach ($arItemsUpdated as $key => $value)
				{
					CUpdateClient::AddMessage2Log("Updated: ".$key.((StrLen($value) > 0) ? "(".$value.")" : ""), "UPD_SUCCESS");
					echo ($bFirst ? "" : ", ").$key.((StrLen($value) > 0) ? "(".$value.")" : "");
					$bFirst = False;
				}
			}
		}
	}
	else
	{
		CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
		echo "ERR".$errorMessage;
	}
}
else
{
	if (!CUpdateClient::GetNextStepHelpUpdates($errorMessage, LANG, $arRequestedHelps))
	{
		$errorMessage .= "[CL01] ".GetMessage("SUPC_ME_LOAD").". ";
		CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_LOAD"), "CL01");
	}

	if (StrLen($errorMessage) <= 0)
	{
		$temporaryUpdatesDir = "";
		if (!CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
		{
			$errorMessage .= "[CL02] ".GetMessage("SUPC_ME_PACK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_PACK"), "CL02");
		}
	}

	$arStepUpdateInfo = array();
	if (strlen($errorMessage) <= 0)
	{
		$arStepUpdateInfo = CUpdateClient::GetStepUpdateInfo($temporaryUpdatesDir, $errorMessage);
	}

	if (StrLen($errorMessage) <= 0)
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ERROR"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ERROR"]); $i < $cnt; $i++)
				$errorMessage .= "[".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["#"];
		}
	}

	$arItemsUpdated = array();
	if (StrLen($errorMessage) <= 0)
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
				$arItemsUpdated[$arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME"]] = $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"];
		}
	}

	if (StrLen($errorMessage) <= 0)
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["NOUPDATES"]))
		{
			CUpdateClient::ClearUpdateFolder($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$temporaryUpdatesDir);
			CUpdateClient::AddMessage2Log("Finish - NOUPDATES", "STEP");
			echo "FIN";
		}
		else
		{
			if (strlen($errorMessage) <= 0)
			{
				if (!CUpdateClient::UpdateStepHelps($temporaryUpdatesDir, $errorMessage))
				{
					$errorMessage .= "[CL04] ".GetMessage("SUPC_HE_UPD").". ";
					CUpdateClient::AddMessage2Log(GetMessage("SUPC_HE_UPD"), "CL04");
				}
			}

			if (StrLen($errorMessage) > 0)
			{
				CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
				echo "ERR".$errorMessage;
			}
			else
			{
				echo "STP";
				echo count($arItemsUpdated)."|";
				$bFirst = True;
				foreach ($arItemsUpdated as $key => $value)
				{
					CUpdateClient::AddMessage2Log("Updated: ".$key.((StrLen($value) > 0) ? "(".$value.")" : ""), "UPD_SUCCESS");
					echo ($bFirst ? "" : ", ").$key.((StrLen($value) > 0) ? "(".$value.")" : "");
					$bFirst = False;
				}
			}
		}
	}
	else
	{
		CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
		echo "ERR".$errorMessage;
	}
}
/************************************/


if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
}
?>