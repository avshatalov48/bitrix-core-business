<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$this->setFrameMode(false);

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/desktop/include.php');

$arParams["ID"] = (isset($arParams["ID"])? preg_replace("/[^a-z0-9_]/i", "", $arParams["ID"]) : "gdholder1");

if ($arParams["MULTIPLE"] == "Y")
	$arParams["DESKTOP_PAGE"] = intval($_REQUEST["dt_page"]);

if (
	in_array($arParams["MODE"], array("SU", "SG"))
	&& $arParams["DEFAULT_ID"] <> ''
)
{
	$arUserOptionsDefault = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], false, 0);
	if (!$arUserOptionsDefault)
	{
		$arTmp = explode("_", $arParams["DEFAULT_ID"]);
		if (count($arTmp) == 3)
		{
			$DefaultIDWOS = implode("_", array_slice($arTmp, 0, 2));
			$arUserOptionsDefaultWOS = CUserOptions::GetOption("intranet", "~gadgets_".$DefaultIDWOS, false, 0);
			if ($arUserOptionsDefaultWOS)
				CUserOptions::SetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], $arUserOptionsDefaultWOS, false, 0);
		}
	}

	$arUserOptionsEntity = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], false, 0);
	if (!$arUserOptionsEntity)
	{
		$arTmp = explode("_", $arParams["ID"]);
		if (count($arTmp) == 4)
		{
			$IDWOS = implode("_", array_merge(array_slice($arTmp, 0, 2), array($arTmp[3])));

			$arUserOptionsDefaultWOS = CUserOptions::GetOption("intranet", "~gadgets_".$IDWOS, false, 0);
			if ($arUserOptionsDefaultWOS)
				CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefaultWOS, false, 0);
		}
	}
}

if (array_key_exists("DEFAULT_ID", $arParams) && trim($arParams["DEFAULT_ID"]) <> '')
{
	$user_option_id = 0;
	$arUserOptionsDefault = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], false, 0);
}
else
{
	$user_option_id = false;
	$arParams["DEFAULT_ID"] = false;
	$arUserOptionsDefault = false;
}

if (IsModuleInstalled('intranet'))
{
	if (trim($arParams["NAME_TEMPLATE"]) == '')
		$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
	$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";

	if (!array_key_exists("PM_URL", $arParams))
		$arParams["PM_URL"] = "/company/personal/messages/chat/#USER_ID#/";
	if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
		$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
	if (IsModuleInstalled("video") && !array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
		$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#USER_ID#/";
}

if (
	!array_key_exists("GADGETS_FIXED", $arParams)
	|| !is_array($arParams["GADGETS_FIXED"])
)
{
	$arParams["GADGETS_FIXED"] = array();
}

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);

$arResult = Array();

if($USER->IsAuthorized() && $APPLICATION->GetFileAccessPermission($APPLICATION->GetCurPage(true)) > "R" && !$arParams["DEFAULT_ID"])
{
	$arResult["PERMISSION"] = "X";
}
elseif(
	$USER->IsAuthorized()
	&& $arParams["DEFAULT_ID"]
	&& (
		$USER->IsAdmin()
		|| (
			CModule::IncludeModule('socialnetwork')
			&& CSocNetUser::IsCurrentUserModuleAdmin()
		)
	)
)
{
	$arResult["PERMISSION"] = "X";
}
elseif($USER->IsAuthorized() && $arParams["CAN_EDIT"]=="Y")
{
	$arResult["PERMISSION"] = "W";
}
else
{
	$arResult["PERMISSION"] = "R";
}

$arParams["PERMISSION"] = $arResult["PERMISSION"];

if($USER->IsAuthorized() && $arResult["PERMISSION"]>"R" && check_bitrix_sessid())
{
	if($_SERVER['REQUEST_METHOD']=='POST')
	{
		if($_POST['holderid'] == $arParams["ID"])
		{
			$gdid = $_POST['gid'];
			$p = mb_strpos($gdid, "@");
			if($p === false)
			{
				$gadget_id = $gdid;
				$gdid = $gdid."@".rand();
			}
			else
			{
				$gadget_id = mb_substr($gdid, 0, $p);
			}

			$arGadget = BXGadget::GetById($gadget_id);
			if (
				$arGadget 
				&& (
					!is_array($arParams["GADGETS"]) 
					|| in_array($arGadget["ID"], $arParams["GADGETS"]) 
					|| in_array("ALL", $arParams["GADGETS"])
				)
			)
			{
				$arUserOptions = array();
				if (
					$_POST['action'] == 'add'
					|| $_POST['action'] == 'update'
				)
				{
					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);

					if (
						$arParams["MULTIPLE"] == "Y"
						&& array_key_exists($arParams["DESKTOP_PAGE"], $arUserOptions)
					)
					{
						$arUserOptionsTmp = $arUserOptions;
						$arUserOptions = $arUserOptions[$arParams["DESKTOP_PAGE"]];
					}

					if (!$arUserOptions && !$user_option_id)
					{
						if (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."index.php", SITE_DIR, "/")))
						{
							$tmp_desktop_id = "mainpage";
						}
						elseif (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."desktop.php", "/desktop.php")))
						{
							$tmp_desktop_id = "dashboard";
						}

						if ($tmp_desktop_id)
						{
							$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$tmp_desktop_id, false, false);
						}
					}
					if (!is_array($arUserOptions["GADGETS"]))
					{
						$arUserOptions["GADGETS"] = Array();
					}
				}

				if ($_POST['action'] == 'add')
				{
					foreach($arUserOptions["GADGETS"] as $tempid => $tempgadget)
					{
						$p = mb_strpos($tempid, "@");
						$gadget_id_tmp = ($p === false? $tempid : mb_substr($tempid, 0, $p));
			
						if ($gadget_id_tmp == $gadget_id)
						{
							$arGadget = BXGadget::GetById($gadget_id_tmp);
							if (
								array_key_exists("UNIQUE", $arGadget)
								&& $arGadget["UNIQUE"]
							)
							{
								$bUniqueGadgetAlreadyUsed = true;
							}
						}

						if($tempgadget["COLUMN"] == 0)
						{
							$arUserOptions["GADGETS"][$tempid]["ROW"]++;
						}
					}

					if (!$bUniqueGadgetAlreadyUsed)
					{
						$arUserOptions["GADGETS"][$gdid] = Array("COLUMN" => 0, "ROW" => 0);

						if ($arParams["MULTIPLE"] == "Y")
						{
							$arUserOptionsTmp[$arParams["DESKTOP_PAGE"]] = $arUserOptions;
							$arUserOptions = $arUserOptionsTmp;
						}
						CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, false, $user_option_id);
					}
					LocalRedirect($_SERVER['REQUEST_URI']);
				}
				elseif ($_POST['action'] == 'update')
				{
					$arUserOptions["GADGETS"][$gdid]["SETTINGS"] = $_POST["settings"];

					if ($arParams["MULTIPLE"] == "Y")
					{
						$arUserOptionsTmp[$arParams["DESKTOP_PAGE"]] = $arUserOptions;
						$arUserOptions = $arUserOptionsTmp;
					}

					CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, false, $user_option_id);

					LocalRedirect($_SERVER['REQUEST_URI']);
				}
			}
		}
	}
}

if($_REQUEST['gd_ajax']==$arParams["ID"])
{
	if($USER->IsAuthorized() && $arResult["PERMISSION"]>"R" && check_bitrix_sessid())
	{
		$APPLICATION->RestartBuffer();
		switch($_REQUEST['gd_ajax_action'])
		{
			case 'get_settings':
				$gdid = $_REQUEST['gid'];

				$p = mb_strpos($gdid, "@");
				if($p === false)
					break;

				$gadget_id = mb_substr($gdid, 0, $p);

				// closed by an admin
				if(is_array($arParams["GADGETS"]) && !in_array($gadget_id, $arParams["GADGETS"]) && !in_array("ALL", $arParams["GADGETS"]))
					break;

				// get user settings of the gadget
				$arGadget = BXGadget::GetById($gadget_id, true, $arParams);
				if($arGadget)
				{
					// get params values
					$arGadgetParams = $arGadget["USER_PARAMETERS"];
					foreach($arParams as $id=>$p)
					{
						$pref = "GU_".$gadget_id."_";
						if(mb_strpos($id, $pref) === 0 && is_set($arGadgetParams, mb_substr($id, mb_strlen($pref))))
							$arGadgetParams[mb_substr($id, mb_strlen($pref))]["VALUE"] = $p;
					}

					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);

					if (
						$arParams["MULTIPLE"] == "Y"
						&& array_key_exists($arParams["DESKTOP_PAGE"], $arUserOptions)
					)
					{
						$arUserOptionsTmp = $arUserOptions;
						$arUserOptions = $arUserOptions[$arParams["DESKTOP_PAGE"]];
					}

					if (!$arUserOptions && !$user_option_id)
					{
						if (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."index.php", SITE_DIR, "/")))
							$tmp_desktop_id = "mainpage";
						elseif (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."desktop.php", "/desktop.php")))
							$tmp_desktop_id = "dashboard";

						if ($tmp_desktop_id)
							$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$tmp_desktop_id, false, false);
					}

					if(is_array($arUserOptions) && is_array($arUserOptions["GADGETS"]) && is_array($arUserOptions["GADGETS"][$gdid]) && is_array($arUserOptions["GADGETS"][$gdid]["SETTINGS"]))
					{
						foreach($arUserOptions["GADGETS"][$gdid]["SETTINGS"] as $p=>$v)
							if(is_set($arGadgetParams, $p))
								$arGadgetParams[$p]["VALUE"] = $v;
					}

					echo CUtil::PhpToJSObject($arGadgetParams);
				}
				break;

			case 'clear_settings':
				CUserOptions::DeleteOption("intranet", "~gadgets_".$arParams["ID"], false, $user_option_id);
				if (
					in_array($arParams["MODE"], array("SU", "SG"))
					&& $arParams["DEFAULT_ID"] <> ''
				)
				{
					$arTmp = explode("_", $arParams["ID"]);
					if (count($arTmp) == 4)
					{
						$IDWOS = implode("_", array_merge(array_slice($arTmp, 0, 2), array($arTmp[3])));
						CUserOptions::DeleteOption("intranet", "~gadgets_".$IDWOS, false, 0);
					}
				}
				break;

			case 'save_default':
				GDCSaveSettings($arParams, $_REQUEST['POS']);

				if ($arResult["PERMISSION"] > "W")
				{
					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
					if (!$arUserOptions && !$user_option_id)
					{
						if (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."index.php", SITE_DIR, "/")))
							$tmp_desktop_id = "mainpage";
						elseif (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."desktop.php", "/desktop.php")))
							$tmp_desktop_id = "dashboard";

						if ($tmp_desktop_id)
							$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$tmp_desktop_id, false, false);
					}

					if (array_key_exists("DEFAULT_ID", $arParams) && trim($arParams["DEFAULT_ID"]) <> '')
						CUserOptions::SetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], $arUserOptions, false, 0);
					else
						CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, true);
				}
				break;

			case 'update_position':
				GDCSaveSettings($arParams, $_REQUEST['POS']);
				break;
		}
	}
	else
		echo GetMessage("CMDESKTOP_AUTH_ERR");
	die();
}

$arResult["GADGETS"] = Array();
$arResult["ID"] = $arParams["ID"];
$arParams["UPD_URL"] = $arResult["UPD_URL"] = POST_FORM_ACTION_URI;

$parts = explode("?", $arResult['UPD_URL'], 2);
if (count($parts) == 2)
{
	$string = $parts[0]."?";
	$arTmp = array();
	$params = explode("&", $parts[1]);
	foreach ($params as $param)
	{
		$tmp = explode("=", $param);
		if (count($tmp) == 2)
		{
			if ($tmp[0] != "logout")
				$arTmp[] = $param;
		}
		else
			$arTmp[] = $param;
	}
	$string .= implode("&", $arTmp);
	$arParams["UPD_URL"] = $arResult["UPD_URL"] = $string;
}

$arGDList = Array();

$arUserOptions = false;
if(($USER->IsAuthorized() && $arResult["PERMISSION"]>"R") || $user_option_id !== false)
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
else
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, 99999999);

if (!$arUserOptions)
{
	if (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."index.php", SITE_DIR, "/")))
		$tmp_desktop_id = "mainpage";
	elseif (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."desktop.php", "/desktop.php")))
		$tmp_desktop_id = "dashboard";

	if ($tmp_desktop_id)
	{
		if(($USER->IsAuthorized() && $arResult["PERMISSION"]>"R") || $user_option_id !== false)
			$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$tmp_desktop_id, $arUserOptionsDefault, $user_option_id);
		else
			$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$tmp_desktop_id, $arUserOptionsDefault, 99999999);
	}
}

$arResult["DESKTOPS"] = array();

if ($arParams["MULTIPLE"] == "Y")
{
	if (!is_array($arUserOptions) || !array_key_exists($arParams["DESKTOP_PAGE"], $arUserOptions))
		$arParams["DESKTOP_PAGE"] = 0;

	$arUserOptionsTmp = $arUserOptions;
	$arUserOptions = $arUserOptions[$arParams["DESKTOP_PAGE"]];

	if (is_array($arUserOptions))
		foreach($arUserOptionsTmp as $i => $arDesktop)
			$arResult["DESKTOPS"][] = array("NAME" => $arDesktop["NAME"]);
}

if (is_array($arUserOptions) && array_key_exists("COLS", $arUserOptions))
	$arResult["COLS"] = $arUserOptions["COLS"];

if	(intval($arResult["COLS"]) <= 0)
	$arResult["COLS"] = (
			intval($arParams["COLUMNS"])>0
			&& intval($arParams["COLUMNS"])<10
		)
		? intval($arParams["COLUMNS"])
		: 3;

if (
	is_array($arUserOptions)
	&& array_key_exists("arCOLUMN_WIDTH", $arUserOptions)
	&& is_array($arUserOptions["arCOLUMN_WIDTH"])
)
{
	for($i = 0, $intCount = count($arUserOptions["arCOLUMN_WIDTH"]); $i < $intCount; $i++)
		$arResult["COLUMN_WIDTH"][$i] = htmlspecialcharsbx($arUserOptions["arCOLUMN_WIDTH"][$i]);
}
else
{
	for($i = 0; $i < $arResult["COLS"]; $i++)
		$arResult["COLUMN_WIDTH"][$i] = htmlspecialcharsbx($arParams["COLUMN_WIDTH_".$i]);
}

if (
	is_array($arUserOptions)
	&& array_key_exists("NAME", $arUserOptions)
	&& $arUserOptions["NAME"] <> ''
)
{
	$arResult["DESKTOP_NAME"] = $arUserOptions["NAME"];
}

$arGroups = Array(
	"personal" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_PERSONAL"),
		"DESCRIPTION" =>GetMessage("CMDESKTOP_GROUP_PERSONAL_DESCR"),
		"GADGETS" => Array(),
	),
	"employees" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_EMPL"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_EMPL_DESCR"),
		"GADGETS" => Array(),
	),
	"communications" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_COMMUN"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_COMMUN_DESCR"),
		"GADGETS" => Array(),
	),
	"company" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_COMPANY"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_COMPANY_DESCR"),
		"GADGETS" => Array(),
	),
	"services" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_SERVICES"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_SERVICES_DESCR"),
		"GADGETS" => Array(),
	),
	"other" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_OTHER"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_OTHER_DESCR"),
		"GADGETS" => Array(),
	),
	"sonet" => Array(
		"NAME" => ($arParams["MODE"] == "SG" ? GetMessage("CMDESKTOP_GROUP_SONET_GROUP") : GetMessage("CMDESKTOP_GROUP_SONET_USER")),
		"DESCRIPTION" => ($arParams["MODE"] == "SG" ? GetMessage("CMDESKTOP_GROUP_SONET_GROUP_DESCR") : GetMessage("CMDESKTOP_GROUP_SONET_USER_DESCR")),
		"GADGETS" => Array(),
	),
	"admin_content" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_ADMIN_CONTENT"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_ADMIN_CONTENT_DESCR"),
		"GADGETS" => Array(),
	),
	"admin_services" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_ADMIN_SERVICES"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_ADMIN_SERVICES_DESCR"),
		"GADGETS" => Array(),
	),
	"admin_store" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_ADMIN_STORE"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_ADMIN_STORE_DESCR"),
		"GADGETS" => Array(),
	),
	"admin_statistics" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_ADMIN_STATISTICS"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_ADMIN_STATISTICS_DESCR"),
		"GADGETS" => Array(),
	),
	"admin_settings" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_ADMIN_SETTINGS"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_ADMIN_SETTINGS_DESCR"),
		"GADGETS" => Array(),
	),
	"crm" => Array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_CRM"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_CRM_DESCR"),
		"GADGETS" => Array(),
	)
);

foreach (GetModuleEvents("main", "OnFillGadgetGroups", true) as $arEvent)
{
	ExecuteModuleEventEx($arEvent, array(&$arGroups));
}
	
$arResult["ALL_GADGETS"] = Array();
$arGadgets = BXGadget::GetList();
foreach($arGadgets as $gadget)
{
	// skip if prohibited by settings
	if(is_array($arParams["GADGETS"]) && !in_array($gadget["ID"], $arParams["GADGETS"]) && !in_array("ALL", $arParams["GADGETS"]))
		continue;

	if ($arParams["MODE"] != "SU" && $arParams["MODE"] != "SG" && ($gadget["SU_ONLY"] == true || $gadget["SG_ONLY"] == true))
		continue;
	if ($gadget["OO_ONLY"] == true && !$USER->CanDoOperation('view_other_settings'))
		continue;
	if ($arParams["MODE"] != "AI" && $gadget["AI_ONLY"] == true)
		continue;

	if ($arParams["MODE"] == "SU" && $gadget["SU_ONLY"] != true && $gadget["SU"] != true)
		continue;

	if ($arParams["MODE"] == "SG" && $gadget["SG_ONLY"] != true && $gadget["SG"] != true)
		continue;

	if ($arParams["MODE"] == "AI" && $gadget["AI_ONLY"] != true && $gadget["AI"] != true)
		continue;

	if ($gadget["DISABLED"] === true)
	{
		continue;
	}
	if ($gadget["EXTRANET_ONLY"] == true && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()))
		continue;
	if ($gadget["SEARCH_ONLY"] == true && !IsModuleInstalled("search"))
		continue;
	if ($gadget["FORUM_ONLY"] == true && !IsModuleInstalled("forum"))
		continue;
	if ($gadget["BLOG_ONLY"] == true && !IsModuleInstalled("blog"))
		continue;
	if ($gadget["PHOTOGALLERY_ONLY"] == true && !IsModuleInstalled("photogallery"))
		continue;
	if ($gadget["WEBDAV_ONLY"] == true && !IsModuleInstalled("webdav"))
		continue;
	if ($gadget["DISK_ONLY"] == true && !IsModuleInstalled("disk"))
		continue;
	if ($gadget["SALE_ONLY"] == true && !IsModuleInstalled("sale"))
		continue;
	if ($gadget["SALE_ONLY"] == true && $gadget["AI_ONLY"] == true && $APPLICATION->GetGroupRight("sale") == "D")
		continue;
	if ($gadget["STATISTIC_ONLY"] == true && !IsModuleInstalled("statistic"))
		continue;
	if ($gadget["STATISTIC_ONLY"] == true && $gadget["AI_ONLY"] == true && $APPLICATION->GetGroupRight("statistic") == "D")
		continue;
	if ($gadget["IBLOCK_ONLY"] == true && !IsModuleInstalled("iblock"))
		continue;
	if ($gadget["LANGUAGE_ONLY_RU"] == true && LANGUAGE_ID != "ru")
		continue;
	if ($gadget["IBLOCK_ONLY"] == true && $gadget["AI_ONLY"] == true)
	{
		if(CModule::IncludeModule('iblock'))
		{
			$dbIBlock = CIBlock::GetList(Array(), array("MIN_PERMISSION" => (IsModuleInstalled("workflow")?"U":"W")));
			$arIBlock = $dbIBlock->Fetch();
		}
		else
			$arIBlock = false;

		if (!$arIBlock)
			continue;
	}
	if (
		$gadget["SUPPORT_ONLY"] == true
		&&
		(
			!CModule::IncludeModule("support")
			|| !$USER->IsAuthorized()
			|| (!CTicket::IsSupportClient() && !CTicket::IsAdmin() && !CTicket::IsSupportTeam())
		)
	)
		continue;
	if ($gadget["WIKI_ONLY"] == true && !IsModuleInstalled("wiki"))
		continue;
	if ($gadget["CRM_ONLY"] == true && !IsModuleInstalled("crm"))
		continue;
	if ($gadget["VOTE_ONLY"] == true && (!IsModuleInstalled("vote") || !CBXFeatures::IsFeatureEnabled("Vote")))
		continue;
	if ($gadget["TASKS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Tasks"))
		continue;
	if ($gadget["MESSENGER_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("WebMessenger"))
		continue;
	if ($gadget["ABSENCE_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("StaffAbsence"))
		continue;
	if ($gadget["STAFF_CHANGES_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("StaffChanges"))
		continue;
	if ($gadget["COMMON_DOCS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CommonDocuments"))
		continue;
	if ($gadget["COMPANY_PHOTO_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CompanyPhoto"))
		continue;
	if ($gadget["COMPANY_CALENDAR_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CompanyCalendar"))
		continue;
	if ($gadget["CALENDAR_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Calendar"))
		continue;
	if ($gadget["COMPANY_VIDEO_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CompanyVideo"))
		continue;
	if ($gadget["WORKGROUPS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Workgroups"))
		continue;
	if ($gadget["FRIENDS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Friends"))
		continue;

	if ($USER->IsAuthorized() && $arResult["PERMISSION"] < "W" && $gadget["SELF_PROFILE_ONLY"] == true && $arParams["MODE"] == "SU" && intval($arParams["USER_ID"]) > 0 && $arParams["USER_ID"] != $USER->GetID())
		continue;

	if ($gadget["BLOG_ONLY"] == true && $gadget["SU_ONLY"] == true && intval($arParams["USER_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog"))
		continue;

	if ($gadget["BLOG_ONLY"] == true && $gadget["SG_ONLY"] == true && intval($arParams["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog"))
		continue;

	if ($gadget["FORUM_ONLY"] == true && $gadget["SU_ONLY"] == true && intval($arParams["USER_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum"))
		continue;

	if ($gadget["FORUM_ONLY"] == true && $gadget["SG_ONLY"] == true && intval($arParams["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum"))
		continue;

	if ($gadget["SEARCH_ONLY"] == true && $gadget["SU_ONLY"] == true && intval($arParams["USER_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "search"))
		continue;

	if ($gadget["SEARCH_ONLY"] == true && $gadget["SG_ONLY"] == true && intval($arParams["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "search"))
		continue;

	if (
		$gadget["WIKI_ONLY"] == true 
		&& $gadget["SG_ONLY"] == true 
		&& intval($arParams["SOCNET_GROUP_ID"]) > 0 
		&& CModule::IncludeModule('socialnetwork') 
		&& (
			!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "wiki")
			|| !CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "wiki", "view", CSocNetUser::IsCurrentUserModuleAdmin())
		)
	)
	{
		continue;
	}

	if($gadget["GROUP"]["ID"] == "")
	{
		$gadget["GROUP"]["ID"] = "other";
	}

	if (
		!isset($gadget["TOTALLY_FIXED"]) 
		|| !$gadget["TOTALLY_FIXED"]
	)
	{
		if (!is_array($gadget["GROUP"]["ID"]))
		{
			$arGroups[$gadget["GROUP"]["ID"]]["GADGETS"][] = $gadget["ID"];
		}
		else
		{
			foreach($gadget["GROUP"]["ID"] as $group_id)
			{
				if (
					(
						in_array($arParams["MODE"], array("SU", "SG"))
						&& $group_id != "sonet"
					)
					|| (
						!in_array($arParams["MODE"], array("SU", "SG"))
						&& $group_id == "sonet"
					)
					|| (
						$arParams["MODE"] == "AI" 
						&& $group_id != "admin"
					)
					|| (
						$arParams["MODE"] != "AI" 
						&& $group_id == "admin"
					)
				)
				{
					continue;
				}

				$arGroups[$group_id]["GADGETS"][] = $gadget["ID"];
			}
		}
	}

	$arResult["ALL_GADGETS"][$gadget['ID']] = $gadget;
}

$arResult["GROUPS"] = Array();
foreach($arGroups as $arGroup)
{
	if(count($arGroup['GADGETS'])>0)
	{
		$arResult['GROUPS'][] = $arGroup;
	}
}

$arResult["GADGETS"] = Array();
$arResult["GADGETS_LIST"] = Array();
for($i=0; $i<$arResult["COLS"]; $i++)
	$arResult["GADGETS"][$i] = Array();

// saved layout
if(is_array($arUserOptions))
{
	$bForceRedirect = false;
	if (array_key_exists("GADGETS", $arUserOptions) && is_array($arUserOptions["GADGETS"]))
	{
		foreach($arUserOptions["GADGETS"] as $gdid=>$gadgetUserSettings)
		{
			$gadgetUserSettings = $arUserOptions["GADGETS"][$gdid];

			$p = mb_strpos($gdid, "@");
			if($p === false)
			{
				$gadget_id = $gdid;
				$gdid = $gdid."@".rand();
			}
			else
			{
				$gadget_id = mb_substr($gdid, 0, $p);
			}

			if($arResult["ALL_GADGETS"][$gadget_id])
			{
				$arGadgetParams = $gadgetUserSettings["SETTINGS"] ?? [];

				$arGadget = $arResult["ALL_GADGETS"][$gadget_id];
				foreach($arParams as $id=>$p)
				{
					$pref = "G_".$gadget_id."_";
					if(mb_strpos($id, $pref) === 0)
						$arGadgetParams[mb_substr($id, mb_strlen($pref))]=$p;

					$pref = "GU_".$gadget_id."_";
					if(mb_strpos($id, $pref) === 0 && !isset($arGadgetParams[mb_substr($id, mb_strlen($pref))]))
						$arGadgetParams[mb_substr($id, mb_strlen($pref))]=$p;
				}

				if(intval($gadgetUserSettings["COLUMN"])<=0 || intval($gadgetUserSettings["COLUMN"])>=$arResult["COLS"])
				{
					$gadgetUserSettings["COLUMN"] = 0;
				}

				$arGCol = &$arResult["GADGETS"][$gadgetUserSettings["COLUMN"]];

				if(isset($arGCol[$gadgetUserSettings["ROW"]]))
				{
					ksort($arGCol, SORT_NUMERIC);
					$ks = array_keys($arGCol);
					$gadgetUserSettings["ROW"] = $ks[count($ks)-1] + 1;
				}

				$arGadget["ID"] = $gdid;
				$arGadget["GADGET_ID"] = $arResult["GADGETS_LIST"][] = $gadget_id;
				$arGadget["TITLE"] = htmlspecialcharsbx($arGadget["NAME"]);
				$arGadget["SETTINGS"] = $arGadgetParams;

				if (
					is_array($arGadgetParams)
					&& array_key_exists("TITLE_STD", $arGadgetParams)
					&& $arGadgetParams["TITLE_STD"] <> ''
				)
				{
					$arGadget["TITLE"] = htmlspecialcharsbx($arGadgetParams["TITLE_STD"]);
				}

				$arGadget["HIDE"] = $gadgetUserSettings["HIDE"];
				if($arParams["PERMISSION"]>"R")
					$arGadget["USERDATA"] = &$arUserOptions["GADGETS"][$gdid]["USERDATA"];
				else
					$arGadget["USERDATA"] = $arUserOptions["GADGETS"][$gdid]["USERDATA"];
				$arGadget["CONTENT"] = BXGadget::GetGadgetContent($arGadget, $arParams);
				$arResult["GADGETS"][$gadgetUserSettings["COLUMN"]][$gadgetUserSettings["ROW"]] = $arGadget;

				if(isset($arGadget["FORCE_REDIRECT"]) && $arGadget["FORCE_REDIRECT"])
				{
					$bForceRedirect = true;
				}
			}
			else
			{
				unset($arUserOptions["GADGETS"][$gdid]);
			}
		}
	}

	for($i=0; $i<$arResult["COLS"]; $i++)
		ksort($arResult["GADGETS"][$i], SORT_NUMERIC);

	$arResult["GADGETS_LIST"] = array_unique($arResult["GADGETS_LIST"]);

	if($bForceRedirect)
	{
		if ($arParams["MULTIPLE"] == "Y")
		{
			$arUserOptionsTmp[$arParams["DESKTOP_PAGE"]] = $arUserOptions;
			$arUserOptions = $arUserOptionsTmp;
		}
		CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, false, $user_option_id);
		LocalRedirect($APPLICATION->GetCurPageParam(($arParams["MULTIPLE"]=="Y"?"dt_page=".$arParams["DESKTOP_PAGE"]:""), array("dt_page")));
	}
}

$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/popup_menu.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/ajax.js');

CUtil::InitJSCore(array("ajax"));

$this->IncludeComponentTemplate();
