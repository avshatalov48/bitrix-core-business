<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\Alias;
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
use Bitrix\Pull\Event;
use CGlobalCounter;
use CIMNotify;
use CPushManager;

/**
 * Chat version #2
 */
abstract class Chat implements RegistryEntry, ActiveRecord, Im\V2\Rest\RestEntity
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
		self::ENTITY_TYPE_VIDEOCONF,
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
		'manageUi',
		'manageSettings',
		'disappearingTime',
		'canPost',
		'avatar',
		'conferencePassword',
	];

	public const
		MANAGE_RIGHTS_NONE = 'NONE',
		MANAGE_RIGHTS_MEMBER = 'MEMBER',
		MANAGE_RIGHTS_OWNER = 'OWNER',
		MANAGE_RIGHTS_MANAGERS = 'MANAGER'
	;

	public const ROLE_OWNER = 'OWNER';
	public const ROLE_MANAGER = 'MANAGER';
	public const ROLE_MEMBER = 'MEMBER';
	public const ROLE_GUEST = 'GUEST';
	public const ROLE_NONE = 'NONE';

	private const CHUNK_SIZE = 1000;
	protected const EXTRANET_CAN_SEE_HISTORY = false;

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
	protected ?int $lastFileId = null;

	protected ?int $markedId = null;
	protected ?string $role = null;

	protected ?string $aliasName = null;

	protected ?string $lastMessageStatus = null;

	protected ?DateTime $dateCreate = null;

	protected ?string $manageUsers = null;

	protected ?string $manageUI = null;

	protected ?string $manageSettings = null;

	protected ?string $canPost = null;

	protected ?array $usersIds = null;

	protected ?int $disappearingTime = null;

	/** @var Registry<Message>  */
	protected Registry $messageRegistry;

	/**
	 * @var array<RelationCollection>
	 */
	protected ?array $relations = null;

	protected ?ReadService $readService = null;

	protected bool $isFilledNonCachedData = false;

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

	public function getAliasName(): ?string
	{
		$this->fillNonCachedData();

		return $this->aliasName;
	}

	public function setAliasName(string $aliasName): self
	{
		$this->aliasName = $aliasName;

		return $this;
	}

	public function prepareAliasToLoad($alias): ?string
	{
		if ($alias === null)
		{
			return null;
		}

		return $alias['ALIAS'] ?? null;
	}

	public function getMarkedId(): int
	{
		if (!isset($this->markedId))
		{
			$this->markedId = Im\Recent::getMarkedId($this->getContext()->getUserId(), $this->getType(), $this->getDialogId());
		}

		return $this->markedId;
	}

	public function getRole(): string
	{
		if (isset($this->role))
		{
			return $this->role;
		}

		if ($this->getContext()->getUserId() === (int)$this->getAuthorId())
		{
			$this->role = self::ROLE_OWNER;

			return $this->role;
		}

		$selfRelation = $this->getSelfRelation();

		if ($selfRelation === null)
		{
			$this->role = self::ROLE_GUEST;
		}
		elseif ($selfRelation->getManager())
		{
			$this->role = self::ROLE_MANAGER;
		}
		else
		{
			$this->role = self::ROLE_MEMBER;
		}

		return $this->role;
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

	/**
	 * @param static[] $chats
	 * @param int|null $userId
	 * @return void
	 */
	public static function fillRole(array $chats, ?int $userId = null): void
	{
		//todo: replace to ChatCollection
		$chatIdsToFill = [];
		$userId ??= Im\V2\Entity\User\User::getCurrent()->getId();

		foreach ($chats as $chat)
		{
			if ($chat->getAuthorId() === $userId)
			{
				$chat->role = self::ROLE_OWNER;
			}
			else
			{
				$id = $chat->getId();
				$chatIdsToFill[$id] = $id;
			}
		}

		if (empty($chatIdsToFill))
		{
			return;
		}

		$result = RelationTable::query()
			->setSelect(['CHAT_ID', 'MANAGER'])
			->where('USER_ID', $userId)
			->whereIn('CHAT_ID', $chatIdsToFill)
			->fetchAll()
		;
		$isManager = [];

		foreach ($result as $row)
		{
			$isManager[(int)$row['CHAT_ID']] = $row['MANAGER'] === 'Y';
		}

		foreach ($chats as $chat)
		{
			if (!isset($chatIdsToFill[$chat->getId()]))
			{
				continue;
			}
			if (!isset($isManager[$chat->getId()]))
			{
				$chat->role = self::ROLE_GUEST;
			}
			elseif ($isManager[$chat->getId()])
			{
				$chat->role = self::ROLE_MANAGER;
			}
			else
			{
				$chat->role = self::ROLE_MEMBER;
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

	public function startRecordVoice(): void
	{
		if (!Main\Loader::includeModule('pull'))
		{
			return;
		}

		$pushFormatter = new Im\V2\Message\PushFormat();
		Event::add($this->getUsersForPush(), $pushFormatter->formatStartRecordVoice($this));
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

		$usersInChat = $this->getRelations()->getUserIds();
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
				'field' => 'chatId', /** @see Chat::$chatId */
				'set' => 'setChatId', /** @see Chat::setChatId */
				'get' => 'getChatId', /** @see Chat::getChatId */
			],
			'TYPE' => [
				'field' => 'type', /** @see Chat::$type */
				'set' => 'setType', /** @see Chat::setType */
				'get' => 'getType', /** @see Chat::getType */
				'default' => 'getDefaultType', /** @see Chat::getDefaultType */
				'beforeSave' => 'beforeSaveType', /** @see Chat::beforeSaveType */
			],
			'AUTHOR_ID' => [
				'field' => 'authorId', /** @see Chat::$authorId */
				'set' => 'setAuthorId', /** @see Chat::setAuthorId */
				'get' => 'getAuthorId', /** @see Chat::getAuthorId */
			],
			'COLOR' => [
				'field' => 'color', /** @see Chat::$color */
				'get' => 'getColor', /** @see Chat::getColor */
				'set' => 'setColor', /** @see Chat::setColor */
				'beforeSave' => 'checkColor', /** @see Chat::checkColor */
				// 'beforeSave' => 'validateColor', /** @see Chat::validateColor */
				//'default' => 'getDefaultColor', /** @see Chat::getDefaultColor */
			],
			'TITLE' => [
				'field' => 'title', /** @see Chat::$title */
				'set' => 'setTitle', /** @see Chat::setTitle */
				'get' => 'getTitle',  /** @see Chat::getTitle */
				'beforeSave' => 'checkTitle', /** @see Chat::checkTitle */
				//'default' => 'getDefaultTitle', /** @see Chat::getDefaultTitle */
			],
			'DESCRIPTION' => [
				'field' => 'description', /** @see Chat::$description */
				'get' => 'getDescription',  /** @see Chat::getDescription */
				'set' => 'setDescription',  /** @see Chat::setDescription */
			],
			'PARENT_ID' => [
				'field' => 'parentChatId', /** @see Chat::$parentChatId */
				'get' => 'getParentId',  /** @see Chat::getParentChatId */
				'set' => 'setParentId',  /** @see Chat::setParentChatId */
			],
			'PARENT_MID' => [
				'field' => 'parentMessageId', /** @see Chat::$parentMessageId */
				'get' => 'getParentMessageId',  /** @see Chat::getParentMessageId */
				'set' => 'setParentMessageId',  /** @see Chat::setParentMessageId */
			],
			'EXTRANET' => [
				'field' => 'extranet', /** @see Chat::$extranet */
				'get' => 'getExtranet',  /** @see Chat::getExtranet */
				'set' => 'setExtranet',  /** @see Chat::setExtranet */
				'default' => 'getDefaultExtranet', /** @see Chat::getDefaultExtranet */
			],
			'AVATAR' => [
				'field' => 'avatarId', /** @see Chat::$avatarId */
				'get' => 'getAvatarId',  /** @see Chat::getAvatarId */
				'set' => 'setAvatarId',  /** @see Chat::setAvatarId */
			],
			'PIN_MESSAGE_ID' => [
				'field' => 'pinMessageId', /** @see Chat::$pinMessageId */
				'get' => 'getPinMessageId',  /** @see Chat::getPinMessageId */
				'set' => 'setPinMessageId',  /** @see Chat::setPinMessageId */
			],
			'CALL_TYPE' => [
				'field' => 'callType', /** @see Chat::$callType */
				'get' => 'getCallType',  /** @see Chat::getCallType */
				'set' => 'setCallType',  /** @see Chat::setCallType */
			],
			'CALL_NUMBER' => [
				'field' => 'callNumber', /** @see Chat::$callNumber */
				'get' => 'getCallNumber',  /** @see Chat::getCallNumber */
				'set' => 'setCallNumber',  /** @see Chat::setCallNumber */
			],
			'ENTITY_TYPE' => [
				'field' => 'entityType', /** @see Chat::$entityType */
				'get' => 'getEntityType',  /** @see Chat::getEntityType */
				'set' => 'setEntityType',  /** @see Chat::setEntityType */
				'default' => 'getDefaultEntityType', /** @see Chat::getDefaultEntityType */
			],
			'ENTITY_ID' => [
				'field' => 'entityId', /** @see Chat::$entityId */
				'get' => 'getEntityId',  /** @see Chat::getEntityId */
				'set' => 'setEntityId',  /** @see Chat::setEntityId */
			],
			'ENTITY_DATA_1' => [
				'field' => 'entityData1', /** @see Chat::$entityData1 */
				'get' => 'getEntityData1',  /** @see Chat::getEntityData1 */
				'set' => 'setEntityData1',  /** @see Chat::setEntityData1 */
			],
			'ENTITY_DATA_2' => [
				'field' => 'entityData2', /** @see Chat::$entityData2 */
				'get' => 'getEntityData2',  /** @see Chat::getEntityData2 */
				'set' => 'setEntityData2',  /** @see Chat::setEntityData2 */
			],
			'ENTITY_DATA_3' => [
				'field' => 'entityData3', /** @see Chat::$entityData3 */
				'get' => 'getEntityData3',  /** @see Chat::getEntityData3 */
				'set' => 'setEntityData3',  /** @see Chat::setEntityData3 */
			],
			'DISK_FOLDER_ID' => [
				'field' => 'diskFolderId', /** @see Chat::$diskFolderId */
				'get' => 'getDiskFolderId',  /** @see Chat::getDiskFolderId */
				'set' => 'setDiskFolderId',  /** @see Chat::setDiskFolderId */
			],
			'MESSAGE_COUNT' => [
				'field' => 'messageCount', /** @see Chat::$messageCount */
				'get' => 'getMessageCount',  /** @see Chat::getMessageCount */
				'set' => 'setMessageCount',  /** @see Chat::setMessageCount */
			],
			'USER_COUNT' => [
				'field' => 'userCount', /** @see Chat::$userCount */
				'get' => 'getUserCount',  /** @see Chat::getUserCount */
				'set' => 'setUserCount',  /** @see Chat::setUserCount */
			],
			'PREV_MESSAGE_ID' => [
				'field' => 'prevMessageId', /** @see Chat::$prevMessageId */
				'get' => 'getPrevMessageId',  /** @see Chat::getPrevMessageId */
				'set' => 'setPrevMessageId',  /** @see Chat::setPrevMessageId */
			],
			'LAST_MESSAGE_ID' => [
				'field' => 'lastMessageId', /** @see Chat::$lastMessageId */
				'get' => 'getLastMessageId',  /** @see Chat::getLastMessageId */
				'set' => 'setLastMessageId',  /** @see Chat::setLastMessageId */
			],
			'LAST_MESSAGE_STATUS' => [
				'field' => 'lastMessageStatus', /** @see Chat::$lastMessageStatus */
				'get' => 'getLastMessageStatus',  /** @see Chat::getLastMessageStatus */
				'set' => 'setLastMessageStatus',  /** @see Chat::setLastMessageStatus */
				'default' => 'getDefaultLastMessageStatus', /** @see Chat::getDefaultLastMessageStatus */
			],
			'DATE_CREATE' => [
				'field' => 'dateCreate', /** @see Chat::$dateCreate */
				'get' => 'getDateCreate',  /** @see Chat::getDateCreate */
				'set' => 'setDateCreate',  /** @see Chat::setDateCreate */
				'default' => 'getDefaultDateCreate', /** @see Chat::getDefaultDateCreate */
			],
			'MANAGE_USERS' => [
				'field' => 'manageUsers', /** @see Chat::$manageUsers */
				'get' => 'getManageUsers',  /** @see Chat::getManageUsers */
				'set' => 'setManageUsers',  /** @see Chat::setManageUsers */
				'default' => 'getDefaultManageUsers', /** @see Chat::getDefaultManageUsers */
			],
			'MANAGE_UI' => [
				'field' => 'manageUI', /** @see Chat::$manageUI */
				'get' => 'getManageUI',  /** @see Chat::getManageUI */
				'set' => 'setManageUI',  /** @see Chat::setManageUI */
				'default' => 'getDefaultManageUI', /** @see Chat::getDefaultManageUI */
			],
			'MANAGE_SETTINGS' => [
				'field' => 'manageSettings', /** @see Chat::$manageSettings */
				'get' => 'getManageSettings',  /** @see Chat::getManageSettings */
				'set' => 'setManageSettings',  /** @see Chat::setManageSettings */
				'default' => 'getDefaultManageSettings', /** @see Chat::getDefaultManageSettings */
			],
			'DISAPPEARING_TIME' => [
				'field' => 'disappearingTime', /** @see Chat::$disappearingTime */
				'get' => 'getDisappearingTime',  /** @see Chat::getDisappearingTime */
				'set' => 'setDisappearingTime',  /** @see Chat::setDisappearingTime */
			],
			'CAN_POST' => [
				'field' => 'canPost', /** @see Chat::$canPost */
				'get' => 'getCanPost',  /** @see Chat::getCanPost */
				'set' => 'setCanPost',  /** @see Chat::setCanPost */
				'default' => 'getDefaultCanPost', /** @see Chat::getDefaultCanPost */
			],
			'USERS' => [
				'get' => 'getUserIds',  /** @see Chat::getUserIds */
				'set' => 'setUserIds',  /** @see Chat::setUserIds */
			],
			'ALIAS' => [
				'field' => 'aliasName',
				'get' => 'getAliasName',  /** @see Chat::getAliasName */
				'set' => 'setAliasName',  /** @see Chat::setAliasName */
				'loadFilter' => 'prepareAliasToLoad', /** @see Chat::prepareAliasToLoad */
				'skipSave' => true,
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
				'ENTITY_ID' => $row['CHAT_ENTITY_ID'],
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

	public function getId(): ?int
	{
		return $this->getChatId();
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

	public function getDisplayedTitle(): ?string
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

	public function getAvatar(int $size = 200, bool $addBlankPicture = false): string
	{
		$url = $addBlankPicture? '/bitrix/js/im/images/blank.gif': '';

		if ((int)$this->getAvatarId() > 0 && $size > 0)
		{
			$arFileTmp = \CFile::ResizeImageGet(
				$this->getAvatarId(),
				['width' => $size, 'height' => $size],
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			if (!empty($arFileTmp['src']))
			{
				$url = $arFileTmp['src'];
			}
		}
		return $url;
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
		$this->fillNonCachedData();

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
		$this->fillNonCachedData();

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
		$this->fillNonCachedData();

		return $this->lastMessageId;
	}

	public function getLastFileId(): int
	{
		$this->lastFileId ??= \CIMDisk::GetMaxFileId($this->getId());

		return $this->lastFileId;
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
	 * @return RelationCollection
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

		$emptyOptionsHash = md5(serialize([]));
		if (isset($this->relations[$emptyOptionsHash]))
		{
			return $this->relations[$emptyOptionsHash]->getByUserId($userId, $this->getChatId());
		}

		return $this->getRelations(['FILTER' => ['USER_ID' => $userId]])->getByUserId($userId, $this->getChatId());
	}

	public function getBotInChat(): array
	{
		$botInChat = [];
		$relations = $this->getRelations();
		foreach ($relations as $relation)
		{
			if ($relation->getUser()->getExternalAuthId() === Im\Bot::EXTERNAL_AUTH_ID)
			{
				$botInChat[$relation->getUserId()] = $relation->getUserId();
			}
		}

		return $botInChat;
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
		$manageUsers = mb_strtoupper($manageUsers);
		if (!in_array(
			$manageUsers,
			[self::MANAGE_RIGHTS_MEMBER, self::MANAGE_RIGHTS_OWNER, self::MANAGE_RIGHTS_MANAGERS],
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
		return self::MANAGE_RIGHTS_MEMBER;
	}

	/**
	 * @param string $manageUI ALL|OWNER|MANAGERS
	 * @return self
	 */
	public function setManageUI(string $manageUI): self
	{
		$manageUI = mb_strtoupper($manageUI);
		if (!in_array(
			$manageUI,
			[self::MANAGE_RIGHTS_MEMBER, self::MANAGE_RIGHTS_OWNER, self::MANAGE_RIGHTS_MANAGERS],
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
		return self::MANAGE_RIGHTS_MEMBER;
	}

	/**
	 * @param string $manageSettings OWNER|MANAGERS
	 * @return self
	 */
	public function setManageSettings(string $manageSettings): self
	{
		$manageSettings = mb_strtoupper($manageSettings);
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

	public function setDisappearingTime(int $disappearingTime): self
	{
		if (is_numeric($disappearingTime) && (int)$disappearingTime > -1)
		{
			$this->disappearingTime = $disappearingTime;
		}

		return $this;
	}

	public function getDisappearingTime(): ?int
	{
		return $this->disappearingTime;
	}

	/**
	 * @param string $canPost ALL|OWNER|MANAGER
	 * @return self
	 */
	public function setCanPost(string $canPost): self
	{
		$canPost = mb_strtoupper($canPost);
		if (!in_array(
			$canPost,
			[
				self::MANAGE_RIGHTS_NONE,
				self::MANAGE_RIGHTS_MEMBER,
				self::MANAGE_RIGHTS_OWNER,
				self::MANAGE_RIGHTS_MANAGERS
			],
			true
		))
		{
			$canPost = $this->getDefaultCanPost();
		}
		$this->canPost = $canPost;

		return $this;
	}

	public function getCanPost(): ?string
	{
		return $this->canPost;
	}

	public function getDefaultCanPost(): string
	{
		return self::MANAGE_RIGHTS_MEMBER;
	}

	public static function getCanPostList(): array
	{
		return [
			self::MANAGE_RIGHTS_NONE => Loc::getMessage('IM_CHAT_CAN_POST_NONE'),
			self::MANAGE_RIGHTS_MEMBER => Loc::getMessage('IM_CHAT_CAN_POST_ALL'),
			self::MANAGE_RIGHTS_OWNER => Loc::getMessage('IM_CHAT_CAN_POST_OWNER'),
			self::MANAGE_RIGHTS_MANAGERS => Loc::getMessage('IM_CHAT_CAN_POST_MANAGERS')
		];
	}
	//endregion

	public function hasPostAccess(?int $userId = null): bool
	{
		$canPost = $this->getCanPost();

		if (!$userId)
		{
			$userId = $this->getContext()->getUserId();
		}

		switch ($canPost)
		{
			case self::MANAGE_RIGHTS_MEMBER:
				return !!$this->getRelations([
					'FILTER' => [
						'USER_ID' => $userId,
					],
					'LIMIT' => 1,
				])->count();
			case self::MANAGE_RIGHTS_MANAGERS:
				return !!$this->getRelations([
					'FILTER' => [
						'USER_ID' => $userId,
						'MANAGER' => 'Y'
					],
					'LIMIT' => 1,
				])->count();
			case self::MANAGE_RIGHTS_OWNER:
				return $userId === $this->getAuthorId();
			default:
				return false;
		}
	}

	public function createChatIfNotExists(array $params): self
	{
		return $this;
	}

	public function join(): self
	{
		return $this->withContextUser(0)->addUsers([$this->getContext()->getUserId()], [], false);
	}

	/**
	 * @param array $userIds
	 * @return self
	 */
	public function addUsers(array $userIds, array $managerIds = [], ?bool $hideHistory = null, bool $withMessage = true, bool $skipRecent = false): self
	{
		if (empty($userIds) || !$this->getChatId())
		{
			return $this;
		}

		$usersToAdd = $this->filterUsersToAdd($userIds);

		if (empty($usersToAdd))
		{
			return $this;
		}

		$relations = $this->getRelations();
		$this->addUsersToRelation($usersToAdd, $managerIds, $hideHistory);
		$this->updateStateAfterUsersAdd($usersToAdd)->save();
		$this->sendPushUsersAdd($usersToAdd, $relations);
		$this->sendEventUsersAdd($usersToAdd);
		if ($withMessage)
		{
			$this->sendMessageUsersAdd($usersToAdd, $skipRecent);
		}

		return $this;
	}

	protected function sendMessageUsersAdd(array $usersToAdd, bool $skipRecent = false): void
	{
		if (empty($usersToAdd))
		{
			return;
		}

		$currentUserId = $this->getContext()->getUserId();
		$userCodes = [];
		foreach ($usersToAdd as $userId)
		{
			$userCodes[] = "[USER={$userId}][/USER]";
		}
		$userCodesString = implode(', ', $userCodes);

		$addsOnlyHimself = count($usersToAdd) === 1 && (isset($usersToAdd[$currentUserId]) || $currentUserId === 0);
		if ($addsOnlyHimself)
		{
			$userIdToAdd = current($usersToAdd);
			$userToAdd = Im\V2\Entity\User\User::getInstance($userIdToAdd);
			$messageText = Loc::getMessage("IM_CHAT_SELF_JOIN_{$userToAdd->getGender()}", ['#USER_NAME#' => $userCodesString]);
		}
		elseif ($currentUserId === 0 && count($usersToAdd) > 1)
		{
			$messageText = Loc::getMessage('IM_CHAT_SELF_JOIN', ['#USERS_NAME#' => $userCodesString]);
		}
		else
		{
			$currentUser = Im\V2\Entity\User\User::getInstance($currentUserId);
			$messageText = Loc::getMessage(
				"IM_CHAT_JOIN_{$currentUser->getGender()}",
				[
					'#USER_1_NAME#' => htmlspecialcharsback($currentUser->getName()),
					'#USER_2_NAME#' => $userCodesString
				]
			);
		}

		\CIMChat::AddMessage([
			"TO_CHAT_ID" => $this->getId(),
			"MESSAGE" => $messageText,
			"FROM_USER_ID" => $currentUserId,
			"SYSTEM" => 'Y',
			"RECENT_ADD" => $skipRecent ? 'N' : 'Y',
			"PARAMS" => [
				"CODE" => 'CHAT_JOIN',
				"NOTIFY" => $this->getEntityType() === self::ENTITY_TYPE_LINE? 'Y': 'N',
			],
			"PUSH" => 'N',
			"SKIP_USER_CHECK" => 'Y',
		]);
	}

	protected function sendPushUsersAdd(array $usersToAdd, RelationCollection $oldRelations): array
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return [];
		}

		$pushMessage = [
			'module_id' => 'im',
			'command' => 'chatUserAdd',
			'params' => [
				'chatId' => $this->getChatId(),
				'dialogId' => 'chat' . $this->getChatId(),
				'chatTitle' => \Bitrix\Im\Text::decodeEmoji($this->getTitle() ?? ''),
				'chatOwner' => $this->getAuthorId(),
				'chatExtranet' => $this->getExtranet() ?? false,
				'users' => (new Im\V2\Entity\User\UserCollection($usersToAdd))->toRestFormat(),
				'newUsers' => array_values($usersToAdd),
				'userCount' => $this->getUserCount()
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		$allUsersIds = $oldRelations->getUserIds();
		if ($this->getEntityType() === self::ENTITY_TYPE_LINE) //todo: refactor this
		{
			foreach ($oldRelations as $relation)
			{
				if ($relation->getUser()->getExternalAuthId() === 'imconnector')
				{
					unset($allUsersIds[$relation->getUserId()]);
				}
			}
		}
		\Bitrix\Pull\Event::add(array_values($allUsersIds), $pushMessage);

		return $pushMessage;
	}

	protected function updateStateAfterUsersAdd(array $usersToAdd): self
	{
		if (!($this->getExtranet() ?? false))
		{
			foreach ($usersToAdd as $userId)
			{
				if (Im\V2\Entity\User\User::getInstance($userId)->isExtranet())
				{
					$this->setExtranet(true);
					break;
				}
			}
		}

		$userCount = RelationTable::getCount(
			Main\ORM\Query\Query::filter()
				->where('CHAT_ID', $this->getId())
				->where('USER.ACTIVE', true)
		);

		$this->setUserCount($userCount);

		\CIMDisk::ChangeFolderMembers($this->getId(), $usersToAdd);
		self::cleanAccessCache($this->getId());
		$this->updateIndex();

		return $this;
	}

	protected function addUsersToRelation(array $usersToAdd, array $managerIds = [], ?bool $hideHistory = null)
	{
		if (empty($usersToAdd))
		{
			return;
		}

		$hideHistory ??= false;

		$managersMap = [];
		foreach ($managerIds as $managerId)
		{
			$managersMap[$managerId] = $managerId;
		}

		$relations = $this->getRelations();
		foreach ($usersToAdd as $userId)
		{
			$user = Im\V2\Entity\User\User::getInstance($userId);
			$hideHistory = (!static::EXTRANET_CAN_SEE_HISTORY && $user->isExtranet()) ? true : $hideHistory;
			$relation = new Relation();
			$relation
				->setChatId($this->getId())
				->setMessageType($this->getType())
				->setUserId($userId)
				->setLastId($this->getLastMessageId())
				->setStatus(\IM_STATUS_READ)
				->fillRestriction($hideHistory, $this)
			;
			if (isset($managersMap[$userId]))
			{
				$relation->setManager(true);
			}
			$relations->add($relation);
		}
		$relations->save(true);
		$this->relations = [];
	}

	protected function filterUsersToAdd(array $userIds): array
	{
		$usersAlreadyInChat = $this->getRelations()->getUserIds();
		$usersToAdd = [];

		foreach ($userIds as $userId)
		{
			$userId = (int)$userId;
			if (!isset($usersAlreadyInChat[$userId]) && $userId > 0)
			{
				$user = Im\V2\Entity\User\User::getInstance($userId);
				if ($user->isExist() && $user->isActive())
				{
					$usersToAdd[$userId] = $userId;
				}
			}
		}

		return $usersToAdd;
	}

	protected function sendEventUsersAdd(array $usersToAdd): void
	{
		if (empty($usersToAdd))
		{
			return;
		}

		foreach ($usersToAdd as $userId)
		{
			$relation = $this->getRelations()->getByUserId($userId, $this->getId());
			if ($relation === null)
			{
				continue;
			}
			if ($relation->getUser()->isBot())
			{
				IM\Bot::changeChatMembers($this->getId(), $userId);
				IM\Bot::onJoinChat('chat'.$this->getId(), [
					'CHAT_TYPE' => $this->getType(),
					'MESSAGE_TYPE' => $this->getType(),
					'BOT_ID' => $userId,
					'USER_ID' => $this->getContext()->getUserId(),
					'CHAT_ID' => $this->getId(),
					"CHAT_AUTHOR_ID" => $this->getAuthorId(),
					"CHAT_ENTITY_TYPE" => $this->getEntityType(),
					"CHAT_ENTITY_ID" => $this->getEntityId(),
					"ACCESS_HISTORY" => (int)$relation->getStartCounter() === 0,
				]);
			}
		}

		if (!empty($this->getEntityType()))
		{
			$converter = new Main\Engine\Response\Converter(Main\Engine\Response\Converter::TO_CAMEL | Main\Engine\Response\Converter::UC_FIRST);
			$eventCode = $converter->process($this->getEntityType());
			//$eventCode = str_replace('_', '', ucfirst(ucwords(mb_strtolower($chatEntityType), '_')));
			foreach(GetModuleEvents("im", "OnChatUserAddEntityType".$eventCode, true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array([
					'CHAT_ID' => $this->getId(),
					'NEW_USERS' => $usersToAdd,
				]));
			}
		}
	}

	public function deleteUser(int $userId, bool $withMessage = true, bool $skipRecent = false): Result
	{
		$relations = clone $this->getRelations();
		$userRelation = $this->getRelations()->getByUserId($userId, $this->getId());

		if ($userRelation === null)
		{
			return (new Result())->addError(new Im\V2\Entity\User\UserError(Im\V2\Entity\User\UserError::NOT_FOUND));
		}

		if ($this->getAuthorId() === $userId)
		{
			$this->changeAuthor();
		}

		\CIMContactList::DeleteRecent($this->getId(), true, $userId);
		\Bitrix\Im\LastSearch::delete('chat' . $this->getId(), $userId);

		$userRelation->delete();
		$this->updateStateAfterUserDelete($userId)->save();
		$this->sendPushUserDelete($userId, $relations);
		$this->sendEventUserDelete($userId);
		if ($withMessage)
		{
			$this->sendMessageUserDelete($userId, $skipRecent);
		}
		if ($this->getContext()->getUserId() !== $userId)
		{
			$this->sendNotificationUserDelete($userId);
		}

		return new Result();
	}

	protected function sendMessageUserDelete(int $userId, bool $skipRecent = false): void
	{
		if ($this->getEntityType() === 'ANNOUNCEMENT')
		{
			return;
		}

		$messageText = $this->getMessageUserDeleteText($userId);

		if ($messageText === '')
		{
			return;
		}

		\CIMChat::AddMessage([
			"TO_CHAT_ID" => $this->getId(),
			"MESSAGE" => $messageText,
			"FROM_USER_ID" => $this->getContext()->getUserId(),
			"SYSTEM" => 'Y',
			"RECENT_ADD" => $skipRecent ? 'N' : 'Y',
			"PARAMS" => [
				"CODE" => 'CHAT_LEAVE',
				"NOTIFY" => $this->getEntityType() === 'LINES'? 'Y': 'N',
			],
			"PUSH" => 'N',
			"SKIP_USER_CHECK" => "Y",
		]);
	}

	protected function sendNotificationUserDelete(int $userId): void
	{
		if ($userId === $this->getContext()->getUserId())
		{
			return;
		}
		$gender = $this->getContext()->getUser()->getGender();
		$userName = $this->getContext()->getUser()->getName();
		$userName = "[USER={$userId}]{$userName}[/USER]";
		$notificationMessage = Loc::getMessage('IM_CHAT_KICK_NOTIFICATION_'. $gender, ["#USER_NAME#" => $userName]);
		$notificationFields = [
			'TO_USER_ID' => $userId,
			'FROM_USER_ID' => 0,
			'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
			'NOTIFY_MODULE' => 'im',
			'NOTIFY_TITLE' => htmlspecialcharsback(\Bitrix\Main\Text\Emoji::decode($this->getTitle())),
			'NOTIFY_MESSAGE' => $notificationMessage,
		];
		CIMNotify::Add($notificationFields);
	}

	protected function getMessageUserDeleteText(int $userId): string
	{
		$currentUser = $this->getContext()->getUser();
		if ($this->getContext()->getUserId() === $userId)
		{
			return Loc::getMessage("IM_CHAT_LEAVE_{$currentUser->getGender()}", ['#USER_NAME#' => htmlspecialcharsback($currentUser->getName())]);
		}

		$user = Im\V2\Entity\User\User::getInstance($userId);

		return Loc::getMessage("IM_CHAT_KICK_{$currentUser->getGender()}", ['#USER_1_NAME#' => htmlspecialcharsback($currentUser->getName()), '#USER_2_NAME#' => htmlspecialcharsback($user->getName())]);
	}

	protected function updateStateAfterUserDelete(int $deletedUserId): self
	{
		$this->relations = [];
		if (
			($this->getExtranet() ?? false)
			&& $this->getRelations()->filter(fn (Relation $relation) => $relation->getUser()->isExtranet())->count() <= 0
		)
		{
			$this->setExtranet(false);
		}

		$userCount = RelationTable::getCount(
			Main\ORM\Query\Query::filter()
				->where('CHAT_ID', $this->getId())
				->where('USER.ACTIVE', true)
		);

		$this->setUserCount($userCount);

		\CIMDisk::ChangeFolderMembers($this->getId(), $deletedUserId, false);
		self::cleanAccessCache($this->getId());
		$this->updateIndex();

		return $this;
	}

	protected function sendEventUserDelete(int $userId): void
	{
		$user = Im\V2\Entity\User\User::getInstance($userId);
		if ($user->isBot())
		{
			IM\Bot::changeChatMembers($this->getId(), $userId);
			IM\Bot::onLeaveChat('chat'.$this->getId(), [
				'CHAT_TYPE' => $this->getType(),
				'MESSAGE_TYPE' => $this->getType(),
				'BOT_ID' => $userId,
				'USER_ID' => $this->getContext()->getUserId(),
				"CHAT_AUTHOR_ID" => $this->getAuthorId(),
				"CHAT_ENTITY_TYPE" => $this->getEntityType(),
				"CHAT_ENTITY_ID" => $this->getEntityId(),
			]);
		}

		if (!empty($this->getEntityType()))
		{
			$converter = new Main\Engine\Response\Converter(Main\Engine\Response\Converter::TO_CAMEL | Main\Engine\Response\Converter::UC_FIRST);
			$eventCode = $converter->process($this->getEntityType());
			//$eventCode = str_replace('_', '', ucfirst(ucwords(mb_strtolower($chatEntityType), '_')));
			foreach(GetModuleEvents("im", "OnChatUserDeleteEntityType".$eventCode, true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array([
					'CHAT_ID' => $this->getId(),
					'USER_ID' => $userId,
				]));
			}
		}
	}

	protected function sendPushUserDelete(int $userId, RelationCollection $oldRelations): array
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return [];
		}

		$pushMessage = [
			'module_id' => 'im',
			'command' => 'chatUserLeave',
			'params' => [
				'chatId' => $this->getChatId(),
				'dialogId' => 'chat' . $this->getChatId(),
				'chatTitle' => \Bitrix\Im\Text::decodeEmoji($this->getTitle() ?? ''),
				'userId' => $userId,
				'message' => $userId === $this->getContext()->getUserId() ? '' : $this->getMessageUserDeleteText($userId),
				'userCount' => $this->getUserCount()
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		$allUsersIds = $oldRelations->getUserIds();
		if ($this->getEntityType() === self::ENTITY_TYPE_LINE) //todo: refactor this
		{
			foreach ($oldRelations as $relation)
			{
				if ($relation->getUser()->getExternalAuthId() === 'imconnector')
				{
					unset($allUsersIds[$relation->getUserId()]);
				}
			}
		}
		\Bitrix\Pull\Event::add(array_values($allUsersIds), $pushMessage);

		return $pushMessage;
	}

	public function changeAuthor(): void
	{
		$currentAuthorId = $this->getAuthorId();
		$relations = $this->getRelations();
		$authorRelation = $relations->getByUserId($currentAuthorId, $this->getId());
		if ($authorRelation !== null)
		{
			$authorRelation->setManager(false);
		}
		$otherRealUserRelation = $relations->filter(static function (Relation $relation) use ($currentAuthorId) {
			$user = $relation->getUser();

			return $user->getId() !== $currentAuthorId
				&& $user->isActive()
				&& !$user->isBot()
				&& !$user->isExtranet()
				&& !$user->isConnector()
			;
		})->getAny();

		if (!$otherRealUserRelation instanceof Relation)
		{
			return;
		}

		$this->setAuthorId($otherRealUserRelation->getUserId());
		$otherRealUserRelation->setManager(true);
		$relations->save(true);
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

		if (!$removedCount)
		{
			return $this;
		}

		$relations = $this->getRelations();
		if ($extranetFlag = $this->getExtranet() ?? false)
		{
			$extranetFlag = false;
			foreach ($relations as $relation)
			{
				if ($extranetFlag = $relation->getUser()->isExtranet())
				{
					break;
				}
			}
		}

		$this
			->setExtranet($extranetFlag)
			->setUserCount($relations->count())
			->save();

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			$pushMessage = [
				'module_id' => 'im',
				'command' => 'chatUsersRemove',
				'params' => [
					'chatId' => $this->getChatId(),
					'dialogId' => 'chat' . $this->getChatId(),
					'chatExtranet' => $extranetFlag,
					'userCount' => $relations->count()
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			];

			$allUsersIds = $relations->getUserIds();
			if ($this->getEntityType() === self::ENTITY_TYPE_LINE)
			{
				foreach ($relations as $relation)
				{
					if ($relation->getUser()->getExternalAuthId() === 'imconnector')
					{
						unset($allUsersIds[$relation['userId']]);
					}
				}
			}
			\Bitrix\Pull\Event::add(array_values($allUsersIds), $pushMessage);
			if ($this->getType() === self::IM_TYPE_OPEN  || $this->getType() === self::IM_TYPE_OPEN_LINE)
			{
				\CPullWatch::AddToStack('IM_PUBLIC_' . $this->getChatId(), $pushMessage);
			}
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
		$unsetManagerIds = [];
		/** @var Relation $relation */
		foreach ($relations as $relation)
		{
			if (in_array($relation->getUserId(), $managerIds, true))
			{
				$relationIds[] = $relation->getPrimaryId();
			}
			elseif ($relation->getManager())
			{
				$unsetManagerIds[] = $relation->getPrimaryId();
			}

		}

		if ($unsetManagerIds)
		{
			RelationTable::updateMulti(
				$unsetManagerIds,
				[
					'MANAGER' => 'N',
				]
			);
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
		$this->role = null;

		return $this;
	}

	public function getLoadContextMessage(): Message
	{
		$startMessageId = $this->getMarkedId() ?: $this->getLastId();

		return (new \Bitrix\Im\V2\Message($startMessageId))->setChatId($this->getId())->setMessageId($startMessageId);
	}

	public function fillNonCachedData(): self
	{
		if ($this->isFilledNonCachedData)
		{
			return $this;
		}

		$this->fillActual(['MESSAGE_COUNT', 'USER_COUNT', 'LAST_MESSAGE_ID', 'ALIAS.ALIAS']);
		$this->isFilledNonCachedData = true;

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'chat';
	}

	public function toRestFormat(array $option = []): array
	{
		if ($option['CHAT_SHORT_FORMAT'] ?? false)
		{
			return [
				'avatar' => $this->getAvatar(),
				'color' => (string)$this->getColor() !== '' ? Color::getColor($this->getColor()) : Color::getColorByNumber($this->getChatId()),
				'description' => $this->getDescription() ?? '',
				'dialogId' => $this->getDialogId(),
				'diskFolderId' => $this->getDiskFolderId(),
				'entityData1' => $this->getEntityData1() ?? '',
				'entityData2' => $this->getEntityData2() ?? '',
				'entityData3' => $this->getEntityData3() ?? '',
				'entityId' => $this->getEntityId() ?? '',
				'entityType' => $this->getEntityType() ?? '',
				'extranet' => $this->getExtranet() ?? false,
				'id' => $this->getId(),
				'name' => $this->getTitle(),
				'owner' => (int)$this->getAuthorId(),
				'messageType' => $this->getType(),
				'role' => $this->getRole(),
				'type' => $this->getTypeForRest(),
				'manageUsers' => mb_strtolower($this->getManageUsers()),
				'manageUi' => mb_strtolower($this->getManageUI()),
				'manageSettings' => mb_strtolower($this->getManageSettings()),
				'canPost' => mb_strtolower($this->getCanPost()),
			];
		}

		return [
			'avatar' => $this->getAvatar(),
			'color' => (string)$this->getColor() !== '' ? Color::getColor($this->getColor()) : Color::getColorByNumber($this->getChatId()),
			'counter' => $this->getReadService()->getCounterService()->getByChat($this->getChatId()),
			'dateCreate' => $this->getDateCreate() === null ? null : $this->getDateCreate()->format('c'),
			'description' => $this->getDescription() ?? '',
			'dialogId' => $this->getDialogId(),
			'diskFolderId' => $this->getDiskFolderId(),
			'entityData1' => $this->getEntityData1() ?? '',
			'entityData2' => $this->getEntityData2() ?? '',
			'entityData3' => $this->getEntityData3() ?? '',
			'entityId' => $this->getEntityId() ?? '',
			'entityType' => $this->getEntityType() ?? '',
			'extranet' => $this->getExtranet() ?? false,
			'id' => $this->getId(),
			'lastMessageId' => $this->getLastMessageId(),
			'lastMessageViews' => Im\Common::toJson($this->getLastMessageViews()),
			'lastId' => $this->getLastId(),
			'managerList' => $this->getManagerList(),
			'markedId' => $this->getMarkedId(),
			'messageCount' => $this->getMessageCount(),
			'messageType' => $this->getType(),
			'muteList' => $this->getMuteList(),
			'name' => $this->getTitle(),
			'owner' => (int)$this->getAuthorId(),
			'public' => $this->getPublicOption() ?? '',
			'role' => mb_strtolower($this->getRole()),
			'type' => $this->getTypeForRest(),
			'unreadId' => $this->getUnreadId(),
			'userCounter' => $this->getUserCount(),
			'manageUsers' => mb_strtolower($this->getManageUsers()),
			'manageUi' => mb_strtolower($this->getManageUI()),
			'manageSettings' => mb_strtolower($this->getManageSettings()),
			'canPost' => mb_strtolower($this->getCanPost()),
		];
	}

	protected function getManagerList(): array
	{
		$userIds = [];
		$relations = $this->getRelations();

		foreach ($relations as $relation)
		{
			if ($relation->getManager() ?? false)
			{
				$userIds[] = $relation->getUserId();
			}
		}

		return $userIds;
	}

	protected function getMuteList(): array
	{
		$selfRelation = $this->getSelfRelation();

		if ($selfRelation === null)
		{
			return [];
		}

		if ($selfRelation->getNotifyBlock() ?? false)
		{
			return [$this->getContext()->getUserId()];
		}

		return [];
	}

	protected function getPublicOption(): ?array
	{
		if ($this->getAliasName() === null)
		{
			return null;
		}

		return [
			'code' => $this->getAliasName(),
			'link' => Alias::getPublicLink($this->getEntityType(), $this->getAliasName())
		];
	}

	protected function getRestrictions(): array
	{
		$options = \CIMChat::GetChatOptions();

		if ($this->getEntityType() && isset($options[$this->getEntityType()]))
		{
			return $options[$this->getEntityType()];
		}

		return $options['DEFAULT'];
	}

	public function getTypeForRest(): string
	{
		return Im\Chat::getType(['ID' => $this->getId(), 'TYPE' => $this->getType(), 'ENTITY_TYPE' => $this->getEntityType()]);
	}

	protected function getUnreadId(): int
	{
		$selfRelation = $this->getSelfRelation();
		if ($selfRelation === null)
		{
			return 0;
		}

		return $selfRelation->getUnreadId() ?? 0;
	}

	protected function getLastId(): int
	{
		$selfRelation = $this->getSelfRelation();
		if ($selfRelation === null)
		{
			return 0;
		}

		return $selfRelation->getLastId() ?? 0;
	}

	protected function addIndex(): self
	{
		if (!$this->getChatId())
		{
			return $this;
		}

		$index = \Bitrix\Im\Internals\ChatIndex::create()
			->setChatId($this->getChatId())
			->setTitle(mb_substr($this->getTitle() ?? '', 0, 255))
			->setUserList($this->getUserNamesForIndex())
		;
		\Bitrix\Im\Model\ChatTable::addIndexRecord($index);

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
			->setUserList($this->getUserNamesForIndex())
		;
		\Bitrix\Im\Model\ChatTable::updateIndexRecord($index);

		return $this;
	}

	private function getUserNamesForIndex(): array
	{
		$relations = $this->getRelations(['LIMIT' => 100]);

		$users = [];
		foreach ($relations as $relation)
		{
			$users[] = $relation->getUser()->getName();
		}

		return $users;
	}

	public function deleteChat(): Result
	{
		$result = new Result();

		if (!$this->getChatId())
		{
			return $result->addError(new ChatError(ChatError::NOT_FOUND));
		}

		$this->hideChat();

		$this->getRelations()->delete();

		$chatId = $this->getChatId();
		$chatFolderId = $this->getDiskFolderId();
		$this->delete();

		$messageCollection = MessageCollection::find(['CHAT_ID' => $chatId], []);
		$messageIds = $messageCollection->getIds();
		$messageCollection->delete();

		foreach (array_chunk($messageIds, self::CHUNK_SIZE) as $messageIdsChunk)
		{
			Im\Model\MessageParamTable::deleteBatch([
				'=MESSAGE_ID' => $messageIdsChunk,
			]);
		}

		Im\V2\Link\Url\UrlCollection::deleteByChatsIds([$chatId]);
		Im\V2\Chat::cleanCache($chatId);

		if ($chatFolderId)
		{
			$folderModel = \Bitrix\Disk\Folder::getById($chatFolderId);
			if ($folderModel)
			{
				$folderModel->deleteTree(\Bitrix\Disk\SystemUser::SYSTEM_USER_ID);
			}
		}

		return $result;
	}

	private function hideChat(): Result
	{
		$result = new Result();

		if (!$this->getChatId())
		{
			return $result->addError(new ChatError(ChatError::NOT_FOUND));
		}

		$pushList = [];
		foreach($this->getRelations() as $relation)
		{
			\CIMContactList::DeleteRecent($this->getChatId(), true, $relation->getUserId());

			if (!Im\User::getInstance($relation->getUserId())->isConnector())
			{
				$pushList[] = $relation->getUserId();
			}
		}

		if (
			!empty($pushList)
			&& \Bitrix\Main\Loader::includeModule("pull")
		)
		{
			\Bitrix\Pull\Event::add($pushList, [
				'module_id' => 'im',
				'command' => 'chatHide',
				'expiry' => 3600,
				'params' => [
					'dialogId' => 'chat' . $this->getChatId(),
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return $result;
	}
}