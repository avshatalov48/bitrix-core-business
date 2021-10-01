<?php
namespace Bitrix\Report\VisualConstructor\Internal;


use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class WidgetConfigurationTable
 * @package Bitrix\Report\VisualConstructor\Internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WidgetConfiguration_Query query()
 * @method static EO_WidgetConfiguration_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_WidgetConfiguration_Result getById($id)
 * @method static EO_WidgetConfiguration_Result getList(array $parameters = array())
 * @method static EO_WidgetConfiguration_Entity getEntity()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration createObject($setDefaultValues = true)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection createCollection()
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration wakeUpObject($row)
 * @method static \Bitrix\Report\VisualConstructor\Internal\EO_WidgetConfiguration_Collection wakeUpCollection($rows)
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