<?php

namespace Bitrix\Report\VisualConstructor\Internal;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;

/**
 * Class ReportWidgetTable
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Widget_Query query()
 * @method static EO_Widget_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Widget_Result getById($id)
 * @method static EO_Widget_Result getList(array $parameters = array())
 * @method static EO_Widget_Entity getEntity()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Widget createObject($setDefaultValues = true)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection createCollection()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Widget wakeUpObject($row)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Widget_Collection wakeUpCollection($rows)
 */
class WidgetTable extends DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_visual_report_widget';
	}

	/**
	 * Returns entity map definition.
	 * To get initialized fields.
	 * @see \Bitrix\Main\Entity\Base::getFields() and \Bitrix\Main\Entity\Base::getField().
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
			new StringField('BOARD_ID'),
			new IntegerField('DASHBOARD_ROW_ID'),
			new IntegerField('PARENT_WIDGET_ID'),
			new StringField('WEIGHT'),
			new StringField('CATEGORY_KEY'),
			new StringField('VIEW_KEY'),
			new IntegerField('OWNER_ID'),
			new StringField('WIDGET_CLASS'),
			new DateField('CREATED_DATE'),
			new DateField('UPDATED_DATE'),
			new BooleanField('IS_PATTERN'),
			new ReferenceField(
				'ROW',
				'Bitrix\Report\VisualConstructor\Internal\DashboardRowTable',
				array('=this.DASHBOARD_ROW_ID' => 'ref.ID')
			),
			new ReferenceField(
				'PARENTWIDGET',
				'Bitrix\Report\VisualConstructor\Internal\WidgetTable',
				array('=this.PARENT_WIDGET_ID' => 'ref.ID')
			)
		);
	}
}