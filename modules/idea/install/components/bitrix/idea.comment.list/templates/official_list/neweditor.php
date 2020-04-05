<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$LHEditorId = 'LHEBlogCom';

$APPLICATION->IncludeComponent(
	"bitrix:main.post.form",
	"",
	Array(
		"FORM_ID" => $arResult['FORM_NAME'],
		"SHOW_MORE" => "Y",
		"PARSER" => blogTextParser::GetEditorToolbar(array('blog' => $arResult['Blog'])),
		"LHE" => array(
			'id' => $LHEditorId,
			'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'],
			'bResizable' => true,
			'bAutoResize' => true,
			"documentCSS" => "body {color:#434343; font-size: 14px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 20px;}",
			'setFocusAfterShow' => false,
			'jsObjName' => 'oBlogComLHE'
		),

		"ADDITIONAL" => array(),

		"TEXT" => Array(
			"ID" => "comment",
			"NAME" => "comment",
			"VALUE" => isset($arResult['Post']["~DETAIL_TEXT"]) ? $arResult['Post']["~DETAIL_TEXT"] : "",
			"SHOW" => "Y",
			"HEIGHT" => "200px"
		),
		"UPLOAD_FILE_PARAMS" => array('width' => $arParams["IMAGE_MAX_WIDTH"], 'height' => $arParams["IMAGE_MAX_HEIGHT"]),
		"SMILES" => COption::GetOptionInt("blog", "smile_gallery_id", 0),
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);