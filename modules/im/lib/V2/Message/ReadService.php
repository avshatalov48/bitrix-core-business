<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

class ReadService
{
	use ContextCustomer
	{
		setContext as private defaultSetContext;
	}

	protected CounterService $counterService;
	protected ViewedService $viewedService;

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
		$time = microtime(true);
		$this->viewedService->addTo($message);
		$this->updateDateRecent($message->getChatId());
		$this->sendPush($message->getChatId(), [$this->getContext()->getUserId()], $counter, $time);

		return (new Result())->setResult(['COUNTER' => $counter]);
	}

	public function read(MessageCollection $messages, Chat $chat): Result
	{
		$maxId = max($messages->getIds());
		$this->setLastIdForRead($maxId, $chat->getChatId());
		$this->counterService->deleteTo($messages[$maxId]);
		$counter = $this->counterService->getByChat($chat->getChatId());
		$time = microtime(true);
		$this->viewedService->add($messages);
		$this->updateDateRecent($chat->getChatId());
		$this->sendPush($chat->getChatId(), [$this->getContext()->getUserId()], $counter, $time);

		return (new Result())->setResult(['COUNTER' => $counter]);
	}

	public function readNotifications(MessageCollection $messages, array $userByChatId): Result
	{
		$chatIds = [];

		foreach ($messages as $message)
		{
			$chatIds[$message->getChatId()] = 0;
		}

		$chatIds = array_keys($chatIds);

		$this->counterService->deleteByMessageIdsForAll($messages->getIds(), $userByChatId);
		$counters = $this->counterService->getForNotifyChats($chatIds);
		$time = microtime(true);
		//$this->viewedController->add($messages);

		foreach ($chatIds as $chatId)
		{
			$this->sendPush($chatId, [(int)$userByChatId[$chatId]], $counters[$chatId], $time);
		}

		return (new Result())->setResult(['COUNTERS' => $counters]);
	}

	public function readAllInChat(int $chatId): Result
	{
		$lastId = $this->viewedService->getLastMessageIdInChat($chatId) ?? 0;
		$this->setLastIdForRead($lastId, $chatId);
		$this->counterService->deleteByChatId($chatId);
		$time = microtime(true);
		$counter = 0;
		//$this->viewedController->addAllFromChat($chatId);
		$this->updateDateRecent($chatId);
		$this->sendPush($chatId, [$this->getContext()->getUserId()], $counter, $time);

		return (new Result())->setResult(['COUNTER' => $counter]);
	}

	public function readAll(): void
	{
		$this->setLastIdForReadAll();
		$this->counterService->deleteAll();
	}

	public function unreadTo(Message $message): Result
	{
		//$this->setLastIdForUnread($message->getMessageId(), $message->getChatId());
		$relation = $message->getChat()->getSelfRelation();
		if ($relation === null)
		{
			return new Result();
		}
		$this->counterService->addStartingFrom($message->getMessageId(), $relation);
		$this->viewedService->deleteStartingFrom($message);

		return new Result();
	}

	public function unreadNotifications(MessageCollection $messages, Relation $relation): Result
	{
		$this->counterService->addCollection($messages, $relation);
		$counter = $this->counterService->getByChat($relation->getChatId());
		$time = microtime(true);
		//$this->viewedController->deleteByMessageIds($messages->getIds(), $relation->getChatId());
		$this->sendPush($relation->getChatId(), [$this->getContext()->getUserId()], $counter, $time);

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
		Recent::unread($message->getChat()->getDialogId(), false, $this->getContext()->getUserId());
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
		$onlineUsers = UserCollection::filterOnlineUserId($relations->getUserIds());
		$counters = $this->counterService->getByChatForEachUsers($message->getChatId(), $onlineUsers);

		$time = microtime(true);
		$this->sendPushByGroup($message->getChatId(), $counters, $time);

		return $counters;
	}

	/**
	 * Returns unread counters for the rest answer.
	 *
	 * @param Message $message
	 * @param RelationCollection $relations
	 * @return Result
	 */
	public function onAfterMessageSend(Message $message, RelationCollection $relations): Result
	{
		$counters = $this
			->markMessageUnread($message, $relations)
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

		$time = microtime(true);
		$this->sendPush($relation->getChatId(), [$this->getContext()->getUserId()], $counter, $time);

		return (new Result())->setResult(['COUNTER' => $counter]);
	}


	//region Push
	protected function sendPushByGroup(int $chatId, array $counters, float $time): void
	{
		$groups = $this->splitRecipientsByGroups($counters);

		foreach ($groups as $group)
		{
			$this->sendPush($chatId, $group['USER_IDS'], $group['COUNTER'], $time);
		}
	}

	protected function splitRecipientsByGroups(array $counters): array
	{
		$currentUserId = $this->getContext()->getUserId();
		$groups = [];

		foreach ($counters as $userId => $counter)
		{
			if ($userId === $currentUserId)
			{
				$counter = 0;
			}

			$groups[$counter]['COUNTER'] = $counter;
			$groups[$counter]['USER_IDS'][] = $userId;
		}

		return array_values($groups);
	}

	public function sendPush(int $chatId, array $userIds, int $counter, float $time): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}
		\Bitrix\Pull\Event::add($userIds, [
			'module_id' => 'im',
			'command' => 'chatCounterChange',
			'params' => [
				'chatId' => $chatId,
				'counter' => $counter,
				'time' => $time
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	//endregion

	public function deleteByMessageId(int $messageId, ?array $invalidateCacheUsers = null): void
	{
		$this->counterService->deleteByMessageIdForAll($messageId, $invalidateCacheUsers);
		$this->viewedService->deleteByMessageIdForAll($messageId);
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
		$result = ChatTable::query()->setSelect(['LAST_MESSAGE_ID'])->where('ID', $chatId)->fetch();

		return $result ? (int)$result['LAST_MESSAGE_ID'] : 0;
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

	private function setLastIdForReadAll(): void
	{
		$sql = "
			UPDATE b_im_relation R
			INNER JOIN b_im_chat C on C.ID = R.CHAT_ID
			SET R.LAST_ID = C.LAST_MESSAGE_ID
			WHERE R.MESSAGE_TYPE NOT IN ('" . IM_MESSAGE_OPEN_LINE . "', '" . IM_MESSAGE_SYSTEM . "')
			AND R.USER_ID = {$this->getContext()->getUserId()}
		";

		Application::getConnection()->queryExecute($sql);
	}

	private function updateDateRecent(int $chatId): void
	{
		$userId = $this->getContext()->getUserId();
		\Bitrix\Main\Application::getConnection()->query(
			"UPDATE b_im_recent SET DATE_UPDATE = NOW() WHERE USER_ID = {$userId} AND ITEM_CID = {$chatId}"
		);
	}
}