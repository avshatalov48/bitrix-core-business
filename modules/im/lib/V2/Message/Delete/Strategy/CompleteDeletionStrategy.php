<?php

namespace Bitrix\Im\V2\Message\Delete\Strategy;

use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Link\Calendar\CalendarCollection;
use Bitrix\Im\V2\Link\Calendar\CalendarService;
use Bitrix\Im\V2\Link\Task\TaskCollection;
use Bitrix\Im\V2\Link\Task\TaskService;
use Bitrix\Im\V2\Message\Delete\DeletionMode;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Sync\Event;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class CompleteDeletionStrategy extends DeletionStrategy
{
	protected ?array $chatLastMessage = null;
	protected ?array $chatPrevMessage = null;
	protected ?array $previousMessageIds = null;

	/**
	 * @throws InterruptedExecutionException
	 */
	protected function onBeforeDelete(): void
	{
		if ($this->chat instanceof Chat\NullChat)
		{
			throw new InterruptedExecutionException(
				(new Result())->addError(new ChatError(ChatError::NOT_FOUND))
			);
		}

		$this->messages->fillFiles();

		$this->fillChatPreviousMessages();
		if ($this->messages->count() !== 0)
		{
			$this->deleteLinks();
			$this->recountChat();
		}
	}

	/**
	 * @throws InterruptedExecutionException
	 */
	protected function execute(): void
	{
		$result = $this->messages->delete();
		$this->checkResult($result);
	}

	protected function fillChatPreviousMessages(): void
	{
		$lastChatMessageId = $this->chat->getLastMessageId();
		$prevChatMessageId = $this->chat->getPrevMessageId();

		$intersection = array_intersect(
			[
				0,
				$prevChatMessageId,
				$lastChatMessageId,
			],
			$this->messages->getIds(),
		);

		if (empty($intersection))
		{
			return;
		}

		$lastMessages = MessageTable::query()
			->setSelect(['ID', 'DATE_CREATE', 'MESSAGE'])
			->where('CHAT_ID', $this->chat->getChatId())
			->whereNotIn('ID', $this->messages->getIds())
			->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
			->setLimit(2)
			->fetchAll();

		$nullMessage = ['ID' => 0, 'DATE_CREATE' => (new DateTime()), 'MESSAGE' => ''];

		if (isset($intersection[2]))
		{
			$this->chatLastMessage = $lastMessages[0] ?? $nullMessage;
			$this->chatPrevMessage = $lastMessages[1] ?? $nullMessage;
		}
		elseif (isset($intersection[1]))
		{
			$this->chatPrevMessage = $lastMessages[1] ?? $nullMessage;
		}
	}

	protected function deleteLinks()
	{
		$connection = Application::getConnection();

		(new \Bitrix\Im\V2\Link\Favorite\FavoriteService())->unmarkMessagesAsFavoriteForAll($this->messages);
		(new \Bitrix\Im\V2\Message\ReadService())->deleteByMessages(
			$this->messages,
			$this->chat->getRelations()->getUserIds()
		);

		$this->messages->unpin(clearParams: false);

		if (Loader::includeModule('tasks'))
		{
			$taskCollection = TaskCollection::getByMessages($this->messages);

			foreach ($taskCollection as $taskItem)
			{
				$taskItem->setMessageId(0);
			}

			(new TaskService())->updateTaskLinks($taskCollection);
		}

		if (Loader::includeModule('calendar'))
		{
			$calendarCollection = CalendarCollection::getByMessages($this->messages);

			foreach ($calendarCollection as $calendarItem)
			{
				$calendarItem->setMessageId(0);
			}

			(new CalendarService())->updateCalendarLinks($calendarCollection);
		}

		$this->messages->deleteParams();

		// delete unused rows in db
		$tablesToDeleteRow = [
			'b_im_message_uuid' => 'im',
			'b_im_message_favorite' => 'im',
			'b_im_message_disappearing' => 'im',
			'b_im_message_index' => 'im',
			'b_im_link_reminder' => 'im',
			'b_imconnectors_delivery_mark' => 'imconnector',
		];

		foreach ($tablesToDeleteRow as $table => $module)
		{
			if ($module !== 'im' && !Loader::includeModule($module))
			{
				continue;
			}
			$connection->query(
				"DELETE FROM " . $table . " WHERE MESSAGE_ID IN ( "
				. implode(', ', $this->messages->getIds())
				. " ) "
			);
		}

		$resultGetComments = CommentChat::getChatsByMessages($this->messages);
		if (!$resultGetComments->isSuccess())
		{
			return;
		}

		foreach ($resultGetComments->getResult() as $chat)
		{
			$chat?->deleteChat();
		}
	}

	protected function recountChat()
	{
		$this->updateRecent();

		if (isset($this->chatLastMessage) && $this->chatLastMessage)
		{
			$this->chat->setLastMessageId((int)($this->chatLastMessage['ID'] ?? 0));
		}

		if (isset($this->chatPrevMessage) && $this->chatPrevMessage)
		{
			$this->chat->setPrevMessageId((int)($this->chatPrevMessage['ID'] ?? 0));
		}

		$this->chat->setMessageCount($this->chat->getMessageCount() - $this->messages->count());
		$this->chat->save();
		$this->updateRelation();
	}

	protected function updateRecent(): void
	{
		if (isset($this->chatLastMessage) && !in_array((int)$this->chatLastMessage['ID'], $this->messages->getIds(), true))
		{
			$update = [
				'DATE_MESSAGE' => $this->chatLastMessage['DATE_CREATE'],
				'DATE_LAST_ACTIVITY' => $this->chatLastMessage['DATE_CREATE'],
				'DATE_UPDATE' => $this->chatLastMessage['DATE_CREATE'],
				'ITEM_MID' => $this->chatLastMessage['ID'] ?? 0,
			];

			if ($this->chat instanceof Chat\PrivateChat || $this->chat->getType() === Chat::IM_TYPE_PRIVATE)
			{
				$userIds = array_values($this->chat?->getRelations()->getUserIds());
				$userId = $userIds[0];
				$opponentId = $this->chat?->getCompanion($userId)->getId();
				RecentTable::updateByFilter(
					[
						'=USER_ID' => $userId,
						'=ITEM_TYPE' => Chat::IM_TYPE_PRIVATE,
						'=ITEM_ID' => $opponentId
					],
					$update
				);
				RecentTable::updateByFilter(
					[
						'=USER_ID' => $opponentId,
						'=ITEM_TYPE' => Chat::IM_TYPE_PRIVATE,
						'=ITEM_ID' => $userId
					],
					$update
				);
			}
			else
			{
				RecentTable::updateByFilter(
					['=ITEM_TYPE' => $this->chat->getType(), '=ITEM_ID' => $this->chat->getId()],
					$update
				);
			}
		}
	}

	private function updateRelation(): void
	{
		$prevMessageIds = $this->getPreviousMessageIds();
		$conditionString = '';
		foreach ($prevMessageIds as $newLastId => $oldLastIds)
		{
			if (empty($oldLastIds) || !isset($newLastId))
			{
				continue;
			}

			$conditionString .= "WHEN LAST_ID IN (" . implode(',', $oldLastIds) .") THEN {$newLastId}\n";
		}

		Application::getConnection()->query("
			UPDATE b_im_relation
			SET LAST_ID = CASE
				{$conditionString}
				ELSE LAST_ID
			END
			WHERE CHAT_ID = {$this->chat->getChatId()}
		");
	}

	protected function getPreviousMessageIds(): ?array
	{
		if (isset($this->previousMessageIds))
		{
			return $this->previousMessageIds;
		}

		$previousMessageIds = [];
		$ids = $this->messages->getIds();

		foreach ($this->messages as $message)
		{
			$result = MessageTable::query()
				->setSelect(['ID'])
				->where('CHAT_ID', $message->getChatId())
				->where('ID', '<', $message->getMessageId())
				->where('DATE_CREATE', '<=', $message->getDateCreate())
				->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
				->setLimit(1)
				->fetch()
			;

			$previousMessageId = (int)($result['ID'] ?? 0);
			$previousMessageIds[$message->getId()] = $previousMessageId;
		}

		$getPreviousId = static function (int $currentId) use (&$getPreviousId, &$previousMessageIds, &$ids): int {
			if (in_array($previousMessageIds[$currentId], $ids, true))
			{
				$prevId = $getPreviousId($previousMessageIds[$currentId]);
				$previousMessageIds[$currentId] = $prevId;

				return $prevId;
			}

			return $previousMessageIds[$currentId];
		};

		$result = [];

		foreach ($previousMessageIds as $currentMessageId => $previousMessageId)
		{
			$prevId = $getPreviousId($currentMessageId);
			$result[$prevId][] = $currentMessageId;
		}

		$this->previousMessageIds = $result;

		return $result;
	}

	protected function onAfterDelete(): void
	{
		$this->logToSync(Event::COMPLETE_DELETE_EVENT);
		$this->deleteFiles();
		$this->chat->onAfterMessagesDelete($this->messages, $this->getDeletionMode());
	}

	protected function getDeletionMode(): DeletionMode
	{
		return DeletionMode::Complete;
	}
}