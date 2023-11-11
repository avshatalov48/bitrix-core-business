<?php

namespace Bitrix\Im\V2\Message\Delete;

use Bitrix\Disk\SystemUser;
use Bitrix\Im\Bot;
use Bitrix\Im\Common;
use Bitrix\Im\Model\MessageIndexTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\Calendar\CalendarItem;
use Bitrix\Im\V2\Link\Calendar\CalendarService;
use Bitrix\Im\V2\Link\Task\TaskItem;
use Bitrix\Im\V2\Link\Task\TaskService;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Pull\Event;

class DeleteService
{
	use ContextCustomer;

	public const DELETE_NONE = 0; // cannot be deleted
	public const DELETE_SOFT = 1; // replacement with the text "message deleted"
	public const DELETE_HARD = 2; // complete removal if no one has read
	public const DELETE_COMPLETE = 3; // unconditional deletion

	private const MESSAGE_OWN_SELF = self::DELETE_NONE;
	private const MESSAGE_OWN_OTHER = 4;

	private const ROLE_USER = self::DELETE_SOFT;
	private const ROLE_MANAGER = self::DELETE_HARD;
	private const ROLE_OWNER = self::DELETE_COMPLETE;

	public const EVENT_AFTER_MESSAGE_DELETE = 'OnAfterMessagesDelete';
	public const OPTION_KEY_DELETE_AFTER = 'complete_delete_message_start_date';
	public const MODE_AUTO = self::DELETE_NONE;
	public const MODE_SOFT = self::DELETE_SOFT;
	public const MODE_HARD = self::DELETE_HARD;
	public const MODE_COMPLETE = self::DELETE_COMPLETE;

	private Message $message;
	private Chat $chat;
	private ?array $chatLastMessage = null;
	private ?int $chatPrevMessageId = null;
	private int $mode = self::MODE_AUTO;
	private bool $needUpdateRecent = false;
	private array $counters;
	private array $lastMessageViewers;
	private int $previousMessageId;

	public function __construct(Message $message)
	{
		$this->message = $message;
		Chat::cleanCache($this->message->getChatId());
		$this->chat = Chat\ChatFactory::getInstance()->getChatById($this->message->getChatId());
	}

	public function setMessage(Message $message): self
	{
		$this->message = $message;
		Chat::cleanCache($this->message->getChatId());
		$this->chat = Chat\ChatFactory::getInstance()->getChatById($this->message->getChatId());

		return $this;
	}

	/**
	 * @param int $mode MODE_AUTO|MODE_SOFT|MODE_HARD|MODE_COMPLETE
	 * @return $this
	 */
	public function setMode(int $mode): self
	{
		if (in_array($mode, [self::MODE_AUTO, self::MODE_SOFT, self::MODE_HARD, self::MODE_COMPLETE], true))
		{
			$this->mode = $mode;
		}

		return $this;
	}

	/**
	 * @return Result
	 */
	public function delete(): Result
	{
		$messageFields = $this->message->toArray();
		$messageFields['PARAMS'] = $this->message->getParams()->toRestFormat();
		$messageFields['CHAT_ENTITY_TYPE'] = $this->chat->getEntityType();
		$messageFields['CHAT_ENTITY_ID'] = $this->chat->getEntityId();

		if (!$this->mode)
		{
			$this->mode = $this->canDelete();
		}

		$files = $this->message->getFiles();

		switch ($this->mode)
		{
			case self::DELETE_SOFT:
				$result = $this->deleteSoft();
				$this->fireEventAfterMessageDelete($messageFields);
				break;
			case self::DELETE_HARD:
				$this->getChatPreviousMessages();
				$result = $this->deleteHard();
				$this->fireEventAfterMessageDelete($messageFields, true);
				break;
			case self::DELETE_COMPLETE:
				$this->getChatPreviousMessages();
				$result = $this->deleteHard(true);
				$this->fireEventAfterMessageDelete($messageFields, true);
				break;
			default:
				return (new Result())->addError(new Message\MessageError(Message\MessageError::MESSAGE_ACCESS_ERROR));
		}

		if (Option::get('im', 'message_history_index'))
		{
			MessageIndexTable::delete($this->message->getId());
		}

		(new UrlService())->deleteUrlsByMessage($this->message);
		foreach ($files as $file)
		{
			$file->getDiskFile()->delete(SystemUser::SYSTEM_USER_ID);
		}

		return $result;
	}

	/**
	 * The method returns the available message deletion level for a specific user:
	 * 0 - none
	 * 1 - soft delete with text replacement
	 * 2 - permanent removal of the message created after installing this update
	 * 3 - complete deletion of any message
	 *
	 * @return int
	 */
	public function canDelete(): int
	{
		if ($this->getContext()->getUser()->isSuperAdmin())
		{
			return self::DELETE_COMPLETE;
		}

		$userId = $this->getContext()->getUserId();

		// not chat access
		if (!$this->chat->hasAccess($userId))
		{
			return self::DELETE_NONE;
		}

		// get user role in this chat
		$removerRole = self::ROLE_USER;
		if ($userId === $this->chat->getAuthorId())
		{
			if ($this->chat->getType() !== Chat::IM_TYPE_PRIVATE)
			{
				$removerRole =  self::ROLE_OWNER;
			}
		}
		else
		{
			$relation = $this->chat->getSelfRelation();
			if ($relation && $relation->getManager())
			{
				$removerRole = self::ROLE_MANAGER;
			}
			elseif ($relation->getUser()->getExternalAuthId() === Bot::EXTERNAL_AUTH_ID)
			{
				return self::DELETE_COMPLETE;
			}
		}

		// determine the owner of the message
		$messageOwner = self::MESSAGE_OWN_OTHER;
		if ($messageAuthor = $this->message->getAuthor())
		{
			if ($messageAuthor->getId() === $userId)
			{
				$messageOwner = self::MESSAGE_OWN_SELF;
			}
			elseif($messageAuthor->getId() === $this->chat->getAuthorId())
			{
				$messageOwner = self::ROLE_OWNER;
			}
			else
			{
				$relations = $this->chat->getRelations(['USER_ID' => $messageAuthor->getId()]);
				if ($user = $relations->getByUserId($messageAuthor->getId(), $this->chat->getChatId()))
				{
					$messageOwner = self::ROLE_USER;
					if ($user->getManager())
					{
						$messageOwner = self::ROLE_MANAGER;
					}
				}
			}
		}

		if ($removerRole <= $messageOwner)
		{
			return self::DELETE_NONE;
		}

		if (
			$messageAuthor === self::ROLE_OWNER
			&& in_array($this->chat->getType(), [Chat::IM_TYPE_OPEN, Chat::IM_TYPE_CHANNEL], true)
		)
		{
			return self::DELETE_COMPLETE;
		}

		// message was read by someone other than the author
		if ($this->message->isViewedByOthers())
		{
			return self::DELETE_SOFT;
		}

		return self::DELETE_HARD;
	}

	private function deleteSoft(): Result
	{
		$date = FormatDate('FULL', $this->message->getDateCreate()->getTimestamp() + \CTimeZone::GetOffset());

		$this->message->setMessage(Loc::getMessage('IM_MESSAGE_DELETED'));
		$this->message->setMessageOut(Loc::getMessage('IM_MESSAGE_DELETED_OUT', ['#DATE#' => $date]));
		$this->message->resetParams([
			'IS_DELETED' => 'Y'
		]);
		$this->message->save();

		$this->sendPullMessage();

		return new Result();
	}

	private function deleteHard($removeAny = false): Result
	{
		if (!$removeAny)
		{
			$deleteAfter = \COption::GetOptionInt('im', self::OPTION_KEY_DELETE_AFTER);
			if ($deleteAfter > $this->message->getDateCreate()->getTimestamp())
			{
				return (new Result())->addError(new Message\MessageError(Message\MessageError::MESSAGE_TOO_OLD_FOR_DELETION));
			}
		}

		$this->deleteLinks();
		$this->recountChat();
		$this->sendPullMessage(true);
		$this->message->delete();

		return new Result();
	}

	private function sendPullMessage(bool $completeDelete = false): Result
	{
		$pullMessage = $this->getFormatPullMessage($completeDelete);

		if ($this->chat instanceof Chat\PrivateChat)
		{
			$userId = $this->message->getAuthorId();
			$companionUserId = $this->chat->getCompanion($userId)->getId();
			$this->sendPullMessagePrivate($userId, $companionUserId, $pullMessage, $completeDelete);
			$this->sendPullMessagePrivate($companionUserId, $userId, $pullMessage, $completeDelete);
		}
		else
		{
			$groupedPullMessage = $this->groupPullByCounter($pullMessage, $completeDelete);
			foreach ($groupedPullMessage as $pullForGroup)
			{
				Event::add($pullForGroup['users'], $pullForGroup['event']);
			}

			if (in_array($this->chat->getType(), [Chat::IM_TYPE_OPEN, Chat::IM_TYPE_OPEN_LINE], true))
			{
				\CPullWatch::AddToStack('IM_PUBLIC_' . $this->chat->getChatId(), $pullMessage);
			}
		}

		return new Result;
	}

	private function sendPullMessagePrivate(int $fromUser, int $toUser, array $pullMessage, bool $completeDelete): void
	{
		$isMuted = false;
		$relation = $this->chat->getRelations()->getByUserId($toUser, $this->chat->getChatId());
		if ($relation !== null)
		{
			$isMuted = $relation->getNotifyBlock() ?? false;
		}
		$pullMessage['params']['dialogId'] = $fromUser;
		$pullMessage['params']['fromUserId'] = $fromUser;
		$pullMessage['params']['toUserId'] = $toUser;
		$pullMessage['params']['counter'] = $this->getCounter($toUser);
		$pullMessage['params']['unread'] = Recent::isUnread($toUser, $this->chat->getType(), $fromUser);
		$pullMessage['params']['muted'] = $isMuted;
		if ($completeDelete && $this->needUpdateRecent)
		{
			$pullMessage['params']['lastMessageViews'] = $this->getLastViewers($toUser);
		}
		Event::add($toUser, $pullMessage);
	}

	public function getFormatPullMessage(bool $completeDelete): array
	{
		$params = [
			'id' => (int)$this->message->getId(),
			'type' => $this->chat->getType() === Chat::IM_TYPE_PRIVATE ? 'private' : 'chat',
			'text' => Loc::getMessage('IM_MESSAGE_DELETED'),
			'senderId' => $this->message->getAuthorId(),
			'params' => ['IS_DELETED' => 'Y', 'URL_ID' => [], 'FILE_ID' => [], 'KEYBOARD' => 'N', 'ATTACH' => []],
			'chatId' => $this->chat->getChatId(),
		];

		if (!$this->chat instanceof Chat\PrivateChat)
		{
			$params['dialogId'] = $this->chat->getDialogId();
		}

		if ($completeDelete && $this->needUpdateRecent)
		{
			if ($this->chatLastMessage['ID'] !== 0)
			{
				$newLastMessage = new Message($this->chatLastMessage['ID']);
				if ($newLastMessage->getId())
				{
					$params['newLastMessage'] = $this->formatNewLastMessage($newLastMessage);
				}
			}
			else
			{
				$params['newLastMessage'] = ['id' => 0];
			}
		}

		return [
			'module_id' => 'im',
			'command' => $completeDelete ? 'messageDeleteComplete' : 'messageDelete',
			'params' => $params,
			'push' => $completeDelete ? ['badge' => 'Y'] : [],
			'extra' => Common::getPullExtra()
		];
	}

	private function groupPullByCounter(array $pullMessage, bool $completeDelete): array
	{
		$events = [];
		/** @var Relation $relation */
		$relations = $this->chat->getRelations();
		$unreadList = Recent::getUnread($this->chat->getType(), $this->chat->getDialogId());
		foreach ($relations as $relation)
		{
			$user = $relation->getUser();
			if (
				(!$user->isActive() && $user->getExternalAuthId() !== \Bitrix\Im\Bot::EXTERNAL_AUTH_ID)
				|| ($this->chat->getEntityType() === Chat::ENTITY_TYPE_LINE && $user->getExternalAuthId() === 'imconnector')
			)
			{
				continue;
			}

			$userId = $relation->getUserId();

			$pullMessage['params']['unread'] = $unreadList[$userId] ?? false;
			$pullMessage['params']['muted'] = $relation->getNotifyBlock() ?? false;

			$events[$userId] = $pullMessage;

			$count = 0;
			if ($this->needUpdateRecent && $completeDelete)
			{
				$lastMessageViews = $this->getLastViewers($userId);
				$events[$userId]['params']['lastMessageViews'] = $lastMessageViews;
				$count = $lastMessageViews['countOfViewers'] ?? 0;
			}

			$unreadGroupFlag = $pullMessage['params']['unread'] ? 1 : 0;
			$mutedGroupFlag = $pullMessage['params']['muted'] ? 1 : 0;

			$events[$userId]['params']['counter'] = $this->getCounter($userId);
			$events[$userId]['groupId'] =
				'im_chat_'
				. $this->chat->getChatId()
				. '_'. $this->message->getMessageId()
				. '_'. $events[$userId]['params']['counter']
				. '_'. $count
				. '_'. $unreadGroupFlag
				. '_'. $mutedGroupFlag
			;
		}

		return (new Message\Send\PushService())->getEventByCounterGroup($events);
	}

	private function deleteLinks()
	{
		$connection = Application::getConnection();

		// delete chats with PARENT_MID
		$childChatResult = Chat\ChatFactory::getInstance()->findChat(['PARENT_MID' => $this->message->getId()]);
		if ($childChatResult->hasResult())
		{
			$childChat = Chat\ChatFactory::getInstance()->getChat($childChatResult->getResult());
			$childChat->deleteChat();
		}

		(new \Bitrix\Im\V2\Link\Favorite\FavoriteService())->unmarkMessageAsFavoriteForAll($this->message);
		(new \Bitrix\Im\V2\Message\ReadService())->deleteByMessageId(
			$this->message->getMessageId(),
			$this->chat->getRelations()->getUserIds()
		);
		$this->message->unpin();

		if (Loader::includeModule('tasks'))
		{
			$taskItem = TaskItem::getByMessageId($this->message->getMessageId());
			if ($taskItem !== null)
			{
				$taskItem->setMessageId(0);
				(new TaskService())->updateTaskLink($taskItem);
			}
		}

		if (Loader::includeModule('calendar'))
		{
			$calendarItem = CalendarItem::getByMessageId($this->message->getMessageId());
			if ($calendarItem !== null)
			{
				$calendarItem->setMessageId(0);
				(new CalendarService())->updateCalendarLink($calendarItem);
			}
		}

		$this->message->getParams()->delete();

		// delete unused rows in db
		$tablesToDeleteRow = [
			'b_im_message_uuid',
			'b_im_message_favorite',
			'b_im_message_disappearing',
			'b_im_message_index',
			'b_im_link_reminder',
			'b_imconnectors_delivery_mark',
		];

		foreach ($tablesToDeleteRow as $table)
		{
			$connection->query("DELETE FROM " . $table . " WHERE MESSAGE_ID = " . $this->message->getId());
		}
	}

	private function fireEventAfterMessageDelete(array $messageFields, bool $completeDelete = false): Result
	{
		$result = new Result;

		foreach(GetModuleEvents('im', self::EVENT_AFTER_MESSAGE_DELETE, true) as $event)
		{
			$deleteFlags = [
				'ID' => $messageFields['ID'],
				'USER_ID' => 0,
				'COMPLETE_DELETE' => $completeDelete,
				'BY_EVENT' => false
			];

			ExecuteModuleEventEx($event, [$messageFields['ID'], $messageFields, $deleteFlags]);
		}

		return $result;
	}

	private function recountChat(): void
	{
		$this->updateRecent();
		if (!is_null($this->chatLastMessage))
		{
			$isMessageRead = !!MessageViewedTable::query()
				->addFilter('MESSAGE_ID', $this->chatLastMessage['ID'])
				->fetch();

			$this->chat->setLastMessageId((int)($this->chatLastMessage['ID'] ?? 0));
			$this->chat->setLastMessageStatus($isMessageRead ? \IM_MESSAGE_STATUS_DELIVERED : \IM_MESSAGE_STATUS_RECEIVED);
		}

		$this->chat->setPrevMessageId($this->chatPrevMessageId ?? 0);

		$this->chat->setMessageCount($this->chat->getMessageCount() - 1);
		$this->chat->save();
		$this->updateRelation();
	}

	private function updateRelation(): void
	{
		if ($this->needUpdateRecent)
		{
			$newLastId = $this->chatLastMessage['ID'] ?? 0;
		}
		else
		{
			$newLastId = $this->getPreviousMessageId();
		}
		Application::getConnection()->query("
			UPDATE b_im_relation 
			SET LAST_ID = {$newLastId} 
			WHERE CHAT_ID = {$this->message->getChatId()} AND LAST_ID = {$this->message->getMessageId()}
			");
	}

	private function updateRecent(): void
	{
		if ($this->chatLastMessage && (int)$this->chatLastMessage['ID'] !== $this->message->getId())
		{
			$update = [
				'DATE_MESSAGE' => $this->chatLastMessage['DATE_CREATE'],
				'DATE_UPDATE' => $this->chatLastMessage['DATE_CREATE'],
				'ITEM_MID' => $this->chatLastMessage['ID'] ?? 0,
			];

			if ($this->chat instanceof Chat\PrivateChat || $this->chat->getType() === Chat::IM_TYPE_PRIVATE)
			{
				$userId = $this->getContext()->getUserId();
				$opponentId = $this->chat->getCompanion($userId)->getId();
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

	private function getCounter(int $userId): int
	{
		$this->counters ??= (new Message\CounterService())
			->getByChatForEachUsers($this->chat->getChatId(), $this->chat->getRelations()->getUserIds())
		;

		return $this->counters[$userId] ?? 0;
	}

	private function formatNewLastMessage(Message $message): array
	{
		$result = $message
			->setViewed(false) // todo: refactor this
			->toRestFormat()
		;

		if ($message->getFiles()->count() <= 0)
		{
			return $result;
		}

		$file = $message->getFiles()->getAny();

		if ($file === null)
		{
			return $result;
		}

		$result['file'] = ['type' => $file->getContentType(), 'name' => $file->getDiskFile()->getName()];

		return $result;
	}

	private function getLastViewers(int $userId): array
	{
		$this->lastMessageViewers ??= $this->chat->getLastMessageViewsByGroups();

		foreach ($this->lastMessageViewers as $viewers)
		{
			if (isset($viewers['USERS'][$userId]))
			{
				return Common::toJson($viewers['VIEW_INFO'] ?? []);
			}
		}

		return [];
	}

	private function getPreviousMessageId(): int
	{
		if (isset($this->previousMessageId))
		{
			return $this->previousMessageId;
		}

		$result = MessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $this->chat->getChatId())
			->where('ID', '<', $this->message->getMessageId())
			->setOrder(['ID' => 'DESC'])
			->setLimit(1)
			->fetch()
		;
		$this->previousMessageId = ($result && isset($result['ID'])) ? (int)$result['ID'] : 0;

		return $this->previousMessageId;
	}

	private function getChatPreviousMessages(): ?array
	{
		$lastChatMessageId = $this->chat->getLastMessageId();
		$prevChatMessageId = $this->chat->getPrevMessageId();

		if (
			!in_array(
				$this->message->getId(),
				[
					$lastChatMessageId,
					$prevChatMessageId,
					0
				],
				true
			)
		)
		{
			return $this->message->toArray();
		}

		$lastMessages = MessageTable::query()
			->setSelect(['ID', 'DATE_CREATE', 'MESSAGE'])
			->addFilter('CHAT_ID', $this->chat->getChatId())
			->setOrder(['ID' => 'DESC'])
			->setLimit(3)
			->fetchAll();

		$this->chatPrevMessageId = (int)($lastMessages[2]['ID'] ?? 0);
		$nullMessage = ['ID' => 0, 'DATE_CREATE' => (new DateTime()), 'MESSAGE' => ''];
		if ($this->message->getId() === $lastChatMessageId)
		{
			$this->needUpdateRecent = true;
			$this->chatLastMessage = $lastMessages[1] ?? $nullMessage;
		}
		else
		{
			$this->chatLastMessage = $lastMessages[0] ?? $nullMessage;
		}

		return $this->chatLastMessage;
	}
}
