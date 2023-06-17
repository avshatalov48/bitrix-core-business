<?php

namespace Bitrix\Socialnetwork\Internals\EventService;

/**
 * Class Event
 *
 * @package Bitrix\Socialnetwork\Internals\EventService\Event
 */

class Event
{
	/* @var string $type */
	protected $type;
	/* @var array $data */
	protected $data = [];

	/**
	 * Event constructor.
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
		$this->collectOldData();

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
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$userId = (int) ($this->data['USER_ID'] ?? 0);
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
			case EventDictionary::EVENT_WORKGROUP_ADD:
			case EventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_DELETE:
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$groupId = (int) ($this->data['GROUP_ID'] ?? 0);
				break;
		}

		return $groupId;
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
	 * @return string
	 */
	public function getRelationKey(): string
	{
		$result = '';

		switch ($this->type)
		{
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$result = ($this->data['GROUP_ID'] ?? 0) . '_'. ($this->data['USER_ID'] ?? 0);
				break;
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function prepareData(array $data = []): array
	{
		return [];
	}

	protected function getChanges($entityId): array
	{
		$oldFields = $this->getOldFields()[$entityId];
		$newFields = $this->getNewFields()[$entityId];

		$changes = [];

		foreach ($newFields as $key => $value)
		{
			if (mb_strpos($key, '~') === 0)
			{
				continue;
			}

			if (isset($oldFields[$key]) && $oldFields[$key] !== $value)
			{
				$changes[$key] = $value;
			}
		}

		return $changes;
	}

	protected function collectOldData(): void
	{
	}

	protected function collectNewData(): void
	{
	}

	protected function setOldFields($oldFields): void
	{
	}

	protected function getOldFields(): array
	{
		return [];
	}

	protected function setNewFields($newFields): void
	{
	}

	protected function getNewFields(): array
	{
		return [];
	}
}
