<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

$canRead = $USER->CanDoOperation('security_frame_settings_read');
$canWrite = $USER->CanDoOperation('security_frame_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_FRAME_MAIN_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_FRAME_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "exceptions",
		"TAB" => GetMessage("SEC_FRAME_EXCEPTIONS_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_FRAME_EXCEPTIONS_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$bVarsFromForm = false;
$_GET["return_url"] = $_GET["return_url"] ?? "";

if($_SERVER["REQUEST_METHOD"] == "POST"
	&& (isset($_REQUEST["save"]) || isset($_REQUEST["apply"]) || isset($_REQUEST["frame_siteb"]))
	&& $canWrite
	&& check_bitrix_sessid())
{
	if($_REQUEST["frame_siteb"] != "")
		CSecurityFrame::SetActive($_POST["frame_active"]==="Y");

	CSecurityFrameMask::Update($_POST["FRAME_MASKS"]);

	if(isset($_REQUEST["save"]) && $_GET["return_url"] != "")
		LocalRedirect($_GET["return_url"]);

	$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";
	LocalRedirect("/bitrix/admin/security_frame.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
}

$rsSecurityFrameExclMask = CSecurityFrameMask::GetList();
if($rsSecurityFrameExclMask->Fetch())
	$bSecurityFrameExcl = true;
else
	$bSecurityFrameExcl = false;

$messageDetails = "";
if (CHTMLPagesCache::IsOn())
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_FRAME_HTML_CACHE");
}
else if (CSecurityFrame::IsActive())
{
	$messageType = "OK";
	$messageText = GetMessage("SEC_FRAME_ON");
	if($bSecurityFrameExcl)
		$messageDetails = "<span style=\"font-style: italic;\">".GetMessage("SEC_FRAME_EXCL_FOUND")."</span>";
} else
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_FRAME_OFF");
}

$APPLICATION->SetTitle(GetMessage("SEC_FRAME_TITLE"));

CUtil::InitJSCore();
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/interface.js');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage(array(
			"MESSAGE"=>$messageText,
			"TYPE"=>$messageType,
			"DETAILS"=>$messageDetails,
			"HTML"=>true
		)
);
?>

<form method="POST" action="security_frame.php?lang=<?echo LANGUAGE_ID?><?echo $_GET["return_url"]? "&amp;return_url=".urlencode($_GET["return_url"]): ""?>" enctype="multipart/form-data" name="editform">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2" align="left">
<?if(CSecurityFrame::IsActive()):?>
		<input type="hidden" name="frame_active" value="N">
		<input type="submit" name="frame_siteb" value="<?echo GetMessage("SEC_FRAME_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
<?else:?>
		<input type="hidden" name="frame_active" value="Y">
		<input type="submit" name="frame_siteb" value="<?echo GetMessage("SEC_FRAME_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_FRAME_NOTE")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
$arMasks = array();
if($bVarsFromForm)
{
	if(is_array($_POST["FRAME_MASKS"]))
		foreach($_POST["FRAME_MASKS"] as $i => $POST_MASK)
			$arMasks[] = array(
				"SITE_ID" => htmlspecialcharsbx($POST_MASK["SITE_ID"]),
				"FRAME_MASK" => htmlspecialcharsbx($POST_MASK["FRAME_MASK"]),
			);
}
else
{
	$rs = CSecurityFrameMask::GetList();
	while($ar = $rs->Fetch())
		$arMasks[] = array(
			"SITE_ID" => htmlspecialcharsbx($ar["SITE_ID"]),
			"FRAME_MASK" => htmlspecialcharsbx($ar["FRAME_MASK"]),
		);
}
?>
<tr>
	<td class="adm-detail-valign-top" width="40%"><?echo GetMessage("SEC_FRAME_MASKS")?></td>
	<td width="60%">
	<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbFRAME_MASKS">
		<?foreach($arMasks as $i => $arMask):?>
			<tr><td nowrap style="padding-bottom: 3px;">
				<input type="text" size="45" name="FRAME_MASKS[<?echo $i?>][MASK]" value="<?echo $arMask["FRAME_MASK"]?>">&nbsp;<?echo GetMessage("SEC_FRAME_SITE")?>&nbsp;<?echo CSite::SelectBox("FRAME_MASKS[$i][SITE_ID]", $arMask["SITE_ID"], GetMessage("MAIN_ALL"), "");?><br>
			</td></tr>
		<?endforeach;?>
		<?if(!$bVarsFromForm):?>
			<tr class="security-addable-row"><td nowrap style="padding-bottom: 3px;">
				<input type="text" size="45" name="FRAME_MASKS[n0][MASK]" value="">&nbsp;<?echo GetMessage("SEC_FRAME_SITE")?>&nbsp;<?echo CSite::SelectBox("FRAME_MASKS[n0][SITE_ID]", "", GetMessage("MAIN_ALL"), "");?><br>
			</td></tr>
		<?endif;?>
			<tr><td>
				<br><input type="button" id="add-button" value="<?echo GetMessage("SEC_FRAME_ADD")?>">
			</td></tr>
		</table>
	</td>
</tr>
<script id="security-interface-settings" type="application/json">
	{
		"addableRows": [{
			"tableId": "tbFRAME_MASKS",
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
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>