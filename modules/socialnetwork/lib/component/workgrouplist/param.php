<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Helper;

class Param
{
	public static function fillUrls(array &$params = []): void
	{
		if (!isset($params['PATH_TO_USER']) || (string)$params['PATH_TO_USER'] === '')
		{
			$params['PATH_TO_USER'] = Helper\Path::get('user_profile');
		}

		if (!isset($params['PATH_TO_GROUP']) || (string)$params['PATH_TO_GROUP'] === '')
		{
			$params['PATH_TO_GROUP'] = Helper\Path::get('group_path_template');
		}

		if (!isset($params['PATH_TO_GROUP_CREATE']) || (string)$params['PATH_TO_GROUP_CREATE'] === '')
		{
			$params['PATH_TO_GROUP_CREATE'] = Helper\Path::get('user_create_group_path_template');
		}

		if (!isset($params['PATH_TO_GROUP_EDIT']) || (string)$params['PATH_TO_GROUP_EDIT'] === '')
		{
			$params['PATH_TO_GROUP_EDIT'] = Helper\Path::get('group_edit_path_template');
		}

		if (!isset($params['PATH_TO_GROUP_DELETE']) || (string)$params['PATH_TO_GROUP_DELETE'] === '')
		{
			$params['PATH_TO_GROUP_DELETE'] = Helper\Path::get('group_delete_path_template');
		}

		if (!isset($params['PATH_TO_JOIN_GROUP']) || (string)$params['PATH_TO_JOIN_GROUP'] === '')
		{
			$params['PATH_TO_JOIN_GROUP'] = Helper\Path::get('user_request_group_path_template');
		}

		if (!isset($params['PATH_TO_LEAVE_GROUP']) || (string)$params['PATH_TO_LEAVE_GROUP'] === '')
		{
			$params['PATH_TO_LEAVE_GROUP'] = Helper\Path::get('user_leave_group_path_template');
		}

		self::fillTasksUrls($params);
	}

	public static function fillTasksUrls(array &$params = []): void
	{
		if (ModuleManager::isModuleInstalled('tasks'))
		{
			return;
		}

		if (!isset($params['PATH_TO_GROUP_TASKS']) || (string)$params['PATH_TO_GROUP_TASKS'] === '')
		{
			$params['PATH_TO_GROUP_TASKS'] = Helper\Path::get('group_tasks_path_template');
		}

		if (!isset($params['PATH_TO_GROUP_TASKS_VIEW']) || (string)$params['PATH_TO_GROUP_TASKS_VIEW'] === '')
		{
			$params['PATH_TO_GROUP_TASKS_VIEW'] = Helper\Path::get('group_tasks_view_path_template');
		}

		if (!isset($params['PATH_TO_GROUP_TASKS_REPORT']) || (string)$params['PATH_TO_GROUP_TASKS_REPORT'] === '')
		{
			$params['PATH_TO_GROUP_TASKS_REPORT'] = Helper\Path::get('group_tasks_report_path_template');
		}

		if (!isset($params['PATH_TO_GROUP_TASKS_TASK']) || (string)$params['PATH_TO_GROUP_TASKS_TASK'] === '')
		{
			$params['PATH_TO_GROUP_TASKS_TASK'] = Helper\Path::get('group_tasks_task_path_template');
		}

		if (!isset($params['PATH_TO_USER_TASKS']) || (string)$params['PATH_TO_USER_TASKS'] === '')
		{
			$params['PATH_TO_USER_TASKS'] = str_replace(
				[ '#user_id#', '#id#', '#ID#' ],
				$params['USER_ID'],
				Helper\Path::get('user_tasks_path_template')
			);
		}

		if (!isset($params['PATH_TO_USER_TASKS_VIEW']) || (string)$params['PATH_TO_USER_TASKS_VIEW'] === '')
		{
			$params['PATH_TO_USER_TASKS_VIEW'] = str_replace(
				[ '#user_id#', '#id#', '#ID#' ],
				$params['USER_ID'],
				Helper\Path::get('user_tasks_view_path_template')
			);
		}

		if (!isset($params['PATH_TO_USER_TASKS_REPORT']) || (string)$params['PATH_TO_USER_TASKS_REPORT'] === '')
		{
			$params['PATH_TO_USER_TASKS_REPORT'] = str_replace(
				[ '#user_id#', '#id#', '#ID#' ],
				$params['USER_ID'],
				Helper\Path::get('user_tasks_report_path_template')
			);
		}

		if (!isset($params['PATH_TO_USER_TASKS_TEMPLATES']) || (string)$params['PATH_TO_USER_TASKS_TEMPLATES'] === '')
		{
			$params['PATH_TO_USER_TASKS_TEMPLATES'] = str_replace(
				[ '#user_id#', '#id#', '#ID#' ],
				$params['USER_ID'],
				Helper\Path::get('user_tasks_templates_path_template')
			);
		}
	}
}
