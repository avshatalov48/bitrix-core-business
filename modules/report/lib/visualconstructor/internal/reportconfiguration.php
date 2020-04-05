<?php

namespace Bitrix\Report\VisualConstructor\Internal;

use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class ReportConfigurationTable
 * @package Bitrix\Report\VisualConstructor\Internal
 */
class ReportConfigurationTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_visual_report_entity_config';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new IntegerField('REPORT_ID', array(
				'primary' => true
			)),
			new IntegerField('CONFIGURATION_ID', array(
				'primary' => true
			)),
			new ReferenceField(
				'REPORT',
				'Bitrix\Report\VisualConstructor\Internal\ReportTable',
				array('=this.REPORT_ID' => 'ref.ID')
			),
			new ReferenceField(
				'CONFIGURATION_SETTING',
				'Bitrix\Report\VisualConstructor\Internal\ConfigurationSettingTable',
				array('=this.CONFIGURATION_ID' => 'ref.ID')
			),
		);
	}
}