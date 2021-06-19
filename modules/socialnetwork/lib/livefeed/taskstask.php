<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class TasksTask extends Provider
{
	const PROVIDER_ID = 'TASK';
	const CONTENT_TYPE_ID = 'TASK';

	protected static $tasksTaskClass = \CTasks::class;

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return [
			'tasks',
			'crm_activity_add'
		];
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		return new ForumPost();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$taskId = (int)$this->entityId;

		if ($taskId <= 0)
		{
			return;
		}

		$checkAccess = ($this->getOption('checkAccess') !== false);
		$cacheKey = $taskId . '_' . ($checkAccess ? 'Y' : 'N');

		if (isset($cache[$cacheKey]))
		{
			$task = $cache[$cacheKey];
		}
		elseif (Loader::includeModule('tasks'))
		{
			$res = self::$tasksTaskClass::getByID($taskId, $checkAccess);
			$task = $res->fetch();
			$cache[$cacheKey] = $task;
		}

		if (empty($task))
		{
			return;
		}

		$this->setSourceFields($task);
		$this->setSourceDescription($task['DESCRIPTION']);
		$this->setSourceTitle($task['TITLE']);
		$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects());
		$this->setSourceDiskObjects($this->getDiskObjects($taskId, $this->cloneDiskObjects));

	}

	public function getPinnedTitle()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$task = $this->getSourceFields();
		if (empty($task))
		{
			return $result;
		}

		$result = Loc::getMessage('SONET_LIVEFEED_TASKS_TASK_PINNED_TITLE', [
			'#TITLE#' => $task['TITLE']
		]);

		return $result;
	}

	public function getPinnedDescription()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$task = $this->getSourceFields();
		if (empty($task))
		{
			return $result;
		}

		$result = Loc::getMessage('SONET_LIVEFEED_TASKS_TASK_PINNED_DESCRIPTION', [
			'#RESPONSIBLE#' => \CUser::formatName(
				\CSite::getNameFormat(),
				[
					'NAME' => $task['RESPONSIBLE_NAME'],
					'LAST_NAME' => $task['RESPONSIBLE_LAST_NAME'],
					'SECOND_NAME' => $task['RESPONSIBLE_SECOND_NAME']
				],
				true,
				false
			)
		]);

		return $result;
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		global $USER_FIELD_MANAGER;
		static $cache = array();

		$taskId = $this->entityId;

		$result = array();
		$cacheKey = $taskId.$clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$taskUF = $USER_FIELD_MANAGER->getUserFields("TASKS_TASK", $taskId, LANGUAGE_ID);
			if (
				!empty($taskUF['UF_TASK_WEBDAV_FILES'])
				&& !empty($taskUF['UF_TASK_WEBDAV_FILES']['VALUE'])
				&& is_array($taskUF['UF_TASK_WEBDAV_FILES']['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($taskUF['UF_TASK_WEBDAV_FILES']['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $taskUF['UF_TASK_WEBDAV_FILES']['VALUE'];
				}
			}
		}

		return $result;
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getLiveFeedUrl(): string
	{
		$pathToTask = '';
		$userPage = Option::get('socialnetwork', 'user_page', '', SITE_ID);
		if (
			!empty($userPage)
			&& ($task = $this->getSourceFields())
			&& !empty($task)
		)
		{
			$pathToTask = \CComponentEngine::makePathFromTemplate($userPage."user/#user_id#/tasks/task/#action#/#task_id#/", array(
				"user_id" => $task["CREATED_BY"],
				"action" => "view",
				"task_id" => $task["ID"]
			));
		}

		return $pathToTask;
	}
}