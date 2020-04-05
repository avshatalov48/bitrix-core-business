<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage lists
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Lists\Integration\Main;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogTable;

class RatingVote
{
	public static function onGetRatingContentOwner($params)
	{
		if(
			intval($params['ENTITY_ID'])
			&& $params['ENTITY_TYPE_ID'] == 'LISTS_NEW_ELEMENT'
			&& Loader::includeModule('socialnetwork')
		)
		{
			$res = LogTable::getList(array(
				'filter' => array(
					'=SOURCE_ID' => $params['ENTITY_ID'],
					'EVENT_ID' => 'lists_new_element'
				),
				'select' => array('USER_ID')
			));
			if (
				($logFields = $res->fetch())
				&& intval($logFields['USER_ID']) > 0
			)
			{
				return intval($logFields['USER_ID']);
			}
		}

		return false;
	}

}
?>