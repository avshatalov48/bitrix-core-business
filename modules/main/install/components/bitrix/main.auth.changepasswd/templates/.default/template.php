<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

\Bitrix\Main\Page\Asset::getInstance()->addCss(
	'/bitrix/css/main/system.auth/flat/style.css'
);

if ($arResult['AUTHORIZED'])
{
	echo Loc::getMessage('MAIN_AUTH_CHD_SUCCESS');
	return;
}

$fields = $arResult['FIELDS'];
?>

<div class="bx-authform">

	<?if ($arResult['ERRORS']):?>
	<div class="alert alert-danger">
		<? foreach ($arResult['ERRORS'] as $error)
		{
			echo $error;
		}
		?>
	</div>
	<?elseif ($arResult['SUCCESS']):?>
	<div class="alert alert-success">
		<?= $arResult['SUCCESS'];?>
	</div>
	<?endif;?>

	<h3 class="bx-title"><?= Loc::getMessage('MAIN_AUTH_CHD_HEADER');?></h3>

	<form name="bform" method="post" target="_top" action="<?= POST_FORM_ACTION_URI;?>">

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_LOGIN');?></div>
			<div class="bx-authform-input-container">
				<input type="text" name="<?= $fields['login'];?>" maxlength="255" value="<?= \htmlspecialcharsbx($arResult['LAST_LOGIN']);?>" />
			</div>
		</div>

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_CHECKWORD');?></div>
			<div class="bx-authform-input-container">
				<input type="text" name="<?= $fields['checkword'];?>" maxlength="255" value="<?= \htmlspecialcharsbx($arResult[$fields['checkword']]);?>" />
			</div>
		</div>

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_PASS');?></div>
			<div class="bx-authform-input-container">
				<?if ($arResult['SECURE_AUTH']):?>
					<div class="bx-authform-psw-protected" id="bx_auth_secure" style="display:none">
						<div class="bx-authform-psw-protected-desc"><span></span>
							<?= Loc::getMessage('MAIN_AUTH_CHD_SECURE_NOTE');?>
						</div>
					</div>
					<script type="text/javascript">
						document.getElementById('bx_auth_secure').style.display = '';
					</script>
				<?endif;?>
				<input type="password" name="<?= $fields['password'];?>" value="<?= \htmlspecialcharsbx($arResult[$fields['password']]);?>" maxlength="255" autocomplete="off" />
			</div>
		</div>

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_PASS2');?></div>
			<div class="bx-authform-input-container">
				<?if ($arResult['SECURE_AUTH']):?>
					<div class="bx-authform-psw-protected" id="bx_auth_secure2" style="display:none">
						<div class="bx-authform-psw-protected-desc"><span></span>
							<?= Loc::getMessage('MAIN_AUTH_CHD_SECURE_NOTE');?>
						</div>
					</div>
					<script type="text/javascript">
						document.getElementById('bx_auth_secure2').style.display = '';
					</script>
				<?endif;?>
				<input type="password" name="<?= $fields['confirm_password'];?>" value="<?= \htmlspecialcharsbx($arResult[$fields['confirm_password']]);?>" maxlength="255" autocomplete="off" />
			</div>
		</div>

		<?if ($arResult['CAPTCHA_CODE']):?>
			<input type="hidden" name="captcha_sid" value="<?= \htmlspecialcharsbx($arResult['CAPTCHA_CODE']);?>" />
			<div class="bx-authform-formgroup-container dbg_captha">
				<div class="bx-authform-label-container">
					<?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_CAPTCHA');?>
				</div>
				<div class="bx-captcha"><img src="/bitrix/tools/captcha.php?captcha_sid=<?= \htmlspecialcharsbx($arResult['CAPTCHA_CODE']);?>" width="180" height="40" alt="CAPTCHA" /></div>
				<div class="bx-authform-input-container">
					<input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off" />
				</div>
			</div>
		<?endif;?>

		<div class="bx-authform-formgroup-container">
			<input type="submit" class="btn btn-primary" name="<?= $fields['action'];?>" value="<?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_SUBMIT');?>" />
		</div>

		<div class="bx-authform-description-container">
			<?= $arResult['GROUP_POLICY']['PASSWORD_REQUIREMENTS'];?>
		</div>

		<?if ($arResult['AUTH_AUTH_URL'] || $arResult['AUTH_REGISTER_URL']):?>
			<hr class="bxe-light">
			<noindex>
			<?if ($arResult['AUTH_AUTH_URL']):?>
				<div class="bx-authform-link-container">
					<a href="<?= $arResult['AUTH_AUTH_URL'];?>" rel="nofollow">
						<?= Loc::getMessage('MAIN_AUTH_CHD_URL_AUTH_URL');?>
					</a>
				</div>
			<?endif;?>
			<?if ($arResult['AUTH_REGISTER_URL']):?>
				<div class="bx-authform-link-container">
					<a href="<?= $arResult['AUTH_REGISTER_URL'];?>" rel="nofollow">
						<?= Loc::getMessage('MAIN_AUTH_CHD_URL_REGISTER_URL');?>
					</a>
				</div>
			<?endif;?>
			</noindex>
		<?endif;?>

	</form>
</div>

<script type="text/javascript">
	document.bform.<?= $fields['login'];?>.focus();
</script>
