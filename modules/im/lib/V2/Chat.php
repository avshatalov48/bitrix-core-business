<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Im;
use Bitrix\Im\User;
use Bitrix\Im\Color;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\EO_Chat;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\Message\Params;
use CGlobalCounter;
use CIMNotify;
use CPushManager;

/**
 * Chat version #2
 */
abstract class Chat implements RegistryEntry, ActiveRecord
{
	use ContextCustomer
	{
		setContext as private defaultSaveContext;
	}
	use RegistryEntryImplementation;
	use ActiveRecordImplementation
	{
		save as defaultSave;
	}

	public const
		IM_TYPE_PRIVATE = 'P',
		IM_TYPE_CHAT = 'C',
		IM_TYPE_COMMENT = 'T',
		IM_TYPE_OPEN_LINE = 'L',
		IM_TYPE_SYSTEM = 'S',
		IM_TYPE_CHANNEL = 'N',
		IM_TYPE_OPEN = 'O'
	;

	public const IM_TYPES = [
		self::IM_TYPE_PRIVATE,
		self::IM_TYPE_CHAT,
		self::IM_TYPE_COMMENT,
		self::IM_TYPE_OPEN_LINE,
		self::IM_TYPE_SYSTEM,
		self::IM_TYPE_CHANNEL,
		self::IM_TYPE_OPEN,
	];

	public const IM_TYPES_TRANSLATE = [
		'PRIVATE' => self::IM_TYPE_PRIVATE,
		'CHAT' => self::IM_TYPE_CHAT,
		'COMMENT' => self::IM_TYPE_COMMENT,
		'OPENLINE' => self::IM_TYPE_OPEN_LINE,
		'SYSTEM' => self::IM_TYPE_SYSTEM,
		'NOTIFY' => self::IM_TYPE_SYSTEM,
		'CHANNEL' => self::IM_TYPE_CHANNEL,
		'OPEN' => self::IM_TYPE_OPEN,
	];

	// Default entity types
	public const
		ENTITY_TYPE_VIDEOCONF = 'VIDEOCONF',
		ENTITY_TYPE_GENERAL = 'GENERAL',
		ENTITY_TYPE_FAVORITE = 'FAVORITE'
	;

	//OPENLINES
	public const
		ENTITY_TYPE_LINE = 'LINES', //OPERATOR
		ENTITY_TYPE_LIVECHAT = 'LIVECHAT'; //USER

	protected const ENTITY_TYPES = [
		self::ENTITY_TYPE_LINE,
		self::ENTITY_TYPE_LIVECHAT,
		self::ENTITY_TYPE_FAVORITE,
	];

	public const AVAILABLE_PARAMS = [
		'type',
		'entityType',
		'entityId',
		'entityData1',
		'entityData2',
		'entityData3',
		'title',
		'description',
		'searchable',
		'color',
		'ownerId',
		'users',
		'managers',
		'manageUsers',
		'manageUI',
		'manageSettings',
		'avatar'
	];

	public const
		MANAGE_RIGHTS_ALL = 'ALL',
		MANAGE_RIGHTS_OWNER = 'OWNER',
		MANAGE_RIGHTS_MANAGERS = 'MANAGER'
	;

	public const ROLE_OWNER = 'OWNER';
	public const ROLE_MANAGER = 'MANAGER';
	public const ROLE_MEMBER = 'MEMBER';
	public const ROLE_GUEST = 'GUEST';

	/**
	 * @var static[]
	 */
	protected static array $chatStaticCache = [];

	protected array $accessCache = [];

	protected ?int $chatId = null;

	/**
	 * Dialog Id:
	 * 		chatNNN - chat,
	 * 		sgNNN - socnet group,
	 * 		crmNNN - crm chat,
	 * 		NNN - recipient user.
	 */
	protected ?string $dialogId = null;

	/**
	 * Message type:
	 * 	@see \IM_MESSAGE_SYSTEM = S - notification,
	 * 	@see \IM_MESSAGE_PRIVATE = P - private chat,
	 * 	@see \IM_MESSAGE_CHAT = S - group chat,
	 * 	@see \IM_MESSAGE_OPEN = O - open chat,
	 * 	@see \IM_MESSAGE_OPEN_LINE = L - open line chat.
	 */
	protected ?string $type = null;

	protected ?int $authorId = null;

	protected ?string $title = null;

	protected ?string $description = null;

	protected ?string $color = null;

	protected int $parentChatId = 0;

	protected int $parentMessageId = 0;

	protected ?bool $extranet = null;

	protected ?int $avatarId = null;

	protected ?int $pinMessageId = null;

	protected ?int $callType = null;

	protected ?string $callNumber = null;

	protected ?string $entityType = null;

	protected ?string $entityId = null;

	protected ?string $entityData1 = null;

	protected ?string $entityData2 = null;

	/** Keeps only one flag - Silent mode flag for Open Lines (Y|N). */
	protected ?string $entityData3 = null;

	protected ?int $diskFolderId = null;

	protected int $messageCount = 0;

	protected int $userCount = 0;

	protected int $prevMessageId = 0;

	protected int $lastMessageId = 0;

	protected ?string $lastMessageStatus = null;

	protected ?DateTime $dateCreate = null;

	protected ?string $manageUsers = null;

	protected ?string $manageUI = null;

	protected ?string $manageSettings = null;

	protected ?array $usersIds = null;

	/** @var Registry<Message>  */
	protected Registry $messageRegistry;

	/**
	 * @var array<RelationCollection>
	 */
	protected ?array $relations = null;

	protected ?ReadService $readService = null;

	/**
	 * @param int|array|EO_Chat|null $source
	 */
	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}

		$this->messageRegistry = new Registry;
	}

	//region Users
	//endregion

	//region Relations
	//endregion

	/**
	 * @param int|null $chatId
	 * @return static
	 */
	public static function getInstance(?int $chatId): self
	{
		if (!isset($chatId))
		{
			return new Im\V2\Chat\NullChat();
		}

		if (isset(self::$chatStaticCache[$chatId]))
		{
			return self::$chatStaticCache[$chatId];
		}

		$chat = ChatFactory::getInstance()->getChatById($chatId);

		if ($chat instanceof Im\V2\Chat\NullChat)
		{
			return $chat;
		}

		self::$chatStaticCache[$chatId] = $chat;

		return self::$chatStaticCache[$chatId];
	}

	public static function cleanCache(int $id): void
	{
		unset(self::$chatStaticCache[$id]);

		ChatFactory::getInstance()->cleanCache($id);
	}

	public static function cleanAccessCache(int $chatId): void
	{
		if (isset(self::$chatStaticCache[$chatId]))
		{
			self::$chatStaticCache[$chatId]->accessCache = [];
		}
	}

	public function save(): Result
	{
		$id = $this->getChatId();
		$result = $this->defaultSave();

		if (!$result->isSuccess())
		{
			return $result;
		}

		if ($id !== null)
		{
			self::cleanCache($id);
		}

		return $result;
	}

	public function getStartId(?int $userId = null): int
	{
		return RelationCollection::getStartId($userId ?? $this->getContext()->getUserId(), $this->getChatId());
	}

	public function isExist(): bool
	{
		return isset($this->chatId);
	}

	public function add(array $params): Result
	{
		return new Result();
	}

	protected function checkIsExtranet(): bool
	{
		if (
			!count($this->usersIds ?? [])
			|| in_array($this->entityType, [self::ENTITY_TYPE_LINE, self::ENTITY_TYPE_LIVECHAT])
			|| in_array($this->type, [self::IM_TYPE_OPEN_LINE])
		)
		{
			return false;
		}

		$userIds = \CIMContactList::PrepareUserIds($this->usersIds);
		$users = \CIMContactList::GetUserData([
			'ID' => array_values($userIds),
			'DEPARTMENT' => 'N',
			'USE_CACHE' => 'N'
		]);
		foreach ($users['users'] as $user)
		{
			if ($user['extranet'])
			{
				return true;
			}
		}

		return false;
	}

	protected function setUserIds(?array $userIds): self
	{
		if (is_array($userIds) && count($userIds))
		{
			$userIds = filter_var(
				$userIds,
				FILTER_VALIDATE_INT,
				[
					'flags' => FILTER_REQUIRE_ARRAY,
					'options' => ['min_range' => 1]
				]
			);

			foreach ($userIds as $key => $userId)
			{
				if (!is_int($userId))
				{
					unset($userIds[$key]);
				}
			}
		}

		$this->usersIds = array_unique($userIds);

		return $this;
	}

	public function getUserIds(): ?array
	{
		return $this->usersIds;
	}

	public function checkColor(): Result
	{
		if (!Color::isSafeColor($this->color))
		{
			CGlobalCounter::Increment('im_chat_color_id', CGlobalCounter::ALL_SITES, false);
			$chatColorId = CGlobalCounter::GetValue('im_chat_color_id', CGlobalCounter::ALL_SITES);
			$this->color = Color::getCodeByNumber($chatColorId);
		}

		return new Result();
	}

	//region Access & Permissions

	/**
	 * @param int|User|null $user
	 * @return bool
	 */
	public function hasAccess($user = null): bool
	{
		$userId = $this->getUserId($user);

		if (isset($this->accessCache[$userId]))
		{
			return $this->accessCache[$userId];
		}

		if (!$userId || !$this->getChatId())
		{
			$this->accessCache[$userId] = false;

			return false;
		}

		$this->accessCache[$userId] = $this->checkAccessWithoutCaching($userId);

		return $this->accessCache[$userId];
	}

	protected function checkAccessWithoutCaching(int $userId): bool
	{
		return false;
	}

	protected function getUserId($user): int
	{
		$userId = 0;
		if ($user === null)
		{
			$userId = $this->getContext()->getUserId();
		}
		elseif (is_numeric($user))
		{
			$userId = (int)$user;
		}
		elseif ($user instanceof User)
		{
			$userId = $user->getId();
		}

		return $userId;
	}


	//endregion

	//region Message

	/**
	 * @return Registry<Message>
	 */
	public function getMessageRegistry(): Registry
	{
		return $this->messageRegistry;
	}

	/**
	 * @param int $messageId
	 * @return Message|null
	 */
	public function getMessage(int $messageId): ?Message
	{
		if (isset($this->messageRegistry[$messageId]))
		{
			return $this->messageRegistry[$messageId];
		}

		$message = new Message;
		$message->setRegistry($this->messageRegistry);

		$loadResult = $message->load($messageId);
		if ($loadResult->isSuccess())
		{
			return $message;
		}

		return null;
	}

	/**
	 * Provides message sending process.
	 *
	 * @param Message|string|array $message
	 * @param SendingConfig|array|null $sendingConfig
	 * @return Result
	 */
	abstract public function sendMessage($message, $sendingConfig = null): Result;

	/**
	 * @param Message $message
	 * @return Result
	 */
	public function updateMessage(Message $message): Result
	{
		$message->setRegistry($this->messageRegistry);

		$result = new Result;

		//todo: updating process here

		return $result;
	}

	/**
	 * @param Message $message
	 * @return Result
	 */
	public function deleteMessage(Message $message): Result
	{
		//todo: drop process here
		$result = new Result;

		return $result;
	}

	/**
	 * @param Message $message
	 * @return void
	 */
	public function riseInRecent(Message $message): void
	{
		/** @var Relation $relation */
		foreach ($this->getRelations() as $relation)
		{
			if (!User::getInstance($relation->getUserId())->isActive())
			{
				continue;
			}

			if ($this->getEntityType() == self::ENTITY_TYPE_LINE)
			{
				if (User::getInstance($relation->getUserId())->getExternalAuthId() == 'imconnector')
				{
					continue;
				}
			}

			\CIMContactList::SetRecent([
				'ENTITY_ID' => $this->getChatId(),
				'MESSAGE_ID' => $message->getMessageId(),
				'CHAT_TYPE' => $this->getType(),
				'USER_ID' => $relation->getUserId(),
				'CHAT_ID' => $relation->getChatId(),
				'RELATION_ID' => $relation->getId(),
			]);

			if ($relation->getUserId() == $message->getAuthorId())
			{
				$relation
					->setLastId($message->getMessageId())
					->save();
			}
		}
	}

	public static function readAllChats(int $userId): Result
	{
		$readService = new ReadService($userId);
		$readService->readAll();

		Im\Recent::readAll($userId);

		if (Main\Loader::includeModule('pull'))
		{
			\Bitrix\Pull\Event::add($userId, [
				'module_id' => 'im',
				'command' => 'readAllChats',
				'extra' => Im\Common::getPullExtra()
			]);
		}

		return new Result();
	}

	public function read(bool $onlyRecent = false, bool $byEvent = false): Result
	{
		Im\Recent::unread($this->getDialogId(), false, $this->getContext()->getUserId());

		if ($onlyRecent)
		{
			$lastId = $this->getReadService()->getLastMessageIdInChat($this->chatId);

			return (new Result())->setResult([
				'CHAT_ID' => $this->chatId,
				'LAST_ID' => $lastId,
				'COUNTER' => $this->getReadService()->getCounterService()->getByChat($this->chatId),
				'VIEWED_MESSAGES' => [],
			]);
		}

		return $this->readAllMessages($byEvent);
	}

	public function readAllMessages(bool $byEvent = false): Result
	{
		return $this->readMessages(null, $byEvent);
	}

	public function readMessages(?MessageCollection $messages, bool $byEvent = false): Result
	{
		$result = new Result();

		if (isset($messages))
		{
			$messages = $messages->filterByChatId($this->chatId);

			if ($messages->count() === 0)
			{
				return $result->addError(new MessageError(MessageError::MESSAGE_NOT_FOUND));
			}
		}

		$readService = $this->getReadService();
		$startId = $readService->getLastIdByChatId($this->chatId);
		$counter = 0;

		if (isset($messages))
		{
			$counter = $readService->read($messages, $this)->getResult()['COUNTER'];
		}
		else
		{
			$counter = $readService->readAllInChat($this->chatId)->getResult()['COUNTER'];
		}

		$lastId = $readService->getLastIdByChatId($this->chatId);

		$messages ??= new MessageCollection();

		$notOwnMessages = new MessageCollection();

		foreach ($messages as $message)
		{
			if ($message->getAuthorId() !== $this->getContext()->getUserId())
			{
				$notOwnMessages->add($message);
			}
		}

		if (Main\Loader::includeModule('pull'))
		{
			CIMNotify::DeleteBySubTag("IM_MESS_{$this->getChatId()}_{$this->getContext()->getUserId()}", false, false);
			CPushManager::DeleteFromQueueBySubTag($this->getContext()->getUserId(), 'IM_MESS');
			$this->sendPushRead($notOwnMessages, $lastId, $counter);
		}

		$this->sendEventRead($startId, $lastId, $counter, $byEvent);

		return $result->setResult([
			'CHAT_ID' => $this->chatId,
			'LAST_ID' => $lastId,
			'COUNTER' => $counter,
			'VIEWED_MESSAGES' => $notOwnMessages->getIds(),
		]);
	}

	public function unreadToMessage(Message $message): Result
	{
		$result = new Result();

		if ($message->getChatId() !== $this->chatId)
		{
			return $result->addError(new MessageError(MessageError::MESSAGE_NOT_FOUND));
		}

		$readService = $this->getReadService();
		$lastId = $readService->getLastMessageIdInChat($this->chatId);
		$counter = $readService->unreadTo($message)->getResult()['COUNTER'];
		$lastMessageIds = $this->getLastMessages($lastId, $message->getMessageId());
		$lastMessageStatuses = $this->getReadService()->getViewedService()->getMessageStatuses($lastMessageIds);

		/*if (Main\Loader::includeModule('pull'))
		{
			$this->sendPushUnreadSelf($message->getMessageId(), $lastId, $counter, $lastMessageStatuses);
			$this->sendPushUnreadOpponent($lastMessageStatuses[$lastId] ?? \IM_MESSAGE_STATUS_RECEIVED, $lastId, $lastMessageStatuses);
		}*/

		return $result->setResult([
			'CHAT_ID' => $this->chatId,
			'LAST_ID' => $lastId,
			'COUNTER' => $counter,
			'UNREAD_TO' => $message->getId(),
			'LAST_MESSAGE_STATUSES' => $lastMessageStatuses,
		]);
	}


	protected function sendPushRead(MessageCollection $messages, int $lastId, int $counter): void
	{
		if ($this->getType() === self::ENTITY_TYPE_LIVECHAT || !$this->getContext()->getUser()->isConnector())
		{
			$this->sendPushReadSelf($messages, $lastId, $counter);
		}
		$this->sendPushReadOpponent($messages, $lastId);
	}

	protected function sendPushReadSelf(MessageCollection $messages, int $lastId, int $counter): void
	{
		$selfRelation = $this
			->getRelations(['SELECT' => ['ID', 'CHAT_ID', 'USER_ID', 'NOTIFY_BLOCK']])
			->getByUserId($this->getContext()->getUserId(), $this->chatId)
		;
		$muted = isset($selfRelation) ? $selfRelation->getNotifyBlock() : false;
		\Bitrix\Pull\Event::add($this->getContext()->getUserId(), [
			'module_id' => 'im',
			'command' => 'readMessageChat',
			'params' => [
				'dialogId' => $this->getDialogId(),
				'chatId' => $this->getChatId(),
				'lastId' => $lastId,
				'counter' => $counter,
				'muted' => $muted ?? false,
				'unread' => Im\Recent::isUnread($this->getContext()->getUserId(), $this->getType(), $this->getDialogId()),
				'lines' => $this->getType() === IM_MESSAGE_OPEN_LINE,
				'viewedMessages' => $messages->getIds(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	protected function sendPushReadOpponent(MessageCollection $messages, int $lastId): array
	{
		$pushMessage = [
			'module_id' => 'im',
			'command' => 'readMessageChatOpponent',
			'expiry' => 600,
			'params' => [
				'dialogId' => $this->getDialogId(),
				'chatId' => $this->chatId,
				'userId' => $this->getContext()->getUserId(),
				'userName' => $this->getContext()->getUser()->getName(),
				'lastId' => $lastId,
				'date' => (new DateTime())->format('c'),
				'viewedMessages' => $messages->getIds(),
				'chatMessageStatus' => $this->getReadService()->getChatMessageStatus($this->chatId),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
		\Bitrix\Pull\Event::add($this->getUsersForPush(), $pushMessage);

		return $pushMessage;
	}

	protected function sendEventRead(int $startId, int $endId, int $counter, bool $byEvent): void
	{
		foreach (\GetModuleEvents("im", "OnAfterChatRead", true) as $arEvent)
		{
			\ExecuteModuleEventEx($arEvent, array(Array(
				'CHAT_ID' => $this->chatId,
				'CHAT_ENTITY_TYPE' => $this->getEntityType(),
				'CHAT_ENTITY_ID' => $this->getEntityId(),
				'START_ID' => $startId,
				'END_ID' => $endId,
				'COUNT' => $counter,
				'USER_ID' => $this->getContext()->getUserId(),
				'BY_EVENT' => $byEvent
			)));
		}
	}

	protected function sendPushUnreadSelf(int $unreadToId, int $lastId, int $counter, ?array $lastMessageStatuses): void
	{
		$selfRelation = $this->getSelfRelation();
		$muted = isset($selfRelation) ? $selfRelation->getNotifyBlock() : false;
		\Bitrix\Pull\Event::add($this->getContext()->getUserId(), [
			'module_id' => 'im',
			'command' => 'unreadMessageChat',
			'params' => [
				'dialogId' => $this->getDialogId(),
				'chatId' => $this->chatId,
				'lastId' => $lastId,
				'date' => new DateTime(),
				'counter' => $counter,
				'muted' => $muted ?? false,
				'unread' => Im\Recent::isUnread($this->getContext()->getUserId(), $this->getType(), $this->getDialogId()),
				'lines' => $this->getType() === IM_MESSAGE_OPEN_LINE,
				'unreadToId' => $unreadToId,
				'lastMessageStatuses' => $lastMessageStatuses ?? [],
				'lastMessageViews' => Im\Common::toJson($this->getLastMessageViews()),
			],
			'push' => ['badge' => 'Y'],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	protected function sendPushUnreadOpponent(string $chatMessageStatus, int $unreadTo, ?array $lastMessageStatuses): void
	{
		$pushMessage = [
			'module_id' => 'im',
			'command' => 'unreadMessageChatOpponent',
			'expiry' => 600,
			'params' => [
				'dialogId' => $this->getDialogId(),
				'chatId' => $this->chatId,
				'userId' => $this->getContext()->getUserId(),
				'chatMessageStatus' => $chatMessageStatus,
				'unreadTo' => $unreadTo,
				'lastMessageStatuses' => $lastMessageStatuses ?? [],
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		$viewsByGroups = $this->getLastMessageViewsByGroups();

		foreach ($viewsByGroups as $view)
		{
			$pushMessage['params']['lastMessageViews'] = Im\Common::toJson($view['VIEW_INFO']);
			$usersForPush = $this->getUsersForPush();
			$recipient = array_intersect($usersForPush, $view['USERS']);
			\Bitrix\Pull\Event::add($recipient, $pushMessage);
		}
	}

	public function getLastMessages(int $upperBound, int $lowerBound): array
	{
		$lastMessagesRaw = Im\Model\MessageTable::query()
			->setSelect(['ID'])
			->where('ID', '>=', $lowerBound)
			->where('ID', '<=', $upperBound)
			->where('CHAT_ID', $this->chatId)
			->setOrder(['ID' => 'DESC'])
			->setLimit(50)
			->fetchAll()
		;
		$lastMessageIds = [];
		foreach ($lastMessagesRaw as $row)
		{
			$lastMessageIds[] = (int)$row['ID'];
		}

		return $lastMessageIds;
	}


	public function getLastMessageViews(): array
	{
		$lastMessageViewsByGroups = $this->getLastMessageViewsByGroups();

		foreach ($lastMessageViewsByGroups as $lastMessageViews)
		{
			if (isset($lastMessageViews['USERS'][$this->getContext()->getUserId()]))
			{
				return $lastMessageViews['VIEW_INFO'];
			}
		}

		return [];
	}

	public function getLastMessageViewsByGroups(): array
	{
		$readService = $this->getReadService();

		$lastMessageInChat = $readService->getLastMessageIdInChat($this->chatId);

		if ($lastMessageInChat === 0)
		{
			return [];
		}

		$usersInChat = $this->getRelations(['SELECT' => ['ID', 'CHAT_ID', 'USER_ID', 'NOTIFY_BLOCK']])->getUserIds();
		$messageViewers = $readService->getViewedService()->getMessageViewersIds($lastMessageInChat);
		$unviewedMessageUsers = array_diff($usersInChat, $messageViewers);
		$countOfView = count($messageViewers);

		$firstViewers = [];

		foreach ($messageViewers as $messageViewer)
		{
			if (count($firstViewers) >= 2)
			{
				break;
			}

			$firstViewers[$messageViewer] = $messageViewer;
		}

		$datesOfViews = $readService->getViewedService()->getDateViewedByMessageIdForEachUser($lastMessageInChat, $firstViewers);

		$firstViewersWithDate = [];

		foreach ($firstViewers as $viewer)
		{
			$firstViewersWithDate[] = [
				'USER_ID' => $viewer,
				'USER_NAME' => Im\V2\Entity\User\User::getInstance($viewer)->getName(),
				'DATE' => $datesOfViews[$viewer] ?? null
			];
		}

		$viewsInfoByGroups = [];
		$countWithoutSelf = $countOfView - 1;

		if (!empty($messageViewers))
		{
			$viewsInfoByGroups[$countWithoutSelf] = [
				'USERS' => $messageViewers,
				'VIEW_INFO' => [
					'MESSAGE_ID' => $lastMessageInChat,
					'FIRST_VIEWERS' => $firstViewersWithDate,
					'COUNT_OF_VIEWERS' => $countOfView - 1,
				],
			];
		}
		if (!empty($unviewedMessageUsers))
		{
			$viewsInfoByGroups[$countOfView] = [
				'USERS' => $unviewedMessageUsers,
				'VIEW_INFO' => [
					'MESSAGE_ID' => $lastMessageInChat,
					'FIRST_VIEWERS' => $firstViewersWithDate,
					'COUNT_OF_VIEWERS' => $countOfView,
				],
			];
		}

		return $viewsInfoByGroups;
	}

	protected function getUsersForPush(): array
	{
		$userId = $this->getContext()->getUserId();
		$isLineChat = $this->getEntityType() === self::ENTITY_TYPE_LINE;
		$relations = $this->getRelations(['SELECT' => ['ID', 'CHAT_ID', 'USER_ID', 'NOTIFY_BLOCK']]);
		$userIds = [];
		foreach ($relations as $relation)
		{
			$isUserSelf = $relation->getUserId() === $userId;
			$isUserConnector = $isLineChat && $relation->getUser()->isConnector();
			if ($isUserSelf || $isUserConnector)
			{
				continue;
			}
			$userIds[] = $relation->getUserId();
		}

		return $userIds;
	}

	//endregion

	//region Data storage

	/**
	 * @return array<array>
	 */
	protected static function mirrorDataEntityFields(): array
	{
		return [
			'ID' => [
				'primary' => true,
				'field' => 'chatId',
				'set' => 'setChatId', /** @see Chat::setChatId */
				'get' => 'getChatId', /** @see Chat::getChatId */
			],
			'TYPE' => [
				'field' => 'type',
				'set' => 'setType', /** @see Chat::setType */
				'get' => 'getType', /** @see Chat::getType */
				'default' => 'getDefaultType', /** @see Chat::getDefaultType */
				'beforeSave' => 'beforeSaveType', /** @see Chat::beforeSaveType */
			],
			'AUTHOR_ID' => [
				'field' => 'authorId',
				'set' => 'setAuthorId', /** @see Chat::setAuthorId */
				'get' => 'getAuthorId', /** @see Chat::getAuthorId */
			],
			'COLOR' => [
				'field' => 'color',
				'get' => 'getColor', /** @see Chat::getColor */
				'set' => 'setColor', /** @see Chat::setColor */
				'beforeSave' => 'checkColor', /** @see Chat::checkColor */
				// 'beforeSave' => 'validateColor', /** @see Chat::validateColor */
				//'default' => 'getDefaultColor', /** @see Chat::getDefaultColor */
			],
			'TITLE' => [
				'field' => 'title',
				'set' => 'setTitle', /** @see Chat::setTitle */
				'get' => 'getTitle',  /** @see Chat::getTitle */
				'beforeSave' => 'checkTitle', /** @see Chat::checkTitle */
				//'default' => 'getDefaultTitle', /** @see Chat::getDefaultTitle */
			],
			'DESCRIPTION' => [
				'field' => 'description',
				'get' => 'getDescription',  /** @see Chat::getDescription */
				'set' => 'setDescription',  /** @see Chat::setDescription */
			],
			'PARENT_ID' => [
				'field' => 'parentId',
				'get' => 'getParentId',  /** @see Chat::getParentChatId */
				'set' => 'setParentId',  /** @see Chat::setParentChatId */
			],
			'PARENT_MID' => [
				'field' => 'parentMid',
				'get' => 'getParentMessageId',  /** @see Chat::getParentMessageId */
				'set' => 'setParentMessageId',  /** @see Chat::setParentMessageId */
			],
			'EXTRANET' => [
				'field' => 'extranet',
				'get' => 'getExtranet',  /** @see Chat::getExtranet */
				'set' => 'setExtranet',  /** @see Chat::setExtranet */
				'default' => 'getDefaultExtranet', /** @see Chat::getDefaultExtranet */
			],
			'AVATAR' => [
				'field' => 'avatarId',
				'get' => 'getAvatarId',  /** @see Chat::getAvatarId */
				'set' => 'setAvatarId',  /** @see Chat::setAvatarId */
			],
			'PIN_MESSAGE_ID' => [
				'field' => 'pinMessageId',
				'get' => 'getPinMessageId',  /** @see Chat::getPinMessageId */
				'set' => 'setPinMessageId',  /** @see Chat::setPinMessageId */
			],
			'CALL_TYPE' => [
				'field' => 'callType',
				'get' => 'getCallType',  /** @see Chat::getCallType */
				'set' => 'setCallType',  /** @see Chat::setCallType */
			],
			'CALL_NUMBER' => [
				'field' => 'callNumber',
				'get' => 'getCallNumber',  /** @see Chat::getCallNumber */
				'set' => 'setCallNumber',  /** @see Chat::setCallNumber */
			],
			'ENTITY_TYPE' => [
				'field' => 'entityType',
				'get' => 'getEntityType',  /** @see Chat::getEntityType */
				'set' => 'setEntityType',  /** @see Chat::setEntityType */
				'default' => 'getDefaultEntityType', /** @see Chat::getDefaultEntityType */
			],
			'ENTITY_ID' => [
				'field' => 'entityId',
				'get' => 'getEntityId',  /** @see Chat::getEntityId */
				'set' => 'setEntityId',  /** @see Chat::setEntityId */
			],
			'ENTITY_DATA_1' => [
				'field' => 'entityData1',
				'get' => 'getEntityData1',  /** @see Chat::getEntityData1 */
				'set' => 'setEntityData1',  /** @see Chat::setEntityData1 */
			],
			'ENTITY_DATA_2' => [
				'field' => 'entityData2',
				'get' => 'getEntityData2',  /** @see Chat::getEntityData2 */
				'set' => 'setEntityData2',  /** @see Chat::setEntityData2 */
			],
			'ENTITY_DATA_3' => [
				'field' => 'entityData3',
				'get' => 'getEntityData3',  /** @see Chat::getEntityData3 */
				'set' => 'setEntityData3',  /** @see Chat::setEntityData3 */
			],
			'DISK_FOLDER_ID' => [
				'field' => 'diskFolderId',
				'get' => 'getDiskFolderId',  /** @see Chat::getDiskFolderId */
				'set' => 'setDiskFolderId',  /** @see Chat::setDiskFolderId */
			],
			'MESSAGE_COUNT' => [
				'field' => 'messageCount',
				'get' => 'getMessageCount',  /** @see Chat::getMessageCount */
				'set' => 'setMessageCount',  /** @see Chat::setMessageCount */
			],
			'USER_COUNT' => [
				'field' => 'userCount',
				'get' => 'getUserCount',  /** @see Chat::getUserCount */
				'set' => 'setUserCount',  /** @see Chat::setUserCount */
			],
			'PREV_MESSAGE_ID' => [
				'field' => 'prevMessageId',
				'get' => 'getPrevMessageId',  /** @see Chat::getPrevMessageId */
				'set' => 'setPrevMessageId',  /** @see Chat::setPrevMessageId */
			],
			'LAST_MESSAGE_ID' => [
				'field' => 'lastMessageId',
				'get' => 'getLastMessageId',  /** @see Chat::getLastMessageId */
				'set' => 'setLastMessageId',  /** @see Chat::setLastMessageId */
			],
			'LAST_MESSAGE_STATUS' => [
				'field' => 'lastMessageStatus',
				'get' => 'getLastMessageStatus',  /** @see Chat::getLastMessageStatus */
				'set' => 'setLastMessageStatus',  /** @see Chat::setLastMessageStatus */
				'default' => 'getDefaultLastMessageStatus', /** @see Chat::getDefaultLastMessageStatus */
			],
			'DATE_CREATE' => [
				'field' => 'dateCreate',
				'get' => 'getDateCreate',  /** @see Chat::getDateCreate */
				'set' => 'setDateCreate',  /** @see Chat::setDateCreate */
				'default' => 'getDefaultDateCreate', /** @see Chat::getDefaultDateCreate */
			],
			'MANAGE_USERS' => [
				'field' => 'manageUsers',
				'get' => 'getManageUsers',  /** @see Chat::getManageUsers */
				'set' => 'setManageUsers',  /** @see Chat::setManageUsers */
				'default' => 'getDefaultManageUsers', /** @see Chat::getDefaultManageUsers */
			],
			'MANAGE_UI' => [
				'field' => 'manageUI',
				'get' => 'getManageUI',  /** @see Chat::getManageUI */
				'set' => 'setManageUI',  /** @see Chat::setManageUI */
				'default' => 'getDefaultManageUI', /** @see Chat::getDefaultManageUI */
			],
			'MANAGE_SETTINGS' => [
				'field' => 'manageSettings',
				'get' => 'getManageSettings',  /** @see Chat::getManageSettings */
				'set' => 'setManageSettings',  /** @see Chat::setManageSettings */
				'default' => 'getDefaultManageSettings', /** @see Chat::getDefaultManageSettings */
			],
			'USERS' => [
				'get' => 'getUserIds',  /** @see Chat::getUserIds */
				'set' => 'setUserIds',  /** @see Chat::setUserIds */
			]
		];
	}

	/**
	 * @return string|DataManager;
	 */
	public static function getDataClass(): string
	{
		return ChatTable::class;
	}

	/**
	 * @return int|null
	 */
	public function getPrimaryId(): ?int
	{
		return $this->getChatId();
	}

	/**
	 * @param int $primaryId
	 * @return self
	 */
	public function setPrimaryId(int $primaryId): self
	{
		return $this->setChatId($primaryId);
	}

	//endregion

	//region Search

	/**
	 * Looks for chat by its parameters.
	 *
	 * @param array $params
	 * @param Context|null $context
	 * @return Result
	 */
	public static function find(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		if ($params['CHAT_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARAMETER));
		}

		$connection = \Bitrix\Main\Application::getConnection();

		$context = $context ?? Locator::getContext();

		if ($context->getUserId() == 0)
		{
			$res = $connection->query("
				SELECT
					C.ID CHAT_ID,
					C.PARENT_ID CHAT_PARENT_ID,
					C.PARENT_MID CHAT_PARENT_MID,
					C.TITLE CHAT_TITLE,
					C.AUTHOR_ID CHAT_AUTHOR_ID,
					C.TYPE CHAT_TYPE,
					C.AVATAR CHAT_AVATAR,
					C.COLOR CHAT_COLOR,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE,
					C.ENTITY_ID CHAT_ENTITY_ID,
					C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
					C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
					C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
					C.EXTRANET CHAT_EXTRANET,
					C.PREV_MESSAGE_ID CHAT_PREV_MESSAGE_ID,
					'1' RID,
					'Y' IS_MANAGER
				FROM b_im_chat C
				WHERE C.ID = ".(int)$params['CHAT_ID']."
			");
		}
		else
		{
			if (empty($params['FROM_USER_ID']))
			{
				$params['FROM_USER_ID'] = $context->getUserId();
			}

			$params['FROM_USER_ID'] = (int)$params['FROM_USER_ID'];
			if ($params['FROM_USER_ID'] <= 0)
			{
				return $result->addError(new ChatError(ChatError::WRONG_SENDER));
			}

			$res = $connection->query("
				SELECT
					C.ID CHAT_ID,
					C.PARENT_ID CHAT_PARENT_ID,
					C.PARENT_MID CHAT_PARENT_MID,
					C.TITLE CHAT_TITLE,
					C.AUTHOR_ID CHAT_AUTHOR_ID,
					C.TYPE CHAT_TYPE,
					C.AVATAR CHAT_AVATAR,
					C.COLOR CHAT_COLOR,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE,
					C.ENTITY_ID CHAT_ENTITY_ID,
					C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
					C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
					C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
					C.EXTRANET CHAT_EXTRANET,
					C.PREV_MESSAGE_ID CHAT_PREV_MESSAGE_ID,
					R.USER_ID RID,
					R.MANAGER IS_MANAGER
				FROM b_im_chat C
				LEFT JOIN b_im_relation R 
					ON R.CHAT_ID = C.ID 
					AND R.USER_ID = ".$params['FROM_USER_ID']."
				WHERE C.ID = ".(int)$params['CHAT_ID']."
			");
		}

		if ($row = $res->fetch())
		{
			$result->setResult([
				'ID' => (int)$row['CHAT_ID'],
				'TYPE' => $row['CHAT_TYPE'],
				'ENTITY_TYPE' => $row['CHAT_ENTITY_TYPE'],
				/*'RELATIONS' => [
					(int)$row['RID'] => [
						'CHAT_ID' => (int)$row['CHAT_ID'],
						'USER_ID' => (int)$row['RID'],
						'IS_MANAGER' => $row['IS_MANAGER'],
					]
				]*/
			]);
		}

		return $result;
	}

	//endregion


	//region Setters & Getters

	protected function setChatId(int $chatId): self
	{
		if (!$this->chatId)
		{
			$this->chatId = $chatId;
		}
		return $this;
	}

	public function getChatId(): ?int
	{
		return $this->chatId;
	}

	/**
	 * @param string $dialogId
	 * @return self
	 */
	public function setDialogId(string $dialogId): self
	{
		$this->dialogId = $dialogId;

		if (\Bitrix\Im\Common::isChatId($dialogId))
		{
			$this->setChatId((int)\Bitrix\Im\Dialog::getChatId($dialogId));
			if (!$this->getType())
			{
				$this->setType(self::IM_TYPE_CHAT);
			}
		}
		else
		{
			if (!$this->getType())
			{
				$this->setType(self::IM_TYPE_PRIVATE);
			}
		}

		return $this;
	}

	/**
	 * Allows to send mention notification.
	 * @return bool
	 */
	abstract public function allowMention(): bool;

	public function getDialogId(): ?string
	{
		if ($this->dialogId || !$this->getChatId())
		{
			return $this->dialogId;
		}

		$this->dialogId = 'chat'. $this->getChatId();

		return $this->dialogId;
	}

	public function getDialogContextId(): ?string
	{
		return $this->getDialogId();
	}

	/**
	 * @param string $type
	 * @return self
	 */
	public function setType(string $type): self
	{
		if (!in_array($type, self::IM_TYPES))
		{
			if (in_array($type, array_keys(self::IM_TYPES_TRANSLATE), true))
			{
				$type = self::IM_TYPES_TRANSLATE[$type];
			}
			else
			{
				$type = $this->getDefaultType();
			}
		}

		$this->type = $type;
		return $this;
	}

	public function getType(): string
	{
		if (!$this->type)
		{
			$this->type = $this->getDefaultType();
		}

		return $this->type;
	}

	abstract protected function getDefaultType(): string;

	protected function beforeSaveType(): Result
	{
		$check = new Result;

		if (!in_array($this->type, self::IM_TYPES, true))
		{
			$check->addError(new ChatError(ChatError::WRONG_TYPE,'Wrong chat type'));
		}

		return $check;
	}

	// Author
	public function setAuthorId(int $authorId): self
	{
		$this->authorId = $authorId;
		return $this;
	}

	public function getAuthorId(): ?int
	{
		return $this->authorId;
	}

	public function getAuthor(): Entity\User\User
	{
		return Im\V2\Entity\User\User::getInstance($this->getAuthorId());
	}

	// Chat title
	public function setTitle(?string $title): self
	{
		$this->title = $title ? mb_substr(trim($title), 0, 255) : null;
		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	// Chat description
	public function setDescription(?string $description): self
	{
		$this->description = $description ? trim($description) : null;
		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	// Chat color
	public function setColor(?string $color): self
	{
		$this->color = $color ? trim($color) : null;
		return $this;
	}

	public function getColor(): ?string
	{
		return $this->color;
	}

	public function validateColor(): Result
	{
		$check = new Result;
		if (!Color::isSafeColor($this->color))
		{
			$check->addError(new ChatError(ChatError::WRONG_COLOR,'Wrong chat color'));
		}
		return $check;
	}

	public function getDefaultColor(): string
	{
		$color = '';

		return $color;
	}

	// parent chat
	public function setParentChatId(int $parentChatId): self
	{
		$this->parentChatId = $parentChatId > 0 ? $parentChatId : 0;
		return $this;
	}

	public function getParentChatId(): ?int
	{
		return $this->parentChatId;
	}

	// parent message
	public function setParentMessageId(int $messageId): self
	{
		$this->parentMessageId = $messageId > 0 ? $messageId : 0;
		return $this;
	}

	public function getParentMessageId(): int
	{
		return $this->parentMessageId;
	}

	// extranet
	public function setExtranet(?bool $extranet): self
	{
		$this->extranet = is_bool($extranet) ? $extranet : null;
		return $this;
	}

	public function getExtranet(): ?bool
	{
		return $this->extranet;
	}

	public function getDefaultExtranet(): bool
	{
		return false;
	}

	// avatar's file Id
	public function setAvatarId(?int $avatarId): self
	{
		$this->avatarId = is_integer($avatarId) ? $avatarId : null;
		return $this;
	}

	public function getAvatarId(): ?int
	{
		return $this->avatarId;
	}

	// pined message Id
	public function setPinMessageId(?int $pinMessageId): self
	{
		$this->pinMessageId = is_integer($pinMessageId) ? $pinMessageId : null;
		return $this;
	}

	public function getPinMessageId(): ?int
	{
		return $this->pinMessageId;
	}

	// callType
	public function setCallType(?int $callType): self
	{
		$this->callType = is_integer($callType) ? $callType : null;
		return $this;
	}

	public function getCallType(): ?int
	{
		return $this->callType;
	}

	// callNumber
	public function setCallNumber(?string $callNumber): self
	{
		$this->callNumber = $callNumber ? trim($callNumber) : null;
		return $this;
	}

	public function getCallNumber(): ?string
	{
		return $this->callNumber;
	}

	// entity Type
	public function setEntityType(?string $entityType): self
	{
		$this->entityType = $entityType ? trim($entityType) : null;
		return $this;
	}

	public function getEntityType(): ?string
	{
		if ($this->entityType)
		{
			return $this->entityType;
		}

		return $this->getDefaultEntityType();
	}

	protected function getDefaultEntityType(): ?string
	{
		return null;
	}

	// entity Id
	public function setEntityId(?string $entityId): self
	{
		$this->entityId = $entityId ? trim($entityId) : null;
		return $this;
	}

	public function getEntityId(): ?string
	{
		return $this->entityId;
	}

	// entity Data1
	public function setEntityData1(?string $entityData1): self
	{
		$this->entityData1 = $entityData1 ? trim($entityData1) : null;
		return $this;
	}

	public function getEntityData1(): ?string
	{
		return $this->entityData1;
	}

	// entity Data2
	public function setEntityData2(?string $entityData2): self
	{
		$this->entityData2 = $entityData2 ? trim($entityData2) : null;
		return $this;
	}

	public function getEntityData2(): ?string
	{
		return $this->entityData2;
	}

	// entityData3
	public function setEntityData3(?string $entityData3): self
	{
		$this->entityData3 = $entityData3 ? trim($entityData3) : null;
		return $this;
	}

	public function getEntityData3(): ?string
	{
		return $this->entityData3;
	}

	// disk Folder Id
	public function setDiskFolderId(?int $diskFolderId): self
	{
		$this->diskFolderId = is_integer($diskFolderId) ? $diskFolderId : null;
		return $this;
	}

	public function getDiskFolderId(): ?int
	{
		return $this->diskFolderId;
	}

	// message Count
	public function setMessageCount(int $messageCount): self
	{
		$this->messageCount = $messageCount > 0 ? $messageCount : 0;
		return $this;
	}

	public function getMessageCount(): int
	{
		return $this->messageCount;
	}

	/**
	 * Increments chat's message counter.
	 *
	 * @param int $increment
	 * @return self
	 */
	public function incrementMessageCount(int $increment = 1): self
	{
		$this->setMessageCount($this->getMessageCount() + $increment);

		if ($this->getChatId())
		{
			ChatTable::update($this->getChatId(), [
				'MESSAGE_COUNT' => new Main\DB\SqlExpression('?# + ' . $increment, 'MESSAGE_COUNT'),
				'LAST_MESSAGE_ID' => $this->getLastMessageId(),
			]);
		}

		return $this;
	}

	/**
	 * Update chat's parent message counter.
	 *
	 * @return self
	 */
	public function updateParentMessageCount(): self
	{
		if (
			$this->getChatId()
			&& $this->getParentMessageId()
			&& $this->getMessageCount()
		)
		{
			$message = new Message($this->getParentMessageId());
			$message->getParams()
				->fill([
					Params::CHAT_MESSAGE => $this->getMessageCount(),
					Params::CHAT_LAST_DATE => new DateTime()
				])
				->save();

			\CIMMessageParam::SendPull($this->getParentMessageId(), [Params::CHAT_MESSAGE, Params::CHAT_LAST_DATE]);
		}

		return $this;
	}

	// user Count
	public function setUserCount(int $userCount): self
	{
		$this->userCount = $userCount > 0 ? $userCount : 0;
		return $this;
	}

	public function getUserCount(): int
	{
		return $this->userCount;
	}

	// prev Message Id
	public function setPrevMessageId(int $prevMessageId): self
	{
		$this->prevMessageId = $prevMessageId > 0 ? $prevMessageId : 0;
		return $this;
	}

	public function getPrevMessageId(): int
	{
		return $this->prevMessageId;
	}

	// last Message Id
	public function setLastMessageId(int $lastMessageId): self
	{
		$this->lastMessageId = $lastMessageId > 0 ? $lastMessageId : 0;
		return $this;
	}

	public function getLastMessageId(): int
	{
		return $this->lastMessageId;
	}

	// last Message Status
	public function setLastMessageStatus(?string $lastMessageStatus): self
	{
		$this->lastMessageStatus = $lastMessageStatus ? trim($lastMessageStatus) : null;
		return $this;
	}

	public function getLastMessageStatus(): ?string
	{
		return $this->lastMessageStatus;
	}

	public function getDefaultLastMessageStatus(): string
	{
		return \IM_MESSAGE_STATUS_RECEIVED;
	}

	// Create date
	public function setDateCreate(?DateTime $dateCreate): self
	{
		$this->dateCreate = $dateCreate ? $dateCreate : null;
		return $this;
	}

	public function getDateCreate(): ?DateTime
	{
		return $this->dateCreate;
	}

	public function getDefaultDateCreate(): DateTime
	{
		return new DateTime;
	}

	protected function getReadService(): ReadService
	{
		if ($this->readService === null)
		{
			$this->readService = new ReadService();
			$this->readService->setContext($this->context);
		}

		return $this->readService;
	}

	/**
	 * @param array $options
	 * @return RelationCollection<Relation>
	 */
	public function getRelations(array $options = []): RelationCollection
	{
		$optionsHash = md5(serialize($options));

		if (isset($this->relations[$optionsHash]))
		{
			return $this->relations[$optionsHash];
		}

		$filter = $options['FILTER'] ?? [];
		$filter['CHAT_ID'] = $this->getChatId();

		$relations = RelationCollection::find(
			$filter,
			[],
			$options['LIMIT'] ?? null,
			$this->context,
			$options['SELECT'] ?? RelationCollection::COMMON_FIELDS
		);

		$this->relations[$optionsHash] = $relations;

		return $this->relations[$optionsHash];
	}

	public function getSelfRelation(): ?Relation
	{
		$userId = $this->getContext()->getUserId();

		return $this->getRelations(['FILTER' => ['USER_ID' => $userId]])->getByUserId($userId, $this->getChatId());
	}

	public function checkTitle(): Result
	{
		return new Result;
	}

	/**
	 * @param string $manageUsers ALL|OWNER|MANAGERS
	 * @return self
	 */
	public function setManageUsers(string $manageUsers): self
	{
		if (!in_array(
			$manageUsers,
			[self::MANAGE_RIGHTS_ALL, self::MANAGE_RIGHTS_OWNER, self::MANAGE_RIGHTS_MANAGERS],
			true
		))
		{
			$manageUsers = $this->getDefaultManageUsers();
		}
		$this->manageUsers = $manageUsers ;

		return $this;
	}

	public function getManageUsers(): ?string
	{
		return $this->manageUsers;
	}


	public function getDefaultManageUsers(): string
	{
		return self::MANAGE_RIGHTS_ALL;
	}

	/**
	 * @param string $manageUI ALL|OWNER|MANAGERS
	 * @return self
	 */
	public function setManageUI(string $manageUI): self
	{
		if (!in_array(
			$manageUI,
			[self::MANAGE_RIGHTS_ALL, self::MANAGE_RIGHTS_OWNER, self::MANAGE_RIGHTS_MANAGERS],
			true
		))
		{
			$manageUI = $this->getDefaultManageUI();
		}
		$this->manageUI = $manageUI;

		return $this;
	}

	public function getManageUI(): ?string
	{
		return $this->manageUI;
	}

	public function getDefaultManageUI(): string
	{
		return self::MANAGE_RIGHTS_ALL;
	}

	/**
	 * @param string $manageSettings OWNER|MANAGERS
	 * @return self
	 */
	public function setManageSettings(string $manageSettings): self
	{
		if (!in_array($manageSettings, [self::MANAGE_RIGHTS_OWNER, self::MANAGE_RIGHTS_MANAGERS], true))
		{
			$manageSettings = $this->getDefaultManageSettings();
		}
		$this->manageSettings = $manageSettings ;

		return $this;
	}

	public function getManageSettings(): ?string
	{
		return $this->manageSettings;
	}

	public function getDefaultManageSettings(): string
	{
		return self::MANAGE_RIGHTS_OWNER;
	}
	//endregion

	public function createChatIfNotExists(array $params): self
	{
		return $this;
	}

	/**
	 * @param array $userIds
	 * @return self
	 */
	public function addUsers(array $userIds): self
	{
		if (!$this->getChatId() || empty($userIds) || !count($userIds))
		{
			return $this;
		}

		$userIds = filter_var(
			$userIds,
			FILTER_VALIDATE_INT,
			[
				'flags' => FILTER_REQUIRE_ARRAY,
				'options' => ['min_range' => 1],
			]
		);

		foreach ($userIds as $key => $userId)
		{
			if (!is_int($userId))
			{
				unset($userIds[$key]);
			}
		}

		$relations = $this->getRelations([
			'CHAT_ID' => $this->getChatId(),
		]);

		foreach ($relations as $relation)
		{
			if (in_array($relation->getUserId(), $userIds, true))
			{
				$userIds = array_flip($userIds);
				unset($userIds[$relation->getUserId()]);
				$userIds = array_flip($userIds);
			}
		}

		$insertedCount = 0;
		foreach ($userIds as $userId)
		{
			$insertedId = RelationTable::add([
				'CHAT_ID' => $this->getChatId(),
				'MESSAGE_TYPE' => $this->getType(),
				'USER_ID' => $userId,
				'STATUS' => \IM_STATUS_READ,
			]);

			if ($insertedId)
			{
				$insertedCount++;
			}
		}

		if ($insertedCount)
		{
			$this
				->setUserCount($relations->count() + $insertedCount)
				->save();
		}

		return $this;
	}

	public function removeUsers(array $userIds): self
	{
		if (!$this->getChatId() || empty($userIds) || !count($userIds))
		{
			return $this;
		}

		$userIds = filter_var(
			$userIds,
			FILTER_VALIDATE_INT,
			[
				'flags' => FILTER_REQUIRE_ARRAY,
				'options' => ['min_range' => 1],
			]
		);

		foreach ($userIds as $key => $userId)
		{
			if (!is_int($userId))
			{
				unset($userIds[$key]);
			}
		}

		$relations = $this->getRelations([
			'CHAT_ID' => $this->getChatId(),
		]);

		$removedCount = RelationTable::deleteBatch([
			['=USER_ID' => $userIds],
			['=CHAT_ID' => $this->getChatId()]
		]);

		if ($removedCount)
		{
			$this
				->setUserCount($relations->count() - $removedCount)
				->save();
		}

		return $this;
	}

	public function setManagers(array $managerIds): self
	{
		if (!$this->getChatId() || empty($managerIds) || !count($managerIds))
		{
			return $this;
		}

		$managerIds = filter_var(
			$managerIds,
			FILTER_VALIDATE_INT,
			[
				'flags' => FILTER_REQUIRE_ARRAY,
				'options' => ['min_range' => 1],
			]
		);

		foreach ($managerIds as $key => $managerId)
		{
			if (!is_int($managerId))
			{
				unset($managerIds[$key]);
			}
		}

		$relations = $this->getRelations([
			'CHAT_ID' => $this->getChatId(),
		]);

		$relationIds = [];
		/** @var Relation $relation */
		foreach ($relations as $relation)
		{
			if (in_array($relation->getUserId(), $managerIds, true))
			{
				$relationIds[] = $relation->getPrimaryId();
			}
		}

		RelationTable::updateMulti(
			$relationIds,
			[
				'MANAGER' => 'Y',
			]
		);

		return $this;
	}

	/**
	 * Lazy load message's context phrases.
	 * @return void
	 */
	public static function loadPhrases(): void
	{
		Loc::loadMessages(__FILE__);
	}

	public function setContext(?Context $context): self
	{
		$this->defaultSaveContext($context);
		$this->getReadService()->setContext($context);

		return $this;
	}

	protected function updateIndex(): self
	{
		if (!$this->getChatId())
		{
			return $this;
		}

		$index = \Bitrix\Im\Internals\ChatIndex::create()
			->setChatId($this->getChatId())
			->setTitle(mb_substr($this->getTitle(), 0, 255));
		;

		$relations = $this->getRelations(['LIMIT' => 100]);

		$users = [];
		foreach ($relations as $relation)
		{
			$users[] = $relation->getUser()->getName();
		}

		$index->setUserList($users);

		\Bitrix\Im\Model\ChatTable::addIndexRecord($index);

		return $this;
	}
}
