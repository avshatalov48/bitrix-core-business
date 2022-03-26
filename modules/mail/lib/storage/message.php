<?php

namespace Bitrix\Mail\Storage;

use Bitrix\Mail\IMessageStorage;
use Bitrix\Mail\MailMessageTable;

/**
 * @todo implement connection injection via constructor
 */
class Message implements IMessageStorage
{
	public function getMessage(int $id): \Bitrix\Mail\Item\Message
	{
		$messageData = MailMessageTable::getById($id)->fetch();
		return \Bitrix\Mail\Item\Message::fromArray($messageData);
	}
}