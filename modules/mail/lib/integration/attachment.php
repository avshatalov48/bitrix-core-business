<?php

namespace Bitrix\Mail\Integration;

class Attachment
{
	public static function downloadAttachmentsByMessageId(int $messageId): bool
	{
		$messageForDownload = \Bitrix\Mail\MailMessageTable::getList([
			'runtime' => [
				new \Bitrix\Main\Entity\ReferenceField(
					'MESSAGE_UID',
					'Bitrix\Mail\MailMessageUidTable',
					[
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID' => 'ref.MESSAGE_ID',
					],
					[
						'join_type' => 'INNER',
					]
				),
			],
			'select' => [
				'*',
				'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
				'MAILBOX_NAME' => 'MAILBOX.NAME',
				'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
				'IS_SEEN' => 'MESSAGE_UID.IS_SEEN',
				'MSG_HASH' => 'MESSAGE_UID.HEADER_MD5',
				'DIR_MD5' => 'MESSAGE_UID.DIR_MD5',
				'MSG_UID' => 'MESSAGE_UID.MSG_UID',
			],
			'filter' => [
				'=ID' => $messageId,
			],
			'order' => [
				'FIELD_DATE' => 'DESC',
				'MESSAGE_UID.ID' => 'DESC',
				'MESSAGE_UID.MSG_UID' => 'ASC',
			],
			'limit' => 1,
		])->fetch();

		if ($messageForDownload)
		{
			return (bool)\Bitrix\Mail\Helper\Message::ensureAttachments($messageForDownload);
		}

		return false;
	}
}