<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;

//Prepare params
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
$arParams["PATH_TO_FILE"] = intval($arParams["PATH_TO_FILE"]);
$arParams["DURATION"] = trim($arParams["DURATION"]);
$arParams["LOGO"] = trim($arParams["LOGO"]);
$arParams["DEFAULT_SMALL_IMAGE"] = trim($arParams["DEFAULT_SMALL_IMAGE"]);
$arParams["DEFAULT_BIG_IMAGE"] = trim($arParams["DEFAULT_BIG_IMAGE"]);
$arParams["WIDTH"] = intval($arParams["WIDTH"])>0
	?intval($arParams["WIDTH"])
	:640;
$arParams["HEIGHT"] = intval($arParams["HEIGHT"])>0
	?intval($arParams["HEIGHT"])
	:480;
$arParams["CACHE_TIME"] = isset($arParams["CACHE_TIME"])
	?intval($arParams["CACHE_TIME"])
	:3600;
$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
if($arParams["SORT_BY1"] == '')
	$arParams["SORT_BY1"] = 'SORT';
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"]))
	$arParams["SORT_ORDER1"]='DESC';

if($arParams["IBLOCK_ID"]<=0 || $arParams["PATH_TO_FILE"]<=0)
	return false;

$arParams["STAT_EVENT"] = $arParams["STAT_EVENT"] === "Y";
if($arParams["STAT_EVENT"])
{
	$arParams["STAT_EVENT1"] = trim($arParams["STAT_EVENT1"]);
	if($arParams["STAT_EVENT1"] == '')
		$arParams["STAT_EVENT1"] = "player";

	$arParams["STAT_EVENT2"] = trim($arParams["STAT_EVENT2"]);
	if($arParams["STAT_EVENT2"] == '')
		$arParams["STAT_EVENT2"] = "start_playing";
}
else
{
	$arParams["STAT_EVENT1"] = "";
	$arParams["STAT_EVENT2"] = "";
}

$arParams["SHOW_COUNTER_EVENT"] = $arParams["SHOW_COUNTER_EVENT"] === "Y";


//SELECT
$arSelect = array(
	"ID",
	"NAME",
	"IBLOCK_SECTION_ID",
	"PREVIEW_TEXT",
	"PREVIEW_PICTURE",
	"DETAIL_PICTURE",
	"IBLOCK_TYPE_ID",
	"IBLOCK_ID",
	"PROPERTY_".$arParams["PATH_TO_FILE"],
);
if($arParams["DURATION"]>0)
	$arSelect[]="PROPERTY_".$arParams["DURATION"];
//WHERE
$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"ACTIVE" => 'Y',
	"IBLOCK_ACTIVE" => 'Y',
);
if($arParams["IBLOCK_TYPE"] <> '')
	$arFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];
if($arParams["SECTION_ID"]>0)
	$arFilter["SECTION_ID"] = $arParams["SECTION_ID"];
//ORDER BY
$arSort = array(
	$arParams["SORT_BY1"] => $arParams["SORT_ORDER1"],
);


global $BX_TV_PREFIX;
if(!isset($BX_TV_PREFIX))
	$BX_TV_PREFIX = 0;
else
	$BX_TV_PREFIX = intval($BX_TV_PREFIX)+1;

$arResult['FIRST_FLV_ITEM'] = false;
$arResult['FIRST_WMV_ITEM'] = false;

if($this->StartResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $BX_TV_PREFIX)))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("CC_BIT_MODULE_NOT_INSTALLED"));
		return;
	}

	$rsProperty = CIBlockProperty::GetByID(
		$arParams["PATH_TO_FILE"],
		$arParams["IBLOCK_ID"]
	);

	if(!$arProperty = $rsProperty->Fetch())
	{
		$this->AbortResultCache();
		return false;
	}

	$arResult = array(
		"RAW_SECTIONS" => array(),
		"RAW_ELEMENTS" => array(),
		"RAW_FILES" => array(),
		"PREFIX" => $BX_TV_PREFIX,
		"ELEMENT_CNT" => 0,
		"IBLOCK_TYPE_ID" => false,
		"IBLOCK_ID" => false,
	);

	$rsElements = CIBlockElement::GetList(
		$arSort,
		$arFilter,
		false,
		false,
		$arSelect
	);

	//Get Elements
	while($arElements = $rsElements->Fetch())
	{
		$arResult["IBLOCK_TYPE_ID"] = $arElements["IBLOCK_TYPE_ID"];
		$arResult["IBLOCK_ID"] = $arElements["IBLOCK_ID"];

		if(intval($arElements["IBLOCK_SECTION_ID"])>0)
		{
			$arResult["RAW_ELEMENTS"][$arElements["ID"]] = $arElements;
			$arResult["RAW_SECTIONS_ID"][] = intval($arElements["IBLOCK_SECTION_ID"]);
			$arResult["ELEMENT_CNT"]++;
		}
	}

	//Get Sections
	if($arParams["SECTION_ID"]<=0 && count($arResult["RAW_SECTIONS_ID"])>0)
	{
		$arResult["RAW_SECTIONS_ID"] = array_unique($arResult["RAW_SECTIONS_ID"]);
		$rsSections = CIBlockSection::GetList(
			$arSort,
			array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ID" => $arResult["RAW_SECTIONS_ID"],
			)
		);

		while($arSections = $rsSections->Fetch())
			$arResult["RAW_SECTIONS"][$arSections["ID"]] = $arSections;
	}

	//Prepare elements
	foreach($arResult["RAW_ELEMENTS"] as $key => $arItem)
	{
		$SectionId = $arParams["SECTION_ID"] > 0 ?$arParams["SECTION_ID"] : intval($arItem["IBLOCK_SECTION_ID"]);

		$Duration = $arItem["PROPERTY_".$arParams["DURATION"]."_VALUE"];
		$PathToFile = "";
		if ($arProperty["PROPERTY_TYPE"] == 'F')
		{
			$PathToFile = CFile::GetPath($arItem["PROPERTY_".$arParams["PATH_TO_FILE"]."_VALUE"]);
		}
		elseif ($arProperty["PROPERTY_TYPE"] == 'S')
		{
			$val = $arItem["PROPERTY_".$arParams["PATH_TO_FILE"]."_VALUE"];

			if (is_array($val))
			{
				if (isset($val['path']))
					$PathToFile = $val['path'];
				if (isset($val['duration']))
					$Duration = $val['duration'];
			}
			else
			{
				$PathToFile = $arItem["PROPERTY_".$arParams["PATH_TO_FILE"]."_VALUE"];
			}
		}

		if ($PathToFile == "")
			continue;

		if(
			$PathToFile
			&& file_exists(CBXVirtualIoFileSystem::ConvertCharset($_SERVER["DOCUMENT_ROOT"]."/".$PathToFile))
			&& is_file(CBXVirtualIoFileSystem::ConvertCharset($_SERVER["DOCUMENT_ROOT"]."/".$PathToFile))
		)
		{
			$FileSize = filesize($_SERVER["DOCUMENT_ROOT"]."/".$PathToFile);
			if($FileSize)
				$FileSize = round(sprintf("%u", $FileSize)/1024/1024, 2);
			if($FileSize <= 0)
				$FileSize = "";

			$ext = mb_strtolower(mb_substr($PathToFile, -4));
			if($ext == ".wmv" || $ext == ".wma")
				$FileType = "wmv";
			else
				$FileType = "flv";
		}
		elseif(
			CModule::IncludeModule("clouds")
			&& ($uri = CCloudStorage::FindFileURIByURN($PathToFile, "component:iblock.tv, iblock:".$arParams["IBLOCK_ID"].", element: ".$arItem["ID"])) !== ""
		)
		{
			$PathToFile = $uri;

			$FileSize = "";

			$ext = mb_strtolower(mb_substr($PathToFile, -4));
			if($ext == ".wmv" || $ext == "wma")
				$FileType = "wmv";
			else
				$FileType = "flv";
		}
		else
		{
			$FileSize = "";
			$FileType = "flv";
		}

		if (!$arResult['FIRST_FLV_ITEM'] && $FileType == 'flv')
			$arResult['FIRST_FLV_ITEM'] = $PathToFile;
		if (!$arResult['FIRST_WMV_ITEM'] && $FileType == 'wmv')
			$arResult['FIRST_WMV_ITEM'] = $PathToFile;

		if (mb_strpos($_SERVER['HTTP_HOST'], 'xn--') !== false) // It's cyrilyc site
		{
			$PathToFile = CHTTP::URN2URI($PathToFile);
		}


		$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]] = array(
			"NAME" => $arItem["NAME"],
			"PREVIEW_TEXT" => $arItem["PREVIEW_TEXT"],
			"PREVIEW_PICTURE" => CFile::GetPath($arItem["PREVIEW_PICTURE"]),
			"DETAIL_PICTURE" => CFile::GetPath($arItem["DETAIL_PICTURE"]),
			"DURATION" => $Duration,
			"FILE_SIZE" => $FileSize,
			"FILE" => $PathToFile,
			"TYPE" => $FileType,
			"ID" => $arItem["ID"],
			"IBLOCK_SECTION_ID" => $arItem["IBLOCK_SECTION_ID"],
		);

		$arResult["RAW_FILES"][$PathToFile] = array("ID" => $arItem["ID"], "NAME" => $arItem["NAME"]);

		if(!$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["PREVIEW_PICTURE"])
			$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["PREVIEW_PICTURE"] = $arParams["DEFAULT_SMALL_IMAGE"];
		if(!$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["DETAIL_PICTURE"])
			$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["DETAIL_PICTURE"] = $arParams["DEFAULT_BIG_IMAGE"];

		if(!isset($arResult["SELECTED_ELEMENT"]))
		{
			if($arParams["ELEMENT_ID"]<=0 || $arParams["ELEMENT_ID"] == $key)
			{
				$arResult["SELECTED_ELEMENT"] = array(
					"VALUES" => $arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]],
					"FILE" => $arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["FILE"],
				);
			}
		}
	}

	if(CIBlock::GetPermission($arParams["IBLOCK_ID"])>='U')
		$arResult["CAN_EDIT"] = "Y";
	else
		$arResult["CAN_EDIT"] = "N";

	$this->SetResultCacheKeys(array(
		"CAN_EDIT", "IBLOCK_TYPE_ID", "RAW_FILES", "IBLOCK_ID"
	));

	if (isset($arResult["SELECTED_ELEMENT"]))
	{
		$this->IncludeComponentTemplate();
	}
}

//include js
$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/wmvplayer/silverlight.js').'"></script>', true);
$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js').'"></script>', true);
$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/mediaplayer/flvscript.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/mediaplayer/flvscript.js').'"></script>', true);
CUtil::InitJSCore();

if(
	$arParams["IBLOCK_ID"]
	&& $USER->IsAuthorized()
	&& (
		$APPLICATION->GetShowIncludeAreas()
		|| (
			is_object($INTRANET_TOOLBAR)
			&& $arParams["INTRANET_TOOLBAR"]!=="N"
		)
	)
	&& CModule::IncludeModule("iblock")
)
{
	$arButtons = CIBlock::GetPanelButtons($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], array("SECTION_BUTTONS"=>false));
	if($APPLICATION->GetShowIncludeAreas())
		$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));

	if(
		is_array($arButtons["intranet"])
		&& is_object($INTRANET_TOOLBAR)
		&& $arParams["INTRANET_TOOLBAR"]!=="N"
	)
	{
		$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
		foreach($arButtons["intranet"] as $arButton)
			$INTRANET_TOOLBAR->AddButton($arButton);
	}
}

if($arParams["STAT_EVENT"] || $arParams["SHOW_COUNTER_EVENT"])
{
	IncludeAJAX();

	if(!isset($_SESSION["player_files"]))
		$_SESSION["player_files"] = array();

	foreach($arResult["RAW_FILES"] as $path => $arFile)
	{
		if(!isset($_SESSION["player_files"][$arFile["ID"]]))
			$_SESSION["player_files"][$arFile["ID"]] = array(
				"WAS_STAT_EVENT" => false,
				"WAS_SHOW_COUNTER_EVENT" => false,
			);

		$_SESSION["player_files"][$arFile["ID"]]["STAT_EVENT"] = $arParams["STAT_EVENT"];
		$_SESSION["player_files"][$arFile["ID"]]["STAT_EVENT1"] = $arParams["STAT_EVENT1"];
		$_SESSION["player_files"][$arFile["ID"]]["STAT_EVENT2"] = $arParams["STAT_EVENT2"];
		$_SESSION["player_files"][$arFile["ID"]]["STAT_EVENT3"] = $arParams["IBLOCK_ID"]." / [".$arFile["ID"]."] ".$arFile["NAME"];
		$_SESSION["player_files"][$arFile["ID"]]["SHOW_COUNTER_EVENT"] = $arParams["SHOW_COUNTER_EVENT"];
	}
}
?>
