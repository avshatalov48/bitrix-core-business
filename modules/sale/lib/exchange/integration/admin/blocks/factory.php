<?php


namespace Bitrix\Sale\Exchange\Integration\Admin\Blocks;


use Bitrix\Sale\Helpers\Admin\Blocks\BlockType;

class Factory
{
	static public function create($type, $params=[])
	{
		if($type == BlockType::FINANCE_INFO)
		{
			return new \Bitrix\Sale\Helpers\Admin\Blocks\OrderFinanceInfo();
		}
		elseif ($type == BlockType::PAYMENT)
		{
			return new \Bitrix\Sale\Helpers\Admin\Blocks\OrderPayment();
		}
		elseif ($type == BlockType::BUYER)
		{
			return new OrderBuyer();
		}
		elseif ($type == BlockType::ADDITIONAL)
		{
			return new OrderAdditional();
		}
		elseif ($type == BlockType::STATUS)
		{
			return new OrderStatus();
		}
		elseif ($type == BlockType::INFO)
		{
			return new \Bitrix\Sale\Exchange\Integration\Admin\Blocks\OrderInfo();
		}
		elseif ($type == BlockType::SHIPMENT_BASKET)
		{
			$shipment = $params['shipment'];
			$jsObjName = isset($params['jsObjName'])?$params['jsObjName']:'';
			$idPrefix = isset($params['idPrefix'])?$params['idPrefix']:'';

			return new \Bitrix\Sale\Helpers\Admin\Blocks\OrderBasketShipment($shipment, $jsObjName, $idPrefix);
		}
		elseif ($type == BlockType::SHIPMENT_STATUS)
		{
			return new \Bitrix\Sale\Helpers\Admin\Blocks\OrderShipmentStatus();
		}
		elseif ($type == BlockType::SHIPMENT || $type == BlockType::DELIVERY)
		{
			return new \Bitrix\Sale\Helpers\Admin\Blocks\OrderShipment();
		}
		elseif ($type == BlockType::BASKET)
		{
			$order = $params['order'];
			$jsObjName = isset($params['jsObjName']) ? $params['jsObjName']:'';
			$idPrefix = isset($params['idPrefix']) ? $params['idPrefix']:'';
			$createProductBasement = isset($params['createProductBasement']) ? $params['createProductBasement']: true;
			$mode = isset($params['mode']) ? $params['mode']: \Bitrix\Sale\Helpers\Admin\Blocks\OrderBasket::EDIT_MODE;

			return new \Bitrix\Sale\Helpers\Admin\Blocks\OrderBasket($order, $jsObjName, $idPrefix, $createProductBasement, $mode);
		}
		elseif ($type == BlockType::MARKER)
		{
			return new \Bitrix\Sale\Helpers\Admin\Blocks\OrderMarker();
		}
		elseif ($type == BlockType::ANALYSIS)
		{
			return new OrderAnalysis();
		}
		elseif ($type == BlockType::DISCOUNT)
		{
			return new OrderDiscount();
		}
		else
		{
			throw new \Bitrix\Main\NotSupportedException("Mode type: '".$type."' is not supported in current context");
		}
	}
}