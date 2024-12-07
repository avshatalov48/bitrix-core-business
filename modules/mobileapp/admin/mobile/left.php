<?
define('BX_DONT_SKIP_PULL_INIT', true);
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php');

CMobile::getInstance()->setLargeScreenSupport(false);
CMobile::getInstance()->setScreenCategory('NORMAL');

IncludeModuleLangFile(__FILE__);

$arParams = array(
	"SYNC_REQUEST_PATH" => MOBILE_APP_ADMIN_PATH,
	"MENU_FILE_PATH" => MOBILE_APP_MENU_FILE,
	"BUILD_MENU_EVENT_NAME" => MOBILE_APP_BUILD_MENU_EVENT_NAME,
	"MENU_TITLE" => GetMessage("MOBILEAPP_MENU_TITLE")
	);

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.menu',
	'.default',
	$arParams,
	false,
	Array('HIDE_ICONS' => 'Y'));
?>

<script>

	var pullParams = {
			enable:true,
			pulltext:"<?=GetMessage("PULL_TEXT")?>",
			downtext:"<?=GetMessage("DOWN_TEXT")?>",
			loadtext:"<?=GetMessage("LOAD_TEXT")?>"
		};
	if(app.enableInVersion(2))
		pullParams.action = "RELOAD";
	else
		pullParams.callback = function(){document.location.reload();};

	app.pullDown(pullParams);
	app.enableSliderMenu(true);
</script>

<? require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php');?>