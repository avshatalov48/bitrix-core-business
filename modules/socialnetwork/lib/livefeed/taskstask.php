<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

final class TasksTask extends Provider
{
	const PROVIDER_ID = 'TASK';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'TASK';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('tasks');
	}

	public function getType()
	{
		return static::TYPE;
	}

	public function initSourceFields()
	{
		$taskId = $this->entityId;

		if (
			$taskId > 0
			&& Loader::includeModule('tasks')
		)
		{
			$res = \CTasks::getByID(intval($taskId), true);
			if ($task = $res->fetch())
			{
				$this->setSourceFields($task);
				$this->setSourceDescription($task['DESCRIPTION']);
				$this->setSourceTitle($task['TITLE']);
				$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($taskId));
				$this->setSourceDiskObjects($this->getDiskObjects($taskId, $this->cloneDiskObjects));
			}
		}
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

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}

	public function getLiveFeedUrl()
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