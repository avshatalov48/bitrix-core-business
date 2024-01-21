<?php

namespace Bitrix\Calendar\Sharing\Crm;

use Bitrix\Calendar\Sharing\Link\CrmDealLink;
use Bitrix\Calendar\Sharing\Link\Helper;
use Bitrix\Crm\Integration\Calendar\ActivityHandler;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class ActivityManager
{
	public const STATUS_MEETING_NOT_HELD = 'meeting_not_held';
	public const STATUS_CANCELED_BY_MANAGER = 'canceled_by_manager';
	public const STATUS_CANCELED_BY_CLIENT = 'canceled_by_client';

	private int $eventId;
	private ?CrmDealLink $link;
	private ?string $guestName;

	/**
	 * @param int $eventId
	 * @param CrmDealLink|null $link
	 * @param string|null $guestName
	 */
	public function __construct(int $eventId, ?CrmDealLink $link = null, ?string $guestName = null)
	{
		$this->eventId = $eventId;
		$this->link = $link;
		$this->guestName = $guestName;
	}

	/**
	 * creates crm activity for calendar sharing
	 * @param string $activityName
	 * @param string|null $description
	 * @param DateTime $eventStart
	 * @return \Bitrix\Main\Result|null
	 */
	public function createCalendarSharingActivity(
		string $activityName,
		?string $description,
		DateTime $eventStart
	): ?\Bitrix\Main\Result
	{
		if (!$this->isAvailable())
		{
			return null;
		}

		$fields = [
			'SUBJECT' => $activityName,
			'DESCRIPTION' => $description,
			'RESPONSIBLE_ID' => $this->link->getOwnerId(),
			'CALENDAR_EVENT_ID' => $this->eventId,
			'BINDINGS' => $this->getBindings(),
			'SETTINGS' => $this->getSettings(),
			'END_TIME' => $eventStart,
		];

		return (new \Bitrix\Crm\Activity\Provider\CalendarSharing())
			->createActivity(\Bitrix\Crm\Activity\Provider\CalendarSharing::PROVIDER_TYPE_ID, $fields)
		;
	}

	/**
	 * completes activity for calendar sharing
	 * @param string|null $status
	 * @return bool
	 */
	public function completeSharedCrmActivity(?string $status): bool
	{
		if (!$this->isAvailable())
		{
			return false;
		}

		$activity = \CCrmActivity::GetByCalendarEventId($this->eventId, false);

		if (!$activity)
		{
			return false;
		}

		$crmStatus = null;
		switch ($status)
		{
			case self::STATUS_MEETING_NOT_HELD:
				$crmStatus = ActivityHandler::SHARING_STATUS_MEETING_NOT_HELD;
				break;
			case self::STATUS_CANCELED_BY_MANAGER:
				$crmStatus = ActivityHandler::SHARING_STATUS_CANCELED_BY_MANAGER;
				break;
			case self::STATUS_CANCELED_BY_CLIENT:
				$crmStatus = ActivityHandler::SHARING_STATUS_CANCELED_BY_CLIENT;
				break;
		}

		(new ActivityHandler($activity, $activity['OWNER_TYPE_ID'], $activity['OWNER_ID']))
			->completeWithStatus($crmStatus);

		return true;
	}

	/**
	 * if activity exists, launching activity deadline update
	 *
	 * @param DateTime $deadline
	 * @return bool
	 */
	public function editActivityDeadline(DateTime $deadline): bool
	{
		if (!$this->isAvailable())
		{
			return false;
		}

		$activity = \CCrmActivity::GetByCalendarEventId($this->eventId, false);

		if (!$activity)
		{
			return false;
		}

		(new ActivityHandler($activity, $activity['OWNER_TYPE_ID'], $activity['OWNER_ID']))
			->updateDeadline($deadline)
		;

		return true;
	}

	/**
	 * @return bool
	 */
	private function isAvailable(): bool
	{
		return Loader::includeModule('crm') === true
			&& \Bitrix\Crm\Integration\Calendar\Helper::isSharingCrmAvaible()
		;
	}

	/**
	 * @return array
	 */
	private function getBindings(): array
	{
		$result = [];
		$entityTypeId = null;

		switch ($this->link->getObjectType())
		{
			case Helper::CRM_DEAL_SHARING_TYPE:
				$entityTypeId = \CCrmOwnerType::Deal;
				break;
		}

		$result[] = [
			'OWNER_TYPE_ID' => $entityTypeId,
			'OWNER_ID' => $this->link->getObjectId(),
		];

		if ($this->link->getContactType() && $this->link->getContactId())
		{
			$result[] = [
				'OWNER_TYPE_ID' => $this->link->getContactType(),
				'OWNER_ID' => $this->link->getContactId(),
			];
		}

		return $result;
	}

	/**
	 * @return array|null[]|string[]
	 */
	private function getSettings(): array
	{
		$result = [];

		if ($this->link->getContactType() && $this->link->getContactId())
		{
			$result = [
				'CONTACT_TYPE_ID' => $this->link->getContactType(),
				'CONTACT_ID' => $this->link->getContactId(),
			];
		}
		elseif ($this->guestName)
		{
			$result = [
				'GUEST_NAME' => $this->guestName,
			];
		}

		$result['LINK_ID'] = $this->link->getId();

		return $result;
	}
}