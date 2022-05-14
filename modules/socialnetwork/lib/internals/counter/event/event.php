<?php

namespace Bitrix\Socialnetwork\Internals\Counter\Event;

/**
 * Class Event
 *
 * @package Bitrix\Socialnetwork\Internals\Counter\Event
 */

class Event
{
	/* @var string $type */
	private $type;
	/* @var array $data */
	private $data = [];

	/**
	 * CounterEvent constructor.
	 * @param string $type
	 */
	public function __construct(string $type = '')
	{
		$this->type = $type;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data = []): self
	{
		$this->data = $this->prepareData($data);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		$userId = 0;

		switch ($this->type)
		{
			case EventDictionary::EVENT_WORKGROUP_DELETE:
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$userId = (int)$this->data['USER_ID'];
				break;
		}

		return $userId;
	}

	/**
	 * @return int
	 */
	public function getGroupId(): int
	{
		$groupId = 0;

		switch ($this->type)
		{
			case EventDictionary::EVENT_WORKGROUP_DELETE:
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$groupId = (int)$this->data['GROUP_ID'];
				break;
		}

		return $groupId;
	}

	/**
	 * @return array
	 */
	public function getUsedRoles(): array
	{
		$rolesList = [];

		switch ($this->type)
		{
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
				$rolesList[] = $this->data['ROLE'];
				break;
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
				$rolesList[] = $this->data['ROLE_OLD'];
				$rolesList[] = $this->data['ROLE_NEW'];
				break;
		}

		return $rolesList;
	}

	/**
	 * @return array
	 */
	public function getInitiatedByType(): string
	{
		$initiatedByType = null;

		switch ($this->type)
		{
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
			$initiatedByType = $this->data['INITIATED_BY_TYPE'];
				break;
		}

		return $initiatedByType;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function prepareData(array $data = []): array
	{
		$validFields = [
			'USER_ID',
			'GROUP_ID',
			'ROLE',
			'ROLE_OLD',
			'ROLE_NEW',
			'INITIATED_BY_TYPE',
			'RELATION_ID',
		];

		foreach ($data as $key => $row)
		{
			if (!in_array($key, $validFields, true))
			{
				unset($data[$key]);
			}
		}

		return $data;
	}
}
