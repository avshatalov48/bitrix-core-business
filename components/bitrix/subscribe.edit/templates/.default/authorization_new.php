<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arResult["ALLOW_ANONYMOUS"]=="Y" && $_REQUEST["authorize"]<>"YES" && $_REQUEST["register"]<>"YES"):?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
	<thead><tr><td colspan="2"><?echo GetMessage("subscr_title_auth2")?></td></tr></thead>
	<tr valign="top">
		<td width="40%">
			<p><?echo GetMessage("adm_auth1")?> <a href="<?echo $arResult["FORM_ACTION"]?>?authorize=YES&amp;sf_EMAIL=<?echo $arResult["REQUEST"]["EMAIL"]?><?echo $arResult["REQUEST"]["RUBRICS_PARAM"]?>"><?echo GetMessage("adm_auth2")?></a>.</p>
			<?if($arResult["ALLOW_REGISTER"]=="Y"):?>
				<p><?echo GetMessage("adm_reg1")?> <a href="<?echo $arResult["FORM_ACTION"]?>?register=YES&amp;sf_EMAIL=<?echo $arResult["REQUEST"]["EMAIL"]?><?echo $arResult["REQUEST"]["RUBRICS_PARAM"]?>"><?echo GetMessage("adm_reg2")?></a>.</p>
			<?endif;?>
		</td>
		<td width="60%"><?echo GetMessage("adm_reg_text")?></td>
	</tr>
	</table>
	<br />
<?elseif($arResult["ALLOW_ANONYMOUS"]=="N" || $_REQUEST["authorize"]=="YES" || $_REQUEST["register"]=="YES"):?>
	<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
	<?echo bitrix_sessid_post();?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
	<thead><tr><td colspan="2"><?echo GetMessage("adm_auth_exist")?></td></tr></thead>
	<tr valign="top">
		<td width="40%">
			<p><?echo GetMessage("adm_auth_login")?><span class="starrequired">*</span><br />
			<input type="text" name="LOGIN" value="<?echo $arResult["REQUEST"]["LOGIN"]?>" size="20" /></p>
			<p><?echo GetMessage("adm_auth_pass")?><span class="starrequired">*</span><br />
			<input type="password" name="PASSWORD" size="20" value="<?echo $arResult["REQUEST"]["PASSWORD"]?>" /></p>
		</td>
		<td width="60%">
			<?if($arResult["ALLOW_ANONYMOUS"]=="Y"):?>
				<?echo GetMessage("subscr_auth_note")?>
			<?else:?>
				<?echo GetMessage("adm_must_auth")?>
			<?endif;?>
		</td>
	</tr>
	<tfoot><tr><td colspan="2"><input type="submit" name="Save" value="<?echo GetMessage("adm_auth_butt")?>" /></td></tr></tfoot>
	</table>
	<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
		<input type="hidden" name="RUB_ID[]" value="<?=$itemValue["ID"]?>">
	<?endforeach;?>
	<input type="hidden" name="PostAction" value="<?echo ($arResult["ID"]>0? "Update":"Add")?>" />
	<input type="hidden" name="ID" value="<?echo $arResult["SUBSCRIPTION"]["ID"];?>" />
	<?if($_REQUEST["register"] == "YES"):?>
		<input type="hidden" name="register" value="YES" />
	<?endif;?>
	<?if($_REQUEST["authorize"]=="YES"):?>
		<input type="hidden" name="authorize" value="YES" />
	<?endif;?>
	</form>
	<br />
	<?if($arResult["ALLOW_REGISTER"]=="Y"):
		?>
		<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
		<?echo bitrix_sessid_post();?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
		<thead><tr><td colspan="2"><?echo GetMessage("adm_reg_new")?></td></tr></thead>
		<tr valign="top">
			<td width="40%">
			<p><?echo GetMessage("adm_reg_login")?><span class="starrequired">*</span><br />
			<input type="text" name="NEW_LOGIN" value="<?echo $arResult["REQUEST"]["NEW_LOGIN"]?>" size="20" /></p>
			<p><?echo GetMessage("adm_reg_pass")?><span class="starrequired">*</span><br />
			<input type="password" name="NEW_PASSWORD" size="20" value="<?echo $arResult["REQUEST"]["NEW_PASSWORD"]?>" /></p>
			<p><?echo GetMessage("adm_reg_pass_conf")?><span class="starrequired">*</span><br />
			<input type="password" name="CONFIRM_PASSWORD" size="20" value="<?echo $arResult["REQUEST"]["CONFIRM_PASSWORD"]?>" /></p>
			<p><?echo GetMessage("subscr_email")?><span class="starrequired">*</span><br />
			<input type="text" name="EMAIL" value="<?=$arResult["SUBSCRIPTION"]["EMAIL"]!=""?$arResult["SUBSCRIPTION"]["EMAIL"]:$arResult["REQUEST"]["EMAIL"];?>" size="30" maxlength="255" /></p>
		<?
			/* CAPTCHA */
			if (COption::GetOptionString("main", "captcha_registration", "N") == "Y"):
				$capCode = $GLOBALS["APPLICATION"]->CaptchaGetCode();
			?>
				<p><?=GetMessage("subscr_CAPTCHA_REGF_TITLE")?><br />
				<input type="hidden" name="captcha_sid" value="<?= htmlspecialcharsbx($capCode) ?>" />
				<img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialcharsbx($capCode) ?>" width="180" height="40" alt="CAPTCHA" /></p>
				<p><?=GetMessage("subscr_CAPTCHA_REGF_PROMT")?><span class="starrequired">*</span><br />
				<input type="text" name="captcha_word" size="30" maxlength="50" value="" /></p>
			<?endif;?>
			</td>
			<td width="60%">
				<?if($arResult["ALLOW_ANONYMOUS"]=="Y"):?>
					<?echo GetMessage("subscr_auth_note")?>
				<?else:?>
					<?echo GetMessage("adm_must_auth")?>
				<?endif;?>
			</td>
		</tr>
		<tfoot><tr><td colspan="2"><input type="submit" name="Save" value="<?echo GetMessage("adm_reg_butt")?>" /></td></tr></tfoot>
		</table>
		<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
			<input type="hidden" name="RUB_ID[]" value="<?=$itemValue["ID"]?>">
		<?endforeach;?>
		<input type="hidden" name="PostAction" value="<?echo ($arResult["ID"]>0? "Update":"Add")?>" />
		<input type="hidden" name="ID" value="<?echo $arResult["SUBSCRIPTION"]["ID"];?>" />
		<?if($_REQUEST["register"] == "YES"):?>
			<input type="hidden" name="register" value="YES" />
		<?endif;?>
		<?if($_REQUEST["authorize"]=="YES"):?>
			<input type="hidden" name="authorize" value="YES" />
		<?endif;?>
		</form>
		<br />
	<?endif;?>
<?endif; //$arResult["ALLOW_ANONYMOUS"]=="Y" && $authorize<>"YES"?>
