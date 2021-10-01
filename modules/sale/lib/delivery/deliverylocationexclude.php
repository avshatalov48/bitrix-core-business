<?php

namespace Bitrix\Sale\Delivery;

/**
 * Class DeliveryLocationExcludeTable
 * @package Bitrix\Sale\Delivery
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DeliveryLocationExclude_Query query()
 * @method static EO_DeliveryLocationExclude_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_DeliveryLocationExclude_Result getById($id)
 * @method static EO_DeliveryLocationExclude_Result getList(array $parameters = array())
 * @method static EO_DeliveryLocationExclude_Entity getEntity()
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocationExclude createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocationExclude_Collection createCollection()
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocationExclude wakeUpObject($row)
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocationExclude_Collection wakeUpCollection($rows)
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
