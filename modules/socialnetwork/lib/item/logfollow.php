<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Socialnetwork\LogRightTable;
use Bitrix\Socialnetwork\LogFollowTable;

/**
 * Class for incoming mail event handlers
 *
 * Class MailHandler
 * @package Bitrix\Socialnetwork\Internals
 */

class LogFollow
{
	/**
	 * check if all the log entry destinations-users with Smart Follow are subscribed on a log entry
	 *
	 * @param array $params
	 * @return true|false
	 */
	public static function checkDestinationsFollowStatus($params = [])
	{
		$logId = (isset($params['logId']) ? (int)$params['logId'] : 0);
		if ($logId <= 0)
		{
			return false;
		}

		$key = 'L' . $logId;

		$destUserIdList = [];
		$res = LogRightTable::getList([
			'filter' => [
				'LOG_ID' => $logId
			],
			'select' => [ 'GROUP_CODE' ]
		]);
		while ($logRight = $res->fetch())
		{
			if (preg_match('/^U(\d+)$/', $logRight['GROUP_CODE'], $matches))
			{
				$destUserIdList[] = $matches[1];
			}
		}

		$defaultFollowValue = false;
		$userFollowValue = [];

		if (!empty($destUserIdList))
		{
			$defaultFollowValue = LogFollowTable::getDefaultValue([
				'USER_ID' => false
			]);

			$res = LogFollowTable::getList([
				'filter' => [
					'=CODE' => [ '**', $key ],
					'@USER_ID' => $destUserIdList
				],
				'select' => [ 'CODE', 'TYPE', 'USER_ID' ]
			]);
			while($logFollow = $res->fetch())
			{
				if (!isset($userFollowValue[$logFollow['USER_ID']]))
				{
					$userFollowValue[$logFollow['USER_ID']] = [];
				}
				$userFollowValue[$logFollow['USER_ID']][$logFollow['CODE']] = $logFollow['TYPE'];
			}
		}

		foreach($destUserIdList as $destUserId)
		{
			$subscribeTypeList = [];

			if (
				(
					!isset($userFollowValue[$destUserId])
					&& $defaultFollowValue === 'N'
				)
				|| (
					isset($userFollowValue[$destUserId])
					&& !isset($userFollowValue[$destUserId][$key]) // && isset($userFollowValue[$destUserId]['**'])
					&& $userFollowValue[$destUserId]['**'] === 'N'
				)
			)
			{
				$subscribeTypeList[] = 'FOLLOW';
			}

			\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe([
				'logId' => $logId,
				'userId' => $destUserId,
				'typeList' => $subscribeTypeList,
				'followDate' => 'CURRENT'
			]);

		}

		return true;
	}
}
