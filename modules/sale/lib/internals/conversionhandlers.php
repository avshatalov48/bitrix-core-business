<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Conversion\Utils;
use Bitrix\Conversion\DayContext;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

/** @internal */
final class ConversionHandlers
{
	private static float $onBeforeBasketAddQuantity = 0;

	public static function onGetCounterTypes(): array
	{
		return [
			'sale_cart_add_day' => [
				'MODULE' => 'sale',
				'NAME' => 'Added to cart goals',
				'GROUP' => 'day',
			],
			'sale_cart_add' => [
				'MODULE' => 'sale',
				'NAME' => 'Added to cart total',
			],
			'sale_cart_sum_add' => [
				'MODULE' => 'sale',
				'NAME' => 'Sum added to cart',
			],

			'sale_order_add_day' => [
				'MODULE' => 'sale',
				'NAME' => 'Placed orders goals',
				'GROUP' => 'day',
			],
			'sale_order_add' => [
				'MODULE' => 'sale',
				'NAME' => 'Placed orders total',
			],
			'sale_order_sum_add' => [
				'MODULE' => 'sale',
				'NAME' => 'Sum placed orders',
			],

			'sale_payment_add_day' => [
				'MODULE' => 'sale',
				'NAME' => 'Payments a day goals',
				'GROUP' => 'day',
			],
			'sale_payment_add' => [
				'MODULE' => 'sale',
				'NAME' => 'Payments a day total',
			],
			'sale_payment_sum_add' => [
				'MODULE' => 'sale',
				'NAME' => 'Added payment sum',
			],
		];
	}

	public static function onGetRateTypes(): array
	{
		$scale = [0.5, 1, 1.5, 2, 5];

		$format = [
			'SUM' => function ($value, $format = null)
			{
				return Utils::formatToBaseCurrency($value, $format);
			},
		];

		$units = [
			'SUM' => Utils::getBaseCurrencyUnit(), // TODO deprecated
		];

		return [
			'sale_payment' => [
				'NAME' => Loc::getMessage('SALE_CONVERSION_RATE_PAYMENT_NAME'),
				'SCALE' => $scale,
				'FORMAT' => $format,
				'UNITS' => $units,
				'MODULE' => 'sale',
				'SORT' => 1100,
				'COUNTERS' => [
					'conversion_visit_day',
					'sale_payment_add_day',
					'sale_payment_add',
					'sale_payment_add_cmpfb',
					'sale_payment_sum_add',
				],
				'CALCULATE' => function (array $counters)
				{
					$denominator = (int)($counters['conversion_visit_day'] ?? 0);
					$numerator = (int)($counters['sale_payment_add_day'] ?? 0);
					$quantity = (float)($counters['sale_payment_add'] ?? 0) + (float)($counters['sale_payment_add_cmpfb'] ?? 0);
					$sum = (float)($counters['sale_payment_sum_add'] ?? 0);

					return [
						'DENOMINATOR' => $denominator,
						'NUMERATOR' => $numerator,
						'QUANTITY' => $quantity,
						'RATE' => $denominator ? $numerator / $denominator : 0,
						'SUM' => $sum,
					];
				},
			],

			'sale_order' => [
				'NAME' => Loc::getMessage('SALE_CONVERSION_RATE_ORDER_NAME'),
				'SCALE' => $scale,
				'FORMAT' => $format,
				'UNITS' => $units,
				'MODULE' => 'sale',
				'SORT' => 1200,
				'COUNTERS' => [
					'conversion_visit_day',
					'sale_order_add_day',
					'sale_order_add',
					'sale_order_add_cmpfb',
					'sale_order_sum_add',
				],
				'CALCULATE' => function (array $counters)
				{
					$denominator = (int)($counters['conversion_visit_day'] ?? 0);
					$numerator = (int)($counters['sale_order_add_day'] ?? 0);
					$quantity = (float)($counters['sale_order_add'] ?? 0) + (float)($counters['sale_order_add_cmpfb'] ?? 0);
					$sum = (float)($counters['sale_order_sum_add'] ?? 0);

					return [
						'DENOMINATOR' => $denominator,
						'NUMERATOR' => $numerator,
						'QUANTITY' => $quantity,
						'RATE' => $denominator ? $numerator / $denominator : 0,
						'SUM' => $sum,
					];
				},
			],

			'sale_cart' => [
				'NAME' => Loc::getMessage('SALE_CONVERSION_RATE_CART_NAME'),
				'SCALE' => $scale,
				'FORMAT' => $format,
				'UNITS' => $units,
				'MODULE' => 'sale',
				'SORT' => 1300,
				'COUNTERS' => [
					'conversion_visit_day',
					'sale_cart_add_day',
					'sale_cart_add',
					'sale_cart_add_cmpfb',
					'sale_cart_sum_add',
				],
				'CALCULATE' => function (array $counters)
				{
					$denominator = (int)($counters['conversion_visit_day'] ?? 0);
					$numerator = (int)($counters['sale_cart_add_day'] ?? 0);
					$quantity = (float)($counters['sale_cart_add'] ?? 0) + (float)($counters['sale_cart_add_cmpfb'] ?? 0);
					$sum = (float)($counters['sale_cart_sum_add'] ?? 0);

					return [
						'DENOMINATOR' => $denominator,
						'NUMERATOR' => $numerator,
						'QUANTITY' => $quantity,
						'RATE' => $denominator ? $numerator / $denominator : 0,
						'SUM' => $sum,
					];
				},
			],
		];
	}

	public static function onGenerateInitialData(Date $from, Date $to): array
	{
		$data = [];

		// 1. Payments

		$result = \CSaleOrder::GetList(
			[],
			[
				'PAYED' => 'Y',
				'CANCELED' => 'N',
				'>=DATE_PAYED' => $from,
				'<=DATE_PAYED' => $to,
			],
			false,
			false,
			[
				'LID',
				'DATE_PAYED',
				'PRICE',
				'CURRENCY',
			]
		);

		while ($row = $result->Fetch())
		{
			$day = new DateTime($row['DATE_PAYED']);
			$sum = Utils::convertToBaseCurrency($row['PRICE'], $row['CURRENCY']);

			if ($counters =& $data[$row['LID']][$day->format('Y-m-d')])
			{
				$counters['sale_payment_add_day'] += 1;
				$counters['sale_payment_sum_add'] += $sum;
			}
			else
			{
				$counters = [
					'sale_payment_add_day' => 1,
					'sale_payment_sum_add' => $sum,
				];
			}
		}

		// 2. Orders

		$result = \CSaleOrder::GetList(
			[],
			[
				'CANCELED' => 'N',
				'>=DATE_INSERT' => $from,
				'<=DATE_INSERT' => $to,
			],
			false,
			false,
			[
				'LID',
				'DATE_INSERT',
				'PRICE',
				'CURRENCY',
			]
		);

		while ($row = $result->Fetch())
		{
			$day = new DateTime($row['DATE_INSERT']);
			$sum = Utils::convertToBaseCurrency($row['PRICE'], $row['CURRENCY']);

			if ($counters =& $data[$row['LID']][$day->format('Y-m-d')])
			{
				$counters['sale_order_add_day'] += 1;
				$counters['sale_order_sum_add'] += $sum;
			}
			else
			{
				$counters = [
					'sale_order_add_day' => 1,
					'sale_order_sum_add' => $sum,
				];
			}
		}

		// 3. Cart

		$result = \CSaleBasket::GetList(
			[],
			[
				'>=DATE_INSERT' => $from,
				'<=DATE_INSERT' => $to,
			],
			false,
			false,
			[
				'LID',
				'DATE_INSERT',
				'PRICE',
				'CURRENCY',
				'QUANTITY',
			]
		);

		while ($row = $result->Fetch())
		{
			$day = new DateTime($row['DATE_INSERT']);
			$sum = Utils::convertToBaseCurrency($row['PRICE'] * $row['QUANTITY'], $row['CURRENCY']);

			if ($counters =& $data[$row['LID']][$day->format('Y-m-d')])
			{
				$counters['sale_cart_add_day'] += 1;
				$counters['sale_cart_sum_add'] += $sum;
			}
			else
			{
				$counters = [
					'sale_cart_add_day' => 1,
					'sale_cart_sum_add' => $sum,
				];
			}
		}

		// Result

		unset($counters);

		$result = [];

		foreach ($data as $siteId => $dayCounters)
		{
			$result[] = [
				'ATTRIBUTES' => ['conversion_site' => $siteId],
				'DAY_COUNTERS' => $dayCounters,
			];
		}

		return $result;
	}

	// Cart Counters

	// Events can be stacked!!!
	// 1) OnBeforeBasketAdd -> OnBasketAdd
	// 2) OnBeforeBasketAdd -> OnBeforeBasketUpdate -> OnBasketUpdate -> OnBasketAdd
	// 3) and other variations with mixed arguments as well, sick!!!

	public static function onSaleBasketItemSaved(Main\Event $event): void
	{
		if (!$event->getParameter('IS_NEW'))
		{
			return;
		}

		$basketItem = $event->getParameter('ENTITY');

		if ($basketItem instanceof Sale\BasketItem)
		{
			$price = $basketItem->getPrice();
			$quantity = $basketItem->getQuantity();
			$currency = $basketItem->getCurrency();

			if ($quantity && Loader::includeModule('conversion'))
			{
				$context = DayContext::getSiteInstance($basketItem->getField('LID'));

				$context->addDayCounter('sale_cart_add_day', 1);
				$context->addCounter(new Date(), 'sale_cart_add', 1);

				if ($price * $quantity && $currency)
				{
					$context->addCurrencyCounter('sale_cart_sum_add', $price * $quantity, $currency);
				}
			}
		}
	}

	public static function onBeforeBasketAdd(/*array*/ $fields): void
	{
		self::$onBeforeBasketAddQuantity = (float)($fields['QUANTITY'] ?? 0);
	}

	public static function onBasketAdd($id, /*array*/ $fields): void
	{
		if (is_array($fields)
			&& isset($fields['PRICE'], $fields['QUANTITY'], $fields['CURRENCY'])
			&& self::$onBeforeBasketAddQuantity
			&& Loader::includeModule('conversion'))
		{
			$context = DayContext::getSiteInstance($fields['LID']);
			$context->addDayCounter('sale_cart_add_day', 1);
			$context->addCounter(new Date(), 'sale_cart_add'    , 1);
			$context->addCurrencyCounter(
				'sale_cart_sum_add',
				$fields['PRICE'] * self::$onBeforeBasketAddQuantity,
				$fields['CURRENCY']
			);
		}

		self::$onBeforeBasketAddQuantity = 0;
	}

	//static private $onBeforeBasketUpdate = 0;

	public static function onBeforeBasketUpdate($id, /*array*/ $fields = null) // null hack/fix 4 sale 15
	{
		/*self::$onBeforeBasketUpdate =

			Loader::includeModule('conversion')
			&& ($intId = (int) $id) > 0
			&& $intId == $id
			&& ($row = \CSaleBasket::GetByID($id))

				? $row['PRICE'] * $row['QUANTITY'] : 0;*/
	}

	public static function onBasketUpdate($id, /*array*/ $fields)
	{
		/*if (Loader::includeModule('conversion')
			&& is_array($fields)
			&& isset($fields['PRICE'], $fields['QUANTITY'], $fields['CURRENCY']))
		{
			$context = DayContext::getInstance();

			$newSum = $fields['PRICE'] * $fields['QUANTITY'];

			// add item to cart
			if ($newSum > self::$onBeforeBasketUpdate)
			{
				$context->addCurrencyCounter('sale_cart_sum_add', $newSum - self::$onBeforeBasketUpdate, $fields['CURRENCY']);
			}
			// remove item from cart
			elseif ($newSum < self::$onBeforeBasketUpdate)
			{
				$context->addCurrencyCounter('sale_cart_sum_rem', self::$onBeforeBasketUpdate - $newSum, $fields['CURRENCY']);
			}
		}

		self::$onBeforeBasketUpdate = 0;*/
	}

	//static private $onBeforeBasketDeleteSum = 0;
	//static private $onBeforeBasketDeleteCurrency; // TODO same to all other

	public static function onBeforeBasketDelete($id)
	{
		/*self::$onBeforeBasketDeleteSum =

			Loader::includeModule('conversion')
			&& ($intId = (int) $id) > 0
			&& $intId == $id
			&& ($row = \CSaleBasket::GetByID($id))
			&& (self::$onBeforeBasketDeleteCurrency = $row['CURRENCY'])

				? $row['PRICE'] * $row['QUANTITY'] : 0;*/
	}

	public static function onBasketDelete($id)
	{
		/*if (Loader::includeModule('conversion') && self::$onBeforeBasketDeleteSum > 0)
		{
			$context = DayContext::getInstance();
			$context->addCurrencyCounter('sale_cart_sum_rem', self::$onBeforeBasketDeleteSum, self::$onBeforeBasketDeleteCurrency);
		}

		self::$onBeforeBasketDeleteSum = 0;*/
	}

	// Order Counters

	public static function onSaleOrderSaved(Main\Event $event): void
	{
		if (!$event->getParameter('IS_NEW'))
		{
			return;
		}

		$order = $event->getParameter('ENTITY');

		if ($order instanceof Sale\Order)
		{
			$price = $order->getPrice();
			$currency = $order->getCurrency();

			if (Loader::includeModule('conversion'))
			{
				$context = DayContext::getSiteInstance($order->getField('LID'));

				$context->addDayCounter('sale_order_add_day', 1);
				$context->addCounter(new Date(), 'sale_order_add', 1);
				$context->attachEntityItem('sale_order', $order->getId());

				if ($price && $currency)
				{
					$context->addCurrencyCounter('sale_order_sum_add', $price, $currency);
				}
			}
		}
	}

	public static function onOrderAdd($id, array $fields): void
	{
		if (Loader::includeModule('conversion'))
		{
			$context = DayContext::getSiteInstance($fields['LID']);
			$context->addDayCounter('sale_order_add_day', 1);
			$context->addCounter(new Date(), 'sale_order_add', 1);
			$context->addCurrencyCounter('sale_order_sum_add', $fields['PRICE'], $fields['CURRENCY']);
			$context->attachEntityItem('sale_order', $id);
		}
	}

	// Payment Counters

	public static function onSaleOrderPaid(Main\Event $event): void
	{
		$order = $event->getParameter('ENTITY');
		if (Loader::includeModule('conversion') && $order instanceof Sale\Order)
		{
			self::updatePaidOrderConversion(
				$order->getId(),
				$order->getPrice(),
				$order->getCurrency(),
				Date::createFromText($order->getField('DATE_PAYED')),
				$order->isPaid()
			);
		}
	}

	public static function onSalePayOrder($id, $paid): void
	{
		if (Loader::includeModule('conversion') && ($row = \CSaleOrder::GetById($id)))
		{
			self::updatePaidOrderConversion(
				$id,
				$row['PRICE'],
				$row['CURRENCY'],
				new Date($row['DATE_PAYED'], 'Y-m-d H:i:s'),
				$paid === 'Y'
			);
		}
	}

	/**
	 * Add or subtraction conversion values for paid/not paid order.
	 *
	 * @param int $orderId
	 * @param float $price
	 * @param string $currency
	 * @param Date $day
	 * @param bool $isPaid
	 * @return void
	 */
	private static function updatePaidOrderConversion($orderId, $price, $currency, $day, $isPaid): void
	{
		$context = DayContext::getEntityItemInstance('sale_order', $orderId);
		$isAdminSection = defined('ADMIN_SECTION') && ADMIN_SECTION === true;

		if ($isPaid)
		{
			$currentDate = new Date();
			if ($isAdminSection)
			{
				$context->addCounter($currentDate, 'sale_payment_add_day', 1);
			}
			else
			{
				$context->addDayCounter('sale_payment_add_day', 1);
			}

			$context->addCounter($currentDate, 'sale_payment_add', 1);
			$context->addCurrencyCounter('sale_payment_sum_add', $price, $currency);
			unset($currentDate);
		}
		else
		{
			if ($isAdminSection)
			{
				$context->subCounter($day, 'sale_payment_add_day', 1);
			}
			else
			{
				$context->subDayCounter($day, 'sale_payment_add_day', 1);
			}

			$context->subCounter($day, 'sale_payment_add', 1);
			$context->subCurrencyCounter($day, 'sale_payment_sum_add', $price, $currency);
		}
	}
}
