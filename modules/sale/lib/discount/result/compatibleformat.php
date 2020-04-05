<?php
namespace Bitrix\Sale\Discount\Result;

use Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Discount;

Loc::loadMessages(__FILE__);

final class CompatibleFormat
{
	/**
	 * Create simple description for unknown discount.
	 *
	 * @param float $newPrice			New price.
	 * @param float $oldPrice			Old price.
	 * @param string $currency			Currency.
	 * @return array
	 */
	public static function createResultDescription($newPrice, $oldPrice, $currency)
	{
		return array(
			Discount\Formatter::prepareRow(
				Discount\Formatter::TYPE_VALUE,
				array(
					'VALUE_TYPE' => Discount\Formatter::VALUE_TYPE_CURRENCY,
					'VALUE' => abs($oldPrice - $newPrice),
					'VALUE_UNIT' => $currency,
					'VALUE_ACTION' => (
						$oldPrice > $newPrice
						? Discount\Formatter::VALUE_ACTION_DISCOUNT
						: Discount\Formatter::VALUE_ACTION_EXTRA
					)
				)
			)
		);
	}

	/**
	 * Returns result after apply one discount in old format.
	 *
	 * @param array $currentOrder			Current order data.
	 * @param array $oldOrder				Old order data.
	 * @return array
	 */
	public static function getStepResult(array $currentOrder, array $oldOrder)
	{
		$result = array();
		if (isset($oldOrder['PRICE_DELIVERY']) && isset($currentOrder['PRICE_DELIVERY']))
		{
			if ($oldOrder['PRICE_DELIVERY'] != $currentOrder['PRICE_DELIVERY'])
			{
				$descr = self::createResultDescription(
					$currentOrder['PRICE_DELIVERY'],
					$oldOrder['PRICE_DELIVERY'],
					$oldOrder['CURRENCY']
				);
				$result['DELIVERY'] = array(
					'APPLY' => 'Y',
					'DELIVERY_ID' => (isset($currentOrder['DELIVERY_ID']) ? $currentOrder['DELIVERY_ID'] : false),
					'SHIPMENT_CODE' => (isset($currentOrder['SHIPMENT_CODE']) ? $currentOrder['SHIPMENT_CODE'] : false),
					'DESCR' => implode(', ', Discount\Formatter::formatList($descr)),
					'DESCR_DATA' => $descr
				);
				unset($descr);
			}
		}
		if (!empty($oldOrder['BASKET_ITEMS']) && !empty($currentOrder['BASKET_ITEMS']))
		{
			foreach ($oldOrder['BASKET_ITEMS'] as $basketCode => $item)
			{
				if (!isset($currentOrder['BASKET_ITEMS'][$basketCode]))
					continue;
				if ($item['PRICE'] != $currentOrder['BASKET_ITEMS'][$basketCode]['PRICE'])
				{
					if (!isset($result['BASKET']))
						$result['BASKET'] = array();
					$descr = self::createResultDescription(
						$currentOrder['BASKET_ITEMS'][$basketCode]['PRICE'],
						$item['PRICE'],
						$oldOrder['CURRENCY']
					);
					$result['BASKET'][$basketCode] = array(
						'APPLY' => 'Y',
						'DESCR' => implode(', ', Discount\Formatter::formatList($descr)),
						'DESCR_DATA' => $descr,
						'MODULE' => $currentOrder['BASKET_ITEMS'][$basketCode]['MODULE'],
						'PRODUCT_ID' => $currentOrder['BASKET_ITEMS'][$basketCode]['PRODUCT_ID'],
						'BASKET_ID' => (
							isset($currentOrder['BASKET_ITEMS'][$basketCode]['ID'])
							? $currentOrder['BASKET_ITEMS'][$basketCode]['ID']
							: $basketCode
						)
					);
					unset($descr);
				}
			}
		}
		return $result;
	}

	/**
	 * Returns description for old discount.
	 *
	 * @param array $stepResult		Action results.
	 * @return array
	 */
	public static function getDiscountDescription(array $stepResult)
	{
		$result = array();
		if (!empty($stepResult['BASKET']))
		{
			$result['BASKET'] = array(
				0 => Discount\Formatter::prepareRow(
					Discount\Formatter::TYPE_SIMPLE,
					Loc::getMessage('BX_SALE_DISCOUNT_MESS_SIMPLE_DESCRIPTION_BASKET')
				)
			);
		}
		if (!empty($stepResult['DELIVERY']))
		{
			$result['DELIVERY'] = array(
				0 => Discount\Formatter::prepareRow(
					Discount\Formatter::TYPE_SIMPLE,
					Loc::getMessage('BX_SALE_DISCOUNT_MESS_SIMPLE_DESCRIPTION_DELIVERY')
				)
			);
		}
		if (empty($result))
		{
			$result['BASKET'] = array(
				0 => Discount\Formatter::prepareRow(
					Discount\Formatter::TYPE_SIMPLE,
					Loc::getMessage('BX_SALE_DISCOUNT_MESS_SIMPLE_DESCRIPTION_UNKNOWN')
				)
			);
		}

		return $result;
	}
}