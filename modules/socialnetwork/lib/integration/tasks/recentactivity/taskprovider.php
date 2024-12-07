<?php

namespace Bitrix\Socialnetwork\Integration\Tasks\RecentActivity;


use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\AbstractProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\Trait\EntityLoadTrait;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Tasks\Internals\Registry\TaskRegistry;

class TaskProvider extends AbstractProvider
{
	use EntityLoadTrait;

	public function isAvailable(): bool
	{
		return Loader::includeModule('tasks');
	}

	public function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['task'];
	}

	protected function fill(): void
	{
		$taskIds = $this->getEntityIdsFromRecentActivityItems();
		$registry = TaskRegistry::getInstance();
		$registry->load($taskIds);

		foreach ($taskIds as $taskId)
		{
			$this->addEntity($taskId, $registry->getObject($taskId));
		}

		foreach ($this->recentActivityDataItems as $item)
		{
			$task = $this->getEntity($this->getEntityIdFromRecentActivityItem($item));
			if (empty($task))
			{
				continue;
			}

			$message = Loc::getMessage(
				'SONET_TASK_RECENT_ACTIVITY_DESCRIPTION',
				['#CONTENT#' => Emoji::decode($task->getTitle() ?? '')],
			);
			$item->setDescription($message);
		}
	}
}
