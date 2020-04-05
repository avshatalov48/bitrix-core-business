<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Socialnetwork\LogTable;

class Log
{
	private $fields;

	public static function getById($logId = 0)
	{
		static $cachedFields = array();

		$logItem = false;
		$logId = intval($logId);

		if ($logId > 0)
		{
			$logItem = new Log;
			$logFields = array();

			if (isset($cachedFields[$logId]))
			{
				$logFields = $cachedFields[$logId];
			}
			else
			{
				$select = array('*');

				$res = LogTable::getList(array(
					'filter' => array('=ID' => $logId),
					'select' => $select
				));
				if ($fields = $res->fetch())
				{
					$logFields = $fields;

					if ($logFields['LOG_DATE'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$logFields['LOG_DATE'] = $logFields['LOG_DATE']->toString();
					}
					if ($logFields['LOG_UPDATE'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$logFields['LOG_UPDATE'] = $logFields['LOG_UPDATE']->toString();
					}
				}

				$cachedFields[$logId] = $logFields;
			}

			$logItem->setFields($logFields);
		}

		return $logItem;
	}

	public function setFields($fields = array())
	{
		$this->fields = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public static function setLimitedView($params = array())
	{
		return false;
	}

}
