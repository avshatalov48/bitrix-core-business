<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;

class ViewedService
{
	use ContextCustomer;

	public function __construct(?int $userId = null)
	{
		if (isset($userId))
		{
			$context = new Context();
			$context->setUser($userId);
			$this->setContext($context);
		}
	}

	public function add(MessageCollection $messages): void
	{
		$insertFields = $this->prepareInsertFields($messages);
		MessageViewedTable::multiplyInsertWithoutDuplicate($insertFields);
	}

	public function addTo(Message $message): void
	{
		$lowerBound = $this->getLastViewedMessageId($message->getChatId());
		if ($lowerBound === null)
		{
			$lowerBound = $message->getChat()->getStartId($this->getContext()->getUserId());
		}
		$query = MessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $message->getChatId())
			->where('ID', '<=', $message->getMessageId())
			->where('ID', '>=', $lowerBound)
			->setLimit(100)
		;
		if ($message->getChat()->getType() !== \IM_MESSAGE_SYSTEM)
		{
			$query->whereNot('AUTHOR_ID', $this->getContext()->getUserId());
		}
		$dateViewed = new DateTime();
		$userId = $this->getContext()->getUserId();
		$chatId = $message->getChatId();
		$insertFields = [];
		$result = $query->fetchAll();
		foreach ($result as $row)
		{
			$insertFields[] = [
				'USER_ID' => $userId,
				'CHAT_ID' => $chatId,
				'MESSAGE_ID' => (int)$row['ID'],
				'DATE_CREATE' => $dateViewed,
			];
		}
		MessageViewedTable::multiplyInsertWithoutDuplicate($insertFields);
	}

	/*public function addAllFromChat(int $chatId): void
	{
		$lowerBound = $this->getLastViewedMessageId($chatId);
		if ($lowerBound === null)
		{
			$lowerBound = Chat::getInstance($chatId)->getStartId($this->getContext()->getUserId());
		}
		$query = MessageTable::query()
			->setSelect([
				'ID_CONST' => new ExpressionField('ID_CONST', '0'),
				'USER_ID_CONST' => new ExpressionField('USER_ID_CONST', (string)$this->getContext()->getUserId()),
				'CHAT_ID_CONST' => new ExpressionField('CHAT_ID', (string)$chatId),
				'MESSAGE_ID' => 'ID',
				'DATE_CREATE_CONST' => new ExpressionField('DATE_CREATE_CONST', 'CURRENT_TIMESTAMP')
			])
			->where('CHAT_ID', $chatId)
			->where('MESSAGE_ID', '>=', $lowerBound)
		;
		if (Chat::getInstance($chatId)->getType() !== \IM_MESSAGE_SYSTEM)
		{
			$query->whereNot('AUTHOR_ID', $this->getContext()->getUserId());
		}
		MessageViewedTable::insertSelect($query);
	}*/

	public function getLastViewedMessageId(int $chatId): ?int
	{
		$result = MessageViewedTable::query()
			->setSelect(['LAST_VIEWED' => new ExpressionField('LAST_VIEWED', 'MAX(%s)', ['MESSAGE_ID'])])
			->where('CHAT_ID', $chatId)
			->where('USER_ID', $this->getContext()->getUserId())
			->fetch()
		;

		return $result ? (int)$result['LAST_VIEWED'] : null;
	}

	public function getDateViewedByMessageId(int $messageId): ?DateTime
	{
		$result = MessageViewedTable::query() //todo: add unique index (MESSAGE_ID, USER_ID, DATE_CREATE)
			->setSelect(['DATE_CREATE'])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('MESSAGE_ID', $messageId)
			->fetch()
		;

		return $result ? $result['DATE_CREATE'] : null;
	}

	public function getDateViewedByMessageIdForEachUser(int $messageId, array $userIds): array
	{
		if (empty($userIds))
		{
			return [];
		}

		$result = MessageViewedTable::query() //todo: add unique index (MESSAGE_ID, USER_ID, DATE_CREATE)
			->setSelect(['DATE_CREATE', 'USER_ID'])
			->whereIn('USER_ID', $userIds)
			->where('MESSAGE_ID', $messageId)
			->fetchAll()
		;

		$dateViewedByUsers = [];

		foreach ($result as $row)
		{
			$dateViewedByUsers[(int)$row['USER_ID']] = $row['DATE_CREATE'];
		}

		return $dateViewedByUsers;
	}

	public function getMessageStatus(int $messageId): string
	{
		$isMessageRead = MessageViewedTable::query()
			->setSelect(['MESSAGE_ID'])
			->where('MESSAGE_ID', $messageId) //todo add index
			->setLimit(1)
			->fetch()
		;

		return $isMessageRead ? \IM_MESSAGE_STATUS_DELIVERED : \IM_MESSAGE_STATUS_RECEIVED;
	}

	public function getMessageViewersIds(int $messageId, ?int $limit = null, ?int $offset = null): array
	{
		$query = MessageViewedTable::query()
			->setSelect(['USER_ID'])
			->where('MESSAGE_ID', $messageId)
		;

		if (isset($limit))
		{
			$query->setLimit($limit);
		}
		if (isset($offset))
		{
			$query->setOffset($offset);
		}

		$viewedMessages = $query->fetchCollection();
		$viewersIds = [];

		foreach ($viewedMessages as $viewedMessage)
		{
			$userId = $viewedMessage->getUserId();
			$viewersIds[$userId] = $userId;
		}

		return $viewersIds;
	}

	public function getMessageStatuses(array $messageIds): array
	{
		if (empty($messageIds))
		{
			return [];
		}

		$viewedMessageResult = MessageViewedTable::query()
			->setSelect(['MESSAGE_ID'])
			->setDistinct()
			->whereIn('MESSAGE_ID', $messageIds) //todo index
			->exec()
		;

		$deliveredMessages = [];

		while ($row = $viewedMessageResult->fetch())
		{
			$deliveredMessages[(int)$row['MESSAGE_ID']] = \IM_MESSAGE_STATUS_DELIVERED;
		}

		$messageStatuses = [];

		foreach ($messageIds as $messageId)
		{
			$messageStatuses[$messageId] = $deliveredMessages[$messageId] ?? \IM_MESSAGE_STATUS_RECEIVED;
		}

		return $messageStatuses;
	}

	public function deleteStartingFrom(Message $message): void
	{
		$userId = $this->getContext()->getUserId();
		MessageViewedTable::deleteByFilter(['>=MESSAGE_ID' => $message->getMessageId(), '=CHAT_ID' => $message->getChatId(), '=USER_ID' => $userId]);
	}

	public function deleteByMessageIds(array $messageIds, int $chatId): void
	{
		if (empty($messageIds))
		{
			return;
		}

		MessageViewedTable::deleteByFilter(['=MESSAGE_ID' => $messageIds, '=CHAT_ID' => $chatId, '=USER_ID' => $this->getContext()->getUserId()]);
	}

	public function deleteByMessageIdForAll(int $messageId): void
	{
		MessageViewedTable::deleteByFilter(['=MESSAGE_ID' => $messageId]); //todo add index
	}

	public function deleteByMessageIdsForAll(array $messageIds): void
	{
		MessageViewedTable::deleteByFilter(['=MESSAGE_ID' => $messageIds]); //todo add index
	}

	public function getLastMessageIdInChat(int $chatId): ?int
	{
		$result = MessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $chatId)
			->setOrder(['DATE_CREATE' => 'DESC'])
			->setLimit(1)
			->fetch()
		;

		return $result ? (int)$result['ID'] : null;
	}

	private function prepareInsertFields(MessageCollection $messages): array
	{
		$insertFields = [];
		$userId = $this->getContext()->getUserId();
		$dateCreate = new DateTime();

		foreach ($messages as $message)
		{
			if ($message->getAuthorId() === $userId && $message->getChat()->getType() !== \IM_MESSAGE_SYSTEM)
			{
				continue;
			}
			$insertFields[] = [
				'USER_ID' => $userId,
				'CHAT_ID' => $message->getChatId(),
				'MESSAGE_ID' => $message->getMessageId(),
				'DATE_CREATE' => $dateCreate,
			];
		}

		return $insertFields;
	}
}