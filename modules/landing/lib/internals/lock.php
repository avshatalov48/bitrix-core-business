<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class LockTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_entity_lock';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'title' => 'ID',
				'primary' => true
			)),
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LOCK_ENTITY_ID'),
				'required' => true
			)),
			'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LOCK_ENTITY_TYPE'),
				'required' => true
			)),
			'LOCK_TYPE' => new Entity\StringField('LOCK_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LOCK_TYPE'),
				'required' => true
			))
		);
	}
}
