<?php
namespace Bitrix\Sale\Cashbox\Internals;

use	Bitrix\Main\Entity;

/**
 * Class CashboxCheckCorrectionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CashboxCheckCorrection_Query query()
 * @method static EO_CashboxCheckCorrection_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CashboxCheckCorrection_Result getById($id)
 * @method static EO_CashboxCheckCorrection_Result getList(array $parameters = [])
 * @method static EO_CashboxCheckCorrection_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxCheckCorrection createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxCheckCorrection_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxCheckCorrection wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_CashboxCheckCorrection_Collection wakeUpCollection($rows)
 */
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
