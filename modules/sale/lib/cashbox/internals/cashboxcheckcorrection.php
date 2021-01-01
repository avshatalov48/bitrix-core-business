<?php
namespace Bitrix\Sale\Cashbox\Internals;

use	Bitrix\Main\Entity;

class CashboxCheckCorrectionTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_cashbox_check_correction';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'primary' => true,
				'autocomplete' => true,
				'autoincrement' => true,
				'data_type' => 'integer',
			],
			'CHECK_ID' => [
				'data_type' => 'integer',
			],
			'CHECK' => [
				'data_type' => 'Bitrix\Sale\Cashbox\Internals\CashboxCheckTable',
				'reference' => [
					'=this.CHECK_ID' => 'ref.ID'
				]
			],
			'CORRECTION_TYPE' => [
				'data_type' => 'string',
				'required' => true
			],
			'DOCUMENT_NUMBER' => [
				'data_type' => 'string',
				'required' => true
			],
			'DOCUMENT_DATE' => [
				'data_type' => 'date',
				'required' => true
			],
			'DESCRIPTION' => [
				'data_type' => 'string'
			],
			'CORRECTION_PAYMENT' => [
				'data_type' => 'string',
				'serialized' => true
			],
			'CORRECTION_VAT' => [
				'data_type' => 'string',
				'serialized' => true
			],
		];
	}
}
