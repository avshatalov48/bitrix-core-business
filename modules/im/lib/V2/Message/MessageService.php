<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;

class MessageService
{
	use ContextCustomer;

	protected bool $isConvertText;

	public function __construct(bool $isConvertText = true)
	{
		$this->isConvertText = $isConvertText;
	}

	public function getMessageContext(Message $message, int $range, array $select = []): Result
	{
		$result = new Result();

		$messageId = $message->getMessageId() ?? 0;
		$chat = $message->getChat();

		$startId = $chat->getStartId();

		$idsBefore = [];
		$idsAfter = [];
		if ($range > 0)
		{
			$idsBefore = MessageTable::query()
				->setSelect(['ID'])
				->where('ID', '<', $messageId)
				->where('ID', '>=', $startId)
				->where('CHAT_ID', $chat->getChatId())
				->setOrder(['ID' => 'DESC'])
				->setLimit($range)
				->fetchCollection()
				->getIdList()
			;
			$idsAfter = MessageTable::query()
				->setSelect(['ID'])
				->where('ID', '>', $messageId)
				->where('CHAT_ID', $chat->getChatId())
				->setOrder(['ID' => 'ASC'])
				->setLimit($range)
				->fetchCollection()
				->getIdList()
			;
		}
		$targetMessage = $messageId < $startId ? [] : [$messageId];

		$ids = array_merge($idsBefore, $targetMessage, $idsAfter);

		if (empty($select))
		{
			return $result->setResult(new MessageCollection($ids));
		}

		$ormCollection = MessageTable::query()->whereIn('ID', $ids)->setSelect($select)->fetchCollection();

		return $result->setResult(new MessageCollection($ormCollection));
	}
}