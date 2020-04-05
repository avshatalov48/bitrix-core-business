<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$ID = IntVal($ID);

ClearVars();

$strError = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	if (strlen($NAME)<=0) $strError .= GetMessage("ERROR_EMPTY_NAME")."<br>";
	if (strlen($LID)<=0) $strError .= GetMessage("ERROR_EMPTY_LANG")."<br>";

	if (strlen($strError)<=0)
	{
		$arFields = array(
			"LID" => $LID,
			"NAME" => trim($NAME),
			"CODE" => (strlen($CODE)<=0) ? False : $CODE,
			"DESCRIPTION" => $DESCRIPTION
			);

		if (IntVal($ID)>0)
		{
			if (!CSaleTax::Update($ID, $arFields))
				$strError .= GetMessage("ERROR_EDIT_TAX")."<br>";
		}
		else
		{
			$ID = CSaleTax::Add($arFields);
			if (IntVal($ID)<=0)
				$strError .= GetMessage("ERROR_ADD_TAX")."<br>";
		}
	}

	if (strlen($strError)>0) $bInitVars = True;

	if (strlen($save)>0 && strlen($strError)<=0)
		LocalRedirect("sale_tax.php?lang=".LANG.GetFilterParams("filter_", false));
}

if (strlen($ID)>0)
{
	$db_tax = CSaleTax::GetList(Array(), Array("ID" => $ID));
	$db_tax->ExtractFields("str_");
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_sale_tax", "", "str_");
}

if($ID > 0)
	$sDocTitle = GetMessage("SALE_EDIT_RECORD", array("#ID#"=>$ID));
else
	$sDocTitle = GetMessage("SALE_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("STEN_2FLIST"),
				"ICON" => "btn_list",
				"LINK" => "/bitrix/admin/sale_tax.php?lang=".LANG.GetFilterParams("filter_")
			)
	);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("STEN_NEW_TAX"),
			"ICON" => "btn_new",
			"LINK" => "/bitrix/admin/sale_tax_edit.php?lang=".LANG.GetFilterParams("filter_")
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("STEN_DELETE_TAX"),
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("STEN_DELETE_TAX_CONFIRM")."')) window.location='/bitrix/admin/sale_tax.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strError);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fform">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("STEN_TAB_TAX"), "ICON" => "sale", "TITLE" => GetMessage("STEN_TAB_TAX_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID>0):?>
		<tr>
			<td width="40%"><?echo GetMessage("TAX_ID")?>:</td>
			<td width="60%"><b><?echo $ID ?></b></td>
		</tr>
		<tr>
			<td><?echo GetMessage("TAX_TIMESTAMP")?>:</td>
			<td><b><?echo $str_TIMESTAMP_X ?></b></td>
		</tr>
	<?endif;?>

	<tr>
		<td width="40%"><?echo GetMessage("TAX_LID")?>:</td>
		<td width="60%"><?echo CLang::SelectBox("LID", $str_LID, "")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("TAX_NAME")?>:</td>
		<td>
			<input type="text" name="NAME" value="<?echo $str_NAME ?>" size="50" maxlength="250">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("TAX_FCODE")?>:</td>
		<td>
			<input type="text" name="CODE" value="<?echo $str_CODE?>" size="25" maxlength="50">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("TAX_DESCRIPTION")?>:</td>
		<td>
			<input type="text" name="DESCRIPTION" value="<?echo $str_DESCRIPTION ?>" size="50" maxlength="250">
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_tax.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>