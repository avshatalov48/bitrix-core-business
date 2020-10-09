<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
use Bitrix\Main\Loader;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('form');
$err_mess = "File: ".__FILE__."<br>Line: ";

$q = CForm::GetByID($WEB_FORM_ID);
$arrForm = $q->Fetch();

$F_RIGHT = CForm::GetPermission($arrForm["ID"]);
if ($F_RIGHT<30) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if (check_bitrix_sessid()) CForm::SetMailTemplate($WEB_FORM_ID, "Y");

IncludeModuleLangFile(__FILE__);
$strNote .= GetMessage("FORM_GENERATING_FINISHED")."<br>";

$APPLICATION->SetTitle(GetMessage("FORM_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php")
?>
<div align="center"><? ShowNote($strNote); ?>
<font class="tablebodytext">[&nbsp;<a target="_blank" href="/bitrix/admin/message_admin.php?lang=<?=LANGUAGE_ID?>&find_type_id=<?=htmlspecialcharsbx($arrForm["MAIL_EVENT_TYPE"])?>&set_filter=Y" class="tablebodylink"><?=GetMessage("FORM_VIEW_TEMPLATE")?></a>&nbsp;]</font><br>
<form><input class="button" type="button" onClick="window.close()" value="<?echo GetMessage("FORM_CLOSE")?>"></form></div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");