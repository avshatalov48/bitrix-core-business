<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!IsModuleInstalled("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["USER_ID"] = intVal(intVal($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $_REQUEST["USER_ID"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);

	$arParams["SORT_BY"] = (!empty($arParams["SORT_BY"]) ? $arParams["SORT_BY"] : "ID");
	$arParams["SORT_ORD"] = ($arParams["SORT_ORD"] != "ASC" ? "DESC" : "ASC");
/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
	"index" => "",
	"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
	"gallery_edit" => "PAGE_NAME=gallery_edit&USER_ALIAS=#USER_ALIAS#&ACTION=#ACTION#",
	"section" => "PAGE_NAME=section&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#",
	"upload" => "PAGE_NAME=upload&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#&ACTION=upload"
);

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE,
			array("PAGE_NAME", "USER_ALIAS", "GALLERY_ID", "ACTION", "AJAX_CALL", "USER_ID", "sessid", "save", "login", "order", "group_by"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
}
/***************** ADDITIONAL **************************************/
	$arParams["ONLY_ONE_GALLERY"] = ($arParams["ONLY_ONE_GALLERY"] == "N" ? "N" : "Y");
	$arParams["GALLERY_GROUPS"] = (is_array($arParams["GALLERY_GROUPS"]) ? $arParams["GALLERY_GROUPS"] : array());
	$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"]);
	$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 5);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
	$arParams["SECTION_SELECT_FIELDS"] = (is_array($arParams["SECTION_SELECT_FIELDS"]) ? $arParams["SECTION_SELECT_FIELDS"] : array());
	$arParams["SECTION_FILTER"] = (is_array($arParams["SECTION_FILTER"]) ? $arParams["SECTION_FILTER"] : array()); // hidden params
	$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");
/***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/

$arResult["USER"] = array();
$arResult["USERS"] = array();
$arResult["GALLERIES"] = array();

/********************************************************************
				USER Not from cache (!important)
********************************************************************/
if ($arParams["USER_ID"] > 0)
{
	$db_res = CUser::GetByID($arParams["USER_ID"]);
	if (!($db_res && $arResult["USER"] = $db_res->GetNext()))
	{
		ShowError(GetMessage("P_USER_NOT_FOUND"));
		if ($arParams["SET_STATUS_404"] == "Y")
			CHTTP::SetStatus("404 Not Found");
		return 0;
	}
	else
	{
		$arResult["USER"]["SHOW_NAME"] = trim($arResult["USER"]["NAME"]." ".$arResult["USER"]["LAST_NAME"]);
		if (empty($arResult["USER"]["SHOW_NAME"]))
			$arResult["USER"]["SHOW_NAME"] = $arResult["USER"]["LOGIN"];
	}
}
/********************************************************************
				Main Data
********************************************************************/
$cache = new CPHPCache;
$cache_path = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["IBLOCK_ID"]);

/************** PERMISSION *****************************************/
$cache_id = "permission".serialize(array(
	"USER_GROUP" => $GLOBALS["USER"]->GetGroups(),
	"IBLOCK_ID" => $arParams["IBLOCK_ID"]
));
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$arParams["PERMISSION"] = $cache->GetVars();
}
else
{
	CModule::IncludeModule("iblock");
	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache($arParams["PERMISSION"]);
	}
}
$arParams["ABS_PERMISSION"] = $arParams["PERMISSION"];
$arParams["PERMISSION"] = (!empty($arParams["PERMISSION_EXTERNAL"]) ? $arParams["PERMISSION_EXTERNAL"] : $arParams["PERMISSION"]);
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && $arParams["USER_ID"] == $GLOBALS["USER"]->GetId())
	$arParams["PERMISSION"] = "W";
elseif ($arParams["PERMISSION"] < "R")
	return ShowError(GetMessage("P_DENIED_ACCESS"));

/************** GALLERIES ******************************************/
//PAGENAVIGATION
$arNavParams = false; $arNavigation = false;
if ($arParams["PAGE_ELEMENTS"] > 0)
{
	$arNavParams = array("nPageSize" => $arParams["PAGE_ELEMENTS"], "bShowAll" => false);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
}

//CACHE
$cache_id = "gallerylist".serialize(array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"USER_ID" => $arParams["USER_ID"],
	"SECTION_FILTER" => $arParams["SECTION_FILTER"],
	"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
	"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
	"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
	"PERMISSION" => $arParams["PERMISSION"],
	"NAV1" => $arNavParams,
	"NAV2" => $arNavigation
));

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arResult["GALLERIES"] = $res["GALLERIES"];
	$arResult["NAV_STRING"] = $res["NAV_STRING"];
	$arResult["NAV_RESULT"] = $res["NAV_RESULT"];
	$GLOBALS['NavNum'] = intVal($GLOBALS['NavNum']) + 1;
}
else
{
	CModule::IncludeModule("iblock");
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE" => "Y",
		"SECTION_ID" => 0
	);

	if ($arParams["USER_ID"] > 0)
		$arFilter["CREATED_BY"] = $arParams["USER_ID"];

	if (!empty($arParams["SECTION_FILTER"]))
	{
		if ($arParams["SECTION_FILTER"][">ELEMENTS_CNT"] == 0)
		{
			unset($arParams["SECTION_FILTER"][">ELEMENTS_CNT"]);
			$arFilter[">UF_GALLERY_SIZE"] = "0";
		}
		$arFilter = $arFilter + $arParams["SECTION_FILTER"];
	}

	$arSelect = array("ID", "CODE", "NAME", "CREATED_BY", "RIGHT_MARGIN", "LEFT_MARGIN", "PICTURE", "UF_GALLERY_SIZE", "UF_DEFAULT",  "UF_GALLERY_RECALC", "UF_DATE", "SOCNET_GROUP_ID");
	$db_res = CIBlockSection::GetList(
		array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"),
		$arFilter,
		false,
		$arSelect
	);

	if ($db_res)
	{


		if ($arParams["PAGE_ELEMENTS"] > 0)
		{
			$db_res->NavStart($arParams["PAGE_ELEMENTS"], false);
			$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
			$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("P_GALLERIES"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
			$arResult["NAV_RESULT"] = $db_res;
		}

		while ($res = $db_res->GetNext())
		{
			if (preg_match("/[^a-z0-9_]/is", $res["~CODE"]))
				$res["CODE"] = "";

			if ($arParams["SHOW_PHOTO_USER"] == "Y")
			{
				if (empty($arResult["USERS"][$res["CREATED_BY"]]))
				{
					$db_user = CUser::GetByID($res["CREATED_BY"]);
					$res_user = $db_user->Fetch();
					$arResult["USER"][$res_user["ID"]] = $res_user;
				}
				$res["PICTURE"] = intVal($arResult["USER"][$res["CREATED_BY"]]["PERSONAL_PHOTO"]);
				$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
				$image_resize = CFile::ResizeImageGet($res["PICTURE"],
					array("width" => $arParams["GALLERY_AVATAR_SIZE"], "height" => $arParams["GALLERY_AVATAR_SIZE"]));
				$res["PICTURE"]["SRC"] = $image_resize["src"];
			}
			else
			{
				$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
			}
			$res["ALBUMS"] = array();
			if (doubleval($res["UF_GALLERY_SIZE"]) > 0 && in_array("ALBUMS", $arParams["SECTION_SELECT_FIELDS"]))
			{
				$db_res2 = CIBlockSection::GetList(
					array("ID" => "DESC"),
					array("ACTIVE" => "Y", "SECTION_ID" => $res["ID"]),
					false,
					array("ID", "NAME", "DESCRIPTION", "PICTURE", "UF_PASSWORD"));
				if ($db_res2 && $res2 = $db_res2->GetNext())
				{
					$iCount = 1;
					do
					{
						$res2["PASSWORD"] = $res2["UF_PASSWORD"];
						$res2["~PASSWORD"] = $res2["~UF_PASSWORD"];

						$res2["URL"] = array(
							"VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
								array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => $res2["ID"],
									"USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])));
						if (!empty($res2["PICTURE"]))
						{
							$res2["~PICTURE"] = $res2["PICTURE"];
							$res2["PICTURE"] = CFile::GetFileArray($res2["PICTURE"]);
						}
						$res["ALBUMS"][$res2["ID"]] = $res2;
						$iCount++;
						if ($iCount > 2)
							break;
					} while ($res2 = $db_res2->GetNext());
				}
			}

			$res["LINK"] = array(
				"VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
					array("USER_ALIAS" => $res["~CODE"], "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
				"EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
					array("USER_ALIAS" => $res["~CODE"], "ACTION" => "EDIT",
						"USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
				"DROP" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
					array("USER_ALIAS" => $res["~CODE"], "ACTION" => "DROP",
						"USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
				"UPLOAD" => CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
					array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0",
						"USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])));

			$res["LINK"]["EDIT"] .= (strpos($res["LINK"]["EDIT"], "?") === false ? "?" : "&")."GALLERY_ID=".$res["ID"];
			$res["LINK"]["DROP"] .= (strpos($res["LINK"]["DROP"], "?") === false ? "?" : "&")."GALLERY_ID=".$res["ID"];
			foreach ($res["LINK"] as $key => $val)
			{
				$res["LINK"]["~".$key] = $val;
				$res["LINK"][$key] = htmlspecialcharsbx($val);
			}
			$arResult["GALLERIES"][$res["ID"]] = $res;
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array(
				"GALLERIES" => $arResult["GALLERIES"],
				"NAV_STRING" => $arResult["NAV_STRING"],
				"NAV_RESULT" => $arResult["NAV_RESULT"]
			));
		}
	}
}

/********************************************************************
				/Main Data
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** GALLERIES ******************************************/
$arResult["GALLERIES"] = (!is_array($arResult["GALLERIES"]) ? array() : $arResult["GALLERIES"]);
if ($arParams["PERMISSION"] >= "U")
{
	foreach ($arResult["GALLERIES"] as $key => $res)
	{
		$arResult["GALLERIES"][$key]["LINK"]["~DROP"] .= "&".bitrix_sessid_get();
		$arResult["GALLERIES"][$key]["LINK"]["DROP"] = htmlspecialcharsbx($arResult["GALLERIES"][$key]["LINK"]["~DROP"]);
	}
}
/************** PERMISSION *****************************************/
$arResult["I"] = array(
	"ACTIONS" => array(
		"CREATE_GALLERY" => "N",
		"EDIT_GALLERY" => "N",
		"UPLOAD" => "N"),
	"PERMISSION" => $arParams["PERMISSION"],
	"ABS_PERMISSION" => $arParams["ABS_PERMISSION"]);
if (!$GLOBALS["USER"]->IsAuthorized() || $arParams["PERMISSION"] < "U")
{
	// no changes
}
elseif ($arParams["ABS_PERMISSION"] >= "U")
{
	$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "Y";
	$arResult["I"]["ACTIONS"]["EDIT_GALLERY"] = "Y";
	$arResult["I"]["ACTIONS"]["UPLOAD"] = "Y";
}
elseif ($GLOBALS["USER"]->GetID() == $arParams["USER_ID"])
{
	if ($arParams["ONLY_ONE_GALLERY"] == "Y" && !empty($arResult["GALLERIES"])):
		$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "N";
	else:
		$res = array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["GALLERY_GROUPS"]);
		$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = (empty($res) ? "N" : "Y");
	endif;
	$arResult["I"]["ACTIONS"]["EDIT_GALLERY"] = "Y";
	$arResult["I"]["ACTIONS"]["UPLOAD"] = "Y";
}
/************** URLS ***********************************************/
$arResult["LINK"] = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array()),
	"NEW" => CComponentEngine::MakePathFromTemplate($arParams["GALLERY_EDIT_URL"], array("USER_ALIAS" => "NEW_ALIAS", "ACTION" => "CREATE",
		"USER_ID" => $USER->GetID(), "GROUP_ID" => 0)),
	"GALLERIES" =>  CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => $arParams["USER_ID"])));
/********************************************************************
				/Data
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y")
{
	$sTitle = ($arParams["USER_ID"] > 0 ? GetMessage("P_GALLERIES_BY_USER")." ".$arResult["USER"]["SHOW_NAME"] : GetMessage("P_GALLERIES"));
	$GLOBALS['APPLICATION']->SetTitle($sTitle);
}
if ($arParams["SET_NAV_CHAIN"] == "Y")
{
	$sTitle = ($arParams["USER_ID"] > 0 ? $arResult["USER"]["SHOW_NAME"] : GetMessage("P_GALLERIES"));
	$GLOBALS['APPLICATION']->AddChainItem($sTitle);
}
/************** Admin Panel ****************************************/
// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
	// CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
/************** Returns ********************************************/
return $arResult;
/********************************************************************
				/Standart
********************************************************************/
?>