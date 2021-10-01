<?php
namespace Bitrix\Sale\Internals;

/**
 * Class PaySystemRestHandlersTable
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PaySystemRestHandlers_Query query()
 * @method static EO_PaySystemRestHandlers_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PaySystemRestHandlers_Result getById($id)
 * @method static EO_PaySystemRestHandlers_Result getList(array $parameters = array())
 * @method static EO_PaySystemRestHandlers_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestHandlers createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestHandlers_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestHandlers wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestHandlers_Collection wakeUpCollection($rows)
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
			'APP_ID' => array(
				'data_type' => 'string'
			),
		);
	}
}
