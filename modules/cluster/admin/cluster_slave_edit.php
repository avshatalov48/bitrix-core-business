<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/prolog.php");
IncludeModuleLangFile(__FILE__);

if(!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$ID = intval($_REQUEST["ID"]);
$group_id = intval($_REQUEST["group_id"]);
if(!CClusterGroup::GetArrayByID($group_id))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("CLU_SLAVE_EDIT_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>$ID > 1? GetMessage("CLU_SLAVE_EDIT_TAB_TITLE1"): GetMessage("CLU_SLAVE_EDIT_TAB_TITLE2"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($_REQUEST["ID"]); // Id of the edited record
$strFatalError = "";
$strError = "";
$bVarsFromForm = false;

if($ID < 1)
{
	$strFatalError = GetMessage("CLU_SLAVE_EDIT_ERROR");
}
else
{
	$arNode = CClusterDBNode::GetByID($ID);
	if(!is_array($arNode))
		$strFatalError = GetMessage("CLU_SLAVE_EDIT_ERROR");
}

if($strFatalError)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($strFatalError);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	if(
		(isset($_REQUEST["save"]) && $_REQUEST["save"] != "")
		|| (isset($_REQUEST["apply"]) && $_REQUEST["apply"] != "")
	)
	{
		$ob = new CClusterDBNode;
		$arFields = array(
			"NAME" => $_POST["NAME"],
			"DESCRIPTION" => $_POST["DESCRIPTION"],
			"SELECTABLE" => $_POST["SELECTABLE"],
			"WEIGHT" => $_POST["WEIGHT"],
			"DESCRIPTION" => $_POST["DESCRIPTION"],
		);

		if($ID > 0)
			$res = $ob->Update($ID, $arFields);
		else
			$res = $ID = $ob->Add($arFields);

		if($res)
		{
			if(isset($_REQUEST["apply"]) && $_REQUEST["apply"] != "")
				LocalRedirect("/bitrix/admin/cluster_slave_edit.php?ID=".$ID."&lang=".LANGUAGE_ID."&group_id=".$group_id."&".$tabControl->ActiveTabParam());
			else
				LocalRedirect("/bitrix/admin/cluster_slave_list.php?lang=".LANGUAGE_ID."&group_id=".$group_id);
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("CLU_SLAVE_EDIT_SAVE_ERROR"), $e);
			$bVarsFromForm = true;
		}
	}
	elseif((isset($_REQUEST["delete"]) && $_REQUEST["delete"] != "") && $ID > 1)
	{
		$ob = new CClusterDBNode;
		$res = $ob->Delete($ID);
		if($res)
			LocalRedirect("/bitrix/admin/cluster_slave_list.php?lang=".LANGUAGE_ID."&group_id=".$group_id);
		else
			$bVarsFromForm = true;
	}
}

ClearVars("str_");
$str_NAME = "";
$str_DESCRIPTION = "";

if($ID>0)
{
	$rs = CClusterDBNode::GetList(array(), array("=ID"=>$ID), array());
	$ar = $rs->ExtractFields("str_");
	if(!$ar)
		$ID = 0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_cluster_dbnode", "", "str_");

$APPLICATION->SetTitle($ID > 1? GetMessage("CLU_SLAVE_EDIT_EDIT_TITLE1"): GetMessage("CLU_SLAVE_EDIT_EDIT_TITLE2"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("CLU_SLAVE_EDIT_MENU_LIST"),
		"TITLE" => GetMessage("CLU_SLAVE_EDIT_MENU_LIST_TITLE"),
		"LINK" => "cluster_slave_list.php?lang=".LANGUAGE_ID."&group_id=".$group_id,
		"ICON" => "btn_list",
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message)
	echo $message->Show();
?>
<script>
function jsDelete(form_id, message)
{
	var _form = document.getElementById(form_id);
	var _flag = document.getElementById('delete');
	if(_form && _flag)
	{
		if(confirm(message))
		{
			_flag.value = 'y';
			_form.submit();
		}
	}
}
function jsSync()
{
	var selectable = document.getElementById('SELECTABLE');
	var weight = document.getElementById('FORM_WEIGHT');
	weight.disabled = selectable.checked;
}
function updateWeight(source)
{
	var target = document.getElementById('WEIGHT');
	target.value = source.value;
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
			<td><?echo GetMessage("CLU_SLAVE_EDIT_ID")?>:</td>
			<td><?echo $str_ID;?></td>
		</tr>
	<?endif?>
	<tr>
		<td width="40%"><?echo GetMessage("CLU_SLAVE_EDIT_NAME")?>:</td>
		<td width="60%"><input type="text" size="40" maxsize="50" name="NAME" value="<?echo $str_NAME?>"></td>
	</tr>
	<?if($ID > 1):?>
	<tr>
		<td><?echo GetMessage("CLU_SLAVE_EDIT_DB_HOST")?>:</td>
		<td><?echo $str_DB_HOST?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_SLAVE_EDIT_DB_NAME")?>:</td>
		<td><?echo $str_DB_NAME?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_SLAVE_EDIT_DB_LOGIN")?>:</td>
		<td><?echo $str_DB_LOGIN?></td>
	</tr>
	<?endif;?>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("CLU_SLAVE_EDIT_DESCRIPTION")?>:</td>
		<td><textarea cols="40" rows="10" name="DESCRIPTION"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<tr>
		<td><label for="SELECTABLE"><?echo $str_ROLE_ID=="SLAVE"? GetMessage("CLU_SLAVE_EDIT_SELECTABLE1"): GetMessage("CLU_SLAVE_EDIT_SELECTABLE2")?>:</label></td>
		<td>
			<input type="checkbox" id="SELECTABLE" name="SELECTABLE" value="N" <?echo $str_SELECTABLE=="N"? 'checked="checked"': ''?> onclick="jsSync()">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_SLAVE_EDIT_WEIGHT")?>:</td>
		<td>
			<input type="text" size="6" maxsize="6" id="FORM_WEIGHT" name="FORM_WEIGHT" value="<?echo $str_WEIGHT?>" onblur="updateWeight(this)" onchange="updateWeight(this)">
			<input type="hidden" id="WEIGHT" name="WEIGHT" value="<?echo $str_WEIGHT?>">
		</td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"back_url"=>"cluster_slave_list.php?lang=".LANGUAGE_ID."&group_id=".$group_id,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
<input type="hidden" name="group_id" value="<?echo $group_id?>">
<?if($ID>0):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
	<input type="hidden" name="delete" id="delete" value="">
<?endif;?>
<?
$tabControl->End();
?>
</form>
<script>
jsSync();
</script>
<?
$tabControl->ShowWarnings("editform", $message);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>