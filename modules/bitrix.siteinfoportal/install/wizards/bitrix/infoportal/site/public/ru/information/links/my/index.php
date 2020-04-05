<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Каталог ресурсов");
?><?$APPLICATION->IncludeComponent("bitrix:iblock.element.add", ".default", array(
	"NAV_ON_PAGE" => "10",
	"USE_CAPTCHA" => "N",
	"USER_MESSAGE_ADD" => "Сайт успешно добавлен",
	"USER_MESSAGE_EDIT" => "Изменения успешно сохранены",
	"DEFAULT_INPUT_SIZE" => "30",
	"RESIZE_IMAGES" => "N",
	"IBLOCK_TYPE" => "services",
	"IBLOCK_ID" => "#LINKS_IBLOCK_ID#",
	"PROPERTY_CODES" => array(
		0 => "NAME",
		1 => "IBLOCK_SECTION",
		2 => "PREVIEW_TEXT",
		#IDS_CODE_PROPERTY#
	),
	"PROPERTY_CODES_REQUIRED" => array(
		0 => "NAME",
		1 => "IBLOCK_SECTION",
		2 => "PREVIEW_TEXT",
		#IDS_CODE_PROPERTY#
	),
	"GROUPS" => array(
		0 => "1",
		1 => "#GROUPS_ID#"
	),
	#STATUS_SETTINGS#
	"ALLOW_EDIT" => "Y",
	"ALLOW_DELETE" => "N",
	"ELEMENT_ASSOC" => "PROPERTY_ID",
	"ELEMENT_ASSOC_PROPERTY" => "7",
	"MAX_USER_ENTRIES" => "50",
	"MAX_LEVELS" => "2",
	"LEVEL_LAST" => "Y",
	"MAX_FILE_SIZE" => "0",
	"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
	"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "#SITE_DIR#information/links/my/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CUSTOM_TITLE_NAME" => "Название сайта",
	"CUSTOM_TITLE_TAGS" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
	"CUSTOM_TITLE_IBLOCK_SECTION" => "Категория",
	"CUSTOM_TITLE_PREVIEW_TEXT" => "Краткое описание сайта",
	"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
	"CUSTOM_TITLE_DETAIL_TEXT" => "Полное описание сайта",
	"CUSTOM_TITLE_DETAIL_PICTURE" => "",
	"SEND_EMAIL" => "Y",
	"EMAIL_TO" => "#EMAIL_TO#",
	"SUBJECT" => "Добавлен новый ресурс",
	"EVENT_MESSAGE_ID" => array(),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>