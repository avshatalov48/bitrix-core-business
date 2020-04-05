<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class HookDataTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_hook_data';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_ID'),
				'required' => true
			)),
			'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_TYPE'),
				'required' => true
			)),
			'HOOK' => new Entity\StringField('HOOK', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_HOOK'),
				'required' => true
			)),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CODE'),
				'required' => true
			)),
			'VALUE' => new Entity\StringField('VALUE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_VALUE')
			)),
			'PUBLIC' => new Entity\StringField('PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PUBLIC'),
				'default_value' => 'N'
			))
		);
	}
}
