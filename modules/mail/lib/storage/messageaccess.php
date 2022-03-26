<?php

namespace Bitrix\Mail\Storage;

use Bitrix\Mail\IMessageAccessStorage;
use Bitrix\Mail\Internals\MessageAccessTable;

/**
 * @todo implement connection injection via constructor
 */
class MessageAccess implements IMessageAccessStorage
{

	public function getCollectionForMessage(\Bitrix\Mail\Item\Message $message): \Bitrix\Mail\Collection\MessageAccess
	{
		return $this->getCollectionByMessageId($message->getId(), $message->getMailboxId());
	}

	public function getCollectionByMessageId(int $messageId, int $mailboxId): \Bitrix\Mail\Collection\MessageAccess
	{
		$rows = MessageAccessTable::getList(array(
			// 'select' => array('ENTITY_TYPE', 'ENTITY_ID'),
			'filter' => array(
				'=MESSAGE_ID' => $messageId,
				'=MAILBOX_ID' => $mailboxId,
			),
		))->fetchAll();

		return \Bitrix\Mail\Collection\MessageAccess::fromArray($rows);
	}
}