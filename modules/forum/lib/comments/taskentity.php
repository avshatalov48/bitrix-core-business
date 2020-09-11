<?php

namespace Bitrix\Forum\Comments;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

final class TaskEntity extends Entity
{
	const ENTITY_TYPE = 'tk';
	const MODULE_ID = 'tasks';
	const XML_ID_PREFIX = 'TASK_';

	private $taskPostData;
	private $hasAccess;

	protected static $permissions = array();

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canRead($userId)
	{
		// you are not allowed to view the task, so you can not read messages
		if(!$this->checkHasAccess($userId))
		{
			return false;
		}

		return true;
	}
	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canAdd($userId)
	{
		// you are not allowed to view the task, so you can not add new messages
		if(!$this->checkHasAccess($userId))
		{
			return false;
		}

		return true;
	}

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canEditOwn($userId)
	{
		//!!!
		// in case of canEdit($userId) returns FALSE, canEditOwn($userId) may override this

		// if you are not an admin, you must obey "tasks" module settings
		if(!static::checkEditOptionIsOn())
		{
			return false;
		}

		// if you are not an admin AND you are not allowed to view the task, you cant edit even your own comments
		if(!$this->checkHasAccess($userId))
		{
			return false;
		}

		return true;
	}

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		// admin is always able to edit\remove comments
		if (
			Loader::includeModule("tasks")
			&& (
				\CTasksTools::isAdmin($userId)
				|| \CTasksTools::isPortalB24Admin($userId)
			)
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * @return $this
	 */
	public function dropCache()
	{
		$this->taskPostData = null;
		$this->hasAccess = null;
		return $this;
	}

	/**
	 * @param integer $userId User id.
	 * @return bool
	 */
	private function checkHasAccess($userId)
	{
		if($this->hasAccess === null)
		{
			try
			{
				if (Loader::includeModule("tasks"))
				{
					$task = new \CTaskItem($this->getId(), $userId);
					$this->hasAccess = $task->checkCanRead();
				}
				else
				{
					return false;
				}

			}
			catch(\TasksException $e)
			{
				return false;
			}
		}

		return $this->hasAccess;
	}

	private static function checkEditOptionIsOn()
	{
		$value = Option::get("tasks", "task_comment_allow_edit");

		return $value == 'Y' || $value == '1';
	}

	/**
	 * Event before indexing message.
	 * @param integer $id Message ID.
	 * @param array $message Message data.
	 * @param array &$index Search index array.
	 * @return boolean
	 */
	public static function onMessageIsIndexed($id, array $message, array &$index)
	{
		if ($message["PARAM1"] == mb_strtoupper(self::ENTITY_TYPE))
			return false;

		if (
			preg_match("/".self::getXmlIdPrefix()."(\\d+)/", $message["XML_ID"], $matches) &&
			($taskId = intval($matches[1])) &&
			$taskId > 0
		)
		{
			if (!array_key_exists($taskId, self::$permissions))
			{
				$task = \CTasks::GetList(array(), array("ID" => $taskId))->fetch();
				self::$permissions[$taskId] = \CTasks::__GetSearchPermissions($task);
			}
			$index["PERMISSIONS"] = self::$permissions[$taskId];
		}
		return true;
	}
}