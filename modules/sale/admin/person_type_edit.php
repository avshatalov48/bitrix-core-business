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
	if ($ACTIVE!="Y") $ACTIVE = "N";
	
	$arFields = array(
		"LID" => $LID,
		"NAME" => $NAME,
		"SORT" => $SORT,
		"ACTIVE" => $ACTIVE,
	);

	if ($ID > 0)
	{
		if (!CSalePersonType::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString()."<br>";
			else
				$errorMessage .= GetMessage("SPTEN_ERROR_SAVING_PERSON_TYPE")."<br>";
		}
	}
	else
	{
		$ID = CSalePersonType::Add($arFields);
		$ID = IntVal($ID);
		if ($ID <= 0)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString()."<br>";
			else
				$errorMessage .= GetMessage("SPTEN_ERROR_SAVING_PERSON_TYPE")."<br>";
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/sale_person_type.php?lang=".LANG.GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/sale_person_type_edit.php?ID=".$ID."&lang=".LANG.GetFilterParams("filter_", false));
		
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("SPTEN_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("SPTEN_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($saleModulePermissions < "W")
	$errorMessage .= GetMessage("SPTEN_NO_PERMS2ADD").".<br>";

if(IntVal($ID) > 0)
{
	$dbPersonType = CSalePersonType::GetList(Array(), array("ID" => $ID));
	$dbPersonType->ExtractFields("str_");
}
else
{
	$ID = 0;
//	$str_ACTIVE = "Y";
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_person_type", "", "str_");
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("SPTEN_2FLIST"),
		"LINK" => "/bitrix/admin/sale_person_type.php?lang=".LANG.GetFilterParams("filter_"),
		"ICON" => "btn_list"
	)
);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("SPTEN_NEW_PERSON_TYPE"),
		"LINK" => "/bitrix/admin/sale_person_type_edit.php?lang=".LANG.GetFilterParams("filter_"),
		"ICON" => "btn_new"
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("SPTEN_DELETE_PERSON_TYPE"), 
		"LINK" => "javascript:if(confirm('".GetMessage("SPTEN_DELETE_PERSON_TYPE_CONFIRM")."')) window.location='/bitrix/admin/sale_person_type.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y",
		"ICON" => "btn_delete"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if(strlen($errorMessage)>0)
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SPTEN_ERROR"), "HTML"=>true));?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SPTEN_TAB_PERSON_TYPE"), "ICON" => "sale", "TITLE" => GetMessage("SPTEN_TAB_PERSON_TYPE_DESCR"))
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
	<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("F_ACTIVE");?>:</td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" value="Y" <?if ($str_ACTIVE=="Y") echo "checked"?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?echo GetMessage("SPTEN_SITE")?>:</td>
		<td width="60%">
			<?echo CSite::SelectBoxMulti("LID", $str_LIDS);?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("SPTEN_NAME")?>:</td>
		<td width="60%">
			<input type="text" name="NAME" size="30" maxlength="100" value="<?= $str_NAME ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SPTEN_SORT")?>:</td>
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
				"back_url" => "/bitrix/admin/sale_person_type.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>