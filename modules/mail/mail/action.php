<?
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/lang/", "/mail/action.php"));

if(CModule::IncludeModule("support")):?>
<tr valign="top">
	<td align="right"><font class="tablefieldtext"><?echo GetMessage("SUPPORT_MAIL_DEF_REGISTERED")?></font></td>
	<td valign="top"><font class="tablebodytext">
		<input type="radio" name="W_SUPPORT_USER_FIND" value="Y"<?if($W_SUPPORT_USER_FIND!="N")echo " checked"?> id="W_SUPPORT_USER_FIND_1"><label for="W_SUPPORT_USER_FIND_1"><?echo GetMessage("SUPPORT_MAIL_DEF_REGISTERED_Y")?></label><br>
		<input type="radio" name="W_SUPPORT_USER_FIND" value="N"<?if($W_SUPPORT_USER_FIND=="N")echo " checked"?> id="W_SUPPORT_USER_FIND_2"><label for="W_SUPPORT_USER_FIND_2"><?echo GetMessage("SUPPORT_MAIL_DEF_REGISTERED_N")?></label><br>
	</font></td>
</tr>

<tr valign="top">
	<td align="right" valign="top"><font class="tablefieldtext"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_TICKET")?></font></td>
	<td valign="top" nowrap><font class="tablebodytext">
		<input type="radio" name="W_SUPPORT_SEC" value="email"<?if($W_SUPPORT_SEC!="all" && $W_SUPPORT_SEC!="domain")echo " checked"?> id="w_support_sec_1"><label for="w_support_sec_1"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_T_EMAIL")?></label><br>
		<input type="radio" name="W_SUPPORT_SEC" value="domain"<?if($W_SUPPORT_SEC=="domain")echo " checked"?> id="w_support_sec_2"><label for="w_support_sec_2"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_T_DOMAIN")?></label><br>
		<input type="radio" name="W_SUPPORT_SEC" value="all"<?if($W_SUPPORT_SEC=="all")echo " checked"?> id="w_support_sec_3"><label for="w_support_sec_3"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_T_ANY")?></label><br>
	</font></td>
</tr>

<tr valign="top">
	<td align="right"><font class="tablefieldtext"><?echo GetMessage("SUPPORT_MAIL_SUBJECT_TEMPLATE")?><br>
	<?echo GetMessage("SUPPORT_MAIL_SUBJECT_TEMPLATE_NOTES")?></font></td>
	<td valign="top"><font class="tablebodytext">
	<?
	if(!isset($W_SUPPORT_SUBJECT))
	{
		$w_subject = "";
		$db_res = CEventMessage::GetList($o, $b, Array("EVENT_NAME"=>"TICKET_NEW || TICKET_CHANGE", "LID"=>$MAILBOX_LID));
		while($ar_res = $db_res->Fetch())
		{
			$ar_res["SUBJECT"] = preg_quote($ar_res["SUBJECT"], "/");
			$ar_res["SUBJECT"] = str_replace("#ID#", "([0-9]+)", $ar_res["SUBJECT"]);
			$ar_res["SUBJECT"] = preg_replace("/#[-A-Z_0-9]+#/i", ".*?", $ar_res["SUBJECT"]);
			$w_subject .= $ar_res["SUBJECT"]."\r\n";
		}
		$W_SUPPORT_SUBJECT = $w_subject;
	}
	?>
	<textarea name="W_SUPPORT_SUBJECT" cols="45" rows="3" class="typearea" wrap="off"><?=htmlspecialchars($W_SUPPORT_SUBJECT)?></textarea></font></td>
</tr>

<tr valign="top">
	<td align="right"><font class="tablefieldtext"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_CATEORY")?></font></td>
	<td valign="top"><font class="tablebodytext">
	<?=SelectBox("W_SUPPORT_CATEGORY", CTicket::GetRefBookValues("C", $MAILBOX_LID), "(".GetMessage("SUPPORT_MAIL_ADD_TO_CATEGORY_N").")", $W_SUPPORT_CATEGORY);?>
	</font></td>
</tr>

<?endif?>