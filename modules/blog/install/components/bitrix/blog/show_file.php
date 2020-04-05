<?
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

const IMAGE_MAX_WIDTH = 1000;
const IMAGE_MIN_WIDTH = 10;
const IMAGE_MAX_HEIGHT = 1000;
const IMAGE_MIN_HEIGHT = 10;

function getValidImageSizes()
{
	return array(
		"WIDTH" => array(
			"70" => 70,
			"100" => 100,
			"200" => 200,
			"300" => 300,
			"1000" => 1000,
		),
		"HEIGHT" => array(
			"70" => 70,
			"100" => 100,
			"200" => 200,
			"300" => 300,
			"1000" => 1000,
		),
	);
}

function validImageSizes($width, $height, $fileArray)
{
//	sizes may be not set
	if(!intval($width) > 0)
		$width = $fileArray["WIDTH"];
	if(!intval($height) > 0)
		$height = $fileArray["HEIGHT"];
	
	$result = array("WIDTH" => "", "HEIGHT" => "");
	$validSizes = getValidImageSizes();
	
//	if file smaller, then max sizes - we don't need resize them
	if(!$width && !$height)
		return array("WIDTH" => $fileArray["WIDTH"], "HEIGHT" => $fileArray["HEIGHT"]);
	
//	if not set one param - we can use original size
	if(!$width)
		$width = $fileArray["WIDTH"];
	if(!$height)
		$height = $fileArray["HEIGHT"];
	
//	try find sizes in valid array. If fail - need match new scaled sizes
	if(array_key_exists($width, $validSizes["WIDTH"]) && array_key_exists($height, $validSizes["HEIGHT"]))
	{
		$result["WIDTH"] = $width;
		$result["HEIGHT"] = $height;
	}
//	if we use just rounded sizes - we get different image names, but equal scale sizes. Need match correct sizes
	else
	{
//		to preserve overlimits
		$width = max($width, IMAGE_MIN_WIDTH);
		$height = max($height, IMAGE_MIN_HEIGHT);
		
		$arSourceSize = array();
		$arDestinationSize = array();
		$bNeedCreatePicture = false;
		CFile::ScaleImage($fileArray["WIDTH"], $fileArray["HEIGHT"],
			array("width" => $width, "height" => $height),
			($_REQUEST["type"] == "square") ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			$bNeedCreatePicture, $arSourceSize, $arDestinationSize
		);
		$result["WIDTH"] = roundTo10($arDestinationSize["width"]);
		$result["HEIGHT"] = roundTo10($arDestinationSize["height"]);
		
//		to preserve overlimits
		$result["WIDTH"] = min($result["WIDTH"], IMAGE_MAX_WIDTH);
		$result["HEIGHT"] = min($result["HEIGHT"], IMAGE_MAX_HEIGHT);
	}
	
	return $result;
}

function roundTo10($val)
{
	return ceil($val/10) * 10;
}


$MESS = array();
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/".LANGUAGE_ID."/show_file.php");
include_once($path);
$MESS1 =& $MESS;
$GLOBALS["MESS"] = $MESS1 + $GLOBALS["MESS"];

if(!CModule::IncludeModule("blog"))
	return

$arParams = Array();
$arParams["WIDTH"] = (isset($_REQUEST['width']) && intval($_REQUEST['width'])>0) ? intval($_REQUEST['width']) : 0;
$arParams["HEIGHT"] = (isset($_REQUEST['height']) && intval($_REQUEST['height'])>0) ? intval($_REQUEST['height']) : 0;
$arParams["FILE_ID"] = IntVal($_REQUEST["fid"]);
$arParams["BP_FILE_ID"] = IntVal($_REQUEST["bp_fid"]);
$arParams["PERMISSION"] = false;
$arParams["QUALITY"] = (isset($_REQUEST['mobile']) && $_REQUEST["mobile"] == "y") ? "50" : false;

$arResult = array();
$arResult["MESSAGE"] = array();
$arResult["FILE"] = array();
$arResult["POST"] = array();
$arResult["FILE_INFO"] = array();
$user_id = IntVal($USER->GetID());

$arError = array();
if (intVal($arParams["FILE_ID"]) > 0)
{
	if ($res = CBlogImage::GetByID($arParams["FILE_ID"]))
	{
		$arResult["FILE_INFO"] = $res;
		$arResult["FILE"] = CFile::GetFileArray($arResult["FILE_INFO"]["FILE_ID"]);
	}
}
elseif (intVal($arParams["BP_FILE_ID"]) > 0)
{
	$arResult["FILE"] = CFile::GetFileArray(intVal($arParams['BP_FILE_ID']));
	if (!empty($arResult["FILE"]))
	{
		$dbPost = CBlogPost::GetList(array(), array('UF_BLOG_POST_DOC' => intVal($arParams['BP_FILE_ID'])));
		if ($dbPost && $arPost = $dbPost->Fetch())
		{
			$arResult["FILE_INFO"] = array(
				'POST_ID' => $arPost['ID'],
				'BLOG_ID' => $arPost['BLOG_ID']
			);
		}
		else
		{
			$dbPost = CBlogComment::GetList(array(), array('UF_BLOG_COMMENT_DOC' => intVal($arParams['BP_FILE_ID'])));
			if ($dbPost && $arPost = $dbPost->Fetch())
			{
				$arResult["FILE_INFO"] = array(
					'POST_ID' => $arPost['POST_ID'],
					'BLOG_ID' => $arPost['BLOG_ID']
				);
			}
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
else
{
	if (intVal($arResult["FILE_INFO"]["POST_ID"]) > 0)
	{
		if (empty($arResult['POST']))
		{
			$dbPost = CBlogPost::GetList(array(), array("ID" => $arResult["FILE_INFO"]["POST_ID"], "BLOG_ID" => $arResult["FILE_INFO"]["BLOG_ID"]), false, false, array("ID", "BLOG_ID", "BLOG_OWNER_ID", "BLOG_SOCNET_GROUP_ID", "BLOG_USE_SOCNET", "AUTHOR_ID"));
			$arResult["POST"] = $dbPost->Fetch();
		}

		if (!empty($arResult['POST']))
		{
			if($arResult["POST"]["BLOG_USE_SOCNET"] == "Y")
			{
				if(!CModule::IncludeModule("socialnetwork"))
					return;
				$arParams["PERMISSION"] = CBlogPost::GetSocNetPostPerms($arResult["POST"]["ID"]);
			}
			else
			{
				$arParams["PERMISSION"] = CBlogPost::GetBlogUserPostPerms($arResult["POST"]["ID"], $user_id);
			}
		}
	}
}

if(empty($arError))
{
	if (empty($arResult["POST"]))
	{
		$arError = array(
			"code" => "EMPTY POST",
			"title" => GetMessage("F_EMPTY_MID")
		);
	}
	elseif ($arParams["PERMISSION"] < BLOG_PERMS_READ)
	{
		$arError = array(
			"code" => "NOT RIGHT",
			"title" => GetMessage("F_NOT_RIGHT")
		);
	}
}

if (!empty($arError))
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
	echo ShowError((!empty($arError["title"]) ? $arError["title"] : $arError["code"]));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
	die();
}
// *************************/Default params*************************************************************

if (strlen(CFile::CheckImageFile(CFile::MakeFileArray($arResult["FILE"]['ID']))) <= 0)
{
//	get only valid image sizes, to preserve ddos
	$validSizes = validImageSizes($arParams['WIDTH'], $arParams['HEIGHT'], $arResult["FILE"]);
	$arParams['WIDTH'] = $validSizes["WIDTH"];
	$arParams['HEIGHT'] = $validSizes["HEIGHT"];
	
	if ($arResult["FILE"]["WIDTH"] > $arParams['WIDTH'] || $arResult["FILE"]["HEIGHT"] > $arParams['HEIGHT'])
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arResult["FILE"],
			array("width" => $arParams["WIDTH"], "height" => $arParams["HEIGHT"]),
			($_REQUEST["type"] == "square") ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		CFile::ViewByUser(
			array(
				'ORIGINAL_NAME' => $arResult['FILE']['ORIGINAL_NAME'],
				'FILE_SIZE' => $arFileTmp['size'],
				'SRC' => $arFileTmp['src'],
				'HANDLER_ID' => $arResult['FILE']['HANDLER_ID']
			),
			array(
				"content_type" => $arResult['FILE']["CONTENT_TYPE"],
				"cache_time" => 86400,
			)
		);
	}
	else
	{
		CFile::ViewByUser($arResult["FILE"], array("content_type" => $arResult["FILE"]["CONTENT_TYPE"], "cache_time" => 86400));
	}
}
else
{
	$ct = strtolower($arResult["FILE"]["CONTENT_TYPE"]);
	if (strpos($ct, "word") !== false || strpos($ct, "excel") !== false)
		CFile::ViewByUser($arResult["FILE"], array("force_download" => true, "cache_time" => 86400));
	else
		CFile::ViewByUser($arResult["FILE"], array("content_type" => "application/octet-stream", "force_download" => true, "cache_time" => 86400));
}
// *****************************************************************************************
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
echo ShowError(GetMessage("F_ATTACH_NOT_FOUND"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
// *****************************************************************************************
?>