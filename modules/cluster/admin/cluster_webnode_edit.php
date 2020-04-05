<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/prolog.php");
IncludeModuleLangFile(__FILE__);

if(!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$group_id = intval($_REQUEST["group_id"]);
if(!CClusterGroup::GetArrayByID($group_id))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("CLU_WEBNODE_EDIT_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("CLU_WEBNODE_EDIT_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID); // Id of the edited record
$strError = "";
$bVarsFromForm = false;

if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	if(
		(isset($_REQUEST["save"]) && $_REQUEST["save"] != "")
		|| (isset($_REQUEST["apply"]) && $_REQUEST["apply"] != "")
	)
	{
		$ob = new CClusterWebnode;
		$arFields = array(
			"NAME" => $_POST["NAME"],
			"HOST" => $_POST["HOST"],
			"PORT" => $_POST["PORT"],
			"STATUS_URL" => $_POST["STATUS_URL"],
			"DESCRIPTION" => $_POST["DESCRIPTION"],
		);

		if($ID > 0)
		{
			$res = $ob->Update($ID, $arFields);
		}
		else
		{
			$arFields["GROUP_ID"] = $group_id;
			$res = $ID = $ob->Add($arFields);
		}

		if($res)
		{
			if(isset($_REQUEST["apply"]) && $_REQUEST["apply"] != "")
				LocalRedirect("/bitrix/admin/cluster_webnode_edit.php?ID=".$ID."&lang=".LANGUAGE_ID.'&group_id='.$group_id."&".$tabControl->ActiveTabParam());
			else
				LocalRedirect("/bitrix/admin/cluster_webnode_list.php?lang=".LANGUAGE_ID.'&group_id='.$group_id);
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("CLU_WEBNODE_EDIT_SAVE_ERROR"), $e);
			$bVarsFromForm = true;
		}
	}
	elseif((isset($_REQUEST["delete"]) && $_REQUEST["delete"] != "") && $ID > 1)
	{
		$ob = new CClusterDBNode;
		$res = $ob->Delete($ID);
		if($res)
			LocalRedirect("/bitrix/admin/cluster_dbnode_list.php?lang=".LANG.'&group_id='.$group_id);
		else
			$bVarsFromForm = true;
	}
}

ClearVars("str_");
$str_NAME = "";
$str_DESCRIPTION = "";
$str_HOST = "";
$str_PORT = "80";
$str_STATUS_URL = "/server-status";

if($ID > 0)
{
	$rs = CClusterWebnode::GetList(array(), array("=ID" => $ID, "=GROUP_ID" => $group_id), array());
	if(!$rs->ExtractFields("str_"))
		$ID = 0;
}

if ($ID <= 0)
{
	if(!CCluster::checkForServers(1))
	{
		$message = new CAdminMessage(array("MESSAGE"=>GetMessage("CLUSTER_SERVER_COUNT_WARNING"), "TYPE"=>"ERROR"));
	}
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_cluster_webnode", "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("CLU_WEBNODE_EDIT_EDIT_TITLE") : GetMessage("CLU_WEBNODE_EDIT_ADD_TITLE")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("CLU_WEBNODE_EDIT_MENU_LIST"),
		"TITLE" => GetMessage("CLU_WEBNODE_EDIT_MENU_LIST_TITLE"),
		"LINK" => "cluster_webnode_list.php?lang=".LANG.'&group_id='.$group_id,
		"ICON" => "btn_list",
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message)
	echo $message->Show();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>"  enctype="multipart/form-data" name="editform" id="editform">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<?if($ID > 0):?>
		<tr>
			<td><?echo GetMessage("CLU_WEBNODE_EDIT_ID")?>:</td>
			<td><?echo $str_ID;?></td>
		</tr>
	<?endif?>
	<tr>
		<td width="40%"><?echo GetMessage("CLU_WEBNODE_EDIT_NAME")?>:</td>
		<td width="60%"><input type="text" size="40" name="NAME" value="<?echo $str_NAME?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_WEBNODE_EDIT_HOST")?>:</td>
		<td><input type="text" size="20" name="HOST" value="<?echo $str_HOST?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_WEBNODE_EDIT_PORT")?>:</td>
		<td><input type="text" size="6" name="PORT" value="<?echo $str_PORT?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CLU_WEBNODE_EDIT_STATUS_URL")?>:</td>
		<td><input type="text" size="40" name="STATUS_URL" value="<?echo $str_STATUS_URL?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("CLU_WEBNODE_EDIT_DESCRIPTION")?>:</td>
		<td><textarea cols="40" rows="10" name="DESCRIPTION"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"back_url"=>"cluster_webnode_list.php?lang=".LANGUAGE_ID."&group_id=".$group_id,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
<input type="hidden" name="group_id" value="<?echo $group_id?>">
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