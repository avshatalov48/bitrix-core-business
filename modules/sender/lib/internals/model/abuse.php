<?php

namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;


/**
 * Class AbuseTable
 * @package Bitrix\Sender\Internals\Model
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Abuse_Query query()
 * @method static EO_Abuse_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Abuse_Result getById($id)
 * @method static EO_Abuse_Result getList(array $parameters = array())
 * @method static EO_Abuse_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_Abuse createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_Abuse_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_Abuse wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_Abuse_Collection wakeUpCollection($rows)
 */
class AbuseTable extends Entity\DataManager
{
	const COUNTER_CODE_ABUSES = 'abuses';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_abuse';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new Entity\StringField('TEXT', array(
				'required' => false
			)),
			new Entity\IntegerField('CONTACT_ID', array(
				'required' => false
			)),
			new Entity\StringField('CONTACT_CODE', array(
				'required' => true
			)),
			new Entity\IntegerField('CONTACT_TYPE_ID', array(
				'required' => true,
			)),
			new Entity\DatetimeField('DATE_INSERT', array(
				'required' => true,
				'default_value' => new DateTime()
			)),
		);
	}

	/**
	 * After add event handler.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onAfterAdd(Entity\Event $event)
	{
		CounterTable::incrementByCode(self::COUNTER_CODE_ABUSES);
		DailyCounterTable::incrementFieldValue('ABUSE_CNT');
		return new Entity\EventResult();
	}

	/**
	 * Get count of new abuses.
	 *
	 * @return int
	 */
	public static function getCountOfNew()
	{
		return CounterTable::getValueByCode(self::COUNTER_CODE_ABUSES);
	}

	/**
	 * Reset count of new abuses.
	 *
	 * @return bool
	 */
	public static function resetCountOfNew()
	{
		return CounterTable::resetValueByCode(self::COUNTER_CODE_ABUSES);
	}
}