<?
use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$FM_RIGHT = $APPLICATION->GetGroupRight("sale");
if ($FM_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');

if (!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1251">
</head>
<body><?
$res = "";
$ID = intval($ID);
$func_name = preg_replace("/[^a-zA-Z0-9_-]/is", "", $func_name);

if ($ID > 0)
{
	$dbAffiliate = CSaleAffiliate::GetList(array(), array("ID" => $ID), false, false, array("ID", "SITE_ID", "USER_ID", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME"));
	if ($arAffiliate = $dbAffiliate->Fetch())
	{
		$res = "[".$arAffiliate["USER_ID"].", ".$arAffiliate["SITE_ID"]."] ";
		$res .= htmlspecialcharsbx($arAffiliate["USER_NAME"])." ".htmlspecialcharsbx($arAffiliate["USER_LAST_NAME"]);
		$res .= " (".htmlspecialcharsbx($arAffiliate["USER_LOGIN"]).")";
	}
	else
	{
		$res = "NA";
	}
}
?>
<script>
window.parent.<?= $func_name ?>('<?= CUtil::JSEscape($res) ?>');
</script>
</body>
</html><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");