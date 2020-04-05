<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php");

IncludeModuleLangFile(__FILE__);
define("HELP_FILE", "add_subscriber.php");

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("subscr_tab_subscriber"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("subscr_tab_subscriber_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("subscr_tab_subscription"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("subscr_tab_subscription_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// Id of the edited record
$strError = "";
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT>="W" && check_bitrix_sessid())
{
	$subscr = new CSubscription;
	$arFields = Array(
		"USER_ID"		=> ($ANONYMOUS == "Y"? false:$USER_ID),
		"ACTIVE"		=> ($ACTIVE <> "Y"? "N":"Y"),
		"FORMAT"		=> ($FORMAT <> "html"? "text":"html"),
		"EMAIL"			=> $EMAIL,
		"CONFIRMED"		=> ($CONFIRMED <> "Y"? "N":"Y"),
		"SEND_CONFIRM"		=> ($SEND_CONFIRM <> "Y"? "N":"Y"),
		"RUB_ID"		=> $RUB_ID,
		"ALL_SITES"		=> "Y",
	);
	if($ID>0)
	{
		$res = $subscr->Update($ID, $arFields, $SITE_ID);
	}
	else
	{
		$ID = $subscr->Add($arFields, $SITE_ID);
		$res = ($ID>0);
	}

	if($res)
	{
		if($apply!="")
			LocalRedirect("/bitrix/admin/subscr_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/subscr_admin.php?lang=".LANG);
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("subs_save_error"), $e);
		$bVarsFromForm = true;
	}
}

ClearVars();
$str_FORMAT = "text";
$str_ACTIVE = "Y";
$str_USER_ID = 0;

if($ID>0)
{
	$subscr = CSubscription::GetByID($ID);
	if(!$subscr->ExtractFields("str_"))
		$ID=0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_subscription", "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("subscr_title_edit").$ID : GetMessage("subscr_title_add")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("subscr_list_text"),
		"TITLE"=>GetMessage("subscr_list"),
		"LINK"=>"subscr_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("subscr_add_text"),
		"TITLE"=>GetMessage("subscr_mnu_add"),
		"LINK"=>"subscr_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("subscr_del_text"),
		"TITLE"=>GetMessage("subscr_mnu_del"),
		"LINK"=>"javascript:if(confirm('".GetMessage("subscr_mnu_del_conf")."'))window.location='subscr_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($_REQUEST["mess"] == "ok" && $ID>0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("subs_saved"), "TYPE"=>"OK"));
if($message)
	echo $message->Show();
?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>"  enctype="multipart/form-data" name="subscrform">
<?
$tabControl->Begin();
?>
<?
//********************
//Subscriber tab
//********************
$tabControl->BeginNextTab();
?>
	<?if($ID > 0):?>
		<tr>
			<td><?echo GetMessage("subscr_date_add")?></td>
			<td><?echo $str_DATE_INSERT;?></td>
		</tr>
		<?if($str_DATE_UPDATE <> ""):?>
			<tr>
				<td><?echo GetMessage("subscr_date_upd")?></td>
				<td><?echo $str_DATE_UPDATE;?></td>
			</tr>
		<?endif?>
	<?endif?>
	<tr>
		<td width="40%"><?echo GetMessage("subscr_conf")?></td>
		<td width="60%"><input type="checkbox" name="CONFIRMED" value="Y"<?if($str_CONFIRMED == "Y") echo " checked"?>></td>
	</tr>
	<?if($ID>0):?>
		<tr>
			<td><?echo GetMessage("subscr_conf_code")?></td>
			<td><?echo $str_CONFIRM_CODE?></td>
		</tr>
		<tr>
			<td><?echo GetMessage("subscr_date_conf")?></td>
			<td><?echo $str_DATE_CONFIRM;?></td>
		</tr>
	<?endif;?>
	<tr>
		<td><?echo GetMessage("subscr_anonym")?></td>
		<td><input type="checkbox" name="ANONYMOUS" value="Y"<?if((integer)$str_USER_ID==0) echo " checked"?> onClick="document.subscrform.USER_ID.disabled=document.subscrform.FindUser.disabled=this.checked;"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("subscr_user")?></td>
		<td>
		<?
		$sUser = "";
		if($ID > 0 && $str_USER_ID > 0)
		{
			$rsUser = CUser::GetByID($str_USER_ID);
			$arUser = $rsUser->GetNext();
			if($arUser)
				$sUser = "[<a href=\"user_edit.php?ID=".$arUser["ID"]."&amp;lang=".LANG."\">".$arUser["ID"]."</a>] (".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
		}
		echo FindUserID("USER_ID", ($str_USER_ID > 0? $str_USER_ID: ""), $sUser, "subscrform", "10", "", " ... ", "", "");

		if((integer)$str_USER_ID==0):
		?><script language="JavaScript">document.subscrform.USER_ID.disabled=document.subscrform.FindUser.disabled=true;</script><?
		endif;
		?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("subscr_active")?></td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>E-Mail:</td>
		<td><input type="text" name="EMAIL" value="<?echo $str_EMAIL;?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("subscr_send_conf")?></td>
		<td><input type="checkbox" name="SEND_CONFIRM" value="Y"<?if($SEND_CONFIRM == "Y") echo " checked"?> onClick="document.subscrform.SITE_ID.disabled=!this.checked;"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("subscr_templ")?></td>
		<td><?echo CSite::SelectBox("SITE_ID", $SITE_ID);?></td>
	</tr>
<?if($SEND_CONFIRM <> "Y"):?>
	<script language="JavaScript">document.subscrform.SITE_ID.disabled=true;</script>
<?endif;?>
<?
//********************
//Subscribtions tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td><?echo GetMessage("subscr_fmt")?></td>
		<td><input type="radio" id="FORMAT_1" name="FORMAT" value="text"<?if($str_FORMAT == "text") echo " checked"?>><label for="FORMAT_1"><?echo GetMessage("subscr_fmt_text")?></label>&nbsp;/<input type="radio" id="FORMAT_2" name="FORMAT" value="html"<?if($str_FORMAT == "html") echo " checked"?>><label for="FORMAT_2">HTML</label></td>
	</tr>
	<tr>
		<td width="40%" class="adm-detail-valign-top"><?echo GetMessage("subscr_rub")?></td>
		<td width="60%">
			<div class="adm-list">
			<?
			if($bVarsFromForm)
				$aSubscrRub = is_array($RUB_ID)? $RUB_ID: array();
			else
				$aSubscrRub = CSubscription::GetRubricArray($ID);

			$rsRubrics = CRubric::GetList(array("LID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), array("ACTIVE"=>"Y"));
			while($arRubric = $rsRubrics->GetNext()):?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_<?echo $arRubric["ID"]?>" name="RUB_ID[]" value="<?echo $arRubric["ID"]?>"<?if(in_array($arRubric["ID"], $aSubscrRub)) echo " checked"?>></div>
					<div class="adm-list-label"><label for="RUB_ID_<?echo $arRubric["ID"]?>"><?echo "[".$arRubric["LID"]."] ".$arRubric["NAME"]?></label></div>
				</div>
			<?endwhile;?>
			</div>
		</td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"subscr_admin.php?lang=".LANG,

	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?if($ID>0):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("subscrform", $message);
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>