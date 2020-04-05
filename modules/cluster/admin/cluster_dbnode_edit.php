<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/prolog.php");
IncludeModuleLangFile(__FILE__);

if(!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$group_id = intval($_REQUEST["group_id"]);
$arNode = CClusterDBNode::GetByID($_GET["ID"]);
if(is_array($arNode) && $arNode["GROUP_ID"] != $group_id && $arNode["ROLE_ID"] !== "MODULE")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("CLU_DBNODE_EDIT_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("CLU_DBNODE_EDIT_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($_REQUEST["ID"]); // Id of the edited record
$strFatalError = "";
$strError = "";
$bVarsFromForm = false;

if($ID < 2)
{
	if($DB->type == "MYSQL")
		$strFatalError = GetMessage("CLU_DBNODE_EDIT_ERROR");
}
else
{
	$arNode = CClusterDBNode::GetByID($ID);
	if(!is_array($arNode))
		$strFatalError = GetMessage("CLU_DBNODE_EDIT_ERROR");
	elseif($arNode["ROLE_ID"] == "SLAVE")
		$strFatalError = GetMessage("CLU_DBNODE_EDIT_ERROR");
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
			"ACTIVE" => $_POST["ACTIVE"],
			"SORT" => $_POST["SORT"],
			"NAME" => $_POST["NAME"],
			"DB_HOST" => $_POST["DB_HOST"],
			"DB_NAME" => $_POST["DB_NAME"],
			"DB_LOGIN" => $_POST["DB_LOGIN"],
			"DB_PASSWORD" => $_POST["DB_PASSWORD"],
			"DESCRIPTION" => $_POST["DESCRIPTION"],
		);

		if($ID > 0)
		{
			$res = $ob->Update($ID, $arFields);
		}
		else
		{
			$arFields["ROLE_ID"] = "MODULE";
			$arFields["STATUS"] = "READY";
			$arFields["GROUP_ID"] = $group_id;
			$res = $ID = $ob->Add($arFields);
		}

		if($res)
		{
			if(isset($_REQUEST["apply"]) && $_REQUEST["apply"] != "")
				LocalRedirect("/bitrix/admin/cluster_dbnode_edit.php?ID=".$ID."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam().'&group_id='.$group_id);
			else
				LocalRedirect("/bitrix/admin/cluster_dbnode_list.php?lang=".LANGUAGE_ID.'&group_id='.$group_id);
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("CLU_DBNODE_EDIT_SAVE_ERROR"), $e);
			$bVarsFromForm = true;
		}
	}
	elseif((isset($_REQUEST["delete"]) && $_REQUEST["delete"] != "") && $ID > 1)
	{
		$ob = new CClusterDBNode;
		$res = $ob->Delete($ID);
		if($res)
			LocalRedirect("/bitrix/admin/cluster_dbnode_list.php?lang=".LANGUAGE_ID.'&group_id='.$group_id);
		else
			$bVarsFromForm = true;
	}
}

ClearVars("str_");
$str_ACTIVE = "Y";
$str_SORT = "10";
$str_NAME = "";
$str_DESCRIPTION = "";
$str_DB_HOST = "";
$str_DB_NAME = "";
$str_DB_LOGIN = "";
$str_DB_PASSWORD = "";
$str_STATUS = "";

if($ID > 0)
{
	$rs = CClusterDBNode::GetList(array(), array("=ID"=>$ID), array());
	if(!$rs->ExtractFields("str_"))
		$ID = 0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_cluster_dbnode", "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("CLU_DBNODE_EDIT_EDIT_TITLE") : GetMessage("CLU_DBNODE_EDIT_ADD_TITLE")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("CLU_DBNODE_EDIT_MENU_LIST"),
		"TITLE" => GetMessage("CLU_DBNODE_EDIT_MENU_LIST_TITLE"),
		"LINK" => "cluster_dbnode_list.php?lang=".LANGUAGE_ID.'&group_id='.$group_id,
		"ICON" => "btn_list",
	)
);
if($ID > 0)
{
	if($str_STATUS!="ONLINE")
	{
		$aMenu[] = array(
			"TEXT" => GetMessage("CLU_DBNODE_EDIT_MENU_DELETE"),
			"TITLE" => GetMessage("CLU_DBNODE_EDIT_MENU_DELETE_TITLE"),
			"LINK" => "javascript:jsDelete('editform', '".GetMessage("CLU_DBNODE_EDIT_MENU_DELETE_CONF")."')",
			"ICON" => "btn_delete",
		);
	}
}
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
			<td><?echo GetMessage("CLU_DBNODE_EDIT_ID")?>:</td>
			<td><?echo $str_ID;?></td>
		</tr>
	<?endif?>
	<tr>
		<td width="40%"><?echo GetMessage("CLU_DBNODE_EDIT_ACTIVE")?>:</td>
		<td width="60%">
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_DBNODE_EDIT_NAME")?>:</td>
		<td><input type="text" size="40" maxsize="50" name="NAME" value="<?echo $str_NAME?>"></td>
	</tr>
	<?if($DB->type == "ORACLE"):?>
		<tr>
			<td><?echo GetMessage("CLU_DBNODE_EDIT_ORACLE_DB_NAME")?>:</td>
			<td><input type="text" size="40" maxsize="50" name="DB_NAME" value="<?echo $str_DB_NAME?>"></td>
		</tr>
	<?else:?>
		<tr>
			<td><?echo GetMessage("CLU_DBNODE_EDIT_DB_HOST")?>:</td>
			<td><input type="text" size="40" maxsize="50" name="DB_HOST" value="<?echo $str_DB_HOST?>"></td>
		</tr>
		<tr>
			<td><?echo GetMessage("CLU_DBNODE_EDIT_DB_NAME")?>:</td>
			<td><input type="text" size="40" maxsize="50" name="DB_NAME" value="<?echo $str_DB_NAME?>"></td>
		</tr>
	<?endif;?>
	<tr>
		<td><?echo GetMessage("CLU_DBNODE_EDIT_DB_LOGIN")?>:</td>
		<td><input type="text" size="40" maxsize="50" name="DB_LOGIN" value="<?echo $str_DB_LOGIN?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_DBNODE_EDIT_DB_PASSWORD")?>:</td>
		<td><input autocomplete="off" type="password" size="40" maxsize="50" name="DB_PASSWORD" value="<?echo $str_DB_PASSWORD?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("CLU_DBNODE_EDIT_DESCRIPTION")?>:</td>
		<td><textarea cols="40" rows="10" name="DESCRIPTION"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"back_url"=>"cluster_dbnode_list.php?lang=".LANGUAGE_ID.'&group_id='.$group_id,
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

<?
$tabControl->ShowWarnings("editform", $message);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>