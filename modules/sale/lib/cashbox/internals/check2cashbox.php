<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Entity\DataManager;

class Check2CashboxTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_check2cashbox';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
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
