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
<form method="POST" action="<?echo $page.(($s=DeleteParam(array("change_password"))) == ""? "?change_password=yes":"?$s&change_password=yes")?>" name="bform">
<?=$str?>
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="CHANGE_PWD">
<table border="0" cellspacing="0" cellpadding="1" class="tableborder">
	<tr> 
		<td> 
			<table border="0" cellspacing="0" cellpadding="4" class="tablebody">
				<tr> 
					<td width="100%" colspan="2" class="tablebody"> 
						<table width="100%" border="0" cellpadding="3" cellspacing="0">
							<tr> 
								<td class="tablehead"><font class="tableheadtext"><b><?=GetMessage("AUTH_CHANGE_PASSWORD")?></b></font></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap width="1%" class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("AUTH_LOGIN")?></font></td>
					<td align="left" width="99%" class="tablebody"><input type="text" name="USER_LOGIN" size="30" maxlength="50" value="<?echo ($USER_LOGIN <> '') ? htmlspecialcharsbx($USER_LOGIN) : htmlspecialcharsbx($last_login)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap width="1%" class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("AUTH_CHECKWORD")?></font></td>
					<td align="left" nowrap width="99%" class="tablebody"><input type="text" name="USER_CHECKWORD" size="30" maxlength="50" value="<?echo htmlspecialcharsbx($USER_CHECKWORD)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap width="1%" class="tablebody"><font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("AUTH_NEW_PASSWORD")?></font></td>
					<td align="left" nowrap width="99%" class="tablebody"><input type="password" name="USER_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialcharsbx($USER_PASSWORD)?>" class="inputtext"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap width="1%" class="tablebody"><font class="starrequired">*</font><font  class="tablebodytext"><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?></font></td>
					<td align="left" nowrap width="99%"  class="tablebody"><input type="password" name="USER_CONFIRM_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialcharsbx($USER_CONFIRM_PASSWORD)?>" class="inputtext"></td>
				</tr>
				<tr> 
					<td nowrap align="right" class="tablebody"><font  class="tablebodytext">&nbsp;</font></td>
					<td nowrap class="tablebody" align="right"><input type="submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" class="inputbodybutton"></td>
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
document.bform.USER_LOGIN.focus();
// -->
</script>