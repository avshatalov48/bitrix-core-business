<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;

class Reaction
{
	use ContextCustomer;

	public const LIKE = 'LIKE';

	private const ALLOWED_REACTION = [self::LIKE];

	protected string $reaction;

	public function __construct(string $reaction)
	{
		if (!in_array($reaction, self::ALLOWED_REACTION, true))
		{
			throw new ArgumentException('This reaction not found', '$reaction');
		}

		$this->reaction = $reaction;
	}

	public function addToMessage(Message $message): void
	{
		$dateCreate = new DateTime();

		$insertFields = [
			'USER_ID' => $this->getContext()->getUserId(),
			'CHAT_ID' => $message->getChatId(),
			'MESSAGE_ID' => $message->getMessageId(),
			'DATE_CREATE' => $dateCreate,
			'REACTION' => $this->reaction,
		];

		$updateFields = [
			'REACTION' => $this->reaction
		];

		MessageViewedTable::merge($insertFields, $updateFields);
		//todo send push
	}

	public function deleteFromMessage(Message $message): void
	{
		$readObject = MessageViewedTable::query()
			->where('USER_ID', $this->getContext()->getUserId())
			->where('MESSAGE_ID', $message->getMessageId())
			->fetchObject()
		;

		if ($readObject === null)
		{
			return;
		}

		$readObject->setReaction(null);
		//todo send push
	}
}