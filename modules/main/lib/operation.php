<?php

namespace Bitrix\Main;

use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;

/**
 * Class OperationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Operation_Query query()
 * @method static EO_Operation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Operation_Result getById($id)
 * @method static EO_Operation_Result getList(array $parameters = [])
 * @method static EO_Operation_Entity getEntity()
 * @method static \Bitrix\Main\EO_Operation createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_Operation_Collection createCollection()
 * @method static \Bitrix\Main\EO_Operation wakeUpObject($row)
 * @method static \Bitrix\Main\EO_Operation_Collection wakeUpCollection($rows)
 */
class OperationTable extends Entity\DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_operation';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
			),
			'MODULE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateModuleId'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDescription'),
			),
			'BINDING' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateBinding'),
			),
		);
	}

	public static function validateName()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	public static function validateModuleId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	public static function validateDescription()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	public static function validateBinding()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
}
