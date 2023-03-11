<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:rest.devops",
	"",
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => SITE_DIR."devops/",
		"APPLICATION_URL" => SITE_DIR."marketplace/app/#id#/",
		"SEF_URL_TEMPLATES" => array(
			"index" => "",
			"section" => "section/#SECTION_CODE#/",
			"add" => "add/#ELEMENT_CODE#/",
			"edit" => "edit/#ELEMENT_CODE#/#ID#/",
			"list" => "list/",
			"iframe" => "iframe/",
			"statistic" => "statistic/"
		)
	),
	$component ?? null
);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");