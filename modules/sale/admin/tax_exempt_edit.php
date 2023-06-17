<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_tax_exempt.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

ClearVars("f_");

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$ID = intval($ID);
$z = CGroup::GetByID($ID);
if (!$z->ExtractFields("f_"))
{
	$adminSidePanelHelper->localRedirect($listUrl);
	LocalRedirect($listUrl);
}

$strError = "";
$bInitVars = false;
if (($save <> '' || $apply <> '') && $_SERVER['REQUEST_METHOD']=="POST" && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();

	$arTAX = array();

	CSaleTax::DeleteExempt(array("GROUP_ID" => $ID));

	if (isset($TAX_ID) && is_array($TAX_ID))
	{
		$cnt = count($TAX_ID);
		for ($i = 0; $i<$cnt; $i++)
		{
			if (intval($TAX_ID[$i])>0)
			{
				CSaleTax::AddExempt(array("GROUP_ID" => $ID, "TAX_ID" => intval($TAX_ID[$i])));
			}
		}
	}

	if ($strError <> '')
	{
		$adminSidePanelHelper->sendJsonErrorResponse($strError);
		$bInitVars = true;
	}

	$adminSidePanelHelper->sendSuccessResponse("base");

	if ($save <> '' && $strError == '')
	{
		$adminSidePanelHelper->localRedirect($listUrl);
		LocalRedirect($listUrl);
	}
}

$sDocTitle = GetMessage("EXEMPT_EDIT_RECORD", array("#ID#" => $ID));
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

$aMenu = array(
	array(
		"TEXT" => GetMessage("STEEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strError);?>
<?
$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" action="<?=$actionUrl?>" name="fform">
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
					<option value="<?echo $vars["ID"]?>"<?if (in_array(intval($vars["ID"]), $arTAX_ID)) echo " selected"?>><?echo htmlspecialcharsbx($vars["NAME"]." (".$vars["LID"].")")?></option>
				<?endwhile;?>
			</select>
		</td>
	</tr>

<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "W"), "back_url" => $listUrl));
$tabControl->End();
?>

</form>
<?require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");?>