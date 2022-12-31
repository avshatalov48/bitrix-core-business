<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\SystemException;

/**
 * Class QueueMessageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_QueueMessage_Query query()
 * @method static EO_QueueMessage_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_QueueMessage_Result getById($id)
 * @method static EO_QueueMessage_Result getList(array $parameters = [])
 * @method static EO_QueueMessage_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_QueueMessage createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_QueueMessage_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_QueueMessage wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_QueueMessage_Collection wakeUpCollection($rows)
 */
class QueueMessageTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_queue_message';
	}

	/**
	 * @return array
	 *
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new ArrayField('MESSAGE'))
			,
			(new DatetimeField('DATE_CREATE'))
		];
	}
}