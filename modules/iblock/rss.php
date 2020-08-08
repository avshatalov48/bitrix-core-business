<?
define("ADMIN_SECTION", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header("Content-Type: text/xml");
CModule::IncludeModule("iblock");

if (intval($ID)>0)
{
	$ID = intval($ID);
}
else
{
	$ID = Trim($ID);
}
$LANG = Trim($_REQUEST["LANG"]);
$TYPE = Trim($TYPE);
$LIMIT = intval($LIMIT);

CIBlockRSS::GetRSS($ID, $LANG, $TYPE, $LIMIT, false, false);
?>