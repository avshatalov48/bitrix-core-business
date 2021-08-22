<?php

namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;


/**
 * Class AgreementTable
 * @package Bitrix\Sender\Internals\Model
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Agreement_Query query()
 * @method static EO_Agreement_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Agreement_Result getById($id)
 * @method static EO_Agreement_Result getList(array $parameters = array())
 * @method static EO_Agreement_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_Agreement createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_Agreement_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_Agreement wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_Agreement_Collection wakeUpCollection($rows)
 */
class AgreementTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_agreement';
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
			new Entity\IntegerField('USER_ID', array(
				'required' => true
			)),
			new Entity\StringField('NAME', array(
				'validation' => function ()
				{
					return array(
						new Entity\Validator\Length(null, 100),
					);
				}
			)),
			new Entity\StringField('EMAIL', array(
				'required' => false,
				'validation' => function ()
				{
					return array(
						new Entity\Validator\Length(null, 255),
					);
				}
			)),
			new Entity\DatetimeField('DATE', array(
				'required' => true,
				'default_value' => new DateTime()
			)),
			new Entity\StringField('IP_ADDRESS', array(
				'required' => true,
				'validation' => function ()
				{
					return array(
						new Entity\Validator\Length(null, 39),
					);
				}
			)),
		);
	}
}