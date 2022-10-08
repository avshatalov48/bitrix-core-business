<?php
namespace Bitrix\Main\Localization;

/**
 * Class LanguageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Language_Query query()
 * @method static EO_Language_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Language_Result getById($id)
 * @method static EO_Language_Result getList(array $parameters = [])
 * @method static EO_Language_Entity getEntity()
 * @method static \Bitrix\Main\Localization\EO_Language createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Localization\EO_Language_Collection createCollection()
 * @method static \Bitrix\Main\Localization\EO_Language wakeUpObject($row)
 * @method static \Bitrix\Main\Localization\EO_Language_Collection wakeUpCollection($rows)
 */
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
			'CODE' => array(
				'data_type' => 'string',
			),
			'CULTURE' => array(
				'data_type' => 'Bitrix\Main\Localization\Culture',
				'reference' => array('=this.CULTURE_ID' => 'ref.ID'),
			),
		);
	}
}
