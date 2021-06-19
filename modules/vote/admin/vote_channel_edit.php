<?
##############################################
# Bitrix Site Manager Forum					 #
# Copyright (c) 2002-2009 Bitrix			 #
# http://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");

ClearVars();

$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE", "vote_channel_list.php");

$arrSites = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch()) $arrSites[$ar["ID"]] = $ar;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("VOTE_PROP"), "ICON" => "main_channel_edit", "TITLE" => GetMessage("VOTE_GRP_PROP")),
	array("DIV" => "edit2", "TAB" => GetMessage("VOTE_ACCESS"), "ICON" => "main_channel_edit", "TITLE" => GetMessage("VOTE_RIGHTS")),

);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$message = null;
/********************************************************************
				Functions
********************************************************************/

/********************************************************************
				Actions
********************************************************************/
$ID = intval($ID);

if ((!empty($save) || !empty($apply)) && $REQUEST_METHOD == "POST" && $VOTE_RIGHT>="W" && check_bitrix_sessid())
{
	$arFields = array_intersect_key($_REQUEST,
		array_flip(array("TITLE", "SYMBOLIC_NAME", "ACTIVE", "HIDDEN", "C_SORT", "VOTE_SINGLE", "USE_CAPTCHA", "SITE", "GROUP_ID")));
	if (is_array($arFields["SITE"]))
		$arFields["FIRST_SITE_ID"] = reset($arFields["SITE"]);
	foreach(array("ACTIVE", "HIDDEN", "VOTE_SINGLE", "USE_CAPTCHA") as $key)
		if (!isset($arFields[$key]))
			$arFields[$key] = "N";
	foreach(array("SITE", "GROUP_ID") as $key)
		if (!isset($arFields[$key]))
			$arFields[$key] = array();

	$res = ($ID > 0 ? CVoteChannel::Update($ID, $arFields) : CVoteChannel::Add($arFields));
	if ($res > 0)
	{
		if (!empty($save))
			LocalRedirect("vote_channel_list.php?lang=".LANGUAGE_ID);
		LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ID=".$res."&".$tabControl->ActiveTabParam());
	}
	else
	{
		$bVarsFromForm = true;
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("VOTE_GOT_ERROR"), $e);
	}
}

$db_res = ($ID > 0 ? CVoteChannel::GetByID($ID) : false);
if (!($db_res && ($res = $db_res->Fetch())))
{
	$APPLICATION->SetTitle(GetMessage("VOTE_NEW_RECORD"));
	if ($ID > 0 && $message == null)
		$message = new CAdminMessage(GetMessage("VOTE_CHANNEL_IS_NOT_EXISTS", array("#ID#" => $ID)));
	$ID = 0;
	$res = array(
		"TITLE" => "",
		"SYMBOLIC_NAME" => "",
		"ACTIVE" => "Y",
		"HIDDEN" => "N",
		"C_SORT" => 100,
		"VOTE_SINGLE" => "Y",
		"USE_CAPTCHA" => "N",
		"SITE" => array_keys($arrSites),
		"GROUP_ID" => array());
}
else
{
	$APPLICATION->SetTitle(GetMessage("VOTE_EDIT_RECORD", array("#ID#" => $ID)));
	$res["SITE"] = CVoteChannel::GetSiteArray($ID);
	$res["GROUP_ID"] =  CVoteChannel::GetArrayGroupPermission($ID);
}
if (isset($bVarsFromForm) && $bVarsFromForm == true)
	$res = array_intersect_key($_REQUEST, $res);
foreach($res as $k => $v)
	$res[$k] = htmlspecialcharsEx($res[$k]);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/********************************************************************
				Form
********************************************************************/
$aMenu = array(
	array(
		"TEXT"	=> GetMessage("VOTE_LIST"),
		"TITLE" => GetMessage("VOTE_RECORDS_LIST"),
		"LINK"	=> "/bitrix/admin/vote_channel_list.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_list"
	)
);
if ($ID>0)
{
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_VOTES").($res["VOTES"] ? " [".$res["VOTES"]."]" : ""),
		"TITLE"	=> GetMessage("VOTE_VOTES_TITLE"),
		"LINK"	=> "/bitrix/admin/vote_list.php?lang=".LANGUAGE_ID."&find_channel_id=$ID&set_filter=Y",
	);

	if ($VOTE_RIGHT=="W")
	{
		$aMenu[] = array(
			"TEXT"	=> GetMessage("VOTE_CREATE"),
			"TITLE"	=> GetMessage("VOTE_CREATE_NEW_RECORD"),
			"LINK"	=> "/bitrix/admin/vote_channel_edit.php?lang=".LANGUAGE_ID,
			"ICON" => "btn_new");

		$aMenu[] = array(
			"TEXT"	=> GetMessage("VOTE_DELETE"),
			"TITLE"	=> GetMessage("VOTE_DELETE_RECORD"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("VOTE_DELETE_RECORD_CONFIRM")."')) window.location='/bitrix/admin/vote_channel_list.php?action=delete&ID=".$ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
			"ICON" => "btn_delete");
	}

	if ($VOTE_RIGHT=="W")
	{
		$aMenu[] = array(
			"TEXT"	=> GetMessage("VOTE_CREATE_VOTE"),
			"TITLE"	=> GetMessage("VOTE_VOTE_ADD"),
			"LINK"	=> "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&CHANNEL_ID=$ID",
			"ICON" => "btn_new");
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message) echo $message->Show();

?>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>" name="post_form">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
?>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<? if (!empty($res["TIMESTAMP_X"]) && $res["TIMESTAMP_X"] != "00.00.0000 00:00:00") : ?>
	<tr>
		<td><?=GetMessage("VOTE_TIMESTAMP")?></td>
		<td><?=$res["TIMESTAMP_X"]?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td width="40%"><?=GetMessage("VOTE_ACTIVE")?></td>
		<td width="60%"><?=InputType("checkbox","ACTIVE","Y",$res["ACTIVE"],false)?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_HIDDEN")?></td>
		<td><?=InputType("checkbox", "HIDDEN", "Y", $res["HIDDEN"], false)?> </td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_VOTE_SINGLE")?></td>
		<td><?=InputType("checkbox","VOTE_SINGLE","Y",$res["VOTE_SINGLE"],false)?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_USE_CAPTCHA")?></td>
		<td><?=InputType("checkbox","USE_CAPTCHA","Y",$res["USE_CAPTCHA"],false)?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_SORTING")?></td>
		<td><input type="text" name="C_SORT" size="5" value="<?=$res["C_SORT"]?>"></td>
	</tr>
	<tr valign="top" class="adm-detail-required-field">
		<td><?=GetMessage("VOTE_SITE")?></td>
		<td>
			<div class="adm-list">
			<?
		foreach ($arrSites as $sid => $arrS):
			$checked = (is_array($res["SITE"]) && in_array($sid, $res["SITE"]) ? "checked" : "");
			?>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" name="SITE[]" value="<?=htmlspecialcharsex($sid)?>" id="<?=htmlspecialcharsex($sid)?>" class="typecheckbox" <?=$checked?> /></div>
				<div class="adm-list-label"><label for="<?=htmlspecialcharsbx($sid)?>"><?echo '[<a title="'.GetMessage("VOTE_SITE_EDIT").'" href="/bitrix/admin/site_edit.php?LID='.htmlspecialcharsbx($sid).'&lang='.LANGUAGE_ID.'">'.htmlspecialcharsbx($sid).'</a>]&nbsp;'.htmlspecialcharsex($arrS["NAME"])?></label></div>
			</div>
			<?
		endforeach;
		?></div></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("VOTE_SYMBOLIC_NAME")?></td>
		<td><input type="text" name="SYMBOLIC_NAME" size="60" maxlength="255" value="<?=$res["SYMBOLIC_NAME"]?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("VOTE_TITLE")?></td>
		<td><input type="text" name="TITLE" size="60" maxlength="255" value="<?=$res["TITLE"]?>"></td>
	</tr>
<?
//********************
//Permissions Tab
//********************
$tabControl->BeginNextTab();

	$db_res = CGroup::GetList("sort", "asc", Array("ADMIN" => "N"));
	while ($group = $db_res->GetNext())
	{
		$perm = (!empty($res["GROUP_ID"]) ?
			(array_key_exists($group["ID"], $res["GROUP_ID"]) ? $res["GROUP_ID"][$group["ID"]] : 0) : 2);
	?>
	<tr>
		<td width="40%"><?=$group["NAME"].":"?></td>
		<td width="60%"><?=SelectBoxFromArray("GROUP_ID[".$group["ID"]."]", $GLOBALS["aVotePermissions"], $perm);?></td>
	</tr>
	<?}?>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>($VOTE_RIGHT<"W"), "back_url"=>"vote_channel_list.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<?
$tabControl->ShowWarnings("post_form", $message);
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
