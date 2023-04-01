<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDataBase $DB
 **/

$canRead = $USER->CanDoOperation('security_iprule_settings_read');
$canWrite = $USER->CanDoOperation('security_iprule_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("SEC_IP_EDIT_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_IP_EDIT_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0; // Id of the edited record
$strError = "";
$bVarsFromForm = false;
$bShowForce = false;
$message = CSecurityIPRule::CheckAntiFile(true);

if($_SERVER["REQUEST_METHOD"] == "POST"
	&& (isset($_REQUEST["save"]) || isset($_REQUEST["apply"]))
	&& $canWrite
	&& check_bitrix_sessid())
{
	if(!is_array($_POST["INCL_IPS"]))
		$inclIps = array($_POST["INCL_IPS"]);
	else
		$inclIps = $_POST["INCL_IPS"];

	$filteredInclIps = preg_grep("#^\d{1,3}(\.\d{1,3}){3}#", $inclIps);
	if(empty($filteredInclIps))
		$APPLICATION->ThrowException(GetMessage("SEC_IP_EDIT_SAVE_ERROR_EMPTY_INCL_IPS"));
	unset($inclIps);

	if(!is_array($_POST["INCL_MASKS"]))
		$inclMasks = array($_POST["INCL_MASKS"]);
	else
		$inclMasks = $_POST["INCL_MASKS"];

	$filteredInclMasks = preg_grep("#^/#", $inclMasks);
	if(empty($filteredInclMasks))
		$APPLICATION->ThrowException(GetMessage("SEC_IP_EDIT_SAVE_ERROR_EMPTY_INCL_MASKS"));
	unset($inclMasks);

	if($e = $APPLICATION->GetException())
	{
		$message = new CAdminMessage(GetMessage("SEC_IP_EDIT_SAVE_ERROR"), $APPLICATION->GetException());
		$bVarsFromForm = true;
	}
	else
	{
		$ob = new CSecurityIPRule;
		$selfBlock = $ob->CheckIP($_POST["INCL_IPS"], $_POST["EXCL_IPS"]);

		if($selfBlock && (COption::GetOptionString("security", "ipcheck_allow_self_block")!=="Y"))
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("SEC_IP_EDIT_SAVE_ERROR"), $e);
			$bVarsFromForm = true;
		}
		elseif($selfBlock && $_POST["USE_THE_FORCE_LUK"]!=="Y")
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("SEC_IP_EDIT_SAVE_ERROR"), $e);
			$bVarsFromForm = true;
			$bShowForce = true;
		}
		else
		{
			$arFields = array(
				"RULE_TYPE" => "M",
				"ACTIVE" => $_POST["ACTIVE"],
				"ADMIN_SECTION" => $_POST["ADMIN_SECTION"],
				"SITE_ID" => $_POST["SITE_ID"]=="NOT_REF"? false: $_POST["SITE_ID"],
				"SORT" => $_POST["SORT"],
				"NAME" => $_POST["NAME"],
				"ACTIVE_FROM" => $_POST["ACTIVE_FROM"],
				"ACTIVE_TO" => $_POST["ACTIVE_TO"],
				"INCL_IPS" => $_POST["INCL_IPS"],
				"EXCL_IPS" => $_POST["EXCL_IPS"],
				"INCL_MASKS" => $_POST["INCL_MASKS"],
				"EXCL_MASKS" => $_POST["EXCL_MASKS"],
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
				if($_REQUEST["apply"] != "")
					LocalRedirect("/bitrix/admin/security_iprule_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
				else
					LocalRedirect("/bitrix/admin/security_iprule_list.php?lang=".LANG);
			}
			else
			{
				if($e = $APPLICATION->GetException())
					$message = new CAdminMessage(GetMessage("SEC_IP_EDIT_SAVE_ERROR"), $e);
				$bVarsFromForm = true;
			}
		}
	}
}

ClearVars("str_");
$str_ACTIVE = "Y";
$str_ADMIN_SECTION = "Y";
$str_SITE_ID = "";
$str_SORT = "500";
$str_NAME = "";
$str_ACTIVE_FROM = "";
$str_ACTIVE_TO = "";

if($ID>0)
{
	$rs = CSecurityIPRule::GetList(array(), array("=ID"=>$ID), array());
	if(!$rs->ExtractFields("str_"))
		$ID = 0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sec_iprule", "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("SEC_IP_EDIT_EDIT_TITLE") : GetMessage("SEC_IP_EDIT_ADD_TITLE")));

CUtil::InitJSCore();
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/interface.js');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("SEC_IP_EDIT_MENU_LIST"),
		"TITLE" => GetMessage("SEC_IP_EDIT_MENU_LIST_TITLE"),
		"LINK" => "security_iprule_list.php?lang=".LANG,
		"ICON" => "btn_list",
	)
);
if($ID > 0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT" => GetMessage("SEC_IP_EDIT_MENU_ADD"),
		"TITLE" => GetMessage("SEC_IP_EDIT_MENU_ADD_TITLE"),
		"LINK" => "security_iprule_edit.php?lang=".LANG,
		"ICON" => "btn_new",
	);
	$aMenu[] = array(
		"TEXT" => GetMessage("SEC_IP_EDIT_MENU_DELETE"),
		"TITLE" => GetMessage("SEC_IP_EDIT_MENU_DELETE_TITLE"),
		"LINK" => "javascript:if(confirm('".GetMessage("SEC_IP_EDIT_MENU_DELETE_CONF")."'))window.location='security_iprule_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message)
	echo $message->Show();
?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>"  enctype="multipart/form-data" name="editform">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<?if($ID > 0):?>
		<tr>
			<td><?echo GetMessage("SEC_IP_EDIT_ID")?>:</td>
			<td><?echo $str_ID;?></td>
		</tr>
	<?endif?>
	<tr>
		<td width="40%"><?echo GetMessage("SEC_IP_EDIT_ACTIVE")?>:</td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("SEC_IP_EDIT_ADMIN_SECTION")?>:</td>
		<td width="60%"><input type="checkbox" name="ADMIN_SECTION" value="Y"<?if($str_ADMIN_SECTION == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("SEC_IP_EDIT_SITE_ID")?>:</td>
		<td width="60%"><?echo CLang::SelectBox("SITE_ID", $str_SITE_ID, GetMessage("MAIN_ALL"));?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_IP_EDIT_SORT")?>:</td>
		<td><input type="text" size="5" name="SORT" value="<?echo $str_SORT?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_IP_EDIT_NAME")?>:</td>
		<td><input type="text" size="45" name="NAME" value="<?echo $str_NAME?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_IP_EDIT_ACTIVE_FROM")?>:</td>
		<td><?echo CAdminCalendar::CalendarDate("ACTIVE_FROM", $str_ACTIVE_FROM, 19, true)?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_IP_EDIT_ACTIVE_TO")?>:</td>
		<td><?echo CAdminCalendar::CalendarDate("ACTIVE_TO", $str_ACTIVE_TO, 19, true)?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo GetMessage("SEC_IP_EDIT_INCL_IPS")?>:<br><?echo GetMessage("SEC_IP_EDIT_INCL_IPS_SAMPLE")?></td>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbINCL_IPS">
				<?if($bVarsFromForm)
					$arIPs = $_POST["INCL_IPS"];
				else
					$arIPs = CSecurityIPRule::GetRuleInclIPs($ID);

				foreach($arIPs as $i => $ip):?>
					<tr><td style="padding-bottom: 3px;">
						<input type="text" size="30" value="<?echo htmlspecialcharsbx($ip)?>" name="INCL_IPS[<?echo htmlspecialcharsbx($i)?>]">
					</td></tr>
				<?endforeach;
				if(!$bVarsFromForm):?>
					<tr class="security-addable-row"><td style="padding-bottom: 3px;">
						<input type="text" size="30" value="" name="INCL_IPS[n0]">
					</td></tr>
				<?endif;?>
				<tr><td>
					<input type="button" id="add-button-incl-ips" value="<?echo GetMessage("SEC_IP_EDIT_ROW_ADD")?>">
				</td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top" style="padding-top:12px;"><?echo GetMessage("SEC_IP_EDIT_EXCL_IPS")?>:</td>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbEXCL_IPS">
				<?if($bVarsFromForm)
					$arIPs = $_POST["EXCL_IPS"];
				else
					$arIPs = CSecurityIPRule::GetRuleExclIPs($ID);

				foreach($arIPs as $i => $ip):?>
					<tr><td style="padding-bottom: 3px;">
						<input type="text" size="30" value="<?echo htmlspecialcharsbx($ip)?>" name="EXCL_IPS[<?echo htmlspecialcharsbx($i)?>]">
					</td></tr>
				<?endforeach;
				if(!$bVarsFromForm):?>
					<tr class="security-addable-row"><td style="padding-bottom: 3px;">
						<input type="text" size="30" value="" name="EXCL_IPS[n0]">
					</td></tr>
				<?endif;?>
				<tr><td>
					<input type="button" id="add-button-excl-ips" value="<?echo GetMessage("SEC_IP_EDIT_ROW_ADD")?>">
				</td></tr>
			</table>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo GetMessage("SEC_IP_EDIT_INCL_MASKS")?>:<br><?echo GetMessage("SEC_IP_EDIT_INCL_MASKS_SAMPLE")?></td>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbINCL_PATH">
				<?if($bVarsFromForm)
					$arMasks = $_POST["INCL_MASKS"];
				else
					$arMasks = CSecurityIPRule::GetRuleInclMasks($ID);

				foreach($arMasks as $i => $mask):?>
					<tr><td style="padding-bottom: 3px;">
						<input type="text" size="45" value="<?echo htmlspecialcharsbx($mask)?>" name="INCL_MASKS[<?echo htmlspecialcharsbx($i)?>]">
					</td></tr>
				<?endforeach;
				if(!$bVarsFromForm):?>
					<tr class="security-addable-row"><td style="padding-bottom: 3px;">
						<input type="text" size="45" value="" name="INCL_MASKS[n0]">
					</td></tr>
				<?endif;?>
				<tr><td>
					<input type="button" id="add-button-incl-masks" value="<?echo GetMessage("SEC_IP_EDIT_ROW_ADD")?>">
				</td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top" style="padding-top:12px;"><?echo GetMessage("SEC_IP_EDIT_EXCL_MASKS")?>:</td>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tbEXCL_PATH">
				<?if($bVarsFromForm)
					$arMasks = $_POST["EXCL_MASKS"];
				else
					$arMasks = CSecurityIPRule::GetRuleExclMasks($ID);

				foreach($arMasks as $i => $mask):?>
					<tr><td style="padding-bottom: 3px;">
						<input type="text" size="45" value="<?echo htmlspecialcharsbx($mask)?>" name="EXCL_MASKS[<?echo htmlspecialcharsbx($i)?>]">
					</td></tr>
				<?endforeach;
				if(!$bVarsFromForm):?>
					<tr class="security-addable-row"><td style="padding-bottom: 3px;">
						<input type="text" size="45" value="" name="EXCL_MASKS[n0]">
					</td></tr>
				<?endif;?>
				<tr><td>
					<input type="button" id="add-button-excl-masks" value="<?echo GetMessage("SEC_IP_EDIT_ROW_ADD")?>">
				</td></tr>
			</table>
		</td>
	</tr>
<script id="security-interface-settings" type="application/json">
	{
		"addableRows": [
			{
				"tableId": "tbINCL_IPS",
				"buttonId": "add-button-incl-ips"
			},
			{
				"tableId": "tbEXCL_IPS",
				"buttonId": "add-button-excl-ips"
			},
			{
				"tableId": "tbINCL_PATH",
				"buttonId": "add-button-incl-masks"
			},
			{
				"tableId": "tbEXCL_PATH",
				"buttonId": "add-button-excl-masks"
			}
		]
	}
</script>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$canWrite),
		"back_url"=>"security_iprule_list.php?lang=".LANG,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?if($ID>0):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?if($bShowForce && (COption::GetOptionString("security", "ipcheck_allow_self_block")==="Y")):?>
	<input type="hidden" name="USE_THE_FORCE_LUK" value="Y">
<?endif;?>
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("editform", $message);
?>

<?/*echo BeginNote();?>
<span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();*/?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>