<?php

namespace Bitrix\Report\VisualConstructor\Internal;

use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;

/**
 * Class ReportTable
 * @package Bitrix\Intranet\Reports\Entity
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Report_Query query()
 * @method static EO_Report_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Report_Result getById($id)
 * @method static EO_Report_Result getList(array $parameters = array())
 * @method static EO_Report_Entity getEntity()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Report createObject($setDefaultValues = true)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection createCollection()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Report wakeUpObject($row)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_Report_Collection wakeUpCollection($rows)
 */
class ReportTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_visual_report_entity';
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
			new IntegerField('WIDGET_ID'),
			new StringField('GID'),
			new IntegerField('WEIGHT'),
			new StringField('REPORT_CLASS'),
			new DateField('CREATED_DATE'),
			new DateField('UPDATED_DATE'),
			new ReferenceField(
				'WIDGET',
				'Bitrix\Report\VisualConstructor\Internal\WidgetTable',
				array('=this.WIDGET_ID' => 'ref.ID')
			)
		);
	}
}