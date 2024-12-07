<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Calendar\Core\EventCategory\EventCategory;

interface EventCategoryServiceInterface
{
	public function getAvailableChannelsList(int $userId): array;

	public function createChannel(EventCategory $eventCategory, array $userIds, array $departmentIds = []): int;

	public function updateChannel(EventCategory $eventCategory): void;

	public function setMuteChannel(int $userId, int $channelId, bool $newMuteState): void;

	public function isChannelMuted(int $userId, int $channelId): ?bool;

	public function getThreadCommentsCount(int $threadId): ?int;

	public function includeUserToChannel(int $userId, int $channelId): bool;

	public function hasAccess(int $userId, int $channelId): bool;

	public function getChannelUsers(int $channelId): array;

	public function isChannelPrivate(int $channelId): bool;

	public function isManagerOfChannel(int $userId, int $channelId): bool;

	public function connectChannelToCategory(int $channelId): void;
}
