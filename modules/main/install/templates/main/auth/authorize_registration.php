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
<form name="form_auth" method="post" target="_top" action="<?echo $page.(($s=DeleteParam(array("logout", "login"))) == ""? "?login=yes":"?$s&login=yes");?>">
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="AUTH">
	<?=$str?>
	<?
	foreach($GLOBALS["HTTP_POST_VARS"] as $vname=>$vvalue):
		if($vname=="USER_LOGIN")continue;
		?><input type="hidden" name="<?echo htmlspecialcharsbx($vname)?>" value="<?echo htmlspecialcharsbx($vvalue)?>"><?
	endforeach;
	?>
<p><font class="text"><?=GetMessage("AUTH_PLEASE_AUTH")?></font></p>
<table border="0" cellspacing="0" cellpadding="1" class="tableborder">
	<tr valign="top" align="center">
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tablebody">
				<tr valign="middle"> 
					<td class="tablebody" colspan="2">
						<table width="100%%" border="0" cellpadding="3" cellspacing="0">
							<tr> 
								<td class="tablehead" align="center"><font class="tableheadtext"><b><?=GetMessage("AUTH_AUTH")?></b></font></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="middle"> 
					<td align="right" class="tablebody"><font class="tablebodytext"><?=GetMessage("AUTH_LOGIN")?></font></td>
					<td align="left"  class="tablebody"><input type="text" name="USER_LOGIN" maxlength="50" size="20" value="<?echo htmlspecialcharsbx($last_login)?>" class="inputtext"></td>
				</tr>
				<tr> 
					<td align="right" class="tablebody"><font class="tablebodytext"><?=GetMessage("AUTH_PASSWORD")?></font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_PASSWORD" maxlength="50" class="inputtext" size="20"></td>
				</tr>
				<?if (COption::GetOptionString("main", "store_password", "Y")=="Y") :?>
				<tr> 
					<td align="center" class="tablebody" colspan="2"><font class="tablebodytext"><input type="checkbox" name="USER_REMEMBER" value="Y" class="inputcheckbutton">&nbsp;<?=GetMessage("AUTH_REMEMBER_ME")?></font></td>
				</tr>
				<?endif;?>
				<tr> 
					<td class="tablebody" align="center" colspan="2"><font class="tablebodytext"><input type="submit" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>" class="inputbodybutton"></font></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<p>
<font class="text">
<a href="<?echo $cur_page."?forgot_password=yes".($s<>""? "&amp;$s":"");?>"><b><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></b></a>
<br><?=GetMessage("AUTH_GO")?> <a href="<?echo $cur_page."?forgot_password=yes".($s<>""? "&amp;$s":"");?>"><?=GetMessage("AUTH_GO_AUTH_FORM")?></a>
<br><?=GetMessage("AUTH_MESS_1")?> <a href="<?echo $cur_page."?change_password=yes".($s<>""? "&amp;$s":"");?>"><?=GetMessage("AUTH_CHANGE_FORM")?></a>
</font>
</p>
</form>
<script>
<!--
<? if ($last_login <> '') : ?>
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
<? else : ?>
try{document.form_auth.USER_LOGIN.focus();}catch(e){}
<? endif; ?>
// -->
</script>
<?if(COption::GetOptionString("main", "new_user_registration", "Y")=="Y"):?>
<br>
<p><font class="text"><?=GetMessage("AUTH_FIRST_ONE")?><?=GetMessage("AUTH_REG_FORM")?>:</font></p>
<form method="POST" action="<?echo $page.(($s=DeleteParam(array("authorize_registration"))) == ""? "?authorize_registration=yes":"?$s&authorize_registration=yes")?>" name="bform">
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
					<td align="left" width="99%" class="tablebody"><input type="text" name="USER_NAME" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialcharsbx($USER_NAME) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="tablebodytext"><?=GetMessage("AUTH_LAST_NAME")?></font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LAST_NAME" maxlength="50" size="30" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialcharsbx($USER_LAST_NAME) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("AUTH_LOGIN_MIN")?></font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LOGIN" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialcharsbx($USER_LOGIN) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("AUTH_PASSWORD_MIN")?></font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_PASSWORD" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialcharsbx($USER_PASSWORD) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tablebodytext"><?=GetMessage("AUTH_CONFIRM")?></font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_CONFIRM_PASSWORD" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialcharsbx($USER_CONFIRM_PASSWORD) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tablebodytext">E-Mail:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_EMAIL" size="30" maxlength="255" value="<?echo htmlspecialcharsbx(($sf_EMAIL <> '' && $TYPE=="REGISTRATION")? $sf_EMAIL:$USER_EMAIL)?>"></td>
				</tr>
				<tr> 
					<td nowrap align="right" class="tablebody"><font  class="tablebodytext">&nbsp;</font></td>
					<td nowrap class="tablebody" align="right"><input type="Submit" name="Register" value="<?=GetMessage("AUTH_REGISTER")?>"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<p><font class="starrequired">*</font><font class="text"><?=GetMessage("AUTH_REQ")?></font></p>
</form>
<?endif;?>