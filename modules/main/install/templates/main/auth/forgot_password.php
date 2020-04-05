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
<form name="bform" method="post" target="_top" action="<?=$page.(($s=DeleteParam(array("forgot_password"))) == ""? "?forgot_password=yes":"?$s&forgot_password=yes")?>">
<?=$str?>
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="SEND_PWD">
<p><font class="text">
<?=GetMessage("AUTH_FORGOT_PASSWORD_1")?>
</font></p>

<table border="0" cellspacing="0" cellpadding="1" class="tableborder">
	<tr>
		<td width="100%">
			<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tablebody">
				<tr> 
					<td width="100%" valign="middle" colspan="2" class="tablebody"> 
						<table width="100%" border="0" cellpadding="3" cellspacing="0">
							<tr> 
								<td class="tablehead"><font class="tableheadtext"><b><?=GetMessage("AUTH_GET_CHECK_STRING")?></b></font></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="right" class="tablebody"><font class="tablebodytext"><?=GetMessage("AUTH_LOGIN")?></font></td>
					<td valign="middle" align="left" class="tablebody" nowrap><nobr>
						<input type="text" name="USER_LOGIN" maxlength="50" size="20" value="<?echo htmlspecialcharsbx($last_login)?>" class="inputtext">&nbsp;<font class="tablebodytext"><?=GetMessage("AUTH_OR")?>&nbsp;</nobr></font>
					</td>
				</tr>
				<tr> 
					<td align="right" class="tablebody" nowrap><font class="tablebodytext">E-Mail:</font></td>
					<td valign="middle" align="left" class="tablebody">
						<input type="text" name="USER_EMAIL" maxlength="255" size="20" class="inputtext">
					</td>
				</tr>
				<tr> 
					<td valign="middle" align="center" class="tablebody">&nbsp;</td>
					<td valign="middle" class="tablebody" align="right">
						<input type="submit" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>" class="inputbodybutton">
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<p><font class="text">
<a href="<?=$cur_page.($s=="" ? "?login=yes" : "?$s&login=yes")?>"><b><?=GetMessage("AUTH_AUTH")?></b></a>
</font></p> 
</form>
<script>
<!--
document.bform.USER_LOGIN.focus();
// -->
</script>