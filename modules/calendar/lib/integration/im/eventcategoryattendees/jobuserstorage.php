<?php

namespace Bitrix\Calendar\Integration\Im\EventCategoryAttendees;

use Bitrix\Calendar\EventCategory\Dto\EventCategoryAttendeesUpdateDto;

final class JobUserStorage
{
	private array $usersStorage = [];

	public function get(EventCategoryAttendeesUpdateDto $updateDto): array
	{
		return $this->usersStorage[$this->getKey($updateDto)] ?? [];
	}

	public function add(EventCategoryAttendeesUpdateDto $updateDto, array $userIds): void
	{
		$key = $this->getKey($updateDto);
		$this->usersStorage[$key] = array_unique([
			...$this->usersStorage[$key] ?? [],
			...$userIds,
		]);
	}

	public function clear(EventCategoryAttendeesUpdateDto $updateDto): void
	{
		unset($this->usersStorage[$this->getKey($updateDto)]);
	}

	public function has(EventCategoryAttendeesUpdateDto $updateDto): bool
	{
		return (bool)($this->usersStorage[$this->getKey($updateDto)] ?? null);
	}

	private function getKey(EventCategoryAttendeesUpdateDto $updateDto): string
	{
		return "{$updateDto->type->value}-{$updateDto->chatId}";
	}
}
