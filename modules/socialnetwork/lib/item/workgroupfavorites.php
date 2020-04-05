<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\WorkgroupFavoritesTable;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class WorkgroupFavorites
{
	/**
	 * Adds/deletes a worgroup GROUP_ID to/from a favorites list of a user USER_ID
	 * @param array $params
	 * @return bool
	 * @throws \Exception
	 */
	public static function set($params)
	{
		global $USER;

		$groupId = (isset($params["GROUP_ID"]) ? intval($params["GROUP_ID"]) : false);
		$userId = (isset($params["USER_ID"]) ? intval($params["USER_ID"]) : $USER->getId());
		$value = (isset($params["VALUE"]) && in_array($params["VALUE"], array('Y', 'N')) ? $params["VALUE"] : false);

		if (
			intval($groupId) <= 0
			|| intval($userId) <= 0
			|| !$value
		)
		{
			throw new SystemException(Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUPFAVORITES_ERROR_NO_DATA'));
		}

		if (!($group = \CSocNetGroup::getByID($groupId, true)))
		{
			throw new SystemException(Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUPFAVORITES_ERROR_NO_ACCESS'));
		}

		if ($value == 'Y')
		{
			return WorkgroupFavoritesTable::set(array(
				'GROUP_ID' => $groupId,
				'USER_ID' => $userId
			));
		}
		else
		{
			return self::delete(array(
				'GROUP_ID' => $groupId,
				'USER_ID' => $userId
			));
		}
	}

	/**
	 * Deletes a worgroup GROUP_ID from a favorites list of a user USER_ID
	 * @param array $params
	 * @return bool
	 */
	public static function delete($params)
	{
		global $CACHE_MANAGER, $USER;

		$groupId = (isset($params["GROUP_ID"]) ? intval($params["GROUP_ID"]) : false);
		$userId = (isset($params["USER_ID"]) ? intval($params["USER_ID"]) : $USER->getId());

		if (
			intval($groupId) <= 0
			|| intval($userId) <= 0
		)
		{
			return false;
		}

		$res = WorkgroupFavoritesTable::delete(array(
			'GROUP_ID' => $groupId,
			'USER_ID' => $userId
		));

		$result = $res->isSuccess();

		if (
			$result
			&& defined("BX_COMP_MANAGED_CACHE")
		)
		{
			$CACHE_MANAGER->clearByTag("sonet_group_favorites_U".$userId);
		}

		return $result;
	}
}
