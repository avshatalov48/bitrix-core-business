<?php

namespace Bitrix\Socialnetwork\Integration\Pull;

use Bitrix\Socialnetwork\Item\Log;
use Bitrix\Socialnetwork\Livefeed\Provider;

class Tag
{
	public function getWatchingTagsByLogId(int $logId): array
	{
		$log = Log::getById($logId);
		if (!$log)
		{
			return [];
		}

		$liveFeedContent = Provider::getContentId($log->getFields());
		$entityType = $liveFeedContent['ENTITY_TYPE'] ?? null;
		$entityId = $liveFeedContent['ENTITY_ID'] ?? null;
		if (!$entityType || !$entityId)
		{
			return [];
		}

		return [
			'CONTENTVIEW' . $entityType . '-' . $entityId,
			'UNICOMMENTS' . $this->getForumEntityType($entityType) . '_' . $entityId,
			'UNICOMMENTSMOBILE' . $this->getForumEntityType($entityType) . '_' . $entityId,
		];
	}

	public function getTasksProjects(int $groupId): array
	{
		return [
			"TASKS_PROJECTS_$groupId"
		];
	}

	private function getForumEntityType(string $entityType): string
	{
		return match ($entityType)
		{
			'TASK' => 'TASK',
			'CALENDAR_EVENT' => 'EVENT',
			default => 'BLOG',
		};
	}
}