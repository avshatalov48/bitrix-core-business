<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
$canRead = $USER->CanDoOperation('security_stat_activity_settings_read');
$canWrite = $USER->CanDoOperation('security_stat_activity_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$module_id = "statistic";
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

IncludeModuleLangFile(__FILE__);

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_STATACT_MAIN_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_STATACT_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "params",
		"TAB" => GetMessage("SEC_STATACT_PARAMS_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_STATACT_PARAMS_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$ID = intval($_REQUEST['ID']); // Id of the edited record
$strError = "";
$bVarsFromForm = false;
$bShowForce = false;
$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";

if($_SERVER['REQUEST_METHOD'] == "POST"
	&& (
		$_REQUEST['save'].$_REQUEST['apply'] != ''
		|| $_POST['DEFENCE_OFF'].$_POST['DEFENCE_ON'] != ''
	)
	&& $canWrite
	&& check_bitrix_sessid()
)
{
	if(isset($_POST["DEFENCE_OFF"]))
		COption::SetOptionString($module_id, "DEFENCE_ON", "N");
	elseif(isset($_POST["DEFENCE_ON"]))
		COption::SetOptionString($module_id, "DEFENCE_ON", "Y");

	COption::SetOptionInt($module_id, "DEFENCE_STACK_TIME", $_POST['DEFENCE_STACK_TIME']);
	COption::SetOptionInt($module_id, "DEFENCE_MAX_STACK_HITS", $_POST['DEFENCE_MAX_STACK_HITS']);
	COption::SetOptionInt($module_id, "DEFENCE_DELAY", $_POST['DEFENCE_DELAY']);
	COption::SetOptionString($module_id, "DEFENCE_LOG", $_POST['DEFENCE_LOG']==="Y"? "Y": "N");

	if($_REQUEST['save'] != "" && $_GET["return_url"] != "")
		LocalRedirect($_GET["return_url"]);

	LocalRedirect("/bitrix/admin/security_stat_activity.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
}

$DEFENCE_ON = COption::GetOptionString($module_id, "DEFENCE_ON");
$DEFENCE_STACK_TIME = COption::GetOptionString($module_id, "DEFENCE_STACK_TIME");
$DEFENCE_MAX_STACK_HITS = COption::GetOptionString($module_id, "DEFENCE_MAX_STACK_HITS");
$DEFENCE_DELAY = COption::GetOptionString($module_id, "DEFENCE_DELAY");
$DEFENCE_LOG = COption::GetOptionString($module_id, "DEFENCE_LOG");

$messageDetails = "";
if(COption::GetOptionString($module_id, "DEFENCE_ON")==="Y")
{
	$messageType = "OK";
	$messageText = GetMessage("SEC_STATACT_ON");
} else
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_STATACT_OFF");
}

$APPLICATION->SetTitle(GetMessage("SEC_STATACT_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage(array(
			"MESSAGE" => $messageText,
			"TYPE" => $messageType,
			"DETAILS" => $messageDetails,
			"HTML" => true
		));
?>

<form method="POST" action="security_stat_activity.php?lang=<?=LANGUAGE_ID?><?=$returnUrl?>"  enctype="multipart/form-data" name="editform">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2" align="left">
		<?if(COption::GetOptionString($module_id, "DEFENCE_ON")==="Y"):?>
			<input type="submit" name="DEFENCE_OFF" value="<?echo GetMessage("SEC_STATACT_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
		<?else:?>
			<input type="submit" name="DEFENCE_ON" value="<?echo GetMessage("SEC_STATACT_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
		<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_STATACT_NOTE")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
<?if (CModule::IncludeModule("fileman")):?>
	<tr>
		<td><?echo GetMessage("SEC_STATACT_503_TEMPLATE")?>:</td>
		<td><a href="/bitrix/admin/fileman_file_edit.php?lang=<?=LANGUAGE_ID?>&amp;full_src=Y&amp;path=%2Fbitrix%2Factivity_limit.php"><?echo GetMessage("SEC_STATACT_GRABBER_EDIT_503_TEMPLATE_LINK")?></a></td>
	</tr>
<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("SEC_STATACT_DEFENCE_DELAY")?>:</td>
		<td width="60%"><input size="3" type="text" name="DEFENCE_DELAY" id="DEFENCE_DELAY" value="<?=htmlspecialcharsbx($DEFENCE_DELAY)?>">&nbsp;<?echo GetMessage("SEC_STATACT_DEFENCE_DELAY_MEAS")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_STATACT_DEFENCE_STACK_TIME")?></td>
		<td><input size="3" type="text" name="DEFENCE_STACK_TIME" id="DEFENCE_STACK_TIME" value="<?=htmlspecialcharsbx($DEFENCE_STACK_TIME)?>">&nbsp;<?echo GetMessage("SEC_STATACT_DEFENCE_STACK_TIME_MEAS")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_STATACT_DEFENCE_MAX_HITS")?></td>
		<td><input size="3" type="text" name="DEFENCE_MAX_STACK_HITS" id="DEFENCE_MAX_STACK_HITS" value="<?=htmlspecialcharsbx($DEFENCE_MAX_STACK_HITS)?>">&nbsp;<?echo GetMessage("SEC_STATACT_DEFENCE_MAX_HITS_MEAS")?></td>
	</tr>
	<tr>
		<td nowrap><label for="DEFENCE_LOG"><?echo GetMessage("SEC_STATACT_DEFENCE_LOG", array("#HREF#"=>"/bitrix/admin/event_log.php?lang=".LANGUAGE_ID."&set_filter=Y&find_type=audit_type_id&find_audit_type[]=STAT_ACTIVITY_LIMIT"))?></label></td>
		<td><?echo InputType("checkbox", "DEFENCE_LOG", "Y", $DEFENCE_LOG)?></td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$canWrite),
		"back_url"=>$_GET["return_url"]? $_GET["return_url"]: "security_stat_activity.php?lang=".LANG,
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