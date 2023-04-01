<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

$canRead = $USER->CanDoOperation('security_filter_settings_read');
$canWrite = $USER->CanDoOperation('security_filter_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_FILTER_MAIN_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_FILTER_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "params",
		"TAB" => GetMessage("SEC_FILTER_PARAMETERS_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_FILTER_PARAMETERS_TAB_TITLE"),
	),
	array(
		"DIV" => "exceptions",
		"TAB" => GetMessage("SEC_FILTER_EXCEPTIONS_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_FILTER_EXCEPTIONS_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$bVarsFromForm = false;
$_GET["return_url"] = $_GET["return_url"] ?? "";

if($_SERVER["REQUEST_METHOD"] == "POST"
	&& (isset($_REQUEST["save"]) || isset($_REQUEST["apply"]) || isset($_REQUEST["filter_siteb"]))
	&& $canWrite
	&& check_bitrix_sessid())
{

	if($_REQUEST["filter_siteb"]!="")
		CSecurityFilter::SetActive($_POST["filter_active"]==="Y");

	if($_POST["filter_action"]==="clear")
		COption::SetOptionString("security", "filter_action", "clear");
	elseif($_POST["filter_action"]==="none")
		COption::SetOptionString("security", "filter_action", "none");
	else
		COption::SetOptionString("security", "filter_action", "filter");

	COption::SetOptionString("security", "filter_stop", isset($_POST["filter_stop"]) && $_POST["filter_stop"]==="Y"? "Y": "N");
	COption::SetOptionInt("security", "filter_duration", $_POST["filter_duration"]);
	COption::SetOptionString("security", "filter_log", isset($_POST["filter_log"]) && $_POST["filter_log"]==="Y"? "Y": "N");

	CSecurityFilterMask::Update($_POST["FILTER_MASKS"]);

	if(isset($_REQUEST["save"]) && $_GET["return_url"] != "")
		LocalRedirect($_GET["return_url"]);

	$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";
	LocalRedirect("/bitrix/admin/security_filter.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
}

$rsSecurityFilterExclMask = CSecurityFilterMask::GetList();
if($rsSecurityFilterExclMask->Fetch())
	$bSecurityFilterExcl = true;
else
	$bSecurityFilterExcl = false;

$messageDetails = "";
if (CSecurityFilter::IsActive())
{
	$messageType = "OK";
	$messageText = GetMessage("SEC_FILTER_ON");
	if($bSecurityFilterExcl)
		$messageDetails = "<span style=\"font-style: italic;\">".GetMessage("SEC_FILTER_EXCL_FOUND")."</span>";
} else
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_FILTER_OFF");
}

$APPLICATION->SetTitle(GetMessage("SEC_FILTER_TITLE"));

CUtil::InitJSCore();
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/interface.js');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage(array(
			"MESSAGE"=>$messageText,
			"TYPE"=>$messageType,
			"DETAILS"=>$messageDetails,
			"HTML"=>true
		));
?>

<form method="POST" action="security_filter.php?lang=<?echo LANGUAGE_ID?><?echo $_GET["return_url"]? "&amp;return_url=".urlencode($_GET["return_url"]): ""?>" enctype="multipart/form-data" name="editform">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2" align="left">
<?if(CSecurityFilter::IsActive()):?>
		<input type="hidden" name="filter_active" value="N">
		<input type="submit" name="filter_siteb" value="<?=GetMessage("SEC_FILTER_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
<?else:?>
		<input type="hidden" name="filter_active" value="Y">
		<input type="submit" name="filter_siteb" value="<?=GetMessage("SEC_FILTER_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_FILTER_NOTE")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td width="40%" class="adm-detail-valign-top"><?echo GetMessage("SEC_FILTER_ACTION")?>:</td>
	<td width="60%">
		<label><input type="radio" name="filter_action" value="filter" <?if(COption::GetOptionString("security", "filter_action") != "clear" && COption::GetOptionString("security", "filter_action") != "none") echo "checked";?>><?echo GetMessage("SEC_FILTER_ACTION_FILTER")?><span class="required"><sup>1</sup></span></label><br>
		<label><input type="radio" name="filter_action" value="clear" <?if(COption::GetOptionString("security", "filter_action") == "clear") echo "checked";?>><?echo GetMessage("SEC_FILTER_ACTION_CLEAR")?></label><br>
		<label><input type="radio" name="filter_action" value="none" <?if(COption::GetOptionString("security", "filter_action") == "none") echo "checked";?>><?echo GetMessage("SEC_FILTER_ACTION_NONE")?></label><br>
	</td>
</tr>
<tr>
	<td><label for="filter_stop"><?echo GetMessage("SEC_FILTER_STOP")?></label><span class="required"><sup>2</sup></span>:</td>
	<td>
		<input type="checkbox" name="filter_stop" id="filter_stop" value="Y" <?if(COption::GetOptionString("security", "filter_stop") == "Y") echo "checked";?>>
	</td>
</tr>
<tr>
	<td><label for="filter_duration"><?echo GetMessage("SEC_FILTER_DURATION")?></label>:</td>
	<td>
		<input type="text" size="4" name="filter_duration" value="<?echo COption::GetOptionInt("security", "filter_duration")?>">
	</td>
</tr>
<tr>
	<td><label for="filter_log"><?echo GetMessage("SEC_FILTER_LOG")?></label>:</td>
	<td>
		<input type="checkbox" name="filter_log" id="filter_log" value="Y" <?if(COption::GetOptionString("security", "filter_log") == "Y") echo "checked";?>>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?>
		<span class="required"><sup>1</sup></span><?echo GetMessage("SEC_FILTER_ACTION_NOTE_1")?><br>
		<span class="required"><sup>2</sup></span><?echo GetMessage("SEC_FILTER_ACTION_NOTE_2")?><br>
		<?echo EndNote();?>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
$arMasks = array();
if($bVarsFromForm)
{
	if(is_array($_POST["FILTER_MASKS"]))
		foreach($_POST["FILTER_MASKS"] as $i => $POST_MASK)
			$arMasks[] = array(
				"SITE_ID" => htmlspecialcharsbx($POST_MASK["SITE_ID"]),
				"FILTER_MASK" => htmlspecialcharsbx($POST_MASK["FILTER_MASK"]),
			);
}
else
{
	$rs = CSecurityFilterMask::GetList();
	while($ar = $rs->Fetch())
		$arMasks[] = array(
			"SITE_ID" => htmlspecialcharsbx($ar["SITE_ID"]),
			"FILTER_MASK" => htmlspecialcharsbx($ar["FILTER_MASK"]),
		);
}
?>
<tr>
	<td class="adm-detail-valign-top" width="40%"><?echo GetMessage("SEC_FILTER_MASKS")?></td>
	<td width="60%">
	<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbFILTER_MASKS">
		<?foreach($arMasks as $i => $arMask):?>
			<tr><td nowrap style="padding-bottom: 3px;">
				<input type="text" size="45" name="FILTER_MASKS[<?echo $i?>][MASK]" value="<?echo $arMask["FILTER_MASK"]?>">&nbsp;<?echo GetMessage("SEC_FILTER_SITE")?>&nbsp;<?echo CSite::SelectBox("FILTER_MASKS[$i][SITE_ID]", $arMask["SITE_ID"], GetMessage("MAIN_ALL"), "");?><br>
			</td></tr>
		<?endforeach;?>
		<?if(!$bVarsFromForm):?>
			<tr class="security-addable-row"><td nowrap style="padding-bottom: 3px;">
				<input type="text" size="45" name="FILTER_MASKS[n0][MASK]" value="">&nbsp;<?echo GetMessage("SEC_FILTER_SITE")?>&nbsp;<?echo CSite::SelectBox("FILTER_MASKS[n0][SITE_ID]", "", GetMessage("MAIN_ALL"), "");?><br>
			</td></tr>
		<?endif;?>
			<tr><td>
				<br><input type="button" id="add-button" value="<?echo GetMessage("SEC_FILTER_ADD")?>">
			</td></tr>
		</table>
	</td>
</tr>
<script id="security-interface-settings" type="application/json">
	{
		"addableRows": [{
			"tableId": "tbFILTER_MASKS",
			"buttonId": "add-button"
		}]
	}
</script>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$canWrite),
		"back_url"=>$_GET["return_url"]? $_GET["return_url"]: "security_iprule_list.php?lang=".LANG,
	)
);
?>
<?
$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>