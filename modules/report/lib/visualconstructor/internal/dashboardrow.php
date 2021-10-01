<?php
namespace Bitrix\Report\VisualConstructor\Internal;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;

/**
 * Class DashboardRowTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DashboardRow_Query query()
 * @method static EO_DashboardRow_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_DashboardRow_Result getById($id)
 * @method static EO_DashboardRow_Result getList(array $parameters = array())
 * @method static EO_DashboardRow_Entity getEntity()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow createObject($setDefaultValues = true)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection createCollection()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow wakeUpObject($row)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_DashboardRow_Collection wakeUpCollection($rows)
 */
class DashboardRowTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_visual_report_dashboard_row';
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
			new IntegerField('WEIGHT'),
			new IntegerField('BOARD_ID'),
			new TextField('LAYOUT_MAP', array('default' => '')),
			new DateField('CREATED_DATE'),
			new DateField('UPDATED_DATE'),
			new ReferenceField(
				'DASHBOARD',
				'Bitrix\Report\VisualConstructor\Internal\DashboardTable',
				array('=this.BOARD_ID' => 'ref.ID')
			),
		);
	}
}