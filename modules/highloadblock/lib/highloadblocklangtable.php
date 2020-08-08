<?php
namespace Bitrix\Highloadblock;

use Bitrix\Main\Entity;

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