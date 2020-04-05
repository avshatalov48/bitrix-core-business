<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
extract($_REQUEST, EXTR_SKIP);
IncludeTemplateLangFile($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/main/auth/auth_form.php");
$cur_page = $GLOBALS["APPLICATION"]->GetCurPage();
$str = "";
if(defined("AUTH_404"))
{
	$page = SITE_DIR."auth.php";
	$str = "<input type='hidden' name='backurl' value='".$GLOBALS["APPLICATION"]->GetCurPage()."'>";
}
else 
	$page = $cur_page;

ShowMessage($arAuthResult);
?>
<form method="POST" action="<?echo htmlspecialcharsbx($page).(($s=DeleteParam(array("register"))) == ""? "?register=yes":"?$s&register=yes")?>" name="bform">
<?=$str?>
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="REGISTRATION">
<table border="0" cellspacing="0" cellpadding="1" class="tableborder">
	<tr> 
		<td> 
			<table border="0" cellspacing="0" cellpadding="4" class="tablebody">
				<tr> 
					<td width="100%" valign="middle" colspan="2" class="tablebody"> 
						<table width="100%%" border="0" cellpadding="3" cellspacing="0">
							<tr> 
								<td class="tablehead"><font class="tableheadtext"><b><?=GetMessage("AUTH_REGISTER")?></b></font></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap width="1%" class="tablebody"><font class="tablebodytext"><?=GetMessage("AUTH_NAME")?></font></td>
					<td align="left" width="99%" class="tablebody"><input type="text" name="USER_NAME" size="30" maxlength="50" value="<?echo htmlspecialcharsbx($USER_NAME)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="tablebodytext"><?=GetMessage("AUTH_LAST_NAME")?></font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LAST_NAME" maxlength="50" size="30" value="<?echo htmlspecialcharsbx($USER_LAST_NAME)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("AUTH_LOGIN_MIN")?></font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LOGIN" size="30" maxlength="50" value="<?echo htmlspecialcharsbx($USER_LOGIN)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("AUTH_PASSWORD_MIN")?></font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialcharsbx($USER_PASSWORD)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tablebodytext"><?=GetMessage("AUTH_CONFIRM")?></font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_CONFIRM_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialcharsbx($USER_CONFIRM_PASSWORD)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tablebodytext">E-Mail:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_EMAIL" size="30" maxlength="255" value="<?echo htmlspecialcharsbx(strlen($sf_EMAIL)>0? $sf_EMAIL:$USER_EMAIL)?>" class="inputtext"></td>
				</tr>

				<?
			$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
			if (is_array($arUserFields) && count($arUserFields) > 0)
			{
				foreach ($arUserFields as $FIELD_NAME => $arUserField)
				{
					if ($arUserField["MANDATORY"] != "Y")
						continue;
					$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsbx(strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"]);
				?><tr valign="top"> 
					<td align="right" nowrap class="tablebody"><?if ($arUserField["MANDATORY"]=="Y"):?><span class="required">*</span><?endif;?><font  class="tablebodytext"><?=$arUserField["EDIT_FORM_LABEL"]?>:</font></td>
					<td align="left" class="tablebody"><?$APPLICATION->IncludeComponent(
				"bitrix:system.field.edit", 
				$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
				array("bVarsFromForm" => (empty($arAuthResult) ? false : true) , "arUserField" => $arUserField, "form_name" => "bform"));?></td>
				</tr><?
				}
			}
				/* CAPTCHA */
				if (COption::GetOptionString("main", "captcha_registration", "N") == "Y")
				{
					?>
					<tr>
						<td width="100%" valign="middle" colspan="2" class="tablebody">
							<table width="100%%" border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td class="tablehead"><font class="tableheadtext"><b><?=GetMessage("CAPTCHA_REGF_TITLE")?></b></font></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr valign="middle"> 
						<td align="right" nowrap width="1%" class="tablebody">&nbsp;</td>
						<td align="left" width="99%" class="tablebody">
							<?
							$capCode = $GLOBALS["APPLICATION"]->CaptchaGetCode();
							?>
							<input type="hidden" name="captcha_sid" value="<?= htmlspecialcharsbx($capCode) ?>">
							<img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialcharsbx($capCode) ?>" width="180" height="40">
						</td>
					</tr>
					<tr valign="middle"> 
						<td align="right" nowrap width="1%" class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("CAPTCHA_REGF_PROMT")?>:</font></td>
						<td align="left" width="99%" class="tablebody"><input type="text" name="captcha_word" size="30" maxlength="50" value="" class="inputtext"></td>
					</tr>
					<?
				}
				/* CAPTCHA */
				?>

				<tr> 
					<td nowrap align="right" class="tablebody"><font  class="tablebodytext">&nbsp;</font></td>
					<td nowrap class="tablebody" align="right"><input type="Submit" name="Register" value="<?=GetMessage("AUTH_REGISTER")?>" class="inputbodybutton"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<p><font class="starrequired">*</font><font class="text"><?=GetMessage("AUTH_REQ")?></font></p>

<p><font class="text">
<a href="<?=$cur_page.($s=="" ? "?login=yes" : "?$s&login=yes")?>"><b><?=GetMessage("AUTH_AUTH")?></b></a>
</font></p> 

</form>

<script>
<!--
document.bform.USER_NAME.focus();
// -->
</script>