<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\EO_Message_Collection;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
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
		$messagesToView = $this->filterMessageToInsert($messages);

		$insertFields = $this->prepareInsertFields($messagesToView);
		MessageViewedTable::multiplyInsertWithoutDuplicate($insertFields, ['DEADLOCK_SAFE' => true]);
		$messagesToView->setViewedByOthers()->save(true);
	}

	public function addTo(Message $message): Result
	{
		$lowerBound = $this->getLastViewedMessageId($message->getChatId());
		$includeBound = false;
		if ($lowerBound === null)
		{
			$lowerBound = $message->getChat()->getStartId($this->getContext()->getUserId());
			$includeBound = true;
		}

		$messages = $this->getLastMessageIdsBetween($message, $lowerBound, $includeBound);
		$messagesToViewByOthers = new EO_Message_Collection();
		$dateViewed = new DateTime();
		$userId = $this->getContext()->getUserId();
		$chatId = $message->getChatId();
		$insertFields = [];

		foreach ($messages as $messageEntity)
		{
			$insertFields[] = [
				'USER_ID' => $userId,
				'CHAT_ID' => $chatId,
				'MESSAGE_ID' => $messageEntity->getId(),
				'DATE_CREATE' => $dateViewed,
			];
			if (!$messageEntity->getNotifyRead())
			{
				$messageEntity->setNotifyRead(true);
				$messagesToViewByOthers->add($messageEntity);
			}
		}

		MessageViewedTable::multiplyInsertWithoutDuplicate($insertFields, ['DEADLOCK_SAFE' => true]);
		if ($messagesToViewByOthers->count() !== 0)
		{
			$messagesToViewByOthers->save(true);
		}

		return (new Result())->setResult(['VIEWED_MESSAGES' => $messages->getIdList()]);
	}

	public function getLastViewedMessageId(int $chatId): ?int
	{
		$result = MessageViewedTable::query()
			->setSelect(['LAST_VIEWED' => new ExpressionField('LAST_VIEWED', 'MAX(%s)', ['MESSAGE_ID'])])
			->where('CHAT_ID', $chatId)
			->where('USER_ID', $this->getContext()->getUserId())
			->fetch()
		;

		return ($result && isset($result['LAST_VIEWED'])) ? (int)$result['LAST_VIEWED'] : null;
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
		$isMessageRead = MessageTable::query()
			->setSelect(['ID', 'NOTIFY_READ'])
			->where('ID', $messageId)
			->fetchObject()
			?->getNotifyRead()
		;

		return $isMessageRead ? \IM_MESSAGE_STATUS_DELIVERED : \IM_MESSAGE_STATUS_RECEIVED;
	}

	public function getMessageViewersIds(int $messageId, ?int $limit = null, ?int $offset = null): array
	{
		$query = MessageViewedTable::query()
			->setSelect(['USER_ID'])
			->where('MESSAGE_ID', $messageId)
			->setOrder(['ID' => 'ASC'])
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

	public function deleteStartingFrom(Message $message): void
	{
		return;
		$userId = $this->getContext()->getUserId();
		MessageViewedTable::deleteByFilter(['>=MESSAGE_ID' => $message->getMessageId(), '=CHAT_ID' => $message->getChatId(), '=USER_ID' => $userId]);
	}

	public function deleteByMessageIdForAll(int $messageId): void
	{
		MessageViewedTable::deleteByFilter(['=MESSAGE_ID' => $messageId]); //todo add index
	}

	public function deleteByChatId(int $chatId): void
	{
		MessageViewedTable::deleteByFilter(['=CHAT_ID' => $chatId, '=USER_ID' => $this->getContext()->getUserId()]);
	}

	private function prepareInsertFields(MessageCollection $messages): array
	{
		$insertFields = [];
		$userId = $this->getContext()->getUserId();
		$dateCreate = new DateTime();

		foreach ($messages as $message)
		{
			$insertFields[] = [
				'USER_ID' => $userId,
				'CHAT_ID' => $message->getChatId(),
				'MESSAGE_ID' => $message->getMessageId(),
				'DATE_CREATE' => $dateCreate,
			];
		}

		return $insertFields;
	}

	private function getLastMessageIdsBetween(Message $message, int $lowerBound, bool $includeBound): EO_Message_Collection
	{
		$operator = $includeBound ? '>=' : '>';

		$query = MessageTable::query()
			->setSelect(['ID', 'NOTIFY_READ'])
			->where('CHAT_ID', $message->getChatId())
			->where('ID', '<=', $message->getMessageId())
			->where('ID', $operator, $lowerBound)
			->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
			->setLimit(100)
		;
		if ($message->getChat()->getType() !== \IM_MESSAGE_SYSTEM)
		{
			$query->whereNot('AUTHOR_ID', $this->getContext()->getUserId());
		}

		return $query->fetchCollection();
	}

	private function filterMessageToInsert(MessageCollection $messages): MessageCollection
	{
		$userId = $this->getContext()->getUserId();

		return $messages->filter(
			fn (Message $message) => $message->getAuthorId() !== $userId || $message->getChat()->getType() === \IM_MESSAGE_SYSTEM
		);
	}
}