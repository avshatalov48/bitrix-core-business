<?php
namespace Bitrix\Main\Rest;

use Bitrix\Main;
use Bitrix\Rest;

if(Main\Loader::includeModule("rest")):

class User extends \IRestService
{
	public static function getHistoryList($query, $nav = 0, \CRestServer $server)
	{
		global $USER;

		$query = array_change_key_case($query, CASE_LOWER);

		$filter = ($query['filter'] ?? array());
		$order = ($query['order'] ?? array('ID' => 'DESC'));

		static $filterFields = array("USER_ID", "DATE_INSERT", "EVENT_TYPE", "REMOTE_ADDR", "USER_AGENT", "REQUEST_URI", "FIELD");
		static $orderFields = array("ID");

		$queryFilter = static::sanitizeFilter(
			$filter,
			$filterFields,
			function($field, $value, $operation)
			{
				switch($field)
				{
					case 'DATE_INSERT':
						return Main\Type\DateTime::createFromUserTime(\CRestUtil::unConvertDateTime($value));
						break;
					case 'USER_ID':
					case 'FIELD':
						if($operation <> '=')
						{
							throw new Rest\RestException("Only '=' operation is allowed for the filter field {$field}.", Rest\RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
						}
						break;

				}
				return $value;
			}
		);

		if(!isset($queryFilter["=USER_ID"]))
		{
			throw new Rest\RestException("USER_ID filter field is required.", Rest\RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
		}

		if(!$USER->CanDoOperation('edit_all_users') && $queryFilter["=USER_ID"] <> $USER->GetID())
		{
			throw new Rest\AccessException();
		}

		if(isset($queryFilter["=FIELD"]))
		{
			$queryFilter['=\Bitrix\Main\UserProfileRecordTable:HISTORY.FIELD'] = $queryFilter["=FIELD"];
			unset($queryFilter["=FIELD"]);
		}

		$order = static::sanitizeOrder($order, $orderFields);

		$navParams = static::getNavData($nav, true);

		$dbRes = Main\UserProfileHistoryTable::getList(array(
			'filter' => $queryFilter,
			'limit' => $navParams['limit'],
			'offset' => $navParams['offset'],
			'count_total' => true,
			'order' => $order,
		));

		$result = array();
		while($event = $dbRes->fetch())
		{
			/** @var Main\Type\DateTime $ts */
			$ts = $event['DATE_INSERT'];
			$event['DATE_INSERT'] = \CRestUtil::convertDateTime($ts->toString());

			$result[] = $event;
		}

		return static::setNavData($result, array(
			"count" => $dbRes->getCount(),
			"offset" => $navParams['offset']
		));
	}

	public static function getHistoryFieldsList($query, $nav = 0, \CRestServer $server)
	{
		global $USER;

		$query = array_change_key_case($query, CASE_LOWER);

		$filter = ($query['filter'] ?? array());
		$order = ($query['order'] ?? array('ID' => 'ASC'));

		static $filterFields = array("HISTORY_ID", "FIELD");
		static $orderFields = array("ID");

		$queryFilter = static::sanitizeFilter(
			$filter,
			$filterFields,
			function($field, $value, $operation)
			{
				switch($field)
				{
					case 'HISTORY_ID':
					case 'FIELD':
						if($operation <> '=')
						{
							throw new Rest\RestException("Only '=' operation is allowed for the filter field {$field}.", Rest\RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
						}
						break;

				}
				return $value;
			}
		);

		if(!isset($queryFilter["=HISTORY_ID"]))
		{
			throw new Rest\RestException("HISTORY_ID filter field is required.", Rest\RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
		}

		if(!$USER->CanDoOperation('edit_all_users'))
		{
			$queryFilter["=HISTORY.USER_ID"] = $USER->GetID();
		}

		$order = static::sanitizeOrder($order, $orderFields);

		$dbRes = Main\UserProfileRecordTable::getList(array(
			'filter' => $queryFilter,
			'order' => $order,
		));

		$result = $dbRes->fetchAll();

		return $result;
	}
}

endif;