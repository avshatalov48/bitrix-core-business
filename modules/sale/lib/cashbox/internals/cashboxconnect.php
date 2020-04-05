<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Config\Option;
use	Bitrix\Main\Entity\DataManager;
use	Bitrix\Main\Type\DateTime;

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
