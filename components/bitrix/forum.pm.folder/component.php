<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("PM_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("PM_AUTH"));
	return 0;
elseif (intval(COption::GetOptionString("forum", "UsePMVersion", "2")) <= 0):
	ShowError(GetMessage("F_NO_PM"));
	CHTTP::SetStatus("404 Not Found");
	return 0;
endif;
// *****************************************************************************************
	$APPLICATION->ResetException();
	InitSorting();
	global $by, $order;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intval(isset($arParams["FID"]) && intVal($arParams["FID"]) <= 0 ? (isset($_REQUEST["FID"]) ? $_REQUEST["FID"] : null) : (isset($arParams["FID"]) ? $arParams["FID"] : null));
	$arParams["mode"] = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : null;
$action = isset($_REQUEST["action"]) ? mb_strtolower($_REQUEST["action"]) : null;
	$version = COption::GetOptionString("forum", "UsePMVersion", "2");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"pm_folder" => "PAGE_NAME=pm_folder",
		"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
		"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&mode=#mode#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		if (!empty($by)):
			$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = ForumAddPageParams($arParams["URL_TEMPLATES_".mb_strtoupper($URL)],
				array("by" => $by, "order" => $order), false, false);
		endif;
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
//	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
/***************** STANDART ****************************************/
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_FOLDER"], array());
$arResult["URL"] = array(
	"FOLDER_NEW" => ForumAddPageParams($arResult["CURRENT_PAGE"], array("mode" => "new")),
	"MESSAGE_NEW" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], array("FID" => "1", "MID" => 0, "UID" => 0, "mode" => "new")));
$arResult["POST_VALUES"] = array();

$arResult["create_new_folder"] = $arResult["URL"]["FOLDER_NEW"];
$arParams["version"] = intval(COption::GetOptionString("forum", "UsePMVersion", "2"));

$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";

$arError = array();

if (!empty($_REQUEST["result"])):
	switch(mb_strtolower($_REQUEST["result"]))
	{
		case "create":
		case "save":
			$arResult["OK_MESSAGE"] = GetMessage("PM_SUCC_CREATE");
			break;
		case "delete":
			$arResult["OK_MESSAGE"] = GetMessage("PM_SUCC_DELETE");
			break;
		case "remove":
			$arResult["OK_MESSAGE"] = GetMessage("PM_SUCC_REMOVE");
			break;
		case "saved":
		case "update":
			$arResult["OK_MESSAGE"] = GetMessage("PM_SUCC_SAVED");
			break;
	}
endif;
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if (!empty($action))
{
	if (!check_bitrix_sessid()):
		$arError[] = array("id" => "BAD_SESSID", "text" => GetMessage("F_ERR_SESS_FINISH"));
	elseif (!in_array($action, array("update", "save", "delete", "remove"))):
		$arError[] = array("id" => "BAD_ACTION", "text" => GetMessage("F_ERR_ACTION"));
	else:
		switch($action)
		{
			case "update":
				$db_res = CForumPMFolder::GetByID($arParams["FID"]);
				$_REQUEST["FOLDER_TITLE"] = trim($_REQUEST["FOLDER_TITLE"]);
				if (empty($_REQUEST["FOLDER_TITLE"])):
					$arError[] = array(
						"id" => "empty_data",
						"text" => GetMessage("PM_NOT_FOLDER_TITLE"));
				elseif (!($db_res && $res = $db_res->GetNext())):
					$arError[] = array(
						"id" => "bad_fid",
						"text" => GetMessage("PM_NOT_FOLDER"));
				elseif (!CForumPMFolder::CheckPermissions($arParams["FID"])):
					$arError[] = array(
						"id" => "bad_permission",
						"text" => GetMessage("PM_NOT_RIGHT"));
				elseif (!CForumPMFolder::Update($arParams["FID"], array("TITLE"=>$_REQUEST["FOLDER_TITLE"]))):
					$str = "";
					$err = $APPLICATION->GetException();
					if ($err)
						$str = $err->GetString();
					$arError[] = array(
						"id" => "not_updated",
						"text" => $str);
				endif;
				break;
			case "save":
				$_REQUEST["FOLDER_TITLE"] = trim($_REQUEST["FOLDER_TITLE"]);
				if (empty($_REQUEST["FOLDER_TITLE"])):
					$arError[] = array(
						"id" => "empty_data",
						"text" => GetMessage("PM_NOT_FOLDER_TITLE"));
				elseif (!CForumPMFolder::Add($_REQUEST["FOLDER_TITLE"])):
					$str = "";
					$err = $APPLICATION->GetException();
					if ($err)
						$str = $err->GetString();
					$arError[] = array(
						"id" => "not_add",
						"text" => $str);
				endif;
				break;
			case "delete":
			case "remove":
				$arFolders = (is_array($_REQUEST["FID"]) && !empty($_REQUEST["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
				foreach ($arFolders as $iFid):
					$remMes = true;
					$iFid = intval($iFid);
					if ($iFid <= 0):
					elseif (!CForumPMFolder::CheckPermissions($iFid)):
						$arError[] = array(
							"id" => "bad_permission",
							"text" => GetMessage("PM_NOT_RIGHT"));
					elseif ($action == "delete" && $iFid <= FORUM_SystemFolder):
						$arError[] = array(
							"id" => "bad_folders",
							"text" => GetMessage("F_ERR_SYSTEM_FOLDERS"));
					else:
						$arFilter = array("FOLDER_ID" => $iFid, "USER_ID" => $USER->GetId());
						if ($version == "2" && ($iFid == 2 || $iFid == 3))
							$arFilter = array("OWNER_ID"=>$USER->GetId());
						elseif ($version != "2" && $iFid == 2)
							$arFilter = array("FOLDER_ID"=>2, "USER_ID"=>$USER->GetId(), "OWNER_ID"=>$USER->GetId());

						$arMessage = CForumPrivateMessage::GetListEx(array(), $arFilter);
						while ($res = $arMessage->GetNext())
						{
							if(!CForumPrivateMessage::Delete($res["ID"]))
								$arError[] = array(
									"id" => "bad_delete_".$res["ID"],
									"text" => GetMessage("PM_NOT_DELETE"));
						}
						if (empty($arError) && $action == "delete" && !CForumPMFolder::Delete($iFid))
						{
							$arError[] = array(
								"id" => "not_delete",
								"text" => GetMessage("PM_NOT_DELETE"));
						}
						if (empty($arError))
						{
							BXClearCache(true, "/bitrix/forum/user/".$USER->GetId()."/");
							$arComponentPath = array("bitrix:forum");
							foreach ($arComponentPath as $path)
							{
								$componentRelativePath = CComponentEngine::MakeComponentPath($path);
								$arComponentDescription = CComponentUtil::GetComponentDescr($path);

								if ($componentRelativePath == '' || !is_array($arComponentDescription))
									continue;
								elseif (!array_key_exists("CACHE_PATH", $arComponentDescription))
									continue;

								$path = str_replace("//", "/", $componentRelativePath."/user".$USER->GetID());

								if ($arComponentDescription["CACHE_PATH"] == "Y")
									$path = "/".SITE_ID.$path;

								if (!empty($path))
									BXClearCache(true, $path);
							}
						}
					endif;
				endforeach;
				break;
		}
	endif;



	if (empty($arError))
	{
		LocalRedirect(ForumAddPageParams($arResult["CURRENT_PAGE"], array("res" => $action), false, false));
	}
	else
	{
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] = $err->GetString();
	}
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["count"] = CForumPrivateMessage::PMSize($USER->GetID(), COption::GetOptionInt("forum", "MaxPrivateMessages", 100));
$arResult["count"] = round($arResult["count"]*100);
$arResult["SortingExTitle"] = SortingEx("title");
$arResult["SortingExCount"] = SortingEx("count");
$arResult["FORUM_SystemFolder"] = FORUM_SystemFolder;
$arResult["SYSTEM_FOLDER"] = array();
$arResult["USER_FOLDER"] = array();
$arResult["sessid"] = bitrix_sessid_post();
$arResult["FID"] = (isset($_REQUEST["FID"]) && is_array($_REQUEST["FID"]) && !empty($_REQUEST["FID"]) ? $_REQUEST["FID"] : (isset($arParams["FID"]) ? $arParams["FID"] : null));
$arResult["action"] = $arParams["mode"]=="new" ? "save" : "update";
$arResult["FOLDER"] = array();
/*******************************************************************/
if ($arParams["mode"] == "edit" || $arParams["mode"] == "new")
{
	if (intval($arParams["FID"]) > 0)
	{
		$db_res = CForumPMFolder::GetByID($arParams["FID"]);
		if ($db_res && ($res = $db_res->GetNext()))
		{
			$arResult["FOLDER"] = $res;
			$arResult["POST_VALUES"]["FOLDER_TITLE"] = $res["TITLE"];
		}
	}
	if (!empty($arError))
	{
		$arResult["POST_VALUES"]["FOLDER_TITLE"] = htmlspecialcharsbx($_REQUEST["FOLDER_TITLE"]);
	}
}
else
{
	for ($ii = 1; $ii <= FORUM_SystemFolder; $ii++)
	{
		$arResult["SYSTEM_FOLDER"][$ii]["cnt"] = "";
		$arFilter = ($ii == 2 ? array("FOLDER_ID"=>$ii, "USER_ID"=>$USER->GetId(), "OWNER_ID"=>$USER->GetId()) : array("FOLDER_ID"=>$ii, "USER_ID"=>$USER->GetId()));
		$db_res = CForumPrivateMessage::GetList(array(), $arFilter, true);
		if ($db_res && ($res = $db_res->GetNext()))
		{
			$arResult["SYSTEM_FOLDER"][$ii]["cnt"] = intval($res["CNT"]);
			$arResult["SYSTEM_FOLDER"][$ii]["CNT"] = intval($res["CNT"]);
			$arResult["SYSTEM_FOLDER"][$ii]["CNT_NEW"] = intval($res["CNT_NEW"]);
		}
		$arResult["SYSTEM_FOLDER"][$ii]["URL"] = array(
			"FOLDER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID" => $ii)),
			"REMOVE" => ForumAddPageParams($arResult["CURRENT_PAGE"], array("action" => "remove", "FID" => $ii)));
		$arResult["SYSTEM_FOLDER"][$ii]["pm_list"] = $arResult["SYSTEM_FOLDER"][$ii]["URL"]["FOLDER"];
		$arResult["SYSTEM_FOLDER"][$ii]["remove"] = $arResult["SYSTEM_FOLDER"][$ii]["URL"]["REMOVE"];
	}

	$arResult["SHOW_USER_FOLDER"] = "N";
	$db_res = CForumPMFolder::GetList(array($by=>$order), array("USER_ID"=>$USER->GetId()));
	if ($db_res && ($res = $db_res->GetNext()))
	{
		$arResult["SHOW_USER_FOLDER"] = "Y";
		do
		{
			$res["URL"] = array(
				"FOLDER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID" => $res["ID"])),
				"DELETE" => ForumAddPageParams($arResult["CURRENT_PAGE"] , array("action" => "delete", "FID" => $res["ID"])),
				"REMOVE" => ForumAddPageParams($arResult["CURRENT_PAGE"] , array("action" => "remove", "FID" => $res["ID"])),
				"EDIT" => ForumAddPageParams($arResult["CURRENT_PAGE"] , array("mode" => "edit", "FID" => $res["ID"])));
			$res["pm_list"] = $res["URL"]["FOLDER"];
			$res["CNT"] = intval($res["CNT"]);
			$res["CNT_NEW"] = intval($res["CNT_NEW"]);
			$res["delete"] =  $res["URL"]["DELETE"];
			$res["remove"] = $res["URL"]["REMOVE"];
			$res["edit"] = $res["URL"]["EDIT"];
			$arResult["USER_FOLDER"][] = $res;
		}
		while ($res = $db_res->GetNext());
	}
}
/********************************************************************
				/Data
********************************************************************/
/*******************************************************************/
$this->IncludeComponentTemplate();
/*******************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
{
	if ($arParams["mode"] == "edit" || $arParams["mode"] == "new")
	{
		$APPLICATION->AddChainItem(GetMessage("PM_PM"), CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_FOLDER"], array()));
		if ($arParams["mode"] == "edit")
			$APPLICATION->AddChainItem($arResult["FOLDER"]["TITLE"]);
		else
			$APPLICATION->AddChainItem(GetMessage("PM_TITLE_NEW"));
	}
	else
		$APPLICATION->AddChainItem(GetMessage("PM_PM"));
}
/*******************************************************************/
if ($arParams["SET_TITLE"] != "N")
{
	if ($arParams["mode"] == "new")
		$APPLICATION->SetTitle(GetMessage("PM_TITLE_NEW"));
	elseif ($arParams["mode"] == "edit")
		$APPLICATION->SetTitle(str_replace("#TITLE#", $arResult["FOLDER"]["TITLE"], GetMessage("PM_TITLE_EDIT")));
	else
		$APPLICATION->SetTitle(GetMessage("PM_PM"));
}
?>
