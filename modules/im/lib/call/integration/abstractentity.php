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

	abstract public function onUserAdd($userId);

	abstract public function onStateChange($state, $prevState);

	public function toArray($currentUserId = 0)
	{
		if($currentUserId == 0)
		{
			$currentUserId = $this->initiatorId;
		}
		return [
			'type' => $this->getEntityType(),
			'id' => $this->getEntityId($currentUserId),
			'name' => $this->getName($currentUserId)
		];
	}

	protected function getCurrentUserId()
	{
		global $USER;

		return (int)$USER->getId();
	}
}