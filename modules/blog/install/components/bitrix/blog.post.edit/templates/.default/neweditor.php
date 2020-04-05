<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:main.post.form",
	"",
	Array(
		"FORM_ID" => $component->createPostFormId(),
		"SHOW_MORE" => "Y",
		"PARSER" => blogTextParser::GetEditorToolbar(array("EDITOR_FULL" => "Y")),
		"LHE" => array(
			'id' => $component->createEditorId(),
			'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'],
			'bResizable' => true,
			'bAutoResize' => true,
			"documentCSS" => "body {color:#434343; font-size: 14px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 20px;}",
			'setFocusAfterShow' => false,
		),
		
		"ADDITIONAL" => array(),
		
		"TEXT" => Array(
			"ID" => "POST_MESSAGE",
			"NAME" => "POST_MESSAGE",
			"VALUE" => isset($arResult['Post']["~DETAIL_TEXT"]) ? $arResult['Post']["~DETAIL_TEXT"] : "",
			"SHOW" => "Y",
			"HEIGHT" => "200px"
		),
		"UPLOAD_FILE_PARAMS" => array('width' => $arParams["IMAGE_MAX_WIDTH"], 'height' => $arParams["IMAGE_MAX_HEIGHT"]),
		"PROPERTIES" => array(
			array_merge(
				(is_array($arResult["POST_PROPERTIES"]["DATA"][CBlogPost::UF_NAME]) ? $arResult["POST_PROPERTIES"]["DATA"][CBlogPost::UF_NAME] : array()),
				(is_array($_POST[CBlogPost::UF_NAME]) ? array("VALUE" => $_POST[CBlogPost::UF_NAME]) : array()),
				array("POSTFIX" => "file")
			),
			array_key_exists("UF_BLOG_POST_URL_PRV", $arResult["POST_PROPERTIES"]["DATA"]) ?
				array_merge(
					$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_URL_PRV"],
					array(
						'ELEMENT_ID' => 'url_preview_'.$component->createEditorId(),
						'STYLE' => 'margin: 0 18px'
					)
				)
				:
				array()
		),
		"SMILES" => COption::GetOptionInt("blog", "smile_gallery_id", 0),
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);