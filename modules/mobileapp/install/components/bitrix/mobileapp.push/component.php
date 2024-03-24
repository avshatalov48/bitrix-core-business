<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage('MOBILEAPP_NOT_INSTALLED'));
	return;
}

/**
 * @var $APPLICATION CMain
 */
$arResult = array(
	"CURRENT_PAGE" => $APPLICATION->GetCurPage(),
	"AJAX_URL" => $componentPath."/ajax.php"
);

$arResult["PATH"] = isset($_REQUEST["path"]) ? $_REQUEST["path"] : '';
$arResult["DATA"] = CAdminMobilePush::getData($arResult["PATH"]);

CJSCore::Init('ajax');

$this->IncludeComponentTemplate();
?>