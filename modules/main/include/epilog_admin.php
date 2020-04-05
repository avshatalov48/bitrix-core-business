<?
if (!defined("PUBLIC_MODE") || PUBLIC_MODE != 1)
{
	require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_before.php");
	require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_after.php");
	die();
}
?>