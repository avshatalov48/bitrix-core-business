<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/**
 * @var $APPLICATION CMain
 */
$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
\Bitrix\Main\Loader::includeModule("forum");
IncludeModuleLangFile(__FILE__);

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
//region Default values
$sysLangs = [];
$dbRes = CLanguage::GetList($by="sort", $order="desc", ["ACTIVE" => "Y"]);
while ($res = $dbRes->Fetch())
{
	$sysLangs[$res["LID"]] = htmlspecialcharsbx($res["NAME"]);
}
$groups = [["ID" => 0, "NAME" => "..."]];
$dbRes = \Bitrix\Forum\GroupTable::getList([
	"select" => ["ID", "SORT", "PARENT_ID", "NAME" => "LANG.NAME"],
	"order" => ["LEFT_MARGIN" => "ASC", "SORT" => "ASC"],
	"filter" => ["LANG.LID" => LANGUAGE_ID]
]);
while ($res = $dbRes->fetch())
{
	$groups[$res["ID"]] = $res;
}

$ID = intval($request->isPost() ? $request->getPost("ID") : $request->getQuery("ID"));
$gid = ($ID > 0 ? $ID : intval($request->getQuery("COPY_ID")));
if ($gid > 0 && ($dbRes = \Bitrix\Forum\GroupTable::getList([
		"select" => ["ID", "SORT", "PARENT_ID", "LANG_" => "LANG.*"],
		"filter" => ["ID" => $gid]
	])) && ($group = $dbRes->fetch()) && $group)
{
	$fields = [
		"ID" => $ID,
		"SORT" => $group["SORT"],
		"PARENT_ID" => $group["PARENT_ID"],
		"LANG" => []
	];
	do
	{
		$fields["LANG"][$group["LANG_LID"]] = [
			"LID" => $group["LANG_LID"],
			"NAME" => $group["LANG_NAME"],
			"DESCRIPTION" => $group["LANG_DESCRIPTION"]
		];
	} while ($group = $dbRes->fetch());
}
else
{
	$fields = [
		"ID" => 0,
		"SORT" => 150,
		"PARENT_ID" => ($_REQUEST["PARENT_ID"] > 0 ? $_REQUEST["PARENT_ID"] : 0),
		"LANG" => []
	];
}
foreach ($sysLangs as $lid => $name)
{
	if (!array_key_exists($lid, $fields["LANG"]))
	{
		$fields["LANG"][$lid] = [
			"LID" => $lid,
			"NAME" => "",
			"DESCRIPTION" => ""
		];
	}
}
//endregion

$arError = array();
//region Post actions
if (
		$forumPermissions == "W" &&
		$request->isPost() &&
		($request->getPost("save") !== null || $request->getPost("apply") !== null) &&
		check_bitrix_sessid()
)
{
	$fields["SORT"] = intval($request->getPost("SORT"));
	$data = $request->getPost("FORUM_GROUP");
	$fields["PARENT_ID"] = intval($data["PARENT_ID"]);

	foreach ($fields["LANG"] as $lid => $name)
	{
		$fields["LANG"][$lid] = array(
			"LID" => $lid,
			"NAME" => $data["LANG"][$lid]["NAME"],
			"DESCRIPTION" => $data["LANG"][$lid]["DESCRIPTION"]);
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
}
//endregion

$APPLICATION->SetTitle($ID > 0 ? str_replace("#ID#", $ID, GetMessage("FORUM_EDIT_RECORD")) : GetMessage("FORUM_NEW_RECORD"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
//region menu
$aMenu = [[
		"TEXT" => GetMessage("FGN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_group.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list"]];

if ($ID > 0 && $forumPermissions == "W")
{
	$aMenu[] = ["SEPARATOR" => "Y"];
	$aMenu[] = [
		"TEXT" => GetMessage("FGN_NEW_GROUP"),
		"LINK" => "/bitrix/admin/forum_group_edit.php?PARENT_ID=".$ID."&lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new"];
	$aMenu[] = [
		"TEXT" => GetMessage("FGN_COPY_GROUP"),
		"LINK" => "/bitrix/admin/forum_group_edit.php?".($ID > 0 ? "COPY_ID=".$ID."&" : "")."lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_copy"];
	$aMenu[] = [
		"TEXT" => GetMessage("FGN_DELETE_GROUP"),
		"LINK" => "javascript:if(confirm('".GetMessage("FGN_DELETE_GROUP_CONFIRM")."')) window.location='/bitrix/admin/forum_group.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete"];
}
(new CAdminContextMenu($aMenu))->Show();
//endregion
if (!empty($arError))
{
	echo (new CAdminMessage(
			($ID > 0 ? GetMessage("ERROR_EDIT_GROUP") : GetMessage("ERROR_ADD_GROUP")),
			$GLOBALS["APPLICATION"]->GetException()
	))->Show();
}

?>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" name="fform">
		<input type="hidden" name="Update" value="Y">
		<input type="hidden" name="lang" value="<?echo LANG ?>">
		<input type="hidden" name="ID" value="<?echo $ID ?>">
		<?=bitrix_sessid_post()?>
		<?
		$tabControl = new CAdminTabControl("tabControl", [["DIV" => "edit1", "TAB" => GetMessage("FGN_TAB_GROUP"), "ICON" => "forum", "TITLE" => GetMessage("FGN_TAB_GROUP_DESCR")]]);
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
			<td width="60%"><input type="text" name="SORT" value="<?=intval($fields["SORT"])?>" size="10" /></td>
		</tr>
		<tr>
			<td width="40%"><?=GetMessage("FORUM_PARENT_ID")?>:</td>
			<td width="60%"><select name="FORUM_GROUP[PARENT_ID]"><?
					foreach ($groups as $res)
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
			if (!array_key_exists($lid, $sysLangs))
				continue;
			?>
			<tr class="heading">
				<td colspan="2">[<?=htmlspecialcharsbx($lid)?>] <?=htmlspecialcharsbx($sysLangs[$lid])?></td>
			</tr>
			<tr class="adm-detail-required-field">
				<td><?=GetMessage("FORUM_NAME")?>:</td>
				<td><input type="text" name="FORUM_GROUP[LANG][<?=htmlspecialcharsbx($lid)?>][NAME]" value="<?=htmlspecialcharsbx($res["NAME"])?>" size="40" /></td>
			</tr>
			<tr>
				<td><?=GetMessage("FORUM_DESCR")?>:</td>
				<td><textarea name="FORUM_GROUP[LANG][<?=htmlspecialcharsbx($lid)?>][DESCRIPTION]" rows="3" cols="40"><?=htmlspecialcharsbx($res["DESCRIPTION"])?></textarea></td>
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
		?>
	</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>