<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class PlacementTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Placement_Query query()
 * @method static EO_Placement_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Placement_Result getById($id)
 * @method static EO_Placement_Result getList(array $parameters = array())
 * @method static EO_Placement_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_Placement createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_Placement_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_Placement wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_Placement_Collection wakeUpCollection($rows)
 */
class PlacementTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_placement';
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
			'APP_ID' => new Entity\IntegerField('APP_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_APP_ID'),
				'required' => true
			)),
			'PLACEMENT' => new Entity\StringField('PLACEMENT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PLACEMENT'),
				'required' => true
			)),
			'PLACEMENT_HANDLER' => new Entity\StringField('PLACEMENT_HANDLER', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PLACEMENT_HANDLER'),
				'required' => true
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PLC_TITLE'),
				'required' => true
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}
}