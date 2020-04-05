<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$type = $arCurrentValues["PLAYER_TYPE"] ? $arCurrentValues["PLAYER_TYPE"] : 'auto';
$adv_mode = ($arCurrentValues["ADVANCED_MODE_SETTINGS"] == 'Y');
$hidden = ($adv_mode) ? "N" : "Y";

$defaultVideoJsSkinPath = '/bitrix/js/fileman/player/videojs/skins';

if (!function_exists('getSkinsFromDir'))
{
	function getSkinsFromDir($path) //http://jabber.bx/view.php?id=28856
	{
		$arSkins = Array();
		$basePath = $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $path);
		$arSkinExt = array('swf', 'zip', 'css');
		$arPreviewExt = array('png', 'gif', 'jpg', 'jpeg');
		$prExtCnt = count($arPreviewExt);

		$handle  = @opendir($basePath);

		while(false !== ($f = @readdir($handle)))
		{
			if($f == "." || $f == ".." || $f == ".htaccess" || !is_file($basePath.'/'.$f))
				continue;

			$ext = strtolower(GetFileExtension($f));
			if (in_array($ext, $arSkinExt)) // We find skin
			{
				$name = substr($f, 0, - strlen($ext) - 1); // name of the skin
				if (strlen($name) <= 0)
					continue;

				if (strpos($name, '.min') !== false)
					continue;

				$Skin = array('filename' => $f);
				$Skin['name'] = strtoupper(substr($name, 0, 1)).strtolower(substr($name, 1));
				$Skin['the_path'] = $path;

				// Try to find preview
				for ($i = 0; $i < $prExtCnt; $i++)
				{
					if (file_exists($basePath.'/'.$name.'.'.$arPreviewExt[$i]))
					{
						$Skin['preview'] = $name.'.'.$arPreviewExt[$i];
						break;
					}
				}
				$arSkins[] = $Skin;
			}
		}

		return $arSkins;
	}
}

if (!function_exists('getSkinsEx'))
{
	function getSkinsEx($path)
	{
		$basePath = $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $path);
		$arSkins = Array();

		if (!is_dir($basePath)) // Not valid folder
			return $arSkins;

		$arSkins = getSkinsFromDir($path);

		$handle  = @opendir($basePath);

		while(false !== ($skinDir = @readdir($handle)))
		{

			if(!is_dir($basePath.'/'.$skinDir) || $skinDir == "." || $skinDir == ".." )
				continue;

			$arDirSkins=getSkinsFromDir($path.'/'.$skinDir);
			$arSkins = array_merge($arSkins,$arDirSkins);
		}
		return $arSkins;
	}
}

$fp = $arCurrentValues["PATH"];
if ($type == 'auto' && strlen($fp) > 0 && strpos($fp, '.') !== false)
{
	$ext = strtolower(GetFileExtension($fp));
	$type = (in_array($ext, array('wmv', 'wma'))) ? 'wmv' : 'videojs';
}

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
$arParams["PLAYER_TYPE"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("PC_PAR_PLAYER_TYPE"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"auto" => GetMessage("PC_PAR_PLAYER_AUTODETECT"),
		"videojs" => "video.js",
		"flv" => GetMessage("PC_PAR_PLAYER_FLV"),
		"wmv" => GetMessage("PC_PAR_PLAYER_WMV"),
	),
	"DEFAULT" => $type,
	"REFRESH" => "Y",
);

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


if ($arCurrentValues["USE_PLAYLIST"] == 'Y')
	$ext = 'xml';
elseif($type == 'flv')
	$ext = 'flv,mp3,mp4,aac,jpg,jpeg,gif,png';
elseif($type == 'wmv')
	$ext = 'wmv,wma';
elseif ($type == 'videojs')
	$ext = 'flv,mp3,mp4,m4v,aac,webm,ogv,ogg,weba,wav,m4a';
else
	$ext = 'wmv,wma,flv,mp3,mp4,aac,jpg,jpeg,gif,png,m4v,webm,ogv,ogg,weba,wav';

/*if ($type == 'videojs')
{
	if ($arCurrentValues["USE_PLAYLIST"] != 'Y')
	{
		$arParams["STREAM"] = Array(
			"PARENT" => "BASE_SETTINGS",
			"NAME" => GetMessage("PC_PAR_STREAM"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		);
		if ($arCurrentValues["STREAM"] == "Y")
		{
			$arParams["STREAM_TYPE"] = Array (
				"PARENT" => "BASE_SETTINGS",
				"NAME" => GetMessage("PC_PAR_STREAM_TYPE"),
				"TYPE" => "LIST",
				"DEFAULT" => "rtmp",
				"VALUES" => Array (
					"rtmp" => "RTMP",
					"rtmpt" => "RTMPT",
					"rtmpe" => "RTMPE",
					"rtmps" => "RTMPS",
				)
			);
		}
	}
}*/

$arParams["PATH"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => $arCurrentValues['USE_PLAYLIST'] != 'Y' ? GetMessage("PC_PAR_FILE_PATH") : GetMessage("PC_PAR_PLAYLIST_PATH"),
	"TYPE" => "FILE",
	"FD_TARGET" => "F",
	"FD_EXT" => $ext,
	"FD_UPLOAD" => true,
	"FD_USE_MEDIALIB" => true,
	"FD_MEDIALIB_TYPES" => Array('video', 'sound'),
	"DEFAULT" => "",
	"REFRESH" => "Y"
);

if ($arCurrentValues["USE_PLAYLIST"] == 'Y')
{
	$bPlaylistExists = strlen($arCurrentValues['PATH']) > 0 && file_exists($_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arCurrentValues['PATH']));
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

if ($type == 'flv')
{
	if($arCurrentValues["USE_PLAYLIST"]!='Y')
	{
		$arParams["PROVIDER"] = Array(
			"PARENT" => "BASE_SETTINGS",
			"NAME" => GetMessage("PC_PAR_PROVIDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"" => GetMessage("PC_PAR_PROVIDER_NONE"),
				"video" => GetMessage("PC_PAR_PROVIDER_VIDEO"),
				"http" => GetMessage("PC_PAR_PROVIDER_HTTP"),
				"rtmp" => GetMessage("PC_PAR_PROVIDER_RTMP"),
				"sound" => GetMessage("PC_PAR_PROVIDER_SOUND"),
				"image" => GetMessage("PC_PAR_PROVIDER_IMAGE")
			),
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			//"HIDDEN" => $hidden,
		);
	}

	$arParams["STREAMER"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_STREAMER"),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
}

if ($type == 'videojs' || $type == 'auto')
{
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
	if ($arCurrentValues["USE_PLAYLIST"] == "Y")
	{
		unset ($arParams["SIZE_TYPE"]["VALUES"]["auto"]);
	}
	if ($arCurrentValues["SIZE_TYPE"] == "absolute" || $arCurrentValues["SIZE_TYPE"] == "")
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
}
else
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

//APPEARANCE   -FLV-
if ($type == 'flv')
{
	$arParams["SKIN_PATH"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_SKIN_PATH"),
		"TYPE" => "FILE",
		"FD_TARGET" => "D",
		"FD_UPLOAD" => false,
		"DEFAULT" => "/bitrix/components/bitrix/player/mediaplayer/skins",
		"REFRESH" => "Y",
		"HIDDEN" => $hidden,
	);

	if ($arCurrentValues['SKIN_PATH'] == $defaultVideoJsSkinPath)
		$arCurrentValues['SKIN_PATH'] = "/bitrix/components/bitrix/player/mediaplayer/skins";
	$arSkins = getSkinsEx($arCurrentValues['SKIN_PATH'] ? $arCurrentValues['SKIN_PATH'] : "/bitrix/components/bitrix/player/mediaplayer/skins");

	$arParams["SKIN"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_SKIN"),
		"TYPE" => "CUSTOM",
		"JS_FILE" => "/bitrix/components/bitrix/player/js/prop_skin_selector.js",
		"JS_EVENT" => "ComponentPropsSkinSelector",
		"JS_DATA" => CUtil::PhpToJSObject(array($arSkins, array(
				'NoPreview' => GetMessage("PC_PAR_NO_PREVIEW")
			)
		)),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);

	$arParams["CONTROLBAR"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_CONTROLS"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'bottom' => GetMessage("PC_PAR_CONTROLS_BOTTOM"),
			'over' => GetMessage("PC_PAR_CONTROLS_OVER"),
			'none' => GetMessage("PC_PAR_CONTROLS_NONE")
		),
		"DEFAULT" => "bottom",
		"HIDDEN" => $hidden,
	);
	$arParams["WMODE"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_WMODE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'window' => GetMessage("PC_PAR_WMODE_WINDOW"),
			'opaque' => GetMessage("PC_PAR_WMODE_OPAQUE"),
			'transparent' => GetMessage("PC_PAR_WMODE_TRANSPARENT")
		),
		"DEFAULT" => "opaque",
		"HIDDEN" => $hidden,
	);
	if ($arCurrentValues['USE_PLAYLIST'] == 'Y')
	{
		$arParams["PLAYLIST"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'bottom' => GetMessage("PC_PAR_CONTROLS_BOTTOM"),
				//'over' => GetMessage("PC_PAR_CONTROLS_OVER"),
				'right' => GetMessage("PC_PAR_PLAYLIST_RIGHT"),
				'none' => GetMessage("PC_PAR_CONTROLS_NONE")
			),
			"DEFAULT" => "none",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_SIZE"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_SIZE"),
			"COLS" => 10,
			"DEFAULT" => "180",
			"HIDDEN" => $hidden,
		);
	}

	$arParams["LOGO"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_LOGO"),
		"TYPE" => "FILE",
		"FD_TARGET" => "F",
		"FD_EXT" => "png,gif,jpg,jpeg",
		"FD_UPLOAD" => true,
		"FD_USE_MEDIALIB" => true,
		"FD_MEDIALIB_TYPES" => Array('image'),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
	$arParams["LOGO_LINK"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_LOGO_LINK"),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
	$arParams["LOGO_POSITION"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_LOGO_POSITION"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'none' => GetMessage("PC_PAR_LOGO_POS_NONE"),
			'bottom-left' => GetMessage("PC_PAR_LOGO_POS_BOTTOM_LEFT"),
			'top-left' => GetMessage("PC_PAR_LOGO_POS_TOP_LEFT"),
			'top-right' => GetMessage("PC_PAR_LOGO_POS_TOP_RIGHT"),
			'bottom-right' => GetMessage("PC_PAR_LOGO_POS_BOTTOM_RIGHT")
		),
		"DEFAULT" => "none",
		"HIDDEN" => $hidden,
	);
	$addGroupPar = 'ADDITIONAL_SETTINGS';

	$arPluginList = array(
		'tweetit-1' => array(
			'name' => 'Tweet It',
			'flashvars' => array('tweetit.link' => '')
		),
		'fbit-1' => array(
			'name' => 'Facebook It',
			'flashvars' => array('fbit.link' => '')
		),
		'viral-2' => array(
			'name' => 'Viral',
			'test' => "qqq",
			'flashvars' => array(
				'viral.onpause' => 'false',
				'viral.oncomplete' => 'true',
				'viral.allowmenu' => 'false',
				'viral.functions' => 'all',
				'viral.link' => '',
				'viral.email_subject' => 'text',
				'viral.email_footer' => 'text',
				'viral.embed' => ''
			)
		),
		'flow-1' => array(
			'name' => 'Flow',
			'flashvars' => array(
				'flow.coverheight' => '100',
				'flow.coverwidth' => '150'
			)
		),
		'gapro-1' => array(
			'name' => 'Google Analytics Pro',
			'flashvars' => array(
				'gapro.accountid' => 'UA-XXXXXXX-X',
				'gapro.trackstarts' => 'true',
				'gapro.trackpercentage' => 'true',
				'gapro.tracktime' => 'true'
			)
		),
		'drelated-1' => array(
			'name' => 'D-Related',
			'flashvars' => array()
		),
		'hd' => array(
			'name' => 'HD',
			'flashvars' => array(
				'file' => '',
				'fullscreen' => 'true'
			)
		),
		'revolt-1' => array(
			'name' => 'Revolt',
			'flashvars' => array()
		),
		'yousearch-1' => array(
			'name' => 'YouSearch',
			'flashvars' => array()
		),
		'spectrumvisualizer-1' => array(
			'name' => 'Spectrum Visualizer'
		)
	);

	$arParams["PLUGINS"] = Array(
		"PARENT" => $addGroupPar,
		"NAME" => GetMessage("PC_PAR_PLUGINS"),
		"TYPE" => "LIST",
		"VALUES" => array(),
		"ADDITIONAL_VALUES" => "Y",
		"MULTIPLE"=> "Y",
		"DEFAULT" => array(),
		"REFRESH" => "Y",
		"HIDDEN" => $hidden
	);

	foreach ($arPluginList as $key => $arPlugin)
	{
		$arParams["PLUGINS"]["VALUES"][$key] = $arPlugin['name'];
		if ($arPlugin['default'])
			$arParams["PLUGINS"]["DEFAULT"][] = $key;
	}

	$arPlugins = isset($arCurrentValues['PLUGINS']) ? $arCurrentValues['PLUGINS'] : $arParams["PLUGINS"]["DEFAULT"];
	for ($j = 0, $n = count($arPlugins); $j < $n; $j++)
	{
		$pluginTitle = isset($arPluginList[$arPlugins[$j]]['name']) ? $arPluginList[$arPlugins[$j]]['name'] : trim($arPlugins[$j]);
		$pluginName = preg_replace("/[^a-zA-Z0-9_-]/i", "_", trim($arPlugins[$j]));

		if (strlen($pluginName) <= 0)
			continue;

		$defValue = '';
		if (isset($arPluginList[$pluginName]['flashvars']))
		{
			foreach ($arPluginList[$pluginName]['flashvars'] as $varName => $varVal)
				$defValue .= $varName.'='.$varVal."\n";
		}

		$arParams["PLUGINS_".strtoupper($pluginName)] = Array(
			"PARENT" => $addGroupPar,
			"NAME" => GetMessage("PC_PAR_PLUGIN_NAME", array('#PLUGIN_NAME#' => $pluginTitle)),
			"ROWS" => 3,
			"COLS" => 50,
			"DEFAULT" => $defValue,
			"HIDDEN" => $hidden
		);
	}

	$arParams["ADDITIONAL_FLASHVARS"] = Array(
		"PARENT" => $addGroupPar,
		"NAME" => GetMessage("PC_PAR_ADDITIONAL_FLASHVARS"),
		"ROWS" => 3,
		"COLS" => 50,
		"DEFAULT" => "",
		"HIDDEN" => $hidden
	);
}

if ($type == 'wmv')
{
	$arParams["WMODE_WMV"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_WMODE_WMV"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'window' => GetMessage("PC_PAR_WMODE_WINDOW"),
			'windowless' => GetMessage("PC_PAR_WMODE_TRANSPARENT")
		),
		"DEFAULT" => "window",
		"HIDDEN" => $hidden,
	);

	$arParams["SHOW_CONTROLS"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_SHOW_CONTROLS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
		"HIDDEN" => $hidden,
	);
	if ($arCurrentValues['USE_PLAYLIST'] == 'Y')
	{
		$arParams["PLAYLIST"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'bottom' => GetMessage("PC_PAR_CONTROLS_BOTTOM"),
				'right' => GetMessage("PC_PAR_PLAYLIST_RIGHT")
			),
			"DEFAULT" => "right",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_TYPE"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'asx' => 'ASX',
				'atom' => 'ATOM',
				'rss' => 'RSS',
				'xspf' => 'XSPF'
			),
			"DEFAULT" => "xspf",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_SIZE"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_SIZE"),
			"COLS" => 10,
			"DEFAULT" => "180",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_PREVIEW_WIDTH"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_PREVIEW_WIDTH"),
			"COLS" => 4,
			"DEFAULT" => "64",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_PREVIEW_HEIGHT"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_PREVIEW_HEIGHT"),
			"COLS" => 4,
			"DEFAULT" => "48",
			"HIDDEN" => $hidden,
		);
	}

	if ($arCurrentValues['SHOW_CONTROLS'] != 'N')
	{
		$arParams["SHOW_DIGITS"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_SHOW_DIGITS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => $hidden,
		);
		$arParams["CONTROLS_BGCOLOR"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_BGCOLOR"),
			"COLS" => 10,
			"DEFAULT" => "FFFFFF",
			//"TYPE" => "COLORPICKER",
			"HIDDEN" => $hidden,
		);
		$arParams["CONTROLS_COLOR"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_COLOR"),
			"COLS" => 10,
			"DEFAULT" => "000000",
			"HIDDEN" => $hidden,
		);
		$arParams["CONTROLS_OVER_COLOR"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_OVER_COLOR"),
			"COLS" => 10,
			"DEFAULT" => "000000",
			"HIDDEN" => $hidden,
		);
		$arParams["SCREEN_COLOR"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_SCREEN_COLOR"),
			"COLS" => 10,
			"DEFAULT" => "000000",
			"HIDDEN" => $hidden,
		);
	}
}

if ($type == 'videojs' || $type == 'auto')
{
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
	if ($arCurrentValues['USE_PLAYLIST'] == 'Y')
	{
		$arParams['PLAYLIST_HIDE'] = Array (
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_HIDE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		);
		$arParams["PLAYLIST_SIZE"] = Array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_SIZE"),
			"COLS" => 10,
			"DEFAULT" => "190",
			"HIDDEN" => $arCurrentValues['PLAYLIST_HIDE'] == "Y" ? "Y" : "N",
		);
		$arParams["PLAYLIST_NUMBER"] = Array (
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_NUM"),
			"TYPE" => "STRING",
			"DEFAULT" => 3,
			"HIDDEN" => $arCurrentValues['PLAYLIST_HIDE'] == "Y" ? "Y" : "N",
		);
	}

	$arParams["SKIN_PATH"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_SKIN_PATH"),
		"TYPE" => "FILE",
		"FD_TARGET" => "D",
		"FD_UPLOAD" => false,
		"DEFAULT" => $defaultVideoJsSkinPath,
		"REFRESH" => "Y",
		"HIDDEN" => $hidden,
	);

	if ($arCurrentValues['SKIN_PATH'] == "/bitrix/components/bitrix/player/mediaplayer/skins")
		$arCurrentValues['SKIN_PATH'] = $defaultVideoJsSkinPath;

	$arSkins = getSkinsEx($arCurrentValues['SKIN_PATH'] ? $arCurrentValues['SKIN_PATH'] : $defaultVideoJsSkinPath);

	$arParams["SKIN"] = Array(
		"PARENT" => "APPEARANCE",
		"NAME" => GetMessage("PC_PAR_SKIN"),
		"TYPE" => "CUSTOM",
		"JS_FILE" => "/bitrix/components/bitrix/player/js/prop_skin_selector.js",
		"JS_EVENT" => "ComponentPropsSkinSelector",
		"JS_DATA" => CUtil::PhpToJSObject(array($arSkins, array(
			'NoPreview' => GetMessage("PC_PAR_NO_PREVIEW")
		),
			array ('defaultImage' => '/bitrix/components/bitrix/player/images/default_skin_videojs.jpg')
		)),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
}

// PLAYBACK
$playback_parent = 'PLAYBACK';
$arParams["AUTOSTART"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_AUTOSTART"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);

if ($arCurrentValues['AUTOSTART'] != 'Y')
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
if ($type == 'flv')
{
	$arParams["REPEAT"]["VALUES"]["list"] = GetMessage("PC_PAR_REPEAT_LIST");
	$arParams["REPEAT"]["VALUES"]["single"] = GetMessage("PC_PAR_REPEAT_SINGLE");
}
if ($type == 'videojs' || $type == 'auto')
{
	if ($arCurrentValues['USE_PLAYLIST'] == "Y")
	{
		unset ($arParams["REPEAT"]);
	}
}
$arParams["VOLUME"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_VOLUME"),
	"COLS" => 10,
	"DEFAULT" => "90"
);

if ($type == 'videojs' || $type == 'auto')
{
	$arParams["MUTE"] = Array(
		"PARENT" => $playback_parent,
		"NAME" => GetMessage("PC_PAR_MUTE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden,
	);
	if ($arCurrentValues['USE_PLAYLIST'] != 'Y')
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
}

if ($type == 'flv')
{
	//$arParams["DISPLAY_CLICK"] = Array(
	//	"PARENT" => "PLAYBACK",
	//	"NAME" => GetMessage("PC_PAR_DISPLAY_CLICK"),
	//	"TYPE" => "LIST",
	//	"VALUES" => array(
	//		'play' => GetMessage("PC_PAR_DISPLAY_CLICK_PLAY"),
	//		'link' => GetMessage("PC_PAR_DISPLAY_CLICK_LINK"),
	//		'fullscreen' => GetMessage("PC_PAR_DISPLAY_CLICK_FULLSCREEN"),
	//		'none' => GetMessage("PC_PAR_DISPLAY_CLICK_NONE"),
	//		'mute' => GetMessage("PC_PAR_DISPLAY_CLICK_MUTE"),
	//		'next' => GetMessage("PC_PAR_DISPLAY_CLICK_NEXT"),
	//	),
	//	"DEFAULT" => 'play',
	//	"HIDDEN" => $hidden,
	//);

	$arParams["MUTE"] = Array(
		"PARENT" => $playback_parent,
		"NAME" => GetMessage("PC_PAR_MUTE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden,
	);

	if ($arCurrentValues['USE_PLAYLIST'] == 'Y')
	{
		$arParams["SHUFFLE"] = Array(
			"PARENT" => $playback_parent,
			"NAME" => GetMessage("PC_PAR_SHUFFLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"HIDDEN" => $hidden,
		);
		$arParams["START_ITEM"] = Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("PC_PAR_START_FROM"),
			"TYPE" => "STRING",
			"DEFAULT" => "0",
			"HIDDEN" => $hidden,
		);
	}
}

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

if ($type == 'wmv')
{
	$arParams["BUFFER_LENGTH"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_BUFFER_LENGTH"),
		"COLS" => "10",
		"DEFAULT" => "10",
		"HIDDEN" => $hidden,
	);
	$arParams["DOWNLOAD_LINK"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_DOWNLOAD_LINK"),
		"COLS" => "40",
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
	$arParams["DOWNLOAD_LINK_TARGET"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_LINK_TARGET"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'_self' => GetMessage("PC_PAR_LINK_TARGET_SELF"),
			'_blank' => GetMessage("PC_PAR_LINK_TARGET_BLANK")
		),
		"DEFAULT" => '_self',
		"HIDDEN" => $hidden,
	);

	$arParams["ADDITIONAL_WMVVARS"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_ADDITIONAL_WMVVARS"),
		"ROWS" => 3,
		"COLS" => 50,
		"DEFAULT" => "",
		"HIDDEN" => $hidden
	);
}

if ($type == 'flv')
{
	$arParams["BUFFER_LENGTH"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_BUFFER_LENGTH"),
		"COLS" => "10",
		"DEFAULT" => "10",
		"HIDDEN" => $hidden,
	);
	$arParams["ALLOW_SWF"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_ALLOW_SWF"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden,
	);
}

$arComponentParameters["PARAMETERS"] = $arParams;
?>