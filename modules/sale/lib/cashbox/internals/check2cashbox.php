<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Entity\DataManager;

/**
 * Class Check2CashboxTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Check2Cashbox_Query query()
 * @method static EO_Check2Cashbox_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Check2Cashbox_Result getById($id)
 * @method static EO_Check2Cashbox_Result getList(array $parameters = array())
 * @method static EO_Check2Cashbox_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Check2Cashbox createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Check2Cashbox_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Check2Cashbox wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Check2Cashbox_Collection wakeUpCollection($rows)
 */
class Check2CashboxTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_check2cashbox';
	}

	public static function getMap()
	{
		return array(
			'ID' => array('primary' => true,
				'autocomplete' => true,
				'autoincrement' => true,
				'data_type' => 'integer',
			),
			'CHECK_ID' => array(
				'required' => true,
				'data_type' => 'integer',
			),
			'CASHBOX_ID' => array(
				'required' => true,
				'data_type' => 'integer',
			),
		);
	}
}
