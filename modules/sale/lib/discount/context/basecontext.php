<?php
namespace Bitrix\Sale\Discount\Context;

use Bitrix\Main;

abstract class BaseContext
{
	const GUEST_USER_ID = 0;

	/** @var int */
	protected $userId;
	/** @var array */
	protected $userGroups = array();

	protected $userGroupsHash = '';

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @return array
	 */
	public function getUserGroups()
	{
		return $this->userGroups;
	}

	public function getUserGroupsHash()
	{
		return $this->userGroupsHash;
	}

	protected function setUserGroups(array $userGroups)
	{
		Main\Type\Collection::normalizeArrayValuesByInt($userGroups, true);
		$this->userGroups = $userGroups;
		$this->userGroupsHash = md5(serialize($this->userGroups));
	}
}