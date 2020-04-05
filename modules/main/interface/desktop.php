<?
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

if(empty($adminMenu->aGlobalMenu))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetAdditionalCSS("/bitrix/themes/".ADMIN_THEME_ID."/index.css");

$APPLICATION->SetTitle(GetMessage("admin_index_title"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:desktop",
	"admin",
	Array(
		"MODE" => "AI",
		"ID" => "admin_index",
		"MULTIPLE" => "Y",
		"CAN_EDIT" => "Y",
		"COLUMNS" => "2",
		"COLUMN_WIDTH_0" => "50%",
		"COLUMN_WIDTH_1" => "50%",
		"GADGETS" => Array("ALL"),
		"G_RSSREADER_SHOW_URL" => "Y",
	),
	false,
	array(
		"HIDE_ICONS" => "Y"
	)
	);?>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>