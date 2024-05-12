<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

IncludeModuleLangFile(__FILE__);

define("FROMDIALOGS", true);
$fname = "";
$no_prolog = false;
switch($dtype)
{
	case "colorpick":
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/admin/colorpick.htm");
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
		die();
	case "":
		$fname = "";
		$title = "";
		break;
	default:
		$fname = "";
		$title = "";
}
?>
<HTML>
<HEAD>
<STYLE TYPE="text/css">
BODY   {margin-left:10; font-family:Arial; font-size:12px; background:menu}
BUTTON {width:5em}
TABLE  {font-family:Arial; font-size:12px}
P      {text-align:center}
</STYLE>
<script>
function KeyPress()
{
	if(window.event.keyCode == 27)
		window.close();
}
</script>
<title><?echo htmlspecialcharsbx($title)?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
</HEAD>
<BODY onKeyPress="KeyPress()">
<?
if($fname=="")
	echo GetMessage("VOTE_DIALOGS_BAD_TYPE");
else
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/admin/".$fname);
}
?>
</BODY>
</HTML>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>