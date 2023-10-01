<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class Table
 * @package Bitrix\Sale\Internals
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Reliability_Query query()
 * @method static EO_Reliability_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Reliability_Result getById($id)
 * @method static EO_Reliability_Result getList(array $parameters = [])
 * @method static EO_Reliability_Entity getEntity()
 * @method static \Sale\Handlers\Delivery\Additional\RusPost\Reliability\Reliability createObject($setDefaultValues = true)
 * @method static \Sale\Handlers\Delivery\Additional\RusPost\Reliability\ReliabilityCollection createCollection()
 * @method static \Sale\Handlers\Delivery\Additional\RusPost\Reliability\Reliability wakeUpObject($row)
 * @method static \Sale\Handlers\Delivery\Additional\RusPost\Reliability\ReliabilityCollection wakeUpCollection($rows)
 */
class ReliabilityTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_ruspost_reliability';
	}

	public static function getObjectClass()
	{
		return \Sale\Handlers\Delivery\Additional\RusPost\Reliability\Reliability::class;
	}

	public static function getCollectionClass()
	{
		return \Sale\Handlers\Delivery\Additional\RusPost\Reliability\ReliabilityCollection::class;
	}
	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new Fields\StringField('HASH'))
				->configurePrimary(true),

			new Fields\IntegerField('RELIABILITY'),
			new Fields\StringField('ADDRESS')	,
			new Fields\StringField('FULL_NAME'),
			new Fields\StringField('PHONE'),
			new Fields\DatetimeField('UPDATED_AT')
		];
	}
}