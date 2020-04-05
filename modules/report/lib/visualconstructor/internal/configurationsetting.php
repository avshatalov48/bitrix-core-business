<?php

namespace Bitrix\Report\VisualConstructor\Internal;

use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;

/**
 * Class ConfigurationSettingTable
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class ConfigurationSettingTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_visual_report_configuration';
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
			new IntegerField('WEIGHT'),
			new StringField('GID'),
			new StringField('UKEY'),
			new StringField('CONFIGURATION_FIELD_CLASS'),
			new TextField('SETTINGS'),
			new DateField('CREATED_DATE'),
			new DateField('UPDATED_DATE'),
		);
	}
}