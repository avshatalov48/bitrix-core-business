<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

switch($_REQUEST['bxsender']):

/**********************************************************/
/************** core_window standard dialogs **************/
/**********************************************************/

	case 'core_window_cdialog':
	case 'core_window_cadmindialog':
?>
<script type="text/javascript" bxrunfirst="true">top.BX.WindowManager.Get().Authorize(<?=CUtil::PhpToJsObject($arAuthResult)?>)</script>
<?
	break;

/**********************************************************/
/************** Admin Wizard Dialog***********************/
/**********************************************************/

	case 'admin_wizard_dialog':
?>
<script type="text/javascript" bxrunfirst="true">
	(new top.BX.CAuthDialog({
		content_url: "/bitrix/admin/wizard_install.php",
		auth_result: <?=CUtil::PhpToJsObject($arAuthResult)?>,
		callback: function() {
			var frameWindow = top.WizardWindow.currentFrame.contentWindow;
			var reloadForm = frameWindow.document.forms["wizard_reload_form"];
			var submitButton = reloadForm.elements["reload_submit"];
			submitButton.click();
		}
	})).Show();
</script>

<form action="<?=$APPLICATION->GetCurPageParam(bitrix_sessid_get(), Array("sessid"))?>" method="post" name="wizard_reload_form">
	<input type="submit" name="reload_submit" value="Y" style="display: none;">
	<?=CAdminUtil::dumpVars($_POST, array("USER_LOGIN", "USER_PASSWORD", "sessid"));?>
</form>
<?
	break;

/**********************************************************/
/************** WYSIWYG editor requests *******************/
/**********************************************************/

	case 'fileman_html_editor':
?>
<script type="text/javascript" bxrunfirst="true">
	top.BX.onCustomEvent(top, 'OnHtmlEditorRequestAuthFailure', ['<?= CUtil::JSEscape($_REQUEST['bxeditor'])?>', <?=CUtil::PhpToJsObject($arAuthResult)?>]);
</script>
<?
	break;

/***************************************************************************************************/
/*************** core window auth dialog - we shold return auth form content ***********************/
/***************************************************************************************************/

	case 'core_window_cauthdialog':

		$store_password = COption::GetOptionString("main", "store_password", "Y");
		$bNeedCaptcha = $APPLICATION->NeedCAPTHAForLogin($last_login);

		ob_start();
?>
<form name="form_auth" method="post" action="" novalidate>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="AUTH">

	<div class="bx-core-popup-auth-field">
		<div class="bx-core-popup-auth-field-caption"><?=GetMessage("AUTH_LOGIN")?></div>
		<div class="bx-core-popup-auth-field"><input type="text" name="USER_LOGIN" value="<?echo htmlspecialcharsbx($last_login)?>"></div>
	</div>
	<div class="bx-core-popup-auth-field">
		<div class="bx-core-popup-auth-field-caption"><?=GetMessage("AUTH_PASSWORD")?></div>
		<div class="bx-core-popup-auth-field"><input type="password" name="USER_PASSWORD"></div>
	</div>

<?
		if($store_password=="Y"):
?>
	<div class="bx-core-popup-auth-field">
		<input type="checkbox" class="adm-designed-checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y">
		<label for="USER_REMEMBER" class="adm-designed-checkbox-label"></label><label for="USER_REMEMBER">&nbsp;<?=GetMessage("AUTH_REMEMBER_ME")?></label>
	</div>
<?
		endif;

		$CAPTCHA_CODE = '';
		if($bNeedCaptcha):
			$CAPTCHA_CODE = $APPLICATION->CaptchaGetCode();
?>
	<input type="hidden" name="captcha_sid" value="<?=$CAPTCHA_CODE?>" />
	<div class="bx-core-popup-auth-field">
		<div class="bx-core-popup-auth-field-caption">
			<div><?=GetMessage("AUTH_CAPTCHA_PROMT")?></div>
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$CAPTCHA_CODE?>" width="180" height="40" alt="CAPTCHA" />
		</div>
		<div class="bx-core-popup-auth-field"><input type="text" name="captcha_word"></div>
	</div>
<?
		endif; // $bNeedCaptcha
?>
</form>
<?
		$form = ob_get_contents();
		ob_end_clean();
?>
<script type="text/javascript">
var authWnd = top.BX.WindowManager.Get();
authWnd.SetTitle('<?=GetMessageJS('AUTH_TITLE')?>');
authWnd.SetContent('<?=CUtil::JSEscape($form)?>');
authWnd.SetError(<?=CUtil::PhpToJsObject($arAuthResult)?>);
authWnd.adjustSizeEx();
</script>
<?
		if(!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
		{
			$sec = new CRsaSecurity();
			if(($arKeys = $sec->LoadKeys()))
			{
				$sec->SetKeys($arKeys);
				$sec->AddToForm('form_auth', array('USER_PASSWORD'));
			}
		}

	break;
endswitch;
?>