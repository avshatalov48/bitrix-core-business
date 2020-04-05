<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive\TypeFirst;

use Bitrix\Sale\Helpers\Admin\Blocks\Archive\Template,
	Bitrix\Main\Page\Asset,
	Bitrix\Sale\Helpers\Admin\Blocks;

class OrderShipment extends Template
{
	protected $name = "delivery";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		$result = "";
		$index = 0;
		Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_shipment_basket.js");
		\Bitrix\Main\UI\Extension::load('sale.admin_order');
		$shipmentCollection = $this->order->getShipmentCollection();
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$result .= Blocks\OrderShipment::getView($shipment, $index++, 'archive');
			}
		}
		return $result;
	}
}