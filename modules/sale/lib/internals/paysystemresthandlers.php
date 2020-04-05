<?php
namespace Bitrix\Sale\Internals;

/**
 * Class PaySystemRestHandlersTable
 * @package Bitrix\Sale\Internals
 */
class PaySystemRestHandlersTable extends \Bitrix\Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_pay_system_rest_handlers';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'CODE' => array(
				'data_type' => 'string'
			),
			'SORT' => array(
				'data_type' => 'integer'
			),
			'SETTINGS' => array(
				'data_type' => 'string',
				'serialized' => true
			),
		);
	}
}
