<?
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/**
 * @global CUser $USER
 */

$MESS = array();
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/".LANGUAGE_ID."/show_file.php");
include_once($path);
$MESS1 =& $MESS;
$GLOBALS["MESS"] = $MESS1 + $GLOBALS["MESS"];

if (!CModule::IncludeModule("socialnetwork"))
	return;

$arParams = Array();
$arParams["WIDTH"] = (isset($_REQUEST["width"]) && intval($_REQUEST["width"])>0) ? intval($_REQUEST["width"]) : 0;
$arParams["HEIGHT"] = (isset($_REQUEST["height"]) && intval($_REQUEST["height"])>0) ? intval($_REQUEST["height"]) : 0;
$arParams["FILE_ID"] = intval($_REQUEST["fid"]);
$arParams["PERMISSION"] = false;
$arParams["QUALITY"] = (isset($_REQUEST["mobile"]) && $_REQUEST["mobile"] == "y") ? "50" : false;
$arParams["TYPE"] = (isset($_REQUEST["ltype"]) && $_REQUEST["ltype"] == "comment") ? "comment" : "post";

$arResult = array();
$arResult["MESSAGE"] = array();
$arResult["FILE"] = array();
$arResult["FILE_INFO"] = array();
$arResult["LOG"] = array();
$user_id = intval($USER->GetID());

$arError = array();
if (intval($arParams["FILE_ID"]) > 0)
{
	$arResult["FILE"] = CFile::GetFileArray(intval($arParams["FILE_ID"]));
	if (!empty($arResult["FILE"]))
	{
		if ($arParams["TYPE"] == "comment")
		{
			$rsComment = CSocNetLogComments::GetList(
				array(), 
				array("UF_SONET_COM_FILE" => $arParams["FILE_ID"]),
				false,
				false,
				array("ID", "LOG_ID"),
				array("CHECK_RIGHTS" => "Y")
			);
			if ($rsComment && $arComment = $rsComment->Fetch())
				$arResult["FILE_INFO"] = array(
					"LOG_ID" => $arComment["LOG_ID"]
				);
		}
		else
		{
			$rsLog = CSocNetLog::GetList(
				array(), 
				array("UF_SONET_LOG_FILE" => $arParams["FILE_ID"]),
				false,
				false,
				array("ID"),
				array("CHECK_RIGHTS" => "Y")
			);
			if ($rsLog && $arLog = $rsLog->Fetch())
				$arResult["FILE_INFO"] = array(
					"LOG_ID" => $arLog["ID"]
				);
		}
	}
}

if (empty($arResult["FILE"]))
{
	$arError = array(
		"code" => "EMPTY FILE",
		"title" => GetMessage("F_EMPTY_FID")
	);
}
elseif (empty($arResult["FILE_INFO"]))
{
	$arError = array(
		"code" => "NOT RIGHT",
		"title" => GetMessage("F_NOT_RIGHT")
	);
}

if (!empty($arError))
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
	ShowError((!empty($arError["title"]) ? $arError["title"] : $arError["code"]));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
	die();
}
// *************************/Default params*************************************************************

set_time_limit(0);

if (CFile::IsImage($arResult["FILE"]["FILE_NAME"], $arResult["FILE"]["CONTENT_TYPE"]))
{
	if (
		$arResult["FILE"]["WIDTH"] > $arParams["WIDTH"] 
		|| $arResult["FILE"]["HEIGHT"] > $arParams["HEIGHT"]
	)
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arResult["FILE"],
			array(
				"width" => $arParams["WIDTH"], 
				"height" => $arParams["HEIGHT"]
			),
			($_REQUEST["type"] == "square") ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$arResult["FILE"]["FILE_SIZE"] = $arFileTmp["size"];
		$arResult["FILE"]["SRC"] = $arFileTmp["src"];
	}
}

CFile::ViewByUser($arResult["FILE"], array("cache_time" => 86400));
