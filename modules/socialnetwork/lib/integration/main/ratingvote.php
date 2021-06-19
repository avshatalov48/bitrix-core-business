<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Main;

use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\LogCommentTable;

class RatingVote
{
	public static function onGetRatingContentOwner($params)
	{
		if(intval($params['ENTITY_ID']) && $params['ENTITY_TYPE_ID'] == 'LOG_COMMENT')
		{
			$res = LogCommentTable::getList(array(
				'filter' => array(
					'ID' => $params['ENTITY_ID']
				),
				'select' => array('USER_ID')
			));
			if (
				($logCommentFields = $res->fetch())
				&& intval($logCommentFields['USER_ID']) > 0
			)
			{
				return intval($logCommentFields['USER_ID']);
			}
		}

		return false;
	}
}
