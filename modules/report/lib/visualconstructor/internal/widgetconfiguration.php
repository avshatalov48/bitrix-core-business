<?php
namespace Bitrix\Report\VisualConstructor\Internal;


use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class WidgetConfigurationTable
 * @package Bitrix\Report\VisualConstructor\Internal
 */
class WidgetConfigurationTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_visual_report_widget_config';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new IntegerField('WIDGET_ID', array(
				'primary' => true
			)),
			new IntegerField('CONFIGURATION_ID', array(
				'primary' => true
			)),
			new ReferenceField(
				'WIDGET',
				'Bitrix\Report\VisualConstructor\Internal\WidgetTable',
				array('=this.WIDGET_ID' => 'ref.ID')
			),
			new ReferenceField(
				'CONFIGURATION_SETTING',
				'Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable',
				array('=this.CONFIGURATION_ID' => 'ref.ID')
			),
		);
	}

}