<?php

namespace Bitrix\Im\V2\Entity\Calendar;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Type\DateTime;
use CCalendar;
use CCalendarEvent;

class CalendarItem implements RestEntity
{
	use ContextCustomer;

	protected ?int $id;
	protected int $ownerId;
	protected int $createdBy;
	protected string $title;
	protected string $type;
	protected DateTime $dateFrom;
	protected DateTime $dateTo;
	protected array $membersIds;

	public static function getRestEntityName(): string
	{
		return 'calendar';
	}

	public static function initByGetListArray(array $calendarInfo): self
	{
		$calendar = new static();

		if ($calendarInfo['DT_SKIP_TIME'] === 'Y')
		{
			$calendarSettings = CCalendar::GetSettings(['getDefaultForEmpty' => false]);
			$workTimeStart = explode('.', $calendarSettings['work_time_start']);
			$workTimeEnd = explode('.', $calendarSettings['work_time_end']);
			$dateFrom = new DateTime($calendarInfo['DATE_FROM']);
			$dateTo = new DateTime($calendarInfo['DATE_TO']);
			$dateFrom->setTime((int)$workTimeStart[0], (int)($workTimeStart[1] ?? 0));
			$dateTo->setTime((int)$workTimeEnd[0], (int)($workTimeEnd[1] ?? 0));
		}
		else
		{
			$dateFrom = DateTime::createFromTimestamp($calendarInfo['DATE_FROM_TS_UTC'] + (int)date('Z'));
			$dateTo = DateTime::createFromTimestamp($calendarInfo['DATE_TO_TS_UTC'] + (int)date('Z'));
		}
		$calendar
			->setId($calendarInfo['ID'])
			->setTitle($calendarInfo['NAME'])
			->setType($calendarInfo['CAL_TYPE'])
			->setOwnerId((int)$calendarInfo['OWNER_ID'])
			->setCreatedBy((int)$calendarInfo['CREATED_BY'])
			->setDateFrom($dateFrom)
			->setDateTo($dateTo)
			->setMembersIds(array_map('intval', array_column($calendarInfo['ATTENDEE_LIST'], 'id')))
		;

		return $calendar;
	}

	public static function initById(int $id, ?Context $context = null): self
	{
		$context = $context ?? Locator::getContext();
		$checkPermissions = false;

		$calendarGetList = CCalendarEvent::GetList([
			'arFilter' => [
				'ID' => $id,
				'DELETED' => false,
			],
			'parseRecursion' => false,
			'fetchAttendees' => true,
			'userId' => $context->getUserId(),
			'fetchMeetings' => false,
			'setDefaultLimit' => false,
			'checkPermissions' => $checkPermissions,
		]);

		return static::initByGetListArray($calendarGetList[0]);
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'dateFrom' => $this->getDateFrom()->format('c'),
			'dateTo' => $this->getDateTo()->format('c'),
			'source' => $this->getUrl(),
		];
	}

	public function getUrl(): string
	{
		return CCalendar::GetPath($this->getType(), $this->getOwnerId()) . '?EVENT_ID=' . $this->getId();
	}

	//region Getters & setters

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(?int $id): CalendarItem
	{
		$this->id = $id;
		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): CalendarItem
	{
		$this->title = $title;
		return $this;
	}

	public function getDateFrom(): DateTime
	{
		return $this->dateFrom;
	}

	public function setDateFrom(DateTime $dateFrom): CalendarItem
	{
		$this->dateFrom = $dateFrom;
		return $this;
	}

	public function getDateTo(): DateTime
	{
		return $this->dateTo;
	}

	public function setDateTo(DateTime $dateTo): CalendarItem
	{
		$this->dateTo = $dateTo;
		return $this;
	}

	public function getOwnerId(): int
	{
		return $this->ownerId;
	}

	public function setOwnerId(int $ownerId): CalendarItem
	{
		$this->ownerId = $ownerId;
		return $this;
	}

	public function getCreatedBy(): int
	{
		return $this->createdBy;
	}

	public function setCreatedBy(int $createdBy): CalendarItem
	{
		$this->createdBy = $createdBy;
		return $this;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): CalendarItem
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getMembersIds(): array
	{
		return $this->membersIds;
	}

	/**
	 * @param int[] $membersIds
	 */
	public function setMembersIds(array $membersIds): CalendarItem
	{
		$this->membersIds = array_unique($membersIds);
		return $this;
	}

	//endregion
}