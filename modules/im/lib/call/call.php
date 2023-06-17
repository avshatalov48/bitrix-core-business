<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Call\Integration\EntityFactory;
use Bitrix\Im\Call\Integration\EntityType;
use Bitrix\Im\Dialog;
use Bitrix\Im\Model\AliasTable;
use Bitrix\Im\Model\CallTable;
use Bitrix\Im\Model\CallUserTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Event;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\JWT;

class Call
{
	const STATE_NEW = 'new';
	const STATE_INVITING = 'inviting';
	const STATE_ANSWERED = 'answered';
	const STATE_FINISHED = 'finished';

	const TYPE_INSTANT = 1;
	const TYPE_PERMANENT = 2;

	const RECORD_TYPE_VIDEO = 'video';
	const RECORD_TYPE_AUDIO = 'audio';

	const PROVIDER_PLAIN = 'Plain';
	const PROVIDER_VOXIMPLANT = 'Voximplant';

	protected $id;
	protected $type;
	protected $initiatorId;
	protected $isPublic = false;
	protected $publicId;
	protected $provider;
	protected $entityType;
	protected $entityId;
	protected $parentId;
	protected $state;
	protected $startDate;
	protected $endDate;
	protected $logUrl;
	protected $chatId;

	/** @var Integration\AbstractEntity */
	protected $associatedEntity = null;
	/** @var CallUser[] */
	protected $users;

	/** @var Signaling */
	protected $signaling;

	/**
	 * Use one of the named constructors
	 */
	protected function __construct()
	{
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return (int)$this->id;
	}

	/**
	 * @return string
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * @return int
	 */
	public function getInitiatorId()
	{
		return $this->initiatorId;
	}

	/**
	 * @param $userId
	 * @return CallUser|null
	 */
	public function getUser($userId)
	{
		$this->loadUsers();
		return isset($this->users[$userId]) ? $this->users[$userId] : null;
	}

	/**
	 * Returns arrays of ids of the users, currently participating in the call.
	 * @return int[]
	 */
	public function getUsers()
	{
		$this->loadUsers();
		return array_keys($this->users);
	}

	/**
	 * Return true if a user is the part of the call.
	 *
	 * @param int $userId Id of the user.
	 * @return bool
	 */
	public function hasUser($userId)
	{
		$this->loadUsers();
		return isset($this->users[$userId]);
	}

	/**
	 * Adds new user to the call.
	 *
	 * @param int $newUserId
	 * @return CallUser|false
	 */
	public function addUser($newUserId)
	{
		$this->loadUsers();
		if($this->users[$newUserId])
		{
			return $this->users[$newUserId];
		}

		if(count($this->users) >= $this->getMaxUsers())
		{
			return false;
		}

		$this->users[$newUserId] = CallUser::create([
			'CALL_ID' => $this->id,
			'USER_ID' => $newUserId,
			'STATE' => CallUser::STATE_IDLE,
			'LAST_SEEN' => null
		]);
		$this->users[$newUserId]->save();

		if($this->associatedEntity)
		{
			$this->associatedEntity->onUserAdd($newUserId);
		}

		return $this->users[$newUserId];
	}

	public function removeUser($userId)
	{
		$this->loadUsers();
		if($this->users[$userId])
		{
			CallUser::delete($this->id, $userId);
			unset($this->users[$userId]);
		}
	}

	/**
	 * Call is considered active if it has at least:
	 *  - one user in ready state
	 *  - another user in ready or calling state
	 * @return bool
	 */
	public function hasActiveUsers(bool $strict = true)
	{
		$this->loadUsers();
		$states = [];

		foreach ($this->users as $userId => $user)
		{
			$userState = $user->getState();
			$states[$userState] = isset($states[$userState]) ? $states[$userState] + 1 : 1;
		}
		if($this->type == static::TYPE_PERMANENT || !$strict)
		{
			 return $states[CallUser::STATE_READY] >= 1;
		}
		else
		{
			return $states[CallUser::STATE_READY] >= 2 || ($states[CallUser::STATE_READY] >= 1 && $states[CallUser::STATE_CALLING] >= 1);
		}
	}

	public function getSignaling()
	{
		if(is_null($this->signaling))
		{
			$this->signaling = new Signaling($this);
		}

		return $this->signaling;
	}

	/**
	 * @return Integration\AbstractEntity|null
	 */
	public function getAssociatedEntity()
	{
		return $this->associatedEntity;
	}

	public function setAssociatedEntity($entityType, $entityId)
	{
		$entity = EntityFactory::createEntity($this, $entityType, $entityId);

		if(!$entity)
		{
			throw new ArgumentException("Unknown entity " . $entityType . "; " . $entityId);
		}

		$this->associatedEntity = $entity;
		$this->entityType = $entityType;
		$this->entityId = $entityId;
		$this->save();

		$this->getSignaling()->sendAssociatedEntityReplaced($this->getCurrentUserId());
	}

	/**
	 * Returns true if specified user has access to the call.
	 *
	 * @param int $userId Id of the user.
	 * @return bool
	 */
	public function checkAccess($userId)
	{
		return in_array($userId, $this->getUsers());
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return int|false
	 */
	public function getParentId()
	{
		return $this->parentId;
	}

	/**
	 * Returns id of the chat, associated with the call.
	 *
	 * @return int
	 */
	public function getChatId()
	{
		return $this->chatId;
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
	 * @param string $state
	 */
	public function updateState($state)
	{
		if($this->state == $state)
		{
			return false;
		}
		$prevState = $this->state;
		$this->state = $state;
		$updateResult = CallTable::updateState($this->getId(), $state);
		if(!$updateResult)
		{
			return false;
		}

		if($this->associatedEntity)
		{
			$this->associatedEntity->onStateChange($state, $prevState);
		}
		return true;
	}

	public function setLogUrl(string $logUrl)
	{
		$this->logUrl = $logUrl;
	}

	public function finish()
	{
		if($this->endDate instanceof DateTime)
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
		}
	}

	public function toArray($currentUserId = 0)
	{
		return array(
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
		);
	}

	public function save()
	{
		$fields = $this->toArray();
		unset($fields['ID']);

		if(!$this->id)
		{
			$insertResult = CallTable::add($fields);
			$this->id = $insertResult->getId();
		}
		else
		{
			$updateResult = CallTable::update($this->id, $fields);
		}
	}

	public function makeClone($newProvider = null)
	{
		$instance = static::createWithArray($this->toArray());
		$instance->id = null;
		$instance->publicId = randString(10);
		$instance->state = static::STATE_NEW;
		if($newProvider)
		{
			$instance->provider = $newProvider;
		}
		$instance->parentId = $this->id;

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

	protected function loadUsers()
	{
		if(is_array($this->users))
			return;

		$this->users = array();

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

	public function getMaxUsers()
	{
		if ($this->provider == static::PROVIDER_VOXIMPLANT)
		{
			return static::getMaxCallServerParticipants();
		}

		return (int)Option::get('im', 'turn_server_max_users');
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

	public static function getMaxParticipants()
	{
		if (static::isCallServerEnabled())
		{
			return static::getMaxCallServerParticipants();
		}
		else
		{
			return Option::get('im', 'turn_server_max_users');
		}
	}

	public static function getMaxCallServerParticipants()
	{
		if(Loader::includeModule('bitrix24'))
		{
			return (int)\Bitrix\Bitrix24\Feature::getVariable('im_max_call_participants');
		}
		return (int)Option::get('im', 'call_server_max_users');
	}

	/**
	 * Use this constructor only for creating new calls
	 */
	public static function createWithEntity($type, $provider, $entityType, $entityId, $initiatorId)
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
		$instance->chatId = $instance->associatedEntity->getChatId();

		$instance->save();

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

		$event = new Event(
			'im',
			'onCallCreate',
			array(
				'id' => $instance->id,
				'type' => $instance->type,
				'initiatorId' => $instance->initiatorId,
				'provider' => $instance->provider,
				'entityType' => $instance->entityType,
				'entityId' => $instance->entityId,
				'startDate' => $instance->startDate,
				'publicId' => $instance->publicId,
				'chatId' => $instance->chatId,
			)
		);
		$event->send();

		return $instance;
	}

	/**
	 * @param string $type
	 * @param string $provider
	 * @param string $entityType
	 * @param string $entityId
	 * @param int $currentUserId
	 * @return Call|null
	 *
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function searchActive($type, $provider, $entityType, $entityId, $currentUserId = 0)
	{
		if (!$currentUserId)
		{
			$currentUserId = \Bitrix\Im\User::getInstance()->getId();
		}
		$query = CallTable::query()
			->addSelect("*")
			->where("TYPE", $type)
			->where("PROVIDER", $provider)
			->where("ENTITY_TYPE", $entityType)
			->whereNull("END_DATE")
			->setOrder(["ID" => "DESC"])
			->setLimit(1);

		if ($entityType === EntityType::CHAT && strpos($entityId, "chat") !== 0)
		{
			$query->where("INITIATOR_ID", $entityId);
			$query->where("ENTITY_ID", $currentUserId);
		}
		else
		{
			$query->where("ENTITY_ID", $entityId);
		}

		$callFields = $query->exec()->fetch();

		if(!$callFields)
		{
			return null;
		}

		$instance = static::createWithArray($callFields);
		if($instance->hasActiveUsers(false))
		{
			return $instance;
		}
		return null;
	}

	/**
	 * Creates new instance of the Call with values from the database.
	 *
	 * @param array $fields Call fields
	 * @return Call
	 */
	public static function createWithArray(array $fields)
	{
		$instance = new static();

		$instance->id = $fields['ID'];
		$instance->type = (int)$fields['TYPE'];
		$instance->initiatorId = $fields['INITIATOR_ID'];
		$instance->isPublic = $fields['IS_PUBLIC'];
		$instance->publicId = $fields['PUBLIC_ID'];
		$instance->provider = $fields['PROVIDER'];
		$instance->entityType = $fields['ENTITY_TYPE'];
		$instance->entityId = $fields['ENTITY_ID'];
		$instance->startDate = isset ($fields['START_DATE']) && $fields['START_DATE'] instanceof DateTime ? $fields['START_DATE'] : null;
		$instance->endDate = isset ($fields['END_DATE']) && $fields['END_DATE'] instanceof DateTime ? $fields['END_DATE'] : null;
		$instance->parentId = (int)$fields['PARENT_ID'] ?: null;
		$instance->state = $fields['STATE'];
		$instance->logUrl = $fields['LOG_URL'];
		$instance->chatId = $fields['CHAT_ID'];

		if($instance->entityType && $instance->entityId)
		{
			$instance->associatedEntity = Integration\EntityFactory::createEntity($instance, $instance->entityType, $instance->entityId);
		}

		return $instance;
	}

	/**
	 * Loads instance of the Call from the database using call's public id.
	 *
	 * @param string $publicId
	 * @return Call|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function createWithPublicId($publicId)
	{
		$row = CallTable::getRow([
			'filter' => [
				'=PUBLIC_ID' => $publicId
			]
		]);

		if(is_array($row))
		{
			return static::createWithArray($row);
		}
		else
		{
			return false;
		}
	}

	public static function loadWithId($id)
	{
		$row = CallTable::getRowById($id);

		if(is_array($row))
		{
			return static::createWithArray($row);
		}
		else
		{
			return false;
		}
	}

	public static function isCallServerEnabled()
	{
		if(Loader::includeModule("bitrix24"))
		{
			return true;
		}
		if(!ModuleManager::isModuleInstalled("voximplant"))
		{
			return false;
		}

		return (bool)Option::get("im", "call_server_enabled");
	}

	protected function getCurrentUserId() : int
	{
		return $GLOBALS['USER'] ? (int)$GLOBALS['USER']->getId() : 0;
	}

	public static function onVoximplantConferenceFinished(Event $event)
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