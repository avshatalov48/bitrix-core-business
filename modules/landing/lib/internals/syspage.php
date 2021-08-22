<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class SyspageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Syspage_Query query()
 * @method static EO_Syspage_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Syspage_Result getById($id)
 * @method static EO_Syspage_Result getList(array $parameters = array())
 * @method static EO_Syspage_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_Syspage createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_Syspage_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_Syspage wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_Syspage_Collection wakeUpCollection($rows)
 */
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