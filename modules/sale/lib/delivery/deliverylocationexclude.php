<?php

namespace Bitrix\Sale\Delivery;

/**
 * Class DeliveryLocationExcludeTable
 * @package Bitrix\Sale\Delivery
 */
final class DeliveryLocationExcludeTable extends DeliveryLocationTable
{
	const DB_LOCATION_FLAG = 'LE';
	const DB_GROUP_FLAG = 	'GE';

	public static function getFilePath()
	{
		return __FILE__;
	}
}
