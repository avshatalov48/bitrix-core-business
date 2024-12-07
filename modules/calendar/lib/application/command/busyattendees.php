<?php

namespace Bitrix\Calendar\Application\Command;

interface BusyAttendees
{
	public function isCheckCurrentUsersAccessibility(): bool;
	public function getNewAttendeesList(): ?array;
	public function getExcludeUsers(): ?string;
	public function getDateFrom(): string;
	public function getDateTo(): string;
	public function getTimeZoneFrom(): string;
	public function getTimeZoneTo(): string;
	public function isSkipTime(): bool;
}