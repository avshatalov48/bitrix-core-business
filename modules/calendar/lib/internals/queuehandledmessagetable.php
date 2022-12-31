<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

/**
 * Class QueueHandledMessageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_QueueHandledMessage_Query query()
 * @method static EO_QueueHandledMessage_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_QueueHandledMessage_Result getById($id)
 * @method static EO_QueueHandledMessage_Result getList(array $parameters = [])
 * @method static EO_QueueHandledMessage_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_QueueHandledMessage createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_QueueHandledMessage wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection wakeUpCollection($rows)
 */
class QueueHandledMessageTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_queue_handled_message';
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
			(new IntegerField('MESSAGE_ID'))
			,
			(new IntegerField('QUEUE_ID'))
			,
			(new StringField('HASH'))
				->configureSize(255)
			,
			(new DatetimeField('DATE_CREATE'))
			,
			(new ReferenceField(
				'MESSAGE',
				QueueMessageTable::class,
				Join::on('this.MESSAGE_ID', 'ref.ID'),
			)),
		];
	}
}