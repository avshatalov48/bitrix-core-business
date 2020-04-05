<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$LHEditorId = 'LHEIdeaId';
$formId = $arResult['FORM_NAME'];

$bbCode = true;
if (
	$arResult["allow_html"] == "Y"
	&& (
		(
			$arResult["PostToShow"]["DETAIL_TEXT_TYPE"] == "html"
			&& $_REQUEST["load_editor"] != "N"
		)
		|| $_REQUEST["load_editor"] == "Y"
	)
)
{
	$bbCode = false;
}

// Detect necessity of first convertion content from BB-code to HTML in editor.
$bConvertContentFromBBCodes = (
	!$bbCode
	&& $_REQUEST["load_editor"] == "Y"
	&& !isset($_REQUEST['preview'])
	&& !isset($_REQUEST['save'])
	&& !isset($_REQUEST['apply'])
	&& !isset($_REQUEST['draft'])
);
?><div id="edit-post-text"><?
$APPLICATION->IncludeComponent(
	"bitrix:main.post.form",
	"",
	Array(
		"FORM_ID" => $formId,
		"SHOW_MORE" => "Y",
		"PARSER" => blogTextParser::GetEditorToolbar(array("blog" => $arResult["Blog"])),
		"BUTTONS" => array("UploadImage"),
		"LHE" => array(
			'id' => $LHEditorId,
			'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'],
			'bResizable' => $arParams['EDITOR_RESIZABLE'],
			'bAutoResize' => true,
			"documentCSS" => "body {color:#434343; font-size: 14px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 20px;}",
			'setFocusAfterShow' => false,
			'BBCode' => $bbCode,
			'bConvertContentFromBBCodes' => $bConvertContentFromBBCodes,
			'controlsMap' => array(
				array('id' => 'Bold',  'compact' => true, 'sort' => 10),
				array('id' => 'Italic',  'compact' => true, 'sort' => 20),
				array('id' => 'Underline',  'compact' => true, 'sort' => 30),
				array('id' => 'Strikeout',  'compact' => true, 'sort' => 40),
				array('id' => 'RemoveFormat',  'compact' => true, 'sort' => 50),
				array('id' => 'Color',  'compact' => true, 'sort' => 60),
				array('id' => 'FontSelector',  'compact' => false, 'sort' => 70),
				array('id' => 'FontSize',  'compact' => false, 'sort' => 80),
				array('separator' => true, 'compact' => false, 'sort' => 90),
				array('id' => 'OrderedList',  'compact' => true, 'sort' => 100),
				array('id' => 'UnorderedList',  'compact' => true, 'sort' => 110),
				array('id' => 'AlignList', 'compact' => false, 'sort' => 120),
				array('separator' => true, 'compact' => false, 'sort' => 130),
				array('id' => 'InsertLink',  'compact' => true, 'sort' => 140, 'wrap' => 'bx-b-link-'.$formId),
				array('id' => 'InsertImage',  'compact' => false, 'sort' => 150),
				array('id' => 'InsertVideo',  'compact' => true, 'sort' => 160, 'wrap' => 'bx-b-video-'.$formId),
				array('id' => 'InsertTable',  'compact' => false, 'sort' => 170),
				array('id' => 'Code',  'compact' => true, 'sort' => 180),
				array('id' => 'Quote',  'compact' => true, 'sort' => 190, 'wrap' => 'bx-b-quote-'.$formId),
				array('separator' => true, 'compact' => false, 'sort' => 200),
				array('id' => 'BbCode',  'compact' => true, 'sort' => 220),
				array('id' => 'More',  'compact' => true, 'sort' => 230),
			),
		),
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
				(is_array($arResult["POST_PROPERTIES"]["DATA"][CBlogPost::UF_NAME]) ?
					$arResult["POST_PROPERTIES"]["DATA"][CBlogPost::UF_NAME] : array()),
				(is_array($_POST[CBlogPost::UF_NAME]) ? array("VALUE" => $_POST[CBlogPost::UF_NAME]) : array()),
				array("POSTFIX" => "file")),
			array_key_exists("UF_BLOG_POST_URL_PRV", $arResult["POST_PROPERTIES"]["DATA"]) ?
				array_merge(
					$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_URL_PRV"],
					array('ELEMENT_ID' => 'url_preview_'.$LHEditorId, 'STYLE' => 'margin: 0 18px')) : array()
		),
		"SMILES" => COption::GetOptionInt("blog", "smile_gallery_id", 0),
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?></div><?