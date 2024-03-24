<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

if ($arResult['ALLOW_ANONYMOUS'] == 'Y' && $_REQUEST['authorize'] <> 'YES' && $_REQUEST['register'] <> 'YES'):?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
	<thead><tr><td colspan="2"><?php echo GetMessage('subscr_title_auth2')?></td></tr></thead>
	<tr valign="top">
		<td width="40%">
			<p><?php echo GetMessage('adm_auth1')?> <a href="<?php echo $arResult['FORM_ACTION']?>?authorize=YES&amp;sf_EMAIL=<?php echo $arResult['REQUEST']['EMAIL']?><?php echo $arResult['REQUEST']['RUBRICS_PARAM']?>"><?php echo GetMessage('adm_auth2')?></a>.</p>
			<?php if ($arResult['ALLOW_REGISTER'] == 'Y'):?>
				<p><?php echo GetMessage('adm_reg1')?> <a href="<?php echo $arResult['FORM_ACTION']?>?register=YES&amp;sf_EMAIL=<?php echo $arResult['REQUEST']['EMAIL']?><?php echo $arResult['REQUEST']['RUBRICS_PARAM']?>"><?php echo GetMessage('adm_reg2')?></a>.</p>
			<?php endif;?>
		</td>
		<td width="60%"><?php echo GetMessage('adm_reg_text')?></td>
	</tr>
	</table>
	<br />
<?php elseif ($arResult['ALLOW_ANONYMOUS'] == 'N' || $_REQUEST['authorize'] == 'YES' || $_REQUEST['register'] == 'YES'):?>
	<form action="<?=$arResult['FORM_ACTION']?>" method="post">
	<?php echo bitrix_sessid_post();?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
	<thead><tr><td colspan="2"><?php echo GetMessage('adm_auth_exist')?></td></tr></thead>
	<tr valign="top">
		<td width="40%">
			<p><?php echo GetMessage('adm_auth_login')?><span class="starrequired">*</span><br />
			<input type="text" name="LOGIN" value="<?php echo $arResult['REQUEST']['LOGIN']?>" size="20" /></p>
			<p><?php echo GetMessage('adm_auth_pass')?><span class="starrequired">*</span><br />
			<input type="password" name="PASSWORD" size="20" value="<?php echo $arResult['REQUEST']['PASSWORD']?>" /></p>
		</td>
		<td width="60%">
			<?php if ($arResult['ALLOW_ANONYMOUS'] == 'Y'):?>
				<?php echo GetMessage('subscr_auth_note')?>
			<?php else:?>
				<?php echo GetMessage('adm_must_auth')?>
			<?php endif;?>
		</td>
	</tr>
	<tfoot><tr><td colspan="2"><input type="submit" name="Save" value="<?php echo GetMessage('adm_auth_butt')?>" /></td></tr></tfoot>
	</table>
	<?php foreach ($arResult['RUBRICS'] as $itemValue):?>
		<input type="hidden" name="RUB_ID[]" value="<?=$itemValue['ID']?>">
	<?php endforeach;?>
	<input type="hidden" name="PostAction" value="<?php echo ($arResult['ID'] > 0 ? 'Update' : 'Add')?>" />
	<input type="hidden" name="ID" value="<?php echo $arResult['SUBSCRIPTION']['ID'];?>" />
	<?php if ($_REQUEST['register'] == 'YES'):?>
		<input type="hidden" name="register" value="YES" />
	<?php endif;?>
	<?php if ($_REQUEST['authorize'] == 'YES'):?>
		<input type="hidden" name="authorize" value="YES" />
	<?php endif;?>
	</form>
	<br />
	<?php if ($arResult['ALLOW_REGISTER'] == 'Y'):
		?>
		<form action="<?=$arResult['FORM_ACTION']?>" method="post">
		<?php echo bitrix_sessid_post();?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
		<thead><tr><td colspan="2"><?php echo GetMessage('adm_reg_new')?></td></tr></thead>
		<tr valign="top">
			<td width="40%">
			<p><?php echo GetMessage('adm_reg_login')?><span class="starrequired">*</span><br />
			<input type="text" name="NEW_LOGIN" value="<?php echo $arResult['REQUEST']['NEW_LOGIN']?>" size="20" /></p>
			<p><?php echo GetMessage('adm_reg_pass')?><span class="starrequired">*</span><br />
			<input type="password" name="NEW_PASSWORD" size="20" value="<?php echo $arResult['REQUEST']['NEW_PASSWORD']?>" /></p>
			<p><?php echo GetMessage('adm_reg_pass_conf')?><span class="starrequired">*</span><br />
			<input type="password" name="CONFIRM_PASSWORD" size="20" value="<?php echo $arResult['REQUEST']['CONFIRM_PASSWORD']?>" /></p>
			<p><?php echo GetMessage('subscr_email')?><span class="starrequired">*</span><br />
			<input type="text" name="EMAIL" value="<?=$arResult['SUBSCRIPTION']['EMAIL'] != '' ? $arResult['SUBSCRIPTION']['EMAIL'] : $arResult['REQUEST']['EMAIL'];?>" size="30" maxlength="255" /></p>
		<?php
			/* CAPTCHA */
			if (COption::GetOptionString('main', 'captcha_registration', 'N') == 'Y'):
				$capCode = $GLOBALS['APPLICATION']->CaptchaGetCode();
			?>
				<p><?=GetMessage('subscr_CAPTCHA_REGF_TITLE')?><br />
				<input type="hidden" name="captcha_sid" value="<?= htmlspecialcharsbx($capCode) ?>" />
				<img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialcharsbx($capCode) ?>" width="180" height="40" alt="CAPTCHA" /></p>
				<p><?=GetMessage('subscr_CAPTCHA_REGF_PROMT')?><span class="starrequired">*</span><br />
				<input type="text" name="captcha_word" size="30" maxlength="50" value="" /></p>
			<?php endif;?>
			</td>
			<td width="60%">
				<?php if ($arResult['ALLOW_ANONYMOUS'] == 'Y'):?>
					<?php echo GetMessage('subscr_auth_note')?>
				<?php else:?>
					<?php echo GetMessage('adm_must_auth')?>
				<?php endif;?>
			</td>
		</tr>
		<tfoot><tr><td colspan="2"><input type="submit" name="Save" value="<?php echo GetMessage('adm_reg_butt')?>" /></td></tr></tfoot>
		</table>
		<?php foreach ($arResult['RUBRICS'] as $itemValue):?>
			<input type="hidden" name="RUB_ID[]" value="<?=$itemValue['ID']?>">
		<?php endforeach;?>
		<input type="hidden" name="PostAction" value="<?php echo ($arResult['ID'] > 0 ? 'Update' : 'Add')?>" />
		<input type="hidden" name="ID" value="<?php echo $arResult['SUBSCRIPTION']['ID'];?>" />
		<?php if ($_REQUEST['register'] == 'YES'):?>
			<input type="hidden" name="register" value="YES" />
		<?php endif;?>
		<?php if ($_REQUEST['authorize'] == 'YES'):?>
			<input type="hidden" name="authorize" value="YES" />
		<?php endif;?>
		</form>
		<br />
	<?php endif;?>
<?php endif; //$arResult["ALLOW_ANONYMOUS"]=="Y" && $authorize<>"YES"
