<?php

namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main;

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