<?php

namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;


/**
 * Class AgreementTable
 * @package Bitrix\Sender\Internals\Model
 **/
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