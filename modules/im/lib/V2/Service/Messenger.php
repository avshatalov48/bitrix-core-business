<?php

namespace Bitrix\Im\V2\Service;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\GroupChat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Chat\NullChat;
use Bitrix\Im\V2\Link\Calendar\CalendarItem;
use Bitrix\Im\V2\Link\Calendar\CalendarService;
use Bitrix\Im\V2\Link\Task\TaskService;
use Bitrix\Im\V2\Entity\Task\TaskItem;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Tasks\Internals\TaskObject;

class Messenger
{
	use ContextCustomer;

	/**
	 * Returns current instance of the Messenger.
	 * @return self
	 */
	public static function getInstance(): self
	{
		return Locator::getMessenger();
	}

	//region Chats

	/**
	 * @param int $toUserId
	 * @param int $fromUserId
	 * @return NullChat|PrivateChat
	 */
	public function getPrivateChat(int $fromUserId, int $toUserId): Chat
	{
		$chatFactory = ChatFactory::getInstance();
		$chat = $chatFactory
			->setContext($this->context)
			->getPrivateChat($fromUserId, $toUserId)
		;

		if (!$chat)
		{
			return new NullChat();
		}

		return $chat;
	}

	/**
	 * @param string $entityType
	 * @param int|string $entityId
	 * @return NullChat|GroupChat
	 */
	public function getEntityChat(string $entityType, string $entityId): Chat
	{
		$chatFactory = ChatFactory::getInstance();
		$chat = $chatFactory
			->setContext($this->context)
			->getEntityChat($entityType, $entityId)
		;

		if (!$chat)
		{
			return (new NullChat())
				->setPreparedParams([
					'TYPE' => Chat::IM_TYPE_CHAT,
					'ENTITY_TYPE' => $entityType,
					'ENTITY_ID' => $entityId,
				]);
		}

		if (!$chat->hasAccess())
		{
			return new NullChat();
		}

		return $chat;
	}

	/**
	 * @param int $chatId
	 * @return Chat
	 */
	public function getChat(int $chatId): Chat
	{
		return Chat\ChatFactory::getInstance()->getChatById($chatId);
	}

	//endregion

	//region Messages

	/**
	 * @param array|string|null $source
	 */
	public function createMessage($source = null): Message
	{
		if (is_string($source))
		{
			$source = ['MESSAGE' => $source];
		}

		return new Message($source);
	}

	//endregion

	//region Task processing

	public function registerTask(int $chatId, int $messageId, TaskObject $task): void
	{
		try
		{
			$taskService = new TaskService();

			if (!Chat::getInstance($chatId)->hasAccess())
			{
				return;
			}

			$taskService->registerTask($chatId, $messageId, TaskItem::initByTaskObject($task));
		}
		catch (\Bitrix\Main\SystemException $exception)
		{
			//todo: log
		}
	}

	/**
	 * Call when tasks delete to recycle bin or totally
	 * @param array $taskData
	 * @param bool $saveDelete
	 * @return void
	 */
	public function unregisterTask(array $taskData, bool $saveDelete): void
	{
		try
		{
			$taskService = new TaskService();

			$taskService->unregisterTaskByEntity(TaskItem::initByRow($taskData), $saveDelete);
		}
		catch (\Bitrix\Main\SystemException $exception)
		{
			//todo: log
		}
	}

	/**
	 * Call when task delete from recycle bin
	 * @param int $taskId
	 * @return void
	 */
	public function deleteTask(int $taskId): void
	{
		try
		{
			$taskService = new TaskService();

			$taskService->deleteLinkByTaskId($taskId);
		}
		catch (\Bitrix\Main\SystemException $exception)
		{
			//todo: log
		}
	}

	public function updateTask(TaskObject $task): void
	{
		try
		{
			$taskService = new TaskService();

			$taskService->updateTask(TaskItem::initByTaskObject($task));
		}
		catch (\Bitrix\Main\SystemException $exception)
		{
			//todo: log
		}
	}

	//endregion

	//region Calendar processing

	public static function updateCalendar(int $eventId, array $entryFields): void
	{
		if ($entryFields['ID'] !== $entryFields['PARENT_ID'])
		{
			return;
		}

		$calendarService = new CalendarService();
		$calendar = CalendarItem::getByCalendarId($eventId);
		if ($calendar === null)
		{
			return;
		}
		$calendarService->updateCalendar($calendar);
	}

	public static function unregisterCalendar(int $eventId, array $entry): void
	{
		if ($entry['ID'] !== $entry['PARENT_ID'])
		{
			return;
		}

		$calendarService = new CalendarService();
		$calendar = CalendarItem::getByCalendarId($eventId, false);
		if ($calendar === null)
		{
			return;
		}
		$calendarService->unregisterCalendar($calendar);
	}

	//endregion
}
