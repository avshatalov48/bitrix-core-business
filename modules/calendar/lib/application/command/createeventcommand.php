<?php

namespace Bitrix\Calendar\Application\Command;

class CreateEventCommand implements Command, BusyAttendees
{
	private int $userId;
	private int $sectionId;
	private string $dateFrom;
	private string $dateTo;
	private bool $skipTime;
	private string $timeZoneFrom;
	private string $timeZoneTo;
	private string $name;
	private string $description;
	private ?string $color;
	private ?string $accessibility;
	private string $importance;
	private bool $private;
	private ?array $rrule;
	private string $location;
	private array $remindList;
	private ?int $meetingHost;
	private int $chatId;
	private ?array $attendeesEntityList;
	private ?string $excludeUsers;
	private bool $meetingNotify;
	private bool $meetingReinvite;
	private bool $allowInvite;
	private bool $hideGuests;
	private bool $isPlannerFeatureEnabled;
	private bool $checkCurrentUsersAccessibility;
	private ?array $newAttendeesList;
	private ?string $recEditMode;
	private ?string $currentDateFrom;
	private array $ufFields;
	private bool $sendInvitesAgain;
	private int $requestUid;
	private bool $checkLocationOccupancy;
	private ?int $category;
	private ?int $maxAttendees;
	private ?string $analyticsSubSection;
	private ?int $analyticsChatId;

	public function __construct(
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
		?int $category,
		?string $analyticsSubSection,
		?int $analyticsChatId,
	)
	{
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
		$this->category = $category;
		$this->maxAttendees = $maxAttendees;
		$this->analyticsSubSection = $analyticsSubSection;
		$this->analyticsChatId = $analyticsChatId;
	}

	public function isCheckLocationOccupancy(): bool
	{
		return $this->checkLocationOccupancy;
	}

	public function getRequestUid(): int
	{
		return $this->requestUid;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	public function getDateFrom(): string
	{
		return $this->dateFrom;
	}

	public function getDateTo(): string
	{
		return $this->dateTo;
	}

	public function isSkipTime(): bool
	{
		return $this->skipTime;
	}

	public function getTimeZoneFrom(): string
	{
		return $this->timeZoneFrom;
	}

	public function getTimeZoneTo(): string
	{
		return $this->timeZoneTo;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function getColor(): ?string
	{
		return $this->color;
	}

	public function getAccessibility(): ?string
	{
		return $this->accessibility;
	}

	public function getImportance(): string
	{
		return $this->importance;
	}

	public function isPrivate(): bool
	{
		return $this->private;
	}

	public function getRrule(): ?array
	{
		return $this->rrule;
	}

	public function getLocation(): string
	{
		return $this->location;
	}

	public function getRemindList(): array
	{
		return $this->remindList;
	}

	public function getMeetingHost(): ?int
	{
		return $this->meetingHost;
	}

	public function getChatId(): int
	{
		return $this->chatId;
	}

	public function getAttendeesEntityList(): ?array
	{
		return $this->attendeesEntityList;
	}

	public function getExcludeUsers(): ?string
	{
		return $this->excludeUsers;
	}

	public function isMeetingNotify(): bool
	{
		return $this->meetingNotify;
	}

	public function isMeetingReinvite(): bool
	{
		return $this->meetingReinvite;
	}

	public function isAllowInvite(): bool
	{
		return $this->allowInvite;
	}

	public function isHideGuests(): bool
	{
		return $this->hideGuests;
	}

	public function isPlannerFeatureEnabled(): bool
	{
		return $this->isPlannerFeatureEnabled;
	}

	public function isCheckCurrentUsersAccessibility(): bool
	{
		return $this->checkCurrentUsersAccessibility;
	}

	public function getNewAttendeesList(): ?array
	{
		return $this->newAttendeesList;
	}

	public function getRecEditMode(): ?string
	{
		return $this->recEditMode;
	}

	public function getCurrentDateFrom(): ?string
	{
		return $this->currentDateFrom;
	}

	public function getUfFields(): array
	{
		return $this->ufFields;
	}

	public function isSendInvitesAgain(): bool
	{
		return $this->sendInvitesAgain;
	}

	public function getCategory(): ?int
	{
		return $this->category;
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
