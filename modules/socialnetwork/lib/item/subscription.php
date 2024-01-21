<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Exception;

Loc::loadMessages(__FILE__);

class Subscription
{
	public const AVAILABLE_VALUES = [
		'Y', 'N'
	];

	public static function onContentViewed(array $params)
	{
		if (
			!isset($params['userId'], $params['logId'])
			|| !is_array($params)
			|| (int)$params['userId'] <= 0
			|| (int)$params['logId'] <= 0
			|| !Loader::includeModule('im')
		)
		{
			return;
		}

		$CIMNotify = new \CIMNotify();
		$CIMNotify->markNotifyReadBySubTag(array("SONET|EVENT|".(int)$params['logId']."|".(int)$params['userId']));
	}

	/**
	 * Adds/deletes a subscription on a worgroup GROUP_ID for a user USER_ID
	 * @param array $params
	 * @return bool
	 * @throws Exception
	 */
	public static function set(array $params = [])
	{
		global $USER;

		$groupId = (int)($params['GROUP_ID'] ?? 0);
		$userId = (int)($params['USER_ID'] ?? $USER->getId());
		$value = (isset($params['VALUE']) && in_array($params['VALUE'], [ 'Y', 'N' ]) ? $params['VALUE'] : false);

		if (
			$groupId <= 0
			|| $userId <= 0
			|| !$value
		)
		{
			throw new SystemException(Loc::getMessage('SOCIALNETWORK_ITEM_SUBSCRIPTION_ERROR_NO_DATA'));
		}

		if (!(\CSocNetGroup::getById($groupId, true)))
		{
			throw new SystemException(Loc::getMessage('SOCIALNETWORK_ITEM_SUBSCRIPTION_ERROR_NO_ACCESS'));
		}

		$userRole = \CSocNetUserToGroup::getUserRole($userId, $groupId);
		if (!in_array($userRole, \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember()))
		{
			throw new SystemException(Loc::getMessage('SOCIALNETWORK_ITEM_SUBSCRIPTION_ERROR_NO_ACCESS'));
		}

		if (!\CSocNetSubscription::set($userId, 'SG' . $groupId, $value))
		{
			throw new SystemException(Loc::getMessage('SOCIALNETWORK_ITEM_SUBSCRIPTION_ERROR_FAILED'));
		}

		$res = \CSocNetSubscription::getList(
			[],
			[
				'USER_ID' => $userId,
				'CODE' => 'SG' . $groupId,
			]
		);

		return $res->fetch();
	}
}