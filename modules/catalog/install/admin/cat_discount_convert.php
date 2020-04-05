<?
if (isset($_REQUEST['format']) && 'Y' == $_REQUEST['format'])
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/cat_discount_format.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/cat_discount_convert.php");
}
?>