<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Call\Integration\EntityFabric;
use Bitrix\Im\Call\Integration\EntityType;
use Bitrix\Im\Dialog;
use Bitrix\Im\Model\CallTable;
use Bitrix\Im\Model\CallUserTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Event;
use Bitrix\Main\Web\JWT;

class Call
{
	const STATE_NEW = 'new';
	const STATE_INVITING = 'inviting';
	const STATE_ANSWERED = 'answered';
	const STATE_FINISHED = 'finished';

	const TYPE_INSTANT = 1;
	const TYPE_PERMANENT = 2;

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
	public function hasActiveUsers()
	{
		$this->loadUsers();
		$states = [];

		foreach ($this->users as $userId => $user)
		{
			$userState = $user->getState();
			$states[$userState] = isset($states[$userState]) ? $states[$userState] + 1 : 1;
		}
		if($this->type == static::TYPE_PERMANENT)
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
		$entity = EntityFabric::createEntity($this, $entityType, $entityId);

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
		foreach ($this->users as $user)
		{
			if ($user->getLastSeen() != null)
			{
				$usersActive++;
			}
			if ($user->isUaMobile())
			{
				$mobileUsers++;
			}
		}

		$chatType = null;
		$finishStatus = 'normal';
		if ($this->entityType == EntityType::CHAT)
		{
			if(is_numeric($this->entityId))
			{
				$chatType = 'private';
				// private chat, entity id === other user id
				$otherUserState = $this->getUser($this->entityId)->getState();
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
				$chatType = 'group';
			}
		}

		AddEventToStatFile("im", "im_call_finish", $this->id, $userCountChat, "user_count_chat");
		AddEventToStatFile("im", "im_call_finish", $this->id, $usersActive, "user_count_call");
		AddEventToStatFile("im", "im_call_finish", $this->id, $mobileUsers, "user_count_mobile");
		AddEventToStatFile("im", "im_call_finish", $this->id, $callLength, "call_length");
		if($chatType)
		{
			AddEventToStatFile("im","im_call_finish", $this->id, $chatType, "chat_type");
		}
		AddEventToStatFile("im","im_call_finish", $this->id, $finishStatus, "status");
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

		$instance->associatedEntity = Integration\EntityFabric::createEntity($instance, $entityType, $entityId);
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
	 * @return Call|null
	 *
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function searchActive($type, $provider, $entityType, $entityId)
	{
		$callFields = CallTable::getRow([
			'select' => ['*'],
			'filter' => [
				'=TYPE' => $type,
				'=PROVIDER' => $provider,
				'=ENTITY_TYPE' => $entityType,
				'=ENTITY_ID' => $entityId,
				'=END_DATE' => null
			],
			'order' => [
				'ID' => 'desc'
			],
		]);

		if(!$callFields)
		{
			return null;
		}

		$instance = static::createWithArray($callFields);
		if($instance->hasActiveUsers())
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
		$instance->startDate = $fields['START_DATE'];
		$instance->endDate = $fields['END_DATE'];
		$instance->parentId = $fields['PARENT_ID'];
		$instance->state = $fields['STATE'];
		$instance->logUrl = $fields['LOG_URL'];
		$instance->chatId = $fields['CHAT_ID'];

		if($instance->entityType && $instance->entityId)
		{
			$instance->associatedEntity = Integration\EntityFabric::createEntity($instance, $instance->entityType, $instance->entityId);
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
		$call->setLogUrl($logUrl);
		$call->save();
	}
}