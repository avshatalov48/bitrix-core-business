<?php

namespace Bitrix\Im\V2\Service;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\GroupChat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Chat\EntityChat;
use Bitrix\Im\V2\Chat\NullChat;
use Bitrix\Im\V2\Link\Calendar\CalendarItem;
use Bitrix\Im\V2\Link\Calendar\CalendarService;
use Bitrix\Im\V2\Link\Task\TaskService;
use Bitrix\Im\V2\Entity\Task\TaskItem;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Delete\DeleteService;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Tasks\Internals\TaskObject;

class Messenger
{
	use ContextCustomer;

	private const INTRANET_MENU_ID = 'menu_im_messenger';

	/**
	 * Returns current instance of the Messenger.
	 * @return self
	 */
	public static function getInstance(): self
	{
		return Locator::getMessenger();
	}

	public function checkAccessibility(): \Bitrix\Im\V2\Result
	{
		$result = new \Bitrix\Im\V2\Result();

		if (!$this->isPullEnabled())
		{
			$result->addError(new MessengerError(MessengerError::PULL_NOT_ENABLED));
		}

		if (!$this->isEnabled())
		{
			$result->addError(new MessengerError(MessengerError::MESSENGER_NOT_ENABLED));
		}

		return $result;
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
	 * @return EntityChat|GroupChat|NullChat
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

		if (!$chat->checkAccess()->isSuccess())
		{
			return new NullChat();
		}

		return $chat;
	}

	public function getGeneralChat(): Chat
	{
		return Chat\GeneralChat::get();
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

	public function createChat(array $fields): \Bitrix\Im\V2\Result
	{
		return \Bitrix\Im\V2\Chat\ChatFactory::getInstance()->addChat($fields);
	}

	/**
	 * Delete message
	 *
	 * @param Message $message
	 * @param int $mode DeleteService::MODE_AUTO|DeleteService::MODE_SOFT|DeleteService::MODE_HARD|DeleteService::MODE_COMPLETE
	 * @return Result
	 */
	public function deleteMessage(Message $message, int $mode = 0): Result
	{
		$result = new Result();

		$deleteService = new Message\Delete\DeleteService($message);
		$deleteService->setMode($mode);
		$deleteService->delete();

		return $result;
	}

	/**
	 * Disappear message
	 *
	 * @param Message $message
	 * @param int $hours
	 * @return Result
	 */
	public function disappearMessage(Message $message, int $hours): Result
	{
		$deleteService = new DeleteService($message);
		if ($deleteService->canDelete() < DeleteService::DELETE_HARD)
		{
			return (new Result())->addError(new MessageError(MessageError::ACCESS_DENIED));
		}

		return Message\Delete\DisappearService::disappearMessage($message, $hours);
	}

	/**
	 * Update message
	 *
	 * @param Message $message
	 * @param string|null $messageText
	 * @return Result
	 */
	public function updateMessage(Message $message, ?string $messageText): Result
	{
		$updateService = new Message\Update\UpdateService($message);
		return $updateService->update($messageText);
	}

	//endregion

	//region Task processing

	public function registerTask(int $chatId, int $messageId, TaskObject $task): void
	{
		try
		{
			$taskService = new TaskService();
			$chat = Chat::getInstance($chatId);

			if (!$chat->checkAccess()->isSuccess() || !$chat->canDo(Action::CreateTask))
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

	private function isPullEnabled(): bool
	{
		return \CModule::IncludeModule("pull") && \CPullOptions::GetQueueServerStatus();
	}

	private function isEnabled(): bool
	{
		if (
			Loader::includeModule('intranet')
			&& method_exists(\Bitrix\Intranet\Settings\Tools\ToolsManager::class, 'checkAvailabilityByMenuId')
		)
		{
			return \Bitrix\Intranet\Settings\Tools\ToolsManager::getInstance()
				->checkAvailabilityByMenuId(static::INTRANET_MENU_ID)
			;
		}

		return true;
	}
}
