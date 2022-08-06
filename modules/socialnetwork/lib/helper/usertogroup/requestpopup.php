<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper\UserToGroup;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Integration;

class RequestPopup
{
	public static function setHideRequestPopup(array $params = []): bool
	{
		$userId = (int)($params['userId'] ?? 0);
		$groupId = (int)($params['groupId'] ?? 0);

		if ($userId <= 0 || $groupId <= 0)
		{
			return false;
		}

		\CUserOptions::setOption(
			'socialnetwork',
			'hide_request_popup_' . $groupId,
			'Y',
			false,
			$userId,
		);

		return true;
	}

	public static function unsetHideRequestPopup(array $params = []): bool
	{
		$userId = (int)($params['userId'] ?? 0);
		$groupId = (int)($params['groupId'] ?? 0);

		if ($userId <= 0 || $groupId <= 0)
		{
			return false;
		}

		\CUserOptions::deleteOption(
			'socialnetwork',
			'hide_request_popup_' . $groupId,
			false,
			$userId,
		);

		return true;
	}

	public static function checkHideRequestPopup(array $params = []): bool
	{
		$userId = (int)($params['userId'] ?? 0);
		$groupId = (int)($params['groupId'] ?? 0);

		if ($userId <= 0 || $groupId <= 0)
		{
			return false;
		}

		return (\CUserOptions::getOption('socialnetwork', 'hide_request_popup_' . $groupId) === 'Y');
	}
}