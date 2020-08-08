<?
define("BX_SKIP_USER_LIMIT_CHECK", true);
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/webdav.disk/ajax.php"))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/webdav.disk/ajax.php");
}
?>