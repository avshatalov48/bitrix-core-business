<?php

namespace Bitrix\Im\Call\Integration;

use Bitrix\Im\Call\Call;

abstract class AbstractEntity
{
	protected $entityId;
	protected $initiatorId;
	/** @var Call */
	protected $call;

	public function __construct(Call $call, $entityId)
	{
		$this->call = $call;
		$this->entityId = $entityId;
		$this->initiatorId = $call->getInitiatorId();
	}

	public function getEntityId($currentUserId = 0)
	{
		return $this->entityId;
	}

	/**
	 * @return Call
	 */
	public function getCall()
	{
		return $this->call;
	}

	/**
	 * @param Call $call
	 */
	public function setCall(Call $call)
	{
		$this->call = $call;
	}

	abstract public function getEntityType();

	abstract public function canStartCall(int $userId): bool;

	abstract public function checkAccess(int $userId): bool;

	abstract public function getChatId();

	abstract public function getUsers();

	abstract public function getName($currentUserId);

	// todo: remove when the calls are supported in the mobile
	abstract public function onCallCreate(): bool;

	abstract public function onUserAdd($userId): bool;

	abstract public function onExistingUsersInvite($userIds): bool;

	abstract public function onStateChange($state, $prevState);

	public function toArray($initiatorId = 0)
	{
		if($initiatorId == 0)
		{
			$initiatorId = $this->initiatorId;
		}
		return [
			'type' => $this->getEntityType(),
			'id' => $this->getEntityId($initiatorId),
			'name' => $this->getName($initiatorId)
		];
	}

	protected function getCurrentUserId()
	{
		global $USER;

		return (int)$USER->getId();
	}
}