<?php

namespace Bitrix\Report\VisualConstructor\Internal;

use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;

/**
 * Class ReportTable
 * @package Bitrix\Intranet\Reports\Entity
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