<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.eshop/include.php");

IncludeModuleLangFile(__FILE__);

$arThemes = array(
	"_blue",
	"_green",
	"_yellow",
	"_gray",
	"_wood",
	"_red",
);
foreach($arThemes as $theme)
{
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/templates/eshop".$theme."/"))
	{
		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT']."/bitrix/templates/eshop".$theme."/components/bitrix/catalog/.default/",
			$_SERVER['DOCUMENT_ROOT']."/bitrix/templates/eshop".$theme."/components/bitrix/catalog/.default_old/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = true
		);
	}
}

CAdminNotify::DeleteByTag("ESHOP_RENAME");

$APPLICATION->SetTitle(GetMessage("ESHOP_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?=GetMessage("ESHOP_RENAME_SUCCESS")?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>