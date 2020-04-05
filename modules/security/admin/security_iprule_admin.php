<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

$canRead = $USER->CanDoOperation('security_iprule_admin_settings_read');
$canWrite = $USER->CanDoOperation('security_iprule_admin_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_IPRULE_ADMIN_MAIN_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_IPRULE_ADMIN_MAIN_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);

$rsIPRule = CSecurityIPRule::GetList(array(), array(
	"=RULE_TYPE" => "A",
	"=ADMIN_SECTION" => "Y",
	"=SITE_ID" => false,
	"=SORT" => 10,
	"=ACTIVE_FROM" => false,
	"=ACTIVE_TO" => false,
), array("ID" => "ASC"));

$arIPRule = $rsIPRule->Fetch();
if($arIPRule)
{
	$ID = $arIPRule["ID"];
	$ACTIVE = $arIPRule["ACTIVE"];
}
else
{
	$ID = 0;
	$ACTIVE = "N";
}

$exclMasks=array();

foreach(GetModuleEvents("security", "OnIPRuleAdmin", true) as $event)
{
	$exclMasks = array_merge($exclMasks,ExecuteModuleEventEx($event));
}

$strError = "";
$bVarsFromForm = false;
$bShowForce = false;
$message = CSecurityIPRule::CheckAntiFile(true);

if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["save"].$_REQUEST["apply"].$_REQUEST["activate_iprule"].$_REQUEST["deactivate_iprule"] !="" && $canWrite && check_bitrix_sessid())
{
	$ob = new CSecurityIPRule;

	if(!$_REQUEST["activate_iprule"] && $_REQUEST["deactivate_iprule"])
	{
		//When rule is going to be deactivated we will no check for IP
		$noExclIPS = false;
		$selfBlock = false;
	}
	else
	{
		//Otherwise check if ANY input supplied
		$noExclIPS = true;
		foreach($_POST["EXCL_IPS"] as $ip)
		{
			if(strlen(trim($ip)) > 0)
			{
				$noExclIPS = false;
				break;
			}
		}
		//AND it is not selfblocking rule
		$INCL_IPS = array("0.0.0.1-255.255.255.255");
		$selfBlock = $ob->CheckIP($INCL_IPS, $_POST["EXCL_IPS"]);
	}

	if($noExclIPS)
	{
		$message = new CAdminMessage(GetMessage("SEC_IPRULE_ADMIN_NO_IP"));
		$bVarsFromForm = true;
	}
	elseif($selfBlock && (COption::GetOptionString("security", "ipcheck_allow_self_block")!=="Y"))
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("SEC_IPRULE_ADMIN_SAVE_ERROR"), $e);
		$bVarsFromForm = true;
	}
	elseif($selfBlock && $_POST["USE_THE_FORCE_LUK"]!=="Y")
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("SEC_IPRULE_ADMIN_SAVE_ERROR"), $e);
		$bVarsFromForm = true;
		$bShowForce = true;
	}
	else
	{
		$arFields = array(
			"RULE_TYPE" => "A",
			"ACTIVE" => $_REQUEST["activate_iprule"]? "Y": ($_REQUEST["deactivate_iprule"]? "N": $ACTIVE),
			"ADMIN_SECTION" => "Y",
			"SITE_ID" => false,
			"SORT" => 10,
			"NAME" => GetMessage("SEC_IPRULE_ADMIN_RULE_NAME"),
			"ACTIVE_FROM" => false,
			"ACTIVE_TO" => false,
			"INCL_IPS" => $INCL_IPS,
			"EXCL_IPS" => $_POST["EXCL_IPS"],
			"INCL_MASKS" => array("/bitrix/admin/*"),
			"EXCL_MASKS" => $exclMasks,
		);
		if($ID > 0)
		{
			$res = $ob->Update($ID, $arFields);
		}
		else
		{
			$ID = $ob->Add($arFields);
			$res = ($ID>0);
		}

		if($res)
		{
			if($_REQUEST["save"] != "" && $_GET["return_url"]!="")
				LocalRedirect($_GET["return_url"]);

			$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";
			LocalRedirect("/bitrix/admin/security_iprule_admin.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("SEC_IPRULE_ADMIN_SAVE_ERROR"), $e);
			$bVarsFromForm = true;
		}
	}
}

$messageDetails = "";
if ($ID > 0 && $ACTIVE=="Y")
{
	$messageType = "OK";
	$messageText = GetMessage("SEC_IPRULE_ADMIN_ON");
} else
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_IPRULE_ADMIN_OFF");
}

$APPLICATION->SetTitle(GetMessage("SEC_IPRULE_ADMIN_TITLE"));

CUtil::InitJSCore();
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/interface.js');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($message)
	echo $message->Show();

CAdminMessage::ShowMessage(array(
			"MESSAGE"=>$messageText,
			"TYPE"=>$messageType,
			"DETAILS"=>$messageDetails,
			"HTML"=>true
		));
?>

	<form method="POST" action="security_iprule_admin.php?lang=<?echo LANGUAGE_ID?><?echo $_GET["return_url"]? "&amp;return_url=".urlencode($_GET["return_url"]): ""?>"  enctype="multipart/form-data" name="editform">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2" align="left">
		<?if($ID > 0 && $ACTIVE=="Y"):?>
			<input type="submit" name="deactivate_iprule" value="<?echo GetMessage("SEC_IPRULE_ADMIN_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
		<?else:?>
			<input type="submit" name="activate_iprule" value="<?echo GetMessage("SEC_IPRULE_ADMIN_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
		<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_IPRULE_ADMIN_NOTE", array("#IP#" => $_SERVER["REMOTE_ADDR"]))?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$arExclIPs = array();
if($bVarsFromForm)
{
	if(is_array($_POST["EXCL_IPS"]))
		foreach($_POST["EXCL_IPS"] as $i => $ip)
			$arExclIPs[] = htmlspecialcharsbx($ip);
}
elseif($ID > 0)
{
	$ar = CSecurityIPRule::GetRuleExclIPs($ID);
	foreach($ar as $i => $ip)
		$arExclIPs[] = htmlspecialcharsbx($ip);
}
?>
<tr>
	<td class="adm-detail-valign-top" width="40%"><?echo GetMessage("SEC_IPRULE_ADMIN_EXCL_IPS")?>:<br><?echo GetMessage("SEC_IPRULE_ADMIN_EXCL_IPS_SAMPLE")?></td>
	<td width="60%">
	<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbEXCL_IPS">
		<?foreach($arExclIPs as $i => $ip):?>
			<tr><td nowrap style="padding-bottom: 3px;">
				<input type="text" size="45" name="EXCL_IPS[<?echo $i?>]" value="<?echo $ip?>">
			</td></tr>
		<?endforeach;?>
		<?if(!$bVarsFromForm):?>
			<tr class="security-addable-row"><td nowrap style="padding-bottom: 3px;">
				<input type="text" size="45" name="EXCL_IPS[n0]" value="">
			</td></tr>
		<?endif;?>
			<tr><td>
				<br><input type="button" id="add-button" value="<?echo GetMessage("SEC_IPRULE_ADMIN_ADD")?>">
			</td></tr>
		</table>
	</td>
</tr>
<?
if (count($exclMasks) > 0)
{
?>
<tr>
	<td class="adm-detail-valign-top" width="40%"><?echo GetMessage("SEC_IPRULE_ADMIN_EXCL_FILES_".(($ACTIVE == 'Y')?'ACTIVE':'INACTIVE'))?></td>
	<td width="60%">
		<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbEXCL_FILES">
		<?foreach($exclMasks as $mask):?>
			<tr><td nowrap>
				<?echo htmlspecialcharsbx($mask)?>
			</td></tr>
		<?endforeach;?>
		</table>
	</td>
</tr>
<?
}
?>
<script id="security-interface-settings" type="application/json">
	{
		"addableRows": [{
			"tableId": "tbEXCL_IPS",
			"buttonId": "add-button"
		}]
	}
</script>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$canWrite),
		"back_url"=>$_GET["return_url"]? $_GET["return_url"]: "security_iprule_admin.php?lang=".LANG,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?if($bShowForce && (COption::GetOptionString("security", "ipcheck_allow_self_block")==="Y")):?>
	<input type="hidden" name="USE_THE_FORCE_LUK" value="Y">
<?endif;?>
<?
$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>