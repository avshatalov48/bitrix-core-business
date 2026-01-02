<?php

namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\DeleteTrait;
use Bitrix\Im\V2\Common\MultiplyInsertTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Validation\Rule\Length;

class FileParamTable extends DataManager
{
	use MultiplyInsertTrait;
	use DeleteTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_file_param';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'DISK_FILE_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'PARAM_NAME' => [
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateParamName'),
			],
			'PARAM_VALUE' => [
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateParamValue'),
			],
		];
	}

	/**
	 * Returns validators for PARAM_NAME field.
	 *
	 * @return array
	 */
	public static function validateParamName()
	{
		return [
			new Length(null, 100),
		];
	}

	/**
	 * Returns validators for PARAM_VALUE field.
	 *
	 * @return array
	 */
	public static function validateParamValue()
	{
		return [
			new Length(null, 100),
		];
	}
}
