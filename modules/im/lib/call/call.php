<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Call\Integration\EntityFactory;
use Bitrix\Im\Call\Integration\EntityType;
use Bitrix\Im\Dialog;
use Bitrix\Im\Model\AliasTable;
use Bitrix\Im\Model\CallTable;
use Bitrix\Im\Model\CallUserTable;
use Bitrix\Im\V2\Call\CallFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Event;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\JWT;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

class Call
{
	public const
		STATE_NEW = 'new',
		STATE_INVITING = 'inviting',
		STATE_ANSWERED = 'answered',
		STATE_FINISHED = 'finished'
	;

	public const
		TYPE_INSTANT = 1,
		TYPE_PERMANENT = 2,
		TYPE_LANGE = 3
	;

	public const
		PROVIDER_PLAIN = 'Plain',
		PROVIDER_BITRIX = 'Bitrix',
		PROVIDER_VOXIMPLANT = 'Voximplant'
	;

	protected $id;
	protected $type;
	protected $initiatorId;
	protected ?int $actionUserId = null;
	protected $isPublic = false;
	protected $publicId;
	protected $provider;
	protected $entityType;
	protected $entityId;
	protected $parentId;
	protected $state;
	/** @var DateTime|null */
	protected $startDate;
	/** @var DateTime|null */
	protected $endDate;
	protected $logUrl;
	protected $chatId;
	protected $uuid;
	protected $secretKey;
	protected $endpoint;

	/**
	 * Current record status
	 */
	protected bool $enableAudioRecord = false;

	/**
	 * Record will be analyzed with AI
	 */
	protected bool $enableAiAnalyze = false;

	protected ?Integration\AbstractEntity $associatedEntity = null;

	/** @var CallUser[] */
	protected $users;
	protected $userData;

	protected ?Signaling $signaling = null;

	protected ?ErrorCollection $errorCollection = null;

	/**
	 * Use one of the named constructors
	 */
	protected function __construct()
	{
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return (int)$this->id;
	}

	/**
	 * @return int
	 */
	public function getType(): int
	{
		return (int)$this->type;
	}

	/**
	 * @return string
	 */
	public function getProvider(): string
	{
		return $this->provider;
	}

	/**
	 * @return int
	 */
	public function getInitiatorId(): int
	{
		return $this->initiatorId;
	}

	/**
	 * @return int|null
	 */
	public function getActionUserId(): ?int
	{
		return $this->actionUserId;
	}

	//region Errors

	/**
	 * Add multiple errors
	 * @param Error[] $errors
	 */
	public function addErrors(array $errors): void
	{
		if (!$this->errorCollection instanceof ErrorCollection)
		{
			$this->errorCollection = new ErrorCollection();
		}
		$this->errorCollection->add($errors);
	}

	/**
	 * @return Error[]
	 */
	public function getErrors(): array
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return $this->errorCollection->toArray();
		}

		return [];
	}

	/**
	 * Upends stack of errors.
	 * @param Error $error Error message object.
	 * @return void
	 */
	public function addError(Error $error): void
	{
		if (!$this->errorCollection instanceof ErrorCollection)
		{
			$this->errorCollection = new ErrorCollection();
		}
		$this->errorCollection->add([$error]);
	}

	/**
	 * Tells true if error have happened.
	 * @return boolean
	 */
	public function hasErrors(): bool
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return !$this->errorCollection->isEmpty();
		}

		return false;
	}

	//endregion

	//region Users

	public function setActionUserId(int $byUserId): self
	{
		$this->actionUserId = $byUserId;
		return $this;
	}

	/**
	 * @param int $userId
	 * @return CallUser|null
	 */
	public function getUser($userId): ?CallUser
	{
		$this->loadUsers();
		return isset($this->users[$userId]) ? $this->users[$userId] : null;
	}

	/**
	 * Returns arrays of ids of the users, currently participating in the call.
	 * @return int[]
	 */
	public function getUsers(): array
	{
		$this->loadUsers();
		return array_keys($this->users);
	}

	/**
	 * Returns arrays of the users, currently participating in the call.
	 * @return CallUser[]
	 */
	public function getCallUsers(): array
	{
		$this->loadUsers();
		return $this->users;
	}

	/**
	 * Returns arrays of information about the users currently participating in the call.
	 * @return array
	 */
	public function getUserData(): array
	{
		if (!isset($this->userData))
		{
			$this->userData = Util::getUsers($this->getUsers());
		}

		return $this->userData;
	}

	/**
	 * Return true if a user is the part of the call.
	 *
	 * @param int $userId Id of the user.
	 * @return bool
	 */
	public function hasUser($userId): bool
	{
		$this->loadUsers();
		return isset($this->users[$userId]);
	}

	/**
	 * Adds new user to the call.
	 *
	 * @param int $newUserId
	 * @return CallUser|null
	 */
	public function addUser($newUserId): ?CallUser
	{
		$this->loadUsers();
		if ($this->users[$newUserId])
		{
			return $this->users[$newUserId];
		}

		if (count($this->users) >= $this->getMaxUsers())
		{
			return null;
		}

		$this->users[$newUserId] = CallUser::create([
			'CALL_ID' => $this->id,
			'USER_ID' => $newUserId,
			'STATE' => CallUser::STATE_IDLE,
			'LAST_SEEN' => null
		]);
		$this->users[$newUserId]->save();
		unset($this->userData);

		if ($this->associatedEntity)
		{
			$this->associatedEntity->onUserAdd($newUserId);
		}

		return $this->users[$newUserId];
	}

	public function removeUser($userId): void
	{
		$this->loadUsers();
		if($this->users[$userId])
		{
			CallUser::delete($this->id, $userId);
			unset($this->users[$userId]);
			unset($this->userData[$userId]);
		}
	}

	/**
	 * Call is considered active if it has at least:
	 *  - one user in ready state
	 *  - another user in ready or calling state
	 * @return bool
	 */
	public function hasActiveUsers(bool $strict = true): bool
	{
		$this->loadUsers();
		$states = [];

		foreach ($this->users as $user)
		{
			$userState = $user->getState();
			$states[$userState] = isset($states[$userState]) ? $states[$userState] + 1 : 1;
		}
		if (in_array($this->type, [static::TYPE_PERMANENT, static::TYPE_LANGE]) || !$strict)
		{
			 return $states[CallUser::STATE_READY] >= 1;
		}

		return $states[CallUser::STATE_READY] >= 2 || ($states[CallUser::STATE_READY] >= 1 && $states[CallUser::STATE_CALLING] >= 1);
	}

	//endregion

	public function getSignaling(): Signaling
	{
		if (is_null($this->signaling))
		{
			$this->signaling = new Signaling($this);
		}

		return $this->signaling;
	}

	/**
	 * @return Integration\AbstractEntity|null
	 */
	public function getAssociatedEntity(): ?Integration\AbstractEntity
	{
		return $this->associatedEntity;
	}

	/**
	 * @param string $entityType
	 * @param int $entityId
	 * @return void
	 * @throws ArgumentException
	 */
	public function setAssociatedEntity($entityType, $entityId): void
	{
		$entity = EntityFactory::createEntity($this, $entityType, $entityId);
		if (!$entity)
		{
			throw new ArgumentException("Unknown entity " . $entityType . "; " . $entityId);
		}

		$this->associatedEntity = $entity;
		$this->entityType = $entityType;
		$this->entityId = $entityId;
		if ($entityType == EntityType::CHAT)
		{
			$this->chatId = $entity->getChatId();
		}
		$this->save();

		$this->getSignaling()->sendAssociatedEntityReplaced($this->getCurrentUserId());
	}

	/**
	 * Returns true if specified user has access to the call.
	 *
	 * @param int $userId Id of the user.
	 * @return bool
	 */
	public function checkAccess($userId): bool
	{
		if (in_array($userId, $this->getUsers()))
		{
			return true;
		}
		if ($this->getAssociatedEntity()?->checkAccess($userId))
		{
			return true;
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function getState(): string
	{
		return $this->state;
	}

	/**
	 * @return int|null
	 */
	public function getParentId(): ?int
	{
		return $this->parentId;
	}

	/**
	 * Returns id of the chat, associated with the call.
	 *
	 * @return int
	 */
	public function getChatId(): int
	{
		return $this->chatId;
	}

	public function getUuid()
	{
		return $this->uuid;
	}

	public function getSecretKey()
	{
		return $this->secretKey;
	}

	public function getEndpoint()
	{
		return $this->endpoint;
	}

	/**
	 * Returns date of the call start.
	 *
	 * @return DateTime
	 */
	public function getStartDate(): DateTime
	{
		return $this->startDate;
	}

	/**
	 * Returns date of the call end (if there is one).
	 *
	 * @return DateTime|null
	 */
	public function getEndDate(): ?DateTime
	{
		return $this->endDate;
	}

	/**
	 * Returns call duration.
	 * @return int
	 */
	public function getDuration(): int
	{
		if ($this->startDate)
		{
			$end = $this->endDate ?? new DateTime();
			return $end->getTimestamp() - $this->startDate->getTimestamp();
		}
		return -1;
	}

	/**
	 * Do need to record call.
	 * @return bool
	 */
	public function autoStartRecording(): bool
	{
		// by settings or tariif
		$enable = false;

		if (
			\Bitrix\Call\Integration\AI\CallAISettings::isCallAIEnable()
			&& \Bitrix\Call\Integration\AI\CallAISettings::isAutoStartRecordingEnable()
		)
		{
			// by user limit
			$minUserCount = (int)\Bitrix\Call\Integration\AI\CallAISettings::getRecordMinUsers();
			if ($minUserCount > 0)
			{
				$userCount = $this->getUserCount();
				if ($userCount && $userCount >= $minUserCount)
				{
					$enable = true;
				}
			}
		}

		return $enable;
	}

	public function getUserCount(): int
	{
		$userCount = 0;
		if ($this->associatedEntity)
		{
			$userCount = count($this->associatedEntity->getUsers());
		}
		if (!$userCount && $this->id)
		{
			$this->loadUsers();
			$userCount = count($this->users);
		}

		return $userCount;
	}

	/**
	 * Record call.
	 * @return bool
	 */
	public function isAudioRecordEnabled(): bool
	{
		return $this->enableAudioRecord;
	}

	/**
	 * Do need to record call.
	 * @return self
	 */
	public function enableAudioRecord(): self
	{
		$this->enableAudioRecord = true;
		return $this;
	}

	/**
	 * Disable record call.
	 * @return self
	 */
	public function disableAudioRecord(): self
	{
		$this->enableAudioRecord = false;
		return $this;
	}

	/**
	 * Analyze call with AI enabled.
	 * @return bool
	 */
	public function isAiAnalyzeEnabled(): bool
	{
		return $this->enableAiAnalyze;
	}

	/**
	 * Do AI analyze.
	 * @return self
	 */
	public function enableAiAnalyze(): self
	{
		$this->enableAiAnalyze = true;
		return $this;
	}

	/**
	 * Disable AI analyze.
	 * @return self
	 */
	public function disableAiAnalyze(): self
	{
		$this->enableAiAnalyze = false;
		return $this;
	}

	public function inviteUsers(int $senderId, array $toUserIds, $isLegacyMobile, $video = false, $sendPush = true): void
	{
		$this->getSignaling()->sendInvite(
			$senderId,
			$toUserIds,
			$isLegacyMobile,
			$video,
			$sendPush
		);
	}

	/**
	 * @param string $state
	 */
	public function updateState($state): bool
	{
		if ($this->state == $state)
		{
			return false;
		}
		$prevState = $this->state;
		$this->state = $state;
		$updateResult = CallTable::updateState($this->getId(), $state);
		if (!$updateResult)
		{
			return false;
		}

		if ($this->associatedEntity)
		{
			$this->associatedEntity->onStateChange($state, $prevState);
		}

		return true;
	}

	public function setLogUrl(string $logUrl): void
	{
		$this->logUrl = $logUrl;
	}

	public function setEndpoint($endpoint): void
	{
		$this->endpoint = $endpoint;
	}

	public function finish(): void
	{
		if ($this->endDate instanceof DateTime)
		{
			return;
		}

		$this->endDate = new DateTime();

		if ($this->updateState(static::STATE_FINISHED))
		{
			$this->loadUsers();
			foreach ($this->users as $callUser)
			{
				if ($callUser->getState() === CallUser::STATE_CALLING)
				{
					$callUser->updateState(CallUser::STATE_IDLE);
				}
			}
			$this->getSignaling()->sendFinish();
			$this->saveStat();

			$this->fireCallFinishedEvent();
		}
	}

	/**
	 * @event call:onCallStarted
	 * @return Event
	 */
	protected function fireCallStartedEvent(): Event
	{
		$event = new Event('call', 'onCallStarted', ['call' => $this]);
		$event->send();

		return $event;
	}

	/**
	 * @event call:onCallFinished
	 * @return Event
	 */
	protected function fireCallFinishedEvent(): Event
	{
		$event = new Event('call', 'onCallFinished', ['call' => $this]);
		$event->send();

		return $event;
	}

	public function getConnectionData(int $userId): ?array
	{
		return null;
	}

	public function toArray($currentUserId = 0, $withSecrets = false): array
	{
		$result = [
			'ID' => $this->id,
			'TYPE' => $this->type,
			'INITIATOR_ID' => $this->initiatorId,
			'IS_PUBLIC' => $this->isPublic ? 'Y' : 'N',
			'PUBLIC_ID' => $this->publicId,
			'PROVIDER' => $this->provider,
			'ENTITY_TYPE' => $this->entityType,
			'ENTITY_ID' => $this->entityId,
			'PARENT_ID' => $this->parentId,
			'STATE' => $this->state,
			'START_DATE' => $this->startDate,
			'END_DATE' => $this->endDate,
			'LOG_URL' => $this->logUrl,
			'CHAT_ID' => $this->chatId,
			'ASSOCIATED_ENTITY' => ($this->associatedEntity) ? $this->associatedEntity->toArray($currentUserId) : [],
			'UUID' => $this->uuid,
			'ENDPOINT' => $this->endpoint,
			'RECORD_AUDIO' => $this->enableAudioRecord,
			'AI_ANALYZE' => $this->enableAiAnalyze,
		];
		if ($withSecrets)
		{
			$result['SECRET_KEY'] = $this->secretKey;
		}

		return $result;
	}

	public function save(): void
	{
		$fields = $this->toArray(0, true);
		unset($fields['ID']);

		if (!$this->id)
		{
			$insertResult = CallTable::add($fields);
			$this->id = $insertResult->getId();
		}
		else
		{
			CallTable::update($this->id, $fields);
		}
	}

	public function makeClone($newProvider = null): Call
	{
		$callFields = $this->toArray();
		$callFields['ID'] = null;
		$callFields['PUBLIC_ID'] = randString(10);
		$callFields['STATE'] = static::STATE_NEW;
		$callFields['PROVIDER'] = $newProvider ?? $callFields['PROVIDER'];
		$callFields['PARENT_ID'] = $this->id;

		$instance = CallFactory::createWithArray($callFields['PROVIDER'], $callFields);
		$instance->save();

		$instance->users = [];
		foreach ($this->getUsers() as $userId)
		{
			$instance->users[$userId] = CallUser::create([
				'CALL_ID' => $instance->id,
				'USER_ID' => $userId,
				'STATE' => $instance->users[$userId] ? $instance->users[$userId]->getState() : CallUser::STATE_IDLE,
				'LAST_SEEN' => null
			]);
			$instance->users[$userId]->save();
		}

		return $instance;
	}

	protected function loadUsers(): void
	{
		if (is_array($this->users))
		{
			return;
		}

		$this->users = [];

		$cursor = CallUserTable::getList(array(
			'filter' => array(
				'=CALL_ID' => $this->id
			)
		));

		while($row = $cursor->fetch())
		{
			$this->users[$row['USER_ID']] = CallUser::create($row);
		}
	}

	protected function saveStat()
	{
		$callLength = 0;
		if ($this->startDate instanceof DateTime && $this->endDate instanceof DateTime)
		{
			$callLength = $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
		}
		$userCountChat = count($this->users);

		$usersActive = 0;
		$mobileUsers = 0;
		$externalUsers = 0;
		$screenShared = false;
		$recorded = false;
		$authTypes = UserTable::getList([
			'select' => ['ID', 'EXTERNAL_AUTH_ID'],
			'filter' => ['=ID' => $this->getUsers()]
		])->fetchAll();
		$authTypes = array_column($authTypes, 'EXTERNAL_AUTH_ID', 'ID');
		foreach ($this->users as $userId => $user)
		{
			if ($user->getLastSeen() != null)
			{
				$usersActive++;
			}
			if ($user->isUaMobile())
			{
				$mobileUsers++;
			}
			if ($authTypes[$userId] === Auth::AUTH_TYPE)
			{
				$externalUsers++;
				if ($user->getFirstJoined())
				{
					$userLateness = $user->getFirstJoined()->getTimestamp() - $this->startDate->getTimestamp();
					AddEventToStatFile("im", "im_call_finish", $this->id, $userLateness, "user_lateness", $userId);
				}
			}
			if ($user->wasRecorded())
			{
				$recorded = true;
			}
			if ($user->wasRecorded())
			{
				$screenShared = true;
			}
		}

		$chatType = null;
		$finishStatus = 'normal';
		if ($this->entityType === EntityType::CHAT)
		{
			if(is_numeric($this->entityId))
			{
				$chatType = 'private';
				// private chat, entity id === other user id
				$otherUserState =
					$this->getUser($this->entityId)
						? $this->getUser($this->entityId)->getState()
						: ''
				;

				if ($otherUserState == CallUser::STATE_DECLINED)
				{
					$finishStatus = 'declined';
				}
				else if ($otherUserState == CallUser::STATE_BUSY)
				{
					$finishStatus = 'busy';
				}
				else if ($otherUserState == CallUser::STATE_UNAVAILABLE || $otherUserState == CallUser::STATE_CALLING)
				{
					$finishStatus = 'unavailable';
				}
			}
			else
			{
				$chatId = Dialog::getChatId($this->entityId);
				$isVideoConf = (bool)AliasTable::getRow([
					'filter' => ['=ENTITY_ID' => $chatId, '=ENTITY_TYPE' => \Bitrix\Im\Alias::ENTITY_TYPE_VIDEOCONF]
				]);
				$chatType = 'group';
			}
		}

		if ($callLength > 30 && $finishStatus === 'normal')
		{
			\Bitrix\Im\Limit::incrementCounter(\Bitrix\Im\Limit::COUNTER_CALL_SUCCESS);
		}

		AddEventToStatFile("im", "im_call_finish", $this->id, $userCountChat, "user_count_chat", 0);
		AddEventToStatFile("im", "im_call_finish", $this->id, $usersActive, "user_count_call", 0);
		AddEventToStatFile("im", "im_call_finish", $this->id, $mobileUsers, "user_count_mobile", 0);
		AddEventToStatFile("im", "im_call_finish", $this->id, $externalUsers, "user_count_external", 0);
		AddEventToStatFile("im", "im_call_finish", $this->id, $callLength, "call_length", 0);
		AddEventToStatFile("im", "im_call_finish", $this->id, ($screenShared ? "Y" : "N"), "screen_shared", 0);
		AddEventToStatFile("im", "im_call_finish", $this->id, ($recorded ? "Y" : "N"), "recorded", 0);
		if($chatType)
		{
			AddEventToStatFile("im","im_call_finish", $this->id, $chatType, "chat_type", 0);
		}
		if (isset($isVideoConf))
		{
			AddEventToStatFile("im","im_call_finish", $this->id, ($isVideoConf ? "Y" : "N"), "is_videoconf", 0);
		}
		AddEventToStatFile("im","im_call_finish", $this->id, $finishStatus, "status", 0);
	}

	public static function isFeedbackAllowed(): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::getPortalZone() == 'ru';
		}

		return Option::get('im', 'allow_call_feedback', 'N') === 'Y';
	}

	public function getMaxUsers(): int
	{
		return self::getMaxParticipants();
	}

	public function getLogToken(int $userId = 0, int $ttl = 3600) : string
	{
		$userId = $userId ?: $this->getCurrentUserId();
		if(!$userId)
		{
			return  '';
		}

		if (Loader::includeModule("bitrix24") && defined('BX24_HOST_NAME'))
		{
			$portalId = BX24_HOST_NAME;
		}
		else if (defined('IM_CALL_LOG_HOST'))
		{
			$portalId = IM_CALL_LOG_HOST;
		}
		else
		{
			return '';
		}

		$secret = Option::get('im', 'call_log_secret');
		if ($secret == '')
		{
			return '';
		}

		return JWT::encode(
			[
				'prt' => $portalId,
				'call' => $this->getId(),
				'usr' => $userId,
				'exp' => (new DateTime())->getTimestamp() + $ttl
			],
			$secret
		);
	}

	public static function getLogService() : string
	{
		return (string)Option::get('im', 'call_log_service');
	}

	public static function getMaxParticipants(): int
	{
		if (static::isCallServerEnabled())
		{
			return static::getMaxCallServerParticipants();
		}

		return (int)Option::get('im', 'turn_server_max_users');
	}

	public static function getMaxCallServerParticipants(): int
	{
		if (Loader::includeModule('bitrix24'))
		{
			return (int)\Bitrix\Bitrix24\Feature::getVariable('im_max_call_participants');
		}
		return (int)Option::get('im', 'call_server_max_users');
	}

	public static function getMaxCallLimit(): int
	{
		if (!\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return 0;
		}

		return (int)\Bitrix\Bitrix24\Feature::getVariable('im_call_extensions_limit');
	}

	/**
	 * Use this constructor only for creating new calls
	 */
	public static function createWithEntity(int $type, string $provider, string $entityType, string $entityId, int $initiatorId): Call
	{
		$instance = new static();
		$instance->type = $type;
		$instance->initiatorId = $initiatorId;
		$instance->provider = $provider;
		$instance->entityType = $entityType;
		$instance->entityId = $entityId;
		$instance->startDate = new DateTime();
		$instance->publicId = randString(10);
		$instance->state = static::STATE_NEW;

		$instance->associatedEntity = Integration\EntityFactory::createEntity($instance, $entityType, $entityId);
		$instance->chatId = (int)$instance->associatedEntity->getChatId();

		$instance->enableAudioRecord = $instance->autoStartRecording();
		$instance->enableAiAnalyze = $instance->enableAudioRecord;

		$instance->save();

		// todo: remove when the calls are supported in the mobile
		$instance->associatedEntity->onCallCreate();

		$instance->users = [];
		foreach ($instance->associatedEntity->getUsers() as $userId)
		{
			$instance->users[$userId] = CallUser::create([
				'CALL_ID' => $instance->id,
				'USER_ID' => $userId,
				'STATE' => CallUser::STATE_UNAVAILABLE,
				'LAST_SEEN' => null
			]);
			$instance->users[$userId]->save();
		}


		$instance->initCall();

		$event = new Event(
			'im',
			'onCallCreate',
			[
				'id' => $instance->id,
				'type' => $instance->type,
				'initiatorId' => $instance->initiatorId,
				'provider' => $instance->provider,
				'entityType' => $instance->entityType,
				'entityId' => $instance->entityId,
				'startDate' => $instance->startDate,
				'publicId' => $instance->publicId,
				'chatId' => $instance->chatId,
			]
		);
		$event->send();

		return $instance;
	}

	protected function initCall(): void
	{
		$this->fireCallStartedEvent();
	}

	/**
	 * Creates new instance of the Call with values from the database.
	 *
	 * @param array $fields Call fields
	 * @return Call
	 */
	public static function createWithArray(array $fields): Call
	{
		$instance = new static();

		$instance->id = $fields['ID'];
		$instance->type = (int)$fields['TYPE'];
		$instance->initiatorId = (int)$fields['INITIATOR_ID'];
		$instance->isPublic = $fields['IS_PUBLIC'];
		$instance->publicId = $fields['PUBLIC_ID'];
		$instance->provider = $fields['PROVIDER'];
		$instance->entityType = $fields['ENTITY_TYPE'];
		$instance->entityId = $fields['ENTITY_ID'];
		$instance->startDate = isset($fields['START_DATE']) && $fields['START_DATE'] instanceof DateTime ? $fields['START_DATE'] : null;
		$instance->endDate = isset($fields['END_DATE']) && $fields['END_DATE'] instanceof DateTime ? $fields['END_DATE'] : null;
		$instance->parentId = (int)$fields['PARENT_ID'] ?: null;
		$instance->state = $fields['STATE'];
		$instance->logUrl = $fields['LOG_URL'];
		$instance->chatId = (int)$fields['CHAT_ID'];
		$instance->uuid = $fields['UUID'];
		$instance->secretKey = $fields['SECRET_KEY'];
		$instance->endpoint = $fields['ENDPOINT'];

		if ($instance->entityType && $instance->entityId)
		{
			$instance->associatedEntity = Integration\EntityFactory::createEntity($instance, $instance->entityType, $instance->entityId);
		}

		if (isset($fields['RECORD_AUDIO']))
		{
			$instance->enableAudioRecord = ($fields['RECORD_AUDIO'] === 'Y');
		}
		else
		{
			$instance->enableAudioRecord = $instance->autoStartRecording();
		}

		if (isset($fields['AI_ANALYZE']))
		{
			$instance->enableAiAnalyze = ($fields['AI_ANALYZE'] === 'Y');
		}
		else
		{
			$instance->enableAiAnalyze = $instance->enableAudioRecord;
		}

		$instance->initCall();

		return $instance;
	}

	public static function loadWithId($id): ?Call
	{
		$row = CallTable::getRowById($id);

		if (is_array($row))
		{
			return static::createWithArray($row);
		}

		return null;
	}

	public static function isCallServerEnabled(): bool
	{
		if (!Loader::includeModule('call'))
		{
			return false;
		}

		return (bool)Option::get("im", "call_server_enabled");
	}

	public static function isBitrixCallEnabled(): bool
	{
		return self::isCallServerEnabled();
	}

	public static function isIosBetaEnabled(): bool
	{
		$isEnabled = Option::get('im', 'call_beta_ios', 'N');

		return $isEnabled === 'Y';
	}

	protected function getCurrentUserId() : int
	{
		return $GLOBALS['USER'] ? (int)$GLOBALS['USER']->getId() : 0;
	}

	public static function onVoximplantConferenceFinished(Event $event): void
	{
		$callId = $event->getParameter('CONFERENCE_CALL_ID');
		$logUrl = $event->getParameter('LOG_URL');
		if (!$logUrl)
		{
			return;
		}

		$call = Call::loadWithId($callId);
		if (!$call)
		{
			return;
		}
		$call->finish();
		$call->setLogUrl($logUrl);
		$call->save();
	}
}