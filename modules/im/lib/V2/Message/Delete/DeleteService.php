<?php

namespace Bitrix\Im\V2\Message\Delete;

use Bitrix\Disk\SystemUser;
use Bitrix\Im\Common;
use Bitrix\Im\Model\MessageIndexTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Analytics\MessageAnalytics;
use Bitrix\Im\V2\Analytics\MessageContent;
use Bitrix\Im\V2\Anchor\DI\AnchorContainer;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Pull\Event;
use ReflectionNamedType;

class DeleteService
{
	use ContextCustomer
	{
		setContext as private defaultSetContext;
	}

	public const EVENT_AFTER_MESSAGE_DELETE = 'OnAfterMessagesDelete';
	public const OPTION_KEY_DELETE_AFTER = 'complete_delete_message_start_date';
	private MessageCollection $messages;
	private array $messagesForEvent = [];
	private array $messagesDeletionModes = [];
	private bool $byEvent = false;
	private Chat $chat;
	private bool $isModeSpecified = false;
	private bool $needUpdateRecent = false;
	private array $counters;
	private array $lastMessageViewers;
	private bool $isPermissionFilled = false;
	private \WeakMap $deletionTypeMap;
	private \WeakMap $sortedMessagesByMode;

	public function __construct(MessageCollection $messages)
	{
		$this->setMessages($messages);
		$this->initializeDefaultValues();
	}

	protected function initializeDefaultValues(): self
	{
		$this->deletionTypeMap = new \WeakMap();
		foreach (MessageType::cases() as $type)
		{
			$this->deletionTypeMap[$type] = DeletionMode::None;
		}

		$this->sortedMessagesByMode = new \WeakMap();
		foreach (DeletionMode::cases() as $case)
		{
			$this->sortedMessagesByMode[$case] = [];
		}

		return $this;
	}

	public static function getInstanceByMessage(Message $message): self
	{
		$collection = new MessageCollection();
		$collection->add($message);

		return (new self($collection));
	}

	protected function setMessages(MessageCollection $messages): self
	{
		$this->messages = $messages;
		$chatId = $this->messages->getCommonChatId() ?? 0;
		Chat::cleanCache($chatId);
		$this->chat = Chat::getInstance($chatId);

		return $this;
	}

	/**
	 * @param ?DeletionMode $mode
	 * @return $this
	 */
	public function setMode(?DeletionMode $mode = null): self
	{
		if (!isset($mode) || ($mode === DeletionMode::None))
		{
			$this->isModeSpecified = false;
			return $this;
		}

		$this->isModeSpecified = true;
		$this->setDeletionMode(MessageType::OwnMessageRead, $mode)
			->setDeletionMode(MessageType::OwnMessageUnread, $mode)
			->setDeletionMode(MessageType::OtherMessage, $mode)
		;

		return $this;
	}

	public function canDelete(int $messageId): DeletionMode
	{
		if (!isset($this->messages[$messageId]))
		{
			return DeletionMode::None;
		}

		if (!$this->isModeSpecified && !$this->isPermissionFilled)
		{
			$this->fillPermissions();
		}

		$messageType = $this->defineMessageType($this->messages[$messageId]);
		return $this->getDeletionMode($messageType);
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
		if ($this->chat instanceof Chat\NullChat)
		{
			return new Result();
		}

		if (!$this->isModeSpecified)
		{
			if (!$this->chat->checkAccess($this->getContext()->getUserId())->isSuccess())
			{
				return (new Result())->addError(new Message\MessageError(Message\MessageError::ACCESS_DENIED));
			}

			$this->fillPermissions();
		}

		$chatLastMessageId = $this->chat->getLastMessageId();
		$this->messages->fillFiles();
		$this->assignToGroups();
		$this->fillMessagesForEvent();

		foreach (DeletionMode::cases() as $deletionMode)
		{
			Message\Delete\Strategy\DeletionStrategy::getInstance(
				$this->getMessageCollectionByMode($deletionMode),
				$deletionMode
			)
				->setContext($this->getContext())
				->delete()
			;
		}

		$this->needUpdateRecent = $this->chat->getLastMessageId() !== $chatLastMessageId;
		$this->onAfterDelete();

		return new Result();
	}

	private function onAfterDelete(): void
	{
		$ids = $this->sortedMessagesByMode[DeletionMode::None] ?? [];
		$this->clearMessagesByIds($ids);

		$this->fireEventAfterMessagesDelete();

		$this->sendPullMessage();

		if (Option::get('im', 'message_history_index'))
		{
			MessageIndexTable::deleteByFilter(['=MESSAGE_ID' => $this->messages->getPrimaryIds()]);
		}

		(new UrlService())->deleteUrlsByMessages($this->messages);

		$this->deleteAnchors();

		$this->sendAnalyticsData();
	}

	private function clearMessagesByIds(array $ids): void
	{
		$this->messages->unsetByKeys($ids);
		foreach ($ids as $id)
		{
			if (isset($this->messagesForEvent[$id]))
			{
				unset($this->messagesForEvent[$id]);
			}
		}
	}

	private function isCompleteDelete(int $messageId): bool
	{
		/**
		 * @var DeletionMode $mode
		 */
		$mode = $this->messagesDeletionModes[$messageId];

		return $mode === DeletionMode::Complete;
		}

	/**
	 * Set deletion mode for one of the message type
	 * @param MessageType $messageType
	 * @param DeletionMode $mode
	 * @return $this
	 */
	protected function setDeletionMode(MessageType $messageType, DeletionMode $mode): self
	{
		$this->deletionTypeMap[$messageType] = $mode;
		return $this;
	}

	/**
	 * Get deletion mode for one of the message type
	 * @param MessageType $messageType
	 * @return DeletionMode
	 */
	protected function getDeletionMode(MessageType $messageType): DeletionMode
	{
		return $this->deletionTypeMap[$messageType] ?? DeletionMode::None;
	}

	protected function assignToGroups(): void
	{
		foreach ($this->messages as $message)
		{
			if ($message->getId() === null)
			{
				continue;
			}

			$messageType = $this->defineMessageType($message);
			$mode = $this->getDeletionMode($messageType);
			$this->sortedMessagesByMode[$mode][] = $message->getId();
			$this->messagesDeletionModes[$message->getId()] = $mode;
		}
	}

	/**
	 * @param DeletionMode $mode
	 * @return MessageCollection
	 */
	protected function getMessageCollectionByMode(DeletionMode $mode = DeletionMode::None): MessageCollection
	{
		$ids = $this->sortedMessagesByMode[$mode] ?? [];
		$collection = $this->messages->filter(
			fn (Message $message) => in_array($message->getId(), $ids, true)
		);

		return $collection;
	}

	protected function defineMessageType(Message $message): MessageType
	{
		if ($this->isOwnMessage($message))
		{
			return $message->isViewedByOthers() ? MessageType::OwnMessageRead : MessageType::OwnMessageUnread;
		}

		return MessageType::OtherMessage;
	}

	private function fillPermissions(): void
	{
		if ($this->isPermissionFilled)
		{
			return;
		}
		$this->isPermissionFilled = true;

		// case of deletion, if user is SuperAdmin
		if ($this->getContext()->getUser()->isSuperAdmin())
		{
			$this->setDeletionMode(MessageType::OwnMessageUnread, DeletionMode::Complete)
				->setDeletionMode(MessageType::OwnMessageRead, DeletionMode::Complete)
				->setDeletionMode(MessageType::OtherMessage, DeletionMode::Complete)
			;

			return;
		}

		// case of deletion in OpenLineChat
		if ($this->chat instanceof Chat\OpenLineChat && Loader::includeModule('imopenlines'))
		{
			$this->fillPermissionsForOpenLine();

			return;
		}

		// case of deletion other users messages
		if ($this->chat->canDo(Action::DeleteOthersMessage))
		{
			$deletionModeOtherMessage = $this->chat instanceof Chat\CommentChat ? DeletionMode::Soft : DeletionMode::Complete;
			$this->setDeletionMode(MessageType::OtherMessage, $deletionModeOtherMessage);
		}

		// case of deletion own messages(read/unread) in ChannelChat and GeneralChat
		if ($this->chat instanceof Chat\ChannelChat || $this->chat instanceof Chat\GeneralChat)
		{
			if ($this->chat->canDo(Action::Send))
			{
				$this->setDeletionMode(MessageType::OwnMessageUnread, DeletionMode::Complete)
					->setDeletionMode(MessageType::OwnMessageRead, DeletionMode::Complete)
				;
			}

			return;
		}

		// if viewed by others -> "complete"(CommentChat - "soft")
		// if not viewed by others -> "soft"
		$deletionModeSelfMessage = $this->chat instanceof Chat\CommentChat ? DeletionMode::Soft : DeletionMode::Complete;
		$this->setDeletionMode(MessageType::OwnMessageRead, DeletionMode::Soft)
			->setDeletionMode(MessageType::OwnMessageUnread, $deletionModeSelfMessage)
		;
	}

	protected function fillPermissionsForOpenLine(): void
	{
		if (!($this->chat instanceof Chat\OpenLineChat))
		{
			return;
		}

		if ($this->getContext()->getUser()->isBot())
		{
			$this->setDeletionMode(MessageType::OwnMessageUnread, DeletionMode::Complete)
				->setDeletionMode(MessageType::OwnMessageRead, DeletionMode::Complete)
				->setDeletionMode(MessageType::OtherMessage, DeletionMode::Complete)
			;

			return;
		}

		if ($this->chat->canDeleteOwnMessage())
		{
			$this->setDeletionMode(MessageType::OwnMessageUnread, DeletionMode::Soft)
				->setDeletionMode(MessageType::OwnMessageRead, DeletionMode::Soft)
			;
		}

		if (
			$this->chat->canDeleteMessage()
			&& $this->chat->canDo(Action::DeleteOthersMessage)
		)
		{
			$this->setDeletionMode(MessageType::OtherMessage, DeletionMode::Soft);
		}
	}

	protected function isOwnMessage(Message $message): bool
	{
		return
			$this->getContext()->getUserId() === $message->getAuthorId()
			&& !$message->isSystem()
		;
	}

	private function getMessageOut(Message $message): string
	{
		$date = $message->getDateCreate()?->toString();

		return Loc::getMessage('IM_MESSAGE_DELETED_OUT', ['#DATE#' => $date]) ?? '';
	}

	protected function sendPullOpenLineMessages(): Result
	{
		if (!($this->chat instanceof Chat\OpenLineChat || $this->chat instanceof Chat\OpenLineLiveChat))
		{
			return new Result;
		}

		$relations = $this->chat->getRelations()->getUserIds();

		foreach ($this->messages as $message)
		{
			$pullMessage = $this->getFormatOpenLinePullMessage($message);
			\Bitrix\Pull\Event::add($relations, $pullMessage);

			$pullMessage['extra']['is_shared_event'] = true;
			if ($this->chat->needToSendPublicPull())
			{
				\CPullWatch::AddToStack('IM_PUBLIC_' . $this->chat->getChatId(), $pullMessage);
			}
		}

		return new Result;
	}

	private function sendPullMessage(): Result
	{
		if ($this->chat instanceof Chat\OpenLineChat || $this->chat instanceof Chat\OpenLineLiveChat)
		{
			return $this->sendPullOpenLineMessages();
		}

		$pullMessage = $this->getFormatPullMessage();

		if ($this->chat instanceof Chat\PrivateChat)
		{
			$userId = $this->chat->getAuthorId();
			$companionUserId = $this->chat->getCompanion($userId)->getId();
			$this->sendPullMessagePrivate($userId, $companionUserId, $pullMessage);
			$this->sendPullMessagePrivate($companionUserId, $userId, $pullMessage);
		}
		else
		{
			$groupedPullMessage = $this->groupPullByCounter($pullMessage);
			foreach ($groupedPullMessage as $pullForGroup)
			{
				Event::add($pullForGroup['users'], $pullForGroup['event']);
			}

			$pullMessage['extra']['is_shared_event'] = true;
			$pullMessage['params']['recentConfig']['sections'] = $this->chat->getRecentSectionsForGuest();

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

	private function sendPullMessagePrivate(int $fromUser, int $toUser, array $pullMessage): void
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
		if ($this->needUpdateRecent)
		{
			$pullMessage['params']['lastMessageViews'] = $this->getLastViewers($toUser);
		}
		Event::add($toUser, $pullMessage);
	}

	private function getFormatMessageForPull(Message $message, bool $completeDelete): array
	{
		return [
			'id' => (int)$message->getId(),
			'text' => Loc::getMessage('IM_MESSAGE_DELETED'),
			'senderId' => $message->getAuthorId(),
			'params' => ['IS_DELETED' => 'Y', 'URL_ID' => [], 'FILE_ID' => [], 'KEYBOARD' => 'N', 'ATTACH' => []],
			'completelyDeleted' => $completeDelete,
		];
	}

	private function getFormatOpenLinePullMessage(Message $message): array
	{
		$params = [
			'id' => (int)$message->getId(),
			'type' => 'chat',
			'text' => Loc::getMessage('IM_MESSAGE_DELETED'),
			'senderId' => $message->getAuthorId(),
			'params' => ['IS_DELETED' => 'Y', 'URL_ID' => [], 'FILE_ID' => [], 'KEYBOARD' => 'N', 'ATTACH' => []],
			'chatId' => $this->chat->getChatId(),
			'dialogId' => $this->chat->getDialogId(),
		];
		$isComplete = $this->isCompleteDelete((int)$message->getId());

		return [
			'module_id' => 'im',
			'command' => $isComplete ? 'messageDeleteComplete' : 'messageDelete',
			'params' => $params,
			'push' => $isComplete ? ['badge' => 'Y'] : [],
			'extra' => Common::getPullExtra()
		];
	}

	public function getFormatPullMessage(): array
	{
		$params = [
			'messages' => [],
			'chatId' => $this->chat->getChatId(),
			'type' => $this->chat->getType() === Chat::IM_TYPE_PRIVATE ? 'private' : 'chat',
			'unread' => false,
			'muted' => false,
			'counter' => 0,
			'counterType' => $this->chat->getCounterType()->value,
			'recentConfig' => $this->chat->getRecentConfig()->toPullFormat(),
		];

		foreach ($this->messages as $message)
		{
			$isCompleteDelete = $this->isCompleteDelete($message->getId());
			$params['messages'][] = $this->getFormatMessageForPull($message, $isCompleteDelete);
		}

		if (!$this->chat instanceof Chat\PrivateChat)
		{
			$params['dialogId'] = $this->chat->getDialogId();
		}

		$chatLastMessageId = $this->chat->getLastMessageId();
		if ($this->needUpdateRecent && isset($chatLastMessageId))
		{
			if ($chatLastMessageId !== 0)
			{
				$newLastMessage = new Message($chatLastMessageId);
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
			'command' => 'messageDeleteV2',
			'params' => $params,
			'push' => ['badge' => 'Y'],
			'extra' => Common::getPullExtra(),
		];
	}

	private function groupPullByCounter(array $pullMessage): array
	{
		$events = [];
		/** @var Relation $relation */
		$relations = $this->chat->getRelations();
		$unreadList = Recent::getUnread($this->chat->getType(), $this->chat->getDialogId());
		$messageId = $this->messages->getAny()?->getId() ?? 0;

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
			if ($this->needUpdateRecent)
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
				. '_'. $messageId
				. '_'. $events[$userId]['params']['counter']
				. '_'. $count
				. '_'. $unreadGroupFlag
				. '_'. $mutedGroupFlag
			;
		}

		return Message\Send\PushService::getEventByCounterGroup($events);
	}

	private function fireEventAfterMessagesDelete(): Result
	{
		$result = new Result;
		foreach ($this->messages as $message)
		{
			$id = $message->getMessageId();
			if (!isset($id, $this->messagesForEvent[$id]))
			{
				continue;
			}

			$messageForEvent = $this->messagesForEvent[$id];

			\Bitrix\Im\Bot::onMessageDelete(
				$id,
				$messageForEvent
			);

			$deleteFlags = [
				'ID' => $id,
				'USER_ID' => $this->getContext()->getUserId(),
				'COMPLETE_DELETE' => $this->isCompleteDelete($id),
				'BY_EVENT' => $this->byEvent,
			];

			foreach(GetModuleEvents('im', self::EVENT_AFTER_MESSAGE_DELETE, true) as $event)
			{
				ExecuteModuleEventEx($event, [$id, $messageForEvent, $deleteFlags]);
			}
		}

		return $result;
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

		$result['file'] = ['type' => $file->getContentType(), 'name' => $file->getDiskFile()?->getName()];

		return $result;
	}

	private function fillMessagesForEvent(): void
	{
		foreach ($this->messages as $message)
		{
			$id = $message->getId();
			if (isset($id))
			{
				$this->messagesForEvent[$id] = $this->getMessageForEvent($message);
			}
		}
	}

	private function getMessageForEvent(Message $message): array
	{
		$messageForEvent = [
			'ID' => $message->getId(),
			'CHAT_ID' => $message->getChatId(),
			'AUTHOR_ID' => $message->getAuthorId(),
			'MESSAGE' => $this->getMessageOut($message),
			'MESSAGE_OUT' => $message->getMessageOut(),
			'DATE_CREATE' => $message->getDateCreate()?->toUserTime()->getTimestamp(),
			'EMAIL_TEMPLATE' => $message->getEmailTemplate(),
			'NOTIFY_TYPE' => $message->getNotifyType(),
			'NOTIFY_MODULE' => $message->getNotifyModule(),
			'NOTIFY_EVENT' => $message->getNotifyEvent(),
			'NOTIFY_TAG' => $message->getNotifyTag(),
			'NOTIFY_SUB_TAG' => $message->getNotifySubTag(),
			'NOTIFY_TITLE' => $message->getNotifyTitle(),
			'NOTIFY_BUTTONS' => $message->getNotifyButtons(),
			'NOTIFY_READ' => $message->isNotifyRead(),
			'IMPORT_ID' => $message->getImportId(),
			'PARAMS' => $message->getParams()->toRestFormat(),
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
			$authorId = $message->getAuthorId();
			$messageForEvent['FROM_USER_ID'] = $authorId;
			$messageForEvent['TO_USER_ID'] = $this->chat->getCompanion($authorId)->getId() ?: $authorId;
		}
		else
		{
			$messageForEvent['BOT_IN_CHAT'] = $this->chat->getBotInChat();
		}

		return $messageForEvent;
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

	public function setContext(?Context $context): self
	{
		$this->messages->setContext($context);
		$this->chat->setContext($context);

		return $this->defaultSetContext($context);
	}

	private function sendAnalyticsData(): void
	{
		foreach ($this->messages as $message)
		{
			$messageType = $this->getMessageComponentName($message->getId());
			(new MessageAnalytics($message))->addDeleteMessage($messageType);
		}
	}
	private function getMessageComponentName(int $id): string
	{
		if (!isset($this->messages[$id]))
		{
			return '';
		}
		$this->messages->fillParams();

		return (new MessageContent($this->messages[$id]))->getComponentName();
	}

	private function deleteAnchors(): void
	{
		$readService = AnchorContainer::getInstance()->getReadService()->setContext($this->getContext());

		$readService->readByMessageIds($this->messages->getIds());
	}
}
