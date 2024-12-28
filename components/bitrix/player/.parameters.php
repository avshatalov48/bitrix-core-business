<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$adv_mode = (($arCurrentValues["ADVANCED_MODE_SETTINGS"] ?? null) == 'Y');
$hidden = ($adv_mode) ? "N" : "Y";
$fp = $arCurrentValues["PATH"] ?? null;

$arComponentParameters = array();
$arComponentParameters["GROUPS"] = array(
	"BASE_SETTINGS" => array("NAME" => GetMessage("PC_GROUP_BASE_SETTINGS"), "SORT" => "100"),
	"PLAYBACK" =>array("NAME" => GetMessage("PC_GROUP_PLAYBACK"), "SORT" => "200"),
	"ADDITIONAL_SETTINGS" => array("NAME" => GetMessage("PC_GROUP_ADDITIONAL_SETTINGS"), "SORT" => "300")
);

if ($adv_mode)
{
	$arComponentParameters["GROUPS"]["APPEARANCE"] = array(
		"NAME" => GetMessage("PC_GROUP_APPEARANCE_COMMON"),
		"SORT" => "140"
	);

	$arComponentParameters["GROUPS"]["PLAYBACK"] = array(
		"NAME" => GetMessage("PC_GROUP_PLAYBACK"),
		"SORT" => "210"
	);
}

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
$arParams = array();

if ($adv_mode)
{
	$arParams["USE_PLAYLIST"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_USE_PLAYLIST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"HIDDEN" => $hidden,
	);
}

if (($arCurrentValues["USE_PLAYLIST"] ?? null) == 'Y')
{
	$ext = 'xml';
}
else
{
	$ext = 'flv,mp3,mp4,m4v,aac,webm,ogv,ogg,weba,wav,m4a';
}

$arParams["PATH"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => ($arCurrentValues['USE_PLAYLIST'] ?? null) != 'Y' ? GetMessage("PC_PAR_FILE_PATH") : GetMessage("PC_PAR_PLAYLIST_PATH"),
	"TYPE" => "FILE",
	"FD_TARGET" => "F",
	"FD_EXT" => $ext,
	"FD_UPLOAD" => true,
	"FD_USE_MEDIALIB" => true,
	"FD_MEDIALIB_TYPES" => Array('video', 'sound'),
	"DEFAULT" => "",
	"REFRESH" => "Y"
);

if (($arCurrentValues["USE_PLAYLIST"] ?? null) == 'Y')
{
	$bPlaylistExists = ($arCurrentValues['PATH'] ?? '') <> '' && file_exists($_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arCurrentValues['PATH']));
	$butTitle = $bPlaylistExists ? GetMessage("PC_PAR_EDIT") : GetMessage("PC_PAR_CREATE");

	$arParams["PLAYLIST_DIALOG"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_PLAYLIST_BUT"),
		"TYPE" => "CUSTOM",
		"JS_FILE" => "/bitrix/components/bitrix/player/js/prop_playlist_edit.js",
		"JS_EVENT" => "ComponentPropsEditPlaylistDialog",
		"JS_DATA" => $butTitle.'||'.GetMessage("ERROR_EMPTY_PATH"),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);

	$arParams["USE_PLAYLIST_AS_SOURCES"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_USE_PLAYLIST_AS_SOURCES"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden,
	);
}

$arParams["SIZE_TYPE"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage ("PC_PAR_SIZES_TYPE"),
	"TYPE" => "LIST",
	"VALUES" => array (
		"absolute" => GetMessage("PC_PAR_SIZES_ABSOLUTE"),
		"fluid" => GetMessage("PC_PAR_SIZES_FLUID"),
		"auto" => GetMessage("PC_PAR_SIZES_AUTO"),
	),
	"DEFAULT" => "absolute",
	"REFRESH" => "Y",
);
if (($arCurrentValues["USE_PLAYLIST"] ?? null) == "Y")
{
	unset ($arParams["SIZE_TYPE"]["VALUES"]["auto"]);
}
if (($arCurrentValues["SIZE_TYPE"] ?? null) == "absolute" || ($arCurrentValues["SIZE_TYPE"] ?? null) == "")
{
	$arParams["WIDTH"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_WIDTH"),
		"COLS" => 10,
		"DEFAULT" => 400,
	);
	$arParams["HEIGHT"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_HEIGHT"),
		"COLS" => 10,
		"DEFAULT" => 300,
	);
}

if ($adv_mode)
{
	$arParams["TYPE"] = array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_FILE_TYPE"),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
	$arParams["PREVIEW"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_PREVIEW_IMAGE"),
		"TYPE" => "FILE",
		"FD_TARGET" => "F",
		"FD_EXT" => "png,gif,jpg,jpeg",
		"FD_UPLOAD" => true,
		"FD_USE_MEDIALIB" => true,
		"FD_MEDIALIB_TYPES" => Array('image'),
		"DEFAULT" => '',
		"HIDDEN" => $hidden,
	);
}

/*if($arCurrentValues["USE_PLAYLIST"]!='Y')
{
	if ($adv_mode)
	{
		$arParams["PREVIEW"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PREVIEW_IMAGE"),
			"TYPE" => "FILE",
			"FD_TARGET" => "F",
			"FD_EXT" => "png,gif,jpg,jpeg",
			"FD_UPLOAD" => true,
			"FD_USE_MEDIALIB" => true,
			"FD_MEDIALIB_TYPES" => Array('image'),
			"DEFAULT" => '',
			"HIDDEN" => $hidden,
		);
	}

	$arParams["FILE_TITLE"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_FILE_TITLE"),
		"COLS" => 40,
		"DEFAULT" => "",
		"HIDDEN" => $hidden
	);

	$arParams["FILE_DURATION"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_FILE_DURATION"),
		"COLS" => 40,
		"DEFAULT" => "",
		"HIDDEN" => $hidden
	);

	$arParams["FILE_AUTHOR"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_FILE_AUTHOR"),
		"COLS" => 40,
		"DEFAULT" => "",
		"HIDDEN" => $hidden
	);

	$arParams["FILE_DATE"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_FILE_DATE"),
		"COLS" => 40,
		"DEFAULT" => "",
		"HIDDEN" => $hidden
	);

	$arParams["FILE_DESCRIPTION"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_FILE_DESCRIPTION"),
		"COLS" => 40,
		"DEFAULT" => "",
		"HIDDEN" => $hidden
	);
}*/

$arParams["PRELOAD"] = Array (
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_AUTOLOAD"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
);
$arParams["SHOW_CONTROLS"] = Array(
	"PARENT" => "APPEARANCE",
	"NAME" => GetMessage("PC_PAR_SHOW_CONTROLS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"REFRESH" => "Y",
);

// PLAYBACK
$playback_parent = 'PLAYBACK';
$arParams["AUTOSTART"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_AUTOSTART"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);

if (($arCurrentValues['AUTOSTART'] ?? null) != 'Y')
{
	$arParams["AUTOSTART_ON_SCROLL"] = Array(
		"PARENT" => $playback_parent,
		"NAME" => GetMessage("PC_PAR_AUTOSTART_ON_SCROLL"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"
	);
}

$arParams["REPEAT"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_REPEAT"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"none" => GetMessage("PC_PAR_REPEAT_NONE"),
		"always" => GetMessage("PC_PAR_REPEAT_ALWAYS"),
	),
	"DEFAULT" => "N"
);


if (($arCurrentValues['USE_PLAYLIST'] ?? null) == "Y")
{
	unset ($arParams["REPEAT"]);
}

$arParams["VOLUME"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_VOLUME"),
	"COLS" => 10,
	"DEFAULT" => "90"
);


$arParams["MUTE"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_MUTE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"HIDDEN" => $hidden,
);
if (($arCurrentValues['USE_PLAYLIST'] ?? null) != 'Y')
{
	$arParams["START_TIME"] = Array(
		"PARENT" => $playback_parent,
		"NAME" => GetMessage("PC_PAR_START_TIME"),
		"COLS" => 10,
		"DEFAULT" => "0",
		"HIDDEN" => $hidden,
	);
}
$arParams["PLAYBACK_RATE"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_PLAYBACK_RATE"),
	"COLS" => 10,
	"DEFAULT" => "1",
	"HIDDEN" => $hidden,
);

$arParams["ADVANCED_MODE_SETTINGS"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_ADVANCED_MODE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
);

$arParams["PLAYER_ID"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_PLAYER_ID"),
	"DEFAULT" => "",
	"HIDDEN" => $hidden,
);

$arComponentParameters["PARAMETERS"] = $arParams;
