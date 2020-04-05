<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/processes/index.php");

$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));

$APPLICATION->IncludeComponent("bitrix:lists", ".default", array(
	"IBLOCK_TYPE_ID" => "bitrix_processes",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/services/processes/",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"SEF_URL_TEMPLATES" => array(
		"lists" => "",
		"list" => "#list_id#/view/#section_id#/",
		"list_sections" => "#list_id#/edit/#section_id#/",
		"list_edit" => "#list_id#/edit/",
		"list_fields" => "#list_id#/fields/",
		"list_field_edit" => "#list_id#/field/#field_id#/",
		)
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
