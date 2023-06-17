<?php

namespace Bitrix\Calendar\Access;


use Bitrix\Main\Access\AccessibleItem;

interface AccessibleEvent
	extends AccessibleItem
{
	public function getSectionId(): int;
	public function getSectionType(): string;
	public function getEventType(): string;
	public function getMeetingStatus(): string;
	public function getOwnerId(): int;
	public function getParentEventSectionId(): int;
	public function getParentEventSectionType(): string;
	public function getParentEventOwnerId(): int;
}