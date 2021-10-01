<?php
namespace Bitrix\Report\VisualConstructor\Internal;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

/**
 * Class DashboardRowTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Dashboard_Query query()
 * @method static EO_Dashboard_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Dashboard_Result getById($id)
 * @method static EO_Dashboard_Result getList(array $parameters = array())
 * @method static EO_Dashboard_Entity getEntity()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard createObject($setDefaultValues = true)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection createCollection()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard wakeUpObject($row)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Dashboard_Collection wakeUpCollection($rows)
 */
class DashboardTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_visual_report_dashboard';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			new StringField('GID'),
			new StringField('BOARD_KEY'),
			new IntegerField('USER_ID'),
			new StringField('VERSION'),
			new DateField('CREATED_DATE'),
			new DateField('UPDATED_DATE'),
		);
	}
}