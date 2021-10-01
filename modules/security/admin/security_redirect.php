<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/
$canRead = $USER->CanDoOperation('security_redirect_settings_read');
$canWrite = $USER->CanDoOperation('security_redirect_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_REDIRECT_MAIN_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_REDIRECT_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "parameters",
		"TAB" => GetMessage("SEC_REDIRECT_PARAMETERS_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_REDIRECT_PARAMETERS_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";

if(
	$_SERVER['REQUEST_METHOD'] == "POST"
	&& $_REQUEST['save'].$_REQUEST['apply'].$_REQUEST['redirect_button'] != ""
	&& $canWrite && check_bitrix_sessid()
)
{

	if($_REQUEST['redirect_button']!="")
		CSecurityRedirect::SetActive($_POST["redirect_active"] === "Y");

	COption::SetOptionString("security", "redirect_log", $_POST["redirect_log"]==="Y"? "Y": "N");
	COption::SetOptionString("security", "redirect_referer_check", $_POST["redirect_referer_check"]==="Y"? "Y": "N");
	COption::SetOptionString("security", "redirect_referer_site_check", $_POST["redirect_referer_site_check"]==="Y"? "Y": "N");
	COption::SetOptionString("security", "redirect_href_sign", $_POST["redirect_href_sign"]==="Y"? "Y": "N");
	if ($_POST["redirect_action"] === "show_message_and_stay")
	{
		COption::SetOptionString("security", "redirect_action", $_POST["redirect_action"]);
		COption::RemoveOption("security", "redirect_message_warning");
		$l = CLanguage::GetList();
		while($ar = $l->Fetch())
		{
			$mess = trim($_POST["redirect_message_warning_".$ar["LID"]]);
			if($mess <> '')
				COption::SetOptionString("security", "redirect_message_warning_".$ar["LID"], $mess);
			else
				COption::RemoveOption("security", "redirect_message_warning_".$ar["LID"]);
		}
		COption::SetOptionString("security", "redirect_message_charset", LANG_CHARSET);
	}
	else
	{
		COption::SetOptionString("security", "redirect_action", "force_url");
		COption::SetOptionString("security", "redirect_url", $_POST["redirect_url"]);
	}

	CSecurityRedirect::Update($_POST["URLS"]);

	if($_REQUEST["save"] != "" && $_GET["return_url"] != "")
		LocalRedirect($_GET["return_url"]);
	else
		LocalRedirect("/bitrix/admin/security_redirect.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
}

$APPLICATION->SetTitle(GetMessage("SEC_REDIRECT_TITLE"));

CUtil::InitJSCore();
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/interface.js');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (CSecurityRedirect::IsActive())
{
	$messageType = "OK";
	$messageText = GetMessage("SEC_REDIRECT_ON");
}
else
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_REDIRECT_OFF");
}

CAdminMessage::ShowMessage(array(
			"MESSAGE" => $messageText,
			"TYPE" => $messageType,
			"HTML" => true
		));
?>

<form method="POST" action="security_redirect.php?lang=<?=LANGUAGE_ID?><?=htmlspecialcharsbx($returnUrl)?>" enctype="multipart/form-data" name="editform">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
<?if(CSecurityRedirect::IsActive()):?>
	<tr>
		<td colspan="2" align="left">
			<input type="hidden" name="redirect_active" value="N">
			<input type="submit" name="redirect_button" value="<?echo GetMessage("SEC_REDIRECT_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
		</td>
	</tr>
<?else:?>
	<tr>
		<td colspan="2" align="left">
			<input type="hidden" name="redirect_active" value="Y">
			<input type="submit" name="redirect_button" value="<?echo GetMessage("SEC_REDIRECT_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
		</td>
	</tr>
<?endif?>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_REDIRECT_NOTE")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
$arSysUrls = array();
$arUsrUrls = array();
$rs = CSecurityRedirect::GetList();
while($ar = $rs->Fetch())
{
	if($ar["IS_SYSTEM"] == "Y")
		$arSysUrls[] = array(
			"URL" => htmlspecialcharsbx($ar["URL"]),
			"PARAMETER_NAME" => htmlspecialcharsbx($ar["PARAMETER_NAME"]),
		);
	else
		$arUsrUrls[] = array(
			"URL" => htmlspecialcharsbx($ar["URL"]),
			"PARAMETER_NAME" => htmlspecialcharsbx($ar["PARAMETER_NAME"]),
		);
}
?>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("SEC_REDIRECT_METHODS_HEADER")?></td>
</tr>
<tr>
	<td class="adm-detail-valign-top" width="40%"><?echo GetMessage("SEC_REDIRECT_METHODS")?></td>
	<td width="60%">
		<div class="adm-list">
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" name="redirect_referer_check" id="redirect_referer_check" value="Y" <?if(COption::GetOptionString("security", "redirect_referer_check") == "Y") echo "checked";?>></div>
				<div class="adm-list-label"><label for="redirect_referer_check"><?echo GetMessage("SEC_REDIRECT_REFERER_CHECK")?></label></div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" name="redirect_referer_site_check" id="redirect_referer_site_check" value="Y" <?if(COption::GetOptionString("security", "redirect_referer_site_check") == "Y") echo "checked";?>></div>
				<div class="adm-list-label"><label for="redirect_referer_site_check"><?echo GetMessage("SEC_REDIRECT_REFERER_SITE_CHECK")?></label></div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" name="redirect_href_sign" id="redirect_href_sign" value="Y" <?if(COption::GetOptionString("security", "redirect_href_sign") == "Y") echo "checked";?>></div>
				<div class="adm-list-label"><label for="redirect_href_sign"><?echo GetMessage("SEC_REDIRECT_HREF_SIGN")?></label></div>
			</div>
		</div>
	</td>
</tr>
<tr>
	<td class="adm-detail-valign-top"><?echo GetMessage("SEC_REDIRECT_URLS")?>:</td>
	<td>
		<table cellpadding="2" cellspacing="2" border="0" width="100%" id="tbURLS">
		<? if (!empty($arSysUrls)): ?>
		<tr><td nowrap style="padding-bottom: 3px;">
			<?echo GetMessage("SEC_REDIRECT_SYSTEM")?>
		</td></tr>
		<?foreach($arSysUrls as $i => $arUrl):?>
			<tr><td nowrap style="padding-bottom: 3px;">
				<?echo GetMessage("SEC_REDIRECT_URL")?>&nbsp;<input type="text" size="35" name="URLS[<?echo $i?>][URL]" value="<?echo $arUrl["URL"]?>" disabled>&nbsp;<?echo GetMessage("SEC_REDIRECT_PARAMETER_NAME")?>&nbsp;<input type="text" size="15" name="URLS[<?echo $i?>][PARAMETER_NAME]" value="<?echo $arUrl["PARAMETER_NAME"]?>" disabled><br>
			</td></tr>
		<?endforeach;?>
		<? endif ?>

		<tr><td nowrap style="padding-bottom: 3px;">
			<?echo GetMessage("SEC_REDIRECT_USER")?>
		</td></tr>
		<?foreach($arUsrUrls as $i => $arUrl):?>
			<tr><td nowrap style="padding-bottom: 3px;">
				<?echo GetMessage("SEC_REDIRECT_URL")?>&nbsp;<input type="text" size="35" name="URLS[<?echo $i?>][URL]" value="<?echo $arUrl["URL"]?>">&nbsp;<?echo GetMessage("SEC_REDIRECT_PARAMETER_NAME")?>&nbsp;<input type="text" size="15" name="URLS[<?echo $i?>][PARAMETER_NAME]" value="<?echo $arUrl["PARAMETER_NAME"]?>"><br>
			</td></tr>
		<?endforeach;?>
		<tr class="security-addable-row"><td nowrap>
			<?echo GetMessage("SEC_REDIRECT_URL")?>&nbsp;<input type="text" size="35" name="URLS[n0][URL]" value="">&nbsp;<?echo GetMessage("SEC_REDIRECT_PARAMETER_NAME")?>&nbsp;<input type="text" size="15" name="URLS[n0][PARAMETER_NAME]" value=""><br>
		</td></tr>
		<tr><td>
			<br><input type="button" id="add-button" value="<?echo GetMessage("SEC_REDIRECT_ADD")?>">
		</td></tr>
	</table>
	</td>
</tr>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("SEC_REDIRECT_ACTIONS_HEADER")?></td>
</tr>
<tr>
	<td  class="adm-detail-valign-top" width="40%"><?echo GetMessage("SEC_REDIRECT_ACTIONS")?></td>
	<td width="60%">
		<script>
		function Toggle(input)
		{
			BX('redirect_url').disabled = !BX('redirect_force_url').checked;
			var c = arLangs.length;
			for (var i = 0; i < c; i++)
			{
				BX('redirect_message_warning_'+arLangs[i]).disabled = BX('redirect_force_url').checked;
			}
		}
		</script>
		<label><input
			type="radio"
			name="redirect_action"
			id="redirect_show_message_and_stay"
			value="show_message_and_stay"
			<?if(COption::GetOptionString("security", "redirect_action") == "show_message_and_stay") echo "checked";?>
			onClick="Toggle(this);"
		><?echo GetMessage("SEC_REDIRECT_SHOW_MESSAGE_AND_STAY")?></label><br>

		<table style="margin-left:24px">
		<?
		$disabled = COption::GetOptionString("security", "redirect_action") == "force_url";
		$l = CLanguage::GetList();
		$arLangs = array();
		while($ar = $l->GetNext()):?>
			<tr class="adm-detail-valign-top">
				<td><?echo GetMessage("SEC_REDIRECT_MESSAGE")." (".$ar["LID"]."):"?></td>
				<td><textarea
					name="redirect_message_warning_<?echo $ar["LID"]?>"
					id="redirect_message_warning_<?echo $ar["LID"]?>"
					cols=40
					rows=5
					<?if($disabled) echo "disabled";?>
				><?
				$mess = trim(COption::GetOptionString("security", "redirect_message_warning_".$ar["LID"]));
				if($mess == '')
					$mess = trim(COption::GetOptionString("security", "redirect_message_warning"));
				if($mess == '')
					$mess = trim(CSecurityRedirect::GetDefaultMessage($ar["LID"]));
				echo htmlspecialcharsbx($mess);
				$arLangs[] = $ar["LID"];
				?></textarea></td>
			</tr>
		<?endwhile?>
		<tr>
			<td>
				<script>
					var arLangs = <?echo CUtil::PHPToJSObject($arLangs);?>;
				</script>
			</td>
			<td>
			</td>
		</tr>
		</table>
		<label><input type="radio" name="redirect_action" id="redirect_force_url" value="force_url" <?if(COption::GetOptionString("security", "redirect_action") == "force_url") echo "checked";?> onClick="Toggle(this);"><?echo GetMessage("SEC_REDIRECT_ACTION_REDIRECT")?></label><br>
		<table style="margin-left:24px">
		<tr>
			<td>
				<?echo GetMessage("SEC_REDIRECT_ACTION_REDIRECT_URL")?>
			</td>
			<td>
				<input
					type="text"
					name="redirect_url"
					id="redirect_url"
					value="<?echo htmlspecialcharsbx(COption::GetOptionString("security", "redirect_url"))?>"
					size="45"
					<?if(COption::GetOptionString("security", "redirect_action") != "force_url") echo "disabled";?>
				>
		</td></tr>
		</table>
	</td>
</tr>
<tr>
	<td width="40%"><label for="filter_log"><?echo GetMessage("SEC_REDIRECT_LOG")?></label>:</td>
	<td width="60%">
		<input type="checkbox" name="redirect_log" id="redirect_log" value="Y" <?if(COption::GetOptionString("security", "redirect_log") == "Y") echo "checked";?>>
	</td>
</tr>
<script id="security-interface-settings" type="application/json">
	{
		"addableRows": [{
			"tableId": "tbURLS",
			"buttonId": "add-button"
		}]
	}
</script>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$canWrite),
		"back_url"=>$_GET["return_url"]? $_GET["return_url"]: "security_redirect.php?lang=".LANG,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>