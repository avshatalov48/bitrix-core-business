<?php


namespace Bitrix\Sale\Helpers\Admin\Blocks;


class Factory
{
	static public function create($type, $params=[])
	{
		if($type == BlockType::FINANCE_INFO)
		{
			return new OrderFinanceInfo();
		}
		elseif ($type == BlockType::PAYMENT)
		{
			return new OrderPayment();
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
			return new OrderInfo();
		}
		elseif ($type == BlockType::SHIPMENT_BASKET)
		{
			$shipment = $params['shipment'];
			$jsObjName = isset($params['jsObjName'])?$params['jsObjName']:'';
			$idPrefix = isset($params['idPrefix'])?$params['idPrefix']:'';

			return new OrderBasketShipment($shipment, $jsObjName, $idPrefix);
		}
		elseif ($type == BlockType::SHIPMENT_STATUS)
		{
			return new OrderShipmentStatus();
		}
		elseif ($type == BlockType::SHIPMENT || $type == BlockType::DELIVERY)
		{
			return new OrderShipment();
		}
		elseif ($type == BlockType::BASKET)
		{
			$order = $params['order'];
			$jsObjName = isset($params['jsObjName']) ? $params['jsObjName']:'';
			$idPrefix = isset($params['idPrefix']) ? $params['idPrefix']:'';
			$createProductBasement = isset($params['createProductBasement']) ? $params['createProductBasement']: true;
			$mode = isset($params['mode']) ? $params['mode']: OrderBasket::EDIT_MODE;

			$result = new OrderBasket($order, $jsObjName, $idPrefix, $createProductBasement, $mode);

			if($params['setSettingsShowPropsVisible'])
			{
				$result->setSettingsShowPropsVisible((bool)$params['setSettingsShowPropsVisible']);
			}

			return $result;
		}
		elseif ($type == BlockType::MARKER)
		{
			return new OrderMarker();
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