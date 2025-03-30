<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Sync;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;

class ReadService
{
	use ContextCustomer
	{
		setContext as private defaultSetContext;
	}

	protected CounterService $counterService;
	protected ViewedService $viewedService;

	private static array $lastMessageIdCache = [];

	public function __construct(?int $userId = null)
	{
		$this->counterService = new CounterService();
		$this->viewedService = new ViewedService();

		if (isset($userId))
		{
			$context = new Context();
			$context->setUser($userId);
			$this->setContext($context);
			$this->counterService->setContext($context);
			$this->viewedService->setContext($context);
		}
	}

	public function readTo(Message $message): Result
	{
		$this->setLastIdForRead($message->getMessageId(), $message->getChatId());
		$this->counterService->deleteTo($message);
		$counter = $this->counterService->getByChat($message->getChatId());
		$viewResult = $this->viewedService->addTo($message);
		$this->updateDateRecent($message->getChatId());
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $message->getChatId()),
			$this->getContext()->getUserId(),
			$message->getChat()->getType()
		);

		$viewedMessages = [];
		if ($viewResult->isSuccess())
		{
			$viewedMessages = $viewResult->getResult()['VIEWED_MESSAGES'] ?? [];
		}

		return (new Result())->setResult(['COUNTER' => $counter, 'VIEWED_MESSAGES' => $viewedMessages]);
	}

	public function read(MessageCollection $messages, Chat $chat): Result
	{
		$maxId = max($messages->getIds());
		$this->setLastIdForRead($maxId, $chat->getChatId());
		$this->counterService->deleteTo($messages[$maxId]);
		$counter = $this->counterService->getByChat($chat->getChatId());
		$messagesToView = $messages
			->withContextUser($this->getContext()->getUserId())
			->fillViewed()
			->filter(fn (Message $message) => !$message->isViewed())
		;
		$this->viewedService->add($messagesToView);
		$this->updateDateRecent($chat->getChatId());
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $chat->getChatId()),
			$this->getContext()->getUserId(),
			$chat->getType()
		);

		return (new Result())->setResult(['COUNTER' => $counter, 'VIEWED_MESSAGES' => $messagesToView]);
	}

	public function readNotifications(MessageCollection $messages, array $userByChatId): Result
	{
		$chatIds = [];

		foreach ($messages as $message)
		{
			$chatIds[$message->getChatId()] = 0;
		}

		$chatIds = array_keys($chatIds);

		$this->counterService->deleteByMessagesForAll($messages, $userByChatId);
		$counters = $this->counterService->getForNotifyChats($chatIds);
		$time = microtime(true);
		//$this->viewedController->add($messages);

		/*foreach ($chatIds as $chatId)
		{
			$this->sendPush($chatId, [(int)$userByChatId[$chatId]], $counters[$chatId], $time);
			Sync\Logger::getInstance()->add(
				new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $chatId),
				(int)$userByChatId[$chatId]
			);
		}*/

		return (new Result())->setResult(['COUNTERS' => $counters]);
	}

	public function readAllInChat(int $chatId): Result
	{
		$lastId = $this->getLastMessageIdInChat($chatId);
		$this->setLastIdForRead($lastId, $chatId);
		$this->counterService->deleteByChatId($chatId);
		$counter = 0;
		//$this->viewedController->addAllFromChat($chatId);
		$this->updateDateRecent($chatId);
		$chat = Chat::getInstance($chatId);
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $chatId),
			$this->getContext()->getUserId(),
			$chat->getType()
		);

		if ($chat instanceof Chat\ChannelChat)
		{
			$userId = $this->getContext()->getUserId();
			Application::getInstance()->addBackgroundJob(fn () => $this->withContextUser($userId)->readChildren($chat));
		}

		return (new Result())->setResult(['COUNTER' => $counter, 'VIEWED_MESSAGES' => new MessageCollection()]);
	}

	public function readChildren(Chat $parentChat): array
	{
		$childrenToRead = CounterService::getChildrenWithCounters($parentChat, $this->getContext()->getUserId());

		if (empty($childrenToRead))
		{
			return $childrenToRead;
		}

		$this->setLastIdForReadByIds($childrenToRead);
		$this->counterService->deleteByChatIds($childrenToRead);

		return $childrenToRead;
	}

	public function readAll(): void
	{
		$this->setLastIdForReadAll();
		$this->counterService->deleteAll();
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::READ_ALL_EVENT, Sync\Event::CHAT_ENTITY, 0),
			$this->getContext()->getUserId()
		);
	}

	public function unreadTo(Message $message): Result
	{
		//$this->setLastIdForUnread($message->getMessageId(), $message->getChatId());
		$relation = $message->getChat()->withContext($this->context)->getSelfRelation();
		if ($relation === null)
		{
			return new Result();
		}
		$this->counterService->addStartingFrom($message->getMessageId(), $relation);
		$this->viewedService->deleteStartingFrom($message);
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $message->getChatId()),
			$this->getContext()->getUserId(),
			$message->getChat()->getType()
		);

		return new Result();
	}

	public function unreadNotifications(MessageCollection $messages, Relation $relation): Result
	{
		$this->counterService->addCollection($messages, $relation);
		$counter = $this->counterService->getByChat($relation->getChatId());

		return (new Result())->setResult(['COUNTER' => $counter]);
	}

	/**
	 * Marks notification as unread.
	 *
	 * @param Message $message
	 * @param RelationCollection $relations
	 * @return self
	 */
	public function markNotificationUnread(Message $message, RelationCollection $relations): self
	{
		$this->counterService->addForEachUser($message, $relations);
		return $this;
	}

	/**
	 * Marks message as unread and reads messages up to the sent message accept author.
	 *
	 * @param Message $message
	 * @param RelationCollection $relations
	 * @return self
	 */
	public function markMessageUnread(Message $message, RelationCollection $relations): self
	{
		$this->counterService->addForEachUser($message, $relations);
		$this->counterService->deleteTo($message);
		return $this;
	}

	/**
	 * Mark chat unread in Recent.
	 *
	 * @param Message $message
	 * @return $this
	 */
	public function markRecentUnread(Message $message): self
	{
		$chat = $message->getChat()->withContext($this->context);
		Recent::unread($chat->getDialogId(), false, $this->getContext()->getUserId(), null, $chat->getType());
		return $this;
	}

	/**
	 * Send a push about counter changes.
	 *
	 * @param Message $message
	 * @param RelationCollection $relations
	 * @return array
	 */
	public function getCountersForUsers(Message $message, RelationCollection $relations): array
	{
		return $this->counterService->getByChatForEachUsers($message->getChatId(), $relations->getUserIds(), 100);
	}

	/**
	 * Returns unread counters for the rest answer.
	 *
	 * @param Message $message
	 * @param RelationCollection $relations
	 * @return Result
	 */
	public function onAfterMessageSend(Message $message, RelationCollection $relations, bool $withoutCounters = false): Result
	{
		if (!$withoutCounters)
		{
			$this->markMessageUnread($message, $relations);
		}

		$counters = $this
			->markRecentUnread($message)
			->getCountersForUsers($message, $relations)
		;

		return (new Result())->setResult(['COUNTERS' => $counters]);
	}

	public function onAfterNotificationSend(Message $message, Relation $relation): Result
	{
		$relationCollection = new RelationCollection();
		$relationCollection->add($relation);
		$this->counterService->addForEachUser($message, $relationCollection);
		$counter = $this->counterService->getByChat($relation->getChatId());

		return (new Result())->setResult(['COUNTER' => $counter]);
	}

	public function deleteByMessage(Message $message, ?array $invalidateCacheUsers = null): void
	{
		$this->counterService->deleteByMessageForAll($message, $invalidateCacheUsers);
		$this->viewedService->deleteByMessageIdForAll($message->getMessageId());
	}

	public function deleteByChatId(int $chatId): void
	{
		$this->counterService->deleteByChatId($chatId);
		$this->viewedService->deleteByChatId($chatId);
	}

	/*public function deleteByMessageIds(array $messageIds): void
	{
		$this->counterService->deleteByMessageIdsForAll($messageIds);
		$this->viewedController->deleteByMessageIdsForAll($messageIds);
	}*/

	public function getReadStatusesByMessageIds(array $messageIds): array
	{
		if (empty($messageIds))
		{
			return [];
		}

		$query = MessageUnreadTable::query()
			->setSelect(['MESSAGE_ID'])
			->whereIn('MESSAGE_ID', $messageIds)
			->where('USER_ID', $this->getContext()->getUserId())
			->exec()
		; //todo add index

		$unreadMessages = [];

		while ($row = $query->fetch())
		{
			$unreadMessages[(int)$row['MESSAGE_ID']] = false;
		}

		$readStatuses = [];

		foreach ($messageIds as $messageId)
		{
			$readStatuses[$messageId] = $unreadMessages[$messageId] ?? true;
		}

		return $readStatuses;
	}

	public function getViewStatusesByMessageIds(array $messageIds): array
	{
		if (empty($messageIds))
		{
			return [];
		}

		$query = MessageViewedTable::query()
			->setSelect(['MESSAGE_ID'])
			->whereIn('MESSAGE_ID', $messageIds)
			->where('USER_ID', $this->getContext()->getUserId())
			->exec()
		; //todo add index

		$viewedMessages = [];

		while ($row = $query->fetch())
		{
			$viewedMessages[(int)$row['MESSAGE_ID']] = true;
		}

		$viewStatuses = [];

		foreach ($messageIds as $messageId)
		{
			$viewStatuses[$messageId] = $viewedMessages[$messageId] ?? false;
		}

		return $viewStatuses;
	}

	public function getLastIdByChatId(int $chatId): int
	{
		$relation = RelationTable::query()
			->setSelect(['LAST_ID'])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('CHAT_ID', $chatId)->setLimit(1)
			->fetch();

		if ($relation)
		{
			return $relation['LAST_ID'] ?? 0;
		}

		return 0;
	}

	public function getLastMessageIdInChat(int $chatId): int
	{
		if (isset(static::$lastMessageIdCache[$chatId]))
		{
			return static::$lastMessageIdCache[$chatId];
		}

		$result = ChatTable::query()->setSelect(['LAST_MESSAGE_ID'])->where('ID', $chatId)->fetch();
		$lastMessageId = 0;

		if (!$result)
		{
			$lastMessageId = 0;
		}
		else
		{
			$lastMessageId = (int)($result['LAST_MESSAGE_ID'] ?? 0);
		}

		static::$lastMessageIdCache[$chatId] = $lastMessageId;

		return $lastMessageId;
	}

	public function getChatMessageStatus(int $chatId): string
	{
		$lastMessageId = $this->getLastMessageIdInChat($chatId);

		if ($lastMessageId === 0)
		{
			return \IM_MESSAGE_STATUS_RECEIVED;
		}

		return $this->viewedService->getMessageStatus($lastMessageId);
	}

	public function getCounterService(): CounterService
	{
		return $this->counterService;
	}

	public function getViewedService(): ViewedService
	{
		return $this->viewedService;
	}


	public function setLastIdForRead(int $lastId, int $chatId): void
	{
		$sql = "
			UPDATE b_im_relation
			SET LAST_ID=(CASE WHEN LAST_ID > {$lastId} THEN LAST_ID ELSE {$lastId} END)
			WHERE CHAT_ID={$chatId} AND USER_ID={$this->getContext()->getUserId()}
		";

		Application::getConnection()->queryExecute($sql);
	}

	public function setContext(?Context $context): self
	{
		$this->defaultSetContext($context);
		$this->getCounterService()->setContext($context);
		$this->getViewedService()->setContext($context);

		return $this;
	}

	private function getChildrenToReadIds(Chat $parentChat): array
	{
		$parentId = $parentChat->getId();
		if (!$parentId)
		{
			return [];
		}

		$query = ChatTable::query()
			->setSelect(['ID'])
			->where('PARENT_ID', $parentId)
		;
		$alias = Application::getConnection()->getSqlHelper()->quote($query->getInitAlias());

		$subQuery = MessageUnreadTable::query()
			->setSelect(['CHAT_ID'])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('CHAT_ID', new SqlExpression('?#', "{$alias}.ID"))
		;

		return $query
			->whereIn('ID', $subQuery)
			->fetchCollection()
			->getIdList()
		;
	}

	private function setLastIdForReadAll(): void
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$connection->queryExecute($helper->prepareCorrelatedUpdate(
			'b_im_relation',
			'R',
			[
				'LAST_ID' => 'C.LAST_MESSAGE_ID',
			],
			' b_im_chat C ',
			" C.ID = R.CHAT_ID AND R.MESSAGE_TYPE NOT IN ('" . IM_MESSAGE_OPEN_LINE . "', '" . IM_MESSAGE_SYSTEM . "')
				AND R.USER_ID = {$this->getContext()->getUserId()}"
		));
	}

	private function setLastIdForReadByIds(array $chatIds): void
	{
		if (empty($chatIds))
		{
			return;
		}

		$chatIdsString = implode(',', $chatIds);
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$connection->queryExecute($helper->prepareCorrelatedUpdate(
			'b_im_relation',
			'R',
			[
				'LAST_ID' => 'C.LAST_MESSAGE_ID',
			],
			' b_im_chat C ',
			" C.ID = R.CHAT_ID AND R.CHAT_ID IN ({$chatIdsString}) AND R.USER_ID = {$this->getContext()->getUserId()}"
		));
	}

	private function updateDateRecent(int $chatId): void
	{
		$userId = $this->getContext()->getUserId();
		\Bitrix\Main\Application::getConnection()->query(
			"UPDATE b_im_recent SET DATE_UPDATE = NOW() WHERE USER_ID = {$userId} AND ITEM_CID = {$chatId}"
		);
	}
}
