<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 - 2010 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!CModule::IncludeModule('mail'))
	die();

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$mb = new CMailBox();
$res = $mb->Check($_POST['serv'], $_POST['port'], $_POST['ssl'], $_POST['login'], $_POST['passw']);

header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);
echo CUtil::PhpToJSObject($res, false);
?>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
?>