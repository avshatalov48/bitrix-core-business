<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/prolog.php");
IncludeModuleLangFile(__FILE__);

if(!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("CLU_GROUP_EDIT_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("CLU_GROUP_EDIT_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID); // Id of the edited record
$strError = "";
$bVarsFromForm = false;

if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	if(isset($_POST["save"]) || isset($_POST["apply"]))
	{
		$ob = new CClusterGroup;
		$arFields = array(
			"NAME" => $_POST["NAME"],
		);

		if($ID > 0)
			$res = $ob->Update($ID, $arFields);
		else
			$res = $ID = $ob->Add($arFields);

		if($res)
		{
			if(isset($_POST["apply"]))
				LocalRedirect("/bitrix/admin/cluster_group_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
			else
				LocalRedirect("/bitrix/admin/cluster_index.php?lang=".LANG);
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("CLU_GROUP_EDIT_SAVE_ERROR"), $e);
			$bVarsFromForm = true;
		}
	}
	elseif($_POST["action"] == "delete")
	{
		$ob = new CClusterGroup;
		$res = $ob->Delete($ID);
		if($res)
		{
			LocalRedirect("/bitrix/admin/cluster_index.php?lang=".LANG);
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("CLU_GROUP_EDIT_DELETE_ERROR"), $e);
			$bVarsFromForm = true;
		}
	}
}

ClearVars("str_");
$str_NAME = "";

if($ID>0)
{
	$rs = CClusterGroup::GetList(array(), array("=ID" => $ID), array());
	if(!$rs->ExtractFields("str_"))
		$ID = 0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_cluster_group", "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("CLU_GROUP_EDIT_EDIT_TITLE") : GetMessage("CLU_GROUP_EDIT_ADD_TITLE")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($ID > 0)
{
	$aMenu = array(
		array(
			"TEXT" => GetMessage("CLU_GROUP_EDIT_DELETE"),
			"TITLE" => GetMessage("CLU_GROUP_EDIT_DELETE_TITLE"),
			"LINK" => "javascript:jsDelete('editform', '".GetMessage("CLU_GROUP_EDIT_DELETE_CONF")."')",
			"ICON" => "btn_delete",
		)
	);

	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}

if($message)
	echo $message->Show();
?>
<script>
function jsDelete(form_id, message)
{
	var _form = document.getElementById(form_id);
	var _flag = document.getElementById('action');
	if(_form && _flag)
	{
		if(confirm(message))
		{
			_flag.value = 'delete';
			_form.submit();
		}
	}
}
</script>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>"  enctype="multipart/form-data" name="editform" id="editform">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<?if($ID > 0):?>
		<tr>
			<td><?echo GetMessage("CLU_GROUP_EDIT_ID")?>:</td>
			<td><?echo $str_ID;?></td>
		</tr>
	<?endif?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("CLU_GROUP_EDIT_NAME")?>:</td>
		<td width="60%"><input type="text" size="40" name="NAME" value="<?echo $str_NAME?>"></td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"back_url"=>"cluster_index.php?lang=".LANG,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="action" id="action" value="">
<?if($ID>0):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("editform", $message);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>