<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class SyspageTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_syspage';
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
			'SITE_ID' => new Entity\IntegerField('SITE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_ID'),
				'required' => true
			)),
			'TYPE' => new Entity\StringField('TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TYPE'),
				'required' => true
			)),
			'LANDING_ID' => new Entity\IntegerField('LANDING_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID'),
				'required' => true
			))
		);
	}
}