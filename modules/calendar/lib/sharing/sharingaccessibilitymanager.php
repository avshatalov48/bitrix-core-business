<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Managers\Accessibility;

class SharingAccessibilityManager
{
	/** @var array  */
	private array $userIds;
	/** @var int  */
	private int $timestampFrom;
	/** @var int  */
	private int $timestampTo;

	/**
	 * @param $options
	 */
	public function __construct($options)
	{
		$this->userIds = $options['userIds'];
		$this->timestampFrom = $options['timestampFrom'];
		$this->timestampTo = $options['timestampTo'];
	}

	/**
	 * @return bool
	 */
	public function checkUsersAccessibility(): bool
	{
		$busyUserIds = (new Accessibility())
			->setCheckPermissions(false)
			->getBusyUsersIds($this->userIds, $this->timestampFrom, $this->timestampTo)
		;

		return empty($busyUserIds);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function getUsersAccessibilitySegmentsInUtc(): array
	{
		$accessibility = (new Accessibility())
			->setCheckPermissions(false)
			->getAccessibility($this->userIds, $this->timestampFrom, $this->timestampTo)
		;

		return array_merge(...$accessibility);
	}
}