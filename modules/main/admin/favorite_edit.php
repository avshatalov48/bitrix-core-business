<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "favorites/favorite_edit.php");

ClearVars();

function UserInfo($USER_ID)
{
	if(intval($USER_ID) <= 0)
		return "";
	$user = CUser::GetByID($USER_ID);
	if($user_arr = $user->Fetch())
		return '[<a title="'.GetMessage("MAIN_USER_PROFILE").'" href="user_edit.php?ID='.$user_arr["ID"].'&amp;lang='.LANG.'">'.$user_arr["ID"].'</a>] ('.htmlspecialcharsbx($user_arr["LOGIN"]).') '.htmlspecialcharsbx($user_arr["NAME"]).' '.htmlspecialcharsbx($user_arr["LAST_NAME"]);
}

if(!$USER->CanDoOperation('edit_own_profile') && !$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$ID = intval($_REQUEST['ID'] ?? 0);
$message = null;
$bVarsFromForm = false;

//check access rights
if($ID > 0 && !$isAdmin)
{
	$db_fav = CFavorites::GetByID($ID);
	if(($db_fav_arr = $db_fav->Fetch()) && $USER->GetID() <> $db_fav_arr["USER_ID"])
	{
		CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("fav_edit_access_error"), "DETAILS"=>GetMessage("fav_edit_access_error_mess")));
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
	}
}

if($_SERVER['REQUEST_METHOD']=="POST" && (!empty($_POST['save']) || !empty($_POST['apply'])) && check_bitrix_sessid())
{
	$arFields = array(
		"C_SORT"		=> $_POST['C_SORT'],
		"~TIMESTAMP_X"	=> $DB->GetNowFunction(),
		"MODIFIED_BY"	=> $USER->GetID(),
		"NAME"			=> $_POST['NAME'],
		"URL"			=> $_POST['URL'],
		"MENU_ID"		=> $_POST['MENU_ID'],
		"COMMENTS"		=> $_POST['COMMENTS'],
		"LANGUAGE_ID"	=> $_POST['LANGUAGE_ID'],
	);
	if($ID == 0)
	{
		$arFields["COMMON"] = "N";
		$arFields["USER_ID"] = $USER->GetID();
		$arFields["~DATE_CREATE"] = $DB->GetNowFunction();
		$arFields["CREATED_BY"] = $USER->GetID();
	}
	if($isAdmin)
	{
		$arFields["COMMON"] = ($_POST['COMMON'] == "Y"? "Y" : "N");
		$arFields["USER_ID"] = ($arFields["COMMON"] == "Y"? false : $_POST['USER_ID']);
		$arFields["MODULE_ID"] = ($arFields["COMMON"] == "Y" && $_POST['MODULE_ID'] <> ""? $_POST['MODULE_ID'] : false);
	}

	if($ID>0)
		$res = CFavorites::Update($ID, $arFields);
	else
	{
		$ID = CFavorites::Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if($apply <> "")
		{
			\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["FAVORITES_EDIT_MESSAGE"]=array("MESSAGE"=>GetMessage("fav_edit_success"), "TYPE"=>"OK");
			LocalRedirect("favorite_edit.php?ID=".$ID."&lang=".LANG);
		}
		else
			LocalRedirect(($_REQUEST["addurl"]<>""? $_REQUEST["addurl"]:"favorite_list.php?lang=".LANG));
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("fav_edit_error"), $e);
		$bVarsFromForm = true;
	}

}

$str_NAME = htmlspecialcharsbx($_REQUEST["name"]);
$str_URL = htmlspecialcharsbx($_REQUEST["addurl"]);
$str_C_SORT = 100;
$str_COMMON = 'N';
$str_USER_ID = $USER->GetID();
$str_LANGUAGE_ID = LANGUAGE_ID;

if($ID>0)
{
	$fav = CFavorites::GetByID($ID);
	if(!($fav_arr = $fav->ExtractFields("str_")))
		$ID=0;
}
if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_favorite", "", "str_");

$sDocTitle = ($ID>0? GetMessage("MAIN_EDIT_RECORD", array("#ID#"=>$ID)) : GetMessage("MAIN_NEW_RECORD"));

$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("MAIN_RECORDS_LIST"),
		"TITLE"=>GetMessage("fav_edit_list_title"),
		"LINK"=>"favorite_list.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("fav_edit_add"),
		"TITLE"=>GetMessage("fav_edit_add_title"),
		"LINK"=>"favorite_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("fav_edit_del"),
		"TITLE"=>GetMessage("fav_edit_del_title"),
		"LINK"=>"javascript:if(confirm('".GetMessage("fav_edit_del_conf")."')) window.location='favorite_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if(is_array(\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["FAVORITES_EDIT_MESSAGE"]))
{
	CAdminMessage::ShowMessage(\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["FAVORITES_EDIT_MESSAGE"]);
	\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["FAVORITES_EDIT_MESSAGE"]=false;
}

if($message)
	echo $message->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("fav_edit_tab"), "TITLE"=>GetMessage("fav_edit_tab_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" name="favform">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?if (!empty($_REQUEST["addurl"])):?>
<input type="hidden" name="addurl" value="<?echo htmlspecialcharsbx($_REQUEST["addurl"])?>">
<?endif;?>
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<? if ($str_TIMESTAMP_X <> '') : ?>
	<tr>
		<td><?echo GetMessage("MAIN_TIMESTAMP_X")?></td>
		<td><?=$str_TIMESTAMP_X?> / <?echo UserInfo($str_MODIFIED_BY)?></td>
	</tr>
	<? endif; ?>
	<? if ($str_DATE_CREATE <> '') : ?>
	<tr>
		<td><?echo GetMessage("MAIN_CREATED")?></td>
		<td><?=$str_DATE_CREATE?> / <?echo UserInfo($str_CREATED_BY)?></td>
	</tr>
	<? endif; ?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("MAIN_NAME")?></td>
		<td width="60%"><input type="text" name="NAME" size="45" maxlength="255" value="<?=$str_NAME?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("fav_edit_link")?></td>
		<td><input type="text" name="URL" size="45" maxlength="2000" value="<?=$str_URL?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("MAIN_C_SORT")?></td>
		<td><input type="text" name="C_SORT" size="5" maxlength="18" value="<?echo $str_C_SORT?>"></td>
	</tr>
	<tr >
		<td><?echo GetMessage("fav_edit_lang")?></td>
		<td><?echo CLanguage::SelectBox("LANGUAGE_ID", $str_LANGUAGE_ID)?></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("MAIN_MENU_ID")?></td>
		<td width="60%"><input type="text" name="MENU_ID" size="45" maxlength="255" value="<?=$str_MENU_ID?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("MAIN_COMMENTS")?></td>
		<td><textarea name="COMMENTS" class="textarea" rows="5" cols="40"><?echo $str_COMMENTS?></textarea></td>
	</tr>
<?
if($isAdmin):
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("fav_edit_admin")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("fav_edit_common")?></td>
		<td><input type="checkbox" name="COMMON" value="Y"<?if($str_COMMON == "Y") echo " checked"?> onClick="EnableControls(this.checked)"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("fav_edit_user")?></td>
		<td><?
		$sUser = "";
		if($ID>0)
			$sUser = UserInfo($str_USER_ID);
		echo FindUserID("USER_ID", ($str_USER_ID>0? $str_USER_ID:""), $sUser, "favform", "10", "", " ... ", "", "");
		?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("fav_edit_modules")?></td>
		<td>
<select name="MODULE_ID">
	<option value=""><?echo GetMessage("fav_edit_modules_not")?></option>
<?
$a = CModule::GetDropDownList();
while($ar = $a->fetch()):
?>
	<option value="<?echo htmlspecialcharsbx($ar["REFERENCE_ID"])?>"<?if($ar["REFERENCE_ID"] == $str_MODULE_ID) echo " selected"?>><?echo htmlspecialcharsbx($ar["REFERENCE"])?></option>
<?endwhile?>
</select>
<script>
function EnableControls(checked)
{
document.favform.USER_ID.disabled = document.favform.FindUser.disabled = checked;
document.favform.MODULE_ID.disabled = !checked;
}
EnableControls(document.favform.COMMON.checked);
</script>
		</td>
	</tr>
<?
endif; //$isAdmin
?>
<?
$tabControl->Buttons(array(
	"disabled"=>false,
	"back_url"=>($_REQUEST["addurl"]<>""? $_REQUEST["addurl"]:"favorite_list.php?lang=".LANG),
));
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("favform", $message);
?>

<?
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>