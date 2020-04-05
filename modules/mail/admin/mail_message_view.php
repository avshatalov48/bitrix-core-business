<?php
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 - 2004 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/include.php");
$err_mess = "File: ".__FILE__."<br>Line: ";

ClearVars("str_");

$ID = IntVal($ID);
if($_SERVER["REQUEST_METHOD"]=="POST" && $_REQUEST["save_form"]=="Y" && $MOD_RIGHT>="W" && check_bitrix_sessid())
{
	$dbr = CMailMessage::GetByID($ID);
	if($dbr_arr = $dbr->Fetch())
	{
		if($_REQUEST["MARK_AS_SPAM"]=="Y")
		{
			CMailMessage::MarkAsSpam($ID, true, $dbr_arr);
		}
		elseif($_REQUEST["MARK_AS_NOT_SPAM"]=="Y")
		{
			CMailMessage::MarkAsSpam($ID, false, $dbr_arr);
		}
		elseif(($_REQUEST["MARK_SPAM"]=="Y" || $_REQUEST["MARK_SPAM"]=="N"))
		{
			CMailMessage::MarkAsSpam($ID, $_REQUEST["MARK_SPAM"]=="Y", $dbr_arr);
		}

		if(strlen($_REQUEST["MANUAL_FILTER"])>0)
		{
			CMailFilter::FilterMessage($ID, "M", ($_REQUEST["MANUAL_FILTER"]>0?$_REQUEST["MANUAL_FILTER"]:false));
		}

		if($_REQUEST["DELETE_MESSAGE"]=="Y")
		{
			CMailMessage::Delete($ID);
			$_REQUEST["save"] == "Y";
		}
	}

	if(strlen($_REQUEST["apply"])>0)
		LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID."#tb");
	elseif(strlen($_REQUEST["next"])>0 || strlen($_REQUEST["prev"])>0)
	{
		$md5Path = md5("/bitrix/admin/mail_message_admin.php");
		$FILTER = $_SESSION["SESS_ADMIN"][$md5Path];
		$arFilter = Array(
			"ID"=>$FILTER["find_id"],
			"MAILBOX_ID"=>$FILTER["find_mailbox_id"],
			"SENDER"=>$FILTER["find_from"],
			"RECIPIENT"=>$FILTER["find_to"],
			"SUBJECT"=>$FILTER["find_subject"],
			"BODY"=>$FILTER["find_body"],
			"ALL"=>$FILTER["find_all"],
			"NEW_MESSAGE"=>$FILTER["find_new"],
			"SPAM"=>$FILTER["find_spam"]
			);

		if(strlen($_REQUEST["next"])>0)
			$arFilter[">ID"] = $ID;
		else
			$arFilter["<ID"] = $ID;

		$mailmessages = CMailMessage::GetList(Array("ID"=>(strlen($_REQUEST["next"])>0?"asc":"desc")), $arFilter);
		if($arr = $mailmessages->Fetch())
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$arr["ID"]."#tb");
	}

	LocalRedirect("/bitrix/admin/mail_message_admin.php?lang=".LANG);
}


if($ID<=0 && $_REQUEST["MSG_ID"]!='')
	$dbr = CMailMessage::GetList(array(), array("MSG_ID"=>$_REQUEST["MSG_ID"]));
else
	$dbr = CMailMessage::GetByID($ID);

if($dbr_arr = $dbr->ExtractFields("str_")):


	$dbr_arr["SPAM_RATING"] = CMailMessage::GetSpamRating($ID, $dbr_arr);
	if($dbr_arr["NEW_MESSAGE"]=="Y")
		CMailMessage::Update($ID, Array("NEW_MESSAGE"=>"N"));

	if($_REQUEST['show']=='original' && COption::GetOptionString("mail", "save_src", B_MAIL_SAVE_SRC)=="Y")
	{
		echo "<pre>".nl2br(htmlspecialcharsbx($dbr_arr["FULL_TEXT"]))."</pre>";
		die();
	}

	$APPLICATION->SetTitle(GetMessage("MAIL_MSG_VIEW_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

	$aMenu = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("MAIL_MSG_VIEW_BACK_LINK"),
			"LINK"=>"mail_message_admin.php?lang=".LANG
		)
	);

	$context = new CAdminContextMenu($aMenu);
	$context->Show();



	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("MAIL_MSG_MESSAGE"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("MAIL_MSG_VIEW_TITLE")),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);


?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<a name="tb"></a>

<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>

	<tr>
		<td width="40%"><?echo GetMessage("MAIL_MSG_VIEW_DATE")?></td>
		<td width="60%"><?=$str_FIELD_DATE?></td>
	</tr>
	<?if(strlen($dbr_arr["FIELD_FROM"])>0):?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_FROM")?></td>
		<td><?=TxtToHTML($dbr_arr["FIELD_FROM"])?></td>
	</tr>
	<?endif?>
	<?if(strlen($dbr_arr["FIELD_TO"])>0):?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_TO")?></td>
		<td><?=TxtToHTML($dbr_arr["FIELD_TO"])?></td>
	</tr>
	<?endif?>
	<?if(strlen($dbr_arr["FIELD_CC"])>0):?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_CC")?></td>
		<td><?=TxtToHTML($dbr_arr["FIELD_CC"])?></td>
	</tr>
	<?endif?>
	<?if(strlen($dbr_arr["FIELD_BCC"])>0):?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_BCC")?></td>
		<td><?=TxtToHTML($dbr_arr["FIELD_BCC"])?></td>
	</tr>
	<?endif?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_SUBJECT")?></td>
		<td>
		<script>
		function hideshowhdr()
		{
			var d = BX('msghdr');
			if(d.style.display=='none')
				d.style.display = '';
			else
				d.style.display = 'none';
		}

		</script>
			<table width="100%"><tr>
			<td width="80%"><?=TxtToHTML($dbr_arr["SUBJECT"])?></td>
			<td width="20%" nowrap><a href="javascript:void(0)" onclick="hideshowhdr()" title="<?echo GetMessage("MMV_SHOW_HEADER_TITLE")?>"><?echo GetMessage("MMV_SHOW_HEADER")?></a><?if(COption::GetOptionString("mail", "save_src", B_MAIL_SAVE_SRC)=="Y" && $dbr_arr["FULL_TEXT"]!=''):?> | <a href="/bitrix/admin/mail_message_view.php?lang=<?=LANGUAGE_ID?>&amp;ID=<?=intval($ID)?>&amp;show=original" target="_blank" title="<?echo GetMessage("MMV_SHOW_ORIG_TITLE")?>"><?echo GetMessage("MMV_SHOW_ORIG")?></a><?endif?></td>
			</tr></table>
		</td>
	</tr>
	<tr style="display:none" id="msghdr">
		<td><?=GetMessage("MMV_SHOW_HEADER")?>:</td>
		<td><?=nl2br(htmlspecialcharsbx($dbr_arr["HEADER"]))?>
		</td>
	</tr>
	<?
	function _ConvReplies($str1, $str2)
	{
		$str2 = str_replace('\"', '"', $str2);
		if(substr_count($str1, "&gt;")%2 == 1)
			$clr = "#770000";
		else
			$clr = "#CC9933";
		return '<font color="'.$clr.'">'.$str1.$str2.'';
	}
	?>
	<tr>
		<td colspan="2" style="background:white; padding: 15px;"><?=preg_replace_callback("'(^|\r\n)[\s]*([A-Za-z]*(&gt;)+)([^\r\n]+)'", create_function('$m', "return _ConvReplies(\$m[2], \$m[4]);"), TxtToHTML($dbr_arr["BODY"]))?></td>
	</tr>
	<?
	if($dbr_arr["ATTACHMENTS"]>0):

		$dbr_attach = CMailAttachment::GetList(Array("NAME"=>"ASC", "ID"=>"ASC"), Array("MESSAGE_ID"=>$dbr_arr["ID"]));
	?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_ATTACHMENTS")?></td>
		<td>
		<?while($dbr_attach_arr = $dbr_attach->GetNext()):?>
			<a target="_blank" href="mail_attachment_view.php?lang=<?=LANG?>&amp;ID=<?=$dbr_attach_arr["ID"]?>"><?=(strlen($dbr_attach_arr["FILE_NAME"])>0?$dbr_attach_arr["FILE_NAME"]:GetMessage("MAIL_MSG_VIEW_NNM"))?></a> (<?
				echo CFile::FormatSize($dbr_attach_arr["FILE_SIZE"]);
			?>)<br>
		<?endwhile?>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_STATUS")?></td>
		<td>

			<?if($dbr_arr["SPAM"]=="Y"):?>
				<?if($dbr_arr["NEW_MESSAGE"]!="Y"):?>
				<div class="mail-message-spam" title="<?echo GetMessage("MAIL_MSG_VIEW_READ_SPAM")?>"></div> <?echo GetMessage("MAIL_MSG_VIEW_READ_SPAM")?>
				<?else:?>
				<div class="mail-message-unread-spam" title="<?echo GetMessage("MAIL_MSG_VIEW_NOTREAD_SPAM")?>"></div> <?echo GetMessage("MAIL_MSG_VIEW_NOTREAD_SPAM")?>
				<?endif?>
			<?elseif($dbr_arr["SPAM"]=="N"):?>
				<?if($dbr_arr["NEW_MESSAGE"]!="Y"):?>
				<div class="mail-message-notspam" title="<?echo GetMessage("MAIL_MSG_VIEW_READ_NOTSPAM")?>"></div>
				<?else:?>
				<div class="mail-message-unread-notspam" title="<?echo GetMessage("MAIL_MSG_VIEW_NOTREAD_NOTSPAM")?>"></div>
				<?endif?>
			<?else:?>
				<?if($dbr_arr["NEW_MESSAGE"]!="Y"):?>
				<div class="mail-message" title="<?echo GetMessage("MAIL_MSG_VIEW_READ")?>"></div> <?echo GetMessage("MAIL_MSG_VIEW_READ_NOSTATUS")?>
				<?else:?>
				<div class="mail-message-unread" title="<?echo GetMessage("MAIL_MSG_VIEW_NOTREAD_STATUS")?>"></div> <?echo GetMessage("MAIL_MSG_VIEW_NOTREAD_NOSTATUS")?>
				<?endif?>
			<?endif?>
			<span title="<?=htmlspecialcharsbx(preg_replace('/(.?) ([.0-9]+) ([0-9]+) ([0-9]+)/', '\\1 = \\2% (\\3 bad, \\4 good)', $dbr_arr["SPAM_WORDS"]))?>">
			(<?echo GetMessage("MAIL_MSG_VIEW_SPAM_PROB")?> <?=$dbr_arr["SPAM_RATING"]?>%)
			</span>
		</td>
	</tr>
	<?if($dbr_arr["SPAM"]=="Y"):?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_MARK_AS_NOTSPAM")?></td>
		<td><input type="checkbox" name="MARK_AS_NOT_SPAM" value="Y"></td>
	</tr>
	<?elseif($dbr_arr["SPAM"]=="N"):?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_MARK_AS_SPAM")?></td>
		<td><input type="checkbox" name="MARK_AS_SPAM" value="Y"></td>
	</tr>
	<?else:?>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_MSG_VIEW_SPAM_LEARN")?></td>
		<td>
			<input type="radio" name="MARK_SPAM" value="?" id="MARK_SPAM_1" checked><label for="MARK_SPAM_1"><?echo GetMessage("MAIL_MSG_VIEW_SPAM_NOTLEARN")?></label><br>
			<input type="radio" name="MARK_SPAM" value="Y" id="MARK_SPAM_2"><label for="MARK_SPAM_2"><?echo GetMessage("MAIL_MSG_VIEW_ACT_MARK_AS_SPAM")?></label><br>
			<input type="radio" name="MARK_SPAM" value="N" id="MARK_SPAM_3"><label for="MARK_SPAM_3"><?echo GetMessage("MAIL_MSG_VIEW_ACT_MARK_AS_NOTSPAM")?></label><br>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_ACT_RULE")?></td>
		<td>
		<select name="MANUAL_FILTER">
		<option value=""><?echo GetMessage("MAIL_MSG_VIEW_ACT_RULE_NOT")?></option>
		<option value="all"><?echo GetMessage("MAIL_MSG_VIEW_ACT_RULE_ALL")?></option>
		<?
		$res = CMailFilter::GetList(Array("NAME"=>"ASC"), Array("ACTIVE"=>"Y", "WHEN_MANUALLY_RUN"=>"Y", "MAILBOX_ID"=>$dbr_arr["MAILBOX_ID"]));
		while($flt_arr = $res->Fetch()):
		?><option value="<?=htmlspecialcharsbx($flt_arr['ID']) ?>"><?=htmlspecialcharsbx($flt_arr["NAME"])?> [<?=htmlspecialcharsbx($flt_arr["ID"])?>]</option><?
		endwhile?>
		</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIL_MSG_VIEW_ACT_RULE_DELETE")?></td>
		<td><input type="checkbox" name="DELETE_MESSAGE" value="Y"></td>
	</tr>

	<tr class="heading"><td colspan="2"><?=GetMessage("MAIL_MSG_VIEW_LOG")?></td></tr>

	<tr>
		<td colspan="2" align="center">

<?$ml_res = CMailLog::GetList(Array("ID"=>"ASC"), Array("MESSAGE_ID"=>$ID));?>
<select style="width:80%;" size="5">
<?while($arr_log = $ml_res->GetNext()):?>
<option>(<?=$arr_log["DATE_INSERT"]?>) <?=$arr_log["MESSAGE_TEXT"]?></option>
<?endwhile;?>
</select>

		</td>
	</tr>


<?$tabControl->Buttons();?>

<input type="hidden" name="save_form" value="Y">
<input <?if ($MOD_RIGHT<"W") echo "disabled" ?> type="submit" name="save"  class="adm-btn-save" value="<?echo GetMessage("MAIL_MSG_VIEW_SAVE")?>">
&nbsp;<input <?if ($MOD_RIGHT<"W") echo "disabled" ?> type="submit" name="apply" value="<?echo GetMessage("MAIL_MSG_VIEW_APPLY")?>">
&nbsp;<input <?if ($MOD_RIGHT<"W") echo "disabled" ?> type="submit" name="prev" value="&lt;&lt;" title="<?echo GetMessage("MAIL_MSG_VIEW_SAVE_PREV")?>">
&nbsp;<input <?if ($MOD_RIGHT<"W") echo "disabled" ?> type="submit" name="next" value="&gt;&gt;" title="<?echo GetMessage("MAIL_MSG_VIEW_SAVE_NEXT")?>">
<?$tabControl->End();?>
</form>
<?
else:
	$APPLICATION->SetTitle(GetMessage("MAIL_MSG_VIEW_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
	CAdminMessage::ShowMessage(GetMessage("MAIL_MSG_NOTFOUND"));
endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
