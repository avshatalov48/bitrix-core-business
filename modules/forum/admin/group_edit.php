<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/**
 * @var $APPLICATION CMain
 */
$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
ClearVars();
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$ID = intval($_REQUEST["ID"]);


$arError = array();
$message = false;
$arSysLangs = array();

$db_lang = CLanguage::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));
while (($arLang = $db_lang->Fetch()) && $arLang)
{
	$arSysLangs[$arLang["LID"]] = htmlspecialcharsbx($arLang["NAME"]);
}
$arGroups = CForumGroup::GetByLang(LANGUAGE_ID);
array_unshift($arGroups, array("ID" => 0, "NAME" => "..."));
$gid = ($ID ?: $_GET["COPY_ID"]);
if ($gid > 0 && ($group = CForumGroup::GetList(array(), array("ID" => $gid))->fetch()) && $group)
{
	$fields = $group + array("LANG" => array());
	foreach ($arSysLangs as $lid => $name)
	{
		$gLang = CForumGroup::GetLangByID($gid, $lid);
		$fields["LANG"][$lid] = array("LID" => $lid, "NAME" => $gLang["NAME"], "DESCRIPTION" => $gLang["DESCRIPTION"]);
	}
}
else
{
	$fields = array(
		"SORT" => 150,
		"PARENT_ID" => ($_REQUEST["PARENT_ID"] > 0 ? $_REQUEST["PARENT_ID"] : 0),
		"LANG" => $arSysLangs);
	foreach ($arSysLangs as $lid => $name)
		$fields["LANG"][$lid] = array("LID" => $lid, "NAME" => "", "DESCRIPTION" => "");
}

if ((array_key_exists("save", $_POST) || array_key_exists("apply", $_POST)) && $forumPermissions == "W" && check_bitrix_sessid())
{
	$fields["SORT"] = intval($_POST["SORT"]);
	$fields["PARENT_ID"] = intval($_REQUEST["FORUM_GROUP"]["PARENT_ID"]);

	foreach ($fields["LANG"] as $lid => $name)
	{
		$fields["LANG"][$lid] = array(
			"LID" => $lid,
			"NAME" => $_REQUEST["FORUM_GROUP"]["LANG"][$lid]["NAME"],
			"DESCRIPTION" => $_REQUEST["FORUM_GROUP"]["LANG"][$lid]["DESCRIPTION"]);
	}

	if (!CForumGroup::CheckFields(($ID > 0 ? "UPDATE" : "ADD"), $fields, ($ID > 0 ? $ID : false)))
	{
		$arError[] = array(
			"code" => "error_checkfields",
			"title" => GetMessage("ERROR_ADD_GROUP_BAD_FIELDS"));
	}
	else if ($ID > 0 && !CForumGroup::CanUserUpdateGroup($ID, $USER->GetUserGroupArray()))
	{
		$arError[] = array(
			"code" => "not_right_for_edit",
			"title" => GetMessage("ERROR_EDIT_GROUP_NOT_RIGHT"));
	}
	else if ($ID > 0 && (CForumGroup::Update($ID, $fields) != $ID))
	{
		$arError[] = array(
			"code" => "not_edit",
			"title" => GetMessage("ERROR_EDIT_GROUP"));
	}
	else if ($ID <= 0 && !CForumGroup::CanUserAddGroup($USER->GetUserGroupArray()))
	{
		$arError[] = array(
			"code" => "not_right_for_add",
			"title" => GetMessage("ERROR_ADD_GROUP_NOT_RIGHT"));
	}
	else if ($ID <= 0 && ($ID = intval(CForumGroup::Add($fields))) && $ID <= 0)
	{
		$arError[] = array(
			"code" => "not_add",
			"title" => GetMessage("ERROR_ADD_GROUP"));
	}
	else
	{
		BXClearCache(true, "bitrix/forum/group/");
		LocalRedirect((array_key_exists("save", $_POST) ? "forum_group.php?" : "forum_group_edit.php?ID=".$ID."&")."lang=".LANG.GetFilterParams("filter_", false));
	}

	$message = new CAdminMessage(($ID > 0 ? GetMessage("ERROR_EDIT_GROUP") : GetMessage("ERROR_ADD_GROUP")), $GLOBALS["APPLICATION"]->GetException());
}


$APPLICATION->SetTitle($ID > 0 ? str_replace("#ID#", $ID, GetMessage("FORUM_EDIT_RECORD")) : GetMessage("FORUM_NEW_RECORD"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
$aMenu = array(
	array(
		"TEXT" => GetMessage("FGN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_group.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list"));

if ($ID > 0 && $forumPermissions == "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$aMenu[] = array(
		"TEXT" => GetMessage("FGN_NEW_GROUP"),
		"LINK" => "/bitrix/admin/forum_group_edit.php?PARENT_ID=".$ID."&lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new");
	$aMenu[] = array(
		"TEXT" => GetMessage("FGN_COPY_GROUP"),
		"LINK" => "/bitrix/admin/forum_group_edit.php?".($ID > 0 ? "COPY_ID=".$ID."&" : "")."lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_copy");
	$aMenu[] = array(
		"TEXT" => GetMessage("FGN_DELETE_GROUP"), 
		"LINK" => "javascript:if(confirm('".GetMessage("FGN_DELETE_GROUP_CONFIRM")."')) window.location='/bitrix/admin/forum_group.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete");
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
if($message)
	echo $message->Show();

?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" name="fform">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>
<?
$aTabs = array( array("DIV" => "edit1", "TAB" => GetMessage("FGN_TAB_GROUP"), "ICON" => "forum", "TITLE" => GetMessage("FGN_TAB_GROUP_DESCR")) );
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();
if ($ID > 0):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?=$ID?></td>
	</tr>
<?endif;?>
	<tr>
		<td width="40%"><?=GetMessage("FORUM_SORT")?>:</td>
		<td width="60%"><input type="text" name="SORT" value="<?=$fields["SORT"]?>" size="10" /></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("FORUM_PARENT_ID")?>:</td>
		<td width="60%"><select name="FORUM_GROUP[PARENT_ID]"><?
	foreach ($arGroups as $res)
	{
		?><option <?
		if ($ID > 0 && ($ID == $res["ID"] || $fields["LEFT_MARGIN"] < $res["LEFT_MARGIN"] && $res["RIGHT_MARGIN"] < $fields["RIGHT_MARGIN"]))
		{
			?> disabled="disabled" <?
		}
		?>value="<?=$res["ID"]?>" <?=($res["ID"] == $fields["PARENT_ID"] ? "selected" : "")?>><?=str_pad("", ($res["DEPTH_LEVEL"] - 1), ".")?><?=$res["NAME"]?></option><?
	}
		?></select>
		</td>
	</tr>
	<?
	foreach ($fields["LANG"] as $lid => $res)
	{
		if (!array_key_exists($lid, $arSysLangs))
			continue;
	?>
	<tr class="heading">
		<td colspan="2">[<?=$lid?>] <?=$arSysLangs[$lid]?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("FORUM_NAME")?>:</td>
		<td><input type="text" name="FORUM_GROUP[LANG][<?=$lid?>][NAME]" value="<?=htmlspecialcharsbx($res["NAME"])?>" size="40" /></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORUM_DESCR")?>:</td>
		<td><textarea name="FORUM_GROUP[LANG][<?=$lid?>][DESCRIPTION]" rows="3" cols="40"><?=htmlspecialcharsbx($res["DESCRIPTION"])?></textarea></td>
	</tr>
	<?
	}

$tabControl->EndTab();
$tabControl->Buttons(
	array(
		"disabled" => ($forumPermissions < "W"),
		"back_url" => "/bitrix/admin/forum_group.php?lang=".LANG."&".GetFilterParams("filter_", false)
		)
	);
$tabControl->End();

$tabControl->ShowWarnings("fform", $message);
?>
</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>