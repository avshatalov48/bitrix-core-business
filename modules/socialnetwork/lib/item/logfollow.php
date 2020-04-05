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
	public static function checkDestinationsFollowStatus($params = array())
	{
		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		if ($logId <= 0)
		{
			return false;
		}

		$destUserIdList = array();
		$res = LogRightTable::getList(array(
			'filter' => array(
				'LOG_ID' => $logId
			),
			'select' => array('GROUP_CODE')
		));
		while ($logRight = $res->fetch())
		{
			if (preg_match('/^U(\d+)$/', $logRight['GROUP_CODE'], $matches))
			{
				$destUserIdList[] = $matches[1];
			}
		}

		$defaultFollowValue = false;
		$userFollowValue = array();

		if (!empty($destUserIdList))
		{
			$defaultFollowValue = LogFollowTable::getDefaultValue(array(
				'USER_ID' => false
			));

			$res = LogFollowTable::getList(array(
				'filter' => array(
					'CODE' => array('**', 'L'.$logId),
					'@USER_ID' => $destUserIdList
				),
				'select' => array('CODE', 'TYPE', 'USER_ID')
			));
			while($logFollow = $res->fetch())
			{
				if (!isset($userFollowValue[$logFollow['USER_ID']]))
				{
					$userFollowValue[$logFollow['USER_ID']] = array();
				}
				$userFollowValue[$logFollow['USER_ID']][$logFollow['CODE']] = $logFollow['TYPE'];
			}
		}

		foreach($destUserIdList as $destUserId)
		{
			if (
				(
					!isset($userFollowValue[$destUserId])
					&& $defaultFollowValue == 'N'
				)
				|| (
					isset($userFollowValue[$destUserId])
					&& !isset($userFollowValue[$destUserId]['L'.$logId]) // && isset($userFollowValue[$destUserId]['**'])
					&& $userFollowValue[$destUserId]['**'] == 'N'
				)
			)
			{
				\CSocNetLogFollow::set(
					intval($destUserId),
					"L".$logId,
					"Y",
					ConvertTimeStamp(time() + \CTimeZone::getOffset(), "FULL", SITE_ID)
				);
			}
		}

		return true;
	}
}
