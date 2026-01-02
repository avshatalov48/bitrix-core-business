<?php
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/
if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

	if(!$USER->CanDoOperation('install_updates'))
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

@set_time_limit(0);
ini_set("track_errors", "1");
ignore_user_abort(true);

IncludeModuleLangFile(__FILE__);

$errorMessage = "";

$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");

$queryType = isset($_REQUEST["query_type"]) ? $_REQUEST["query_type"] : null;
if (!in_array($queryType, array("licence", "key", "register", "sources", "updateupdate", "coupon", "stability", "mail", "support_full_load")))
	$queryType = "licence";

if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	if (!check_bitrix_sessid())
	{
		echo "ERR".GetMessage("ACCESS_DENIED");
		die();
	}
}

/************************************/
if ($queryType == "licence")
{
	$newLicenceSignedKey = CUpdateClient::getNewLicenseSignedKey();
	COption::SetOptionString("main", $newLicenceSignedKey, "Y");

	echo "Y";
}
elseif ($queryType == "key")
{
	if (!check_bitrix_sessid())
		$errorMessage .= GetMessage("ACCESS_DENIED");

	$newLicenseKey = isset($_REQUEST["NEW_LICENSE_KEY"]) ? $_REQUEST["NEW_LICENSE_KEY"] : '';
	$newLicenseKey = $APPLICATION->UnJSEscape($newLicenseKey);
	$newLicenseKey = preg_replace("/[^A-Za-z0-9_.-]/", "", $newLicenseKey);

	if ($newLicenseKey == '')
		$errorMessage .= "[PULK01] ".GetMessage("SUP_ENTER_KEY").". ";
	elseif (strtolower($newLicenseKey) == "demo")
		$errorMessage .= "[PULK02] ".GetMessage("SUP_ENTER_CORRECT_KEY").". ";

	if ($errorMessage == '')
	{
		if (!($fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php", "w")))
			$errorMessage .= "[PULK03] ".GetMessage("SUP_CANT_OPEN_FILE").". ";
	}

	if ($errorMessage == '')
	{
		fputs($fp, "<"."? \$"."LICENSE_KEY = \"".EscapePHPString($newLicenseKey)."\"; ?".">");
		fclose($fp);

		if (function_exists("opcache_reset"))
		{
			opcache_reset();
		}
		elseif (function_exists("accelerator_reset"))
		{
			accelerator_reset();
		}
		echo "Y";
	}
	else
	{
		echo $errorMessage;
	}
}
elseif ($queryType == "register")
{
	if (CUpdateClient::RegisterVersion($errorMessage, LANG, $stableVersionsOnly))
	{
		CUpdateClient::AddMessage2Log("Registered", "UPD_SUCCESS");
		echo "Y";
	}
	else
	{
		CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
		echo $errorMessage;
	}
}
elseif ($queryType == "sources")
{
	$arRequestedModules = array();
	if (array_key_exists("requested_modules", $_REQUEST))
	{
		$arRequestedModulesTmp = explode(",", $_REQUEST["requested_modules"]);
		for ($i = 0, $cnt = count($arRequestedModulesTmp); $i < $cnt; $i++)
			if (!in_array($arRequestedModulesTmp[$i], $arRequestedModules))
				$arRequestedModules[] = $arRequestedModulesTmp[$i];
	}

	if (empty($arRequestedModules))
	{
		$errorMessage .= "[CL00] ".GetMessage("SUPA_ASE_NO_LIST").". ";
		CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_NO_LIST"), "CL00");
	}

	if ($errorMessage == '')
	{
		if (!CUpdateClient::GetPHPSources($errorMessage, LANG, $stableVersionsOnly, $arRequestedModules))
		{
			$errorMessage .= "[CL01] ".GetMessage("SUPA_ASE_SOURCES").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_SOURCES"), "CL01");
		}
	}

	if ($errorMessage == '')
	{
		$temporaryUpdatesDir = "";
		if (!CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
		{
			$errorMessage .= "[CL02] ".GetMessage("SUPA_ASE_PACK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_PACK"), "CL02");
		}
	}

	if ($errorMessage == '')
	{
		if (!CUpdateClient::CheckUpdatability($temporaryUpdatesDir, $errorMessage))
		{
			$errorMessage .= "[CL03] ".GetMessage("SUPA_ASE_CHECK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_CHECK"), "CL03");
		}
	}

	$arStepUpdateInfo = array();
	if ($errorMessage == '')
	{
		$arStepUpdateInfo = CUpdateClient::GetStepUpdateInfo($temporaryUpdatesDir, $errorMessage);
	}

	if ($errorMessage == '')
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ERROR"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ERROR"]); $i < $cnt; $i++)
				$errorMessage .= "[".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["#"];
		}
	}

	$arItemsUpdated = array();
	if ($errorMessage == '')
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
				$arItemsUpdated[$arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME"]] = $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"];
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
			if (!CUpdateClient::UpdateStepModules($temporaryUpdatesDir, $errorMessage))
			{
				$errorMessage .= "[CL04] ".GetMessage("SUPA_ASE_UPD").". ";
				CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_UPD"), "CL04");
			}

			if ($errorMessage <> '')
			{
				CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
				echo "ERR".$errorMessage;
			}
			else
			{
				echo "STP";
				echo count($arItemsUpdated)."|";
				$bFirst = true;
				foreach ($arItemsUpdated as $key => $value)
				{
					CUpdateClient::AddMessage2Log("Sources loaded: ".$key.(($value <> '') ? "(".$value.")" : ""), "UPD_SUCCESS");
					echo ($bFirst ? "" : ", ").$key.(($value <> '') ? "(".$value.")" : "");
					$bFirst = false;
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
elseif ($queryType == "support_full_load")
{
	$arRequestedModules = array();
	if (array_key_exists("requested_modules", $_REQUEST))
	{
		$arRequestedModulesTmp = explode(",", $_REQUEST["requested_modules"]);
		for ($i = 0, $cnt = count($arRequestedModulesTmp); $i < $cnt; $i++)
			if (!in_array($arRequestedModulesTmp[$i], $arRequestedModules))
				$arRequestedModules[] = $arRequestedModulesTmp[$i];
	}

	if (empty($arRequestedModules))
	{
		$errorMessage .= "[CL00] ".GetMessage("SUPA_ASE_NO_LIST").". ";
		CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_NO_LIST"), "CL00");
	}

	if ($errorMessage == '')
	{
		if (!CUpdateClient::GetSupportFullLoad($errorMessage, LANG, $stableVersionsOnly, $arRequestedModules))
		{
			$errorMessage .= "[CL01] ".GetMessage("SUPA_ASE_SOURCES").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_SOURCES"), "CL01");
		}
	}

	if ($errorMessage == '')
	{
		$temporaryUpdatesDir = "";
		if (!CUpdateClient::UnGzipArchive($temporaryUpdatesDir, $errorMessage, true))
		{
			$errorMessage .= "[CL02] ".GetMessage("SUPA_ASE_PACK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_PACK"), "CL02");
		}
	}

	if ($errorMessage == '')
	{
		if (!CUpdateClient::CheckUpdatability($temporaryUpdatesDir, $errorMessage))
		{
			$errorMessage .= "[CL03] ".GetMessage("SUPA_ASE_CHECK").". ";
			CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_CHECK"), "CL03");
		}
	}

	$arStepUpdateInfo = array();
	if ($errorMessage == '')
	{
		$arStepUpdateInfo = CUpdateClient::GetStepUpdateInfo($temporaryUpdatesDir, $errorMessage);
	}

	if ($errorMessage == '')
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ERROR"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ERROR"]); $i < $cnt; $i++)
				$errorMessage .= "[".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["#"];
		}
	}

	$arItemsUpdated = array();
	if ($errorMessage == '')
	{
		if (isset($arStepUpdateInfo["DATA"]["#"]["ITEM"]))
		{
			for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ITEM"]); $i < $cnt; $i++)
				$arItemsUpdated[$arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["NAME"]] = $arStepUpdateInfo["DATA"]["#"]["ITEM"][$i]["@"]["VALUE"];
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
			if (!CUpdateClient::UpdateStepModules($temporaryUpdatesDir, $errorMessage))
			{
				$errorMessage .= "[CL04] ".GetMessage("SUPA_ASE_UPD").". ";
				CUpdateClient::AddMessage2Log(GetMessage("SUPA_ASE_UPD"), "CL04");
			}

			if ($errorMessage <> '')
			{
				CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
				echo "ERR".$errorMessage;
			}
			else
			{
				echo "STP";
				echo count($arItemsUpdated)."|";
				$bFirst = true;
				foreach ($arItemsUpdated as $key => $value)
				{
					CUpdateClient::AddMessage2Log("Full loaded: ".$key.(($value <> '') ? "(".$value.")" : ""), "UPD_SUCCESS");
					echo ($bFirst ? "" : ", ").$key.(($value <> '') ? "(".$value.")" : "");
					$bFirst = false;
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
elseif ($queryType == "updateupdate")
{
	if (!CUpdateClient::UpdateUpdate($errorMessage, LANG, $stableVersionsOnly))
		$errorMessage .= GetMessage("SUPA_AUE_UPD").". ";

	if ($errorMessage == '')
	{
		CUpdateClient::AddMessage2Log("Update system updated", "UPD_SUCCESS");
		echo "Y";
	}
	else
	{
		CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
		echo $errorMessage;
	}
}
elseif ($queryType == "coupon")
{
	$coupon = isset($_REQUEST["COUPON"]) ? $_REQUEST["COUPON"] : '';
	$coupon = $APPLICATION->UnJSEscape($coupon);
	if ($coupon == '')
		$errorMessage .= GetMessage("SUPA_ACE_CPN").". ";

	if ($errorMessage == '')
	{
		if (!CUpdateClient::ActivateCoupon($coupon, $errorMessage, LANG, $stableVersionsOnly))
			$errorMessage .= GetMessage("SUPA_ACE_ACT").". ";
	}

	if ($errorMessage == '')
	{
		CUpdateClient::AddMessage2Log("Coupon activated", "UPD_SUCCESS");
		echo "Y";
	}
	else
	{
		CUpdateClient::AddMessage2Log("Error: ".$errorMessage, "UPD_ERROR");
		echo $errorMessage;
	}
}
elseif ($queryType == "stability")
{
	$stability = isset($_REQUEST["STABILITY"]) ? $_REQUEST["STABILITY"] : '';
	$stability = $APPLICATION->UnJSEscape($stability);
	if (!in_array($stability, array("Y", "N")) && !is_numeric($stability))
		$errorMessage .= GetMessage("SUPA_ASTE_FLAG").". ";

	if ($errorMessage == '')
		COption::SetOptionString("main", "stable_versions_only", $stability);

	if ($errorMessage == '')
		echo "Y";
	else
		echo $errorMessage;
}
elseif ($queryType == "mail")
{
	$email = isset($_REQUEST["EMAIL"]) ? $_REQUEST["EMAIL"] : '';
	$email = $APPLICATION->UnJSEscape($email);
	if ($email == '')
		$errorMessage .= GetMessage("SUPA_AME_EMAIL").". ";

	if ($errorMessage == '')
	{
		if (!CUpdateClient::SubscribeMail($email, $errorMessage, LANG, $stableVersionsOnly))
			$errorMessage .= GetMessage("SUPA_AME_SUBSCR").". ";
	}

	if ($errorMessage == '')
		echo "Y";
	else
		echo $errorMessage;
}
/************************************/

if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
}
