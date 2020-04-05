<?php
namespace Bitrix\Main\Localization;

class LanguageTable extends \Bitrix\Main\ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_language';
	}

	public static function getMap()
	{
		return array(
			'LID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'ID' => array(
				'data_type' => 'string',
				'expression' => array('%s', 'LID'),
			),
			'LANGUAGE_ID' => array(
				'data_type' => 'string',
				'expression' => array('%s', 'LID'),
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'DEF' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'CULTURE_ID' => array(
				'data_type' => 'integer',
			),
			'CULTURE' => array(
				'data_type' => 'Bitrix\Main\Localization\Culture',
				'reference' => array('=this.CULTURE_ID' => 'ref.ID'),
			),
		);
	}
}
