<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>

<?$APPLICATION->IncludeComponent(
	"bitrix:app.layout", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"DETAIL_URL" => SITE_DIR."marketplace/detail/#code#/",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => SITE_DIR."marketplace/app/",
		"SEF_URL_TEMPLATES" => array(
			"application" => "#id#/",
		)
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>