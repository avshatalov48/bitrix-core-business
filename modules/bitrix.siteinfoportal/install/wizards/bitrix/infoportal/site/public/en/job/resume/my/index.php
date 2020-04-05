<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Write your Resume");
?><?$APPLICATION->IncludeComponent("bitrix:iblock.element.add", ".default", array(
	"NAV_ON_PAGE" => "10",
	"USE_CAPTCHA" => "Y",
	"USER_MESSAGE_ADD" => "You resume has been added",
	"USER_MESSAGE_EDIT" => "You resume has been saved",
	"DEFAULT_INPUT_SIZE" => "30",
	"RESIZE_IMAGES" => "N",
	"IBLOCK_TYPE" => "job",
	"IBLOCK_ID" => "#RESUME_IBLOCK_ID#",
	"PROPERTY_CODES" => array(
		0 => "NAME",
		1 => "DATE_ACTIVE_TO",
		2 => "IBLOCK_SECTION",
		3 => "DETAIL_TEXT",
		#IDS_CODE_PROPERTY#
	),
	"PROPERTY_CODES_REQUIRED" => array(
		0 => "NAME",
		1 => "DATE_ACTIVE_TO",
		2 => "IBLOCK_SECTION",
		#IDS_CODE_REQUIRED#
	),
	"GROUPS" => array(
		0 => "1",
		1 => "#GROUPS_ID#",
	),
	#STATUS_SETTINGS#
	"ALLOW_EDIT" => "Y",
	"ALLOW_DELETE" => "Y",
	"ELEMENT_ASSOC" => "CREATED_BY",
	"MAX_USER_ENTRIES" => "5",
	"MAX_LEVELS" => "1",
	"LEVEL_LAST" => "Y",
	"MAX_FILE_SIZE" => "0",
	"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
	"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "#SITE_DIR#job/vacancy/my/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CUSTOM_TITLE_NAME" => "Job Title",
	"CUSTOM_TITLE_TAGS" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_TO" => "Valid till",
	"CUSTOM_TITLE_IBLOCK_SECTION" => "Career Category",
	"CUSTOM_TITLE_PREVIEW_TEXT" => "",
	"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
	"CUSTOM_TITLE_DETAIL_TEXT" => "Other Information",
	"CUSTOM_TITLE_DETAIL_PICTURE" => "",
	"SEND_EMAIL" => "Y",
	"EMAIL_TO" => "#EMAIL_TO#",
	"SUBJECT" => "A resume has been submitted",
	"EVENT_MESSAGE_ID" => array(),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
