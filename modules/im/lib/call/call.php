<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Call\Integration\EntityFabric;
use Bitrix\Im\Model\CallTable;
use Bitrix\Im\Model\CallUserTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\DateTime;

class Call
{
	const STATE_NEW = 'new';
	const STATE_INVITING = 'inviting';
	const STATE_ANSWERED = 'answered';
	const STATE_FINISHED = 'finished';

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
		return $this->id;
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
			if($states[$user->getState()])
			{
				$states[$user->getState()]++;
			}
			else
			{
				$states[$user->getState()] = 1;
			}
		}
		return $states[CallUser::STATE_READY] >= 2 || ($states[CallUser::STATE_READY] >= 1 && $states[CallUser::STATE_CALLING] >= 1);
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
	public function setState($state)
	{
		if($this->state == $state)
		{
			return;
		}

		$this->state = $state;
		if($this->associatedEntity)
		{
			$this->associatedEntity->onStateChange($state);
		}
	}

	public function finish($code)
	{
		if($this->endDate instanceof DateTime)
		{
			return;
		}

		$this->endDate = new DateTime();
		$this->setState(static::STATE_FINISHED);
		$this->save();

		$this->signaling->sendFinish();
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

	protected function getMaxUsers()
	{
		return $this->provider == static::PROVIDER_VOXIMPLANT ? 10 : 4;
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

		$instance->save();

		$instance->users = [];
		foreach ($instance->associatedEntity->getUsers() as $userId)
		{
			$instance->users[$userId] = CallUser::create([
				'CALL_ID' => $instance->id,
				'USER_ID' => $userId,
				'STATE' => CallUser::STATE_IDLE,
				'LAST_SEEN' => null
			]);
			$instance->users[$userId]->save();
		}

		return $instance;
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
		$instance->type = $fields['TYPE'];
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

	public static function isCallServerEnabled()
	{
		if(ModuleManager::isModuleInstalled("bitrix24"))
		{
			return true;
		}
		if(!ModuleManager::isModuleInstalled("voximplant"))
		{
			return false;
		}

		return (bool)Option::get("im", "call_server_enabled");
	}

	protected function getCurrentUserId()
	{
		global $USER;

		return $USER->getId();
	}
}