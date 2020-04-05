<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$postFormParams = Array(
	"FORM_ID" => $component->createPostFormId(),
	"SHOW_MORE" => "Y",
	"PARSER" => blogTextParser::GetEditorToolbar(array('blog' => $arResult['Blog']), $arResult),
	"LHE" => array(
		'id' => $component->createEditorId(),
		'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'],
		'bResizable' => true,
		'bAutoResize' => true,
		"documentCSS" => "body {color:#434343; font-size: 14px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 20px;}",
		'setFocusAfterShow' => false,
		'ctrlEnterHandler' => 'blogCommentCtrlEnterHandler',
		"height" => 80,
	),
	"BUTTONS" => blogTextParser::getEditorButtons($arResult['Blog'], $arResult),
	"ADDITIONAL" => array(),
	"TEXT" => Array(
		"ID" => "POST_MESSAGE",
		"NAME" => "comment",
		"VALUE" => "",
		"HEIGHT" => "80px"
	),
	"UPLOAD_FILE_PARAMS" => array('width' => $arParams["IMAGE_MAX_WIDTH"], 'height' => $arParams["IMAGE_MAX_HEIGHT"]),
	"PROPERTIES" => array(
		array_merge(
			(is_array($arResult["COMMENT_PROPERTIES"]["DATA"][CBlogComment::UF_NAME]) ? $arResult["COMMENT_PROPERTIES"]["DATA"][CBlogComment::UF_NAME] : array()),
			(is_array($_POST[CBlogComment::UF_NAME]) ? array("VALUE" => $_POST[CBlogComment::UF_NAME]) : array()),
			array("POSTFIX" => "file")
		),
		is_array($arResult["COMMENT_PROPERTIES"]["DATA"]) &&
		array_key_exists("UF_BLOG_POST_URL_PRV", $arResult["COMMENT_PROPERTIES"]["DATA"]) ?
			array_merge(
				$arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_POST_URL_PRV"],
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
);

//if in blog settings not set image upload feature - remove this option from settings
if(array_key_exists("EDITOR_USE_IMAGE", $arResult["Blog"]) && $arResult["Blog"]["EDITOR_USE_IMAGE"] != "Y")
	unset($postFormParams["PROPERTIES"]);

$APPLICATION->IncludeComponent(
	"bitrix:main.post.form",
	"",
	$postFormParams,
	$component,
	array("HIDE_ICONS" => "Y")
);


?>

<script>
BX.ready(function() {
	//	init EDITOR form
	window["UC"] = (!!window["UC"] ? window["UC"] : {});
	window["UC"]["f<?=$component->createPostFormId()?>"] = new FCForm({
		entitiesId : {},
		formId : '<?=$component->createPostFormId()?>',
		editorId : '<?=$component->createEditorId()?>',
		editorName : ''
	});
		if (!!window["UC"]["f<?=$component->createPostFormId()?>"].eventNode)
		{
			BX.addCustomEvent(window["UC"]["f<?=$component->createPostFormId()?>"].eventNode, 'OnUCFormClear', __blogOnUCFormClear);
			BX.addCustomEvent(window["UC"]["f<?=$component->createPostFormId()?>"].eventNode, 'OnUCFormAfterShow', __blogOnUCFormAfterShow);
			BX.addCustomEvent(window["UC"]["f<?=$component->createPostFormId()?>"].eventNode, 'OnUCFormSubmit', __blogOnUCFormSubmit);
		}
});
</script>
