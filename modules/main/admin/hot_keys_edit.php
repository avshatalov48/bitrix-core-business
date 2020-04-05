<?
// v.091

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");

ClearVars();

if(!$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$ID = intval($ID);

$hotKeyCodes = new CHotKeysCode;
$str_IS_CUSTOM = true;
$errMess = "";
$bVarsFromForm = false;

if($_SERVER['REQUEST_METHOD']=="POST" && ($_POST['save']<>"" || $_POST['apply']<>"") && check_bitrix_sessid())
{
	$arFields = array(
			"CLASS_NAME"=>$_REQUEST["CLASS_NAME"],
			"CODE"=>$_REQUEST["CODE"],
			"NAME"=>$_REQUEST["NAME"],
			"COMMENTS" => $_REQUEST["COMMENTS"],
			"TITLE_OBJ"=>$_REQUEST["TITLE_OBJ"],
			"URL"=>$_REQUEST["URL"],
	);

	if($ID>0)
		$res = $hotKeyCodes->Update($ID, $arFields);

	else
	{
		$ID = $hotKeyCodes->Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if(isset($_POST['apply']))
			LocalRedirect("hot_keys_edit.php?ID=".$ID."&lang=".LANG."&applied=ok");
		else
			LocalRedirect(($_REQUEST["addhk"]<>""? $_REQUEST["addhk"]:"hot_keys_list.php?lang=".LANG));
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$errMess = new CAdminMessage(GetMessage("HK_EDIT_ERROR"),$e);
		else
			$errMess = new CAdminMessage(GetMessage("HK_EDIT_ERROR"));

		$bVarsFromForm = true;
	}

}

if($ID>0)
{
	$hk = $hotKeyCodes->GetByID($ID);
	if(!($hk_arr = $hk->ExtractFields("str_")))
		$ID=0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_hot_keys_code", "", "str_");


$sDocTitle = ($ID>0? GetMessage("HK_EDIT_RECORD", array("#ID#"=>$ID)) : GetMessage("HK_NEW_RECORD"));

$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("HK_LIST"),
		"TITLE"=>GetMessage("HK_LIST_TITLE"),
		"LINK"=>"hot_keys_list.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("HK_ADD"),
		"TITLE"=>GetMessage("HK_ADD_TITLE"),
		"LINK"=>"hot_keys_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);

	if($str_IS_CUSTOM)
		$aMenu[] = array(
			"TEXT"=>GetMessage("HK_DELETE"),
			"TITLE"=>GetMessage("HK_DELETE_TITLE"),
			"LINK"=>"javascript:if(confirm('".GetMessage("HK_DEL_CONFIRM")."')) window.location='hot_keys_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
			"ICON"=>"btn_delete",
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($_GET["applied"]=="ok")
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("HK_EDIT_SUCCESS"), "TYPE"=>"OK"));

if($errMess)
	echo $errMess->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("HK_EDIT_TAB"), "TITLE"=>GetMessage("HK_EDIT_TAB_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


?>

<form method="POST" action="<?= $APPLICATION->GetCurPage()?>" name="hkform">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="IS_CUSTOM" value=<?=$str_IS_CUSTOM?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?if($_REQUEST["addhk"]<>""):?>
<input type="hidden" name="addhk" value="<?= htmlspecialcharsbx($_REQUEST["addhk"])?>">
<?endif;?>
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<?if($str_ID):?>
	<tr>
		<td><?= GetMessage("HK_ID");?>:</td>
		<td><?=intval($str_ID)?></td>
	</tr>
<?endif;?>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("HK_NAME")?>:</td>
		<td><input type="text" name="NAME" size="45" maxlength="255" value="<?=$str_IS_CUSTOM ? $str_NAME : GetMessage($str_NAME);?>" <?=$str_IS_CUSTOM ? '': 'disabled'?> ></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?=GetMessage("HK_CODE")?>:</td>
		<td><textarea name="CODE" rows="5" cols="50" maxlength="255" <?=$str_IS_CUSTOM ? '': 'disabled'?>><?=$str_CODE?></textarea></td>
	</tr>
	<tr>
		<td><?=GetMessage("HK_CLASS_NAME")?>:</td>
		<td><input type="text" name="CLASS_NAME" size="45" maxlength="255" value="<?=$str_CLASS_NAME?>" <?=$str_IS_CUSTOM ? '': 'disabled'?>></td>
	</tr>
	<tr>
		<td><?=GetMessage("HK_COMMENTS")?>:</td>
		<td><input type="text" name="COMMENTS" size="45" maxlength="255" value="<?=$str_IS_CUSTOM ? $str_COMMENTS : GetMessage($str_COMMENTS)?>" <?=$str_IS_CUSTOM ? '': 'disabled'?>></td>
	</tr>

	<tr>
		<td><?=GetMessage("HK_TITLE_OBJ")?>:</td>
		<td><input type="text" name="TITLE_OBJ" size="45" maxlength="255" value="<?=$str_TITLE_OBJ?>" <?=$str_IS_CUSTOM ? '': 'disabled'?>></td>
	</tr>

	<tr>
		<td><?=GetMessage("HK_URL")?>:</td>
		<td><input type="text" name="URL" size="45" maxlength="255" value="<?=$str_URL?>" <?=$str_IS_CUSTOM ? '': 'disabled'?>></td>
	</tr>

<?
$tabControl->Buttons(array(
	"disabled"=>$str_IS_CUSTOM ? false : true,
	"back_url"=>($_REQUEST["addhk"]<>""? $_REQUEST["addhk"]:"hot_keys_list.php?lang=".LANG),
));
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("hkform", $errMess);
?>

<?
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
