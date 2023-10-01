<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Linking pay systems to specific payment of order
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PaymentPaySystemAvailable_Query query()
 * @method static EO_PaymentPaySystemAvailable_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_PaymentPaySystemAvailable_Result getById($id)
 * @method static EO_PaymentPaySystemAvailable_Result getList(array $parameters = [])
 * @method static EO_PaymentPaySystemAvailable_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PaymentPaySystemAvailable createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PaymentPaySystemAvailable_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PaymentPaySystemAvailable wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PaymentPaySystemAvailable_Collection wakeUpCollection($rows)
 */
class PaymentPaySystemAvailableTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_order_payment_ps_available';
	}

	/**
	 * @inheritDoc
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			'PAYMENT_ID' => new IntegerField('PAYMENT_ID', [
				'required' => true,
			]),
			'PAY_SYSTEM_ID' => new IntegerField('PAY_SYSTEM_ID', [
				'required' => true,
			]),
			// 
			new Reference('PAYMENT', PaymentTable::class, Join::on('this.PAYMENT_ID', 'ref.ID')),
			new Reference('PAY_SYSTEM', PaySystemActionTable::class, Join::on('this.PAY_SYSTEM_ID', 'ref.ID')),
		];
	}
}
