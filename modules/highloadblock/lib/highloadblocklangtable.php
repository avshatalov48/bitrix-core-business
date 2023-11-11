<?php
namespace Bitrix\Highloadblock;

use Bitrix\Main\Entity;

/**
 * Class HighloadBlockLangTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_HighloadBlockLang_Query query()
 * @method static EO_HighloadBlockLang_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_HighloadBlockLang_Result getById($id)
 * @method static EO_HighloadBlockLang_Result getList(array $parameters = [])
 * @method static EO_HighloadBlockLang_Entity getEntity()
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockLang createObject($setDefaultValues = true)
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection createCollection()
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockLang wakeUpObject($row)
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection wakeUpCollection($rows)
 */
class HighloadBlockLangTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_hlblock_entity_lang';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true
			)),
			'LID' => new Entity\StringField('LID', array(
				'primary' => true,
				'required' => true,
				'validation' => array(__CLASS__, 'validateLid'),
			)),
			'NAME' => new Entity\StringField('NAME', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
			)),
		);
	}

	/**
	 * Returns validators for LID field.
	 *
	 * @return array
	 */
	public static function validateLid()
	{
		return array(
			new Entity\Validator\Length(null, 2),
		);
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}
}