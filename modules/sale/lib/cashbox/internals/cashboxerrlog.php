<?php

namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main;

/**
 * Class CashboxErrLogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CashboxErrLog_Query query()
 * @method static EO_CashboxErrLog_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CashboxErrLog_Result getById($id)
 * @method static EO_CashboxErrLog_Result getList(array $parameters = [])
 * @method static EO_CashboxErrLog_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxErrLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxErrLog_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxErrLog wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxErrLog_Collection wakeUpCollection($rows)
 */
class CashboxErrLogTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_cashbox_err_log';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CASHBOX_ID' => array(
				'data_type' => 'integer'
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'require' => true,
				'default' => new Main\Type\DateTime()
			),
			'MESSAGE' => array(
				'data_type' => 'string',
			),
		);
	}
}