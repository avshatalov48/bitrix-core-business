<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;

class MessageService
{
	use ContextCustomer;

	private Message $message;

	public function __construct(Message $message)
	{
		$this->message = $message;
	}

	public function getMessageContext(int $range, array $select = []): Result
	{
		$result = new Result();

		$messageId = $this->message->getMessageId() ?? 0;
		$chat = $this->message->getChat();

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
				->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
				->setLimit($range)
				->fetchCollection()
				->getIdList()
			;
			$idsAfter = MessageTable::query()
				->setSelect(['ID'])
				->where('ID', '>', $messageId)
				->where('CHAT_ID', $chat->getChatId())
				->setOrder(['DATE_CREATE' => 'ASC', 'ID' => 'ASC'])
				->setLimit($range)
				->fetchCollection()
				->getIdList()
			;
		}
		$targetMessage = $messageId < $startId ? [] : [$messageId];

		$ids = array_merge($idsBefore, $targetMessage, $idsAfter);

		if (empty($ids))
		{
			return $result->setResult(new MessageCollection());
		}

		if (empty($select))
		{
			return $result->setResult(new MessageCollection($ids));
		}

		$ormCollection = MessageTable::query()->whereIn('ID', $ids)->setSelect($select)->fetchCollection();

		return $result->setResult(new MessageCollection($ormCollection));
	}

	public static function deleteByChatId(int $chatId, int $userId): Result
	{
		MessageTable::deleteBatch(['=CHAT_ID' => $chatId]);
		$readService = new ReadService($userId);
		$readService->deleteByChatId($chatId);

		return new Result();
	}

	public function fillContextPaginationData(array $rest, MessageCollection $messages, int $range): array
	{
		$rest['hasPrevPage'] = $this->getCountHigherMessages($messages, $this->message->getId() ?? 0) >= $range;
		$lastSelectedId = $this->getLastSelectedId($messages);
		$lastMessageIdInChat = $this->message->getChat()->getLastMessageId();
		$rest['hasNextPage'] = $lastSelectedId > 0 && $lastMessageIdInChat > 0 && $lastSelectedId < $lastMessageIdInChat;

		return $rest;
	}

	private function getCountHigherMessages(MessageCollection $messages, int $id): int
	{
		$count = 0;

		foreach ($messages as $message)
		{
			if ($message->getId() < $id)
			{
				++$count;
			}
		}

		return $count;
	}

	private function getLastSelectedId(MessageCollection $messages): int
	{
		return max($messages->getIds() ?: [0]);
	}
}