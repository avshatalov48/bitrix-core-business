<?php

namespace Bitrix\Im\V2;

use Bitrix\Disk\Folder;
use Bitrix\Im\Alias;
use Bitrix\Im\V2\Analytics\MessageAnalytics;
use Bitrix\Im\V2\Entity\User\UserError;
use Bitrix\Im\V2\Async\Promise\BackgroundJobPromise;
use Bitrix\Im\V2\Entity\User\UserType;
use Bitrix\Im\V2\Integration\AI\AIHelper;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Integration\Socialnetwork\Group;
use Bitrix\Im\V2\Message\Counter\CounterType;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Message\Send\MentionService;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\Message\Send\SendingService;
use Bitrix\Im\V2\Message\Send\SendResult;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
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
use Bitrix\Im\V2\Chat\Param\Params;
use Bitrix\Pull\Event;
use CGlobalCounter;
use CIMContactList;
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
		IM_TYPE_OPEN_CHANNEL = 'J',
		IM_TYPE_OPEN = 'O',
		IM_TYPE_COPILOT = 'A',
		IM_TYPE_COLLAB = 'B'
	;

	public const IM_TYPES = [
		self::IM_TYPE_PRIVATE,
		self::IM_TYPE_CHAT,
		self::IM_TYPE_COMMENT,
		self::IM_TYPE_OPEN_LINE,
		self::IM_TYPE_SYSTEM,
		self::IM_TYPE_CHANNEL,
		self::IM_TYPE_OPEN_CHANNEL,
		self::IM_TYPE_OPEN,
		self::IM_TYPE_COPILOT,
		self::IM_TYPE_COLLAB,
	];

	public const IM_TYPES_TRANSLATE = [
		'PRIVATE' => self::IM_TYPE_PRIVATE,
		'CHAT' => self::IM_TYPE_CHAT,
		'COMMENT' => self::IM_TYPE_COMMENT,
		'OPENLINE' => self::IM_TYPE_OPEN_LINE,
		'SYSTEM' => self::IM_TYPE_SYSTEM,
		'NOTIFY' => self::IM_TYPE_SYSTEM,
		'CHANNEL' => self::IM_TYPE_CHANNEL,
		'OPEN_CHANNEL' => self::IM_TYPE_OPEN_CHANNEL,
		'OPEN' => self::IM_TYPE_OPEN,
		'COPILOT' => self::IM_TYPE_COPILOT,
		'COLLAB' => self::IM_TYPE_COLLAB,
	];

	// Default entity types
	public const
		ENTITY_TYPE_VIDEOCONF = 'VIDEOCONF',
		ENTITY_TYPE_GENERAL = 'GENERAL',
		ENTITY_TYPE_FAVORITE = 'FAVORITE',
		ENTITY_TYPE_GENERAL_CHANNEL = 'GENERAL_CHANNEL'
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
		'manageUsersAdd',
		'manageUsersDelete',
		'manageUi',
		'manageSettings',
		'disappearingTime',
		'manageMessages',
		'avatar',
		'conferencePassword',
		'memberEntities'
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
	protected const EXTRANET_CAN_SEE_HISTORY = true;

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

	protected ?Folder $diskFolder = null;

	protected ?int $messageCount = null;

	protected ?int $userCount = null;

	protected ?int $prevMessageId = null;

	protected ?int $lastMessageId = null;
	protected ?int $lastFileId = null;
	protected ?DateTime $dateMessage = null;

	protected ?int $markedId = null;
	protected ?string $role = null;

	protected ?string $aliasName = null;

	protected ?string $lastMessageStatus = null;

	protected ?DateTime $dateCreate = null;

	protected ?string $manageUsersAdd = null;
	protected ?string $manageUsersDelete = null;

	protected ?string $manageUI = null;

	protected ?string $manageSettings = null;

	protected ?string $manageMessages = null;

	protected ?array $usersIds = null;

	protected ?int $disappearingTime = null;

	protected ?Params $chatParams = null;

	/** @var Registry<Message>  */
	protected Registry $messageRegistry;

	protected ?Im\V2\Relation\ChatRelations $chatRelations = null;

	protected ?ReadService $readService = null;

	protected bool $isFilledNonCachedData = false;
	protected bool $isDiskFolderFilled = false;

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

	public static function cleanCache(int $id, bool $cleanStaticCache = true): void
	{
		if ($cleanStaticCache)
		{
			unset(self::$chatStaticCache[$id]);
		}

		ChatFactory::getInstance()->cleanCache($id);
		Im\V2\Chat\EntityLink::cleanCache($id);
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

		if ($id !== null && ($result->getResult()['IS_CHANGES'] ?? true) === true)
		{
			self::cleanCache($id);
		}

		if ($this->getChatParams() !== null && $this->getChatParams()->isCreated())
		{
			$this->chatParams->saveWithNewChatId($this->getChatId());
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

		foreach ($this->usersIds as $userId)
		{
			$user = Im\V2\Entity\User\User::getInstance($userId);

			if ($user->getType() === UserType::EXTRANET)
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
		return $this->aliasName;
	}

	public function setAliasName(string $aliasName): self
	{
		$this->aliasName = $aliasName;

		return $this;
	}

	public function prepareAliasToLoad($alias): ?string
	{
		if (is_string($alias))
		{
			return $alias;
		}

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

		$selfRelation = $this->getSelfRelation();

		if ($selfRelation === null)
		{
			$this->role = self::ROLE_GUEST;

			return $this->role;
		}

		if ($this->getContext()->getUserId() === (int)$this->getAuthorId())
		{
			$this->role = self::ROLE_OWNER;

			return $this->role;
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

	public function setChatParams(array $chatParams = []): self
	{
		$this->chatParams = Chat\Param\Params::loadWithoutChat($chatParams);

		return $this;
	}

	public function getChatParams(): ?Params
	{
		if (!isset($this->chatParams) && $this->getChatId() !== null)
		{
			$this->chatParams = Chat\Param\Params::getInstance($this->getChatId());
		}

		return $this->chatParams;
	}

	//region Access & Permissions

	final public function checkAccess(int|User|null $user = null): Result
	{
		$userId = $this->getUserId($user);

		if (isset($this->accessCache[$userId]))
		{
			return $this->accessCache[$userId];
		}

		if (!$userId || !$this->getChatId())
		{
			$this->accessCache[$userId] = (new Result())->addError(new ChatError(ChatError::NOT_FOUND));

			return $this->accessCache[$userId];
		}

		$this->accessCache[$userId] = $this->checkAccessInternal($userId);

		return $this->accessCache[$userId];
	}

	protected function checkAccessInternal(int $userId): Result
	{
		return (new Result())->addError(new ChatError(ChatError::ACCESS_DENIED));
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

	public function sendMessage(Message $message, ?SendingConfig $sendingConfig = null): SendResult
	{
		$result = new SendResult();

		$this->prepareMessage($message);
		$sendingConfig ??= new SendingConfig();

		$sendService = (new SendingService($sendingConfig))->setContext($message->getContext());
		$onBeforeResult = $this->onBeforeMessageSend($message, $sendingConfig);
		if (!$onBeforeResult->isSuccess())
		{
			return $result->addErrors($onBeforeResult->getErrors());
		}

		$checkUuidResult = $sendService->checkDuplicateByUuid($message);
		if (!$checkUuidResult->isSuccess())
		{
			return $result->addErrors($checkUuidResult->getErrors());
		}

		$data = $checkUuidResult->getResult();
		if (!empty($data['messageId']))
		{
			return $result->setMessageId((int)$data['messageId']);
		}

		$message->autocompleteParams($sendingConfig);

		$eventResult = $sendService->fireEventBeforeSend($this, $message);
		if (!$eventResult->isSuccess())
		{
			return $result->addErrors($eventResult->getErrors());
		}

		if ($message->getChatId() !== $this->getId()) // The target chat was changed in the event handler
		{
			return $this->processSendToOtherChat($message, $sendingConfig);
		}

		if ($message->isCompletelyEmpty())
		{
			return $result->addError(new MessageError(MessageError::EMPTY_MESSAGE));
		}

		$message->uploadFileFromText();

		$saveResult = $message->save();
		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		$promise = BackgroundJobPromise::deferJob(fn () => $this->onAfterMessageSend($message, $sendService));

		return $result->setMessageId($message->getId())->setPromise($promise);
	}

	protected function onBeforeMessageSend(Message $message, SendingConfig $config): Result
	{
		return new Result();
	}

	public function getRelationsForSendMessage(): RelationCollection
	{
		return $this->getRelations()->filterActive();
	}

	protected function onAfterMessageSend(Message $message, SendingService $sendingService): void
	{
		$authorContext = $message->getContext();
		$sendingConfig = $sendingService->getConfig();

		$sendingService->updateMessageUuid($message);
		(new MessageAnalytics($message))->addSendMessage();

		if ($sendingConfig->convertMode())
		{
			return;
		}

		$updateStateResult = $this->updateStateAfterMessageSend($message, $sendingConfig);
		$counters = $updateStateResult->getResult()['COUNTERS'] ?? [];

		$this->getMentionService($sendingConfig)->setContext($authorContext)->sendMentions($message);
		$this->getPushService($message, $sendingConfig)->setContext($authorContext)->sendPush($counters);
		$sendingService->fireEventAfterMessageSend($this, $message);
		(new Im\V2\Link\LinkFacade($sendingConfig))->setContext($authorContext)->saveLinksFromMessage($message);
	}

	protected function processSendToOtherChat(Message $message, SendingConfig $config): SendResult
	{
		$newConfig = clone $config;
		$newConfig->skipFireEventBeforeMessageNotifySend();

		return $message->getChat()->sendMessage($message, $config);
	}

	protected function prepareMessage(Message $message): void
	{
		$message
			->setRegistry($this->messageRegistry)
			->setContextUser($message->getAuthorId() ?: $this->getContext()->getUserId())
			->setChatId($this->getId())
			->setChat($this)
			->filterMessageText()
		;
	}

	protected function updateStateAfterMessageSend(Message $message, SendingConfig $sendingConfig): Result
	{
		$result = new Result();
		$this->updateChatAfterMessageSend($message);
		$this->logToSyncAfterMessageSend($message);

		if (!$sendingConfig->addRecent())
		{
			return $result;
		}

		$this->updateRecentAfterMessageSend($message, $sendingConfig);
		$this->updateRelationsAfterMessageSend($message);

		return $this->updateCountersAfterMessageSend($message, $sendingConfig);
	}

	protected function updateChatAfterMessageSend(Message $message): Result
	{
		$countMessageBeforeUpdate = $this->getMessageCount();
		\Bitrix\Im\Model\ChatTable::update($this->getId(), [
			'MESSAGE_COUNT' => new \Bitrix\Main\DB\SqlExpression('?# + 1', 'MESSAGE_COUNT'),
			'LAST_MESSAGE_ID' => $message->getId(),
		]);
		$this->messageCount = $countMessageBeforeUpdate + 1;
		$this->lastMessageId = $message->getId();

		return new Result();
	}

	protected function updateRecentAfterMessageSend(Message $message, SendingConfig $config): Result
	{
		$usersToAddToRecent = Recent::getUsersOutOfRecent($this);
		Im\Model\RecentTable::updateByFilter(
			['=ITEM_CID' => $this->getId()],
			$this->getUpdatedFieldsForRecent($message)
		);

		if ($config->skipAuthorAddRecent())
		{
			unset($usersToAddToRecent[$message->getAuthorId()]);
		}

		$this->addToRecent($usersToAddToRecent, $message);

		return new Result();
	}

	protected function addToRecent(array $users, Message $message): Result
	{
		if (empty($users))
		{
			return new Result();
		}

		$fields = [];

		foreach ($users as $userId)
		{
			$field = $this->getFieldsForRecent($userId, $message);
			if (!empty($field))
			{
				$fields[] = $field;
			}
		}

		$this->insertRecent($fields);

		return new Result();
	}

	protected function insertRecent(array $fields): void
	{
		Im\Model\RecentTable::multiplyInsertWithoutDuplicate(
			$fields,
			['DEADLOCK_SAFE' => true, 'UNIQUE_FIELDS' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID']]
		);
	}

	protected function getFieldsForRecent(int $userId, Message $message): array // todo: refactor
	{
		$relationId = $this->getRelations()->getByUserId($userId, $this->getId())?->getId();

		if ($relationId === null)
		{
			return [];
		}

		return [
			'USER_ID' => $userId,
			'ITEM_TYPE' => $this->getType(),
			'ITEM_ID' => $this->getId(),
			'ITEM_MID' => $message->getId(),
			'ITEM_CID' => $this->getId(),
			'ITEM_RID' => $relationId,
			'DATE_MESSAGE' => $message->getDateCreate(),
			'DATE_LAST_ACTIVITY' => $message->getDateCreate(),
			'DATE_UPDATE' => $message->getDateCreate(),
		];
	}

	protected function getUpdatedFieldsForRecent(Message $message): array
	{
		return [
			'ITEM_MID' => $message->getId(),
			'DATE_MESSAGE' => $message->getDateCreate(),
			'DATE_UPDATE' => $message->getDateCreate(),
			'DATE_LAST_ACTIVITY' => $message->getDateCreate(),
		];
	}

	protected function updateRelationsAfterMessageSend(Message $message): Result
	{
		$this->getRelations()
			->getByUserId($message->getAuthorId(), $this->getId())
			?->setLastId($message->getId())
			?->setLastSendMessageId($message->getId())
			?->save()
		;

		return new Result();
	}

	protected function updateCountersAfterMessageSend(Message $message, SendingConfig $sendingConfig): Result
	{
		return $this
			->getReadService()
			->withContextUser($message->getContext()->getUserId())
			->onAfterMessageSend($message, $this->getRelationsForSendMessage(), $sendingConfig->skipCounterIncrements())
		;
	}

	protected function logToSyncAfterMessageSend(Message $message): Result
	{
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::MESSAGE_ENTITY, $message->getId()),
			$this->getRelations()->getUserIds(),
			$this->getType()
		);
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $this->getId()),
			$this->getRelations()->getUserIds(),
			$this->getType()
		);

		return new Result();
	}

	protected function getMentionService(SendingConfig $config): MentionService
	{
		return new MentionService($config);
	}

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
	 * @param static[] $chats
	 * @param int|null $userId
	 * @return void
	 */
	public static function fillSelfRelations(array $chats, ?int $userId = null): void
	{
		$userId ??= Im\V2\Entity\User\User::getCurrent()->getId();
		$chatIds = [];
		foreach ($chats as $chat)
		{
			$chatIds[] = $chat->getId();
		}

		if (empty($chatIds))
		{
			return;
		}

		$relationEntities = RelationTable::query()
			->setSelect(RelationCollection::COMMON_FIELDS)
			->where('USER_ID', $userId)
			->whereIn('CHAT_ID', $chatIds)
			->fetchAll()
		;
		$relations = new RelationCollection($relationEntities);

		foreach ($chats as $chat)
		{
			$chat->getRelationFacade()->preloadUserRelation($userId, $relations->getByUserId($userId, $chat->getId()));
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
		$readResult = isset($messages) ? $readService->read($messages, $this) :  $readService->readAllInChat($this->chatId);
		$counter = $readResult->getResult()['COUNTER'] ?? 0;
		$viewedMessages = $readResult->getResult()['VIEWED_MESSAGES'] ?? new MessageCollection();

		$lastId = $readService->getLastIdByChatId($this->chatId);

		$notOwnMessages = $viewedMessages->filter(fn (Message $message) => $message->getAuthorId() !== $this->getContext()->getUserId());

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

	public function readTo(Message $message, bool $byEvent = false): Result
	{
		$readService = $this->getReadService();
		$startId = $message->getMessageId();
		$readResult = $readService->readTo($message);
		$counter = $readResult->getResult()['COUNTER'] ?? 0;

		$viewedMessages = $readResult->getResult()['VIEWED_MESSAGES'];
		$messageCollection = new MessageCollection();
		foreach ($viewedMessages as $messageId)
		{
			$viewedMessage = new Message();
			$viewedMessage->setMessageId((int)$messageId);
			$messageCollection->add($viewedMessage);
		}

		$lastId = $readService->getLastIdByChatId($this->chatId);

		if (Main\Loader::includeModule('pull'))
		{
			CIMNotify::DeleteBySubTag("IM_MESS_{$this->getChatId()}_{$this->getContext()->getUserId()}", false, false);
			CPushManager::DeleteFromQueueBySubTag($this->getContext()->getUserId(), 'IM_MESS');
			$this->sendPushRead($messageCollection, $lastId, $counter);
		}

		$this->sendEventRead($startId, $lastId, $counter, $byEvent);

		$result = new Result();
		return $result->setResult([
			'CHAT_ID' => $this->chatId,
			'LAST_ID' => $lastId,
			'COUNTER' => $counter,
			'VIEWED_MESSAGES' => $viewedMessages,
		]);
	}

	public function sendPushUpdateMessage(Message $message): void
	{
		return;
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

		$push = Im\V2\Message\PushFormat::formatStartRecordVoice($this);
		if ($this->getType() === self::IM_TYPE_COMMENT)
		{
			\CPullWatch::AddToStack('IM_PUBLIC_COMMENT_'.$this->getParentChatId(), $push);
		}
		else
		{
			Event::add($this->getUsersForPush(), $push);
		}
		if ($this->needToSendPublicPull())
		{
			\CPullWatch::AddToStack('IM_PUBLIC_'.$this->getId(), $push);
		}
		if ($this->getType() === self::IM_TYPE_OPEN_CHANNEL)
		{
			Im\V2\Chat\OpenChannelChat::sendSharedPull($push);
		}
	}

	abstract protected function getPushService(Message $message, SendingConfig $config): PushService;

	protected function sendPushReadSelf(MessageCollection $messages, int $lastId, int $counter): void
	{
		$selfRelation = $this->getSelfRelation();

		$muted = isset($selfRelation) ? $selfRelation->getNotifyBlock() : false;
		\Bitrix\Pull\Event::add($this->getContext()->getUserId(), [
			'module_id' => 'im',
			'command' => 'readMessageChat',
			'params' => [
				'dialogId' => $this->getDialogId(),
				'chatId' => $this->getChatId(),
				'parentChatId' => $this->getParentChatId(),
				'type' => $this->getExtendedType(),
				'lastId' => $lastId,
				'counter' => $counter,
				'muted' => $muted ?? false,
				'unread' => Im\Recent::isUnread($this->getContext()->getUserId(), $this->getType(), $this->getDialogId()),
				'lines' => $this->getType() === IM_MESSAGE_OPEN_LINE,
				'viewedMessages' => $messages->getIds(),
				'counterType' => $this->getCounterType()->value,
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	protected function sendPushReadOpponent(MessageCollection $messages, int $lastId): array
	{
		$viewedMessageIds = $messages->getIds();
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
				'viewedMessages' => $viewedMessageIds,
				'chatMessageStatus' => $this->getReadService()->getChatMessageStatus($this->chatId),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
		if ($this->getType() === Chat::IM_TYPE_COMMENT)
		{
			\CPullWatch::AddToStack('IM_PUBLIC_COMMENT_' . $this->getParentChatId(), $pushMessage);
		}
		else
		{
			\Bitrix\Pull\Event::add($this->getUsersForPush(), $pushMessage);
		}
		$lastMessageId = $this->getReadService()->getLastMessageIdInChat($this->chatId);
		$maxViewedMessageId = !empty($viewedMessageIds) ? max($viewedMessageIds) : 0;

		if ($this->needToSendPublicPull())
		{
			\CPullWatch::AddToStack("IM_PUBLIC_{$this->chatId}", $pushMessage);
		}
		if ($this->getType() === Chat::IM_TYPE_OPEN_CHANNEL && $maxViewedMessageId === $lastMessageId)
		{
			Im\V2\Chat\OpenChannelChat::sendSharedPull($pushMessage);
		}

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

	public function getLastMessageViews(): array
	{
		$lastMessageViewsByGroups = $this->getLastMessageViewsByGroups();

		if (isset($lastMessageViewsByGroups['USERS'][$this->getContext()->getUserId()]))
		{
			return $lastMessageViewsByGroups['FOR_VIEWERS'];
		}

		return $lastMessageViewsByGroups['FOR_NOT_VIEWERS'];
	}

	public function getLastMessageViewsByGroups(): array
	{
		$defaultViewInfo = [
			'MESSAGE_ID' => 0,
			'FIRST_VIEWERS' => [],
			'COUNT_OF_VIEWERS' => 0,
		];
		$defaultValue = [
			'USERS' => [],
			'FOR_VIEWERS' => $defaultViewInfo,
			'FOR_NOT_VIEWERS' => $defaultViewInfo,
		];

		$readService = $this->getReadService();

		$lastMessageInChat = $this->getLastMessageId() ?? 0;

		if ($lastMessageInChat === 0)
		{
			return $defaultValue;
		}

		$messageViewers = $readService->getViewedService()->getMessageViewersIds($lastMessageInChat);
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

		$viewsInfoByGroups = ['USERS' => $messageViewers];
		$viewInfoForViewers = [
			'MESSAGE_ID' => $lastMessageInChat,
			'FIRST_VIEWERS' => $firstViewersWithDate,
			'COUNT_OF_VIEWERS' => $countOfView - 1,
		];
		$viewInfoForNotViewers = $viewInfoForViewers;
		++$viewInfoForNotViewers['COUNT_OF_VIEWERS'];
		$viewsInfoByGroups['FOR_VIEWERS'] = $viewInfoForViewers;
		$viewsInfoByGroups['FOR_NOT_VIEWERS'] = $viewInfoForNotViewers;

		return $viewsInfoByGroups;
	}

	protected function getUsersForPush(bool $skipBot = false, bool $skipSelf = true): array
	{
		$userId = $this->getContext()->getUserId();
		$isLineChat = $this->getEntityType() === self::ENTITY_TYPE_LINE;
		$relations = $this->getRelations();
		$userIds = [];
		foreach ($relations as $relation)
		{
			if ($skipSelf && $relation->getUserId() === $userId)
			{
				continue;
			}
			if ($skipBot && $relation->getUser()->isBot())
			{
				continue;
			}
			if ($isLineChat && $relation->getUser()->isConnector())
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
			'CHAT_ID' => [
				'alias' => 'ID',
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
			'CHAT_AUTHOR_ID' => [
				'alias' => 'AUTHOR_ID',
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
				'nullable' => true,
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
			'MANAGE_USERS_ADD' => [
				'field' => 'manageUsersAdd', /** @see Chat::$manageUsersAdd */
				'get' => 'getManageUsersAdd',  /** @see Chat::getManageUsersAdd */
				'set' => 'setManageUsersAdd',  /** @see Chat::setManageUsersAdd */
				'default' => 'getDefaultManageUsersAdd', /** @see Chat::getDefaultManageUsersAdd */
			],
			'MANAGE_USERS_DELETE' => [
				'field' => 'manageUsersDelete', /** @see Chat::$manageUsersDelete */
				'get' => 'getManageUsersDelete',  /** @see Chat::getManageUsersDelete */
				'set' => 'setManageUsersDelete',  /** @see Chat::setManageUsersDelete */
				'default' => 'getDefaultManageUsersDelete', /** @see Chat::getDefaultManageUsersDelete */
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
				'field' => 'manageMessages', /** @see Chat::$manageMessages */
				'get' => 'getManageMessages',  /** @see Chat::getManageMessages */
				'set' => 'setManageMessages',  /** @see Chat::setManageMessages */
				'default' => 'getDefaultManageMessages', /** @see Chat::getDefaultManageMessages */
			],
			'MANAGE_MESSAGES' => [
				'alias' => 'CAN_POST'
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
			],
			'RELATIONS' => [
				'set' => 'setRelations', /** @see Chat::setRelations */
				'skipSave' => true,
			],
			'CHAT_PARAMS' => [
				'set' => 'setChatParams', /** @see Chat::setChatParams */
				'skipSave' => true,
			],
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
					C.ID as CHAT_ID,
					C.PARENT_ID as CHAT_PARENT_ID,
					C.PARENT_MID as CHAT_PARENT_MID,
					C.TITLE as CHAT_TITLE,
					C.AUTHOR_ID as CHAT_AUTHOR_ID,
					C.TYPE as CHAT_TYPE,
					C.AVATAR as CHAT_AVATAR,
					C.COLOR as CHAT_COLOR,
					C.ENTITY_TYPE as CHAT_ENTITY_TYPE,
					C.ENTITY_ID as CHAT_ENTITY_ID,
					C.ENTITY_DATA_1 as CHAT_ENTITY_DATA_1,
					C.ENTITY_DATA_2 as CHAT_ENTITY_DATA_2,
					C.ENTITY_DATA_3 as CHAT_ENTITY_DATA_3,
					C.EXTRANET as CHAT_EXTRANET,
					C.PREV_MESSAGE_ID as CHAT_PREV_MESSAGE_ID,
					'1' as RID,
					'Y' as IS_MANAGER
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
					C.ID as CHAT_ID,
					C.PARENT_ID as CHAT_PARENT_ID,
					C.PARENT_MID as CHAT_PARENT_MID,
					C.TITLE as CHAT_TITLE,
					C.AUTHOR_ID as CHAT_AUTHOR_ID,
					C.TYPE as CHAT_TYPE,
					C.AVATAR as CHAT_AVATAR,
					C.COLOR as CHAT_COLOR,
					C.ENTITY_TYPE as CHAT_ENTITY_TYPE,
					C.ENTITY_ID as CHAT_ENTITY_ID,
					C.ENTITY_DATA_1 as CHAT_ENTITY_DATA_1,
					C.ENTITY_DATA_2 as CHAT_ENTITY_DATA_2,
					C.ENTITY_DATA_3 as CHAT_ENTITY_DATA_3,
					C.EXTRANET as CHAT_EXTRANET,
					C.PREV_MESSAGE_ID as CHAT_PREV_MESSAGE_ID,
					R.USER_ID as RID,
					R.MANAGER as IS_MANAGER
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

	/**
	 * @param int $currentUserId
	 * @param int $userId
	 * @param int $limit
	 * @param int $offset
	 * @return static[]
	 */
	public static function getSharedChatsWithUser(int $userId, int $limit = 50, int $offset = 0, ?int $currentUserId = null): array
	{
		$currentUserId ??= Im\V2\Entity\User\User::getCurrent()->getId();
		//todo: change with ChatCollection
		$chats = [];
		$types = [
			self::IM_TYPE_CHAT,
			self::IM_TYPE_OPEN,
			self::IM_TYPE_CHANNEL,
			self::IM_TYPE_OPEN_CHANNEL,
			self::IM_TYPE_COLLAB,
		];

		$recentCollection = Im\Model\RecentTable::query()
			->setSelect(['ITEM_ID', 'DATE_MESSAGE'])
			->registerRuntimeField(
				new Reference(
					'RELATION',
					RelationTable::class,
					Join::on('this.ITEM_ID', 'ref.CHAT_ID')
						->where('this.USER_ID', $currentUserId)
						->where('ref.USER_ID', $userId)
						->whereIn('this.ITEM_TYPE', $types),
					['join_type' => Join::TYPE_INNER]
				)
			)
			->setOrder(['DATE_MESSAGE' => 'DESC'])
			->setLimit($limit)
			->setOffset($offset)
			->fetchCollection()
		;

		foreach ($recentCollection as $recentItem)
		{
			$chat = self::getInstance($recentItem->getItemId());
			if ($chat instanceof Im\V2\Chat\NullChat)
			{
				continue;
			}
			$chat->dateMessage = $recentItem->getDateMessage();
			$chats[$chat->getId()] = $chat;
		}

		return $chats;
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

	public function filterUsersToMention(array $userIds): array
	{
		$result = [];
		$relations = $this->getRelationsByUserIds($userIds);

		foreach ($userIds as $userId)
		{
			$relation = $relations->getByUserId($userId, $this->getChatId());
			if (
				$relation !== null
				&& $relation->getNotifyBlock()
				&& \CIMSettings::GetNotifyAccess($userId, 'im', 'mention', \CIMSettings::CLIENT_SITE)
			)
			{
				$result[$userId] = $userId;
			}
		}

		return $result;
	}

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
	 * @see \Bitrix\Im\V2\Message::getContextId
	 * @param string $contextId
	 * @param int|null $userId
	 * @return string
	 */
	public static function getDialogIdByContextId(string $contextId, ?int $userId = null): string
	{
		$userId ??= Locator::getContext()->getUserId();

		[$dialogContextId] = explode('/', $contextId);
		if (str_starts_with($dialogContextId, 'chat'))
		{
			return $dialogContextId;
		}

		$userIds = explode(':', $dialogContextId);

		foreach ($userIds as $contextUserId)
		{
			if ((int)$contextUserId !== $userId)
			{
				return $contextUserId;
			}
		}

		return '0';
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

	public function getCounterType(): CounterType
	{
		return CounterType::tryFromChat($this);
	}

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

	public function getColor(bool $forRest = false): ?string
	{
		if ($forRest)
		{
			$color = $this->color ?? '';
			return $color !== '' ? Color::getColor($color) : Color::getColorByNumber($this->getId());
		}

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

	public function getAvatar(bool $addBlankPicture = false, bool $withDomain = false): string
	{
		return (new Im\V2\Entity\File\ChatAvatar($this))->get($addBlankPicture, $withDomain);
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

	protected function setDiskFolder(?Folder $folder): void
	{
		$this->isDiskFolderFilled = true;
		$this->diskFolder = $folder;
	}

	public function getOrCreateDiskFolder(): ?Folder
	{
		$folder = $this->getDiskFolder();

		if ($folder === null)
		{
			$folder = $this->createDiskFolder();
			$this->setDiskFolder($folder);
		}

		return $folder;
	}

	public function getStorageId(): int
	{
		return (int)\Bitrix\Main\Config\Option::get('im', 'disk_storage_id', 0);
	}

	public function getDiskFolder(): ?Folder
	{
		if (!Main\Loader::includeModule('disk'))
		{
			return null;
		}

		if ($this->isDiskFolderFilled)
		{
			return $this->diskFolder;
		}

		$diskFolderId = $this->getDiskFolderId();
		$folder = null;

		if ($diskFolderId !== null && $diskFolderId !== 0)
		{
			$folder = \Bitrix\Disk\Folder::getById($diskFolderId);
			if (!($folder instanceof Folder) || (int)$folder->getStorageId() !== \CIMDisk::GetStorageId($this->chatId))
			{
				$folder = null;
			}
		}

		$this->setDiskFolder($folder);

		return $folder;
	}

	protected function createDiskFolder(): ?Folder
	{
		$storage = \CIMDisk::GetStorage($this->chatId);

		if (!$storage)
		{
			return null;
		}

		$folderModel = $storage->addFolder(
			[
				'NAME' => "chat{$this->getId()}",
				'CREATED_BY' => $this->getContext()->getUserId(),
			],
			$this->getAccessCodesForDiskFolder(),
			true
		);

		if ($folderModel)
		{
			$this->setDiskFolderId($folderModel->getId())->save();
			$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;
			$accessProvider->updateChatCodesByRelations($this->getId());
		}

		return $folderModel;
	}

	protected function getAccessCodesForDiskFolder(): array
	{
		$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;
		$driver = \Bitrix\Disk\Driver::getInstance();
		$rightsManager = $driver->getRightsManager();
		$accessCodes = [];
		// allow for access code `CHATxxx`
		$accessCodes[] = [
			'ACCESS_CODE' => $accessProvider->generateAccessCode($this->getId()),
			'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_EDIT)
		];

		return $accessCodes;
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

		return $this->messageCount ?? 0;
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

	public function getLastMessageId(): ?int
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
	public function getRelations(): RelationCollection
	{
		return $this->getRelationFacade()?->get() ?? new RelationCollection();
	}

	public function getRelationFacade(): ?Im\V2\Relation\ChatRelations
	{
		if ($this->getId())
		{
			$this->chatRelations ??= Im\V2\Relation\ChatRelations::getInstance($this->getId());
		}

		return $this->chatRelations;
	}

	public function getRelationsByUserIds(array $userIds): RelationCollection
	{
		return $this->getRelationFacade()?->getByUserIds($userIds) ?? new RelationCollection();
	}

	public function getRelationByReason(Reason $reason): RelationCollection
	{
		return $this->getRelationFacade()?->getByReason($reason) ?? new RelationCollection();
	}

	public function setRelations(RelationCollection $relations): self
	{
		$this->getRelationFacade()?->forceRelations($relations);

		return $this;
	}

	public function getSelfRelation(): ?Relation
	{
		return $this->getRelationFacade()?->getByUserId($this->getContext()->getUserId());
	}

	public function getRelationByUserId(int $userId): ?Relation
	{
		return $this->getRelationFacade()?->getByUserId($userId);
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

	public function isNew(): bool
	{
		return false;
	}

	public function checkTitle(): Result
	{
		return new Result;
	}

	/**
	 * @param string $manageUsersAdd MEMBER|OWNER|MANAGERS
	 * @return self
	 */
	public function setManageUsersAdd(string $manageUsersAdd): self
	{
		$manageUsersAdd = mb_strtoupper($manageUsersAdd);
		if (!in_array(
			$manageUsersAdd,
			[self::MANAGE_RIGHTS_MEMBER, self::MANAGE_RIGHTS_OWNER, self::MANAGE_RIGHTS_MANAGERS],
			true
		))
		{
			$manageUsersAdd = $this->getDefaultManageUsersAdd();
		}
		$this->manageUsersAdd = $manageUsersAdd ;

		return $this;
	}

	public function getManageUsersAdd(): ?string
	{
		return $this->manageUsersAdd;
	}


	public function getDefaultManageUsersAdd(): string
	{
		return self::MANAGE_RIGHTS_MEMBER;
	}

	/**
	 * @param string $manageUsersDelete MEMBER|OWNER|MANAGERS
	 * @return self
	 */
	public function setManageUsersDelete(string $manageUsersDelete): self
	{
		$manageUsersDelete = mb_strtoupper($manageUsersDelete);
		if (!in_array(
			$manageUsersDelete,
			[self::MANAGE_RIGHTS_MEMBER, self::MANAGE_RIGHTS_OWNER, self::MANAGE_RIGHTS_MANAGERS],
			true
		))
		{
			$manageUsersDelete = $this->getDefaultManageUsersDelete();
		}
		$this->manageUsersDelete = $manageUsersDelete ;

		return $this;
	}

	public function getManageUsersDelete(): ?string
	{
		return $this->manageUsersDelete;
	}


	public function getDefaultManageUsersDelete(): string
	{
		return self::MANAGE_RIGHTS_MANAGERS;
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
	 * @deprecated
	 * @see self::setManageMessages()
	 * @param string $canPost
	 * @return $this
	 */
	public function setCanPost(string $canPost): self
	{
		return $this->setManageMessages($canPost);
	}

	/**
	 * @param string $manageMessages ALL|OWNER|MANAGER
	 * @return self
	 */
	public function setManageMessages(string $manageMessages): self
	{
		$manageMessages = mb_strtoupper($manageMessages);
		if (!in_array(
			$manageMessages,
			[
				self::MANAGE_RIGHTS_NONE,
				self::MANAGE_RIGHTS_MEMBER,
				self::MANAGE_RIGHTS_OWNER,
				self::MANAGE_RIGHTS_MANAGERS
			],
			true
		))
		{
			$manageMessages = $this->getDefaultManageMessages();
		}
		$this->manageMessages = $manageMessages;

		return $this;
	}

	public function getManageMessages(): ?string
	{
		return $this->manageMessages;
	}

	public function getDefaultManageMessages(): string
	{
		return self::MANAGE_RIGHTS_MEMBER;
	}

	public static function getCanPostList(): array
	{
		return [
			self::MANAGE_RIGHTS_NONE => Loc::getMessage('IM_CHAT_CAN_POST_NONE'),
			self::MANAGE_RIGHTS_MEMBER => Loc::getMessage('IM_CHAT_CAN_POST_ALL_MSGVER_1'),
			self::MANAGE_RIGHTS_OWNER => Loc::getMessage('IM_CHAT_CAN_POST_OWNER_MSGVER_1'),
			self::MANAGE_RIGHTS_MANAGERS => Loc::getMessage('IM_CHAT_CAN_POST_MANAGERS_MSGVER_1')
		];
	}
	//endregion

	public function createChatIfNotExists(array $params): self
	{
		return $this;
	}

	public function join(bool $withMessage = true): self
	{
		$config = new AddUsersConfig(hideHistory: false, withMessage: $withMessage);

		return $this->addUsers([$this->getContext()->getUserId()], $config);
	}

	/**
	 * @param array $userIds
	 * @return self
	 */
	public function addUsers(array $userIds, AddUsersConfig $config = new AddUsersConfig()): self
	{
		if (empty($userIds) || !$this->getChatId())
		{
			return $this;
		}

		$validUsers = $this->getValidUsersToAdd($userIds);
		$usersToAdd = $this->resolveRelationConflicts($validUsers, $config->getReason());

		if (empty($usersToAdd))
		{
			return $this;
		}

		$relations = $this->getRelations();
		if (!$config->isFakeAdd())
		{
			$this->addUsersToRelation($usersToAdd, $config);
			$this->updateStateAfterUsersAdd($usersToAdd)->save();
		}
		$this->sendPushUsersAdd($usersToAdd, $relations);
		if ($config->withMessage())
		{
			$this->sendMessageUsersAdd($usersToAdd, $config);
		}
		$this->sendEventUsersAdd($usersToAdd);

		return $this;
	}

	protected function resolveRelationConflicts(array $userIds, Reason $reason = Reason::DEFAULT): array
	{
		if (empty($userIds))
		{
			return [];
		}

		$usersToAdd = $conflictUsers = [];
		$usersAlreadyInChat = $this->getRelations()->getUserIds();

		foreach ($userIds as $userId)
		{
			if (!isset($usersAlreadyInChat[$userId]))
			{
				$usersToAdd[$userId] = $userId;
			}
			else
			{
				$conflictUsers[$userId] = $userId;
			}
		}

		if ($reason !== Reason::DEFAULT)
		{
			$this->updateRelationsAfterSync($conflictUsers);
		}

		return $usersToAdd;
	}

	protected function updateRelationsAfterSync(array $userIds): void
	{
		$relations = $this->getRelations();

		foreach ($userIds as $userId)
		{
			$relations->getByUserId($userId, $this->getId())
				?->setReason(Reason::STRUCTURE)
			;
		}

		$relations->save();
	}

	protected function sendMessageUsersAdd(array $usersToAdd, AddUsersConfig $config): void
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
			$type = $this instanceof Im\V2\Chat\ChannelChat ? 'CHANNEL' : 'CHAT';
			$code = "IM_{$type}_JOIN_{$currentUser->getGender()}";
			$messageText = Loc::getMessage(
				$code,
				[
					'#USER_1_NAME#' => htmlspecialcharsback($currentUser->getName()),
					'#USER_2_NAME#' => $userCodesString
				]
			);
		}

		$params = [
			"CODE" => 'CHAT_JOIN',
			"NOTIFY" => $this->getEntityType() === self::ENTITY_TYPE_LINE? 'Y': 'N',
		];

		if ($config->isFakeAdd())
		{
			$params['FAKE_RELATION'] = (int)array_shift($usersToAdd);
		}

		\CIMChat::AddMessage([
			"TO_CHAT_ID" => $this->getId(),
			"MESSAGE" => $messageText,
			"FROM_USER_ID" => $currentUserId,
			"SYSTEM" => 'Y',
			"RECENT_ADD" => $config->skipRecent() ? 'N' : 'Y',
			"PARAMS" => $params,
			"PUSH" => 'N',
			"SKIP_USER_CHECK" => 'Y',
		]);
	}

	protected function sendPushUsersAdd(array $usersToAdd, RelationCollection $oldRelations): void
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return;
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
				'userCount' => $this->getUserCount(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		$this->sendPushOnChangeUsers($oldRelations, $pushMessage);
	}

	protected function updateStateAfterUsersAdd(array $usersToAdd): self
	{
		if (!($this->getExtranet() ?? false))
		{
			foreach ($usersToAdd as $userId)
			{
				if (Im\V2\Entity\User\User::getInstance($userId)->getType() === UserType::EXTRANET)
				{
					$this->setExtranet(true);
					break;
				}

				$this->clearLegacyCache((int)$userId);
			}
		}

		$userCount = $this->getRelationFacade()?->getUserCount();

		$this->setUserCount($userCount);

		\CIMDisk::ChangeFolderMembers($this->getId(), $usersToAdd);
		self::cleanAccessCache($this->getId());
		$this->updateIndex();

		return $this;
	}

	protected function addUsersToRelation(array $usersToAdd, AddUsersConfig $config): void
	{
		$usersToAdd = array_filter($usersToAdd);

		if (empty($usersToAdd))
		{
			return;
		}

		$relations = $this->getRelations();
		foreach ($usersToAdd as $userId)
		{
			$user = Im\V2\Entity\User\User::getInstance($userId);

			if ($user->isBot() && AIHelper::containsCopilotBot([$userId]))
			{
				$params = $this->getChatParams();
				if (isset($params))
				{
					$params->addParamByName(Chat\Param\Params::IS_COPILOT, true);
				}
			}

			$relation = $this->createRelation($userId, $config);
			$relations->add($relation);
		}
		$relations->save(true);
		$this->getRelationFacade()?->onAfterRelationAdd($usersToAdd);

		$chatAnalytics = new Im\V2\Analytics\ChatAnalytics($this);

		foreach ($usersToAdd as $userId)
		{
			$chatAnalytics->addAddUser();
		}
	}

	protected function createRelation(int $userId, AddUsersConfig $config): Relation
	{
		$hideHistory = $config->isHideHistory() ?? false;
		$hideHistory = (!static::EXTRANET_CAN_SEE_HISTORY && Im\V2\Entity\User\User::getInstance($userId)->isExtranet()) ? true : $hideHistory;

		$relation = new Relation();
		$relation
			->setChatId($this->getId())
			->setMessageType($this->getType())
			->setUserId($userId)
			->setLastId($this->getLastMessageId())
			->setStatus(\IM_STATUS_READ)
			->setReason($config->getReason())
			->fillRestriction($hideHistory, $this)
		;
		if ($config->isManager($userId))
		{
			$relation->setManager(true);
		}

		return $relation;
	}

	protected function getValidUsersToAdd(array $userIds): array
	{
		$userIds = Group::filterAddedUsersToChatBySonetRestriction($userIds, $this->getContext()->getUserId());

		if ($this->getContext()->getUser()->isExtranet())
		{
			$userIds = Im\Integration\Socialnetwork\Extranet::filterUserList($userIds) ?: [];
		}

		$usersToAdd = [];

		foreach ($userIds as $userId)
		{
			$userId = (int)$userId;
			if ($this->isValidToAdd($userId))
			{
				$usersToAdd[$userId] = $userId;
			}
		}

		return $usersToAdd;
	}

	protected function isValidToAdd(int $userId): bool
	{
		if ($userId <= 0)
		{
			return false;
		}

		$user = Im\V2\Entity\User\User::getInstance($userId);

		return $user->isExist() && $user->isActive();
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
			$converter = new Converter(Converter::TO_CAMEL | Converter::UC_FIRST);
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

	public function deleteUser(int $userId, DeleteUserConfig $config = new DeleteUserConfig()): Result
	{
		$relations = clone $this->getRelations();
		$userRelation = $this->getRelations()->getByUserId($userId, $this->getId());

		if ($userRelation === null)
		{
			return (new Result())->addError(new UserError(UserError::NOT_FOUND));
		}

		if (!$config->skipCheckReason() && $userRelation->getReason() !== Reason::DEFAULT)
		{
			return (new Result())->addError(new UserError(UserError::DELETE_FROM_STRUCTURE_SYNC));
		}

		if ($this->getAuthorId() === $userId)
		{
			$this->changeAuthor();
		}

		$userRelation->delete();
		$this->getRelationFacade()?->onAfterRelationDelete($userId);

		$this->updateStateAfterUserDelete($userId, $config)->save();
		$this->sendPushUserDelete($userId, $relations);
		$this->sendEventUserDelete($userId);
		$this->sendMessageUserDelete($userId, $config);
		$this->sendNotificationUserDelete($userId, $config);

		(new Im\V2\Analytics\ChatAnalytics($this))->addDeleteUser();

		return new Result();
	}

	protected function needToSendMessageUserDelete(): bool
	{
		return false;
	}

	protected function sendMessageUserDelete(int $userId, DeleteUserConfig $config): void
	{
		if (!$config->withMessage() || !$this->needToSendMessageUserDelete())
		{
			return;
		}
		if ($this->getEntityType() === 'ANNOUNCEMENT')
		{
			return;
		}

		$message = $this->getMessageUserDeleteText($userId);
		if ($message === '')
		{
			return;
		}

		\CIMChat::AddMessage($this->prepareMessageParamsFromUserDelete($message, $config->skipRecent()));
	}

	protected function prepareMessageParamsFromUserDelete(string $message, bool $skipRecent): array
	{
		return [
			'TO_CHAT_ID' => $this->getId(),
			'MESSAGE' => $message,
			'FROM_USER_ID' => $this->getContext()->getUserId(),
			'SYSTEM' => 'Y',
			'RECENT_ADD' => $skipRecent ? 'N' : 'Y',
			'PARAMS' => ['CODE' => 'CHAT_LEAVE', 'NOTIFY' => 'N'],
			'PUSH' => 'N',
			'SKIP_USER_CHECK' => 'Y',
		];
	}

	protected function sendNotificationUserDelete(int $userId, DeleteUserConfig $config): void
	{
		if (!$config->withNotification())
		{
			return;
		}

		if ($userId === $this->getContext()->getUserId() || $this->getContext()->getUserId() === 0)
		{
			return;
		}

		$gender = $this->getContext()->getUser()->getGender();
		$userName = $this->getContext()->getUser()->getName();
		$userName = "[USER={$this->getContext()->getUserId()}]{$userName}[/USER]";

		$notificationCallback = fn (?string $languageId = null) => Loc::getMessage(
			'IM_CHAT_KICK_NOTIFICATION_'. $gender,
			["#USER_NAME#" => $userName],
			$languageId
		);

		$notificationFields = [
			'TO_USER_ID' => $userId,
			'FROM_USER_ID' => 0,
			'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
			'NOTIFY_MODULE' => 'im',
			'NOTIFY_TITLE' => htmlspecialcharsback(\Bitrix\Main\Text\Emoji::decode($this->getTitle())),
			'NOTIFY_MESSAGE' => $notificationCallback,
		];
		CIMNotify::Add($notificationFields);
	}

	protected function getMessageUserDeleteText(int $userId): string
	{
		$currentUser = $this->getContext()->getUser();
		if (!$currentUser->isExist())
		{
			return '';
		}

		if ($this->getContext()->getUserId() === $userId)
		{
			return Loc::getMessage("IM_CHAT_LEAVE_{$currentUser->getGender()}", ['#USER_NAME#' => htmlspecialcharsback($currentUser->getName())]);
		}

		$user = Im\V2\Entity\User\User::getInstance($userId);

		return Loc::getMessage("IM_CHAT_KICK_{$currentUser->getGender()}", ['#USER_1_NAME#' => htmlspecialcharsback($currentUser->getName()), '#USER_2_NAME#' => htmlspecialcharsback($user->getName())]);
	}

	protected function updateStateAfterUserDelete(int $deletedUserId, DeleteUserConfig $config): self
	{
		\CIMContactList::DeleteRecent($this->getId(), true, $deletedUserId, $config->withoutRead());
		\Bitrix\Im\LastSearch::delete($this->getDialogId(), $deletedUserId);

		if ($this->getExtranet() ?? false)
		{
			$relations = $this->getRelations();
			$extranetOnly = $relations->filter(
				fn (Relation $relation) => $relation->getUser()->getType() === UserType::EXTRANET
			);
			$hasNoExtranetUsers = $extranetOnly->count() <= 0;
			if ($hasNoExtranetUsers)
			{
				$this->setExtranet(false);
			}
		}

		$userCount = $this->getRelationFacade()?->getUserCount();

		$this->setUserCount($userCount);

		\CIMDisk::ChangeFolderMembers($this->getId(), $deletedUserId, false);
		self::cleanAccessCache($this->getId());
		$this->updateIndex();

		if (Im\V2\Integration\AI\AIHelper::containsCopilotBot([$deletedUserId]))
		{
			$chatParams = $this->getChatParams();
			$chatParams?->deleteParam(Chat\Param\Params::IS_COPILOT);
		}

		$this->clearLegacyCache($deletedUserId);

		return $this;
	}

	protected function clearLegacyCache(int $userId): void
	{
		CIMContactList::CleanChatCache($userId);
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
			$converter = new Converter(Converter::TO_CAMEL | Converter::UC_FIRST);
			$eventCode = $converter->process($this->getEntityType());
			foreach(GetModuleEvents("im", "OnChatUserDeleteEntityType".$eventCode, true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array([
					'CHAT_ID' => $this->getId(),
					'USER_ID' => $userId,
				]));
			}
		}

		$eventParams = ['chatId' => $this->getId(), 'userIds' => [$userId]];
		$event = new Main\Event('im', 'OnChatUserDelete', $eventParams);
		$event->send();
	}

	protected function sendPushUserDelete(int $userId, RelationCollection $oldRelations): void
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return;
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
				'userCount' => $this->getUserCount(),
				'chatExtranet' => $this->getExtranet() ?? false,
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		$this->sendPushOnChangeUsers($oldRelations, $pushMessage);
	}

	protected function sendPushOnChangeUsers(RelationCollection $relations, array $pushMessage): void
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return;
		}

		$userIds = $relations->getUserIds();

		\Bitrix\Pull\Event::add(array_values($userIds), $pushMessage);

		if ($this->needToSendPublicPull())
		{
			\CPullWatch::AddToStack('IM_PUBLIC_' . $this->getId(), $pushMessage);
		}
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

		$relations = $this->getRelations();

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

	public function getLoadContextMessage(bool $ignoreMark = false): Message
	{
		if (!$ignoreMark)
		{
			$startMessageId = $this->getMarkedId() ?: $this->getLastId();
		}
		else
		{
			$startMessageId = $this->getLastId();
		}

		return (new \Bitrix\Im\V2\Message($startMessageId))->setChatId($this->getId())->setMessageId($startMessageId);
	}

	public function fillNonCachedData(): self
	{
		if ($this->isFilledNonCachedData)
		{
			return $this;
		}

		$this->fillActual(ChatFactory::NON_CACHED_FIELDS);
		$this->isFilledNonCachedData = true;

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'chat';
	}

	public function getEntityLink(): Im\V2\Chat\EntityLink
	{
		return Im\V2\Chat\EntityLink::getInstance($this);
	}

	public function getPermissions(): array
	{
		return [
			'manageUsersAdd' => mb_strtolower($this->getManageUsersAdd()),
			'manageUsersDelete' => mb_strtolower($this->getManageUsersDelete()),
			'manageUi' => mb_strtolower($this->getManageUI()),
			'manageSettings' => mb_strtolower($this->getManageSettings()),
			'manageMessages' => mb_strtolower($this->getManageMessages()),
			'canPost' => mb_strtolower($this->getManageMessages()),
		];
	}

	public function toRestFormat(array $option = []): array
	{
		$commonFields = [
			'avatar' => $this->getAvatar(),
			'color' => $this->getColor(true),
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
			'parentChatId' => $this->getParentChatId(),
			'parentMessageId' => $this->getParentMessageId(),
			'name' => $this->getTitle(),
			'owner' => (int)$this->getAuthorId(),
			'messageType' => $this->getType(),
			'role' => mb_strtolower($this->getRole()),
			'muteList' => $this->getMuteList(),
			'type' => $this->getExtendedType(),
			'entityLink' => $this->getEntityLink()->toRestFormat($option),
			'permissions' => $this->getPermissions(),
			'isNew' => $this->isNew(),
		];
		if ($option['CHAT_WITH_DATE_MESSAGE'] ?? false)
		{
			$commonFields['dateMessage'] = $this->dateMessage;
		}
		if ($option['CHAT_SHORT_FORMAT'] ?? false)
		{
			return $commonFields;
		}

		$additionalFields = [
			'counter' => $this->getReadService()->getCounterService()->getByChat($this->getChatId()),
			'dateCreate' => $this->getDateCreate() === null ? null : $this->getDateCreate()->format('c'),
			'lastMessageId' => $this->getLastMessageId(),
			'lastMessageViews' => Im\Common::toJson($this->getLastMessageViews()),
			'lastId' => $this->getLastId(),
			'managerList' => $this->getManagerList(),
			'markedId' => $this->getMarkedId(),
			'messageCount' => $this->getMessageCount(),
			'public' => $this->getPublicOption() ?? '',
			'unreadId' => $this->getUnreadId(),
			'userCounter' => $this->getUserCount(),
		];

		return array_merge($commonFields, $additionalFields);
	}

	public function toPullFormat(): array
	{
		return [
			'id' => $this->getId(),
			'dialogId' => $this->getDialogId(),
			'parent_chat_id' => $this->getParentChatId(),
			'parent_message_id' => $this->getParentMessageId(),
			'name' => \Bitrix\Im\Text::decodeEmoji($this->getTitle()),
			'owner' => $this->getAuthorId(),
			'color' => $this->getColor(true),
			'extranet' => $this->getExtranet() ?? false,
			'avatar' => $this->getAvatar(true),
			'message_count' => $this->getMessageCount(),
			'call' => $this->getCallType(),
			'call_number' => $this->getCallNumber(),
			'entity_type' => $this->getEntityType(),
			'entity_id' => $this->getEntityId(),
			'entity_data_1' => $this->getEntityData1(),
			'entity_data_2' => $this->getEntityData2(),
			'entity_data_3' => $this->getEntityData3(),
			'public' => $this->getPublicOption() ?? '',
			'mute_list' => $this->getMuteList(true),
			'manager_list' => $this->getManagerList(),
			'date_create' => $this->getDateCreate(),
			'type' => $this->getExtendedType(),
			'entity_link' => $this->getEntityLink()->toRestFormat(),
			'permissions' => $this->getPermissions(),
			'isNew' => $this->isNew(),
			'message_type' => $this->getType(),
			'ai_provider' => null,
			'description' => \Bitrix\Im\Text::decodeEmoji($this->getDescription() ?? ''),
		];
	}

	public function getMultidialogData(): array
	{
		return [];
	}

	protected function getManagerList(): array
	{
		return array_values($this->getRelationFacade()?->getManagerOnly()->getUserIds() ?? []);
	}

	protected function getMuteList(bool $fullList = false): array
	{
		if ($fullList)
		{
			$list = [];
			foreach ($this->getRelations() as $relation)
			{
				$list[$relation->getUserId()] = $relation->getNotifyBlock();
			}

			return $list;
		}

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

	public function getExtendedType(bool $forRest = true): string
	{
		return Im\Chat::getType(
			['ID' => $this->getId(), 'TYPE' => $this->getType(), 'ENTITY_TYPE' => $this->getEntityType()],
			$forRest
		);
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
			return $this->getLastMessageId();
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
		$relations = RelationCollection::find(['CHAT_ID' => $this->getId()], limit: 100);

		$users = [];
		foreach ($relations as $relation)
		{
			$users[] = $relation->getUser()->getName() ?? '';
		}

		return $users;
	}

	/**
	 * @throws \Exception
	 */
	public function deleteChat(): Result
	{
		$result = new Result();

		if (!$this->chatId)
		{
			return $result->addError(new ChatError(ChatError::NOT_FOUND));
		}

		$currentUserId = Entity\User\User::getCurrent()->getId();

		Application::getInstance()->addBackgroundJob(
			fn () => (new Im\V2\Chat\Cleanup\ChatContentCollector($this->chatId))
				->deleteChat($currentUserId)
		);

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
					'chatId' => $this->getId(),
					'lines' => $this->getType() === self::IM_TYPE_OPEN_LINE,
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return $result;
	}

	public function sendMessageUpdateAvatar(bool $skipRecent = false): void
	{
		$currentUser = $this->getContext()->getUser();
		$type = $this instanceof Im\V2\Chat\ChannelChat ? 'CHANNEL' : 'CHAT';
		$code = "IM_{$type}_AVATAR_CHANGE_{$currentUser->getGender()}";

		$messageText = Loc::getMessage(
			$code,
			['#USER_NAME#' => htmlspecialcharsback($currentUser->getName())]
		);

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
				"NOTIFY" => $this->getEntityType() === 'LINES' ? 'Y': 'N',
			],
			"PUSH" => 'N',
			"SKIP_USER_CHECK" => "Y",
		]);
	}

	public function needToSendPublicPull(): bool
	{
		return false;
	}

	public function checkAllowedAction(string $action): bool
	{
		$options = \CIMChat::GetChatOptions();
		$entityType = $this->getEntityType();

		$defaultAllowed = (bool)($chatOptions['DEFAULT'][$action] ?? true);

		if (isset($entityType, $options[$entityType]))
		{
			return (bool)($chatOptions[$entityType][$action] ?? $defaultAllowed);
		}

		return $defaultAllowed;
	}

	public function canDo(Action $action, mixed $target = null): bool
	{
		$userRights = $this->getRole();
		$action = Im\V2\Permission::specifyAction($action, $this, $target);

		$rightByType = Im\V2\Permission::getRoleForActionByType($this->getExtendedType(false), $action);
		$actionGroup = Im\V2\Permission\ActionGroup::tryFromAction($action);

		$manageRights = match ($actionGroup)
		{
			Permission\ActionGroup::ManageUi => $this->getManageUI(),
			Permission\ActionGroup::ManageUsersAdd => $this->getManageUsersAdd(),
			Permission\ActionGroup::ManageUsersDelete => $this->getManageUsersDelete(),
			Permission\ActionGroup::ManageSettings => $this->getManageSettings(),
			Permission\ActionGroup::ManageMessages => $this->getManageMessages(),
			default => Chat::ROLE_GUEST,
		};

		return Im\V2\Permission::compareRole($userRights, $manageRights)
			&& Im\V2\Permission::compareRole($userRights, $rightByType)
		;
	}

	public static function updateStateAfterOrmEvent(int $id, array $fields): void
	{
		$chat = self::$chatStaticCache[$id];
		$chat?->onAfterOrmUpdate($fields);
	}
}
