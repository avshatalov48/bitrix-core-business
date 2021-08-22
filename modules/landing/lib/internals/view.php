<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class ViewTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_View_Query query()
 * @method static EO_View_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_View_Result getById($id)
 * @method static EO_View_Result getList(array $parameters = array())
 * @method static EO_View_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_View createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_View_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_View wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_View_Collection wakeUpCollection($rows)
 */
class ViewTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_view';
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
			'LID' => new Entity\IntegerField('LID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LID'),
				'required' => true
			)),
			'USER_ID' => new Entity\IntegerField('USER_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_USER_ID'),
				'required' => true
			)),
			'VIEWS' => new Entity\IntegerField('VIEWS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_VIEWS'),
				'required' => true
			)),
			'FIRST_VIEW' => new Entity\DatetimeField('FIRST_VIEW', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LAST_VIEW'),
				'required' => true
			)),
			'LAST_VIEW' => new Entity\DatetimeField('LAST_VIEW', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LAST_VIEW'),
				'required' => true
			))
		);
	}
}