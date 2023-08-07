<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array();
$arComponentParameters["GROUPS"] = array(
	"BASE_SETTINGS" => array("NAME" => GetMessage("LHE_GROUP_BASE_SETTINGS"), "SORT" => "100"),
	"VIDEO_SETTINGS" => array("NAME" => GetMessage("LHE_GROUP_VIDEO_SETTINGS"), "SORT" => "200"),
	"ADDITIONAL_SETTINGS" => array("NAME" => GetMessage("LHE_GROUP_ADDITIONAL_SETTINGS"), "SORT" => "300")
);

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
$arParams = array(); // $arComponentParameters["PARAMETERS"]
// BASE
$arParams["CONTENT"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_CONTENT"),
	"DEFAULT" => $content ?? null,
);
$arParams["INPUT_NAME"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_INPUT_NAME"),
	"DEFAULT" => ''
);
$arParams["INPUT_ID"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_INPUT_ID"),
	"DEFAULT" => '',
);
$arParams["WIDTH"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_WIDTH"),
	"COLS" => 10,
	"DEFAULT" => '100%'
);
$arParams["HEIGHT"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_HEIGHT"),
	"COLS" => 10,
	"DEFAULT" => '300px'
);

$arParams["RESIZABLE"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_RESIZABLE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);

if(($arCurrentValues["RESIZABLE"] ?? null) == 'Y')
{
	$arParams["AUTO_RESIZE"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_AUTO_RESIZE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"
	);
}

//VIDEO
$arParams["VIDEO_ALLOW_VIDEO"] = Array(
	"PARENT" => "VIDEO_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_VIDEO_ALLOW_VIDEO"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"REFRESH" => "Y",
);

if(($arCurrentValues["VIDEO_ALLOW_VIDEO"] ?? null) != 'N')
{
	$arParams["VIDEO_MAX_WIDTH"] = Array(
		"PARENT" => "VIDEO_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_VIDEO_MAX_WIDTH"),
		"COLS" => 10,
		"DEFAULT" => '640',
	);
	$arParams["VIDEO_MAX_HEIGHT"] = Array(
		"PARENT" => "VIDEO_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_VIDEO_MAX_HEIGHT"),
		"COLS" => 10,
		"DEFAULT" => '480',
	);
	$arParams["VIDEO_BUFFER"] = Array(
		"PARENT" => "VIDEO_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_VIDEO_BUFFER"),
		"COLS" => 10,
		"DEFAULT" => 20,
	);
	$arParams["VIDEO_LOGO"] = Array(
		"PARENT" => "VIDEO_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_VIDEO_LOGO"),
		"DEFAULT" => '',
		"COLS" => 30,
	);
	$arParams["VIDEO_WMODE"] = Array(
		"PARENT" => "VIDEO_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_VIDEO_WMODE").GetMessage("LHE_PAR_VIDEO_ONLY_FLV"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'window' => GetMessage("LHE_PAR_WMODE_WINDOW"),
			'opaque' => GetMessage("LHE_PAR_WMODE_OPAQUE"),
			'transparent' => GetMessage("LHE_PAR_WMODE_TRANSPARENT")
		),
		"DEFAULT" => "transparent",
	);
	$arParams["VIDEO_WINDOWLESS"] = Array(
		"PARENT" => "VIDEO_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_VIDEO_WINDOWLESS").GetMessage("LHE_PAR_VIDEO_ONLY_WMV"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	);
	$arParams["VIDEO_SKIN"] = Array(
		"PARENT" => "VIDEO_SETTINGS",
		"NAME" => GetMessage("LHE_PAR_VIDEO_SKIN").GetMessage("LHE_PAR_VIDEO_ONLY_FLV"),
		"DEFAULT" => '/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf',
		"COLS" => 30,
	);
}

// ADVANCED
$arParams["USE_FILE_DIALOGS"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_USE_FILE_DIALOGS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
);

$arParams["ID"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_ID"),
	"DEFAULT" => '',
);

$arParams["JS_OBJ_NAME"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("LHE_PAR_JS_OBJ_NAME"),
	"DEFAULT" => "",
);

$arComponentParameters["PARAMETERS"] = $arParams;
?>
