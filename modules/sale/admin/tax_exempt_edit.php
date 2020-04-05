<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

ClearVars("f_");

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$ID = IntVal($ID);
$z = CGroup::GetByID($ID);
if (!$z->ExtractFields("f_"))
{
	LocalRedirect("sale_tax_exempt.php?lang=".LANG.GetFilterParams("filter_", false));
}

$strError = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	$arTAX = array();

	CSaleTax::DeleteExempt(array("GROUP_ID" => $ID));

	if (isset($TAX_ID) && is_array($TAX_ID) && count($TAX_ID)>0)
	{
		for ($i = 0; $i<count($TAX_ID); $i++)
		{
			if (IntVal($TAX_ID[$i])>0)
			{
				CSaleTax::AddExempt(array("GROUP_ID" => $ID, "TAX_ID" => IntVal($TAX_ID[$i])));
			}
		}
	}

	if (strlen($strError)>0) $bInitVars = True;

	if (strlen($save)>0 && strlen($strError)<=0)
		LocalRedirect("sale_tax_exempt.php?lang=".LANG.GetFilterParams("filter_", false));
}

$sDocTitle = GetMessage("EXEMPT_EDIT_RECORD", array("#ID#" => $ID));
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("STEEN_2FLIST"),
				"ICON" => "btn_list",
				"LINK" => "/bitrix/admin/sale_tax_exempt.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
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
		array("DIV" => "edit1", "TAB" => GetMessage("STEEN_TAB_EXMPT"), "ICON" => "sale", "TITLE" => GetMessage("STEEN_TAB_EXMPT_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td width="40%">
			<?echo GetMessage("TAX_ID")?>:
		</td>
		<td width="60%">
			<b><?echo $ID ?></b>
		</td>
	</tr>
	<tr>
		<td>
			<?echo GetMessage("EXEMPT_NAME")?>:
		</td>
		<td>
			<b><?echo $f_NAME ?></b>
		</td>
	</tr>
	<tr>
		<td>
			<?echo GetMessage("EXEMPT_DESCR")?>:
		</td>
		<td>
			<?echo $f_DESCRIPTION ?>
		</td>
	</tr>

	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_TAX_LIST");?>:<br><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="">
		</td>
		<td width="60%" valign="top">
			<select name="TAX_ID[]" size="10" multiple>
				<?$db_vars = CSaleTax::GetList(Array("NAME"=>"ASC"), array())?>
				<?
				$arTAX_ID = array();
				if ($bInitVars)
				{
					$arTAX_ID = $TAX_ID;
				}
				else
				{
					$db_location = CSaleTax::GetExemptList(Array("GROUP_ID" => $ID));
					while ($arLocation = $db_location->Fetch())
					{
						$arTAX_ID[] = $arLocation["TAX_ID"];
					}
				}
				?>
				<?while ($vars = $db_vars->Fetch()):?>
					<option value="<?echo $vars["ID"]?>"<?if (in_array(IntVal($vars["ID"]), $arTAX_ID)) echo " selected"?>><?echo htmlspecialcharsbx($vars["NAME"]." (".$vars["LID"].")")?></option>
				<?endwhile;?>
			</select>
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_tax_exempt.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>