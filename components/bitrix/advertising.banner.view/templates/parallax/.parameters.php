<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("advertising"))
	return;

$arTemplateParameters = array(
	"PARAMETERS" => array(
		"IMG" => Array(
		"NAME" => GetMessage("ADV_PARALL_PARAMETER_IMG"),
		"TYPE" => "IMAGE",
		"DEFAULT" => "Y",
		"SORT" => 10
		)
	)
);
$arTemplateParameters["PARAMETERS"]["LINK_URL"] = Array(
	"NAME" => GetMessage("ADV_PARALL_PARAMETER_LINK_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "",
	"SORT" => 20
);
$arTemplateParameters["PARAMETERS"]["LINK_TITLE"] = Array(
	"NAME" => GetMessage("ADV_PARALL_PARAMETER_LINK_TITLE"),
	"TYPE" => "STRING",
	"DEFAULT" => "",
	"SORT" => 30
);
$arTemplateParameters["PARAMETERS"]["LINK_TARGET"] = Array(
	"NAME" => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET"),
	"TYPE" => "LIST",
	"VALUES" => array(
		'' => GetMessage("ADV_PARALL_PARAMETER_NO"),
		'_self' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_SELF"),
		'_blank' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_BLANK"),
		'_parent' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_PARENT"),
		'_top' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_TOP")
	),
	"DEFAULT" => "left",
	"SORT" => 40
);
if ($arCurrentValues['EXTENDED_MODE'] == 'Y')
{
	$arTemplateParameters["PARAMETERS"]["HEADING"] = Array(
		"NAME" => GetMessage("ADV_PARALL_PARAMETER_HEADING"),
		"TYPE" => "HTML",
		"DEFAULT" => GetMessage("ADV_PARALL_PARAMETER_HEADING"),
		"SORT" => 50
	);
}
if ($arCurrentValues['EXTENDED_MODE'] == 'N')
{
	$arTemplateParameters["PARAMETERS"]["PRESET"] = Array(
		"NAME" => GetMessage("ADV_PARALL_PARAMETER_PRESET"),
		"TYPE" => "PRESET",
		"IMAGES" => array(
			'1' => '/bitrix/themes/.default/icons/advertising/preset1.jpg',
			'2' => '/bitrix/themes/.default/icons/advertising/preset2.jpg',
			'3' => '/bitrix/themes/.default/icons/advertising/preset3.jpg',
			'4' => '/bitrix/themes/.default/icons/advertising/preset4.jpg'
		),
		"DEFAULT" => 1,
		"SORT" => 60
	);
	$arTemplateParameters["PARAMETERS"]["HEADING_SHOW"] = Array(
		"NAME" => GetMessage("ADV_PARALL_PARAMETER_HEADING_SHOW"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => 'Y',
		"SORT" => 70
	);
	if ($arCurrentValues['HEADING_SHOW'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["HEADING"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_HEADING_TEXT"),
			"TYPE" => "STRING",
			"ROWS" => "4",
			"COLS" => "49",
			"DEFAULT" => GetMessage("ADV_PARALL_PARAMETER_HEADING"),
			"SORT" => 80
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_FONT_SIZE"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_FONT_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "30",
			"TOOLTIP" => GetMessage("ADV_PARALL_PARAMETER_FONT_SIZE_TOOLTIP"),
			"SORT" => 90
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 100
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "000000",
			"SORT" => 110
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_BG_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_BG_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 120
		);
	}
	$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_SHOW"] = Array(
		"NAME" => GetMessage("ADV_PARALL_PARAMETER_ANNOUNCEMENT_SHOW"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => 'Y',
		"SORT" => 130
	);
	if ($arCurrentValues['ANNOUNCEMENT_SHOW'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_ANNOUNCEMENT_TEXT"),
			"TYPE" => "STRING",
			"ROWS" => "4",
			"COLS" => "49",
			"DEFAULT" => GetMessage("ADV_PARALL_PARAMETER_ANNOUNCEMENT"),
			"SORT" => 140
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_FONT_SIZE"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_FONT_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "14",
			"SORT" => 150
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 160
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "000000",
			"SORT" => 170
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_BG_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_BG_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 180
		);
	}
	$arTemplateParameters["PARAMETERS"]["BUTTON"] = Array(
		"NAME" => GetMessage("ADV_PARALL_PARAMETER_BUTTON"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 200
	);
	if ($arCurrentValues['BUTTON'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["BUTTON_TEXT"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_BUTTON_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("ADV_PARALL_PARAMETER_BUTTON_TEXT_DEF"),
			"SORT" => 210
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 220
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_BUTTON_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "E6A323",
			"SORT" => 230
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_URL"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_BUTTON_LINK_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 240
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_TITLE"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_LINK_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 250
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_TARGET"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'' => GetMessage("ADV_PARALL_PARAMETER_NO"),
				'_self' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_SELF"),
				'_blank' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_BLANK"),
				'_parent' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_PARENT"),
				'_top' => GetMessage("ADV_PARALL_PARAMETER_LINK_TARGET_TOP")
			),
			"DEFAULT" => "left",
			"SORT" => 260
		);
	}
	$arTemplateParameters["PARAMETERS"]["OVERLAY"] = Array(
		"NAME" => GetMessage("ADV_PARALL_PARAMETER_OVERLAY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 270
	);
	if ($arCurrentValues['OVERLAY'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["OVERLAY_COLOR"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_OVERLAY_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "CCCCCC",
			"SORT" => 280
		);
		$arTemplateParameters["PARAMETERS"]["OVERLAY_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_PARALL_PARAMETER_OVERLAY_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 290
		);
	}
}

$arTemplateParameters["SETTINGS"]["MULTIPLE"] = 'N';