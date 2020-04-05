<?
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/wizard_load.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/wizard.php");

if(!$USER->CanDoOperation('edit_php') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$strError = $strOK = "";
do
{
	if ( !($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["action"]=="import" && $isAdmin && check_bitrix_sessid()) )
		break;

	if (!is_uploaded_file($_FILES["wizardFile"]["tmp_name"]))
	{
		$strError .= GetMessage("MAIN_WIZARD_LOAD_ERROR_LOAD");
		break;
	}
	elseif(GetFileExtension(strtolower($_FILES["wizardFile"]["name"])) != "gz")
	{
		$strError .= GetMessage("MAIN_WIZARD_TAR_GZ");
		break;
	}

	$wizardPath = $_SERVER["DOCUMENT_ROOT"].CWizardUtil::GetRepositoryPath();

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");
	$oArchiver = new CArchiver($_FILES["wizardFile"]["tmp_name"]);

	if (!$oArchiver->extractFiles($wizardPath))
	{
		$strError .= GetMessage("MAIN_WIZARD_IMPORT_ERROR");
		$arErrors = &$oArchiver->GetErrors();
		if(count($arErrors)>0)
		{
			$strError .= ":<br>";
			foreach ($arErrors as $value)
				$strError .= "[".$value[0]."] ".$value[1]."<br>";
		}
		else
			$strError .= ".<br>";

		break;
	}
	
	$strOK .= GetMessage("MAIN_WIZARD_LOAD_OK");
} while (false);

$aTabs = Array(Array("DIV" => "edit1", "TAB" => GetMessage("MAIN_WIZARD_LOAD_TITLE"), "TITLE" => GetMessage("MAIN_WIZARD_LOAD_TITLE")));
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$APPLICATION->SetTitle(GetMessage("MAIN_WIZARD_LOAD_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

echo CAdminMessage::ShowMessage($strError);
echo CAdminMessage::ShowNote($strOK);

$arMenu = array(
	array(
		"TEXT"	=> GetMessage("MAIN_WIZARD_LOAD_LINK_LIST"),
		"LINK"	=> "wizard_list.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_WIZARD_LOAD_LINK_LIST"),
		"ICON"	=> "btn_list"
	)
);

$context = new CAdminContextMenu($arMenu);
$context->Show();
?>

<form method="post" action="<?=$APPLICATION->GetCurPage()?>?" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("MAIN_WIZARD_LOAD_FILE")?>:</td>
		<td width="60%"><input type="file" size="35" name="wizardFile"></td>
	</tr>

<?
$tabControl->Buttons();
?>
	<input type="hidden" name="action" value="import">
	<input <?if(!$isAdmin) echo "disabled" ?> type="submit" name="import" value="<?echo GetMessage("MAIN_WIZARD_LOAD_SUBMIT")?>" class="adm-btn-save">
<?
$tabControl->End();
?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>