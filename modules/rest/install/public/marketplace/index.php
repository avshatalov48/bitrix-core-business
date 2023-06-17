<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $APPLICATION;
$APPLICATION->SetTitle("");

if (Loader::includeModule('market')) {
	$path = str_replace('/marketplace/', '/market/', Context::getCurrent()->getRequest()->getRequestUri());
	LocalRedirect($path);
}

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		"POPUP_COMPONENT_NAME" => "bitrix:rest.marketplace",
		"POPUP_COMPONENT_TEMPLATE_NAME" => ".default",
		"POPUP_COMPONENT_PARAMS" => array(
			"SEF_MODE" => "Y",
			"SEF_FOLDER" => SITE_DIR."marketplace/",
			"APPLICATION_URL" => SITE_DIR."marketplace/app/#id#/",
			"SEF_URL_TEMPLATES" => array(
				//"top" => "",
				"category" => "category/#category#/",
				"detail" => "detail/#app#/",
				"placement_view" => "view/#APP#/",
				"placement" => "placement/#PLACEMENT_ID#/",
				"search" => "search/",
				"buy" => "buy/",
				"updates" => "updates/",
				"installed" => "installed/",
			)
		),
		"USE_UI_TOOLBAR" => "Y",
		"USE_PADDING" => false,
		"PAGE_MODE" => false
	),
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php") ?>