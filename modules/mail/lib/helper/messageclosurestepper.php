<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Mail;

class MessageClosureStepper extends Main\Update\Stepper
{
	protected static $moduleId = 'mail';

	public function execute(array &$option)
	{
		global $DB, $pPERIOD;

		$pPERIOD = 10;

		$option['count'] = Mail\MailMessageTable::getCount();
		$option['steps'] = Mail\Internals\MessageClosureTable::getList(array(
			'select' => array(
				new Main\Entity\ExpressionField('CNT', 'COUNT(DISTINCT %s)', 'MESSAGE_ID')
			),
		))->fetch()['CNT'];

		if ($option['steps'] < $option['count'])
		{
			if (!($option['mailboxId'] > 0) || $option['stage'] < 1)
			{
				$option['mailboxId'] = $DB->query(
					'SELECT MAILBOX_ID FROM b_mail_message M WHERE NOT EXISTS (
						SELECT 1 FROM b_mail_message_closure WHERE MESSAGE_ID = M.ID
					) LIMIT 1'
				)->fetch()['MAILBOX_ID'];

				$option['stage'] = 1;
			}
		}
		else
		{
			$option['mailboxId'] = false;

			$option['stage'] = 3;
		}

		if ($option['mailboxId'] > 0 && 1 == $option['stage'])
		{
			$res = $DB->query(sprintf(
				'INSERT IGNORE INTO b_mail_message_closure (MESSAGE_ID, PARENT_ID)
				(
					SELECT M.ID, M.ID FROM b_mail_message M
					WHERE M.MAILBOX_ID = %u AND (
						M.IN_REPLY_TO IS NULL OR M.IN_REPLY_TO = "" OR M.IN_REPLY_TO = M.MSG_ID OR NOT EXISTS (
							SELECT 1 FROM b_mail_message WHERE MAILBOX_ID = M.MAILBOX_ID AND MSG_ID = M.IN_REPLY_TO
						)
					) AND NOT EXISTS (SELECT 1 FROM b_mail_message_closure WHERE MESSAGE_ID = M.ID)
					LIMIT 40000
				)',
				$option['mailboxId']
			))->affectedRowsCount();

			$option['stage'] = $res < 40000 ? 2 : 1;
			$option['steps'] += $res;

			return self::CONTINUE_EXECUTION;
		}

		if ($option['mailboxId'] > 0 && 2 == $option['stage'])
		{
			$res = $DB->query(sprintf(
				'INSERT IGNORE INTO b_mail_message_closure (MESSAGE_ID, PARENT_ID)
				(
					SELECT DISTINCT M.ID, C.PARENT_ID
					FROM b_mail_message M
						LEFT JOIN b_mail_message R ON M.MAILBOX_ID = R.MAILBOX_ID AND M.IN_REPLY_TO = R.MSG_ID
						LEFT JOIN b_mail_message_closure C ON R.ID = C.MESSAGE_ID
					WHERE M.MAILBOX_ID = %u
						AND EXISTS (SELECT 1 FROM b_mail_message_closure WHERE MESSAGE_ID = R.ID)
						AND NOT EXISTS (SELECT 1 FROM b_mail_message_closure WHERE MESSAGE_ID = M.ID)
				)',
				$option['mailboxId']
			))->affectedRowsCount();

			$option['stage'] = $res > 0 ? 3 : 4;

			return self::CONTINUE_EXECUTION;
		}

		if (3 == $option['stage'])
		{
			$res = $DB->query(
				'INSERT IGNORE INTO b_mail_message_closure (MESSAGE_ID, PARENT_ID)
				(
					SELECT DISTINCT C.MESSAGE_ID, C.MESSAGE_ID
					FROM b_mail_message_closure C
					WHERE NOT EXISTS (SELECT 1 FROM b_mail_message_closure WHERE PARENT_ID = C.MESSAGE_ID)
				)'
			)->affectedRowsCount();

			$option['stage'] = $res > 0 ? 2 : 4;
		}

		if (4 == $option['stage'])
		{
			$res = $DB->query(sprintf(
				'INSERT IGNORE INTO b_mail_message_closure (MESSAGE_ID, PARENT_ID)
				(
					SELECT M.ID, M.ID FROM b_mail_message M
					WHERE M.MAILBOX_ID = %u
						AND NOT EXISTS (SELECT 1 FROM b_mail_message_closure WHERE MESSAGE_ID = M.ID)
					ORDER BY FIELD_DATE ASC LIMIT 1
				)',
				$option['mailboxId']
			))->affectedRowsCount();

			$option['stage'] = $res > 0 ? 2 : 0;
		}

		return $option['mailboxId'] > 0 ? self::CONTINUE_EXECUTION : self::FINISH_EXECUTION;
	}

}
