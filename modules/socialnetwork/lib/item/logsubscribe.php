<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Socialnetwork\LogSubscribeTable;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Main\Loader;

class LogSubscribe
{
	public static function sendPush($params = array())
	{
		if (!Loader::includeModule('pull'))
		{
			return false;
		}

		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		$commentId = (isset($params['commentId']) ? intval($params['commentId']) : 0);
		if (
			$logId <= 0
			&& $commentId > 0
		)
		{
			$res = LogCommentTable::getList(array(
				'filter' => array(
					'=ID' => $commentId
				),
				'select' => array('LOG_ID')
			));
			if ($logCommentFields = $res->fetch())
			{
				$logId = $logCommentFields['LOG_ID'];
			}
		}

		if ($logId <= 0)
		{
			return false;
		}

		$res = LogSubscribeTable::getList(array(
			'filter' => array(
				'=LOG_ID' => $logId,
				'=TYPE' => LogSubscribeTable::TYPE_COUNTER_COMMENT_PUSH
			),
			'select' => array('USER_ID')
		));
		while($subscribeFields = $res->fetch())
		{
			\Bitrix\Pull\MobileCounter::send($subscribeFields['USER_ID']);
		}
	}
}
