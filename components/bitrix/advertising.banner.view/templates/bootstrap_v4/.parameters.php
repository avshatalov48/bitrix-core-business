<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("advertising"))
	return;

$arTemplateParameters = array(
	"PARAMETERS" => array(
		"BACKGROUND" => Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BACKGROUND"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'' => GetMessage("ADV_BS_PARAMETER_NO"),
				'image' => GetMessage("ADV_BS_PARAMETER_BACKGROUND_IMAGE"),
				'stream' => GetMessage("ADV_BS_PARAMETER_BACKGROUND_YT"),
				'video' => GetMessage("ADV_BS_PARAMETER_BACKGROUND_FILE")
			),
			"REFRESH" => 'Y',
			"SORT" => 10
		)
	)
);
if ($arCurrentValues['BACKGROUND'] == 'image')
{
	$arTemplateParameters["PARAMETERS"]["IMG"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_IMG"),
		"TYPE" => "IMAGE",
		"DEFAULT" => "Y",
		"SORT" => 20
	);
}
if ($arCurrentValues['BACKGROUND'] == 'stream')
{
	$arTemplateParameters["PARAMETERS"]["STREAM"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_STREAM"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"SORT" => 20
	);
	$arTemplateParameters["PARAMETERS"]["STREAM_MUTE"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_STREAM_MUTE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"SORT" => 40
	);
}
if ($arCurrentValues['BACKGROUND'] == 'video')
{
	$arTemplateParameters["PARAMETERS"]["VIDEO_MP4"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_VIDEO_MP4"),
		"TYPE" => "FILE",
		"DEFAULT" => "",
		"SORT" => 20
	);
	$arTemplateParameters["PARAMETERS"]["VIDEO_WEBM"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_VIDEO_WEBM"),
		"TYPE" => "FILE",
		"DEFAULT" => "",
		"SORT" => 30
	);
	$arTemplateParameters["PARAMETERS"]["VIDEO_MUTE"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_STREAM_MUTE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"SORT" => 40
	);
}
$arTemplateParameters["PARAMETERS"]["LINK_URL"] = Array(
	"NAME" => GetMessage("ADV_BS_PARAMETER_LINK_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "",
	"SORT" => 50
);
$arTemplateParameters["PARAMETERS"]["LINK_TITLE"] = Array(
	"NAME" => GetMessage("ADV_BS_PARAMETER_LINK_TITLE"),
	"TYPE" => "STRING",
	"DEFAULT" => "",
	"SORT" => 60
);
$arTemplateParameters["PARAMETERS"]["LINK_TARGET"] = Array(
	"NAME" => GetMessage("ADV_BS_PARAMETER_LINK_TARGET"),
	"TYPE" => "LIST",
	"VALUES" => array(
		'' => GetMessage("ADV_BS_PARAMETER_NO"),
		'_self' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_SELF"),
		'_blank' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_BLANK"),
		'_parent' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_PARENT"),
		'_top' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_TOP")
	),
	"DEFAULT" => "left",
	"SORT" => 70
);
if ($arCurrentValues['EXTENDED_MODE'] == 'Y')
{
	$arTemplateParameters["PARAMETERS"]["HEADING"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_HEADING"),
		"TYPE" => "HTML",
		"DEFAULT" => GetMessage("ADV_BS_PARAMETER_HEADING"),
		"SORT" => 90
	);
}
if ($arCurrentValues['EXTENDED_MODE'] == 'N')
{
	$arTemplateParameters["PARAMETERS"]["PRESET"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_PRESET"),
		"TYPE" => "PRESET",
		"IMAGES" => array(
			'1' => '/bitrix/themes/.default/icons/advertising/preset1.jpg',
			'2' => '/bitrix/themes/.default/icons/advertising/preset2.jpg',
			'3' => '/bitrix/themes/.default/icons/advertising/preset3.jpg',
			'4' => '/bitrix/themes/.default/icons/advertising/preset4.jpg'
		),
		"DEFAULT" => 1,
		"SORT" => 75
	);
	$arTemplateParameters["PARAMETERS"]["HEADING_SHOW"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_HEADING_SHOW"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => 'Y',
		"SORT" => 80
	);
	if ($arCurrentValues['HEADING_SHOW'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["HEADING"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_HEADING_TEXT"),
			"TYPE" => "STRING",
			"ROWS" => "4",
			"COLS" => "49",
			"DEFAULT" => GetMessage("ADV_BS_PARAMETER_HEADING"),
			"SORT" => 90
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_FONT_SIZE"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_FONT_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "30",
			"TOOLTIP" => GetMessage("ADV_BS_PARAMETER_FONT_SIZE_TOOLTIP"),
			"SORT" => 100
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 110
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "000000",
			"SORT" => 150
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_BG_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BG_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 160
		);
	}
	$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_SHOW"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_ANNOUNCEMENT_SHOW"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => 'Y',
		"SORT" => 170
	);
	if ($arCurrentValues['ANNOUNCEMENT_SHOW'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_ANNOUNCEMENT_TEXT"),
			"TYPE" => "STRING",
			"ROWS" => "4",
			"COLS" => "49",
			"DEFAULT" => GetMessage("ADV_BS_PARAMETER_ANNOUNCEMENT"),
			"SORT" => 180
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_FONT_SIZE"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_FONT_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "14",
			"SORT" => 190
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 200
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "000000",
			"SORT" => 240
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_BG_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BG_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 250
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_MOBILE_HIDE"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_ANNOUNCEMENT_MOBILE_HIDE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"SORT" => 255
		);
	}
	$arTemplateParameters["PARAMETERS"]["BUTTON"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_BUTTON"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 260
	);
	if ($arCurrentValues['BUTTON'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["BUTTON_TEXT"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BUTTON_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("ADV_BS_PARAMETER_BUTTON_TEXT_DEF"),
			"SORT" => 270
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 280
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BUTTON_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "E6A323",
			"SORT" => 290
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_URL"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_BUTTON_LINK_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 300
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_TITLE"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_LINK_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 310
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_TARGET"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_LINK_TARGET"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'' => GetMessage("ADV_BS_PARAMETER_NO"),
				'_self' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_SELF"),
				'_blank' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_BLANK"),
				'_parent' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_PARENT"),
				'_top' => GetMessage("ADV_BS_PARAMETER_LINK_TARGET_TOP")
			),
			"DEFAULT" => "left",
			"SORT" => 320
		);
	}
	$arTemplateParameters["PARAMETERS"]["ANIMATION"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_ANIMATION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 330
	);
	if ($arCurrentValues['ANIMATION'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["ANIMATION_DURATION"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_ANIMATION_DURATION"),
			"TYPE" => "STRING",
			"DEFAULT" => "500",
			"SORT" => 340
		);
		$arTemplateParameters["PARAMETERS"]["ANIMATION_DELAY"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_ANIMATION_DELAY"),
			"TYPE" => "STRING",
			"DEFAULT" => "500",
			"SORT" => 350
		);
	}
	$arTemplateParameters["PARAMETERS"]["OVERLAY"] = Array(
		"NAME" => GetMessage("ADV_BS_PARAMETER_OVERLAY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 360
	);
	if ($arCurrentValues['OVERLAY'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["OVERLAY_COLOR"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_OVERLAY_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "CCCCCC",
			"SORT" => 370
		);
		$arTemplateParameters["PARAMETERS"]["OVERLAY_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_BS_PARAMETER_OVERLAY_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 380
		);
	}
}