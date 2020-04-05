<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use	Bitrix\Sale\Internals\StatusTable;

/**
 * Class DeliveryStatus
 * @package Bitrix\Sale
 */
class DeliveryStatus extends StatusBase
{
	const TYPE = 'D';

	/**
	 * @return mixed
	 */
	public static function getInitialStatus()
	{
		return 'DN';
	}

	/**
	 * @return mixed
	 */
	public static function getFinalStatus()
	{
		return 'DF';
	}
}
