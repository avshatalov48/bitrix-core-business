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
