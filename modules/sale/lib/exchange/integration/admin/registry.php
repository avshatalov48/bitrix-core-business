<?php


namespace Bitrix\Sale\Exchange\Integration\Admin;


class Registry
{
	const UNDEFINED = 'undefined';
	const SALE_ORDER = 'sale_order';
	const SALE_ORDER_CREATE = 'sale_order_create';
	const SALE_ORDER_EDIT = 'sale_order_edit';
	const SALE_ORDER_VIEW = 'sale_order_view';
	const SALE_ORDER_PRINT = 'sale_order_print';
	const SALE_ORDER_PRINT_NEW = 'sale_order_print_new';
	const SALE_ORDER_PAYMENT = 'sale_order_payment';
	const SALE_ORDER_PAYMENT_EDIT = 'sale_order_payment_edit';
	const SALE_ORDER_SHIPMENT = 'sale_order_shipment';
	const SALE_ORDER_SHIPMENT_EDIT = 'sale_order_shipment_edit';
	const SALE_DELIVERY_REQUEST_VIEW = 'sale_delivery_request_view';
	const SALE_DELIVERY_REQUEST = 'sale_delivery_request';

	public static function getRegistry()
	{
		return [
			ModeType::DEFAULT_TYPE => [
				Registry::SALE_ORDER => '/bitrix/admin/sale_order.php',
				Registry::SALE_ORDER_CREATE => '/bitrix/admin/sale_order_create.php',
				Registry::SALE_ORDER_EDIT => '/bitrix/admin/sale_order_edit.php',
				Registry::SALE_ORDER_VIEW => '/bitrix/admin/sale_order_view.php',
				Registry::SALE_ORDER_PRINT => '/bitrix/admin/sale_order_print.php',
				Registry::SALE_ORDER_PRINT_NEW => '/bitrix/admin/sale_order_print_new.php',
				Registry::SALE_ORDER_PAYMENT => '/bitrix/admin/sale_order_payment.php',
				Registry::SALE_ORDER_PAYMENT_EDIT => '/bitrix/admin/sale_order_payment_edit.php',
				Registry::SALE_ORDER_SHIPMENT => '/bitrix/admin/sale_order_shipment.php',
				Registry::SALE_ORDER_SHIPMENT_EDIT => '/bitrix/admin/sale_order_shipment_edit.php',
				Registry::SALE_DELIVERY_REQUEST_VIEW => '/bitrix/admin/sale_delivery_request_view.php',
				Registry::SALE_DELIVERY_REQUEST => '/bitrix/admin/sale_delivery_request.php'
			],
			ModeType::APP_LAYOUT_TYPE => [
				Registry::SALE_ORDER => '/bitrix/admin/sale_order.php',
				Registry::SALE_ORDER_CREATE => '/bitrix/admin/sale_order_create.php',
				Registry::SALE_ORDER_EDIT => '/bitrix/admin/sale_order_edit.php',
				Registry::SALE_ORDER_VIEW => '/bitrix/admin/sale_order_view.php',
				Registry::SALE_ORDER_PRINT => '/bitrix/admin/sale_order_print.php',
				Registry::SALE_ORDER_PRINT_NEW => '/bitrix/admin/sale_order_print_new.php',
				Registry::SALE_ORDER_PAYMENT => '/bitrix/admin/sale_order.php',
				Registry::SALE_ORDER_PAYMENT_EDIT => '/bitrix/admin/sale_order_payment_edit.php',
				Registry::SALE_ORDER_SHIPMENT => '/bitrix/admin/sale_order.php',
				Registry::SALE_ORDER_SHIPMENT_EDIT => '/bitrix/admin/sale_order_shipment_edit.php',
				Registry::SALE_DELIVERY_REQUEST_VIEW => '/bitrix/admin/sale_delivery_request_view.php',
				Registry::SALE_DELIVERY_REQUEST => '/bitrix/admin/sale_delivery_request.php'
			],
		];
	}
}