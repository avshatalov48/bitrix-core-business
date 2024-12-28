<?php

namespace Bitrix\Calendar\Application\Command;

class UpdateEventCommand implements Command, BusyAttendees
{
	private bool $checkLocationOccupancy;
	private int $requestUid;
	private bool $sendInvitesAgain;
	private array $ufFields;
	private ?string $currentDateFrom;
	private ?string $recEditMode;
	private ?array $newAttendeesList;
	private bool $checkCurrentUsersAccessibility;
	private bool $isPlannerFeatureEnabled;
	private bool $hideGuests;
	private bool $allowInvite;
	private bool $meetingReinvite;
	private bool $meetingNotify;
	private ?string $excludeUsers;
	private ?array $attendeesEntityList;
	private int $chatId;
	private ?int $meetingHost;
	private array $remindList;
	private string $location;
	private ?array $rrule;
	private bool $private;
	private string $importance;
	private ?string $accessibility;
	private ?string $color;
	private string $description;
	private string $name;
	private string $timeZoneTo;
	private string $timeZoneFrom;
	private bool $skipTime;
	private string $dateTo;
	private string $dateFrom;
	private int $sectionId;
	private int $userId;
	private int $id;
	private ?int $maxAttendees;

	public function __construct(
		int $id,
		int $userId,
		int $sectionId,
		string $dateFrom,
		string $dateTo,
		bool $skipTime,
		string $timeZoneFrom,
		string $timeZoneTo,
		string $name,
		string $description,
		?string $color,
		?string $accessibility,
		string $importance,
		bool $private,
		?array $rrule,
		string $location,
		array $remindList,
		?int $meetingHost,
		int $chatId,
		?array $attendeesEntityList,
		?string $excludeUsers,
		bool $meetingNotify,
		bool $meetingReinvite,
		bool $allowInvite,
		bool $hideGuests,
		bool $isPlannerFeatureEnabled,
		bool $checkCurrentUsersAccessibility,
		?array $newAttendeesList,
		?string $recEditMode,
		?string $currentDateFrom,
		array $ufFields,
		bool $sendInvitesAgain,
		int $requestUid,
		bool $checkLocationOccupancy,
		?int $maxAttendees,
	)
	{
		$this->id = $id;
		$this->userId = $userId;
		$this->sectionId = $sectionId;
		$this->dateFrom = $dateFrom;
		$this->dateTo = $dateTo;
		$this->skipTime = $skipTime;
		$this->timeZoneFrom = $timeZoneFrom;
		$this->timeZoneTo = $timeZoneTo;
		$this->name = $name;
		$this->description = $description;
		$this->color = $color;
		$this->accessibility = $accessibility;
		$this->importance = $importance;
		$this->private = $private;
		$this->rrule = $rrule;
		$this->location = $location;
		$this->remindList = $remindList;
		$this->meetingHost = $meetingHost;
		$this->chatId = $chatId;
		$this->attendeesEntityList = $attendeesEntityList;
		$this->excludeUsers = $excludeUsers;
		$this->meetingNotify = $meetingNotify;
		$this->meetingReinvite = $meetingReinvite;
		$this->allowInvite = $allowInvite;
		$this->hideGuests = $hideGuests;
		$this->isPlannerFeatureEnabled = $isPlannerFeatureEnabled;
		$this->checkCurrentUsersAccessibility = $checkCurrentUsersAccessibility;
		$this->newAttendeesList = $newAttendeesList;
		$this->recEditMode = $recEditMode;
		$this->currentDateFrom = $currentDateFrom;
		$this->ufFields = $ufFields;
		$this->sendInvitesAgain = $sendInvitesAgain;
		$this->requestUid = $requestUid;
		$this->checkLocationOccupancy = $checkLocationOccupancy;
		$this->maxAttendees = $maxAttendees;
	}

	public function isCheckLocationOccupancy(): bool
	{
		return $this->checkLocationOccupancy;
	}

	public function getRequestUid(): int
	{
		return $this->requestUid;
	}

	public function isSendInvitesAgain(): bool
	{
		return $this->sendInvitesAgain;
	}

	public function getUfFields(): array
	{
		return $this->ufFields;
	}

	public function getCurrentDateFrom(): ?string
	{
		return $this->currentDateFrom;
	}

	public function getRecEditMode(): ?string
	{
		return $this->recEditMode;
	}

	public function getNewAttendeesList(): ?array
	{
		return $this->newAttendeesList;
	}

	public function isCheckCurrentUsersAccessibility(): bool
	{
		return $this->checkCurrentUsersAccessibility;
	}

	public function isPlannerFeatureEnabled(): bool
	{
		return $this->isPlannerFeatureEnabled;
	}

	public function isHideGuests(): bool
	{
		return $this->hideGuests;
	}

	public function isAllowInvite(): bool
	{
		return $this->allowInvite;
	}

	public function isMeetingReinvite(): bool
	{
		return $this->meetingReinvite;
	}

	public function isMeetingNotify(): bool
	{
		return $this->meetingNotify;
	}

	public function getExcludeUsers(): ?string
	{
		return $this->excludeUsers;
	}

	public function getAttendeesEntityList(): ?array
	{
		return $this->attendeesEntityList;
	}

	public function getChatId(): int
	{
		return $this->chatId;
	}

	public function getMeetingHost(): ?int
	{
		return $this->meetingHost;
	}

	public function getRemindList(): array
	{
		return $this->remindList;
	}

	public function getLocation(): string
	{
		return $this->location;
	}

	public function getRrule(): ?array
	{
		return $this->rrule;
	}

	public function isPrivate(): bool
	{
		return $this->private;
	}

	public function getImportance(): string
	{
		return $this->importance;
	}

	public function getAccessibility(): ?string
	{
		return $this->accessibility;
	}

	public function getColor(): ?string
	{
		return $this->color;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getTimeZoneTo(): string
	{
		return $this->timeZoneTo;
	}

	public function getTimeZoneFrom(): string
	{
		return $this->timeZoneFrom;
	}

	public function isSkipTime(): bool
	{
		return $this->skipTime;
	}

	public function getDateTo(): string
	{
		return $this->dateTo;
	}

	public function getDateFrom(): string
	{
		return $this->dateFrom;
	}

	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getMaxAttendees(): ?int
	{
		return $this->maxAttendees;
	}

	public function getAnalyticsSubSection(): ?string
	{
		return $this->analyticsSubSection;
	}

	public function getAnalyticsChatId(): ?int
	{
		return $this->analyticsChatId;
	}
}
