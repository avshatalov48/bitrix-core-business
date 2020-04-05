<?php
namespace Bitrix\Sale\Cashbox\Internals;

use	Bitrix\Main\Entity\DataManager;

class CashboxCheckTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_cashbox_check';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
				'data_type' => 'integer',
			),
			'PAYMENT_ID' => array(
				'data_type' => 'integer',
			),
			'PAYMENT' => array(
				'data_type' => 'Bitrix\Sale\Internals\PaymentTable',
				'reference' => array(
					'=this.PAYMENT_ID' => 'ref.ID'
				)
			),
			'SHIPMENT_ID' => array(
				'data_type' => 'integer',
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
			),
			'EXTERNAL_UUID' => array(
				'data_type' => 'string',
			),
			'CASHBOX_ID' => array(
				'data_type' => 'integer',
			),
			'CNT_FAIL_PRINT' => array(
				'data_type' => 'integer',
				'default' => 0
			),
			'CASHBOX' => array(
				'data_type' => 'Bitrix\Sale\Cashbox\Internals\CashboxTable',
				'reference' => array(
					'=this.CASHBOX_ID' => 'ref.ID'
				)
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true
			),
			'DATE_PRINT_START' => array(
				'data_type' => 'datetime',
			),
			'DATE_PRINT_END' => array(
				'data_type' => 'datetime',
			),
			'SUM' => array(
				'data_type' => 'float',
			),
			'CURRENCY' => array(
				'data_type' => 'string',
			),
			'LINK_PARAMS' => array(
				'data_type' => 'string',
				'serialized' => true
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true
			),
			'STATUS' => array(
				'data_type' => 'string',
			),
			'CHECK2CASHBOX' => array(
				'data_type' => 'Bitrix\Sale\Cashbox\Internals\Check2CashboxTable',
				'reference' => array(
					'=this.ID' => 'ref.CHECK_ID'
				),
				'join_type' => 'INNER'
			)
		);
	}
}
