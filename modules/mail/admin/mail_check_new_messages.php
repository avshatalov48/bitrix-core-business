<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
Bitrix\Main\Loader::includeModule('mail');
ClearVars("mb_");
$err_mess = "File: ".__FILE__."<br>Line: ";

@set_time_limit(1800);

$APPLICATION->SetTitle(GetMessage("MAIL_CHECK_TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form action="mail_check_new_messages.php" method="get">
<table border="0" cellspacing="1">

	<tr>
		<td valign="top" align="left" nowrap>
		<p>
			<?echo GetMessage("MAIL_CHECK_CHECK")?>
			<select name="mailbox_id">
				<option value=""><?echo GetMessage("MAIL_CHECK_CHECK_ALL")?></option>
				<?
				$l = CMailbox::GetList(array('NAME' => 'ASC', 'ID' => 'ASC'), array('ACTIVE' => 'Y', 'USER_ID' => 0));
				while($l->ExtractFields("mb_")):
					?><option value="<?echo $mb_ID?>"<?if($mailbox_id==$mb_ID)echo " selected"?>><?echo $mb_NAME?></option><?
				endwhile;
				?>
			</select>
			<input type="hidden" name="lang" value="<?echo LANG?>">
			<input type="submit" name="make_action" value="<?echo GetMessage("MAIL_CHECK_CHECK_OK")?>">
			<?echo bitrix_sessid_post();?>
		</p>
	</tr>

</table></form>
<?
if(check_bitrix_sessid())
{
	$arFilter = array('ACTIVE' => 'Y', 'USER_ID' => 0);
	if($mailbox_id>0)
		$arFilter["ID"] = $mailbox_id;

	$dbr = CMailBox::GetList(array(), $arFilter);
	ClearVars("f_");
	while($res = $dbr->ExtractFields("f_"))
	{
		CMailError::ResetErrors();
		$mb = new CMailbox();

		echo '<p><b>'.GetMessage("MAIL_CHECK_TEXT").'&quot;'.$f_NAME.'&quot;:</b></p>';

		$newMessages = false;

		if (in_array($res['SERVER_TYPE'], array('imap', 'controller', 'domain', 'crdomain')))
		{
			$newMessages = \Bitrix\Mail\Helper::syncMailbox($res['ID'], $error);
		}
		else if (in_array($res['SERVER_TYPE'], array('pop3')))
		{
			if ($mb->connect($res['ID']))
			{
				$newMessages = $mb->new_mess_count;
			}
			else
			{
				$error = \CMailError::getErrorsText();
			}
		}

		$aContext = array();

		if ($newMessages !== false && empty($error))
		{
			\CAdminMessage::showNote(sprintf(
				'%s %u %s',
				getMessage('MAIL_CHECK_CNT'),
				$newMessages,
				getMessage('MAIL_CHECK_CNT_NEW')
			));

			if ($newMessages > 0)
			{
				$aContext[] = array(
					'ICON'  => 'btn_list',
					'TEXT'  => getMessage('MAIL_CHECK_VIEW'),
					'LINK'  => 'mail_message_admin.php?find_mailbox_id='.$f_ID.'&lang='.LANG.'&find_new=Y&set_filter=Y',
					'TITLE' => getMessage('MAIL_CHECK_VIEW')
				);
			}
		}
		else
		{
			\CAdminMessage::showMessage(sprintf(
				'%s %s',
				getMessage('MAIL_CHECK_ERR'),
				$error
			));

			$aContext = array(
				array(
					'TEXT'  => getMessage('MAIL_CHECK_MBOX_PARAMS'),
					'LINK'  => 'mail_mailbox_edit.php?ID='.$f_ID.'&lang='.LANG,
					'TITLE' => getMessage('MAIL_CHECK_MBOX_PARAMS')
				),
			);
		}

		if (in_array($res['SERVER_TYPE'], array('pop3')))
		{
			$aContext[] = array(
				'TEXT'  => getMessage('MAIL_CHECK_LOG'),
				'LINK'  => 'mail_log.php?set_filter=Y&find_mailbox_id='.$f_ID.'&lang='.LANG,
				'TITLE' => getMessage('MAIL_CHECK_LOG')
			);
		}

		$context = new CAdminContextMenu($aContext);
		$context->Show();
	}
}
?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>