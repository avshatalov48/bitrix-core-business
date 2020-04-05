<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!IsModuleInstalled("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["ANALIZE_SOCNET_PERMISSION"] = ($arParams["ANALIZE_SOCNET_PERMISSION"] == "Y" ? "Y" : "N");

	$arParams["SORT_BY"] = (!empty($arParams["SORT_BY"]) ? $arParams["SORT_BY"] : "ID");
	$arParams["SORT_ORD"] = ($arParams["SORT_ORD"] != "ASC" ? "DESC" : "ASC");
/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"galleries" => "PAGE_NAME=galleries&USER_ID=#USER_ID#",
		"gallery_edit" => "PAGE_NAME=gallery_edit&USER_ALIAS=#USER_ALIAS#&ACTION=#ACTION#",
		"section_edit" => "PAGE_NAME=section_edit&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"upload" => "PAGE_NAME=upload&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#&ACTION=upload");

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
}
/***************** ADDITIONAL **************************************/
	$arParams["ONLY_ONE_GALLERY"] = ($arParams["ONLY_ONE_GALLERY"] == "N" ? "N" : "Y");
	$arParams["GALLERY_GROUPS"] = (is_array($arParams["GALLERY_GROUPS"]) ? $arParams["GALLERY_GROUPS"] : array());
	$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"])*1024*1024;
	$arParams["RETURN_ARRAY"] = ($arParams["RETURN_ARRAY"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
/***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"]=="Y"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
if ((empty($arParams["USER_ALIAS"]) || $arParams["USER_ALIAS"] == "empty") && $arParams["SECTION_ID"] > 0)
{
	CModule::IncludeModule("photogallery");
	$oPhoto = new CPGalleryInterface(
		array(
			"IBlockID" => $arParams["IBLOCK_ID"],
			"GalleryID" => $arParams["USER_ALIAS"],
			"Permission" => $arParams["PERMISSION_EXTERNAL"]),
		array(
			"cache_time" => $arParams["CACHE_TIME"],
			"set_404" => $arParams["SET_STATUS_404"]
			)
		);
	$oPhoto->Gallery = ($oPhoto->Gallery ? $oPhoto->Gallery : array("CODE" => "empty"));
	$res = $oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]);

	if (intval($res) < 400 && intval($res) > 300)
	{
		$arGallery = $oPhoto->GetSectionGallery($arResult["SECTION"]);
		if ($arGallery)
		{
			$arParams["USER_ALIAS"] = $arGallery["CODE"];
			$oPhoto->Gallery = $arGallery;
		}
	}
}
/********************************************************************
				Main Data
********************************************************************/
$arResult["GALLERY"] = array();
$arResult["backurl_encode"] = urlencode($GLOBALS['APPLICATION']->GetCurPageParam());
$arResult["MY_GALLERY"] = array();
$arResult["MY_GALLERIES"] = array();
$arResult["USERS"] = array();
$cache = new CPHPCache;
$cache_path_main = "/".SITE_ID."/photogallery/".$arParams["IBLOCK_ID"];

/************** MY GALLERIES ***************************************/
if ($GLOBALS["USER"]->IsAuthorized())
{
	$cache_id = "gallerylist_".serialize(array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ID" => $GLOBALS["USER"]->GetId()
	));

	if (!empty($arParams["USER_ALIAS"]))
		$cache_id .= '_'.$arParams["USER_ALIAS"];

	$cache_path = $cache_path_main."/user".$GLOBALS["USER"]->GetId();
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["MY_GALLERY"] = $res["MY_GALLERY"];
		$arResult["MY_GALLERIES"] = $res["MY_GALLERIES"];
	}
	else
	{
		CModule::IncludeModule("iblock");
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"CREATED_BY" => $GLOBALS["USER"]->GetId(),
			"SOCNET_GROUP_ID" => false,
			"SECTION_ID" => 0
		);
		$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"),
			$arFilter, false, array("ID", "ACTIVE", "CREATED_BY", "IBLOCK_SECTION_ID", "NAME", "PICTURE", "DESCRIPTION", "DESCRIPTION_TYPE", "CODE", "SOCNET_GROUP_ID", "PICTURE","UF_DEFAULT", "UF_GALLERY_SIZE", "UF_GALLERY_RECALC", "UF_DATE"));

		if ($db_res)
		{
			while ($res = $db_res->GetNext())
			{
				if (@preg_match("/[^a-z0-9_]/is", $res["~CODE"]))
					$res["CODE"] = "";
//				$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], Array("CNT_ALL"=>"Y")));
				if ($arParams["SHOW_PHOTO_USER"] == "Y")
				{
					if (empty($arResult["USERS"][$res["CREATED_BY"]]))
					{
						$db_user = CUser::GetByID($res["CREATED_BY"]);
						$res_user = $db_user->Fetch();
						$arResult["USERS"][$res_user["ID"]] = $res_user;
					}
					$res["PICTURE"] = intVal($arResult["USERS"][$res["CREATED_BY"]]["PERSONAL_PHOTO"]);
				}
				$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
				if ($arParams["SHOW_PHOTO_USER"] == "Y" && !empty($res["PICTURE"]))
				{
					$image_resize = CFile::ResizeImageGet($res["PICTURE"],
						array("width" => $arParams["GALLERY_AVATAR_SIZE"], "height" => $arParams["GALLERY_AVATAR_SIZE"]));
					$res["PICTURE"]["SRC"] = $image_resize["src"];
				}

				$res["LINK"] = array();
				$url = array(
					"VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
						array("USER_ALIAS" => $res["~CODE"], "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
					"EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
						array("USER_ALIAS" => $res["~CODE"], "ACTION" => "EDIT", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
					"DROP" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
						array("USER_ALIAS" => $res["~CODE"], "ACTION" => "DROP", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
					"NEW_ALBUM" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
						array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"], "ACTION" => "new")),
					"NEW" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
						array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"], "ACTION" => "new")),
					"UPLOAD" => CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
						array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
						);
				foreach ($url as $key => $val):
					$res["LINK"][$key] = htmlspecialcharsbx($val);
					$res["LINK"]["~".$key] = $val;
				endforeach;

				if ($res["UF_DEFAULT"] == "Y")
					$arResult["MY_GALLERY"] = $res;
				$arResult["MY_GALLERIES"][$res["~CODE"]] = $res;
			}
			if (empty($arResult["MY_GALLERY"]))
			{
				reset($arResult["MY_GALLERIES"]);
				$arResult["MY_GALLERY"] = current($arResult["MY_GALLERIES"]);
			}
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(
				array(
					"MY_GALLERY" => $arResult["MY_GALLERY"],
					"MY_GALLERIES" => $arResult["MY_GALLERIES"])
				);
		}
	}
}

/************** GALLERY ********************************************/
if (!empty($arParams["USER_ALIAS"]))
{
	$cache_id = 'gallery_user_alias_'.serialize(array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $arParams["USER_ALIAS"]
	));

	$cache_path = $cache_path_main."/gallery".$arParams["USER_ALIAS"];
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["GALLERY"] = $res["GALLERY"];
	}
	elseif (!empty($arResult["MY_GALLERIES"]) && !empty($arResult["MY_GALLERIES"][$arParams["USER_ALIAS"]]))
	{
		$arResult["GALLERY"] = $arResult["MY_GALLERIES"][$arParams["USER_ALIAS"]];
	}
	else
	{
		CModule::IncludeModule("iblock");
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => 0,
			"CODE" => $arParams["USER_ALIAS"]
		);

		$db_res = CIBlockSection::GetList(
			array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"),
			$arFilter,
			false,
			array(
				"ID",
				"CREATED_BY",
				"ACTIVE",
				"IBLOCK_SECTION_ID",
				"NAME",
				"PICTURE",
				"DESCRIPTION",
				"DESCRIPTION_TYPE",
				"CODE",
				"SOCNET_GROUP_ID",
				"PICTURE",
				"UF_DEFAULT",
				"UF_GALLERY_SIZE",
				"UF_GALLERY_RECALC",
				"UF_DATE"
			)
		);

		if ($res = $db_res->GetNext())
		{
//			$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], array("CNT_ALL"=>"Y")));
			if ($arParams["SHOW_PHOTO_USER"] == "Y")
			{
				if (empty($arResult["USERS"][$res["CREATED_BY"]]))
				{
					$db_user = CUser::GetByID($res["CREATED_BY"]);
					$res_user = $db_user->Fetch();
					$arResult["USERS"][$res_user["ID"]] = $res_user;
				}
				$res["PICTURE"] = intVal($arResult["USERS"][$res["CREATED_BY"]]["PERSONAL_PHOTO"]);

				if ($res["PICTURE"] > 0)
				{
					$image_resize = CFile::ResizeImageGet(	$res["PICTURE"],
						array(
							"width" => $arParams["GALLERY_AVATAR_SIZE"],
							"height" => $arParams["GALLERY_AVATAR_SIZE"]
						)
					);
					$res["PICTURE"] = array("SRC" => $image_resize["src"]);
				}
			}
			elseif ($res["PICTURE"] > 0)
			{
				$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
			}

			$url = array(
				"VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
					array("USER_ALIAS" => $res["~CODE"], "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
				"EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
					array("USER_ALIAS" => $res["~CODE"], "ACTION" => "EDIT", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
				"DROP" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
					array("USER_ALIAS" => $res["~CODE"], "ACTION" => "DROP", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
				"NEW_ALBUM" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
					array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"], "ACTION" => "new")),
				"NEW" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
					array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"], "ACTION" => "new")),
				"UPLOAD" => CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
					array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0", "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])));
			$res["LINK"] = array();
			foreach ($url as $key => $val):
				$res["LINK"][$key] = htmlspecialcharsbx($val);
				$res["LINK"]["~".$key] = $val;
			endforeach;
			$arResult["GALLERY"] = $res;
		}

		if (!empty($arResult["GALLERY"]) && $arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("GALLERY" => $arResult["GALLERY"]));
		}
	}
}

/************** PERMISSION *****************************************/
$cache_id = serialize(array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"USER_GROUPS" => $GLOBALS["USER"]->GetGroups()
));
$cache_path = $cache_path_main."/permission";
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
if (!empty($arParams["PERMISSION_EXTERNAL"])):
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
elseif ($arParams["PERMISSION"] < "R"):
	// empty block
elseif ($arParams["ANALIZE_SOCNET_PERMISSION"] == "Y" && !empty($arResult["GALLERY"]) && CModule::IncludeModule("socialnetwork")):
	if (intVal($arResult["GALLERY"]["SOCNET_GROUP_ID"]) > 0)
	{
		if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["GALLERY"]["SOCNET_GROUP_ID"], "photo", "write"))
			$arParams["PERMISSION"]	= "W";
		elseif (!CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP,
				$arResult["GALLERY"]["SOCNET_GROUP_ID"], "photo", "view"))
			$arParams["PERMISSION"]	= "D";
	}
	else
	{
		if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["GALLERY"]["CREATED_BY"], "photo", "write"))
			$arParams["PERMISSION"]	= "W";
		elseif (!CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER,
				$arResult["GALLERY"]["CREATED_BY"], "photo", "view"))
			$arParams["PERMISSION"]	= "D";
	}
endif;

if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return false;
elseif (!empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ACTIVE"] != "Y"):
	ShowError(GetMessage("P_GALLERY_IS_BLOCKED"));
	if ($arParams["ABS_PERMISSION"] < "W"):
		if ($arParams["SET_TITLE"] == "Y")
			$GLOBALS["APPLICATION"]->SetTitle($arResult["GALLERY"]["NAME"]);
		if ($arParams["SET_NAV_CHAIN"] == "Y")
			$APPLICATION->AddChainItem($arResult["GALLERY"]["NAME"]);
		return false;
	endif;
endif;
/********************************************************************
				/Main Data
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** ACTIONS PERMISSSION ********************************/
$arResult["I"] = array("ACTIONS" => array("CREATE_GALLERY" => "N", "EDIT_GALLERY" => "N"));
if (!$GLOBALS["USER"]->IsAuthorized()):
elseif ($arParams["ABS_PERMISSION"] >= "W"):
	$arResult["I"] = array("ACTIONS" => array("CREATE_GALLERY" => "Y", "EDIT_GALLERY" => "Y", "UPLOAD" => "Y"));
else:
	$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "Y";
	$res = array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["GALLERY_GROUPS"]);
	if (empty($res)):
		$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "N";
	elseif ($arParams["ONLY_ONE_GALLERY"] == "Y" &&  !empty($arResult["MY_GALLERIES"])):
		$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "N";
	endif;

	if ($arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId()):
		$arParams["PERMISSION"] = "W";
		$arResult["I"]["ACTIONS"]["EDIT_GALLERY"] = "Y";
		$arResult["I"]["ACTIONS"]["UPLOAD"] = "Y";
	endif;
endif;
/************** URL ************************************************/
$USER_ID = $GLOBALS["USER"]->GetID();
$res = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"], array(
				"USER_ID" => $USER_ID, "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])),
	"NEW" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"], array("USER_ALIAS" => "NEW_ALIAS", "ACTION" => "CREATE",
				"USER_ID" => $USER_ID, "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])),
	"GALLERIES" =>  CComponentEngine::MakePathFromTemplate($arParams["~GALLERIES_URL"], array(
				"USER_ID" => $USER_ID, "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])));
$arResult["LINK"] = array();
foreach ($res as $key => $val)
{
	$arResult["LINK"]["~".$key] = $val;
	$arResult["LINK"][$key] = htmlspecialcharsEx($val);
}
/********************************************************************
				/Data
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y" && !empty($arResult["GALLERY"]))
{
	$APPLICATION->SetTitle($arResult["GALLERY"]["NAME"]);
}
/************** BreadCrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] == "Y" && !empty($arResult["GALLERY"]))
{
	$GLOBALS["APPLICATION"]->AddChainItem($arResult["GALLERY"]["NAME"],
		CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
			array("USER_ALIAS" => $arResult["GALLERY"]["CODE"])));
}
/************** Returns ********************************************/
if ($arParams["RETURN_ARRAY"] == "N") // For custom component
	return $arResult["GALLERY"]["CODE"];
else
	return array(
		"USER_ALIAS" => (!empty($arResult["GALLERY"]) ? $arResult["GALLERY"]["CODE"] : $arParams["USER_ALIAS"]),
		"ACTIVE" => $arResult["GALLERY"]["ACTIVE"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"PERMISSION" => $arParams["PERMISSION"],
		"ALL" => $arResult);
/********************************************************************
				/Standart
********************************************************************/
?>