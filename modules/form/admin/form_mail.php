<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("form");

ClearVars();

$err_mess = "File: ".__FILE__."<br>Line: ";

$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);

if ($F_RIGHT < 30 || !check_bitrix_sessid())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	die();
}

if ($_REQUEST['action'] == 'delete')
{
	$isAdmin = $USER->CanDoOperation('edit_other_settings');

	if ($isAdmin)
	{
		$ID = intval($_REQUEST['ID']);

		$emessage = new CEventMessage();

		$DB->StartTransaction();
		if(!$emessage->Delete($ID))
		{
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}

	die();
}

$q = CForm::GetByID($WEB_FORM_ID);
$arrForm = $q->Fetch();
$arTemplates = CForm::SetMailTemplate($WEB_FORM_ID, "Y", '', true);

IncludeModuleLangFile(__FILE__);
$strNote .= GetMessage("FORM_GENERATING_FINISHED")."<br>";

$arReturn = array(
	'NOTE' => $strNote,
	'TEMPLATES' => $arTemplates
);

?>
<script>
_processData(<?=CUtil::PhpToJsObject($arReturn)?>);
</script>