<?php

namespace Bitrix\Main;

use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;

/**
 * Class TaskTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Task_Query query()
 * @method static EO_Task_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Task_Result getById($id)
 * @method static EO_Task_Result getList(array $parameters = [])
 * @method static EO_Task_Entity getEntity()
 * @method static \Bitrix\Main\EO_Task createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_Task_Collection createCollection()
 * @method static \Bitrix\Main\EO_Task wakeUpObject($row)
 * @method static \Bitrix\Main\EO_Task_Collection wakeUpCollection($rows)
 */
class TaskTable extends Entity\DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_task';
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
			'LETTER' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateLetter'),
			),
			'MODULE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateModuleId'),
			),
			'SYS' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateSys'),
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
			new Entity\Validator\Length(null, 100),
		);
	}

	public static function validateLetter()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}

	public static function validateModuleId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	public static function validateSys()
	{
		return array(
			new Entity\Validator\Length(null, 1),
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
