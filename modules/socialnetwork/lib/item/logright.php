<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Socialnetwork\LogRightTable;

class LogRight
{
	public static function get(int $logId = 0)
	{
		$result = [];

		if ($logId <= 0)
		{
			return $result;
		}

		$res = LogRightTable::getList([
			'filter' => [
				'=LOG_ID' => $logId
			],
			'select' => [ 'GROUP_CODE' ]
		]);
		while ($logRightFields = $res->fetch())
		{
			$result[] = $logRightFields['GROUP_CODE'];
		}

		return $result;
	}

	public static function OnAfterLogUpdate(\Bitrix\Main\Entity\Event $event)
	{
		$primary = $event->getParameter('primary');
		$logId = (!empty($primary['ID']) ? intval($primary['ID']) : 0);
		$fields = $event->getParameter('fields');

		if (
			$logId > 0
			&& !empty($fields)
			&& !empty($fields['LOG_UPDATE'])
		)
		{
			LogRightTable::setLogUpdate(array(
				'logId' => $logId,
				'value' => $fields['LOG_UPDATE']
			));
		}
	}
}
