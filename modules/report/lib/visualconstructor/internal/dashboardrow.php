<?php
namespace Bitrix\Report\VisualConstructor\Internal;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;

/**
 * Class DashboardRowTable
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