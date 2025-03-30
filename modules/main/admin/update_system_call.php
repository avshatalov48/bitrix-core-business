<?php
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/

/**
 * @global CUser $USER
 */

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

$queryType = isset($_REQUEST["query_type"]) ? $_REQUEST["query_type"] : '';
if (!in_array($queryType, array("M", "L", "H")))
{
	$queryType = "M";
}

$arRequestedModules = array();
$arRequestedLangs = array();

if (
	CUpdateExpertMode::isEnabled()
	&& $_SERVER["REQUEST_METHOD"] === "POST"
	&& isset($_POST['expertModules'])
)
{
	$expertModules = (string)$_POST['expertModules'];
	if (!empty($expertModules))
	{
		$expertModules = json_decode($expertModules, true);
	}
	if (is_array($expertModules))
	{
		$arRequestedModules = $expertModules;
	}
}
if (empty($arRequestedModules))
{
	if (array_key_exists("requested_modules", $_REQUEST))
	{
		$arRequestedModulesTmp = explode(",", $_REQUEST["requested_modules"]);
		for ($i = 0, $cnt = count($arRequestedModulesTmp); $i < $cnt; $i++)
		{
			if (!in_array($arRequestedModulesTmp[$i], $arRequestedModules))
			{
				$arRequestedModules[] = $arRequestedModulesTmp[$i];
			}
		}
	}
	if (array_key_exists("requested_langs", $_REQUEST))
	{
		$arRequestedLangsTmp = explode(",", $_REQUEST["requested_langs"]);
		for ($i = 0, $cnt = count($arRequestedLangsTmp); $i < $cnt; $i++)
		{
			if (!in_array($arRequestedLangsTmp[$i], $arRequestedLangs))
			{
				$arRequestedLangs[] = $arRequestedLangsTmp[$i];
			}
		}
	}
}

COption::SetOptionString("main", "update_system_update_time", time());

/************************************/
if ($queryType == "M")
{
	$loadResult = CUpdateClient::LoadModulesUpdates($errorMessage, $arUpdateDescription, LANG, $stableVersionsOnly, $arRequestedModules);
	if ($loadResult == "S")
	{
		CUpdateClient::AddMessage2Log("LoadModulesUpdates-Step", "LMU01");

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
		CUpdateClient::AddMessage2Log($errorMessage, "CL02");
	}
	elseif ($loadResult == "F")
	{
		CUpdateClient::AddMessage2Log("Finish - NOUPDATES", "STEP");

		if (IsModuleInstalled('intranet'))
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

	if ($errorMessage == '')
	{
		$temporaryUpdatesDir = "";
		if (!CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
		{
			$errorMessage .= "[CL02] ".GetMessage("SUPC_ME_PACK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_PACK"), "CL02");
		}
	}

	if ($errorMessage == '')
	{
		if (!CUpdateClient::CheckUpdatability($temporaryUpdatesDir, $errorMessage))
		{
			$errorMessage .= "[CL03] ".GetMessage("SUPC_ME_CHECK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_CHECK"), "CL03");
		}
	}

	if ($errorMessage == '')
	{
		$isB24Mode = defined("US_BITRIX24_MODE") && US_BITRIX24_MODE;
		if (!CUpdateClient::UpdateStepModules($temporaryUpdatesDir, $errorMessage, $isB24Mode))
		{
			$errorMessage .= "[CL04] ".GetMessage("SUPC_ME_UPDATE").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_UPDATE"), "CL04");
		}
	}

	if ($errorMessage <> '')
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
		$bFirst = true;
		for ($i = 0, $cnt = count($arUpdateDescription["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
		{
			$strModuleDescr = "";
			if ($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["DESCR"] <> '')
			{
				$strModuleDescr = "<br>".htmlspecialcharsback($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["DESCR"]);
				$strModuleDescr = preg_replace("#</?pre>#i", " ", $strModuleDescr);
				$strModuleDescr = preg_replace("/[\s\n\r]+/", " ", $strModuleDescr);
				$strModuleDescr = addslashes($strModuleDescr);
			}

			CUpdateClient::AddMessage2Log("Updated: ".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["NAME"].(($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"] != "0") ? " (".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"].")" : "").$strModuleDescr, "UPD_SUCCESS");

			echo ($bFirst ? "" : ", ").$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["NAME"].(($arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"] != "0") ? " (".$arUpdateDescription["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"].")" : "");
			$bFirst = false;
		}

		CUpdateClient::finalizeModuleUpdate($arUpdateDescription["DATA"]["#"]["ITEM"]);
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
		CUpdateClient::AddMessage2Log($errorMessage, "CL02");
	}
	elseif ($loadResult == "F")
	{
		CUpdateClient::AddMessage2Log("Finish - NOUPDATES", "STEP");
		die("FIN");
	}

	$temporaryUpdatesDir = "";
	if ($errorMessage == '')
	{
		if (!CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
		{
			$errorMessage .= "[CL02] ".GetMessage("SUPC_ME_PACK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPC_ME_PACK"), "CL02");
		}
	}

	$arStepUpdateInfo = $arUpdateDescription;

	if ($errorMessage == '')
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ERROR"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ERROR"]); $i < $cnt; $i++)
				$errorMessage .= "[".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["#"];
		}
	}

	if ($errorMessage == '')
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["NOUPDATES"]))
		{
			CUpdateClient::ClearUpdateFolder($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$temporaryUpdatesDir);
			CUpdateClient::AddMessage2Log("Finish - NOUPDATES", "STEP");
			echo "FIN";
		}
		else
		{
			if (!CUpdateClient::UpdateStepLangs($temporaryUpdatesDir, $errorMessage))
			{
				$errorMessage .= "[CL04] ".GetMessage("SUPC_LE_UPD").". ";
				CUpdateClient::AddMessage2Log(GetMessage("SUPC_LE_UPD"), "CL04");
			}

			if ($errorMessage <> '')
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

				$arItemsUpdated = array();
				if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"]))
				{
					for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
					{
						$arItemsUpdated[$arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["ID"]] = $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME"];
					}
				}

				echo "STP";
				echo count($arItemsUpdated)."|";
				$bFirst = true;
				foreach ($arItemsUpdated as $key => $value)
				{
					CUpdateClient::AddMessage2Log("Updated: ".$key.(($value <> '') ? " (".$value.")" : ""), "UPD_SUCCESS");
					echo ($bFirst ? "" : ", ").$key.(($value <> '') ? " (".$value.")" : "");
					$bFirst = false;
				}

				CUpdateClient::finalizeLanguageUpdate($arItemsUpdated);
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
