<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use	Bitrix\Sale\Internals\StatusTable;

/**
 * Class OrderStatus
 * @package Bitrix\Sale
 */
class OrderStatus extends StatusBase
{
	const TYPE = 'O';

	/**
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public static function getDisallowPayStatusList()
	{
		$allowFlag = false;
		$resultList = array();

		$allowPayStatus = Main\Config\Option::get("sale", "allow_pay_status", static::getInitialStatus());

		$statusList = static::getAllStatuses();
		if (!empty($statusList))
		{
			foreach ($statusList as $statusId)
			{
				if ($allowPayStatus == $statusId)
				{
					break;
				}

				if ($allowFlag === false)
				{
					$resultList[] = $statusId;
				}
			}
		}

		return $resultList;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public static function getAllowPayStatusList()
	{
		$allowFlag = false;
		$resultList = array();

		$allowPayStatus = Main\Config\Option::get("sale", "allow_pay_status", static::getInitialStatus());

		$statusList = static::getAllStatuses();
		if (!empty($statusList))
		{
			foreach ($statusList as $statusId)
			{
				if ($allowPayStatus == $statusId)
				{
					$allowFlag = true;
				}

				if ($allowFlag === true)
				{
					$resultList[] = $statusId;
				}
			}
		}

		return $resultList;
	}

	/**
	 * @param $statusId
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public static function isAllowPay($statusId)
	{
		$allowPayStatusList = static::getAllowPayStatusList();

		if (!empty($allowPayStatusList))
		{
			foreach ($allowPayStatusList as $allowStatusId)
			{
				if ($allowStatusId == $statusId)
				{
					return true;
				}

			}
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	public static function getInitialStatus()
	{
		return 'N';
	}

	/**
	 * @return mixed
	 */
	public static function getFinalStatus()
	{
		return 'F';
	}
}
