<?php
namespace Bitrix\Report\VisualConstructor\Internal;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

/**
 * Class DashboardRowTable
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