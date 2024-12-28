<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Event;

/**
 * Class Event
 *
 * @package Bitrix\Socialnetwork\Internals\EventService\Event\WorkgroupEvent
 */

class WorkgroupEvent extends \Bitrix\Socialnetwork\Internals\EventService\Event
{
	private static array $fields = [];

	private array $oldFields = [];
	private array $newFields = [];

	public function getOldFields(): array
	{
		return $this->oldFields;
	}

	public function getNewFields(): array
	{
		return $this->newFields;
	}

	public function setData(array $data = []): self
	{
		$this->data = $this->prepareData($data);

		$this->collectOldData();

		return $this;
	}

	private function collectOldData(): void
	{
		$groupId = $this->getGroupId();

		if (
			$groupId
			&& empty($this->oldFields)
		)
		{
			$this->oldFields = $this->getGroupFields($groupId);
		}
	}

	public function collectNewData(): void
	{
		$groupId = $this->getGroupId();
		if (
			$groupId
			&& empty($this->newFields)
		)
		{
			$this->newFields = $this->getGroupFields($groupId);
		}
	}

	private function getGroupFields(int $groupId): array
	{
		if (isset(self::$fields[$groupId]))
		{
			return self::$fields[$groupId];
		}

		$fields = \CSocNetGroup::getById($groupId);

		self::$fields[$groupId] = is_array($fields) ? $fields : [];

		return self::$fields[$groupId];
	}
}
