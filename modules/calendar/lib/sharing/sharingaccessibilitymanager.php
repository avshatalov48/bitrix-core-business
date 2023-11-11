<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Managers\Accessibility;

class SharingAccessibilityManager
{
	/** @var int  */
	private int $userId;
	/** @var int  */
	private int $timestampFrom;
	/** @var int  */
	private int $timestampTo;

	/**
	 * @param $options
	 */
	public function __construct($options)
	{
		$this->userId = $options['userId'];
		$this->timestampFrom = $options['timestampFrom'];
		$this->timestampTo = $options['timestampTo'];
	}

	/**
	 * @return bool
	 */
	public function checkUserAccessibility(): bool
	{
		$busyUserIds = (new Accessibility())
			->setCheckPermissions(false)
			->getBusyUsersIds([$this->userId], $this->timestampFrom, $this->timestampTo)
		;

		return empty($busyUserIds);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function getUserAccessibilitySegmentsInUtc(): array
	{
		$accessibility = (new Accessibility())
			->setCheckPermissions(false)
			->getAccessibility([$this->userId], $this->timestampFrom, $this->timestampTo)
		;

		return $accessibility[$this->userId];
	}
}