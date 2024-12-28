<?php

namespace Bitrix\Im\V2\Message\Delete;

use Bitrix\Disk\SystemUser;
use Bitrix\Im\Bot;
use Bitrix\Im\Common;
use Bitrix\Im\Model\MessageIndexTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Analytics\MessageAnalytics;
use Bitrix\Im\V2\Analytics\MessageContent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\Calendar\CalendarItem;
use Bitrix\Im\V2\Link\Calendar\CalendarService;
use Bitrix\Im\V2\Link\Task\TaskItem;
use Bitrix\Im\V2\Link\Task\TaskService;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Sync;
use Bitrix\ImOpenlines\Connector;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Pull\Event;

class DeleteService
{
	use ContextCustomer
	{
		setContext as private defaultSetContext;
	}

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
	private array $messageForEvent;
	private bool $byEvent = false;
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
		$this->setMessage($message);
	}

	public function setMessage(Message $message): self
	{
		$this->message = $message;
		Chat::cleanCache($this->message->getChatId() ?? 0);
		$this->chat = Chat\ChatFactory::getInstance()->getChatById($this->message->getChatId() ?? 0);

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

	public function setByEvent(bool $byEvent): self
	{
		$this->byEvent = $byEvent;

		return $this;
	}

	/**
	 * @return Result
	 */
	public function delete(): Result
	{
		if (!$this->message->getId() || $this->chat instanceof Chat\NullChat)
		{
			return new Result();
		}

		$message = $this->getMessageForEvent();
		if (!$this->mode)
		{
			$this->mode = $this->canDelete();
		}

		$files = $this->message->getFiles();

		$messageType = (new MessageContent($this->message))->getComponentName();

		switch ($this->mode)
		{
			case self::DELETE_SOFT:
				$result = $this->deleteSoft();
				\Bitrix\Im\Bot::onMessageDelete($this->message->getId(), $message);
				$this->fireEventAfterMessageDelete($message);
				break;
			case self::DELETE_HARD:
				$this->getChatPreviousMessages();
				\Bitrix\Im\Bot::onMessageDelete($this->message->getId(), $message);
				$result = $this->deleteHard();
				$this->fireEventAfterMessageDelete($message, true);
				break;
			case self::DELETE_COMPLETE:
				$this->getChatPreviousMessages();
				\Bitrix\Im\Bot::onMessageDelete($this->message->getId(), $message);
				$result = $this->deleteHard(true);
				$this->fireEventAfterMessageDelete($message, true);
				break;
			default:
				return (new Result())->addError(new Message\MessageError(Message\MessageError::ACCESS_DENIED));
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

		if ($result->isSuccess())
		{
			(new MessageAnalytics($this->message))->addDeleteMessage($messageType);
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

		if (!$this->chat->checkAccess($this->getContext()->getUserId())->isSuccess())
		{
			return self::DELETE_NONE;
		}

		if ($this->chat instanceof Chat\OpenLineChat && Loader::includeModule('imopenlines'))
		{
			$resultForOpenLine = $this->checkForOpenLine();

			if ($resultForOpenLine !== null)
			{
				return $resultForOpenLine;
			}
		}

		if (!$this->isOwnMessage())
		{
			if ($this->chat->canDo(Action::DeleteOthersMessage))
			{
				return $this->chat instanceof Chat\CommentChat ? self::DELETE_SOFT : self::DELETE_COMPLETE;
			}

			return self::DELETE_NONE;
		}

		if ($this->chat instanceof Chat\ChannelChat || $this->chat instanceof Chat\GeneralChat)
		{
			if ($this->chat->canDo(Action::Send))
			{
				return self::DELETE_COMPLETE;
			}

			return self::DELETE_NONE;
		}

		if ($this->chat instanceof Chat\CommentChat)
		{
			return self::DELETE_SOFT;
		}

		if (!$this->message->isViewedByOthers())
		{
			return $this->chat instanceof Chat\OpenLineChat ? self::DELETE_SOFT : self::DELETE_HARD;
		}

		return self::DELETE_SOFT;
	}

	protected function checkForOpenLine(): ?int
	{
		if ($this->getContext()->getUser()->isBot())
		{
			return self::DELETE_COMPLETE;
		}

		if ($this->isOwnMessage() && !$this->chat->canDeleteOwnMessage())
		{
			return self::DELETE_NONE;
		}

		if (!$this->isOwnMessage() && !$this->chat->canDeleteMessage())
		{
			return self::DELETE_NONE;
		}

		return null;
	}

	protected function isOwnMessage(): bool
	{
		return
			$this->getContext()->getUserId() === $this->message->getAuthorId()
			&& !$this->message->isSystem()
			;
	}

	private function deleteSoft(): Result
	{
		$this->message->setMessage(Loc::getMessage('IM_MESSAGE_DELETED'));
		$this->message->setMessageOut($this->getMessageOut());
		$this->message->resetParams([
			'IS_DELETED' => 'Y'
		]);
		$this->message->save();
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::DELETE_EVENT, Sync\Event::UPDATED_MESSAGE_ENTITY, $this->message->getId()),
			fn () => $this->chat->getRelations()->getUserIds(),
			$this->chat->getType()
		);

		$this->sendPullMessage();

		return new Result();
	}

	private function getMessageOut(): string
	{
		$date = $this->message->getDateCreate()->toString();

		return Loc::getMessage('IM_MESSAGE_DELETED_OUT', ['#DATE#' => $date]);
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
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::COMPLETE_DELETE_EVENT, Sync\Event::MESSAGE_ENTITY, $this->message->getId()),
			fn () => $this->chat->getRelations()->getUserIds(),
			$this->chat->getType()
		);

		return new Result();
	}

	private function sendPullMessage(bool $completeDelete = false): Result
	{
		$pullMessage = $this->getFormatPullMessage($completeDelete);

		if ($this->chat instanceof Chat\PrivateChat)
		{
			$userId = $this->chat->getAuthorId();
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

			$pullMessage['extra']['is_shared_event'] = true;

			if ($this->chat->getType() === Chat::IM_TYPE_COMMENT)
			{
				\CPullWatch::AddToStack('IM_PUBLIC_COMMENT_' . $this->chat->getParentChatId(), $pullMessage);
			}

			if ($this->chat->needToSendPublicPull())
			{
				\CPullWatch::AddToStack('IM_PUBLIC_' . $this->chat->getChatId(), $pullMessage);
			}
			if ($this->chat->getType() === Chat::IM_TYPE_OPEN_CHANNEL && $this->needUpdateRecent)
			{
				Chat\OpenChannelChat::sendSharedPull($pullMessage);
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
			'unread' => false,
			'muted' => false,
			'counter' => 0,
			'counterType' => $this->chat->getCounterType()->value,
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

		return Message\Send\PushService::getEventByCounterGroup($events);
	}

	private function deleteLinks()
	{
		$connection = Application::getConnection();

		// delete chats with PARENT_MID
		/*$childChatResult = Chat\ChatFactory::getInstance()->findChat(['PARENT_MID' => $this->message->getId()]);
		if ($childChatResult->hasResult())
		{
			$childChat = Chat\ChatFactory::getInstance()->getChat($childChatResult->getResult());
			$childChat->deleteChat();
		}*/
		if ($this->chat instanceof Chat\ChannelChat)
		{
			$result = Chat\CommentChat::get($this->message, false);
			if ($result->isSuccess())
			{
				/** @var Chat\CommentChat $chat */
				$chat = $result->getResult();
				Message\CounterService::deleteByChatIdForAll($chat->getId());
			}
		}

		(new \Bitrix\Im\V2\Link\Favorite\FavoriteService())->unmarkMessageAsFavoriteForAll($this->message);
		(new \Bitrix\Im\V2\Message\ReadService())->deleteByMessage(
			$this->message,
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
			$connection->query("DELETE FROM " . $table . " WHERE MESSAGE_ID = " . $this->message->getId());
		}

		$resultGetComment = Chat\CommentChat::get($this->message, false);
		if ($resultGetComment->isSuccess())
		{
			$resultGetComment->getResult()?->deleteChat();
		}
	}

	private function fireEventAfterMessageDelete(array $messageFields, bool $completeDelete = false): Result
	{
		$result = new Result;
		$deleteFlags = [
			'ID' => $this->message->getId(),
			'USER_ID' => $this->getContext()->getUserId(),
			'COMPLETE_DELETE' => $completeDelete,
			'BY_EVENT' => $this->byEvent,
		];

		foreach(GetModuleEvents('im', self::EVENT_AFTER_MESSAGE_DELETE, true) as $event)
		{
			ExecuteModuleEventEx($event, [$this->message->getId(), $messageFields, $deleteFlags]);
		}

		return $result;
	}

	private function recountChat(): void
	{
		$this->updateRecent();
		if (!is_null($this->chatLastMessage))
		{
			$this->chat->setLastMessageId((int)($this->chatLastMessage['ID'] ?? 0));
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
				'DATE_LAST_ACTIVITY' => $this->chatLastMessage['DATE_CREATE'],
				'DATE_UPDATE' => $this->chatLastMessage['DATE_CREATE'],
				'ITEM_MID' => $this->chatLastMessage['ID'] ?? 0,
			];

			if ($this->chat instanceof Chat\PrivateChat || $this->chat->getType() === Chat::IM_TYPE_PRIVATE)
			{
				$userIds = array_values($this->chat->getRelations()->getUserIds());
				$userId = $userIds[0];
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

	public function getMessageForEvent(): array
	{
		if (isset($this->messageForEvent))
		{
			return $this->messageForEvent;
		}

		$message = [
			'ID' => $this->message->getId(),
			'CHAT_ID' => $this->message->getChatId(),
			'AUTHOR_ID' => $this->message->getAuthorId(),
			'MESSAGE' => $this->getMessageOut(),
			'MESSAGE_OUT' => $this->message->getMessageOut(),
			'DATE_CREATE' => $this->message->getDateCreate()->toUserTime()->getTimestamp(),
			'EMAIL_TEMPLATE' => $this->message->getEmailTemplate(),
			'NOTIFY_TYPE' => $this->message->getNotifyType(),
			'NOTIFY_MODULE' => $this->message->getNotifyModule(),
			'NOTIFY_EVENT' => $this->message->getNotifyEvent(),
			'NOTIFY_TAG' => $this->message->getNotifyTag(),
			'NOTIFY_SUB_TAG' => $this->message->getNotifySubTag(),
			'NOTIFY_TITLE' => $this->message->getNotifyTitle(),
			'NOTIFY_BUTTONS' => $this->message->getNotifyButtons(),
			'NOTIFY_READ' => $this->message->isNotifyRead(),
			'IMPORT_ID' => $this->message->getImportId(),
			'PARAMS' => $this->message->getParams()->toRestFormat(),
			'MESSAGE_TYPE' => $this->chat->getType(),
			'CHAT_AUTHOR_ID'=> $this->chat->getAuthorId(),
			'CHAT_ENTITY_TYPE' => $this->chat->getEntityType(),
			'CHAT_ENTITY_ID' => $this->chat->getEntityId(),
			'CHAT_PARENT_ID' => $this->chat->getParentChatId(),
			'CHAT_PARENT_MID' => $this->chat->getParentMessageId(),
			'CHAT_ENTITY_DATA_1' => $this->chat->getEntityData1(),
			'CHAT_ENTITY_DATA_2' => $this->chat->getEntityData2(),
			'CHAT_ENTITY_DATA_3' => $this->chat->getEntityData3(),
			'DATE_MODIFY' => new DateTime(),
		];

		if ($this->chat instanceof Chat\PrivateChat)
		{
			$authorId = $this->message->getAuthorId();
			$message['FROM_USER_ID'] = $authorId;
			$message['TO_USER_ID'] = $this->chat->getCompanion($authorId)->getId() ?: $authorId;
		}
		else
		{
			$message['BOT_IN_CHAT'] = $this->chat->getBotInChat();
		}

		$this->messageForEvent = $message;

		return $this->messageForEvent;
	}

	private function getLastViewers(int $userId): array
	{
		$this->lastMessageViewers ??= $this->chat->getLastMessageViewsByGroups();

		if (isset($this->lastMessageViewers['USERS'][$userId]))
		{
			return Common::toJson($this->lastMessageViewers['FOR_VIEWERS'] ?? []);
		}

		return Common::toJson($this->lastMessageViewers['FOR_NOT_VIEWERS'] ?? []);
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
			->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
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
			->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
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

	public function setContext(?Context $context): self
	{
		$this->message->setContext($context);
		$this->chat->setContext($context);

		return $this->defaultSetContext($context);
	}
}
