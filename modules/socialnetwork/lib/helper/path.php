<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Config\Option;

class Path
{
	public static function get(string $key = '', string $siteId = SITE_ID): string
	{
		$result = '';

		if ($key === '')
		{
			return $result;
		}

		switch ($key)
		{
			case 'userblogpost_page':
			case 'group_path_template':
				$result = Option::get('socialnetwork', $key, self::getDefault($key), $siteId);
				break;
			case 'department_path_template':
				$result = Option::get('main', 'TOOLTIP_PATH_TO_CONPANY_DEPARTMENT', self::getDefault('TOOLTIP_PATH_TO_CONPANY_DEPARTMENT'), $siteId);
			default:
		}

		return $result;
	}

	private static function getDefault(string $key = ''): string
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

		switch($key)
		{
			case 'userblogpost_page':
				$result = $siteDir . 'company/personal/user/#user_id#/blog/#post_id#/';
				break;
			case 'group_path_template':
				$result = $siteDir . 'workgroups/group/#group_id#/';
				break;
			case 'department_path_template':
				$result = $siteDir . 'company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#';
				break;
			default:
		}

		return $result;
	}
}
