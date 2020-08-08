<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/options_user_settings.php");
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

$canRead = $USER->CanDoOperation('security_otp_settings_read');
$canWrite = $USER->CanDoOperation('security_otp_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_OTP_NEW_MAIN_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_OTP_NEW_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "params",
		"TAB" => GetMessage("SEC_OTP_PARAMETERS_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_OTP_NEW_PARAMETERS_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";
if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["save"].$_REQUEST["apply"].$_REQUEST["otp_siteb"] !="" && $canWrite && check_bitrix_sessid())
{

	if($_REQUEST["otp_siteb"] != "")
		CSecurityUser::setActive($_POST["otp_active"]==="Y");

	$hotp_user_window = intval($_POST["window_size"]);
	if($hotp_user_window <= 0)
		$hotp_user_window = 10;
	COption::SetOptionString("security", "hotp_user_window", $hotp_user_window);

	COption::SetOptionString("security", "otp_allow_remember", $_POST["otp_allow_remember"]==="Y"? "Y": "N");

	COption::SetOptionString("security", "otp_allow_recovery_codes", $_POST["otp_allow_recovery_codes"]==="Y"? "Y": "N");

	if ($_POST['otp_default_type'])
		Bitrix\Security\Mfa\Otp::setDefaultType($_POST['otp_default_type']);

	if (is_numeric($_POST['otp_mandatory_skip_days']))
		Bitrix\Security\Mfa\Otp::setSkipMandatoryDays($_POST['otp_mandatory_skip_days']);

	Bitrix\Security\Mfa\Otp::setMandatoryUsing($_POST['otp_mandatory_using'] === 'Y');

	if (is_array($_POST['otp_mandatory_rights']))
		Bitrix\Security\Mfa\Otp::setMandatoryRights($_POST['otp_mandatory_rights']);

	if($_REQUEST["save"] != "" && $_GET["return_url"] != "")
		LocalRedirect($_GET["return_url"]);
	else
		LocalRedirect("/bitrix/admin/security_otp.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
}

$availableTypes = \Bitrix\Security\Mfa\Otp::getAvailableTypes();
$availableTypesDescription = \Bitrix\Security\Mfa\Otp::getTypesDescription();
$defaultType = \Bitrix\Security\Mfa\Otp::getDefaultType();
$targetRights = \Bitrix\Security\Mfa\Otp::getMandatoryRights();
$access = new CAccess();
$targetRightsNames = $access->GetNames($targetRights);

CJSCore::Init(array('access'));
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/page/otp.js');
$APPLICATION->SetTitle(GetMessage("SEC_OTP_NEW_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (CSecurityUser::isActive())
{
	$messageType = "OK";
	$messageText = GetMessage("SEC_OTP_NEW_ON");
}
else
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_OTP_NEW_OFF");
}

CAdminMessage::ShowMessage(array(
			"MESSAGE" => $messageText,
			"TYPE" => $messageType,
			"HTML" => true
		));
?>

<form method="POST" action="security_otp.php?lang=<?=LANGUAGE_ID?><?=htmlspecialcharsbx($returnUrl)?>" enctype="multipart/form-data" name="editform">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2" align="left">
		<?if(CSecurityUser::isActive()):?>
			<input type="hidden" name="otp_active" value="N">
			<input type="submit" name="otp_siteb" value="<?echo GetMessage("SEC_OTP_NEW_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
		<?else:?>
			<input type="hidden" name="otp_active" value="Y">
			<input type="submit" name="otp_siteb" value="<?echo GetMessage("SEC_OTP_NEW_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
		<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<div style=" padding: 20px; margin-top: 20px">
			<h3 style="clear:both"><br><?=getMessage('SEC_OTP_DESCRIPTION_INTRO_TITLE')?></h3>
			<div style="float: left; margin-right: 20px">
				<div style="-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border: 2px solid #e0e3e5; border-radius: 2px; height: 156px; width: 156px; background: white url(/bitrix/images/security/etoken_pass.png?v2) no-repeat center center;"></div>
			</div>
			<div>
				<?=(IsModuleInstalled('intranet')?
					getMessage('SEC_OTP_DESCRIPTION_INTRO_INTRANET'):
					getMessage('SEC_OTP_DESCRIPTION_INTRO_SITE'))?>
			</div>
			<?
			if (in_array(LANGUAGE_ID, array('en', 'ru', 'de'), true))
				$imageLanguage = LANGUAGE_ID;
			else
				$imageLanguage = \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
			?>
			<h3 style="clear:both"><br><?=getMessage('SEC_OTP_DESCRIPTION_USING_TITLE')?></h3>
			<div style="float: left; margin-right: 20px">
				<div style="-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border: 2px solid #e0e3e5; border-radius: 2px; padding: 5px 10px; background: white; height: 150px;">
					<div style="float: left; background: url(/bitrix/images/security/<?=$imageLanguage?>_login_step0.png) no-repeat top right; width: 220px; height: 120px; padding-top: 20px;" ><?=getMessage('SEC_OTP_DESCRIPTION_USING_STEP_0')?></div>
					<div style="float: left; background: url(/bitrix/images/security/<?=$imageLanguage?>_login_step1.png) no-repeat top right; width: 220px; height: 120px; padding-top: 20px; margin-left:20px;"><?=getMessage('SEC_OTP_DESCRIPTION_USING_STEP_1')?></div>
				</div>
			</div>
			<div>
				<?=getMessage('SEC_OTP_DESCRIPTION_USING')?>
			</div>
			<h3 style="clear:both"><br><?=getMessage('SEC_OTP_DESCRIPTION_ACTIVATION_TITLE')?></h3>
			<div>
				<?=getMessage('SEC_OTP_DESCRIPTION_ACTIVATION')?>
			</div>
			<?=BeginNote()?>
			<h3><?=getMessage('SEC_OTP_DESCRIPTION_ABOUT_TITLE')?></h3>
			<div>
				<?=getMessage('SEC_OTP_DESCRIPTION_ABOUT')?>
			</div>
			<?=EndNote()?>
		</div>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%">
			<?=GetMessage("SEC_OTP_WINDOW_SIZE")?>:
		</td>
		<td width="60%">
			<input type="text" size="4" name="window_size" value="<?=(int) COption::GetOptionInt("security", "hotp_user_window")?>">
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("SEC_OTP_DEFAULT_YPE")?>:
		</td>
		<td>
			<select name="otp_default_type">
				<?foreach($availableTypes as $value):?>
					<option value="<?=$value?>" <?=($defaultType === $value? 'selected': '')?>>
						<?=(isset($availableTypesDescription[$value]['title'])? $availableTypesDescription[$value]['title'] : $value)?>
					</option>
				<?endforeach?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("SEC_OTP_ALLOW_REMEMBER")?>:
		</td>
		<td>
			<input type="checkbox" name="otp_allow_remember" id="otp_allow_remember" value="Y" <?if(COption::GetOptionString("security", "otp_allow_remember") == "Y") echo "checked";?>>
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("SEC_OTP_ALLOW_RECOVERY_CODES")?>:
		</td>
		<td>
			<input type="checkbox" name="otp_allow_recovery_codes" id="otp_allow_recovery_codes" value="Y" <?if(COption::GetOptionString("security", "otp_allow_recovery_codes") == "Y") echo "checked";?>>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("SEC_OTP_NEW_MANDATORY_HEADER")?></td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("SEC_OTP_NEW_MANDATORY_USING")?>:
		</td>
		<td>
			<input type="checkbox" name="otp_mandatory_using" id="otp_mandatory_using" value="Y" <?=(COption::GetOptionString("security", "otp_mandatory_using") == "Y")? "checked": "";?>>
		</td>
	</tr>
	<tr data-hide-by-mandatory="yes" style="<?=(COption::GetOptionString("security", "otp_mandatory_using") == "Y")? "": "display: none;";?>">
		<td>
			<?=GetMessage("SEC_OTP_MANDATORY_SKIP_DAYS")?>:
		</td>
		<td>
			<input type="text" size="4" name="otp_mandatory_skip_days" id="otp_mandatory_skip_days"  value="<?=(int) COption::GetOptionInt("security", "otp_mandatory_skip_days")?>">
		</td>
	</tr>
	<tr data-hide-by-mandatory="yes" style="<?=(COption::GetOptionString("security", "otp_mandatory_using") == "Y")? "": "display: none;";?>">
		<td class="adm-detail-valign-top">
			<?=GetMessage("SEC_OTP_NEW_MANDATORY_RIGHTS")?>:
		</td>
		<td>
			<div id="bx_access_div">
				<?foreach($targetRights as $code):?>
				<?
					$value = ($targetRightsNames[$code]['provider']? $targetRightsNames[$code]['provider'].': ':'');
					$value .= $targetRightsNames[$code]['name'];
				?>
				<div style="margin-bottom:4px">
					<input type="hidden" name="otp_mandatory_rights[]" value="<?=htmlspecialcharsbx($code)?>">
					<?=htmlspecialcharsbx($value)?>&nbsp;<a href="javascript:void(0);" data-role="delete-access" data-code="<?=htmlspecialcharsbx($code)?>" class="access-delete"></a>
				</div>
				<?endforeach;?>
			</div>
			<a href="javascript:void(0)" class="bx-action-href" id="add_access" data-role="add-access"><?=GetMessage("SEC_OTP_MANDATORY_RIGHTS_SELECT")?></a>
		</td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$canWrite),
		"back_url"=>$_GET["return_url"]? $_GET["return_url"]: "security_otp.php?lang=".LANG,
	)
);
?>
<?
$tabControl->End();
?>
</form>
	<script id="settings" type="application/json"><?=\Bitrix\Main\Web\Json::encode(array(
			'rights' => array_flip($targetRights)
		))?></script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
