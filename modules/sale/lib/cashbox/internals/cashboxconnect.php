<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Config\Option;
use	Bitrix\Main\Entity\DataManager;
use	Bitrix\Main\Type\DateTime;

/**
 * Class CashboxConnectTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CashboxConnect_Query query()
 * @method static EO_CashboxConnect_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_CashboxConnect_Result getById($id)
 * @method static EO_CashboxConnect_Result getList(array $parameters = array())
 * @method static EO_CashboxConnect_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxConnect createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxConnect_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxConnect wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxConnect_Collection wakeUpCollection($rows)
 */
class CashboxConnectTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_cashbox_connect';
	}

	public static function getMap()
	{
		return array(
			'HASH' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y'
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime
			)
		);
	}
}
