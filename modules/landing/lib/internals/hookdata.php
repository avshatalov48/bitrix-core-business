<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class HookDataTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_HookData_Query query()
 * @method static EO_HookData_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_HookData_Result getById($id)
 * @method static EO_HookData_Result getList(array $parameters = array())
 * @method static EO_HookData_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_HookData createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_HookData_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_HookData wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_HookData_Collection wakeUpCollection($rows)
 */
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
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_VALUE'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator')
			)),
			'PUBLIC' => new Entity\StringField('PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PUBLIC'),
				'default_value' => 'N'
			))
		);
	}
}
