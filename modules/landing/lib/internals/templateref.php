<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class TemplateRefTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_template_ref';
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
			'AREA' => new Entity\IntegerField('AREA', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_AREA'),
				'required' => true
			)),
			'LANDING_ID' => new Entity\IntegerField('LANDING_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID'),
				'required' => true
			))
		);
	}
}