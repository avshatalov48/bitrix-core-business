<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

class Barcode extends \Bitrix\Main\Engine\Controller
{
	const PERMISSION_READ = 'D';

	public function isBarcodeExistAction(string $barcode, int $basketId, int $orderId, int $storeId)
	{
		if(!\Bitrix\Main\Loader::includeModule("sale"))
		{
			throw new \Bitrix\Main\SystemException('Module Sale has not installed');
		}

		if(!$this->checkPermission(self::PERMISSION_READ))
		{
			$this->addError(new Error(Loc::getMessage('SALE_CONTROLLER_BARCODE_ACCESS_DENIED')));
			return false;
		}

		if($barcode == '')
		{
			return false;
		}

		if((int)$basketId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SALE_CONTROLLER_BARCODE_ERROR_BASKET_ID')));
			return false;
		}

		if((int)$orderId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SALE_CONTROLLER_BARCODE_ERROR_ORDER_ID')));
			return false;
		}

		$basketItem = null;
		$result = false;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$order = $orderClass::load($orderId);

		if ($order)
		{
			$basket = $order->getBasket();

			if ($basket)
			{
				$basketItem = $basket->getItemById($basketId);
			}
		}

		if ($basketItem)
		{
			$result = \Bitrix\Sale\Provider::checkProductBarcode(
				$basketItem,
				[
					'BARCODE' => $barcode,
					'STORE_ID' => $storeId
			]);
		}

		return ['RESULT' => $result];
	}

	protected function checkPermission($permissionType)
	{
		$result =  self::getApplication()->GetGroupRight("sale") >= $permissionType;

		if(!$result)
		{
			$this->addError(new Error('Access denied'));
		}

		return $result;
	}

	protected static function getApplication()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		return $APPLICATION;
	}
}