<?php
namespace Bitrix\Sale\Cashbox\Internals;

use	Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Type\DateTime;

/**
 * Class CashboxZReportTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CashboxZReport_Query query()
 * @method static EO_CashboxZReport_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_CashboxZReport_Result getById($id)
 * @method static EO_CashboxZReport_Result getList(array $parameters = array())
 * @method static EO_CashboxZReport_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxZReport createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxZReport_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxZReport wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxZReport_Collection wakeUpCollection($rows)
 */
class CashboxZReportTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_cashbox_z_report';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
				'data_type' => 'integer',
			),
			'LINK_PARAMS' => array(
				'data_type' => 'string',
				'serialized' => true
			),
			'CNT_FAIL_PRINT' => array(
				'data_type' => 'integer',
				'default' => 0
			),
			'CASHBOX_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default' => new DateTime()
			),
			'DATE_PRINT_START' => array(
				'data_type' => 'datetime'
			),
			'DATE_PRINT_END' => array(
				'data_type' => 'datetime'
			),
			'STATUS' => array(
				'data_type' => 'string',
			),
			'CASH_SUM' => array(
				'data_type' => 'float',
			),
			'CASHLESS_SUM' => array(
				'data_type' => 'float',
			),
			'CUMULATIVE_SUM' => array(
				'data_type' => 'float',
			),
			'CURRENCY' => array(
				'data_type' => 'string',
			),
			'RETURNED_SUM' => array(
				'data_type' => 'float',
			),
		);
	}
}
