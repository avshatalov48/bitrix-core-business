<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("advertising"))
	return;

$arTemplateParameters = array(
	"PARAMETERS" => array(
		"IMG" => Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_IMG"),
			"TYPE" => "IMAGE",
			"DEFAULT" => "",
			"SORT" => 10
		),
		"LINK_URL" => Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_LINK_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 20
		),
		"LINK_TITLE" => Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_LINK_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 30
		),
		"LINK_TARGET" => Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'' => GetMessage("ADV_JSSOR_PARAMETER_NO"),
				'_self' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_SELF"),
				'_blank' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_BLANK"),
				'_parent' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_PARENT"),
				'_top' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_TOP"),
			),
			"DEFAULT" => "left",
			"SORT" => 40
		)
	)
);
if ($arCurrentValues['EXTENDED_MODE'] == 'Y')
{
	$arTemplateParameters["PARAMETERS"]["HEADING"] = Array(
		"NAME" => GetMessage("ADV_JSSOR_PARAMETER_HEADING"),
		"TYPE" => "HTML",
		"DEFAULT" => '<div u="caption" t="*" style="position: absolute; top: 10%; left: 10%;background-color:white;padding:2%">'.GetMessage("ADV_JSSOR_PARAMETER_HEADING").'</div>',
		"SORT" => 50
	);
}
if ($arCurrentValues['EXTENDED_MODE'] == 'N')
{
	$arTemplateParameters["PARAMETERS"]["PRESET"] = Array(
		"NAME" => GetMessage("ADV_JSSOR_PARAMETER_PRESET"),
		"TYPE" => "PRESET",
		"IMAGES" => array(
			'1' => '/bitrix/themes/.default/icons/advertising/preset1.jpg',
			'2' => '/bitrix/themes/.default/icons/advertising/preset2.jpg',
			'3' => '/bitrix/themes/.default/icons/advertising/preset3.jpg',
			'4' => '/bitrix/themes/.default/icons/advertising/preset4.jpg'
		),
		"DEFAULT" => 1,
		"SORT" => 45
	);
	$arTemplateParameters["PARAMETERS"]["HEADING_SHOW"] = Array(
		"NAME" => GetMessage("ADV_JSSOR_PARAMETER_HEADING_SHOW"),
		"TYPE" => "CHECKBOX",
		"REFRESH" => 'Y',
		"DEFAULT" => 'N',
		"SORT" => 50
	);
	if ($arCurrentValues['HEADING_SHOW'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["HEADING"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_HEADING_TEXT"),
			"TYPE" => "STRING",
			"ROWS" => "4",
			"COLS" => "49",
			"DEFAULT" => GetMessage("ADV_JSSOR_PARAMETER_HEADING"),
			"SORT" => 60
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_FONT_SIZE"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_FONT_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "30",
			"SORT" => 70
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 80
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "000000",
			"SORT" => 110
		);
		$arTemplateParameters["PARAMETERS"]["HEADING_BG_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BG_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 120
		);
	}
	$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_SHOW"] = Array(
		"NAME" => GetMessage("ADV_JSSOR_PARAMETER_ANNOUNCEMENT_SHOW"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => 'Y',
		"SORT" => 130
	);
	if ($arCurrentValues['ANNOUNCEMENT_SHOW'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_ANNOUNCEMENT_TEXT"),
			"TYPE" => "STRING",
			"ROWS" => "4",
			"COLS" => "49",
			"DEFAULT" => GetMessage("ADV_JSSOR_PARAMETER_ANNOUNCEMENT"),
			"SORT" => 140
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_FONT_SIZE"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_FONT_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "14",
			"SORT" => 150
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 160
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "000000",
			"SORT" => 190
		);
		$arTemplateParameters["PARAMETERS"]["ANNOUNCEMENT_BG_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BG_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 200
		);
	}
	$arTemplateParameters["PARAMETERS"]["BUTTON"] = Array(
		"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BUTTON"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 205
	);
	if ($arCurrentValues['BUTTON'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["BUTTON_TEXT"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BUTTON_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("ADV_JSSOR_PARAMETER_BUTTON_TEXT_DEF"),
			"SORT" => 210
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_FONT_COLOR"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_FONT_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "FFFFFF",
			"SORT" => 220
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_BG_COLOR"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BUTTON_BG_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "E6A323",
			"SORT" => 225
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_URL"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_BUTTON_LINK_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 230
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_TITLE"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_LINK_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"SORT" => 240
		);
		$arTemplateParameters["PARAMETERS"]["BUTTON_LINK_TARGET"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'' => GetMessage("ADV_JSSOR_PARAMETER_NO"),
				'_self' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_SELF"),
				'_blank' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_BLANK"),
				'_parent' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_PARENT"),
				'_top' => GetMessage("ADV_JSSOR_PARAMETER_LINK_TARGET_TOP")
			),
			"DEFAULT" => "left",
			"SORT" => 250
		);
	}
	$arTemplateParameters["PARAMETERS"]["ANIMATION"] = Array(
		"NAME" => GetMessage("ADV_JSSOR_PARAMETER_ANIMATION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 260
	);
	if ($arCurrentValues['ANIMATION'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["ANIMATION_EFFECT"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_ANIMATION_EFFECT"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'L' => GetMessage("ADV_JSSOR_PARAMETER_ANIMATION_EFFECT_L"),
				'R' => GetMessage("ADV_JSSOR_PARAMETER_ANIMATION_EFFECT_R"),
				'FADE' => GetMessage("ADV_JSSOR_PARAMETER_ANIMATION_EFFECT_FADE")
			),
			"SORT" => 270
		);
		$arTemplateParameters["PARAMETERS"]["ANIMATION_DELAY"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_ANIMATION_DELAY"),
			"TYPE" => "STRING",
			"DEFAULT" => "500",
			"SORT" => 280
		);
	}
	$arTemplateParameters["PARAMETERS"]["OVERLAY"] = Array(
		"NAME" => GetMessage("ADV_JSSOR_PARAMETER_OVERLAY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"SORT" => 290
	);
	if ($arCurrentValues['OVERLAY'] == 'Y')
	{
		$arTemplateParameters["PARAMETERS"]["OVERLAY_COLOR"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_OVERLAY_COLOR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "CCCCCC",
			"SORT" => 300
		);
		$arTemplateParameters["PARAMETERS"]["OVERLAY_OPACITY"] = Array(
			"NAME" => GetMessage("ADV_JSSOR_PARAMETER_OVERLAY_OPACITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "100",
			"SORT" => 310
		);
	}
}