<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class CallTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_im_call';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new Entity\IntegerField('TYPE'),
			new Entity\IntegerField('INITIATOR_ID'),
			new Entity\StringField('IS_PUBLIC', array(
				'default_value' => 'N'
			)),
			new Entity\StringField('PUBLIC_ID'),
			new Entity\StringField('PROVIDER'),
			new Entity\StringField('ENTITY_TYPE'),
			new Entity\StringField('ENTITY_ID'),
			new Entity\IntegerField('PARENT_ID'),
			new Entity\StringField('STATE'),
			new Entity\DatetimeField('START_DATE', array(
				'default_value' => function()
				{
					return new DateTime();
				}
			)),
			new Entity\DatetimeField('END_DATE'),
		);
	}
}