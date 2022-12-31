<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class Path
{
	public static function get(string $key = '', $siteId = SITE_ID): string
	{
		$result = '';

		if ($key === '')
		{
			return $result;
		}

		switch ($key)
		{
			case 'user_profile':
				$result = (
					ModuleManager::isModuleInstalled('intranet')
						? Option::get('intranet', 'search_user_url', self::getDefault($key, $siteId), $siteId)
						: ''
				);
				break;
			case 'user_calendar_path_template':
				$result = self::get('user_profile', $siteId) . 'calendar/';
				break;
			case 'user_create_group_path_template':
				$result = self::get('user_profile', $siteId) . 'groups/create/';
				break;
			case 'user_tasks_path_template':
				$result = self::get('user_profile', $siteId) . 'tasks/';
				break;
			case 'user_tasks_view_path_template':
				$result = self::get('user_tasks_path_template', $siteId) . 'view/#action#/#view_id#/';
				break;
			case 'user_tasks_report_path_template':
				$result = self::get('user_tasks_path_template', $siteId) . 'report/';
				break;
			case 'user_tasks_templates_path_template':
				$result = self::get('user_tasks_path_template', $siteId) . 'templates/';
				break;
			case 'userblogpost_page':
			case 'group_path_template':
			case 'workgroups_page':
				$result = Option::get('socialnetwork', $key, self::getDefault($key, $siteId), $siteId);
				break;
			case 'group_edit_path_template':
				$result = self::get('group_path_template', $siteId) . 'edit/';
				break;
			case 'group_delete_path_template':
				$result = self::get('group_path_template', $siteId) . 'delete/';
				break;
			case 'group_invite_path_template':
				$result = self::get('group_path_template', $siteId) . 'invite/';
				break;
			case 'group_livefeed_path_template':
				$result = self::get('group_path_template', $siteId) . (
					ModuleManager::isModuleInstalled('intranet')
					&& SITE_TEMPLATE_ID === 'bitrix24'
						? 'general/'
						: ''
				);
				break;
			case 'group_tasks_path_template':
				$result = self::get('group_path_template', $siteId) . 'tasks/';
				break;
			case 'group_tasks_task_path_template':
				$result = self::get('group_tasks_path_template', $siteId) . 'task/#action#/#task_id#/';
				break;
			case 'group_tasks_view_path_template':
				$result = self::get('group_tasks_path_template', $siteId) . 'view/#action#/#view_id#/';
				break;
			case 'group_tasks_report_path_template':
				$result = self::get('group_tasks_path_template', $siteId) . 'report/';
				break;
			case 'group_calendar_path_template':
				$result = self::get('group_path_template', $siteId) . 'calendar/';
				break;
			case 'group_users_path_template':
				$result = self::get('group_path_template', $siteId) . 'users/';
				break;
			case 'group_requests_path_template':
				$result = self::get('group_path_template', $siteId) . 'requests/';
				break;
			case 'group_requests_out_path_template':
				$result = self::get('group_path_template', $siteId) . 'requests_out/';
				break;
			case 'user_request_group_path_template':
				$result = self::get('group_path_template', $siteId) . 'user_request/';
				break;
			case 'user_leave_group_path_template':
				$result = self::get('group_path_template', $siteId) . 'user_leave/';
				break;
			case 'department_path_template':
				$result = Option::get('main', 'TOOLTIP_PATH_TO_CONPANY_DEPARTMENT', self::getDefault('TOOLTIP_PATH_TO_CONPANY_DEPARTMENT', $siteId), $siteId);
				break;
			default:
		}

		return $result;
	}

	private static function getDefault(string $key = '', $siteId = SITE_ID): string
	{
		$result = '';
		if ($key === '')
		{
			return $result;
		}

		$siteDir = SITE_DIR;
		if ($siteDir === '')
		{
			$siteDir = '/';
		}

		switch ($key)
		{
			case 'user_profile':
				$result = $siteDir . self::getUserFolder($siteId) . '#user_id#/';
				break;
			case 'userblogpost_page':
				$result = $siteDir . self::getUserFolder($siteId) . '#user_id#/blog/#post_id#/';
				break;
			case 'group_path_template':
				$result = self::getDefault('workgroups_page', $siteId) . 'group/#group_id#/';
				break;
			case 'department_path_template':
				$result = $siteDir . 'company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#';
				break;
			case 'workgroups_page':
				$result = $siteDir . 'workgroups/';
				break;
			default:
		}

		return $result;
	}

	private static function getUserFolder($siteId = SITE_ID): string
	{
		static $extranetSiteId = null;

		if ($extranetSiteId === null)
		{
			$extranetSiteId = (Loader::includeModule('extranet') ? \CExtranet::getExtranetSiteID() : '');
		}

		return ($siteId === $extranetSiteId ? 'contacts' : 'company') . '/personal/user/';
	}
}
