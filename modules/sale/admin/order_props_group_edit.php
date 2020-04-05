<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = IntVal($ID);

if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $saleModulePermissions>="W" && check_bitrix_sessid())
{
	$arFields = array(
		"NAME" => $NAME,
		"SORT" => $SORT
	);

	if ($ID > 0)
	{
		if (!CSaleOrderPropsGroup::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().". ";
			else
				$errorMessage .= GetMessage("SOPGEN_ERROR_SAVING_PROPS_GRP").". ";
		}
	}
	else
	{
		$arFields["PERSON_TYPE_ID"] = $PERSON_TYPE_ID;

		$ID = CSaleOrderPropsGroup::Add($arFields);
		$ID = IntVal($ID);
		if ($ID <= 0)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().". ";
			else
				$errorMessage .= GetMessage("SOPGEN_ERROR_SAVING_PROPS_GRP").". ";
		}
		else
		{
			LocalRedirect("/bitrix/admin/sale_order_props_group_edit.php?ID=$ID&lang=".LANG.GetFilterParams("filter_", false));
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/sale_order_props_group.php?lang=".LANG.GetFilterParams("filter_", false));
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("SOPGEN_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("SOPGEN_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$dbPropsGroup = CSaleOrderPropsGroup::GetList(array(), array("ID" => $ID));
if (!$dbPropsGroup->ExtractFields("str_"))
{
	if ($saleModulePermissions < "W")
		$errorMessage .= GetMessage("SOPGEN_NO_PERMS2ADD").". ";
	$ID = 0;
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_order_props_group", "", "str_");
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("SOPGEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/sale_order_props_group.php?lang=".LANG.GetFilterParams("filter_")
	)
);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("SOPGEN_NEW_PROPS_GRP"),
		"ICON" => "btn_new",
		"LINK" => "/bitrix/admin/sale_order_props_group_edit.php?lang=".LANG.GetFilterParams("filter_")
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("SOPGEN_DELETE_PROPS_GRP"), 
		"LINK" => "javascript:if(confirm('".GetMessage("SOPGEN_DELETE_PROPS_GRP_CONFIRM")."')) window.location='/bitrix/admin/sale_order_props_group.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($errorMessage);?>

<form method="POST" action="<?=$APPLICATION->GetCurPage()."?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false)?>" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SOPGEN_TAB_PROPS_GRP"), "ICON" => "sale", "TITLE" => GetMessage("SOPGEN_TAB_PROPS_GRP_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
		<tr>
			<td width="40%"><?echo GetMessage("SOPGEN_PERSON_TYPE")?>:</td>
			<?
			$arPersType = Array();
			$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("ID" => $str_PERSON_TYPE_ID));
			if($arPersonType = $dbPersonType->Fetch())
			{
				$arPersType = Array("ID" => $arPersonType["ID"], "NAME" => htmlspecialcharsEx($arPersonType["NAME"]), "LID" => implode(", ", $arPersonType["LIDS"]));
			}
			?>
			<td width="60%"><?= "[".$arPersType["ID"]."] ".($arPersType["NAME"])." (".htmlspecialcharsEx($arPersType["LID"]).")" ?></td>
		</tr>
	<?else:?>
		<tr class="adm-detail-required-field">
			<td width="40%"><?echo GetMessage("SOPGEN_PERSON_TYPE")?>:</td>
			<td width="60%">
				<?echo CSalePersonType::SelectBox("PERSON_TYPE_ID", $str_PERSON_TYPE_ID, "", True, "", "")?>
			</td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("SOPGEN_NAME")?>:</td>
		<td width="60%">
			<input type="text" name="NAME" size="30" maxlength="256" value="<?= $str_NAME ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SOPGEN_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" value="<?= IntVal($str_SORT) ?>">
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_order_props_group.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>