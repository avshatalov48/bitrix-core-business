<?php
namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main;

class RestAppTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_messageservice_rest_app';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'APP_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateVarchar128'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateVarchar128'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateType'),
			),
			'HANDLER' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateHandler'),
			),
			'DATE_ADD' => array(
				'data_type' => 'datetime',
				'default_value' => new Main\Type\DateTime(),
			),
			'AUTHOR_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'AUTHOR' => array(
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => array(
					'=this.AUTHOR_ID' => 'ref.ID'
				),
				'join_type' => 'LEFT',
			),
		);
	}

	/**
	 * @return array
	 */
	public static function validateVarchar128()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
		);
	}

	/**
	 * @return array
	 */
	public static function validateType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 30),
		);
	}

	/**
	 * @return array
	 */
	public static function validateHandler()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1000),
		);
	}
}