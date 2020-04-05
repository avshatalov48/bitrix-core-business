<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


use Bitrix\Sale\Exchange\ImportBase;
use Bitrix\Sale\Exchange\ImportOneCBase;

class ConverterDocumentShipment extends \Bitrix\Sale\Exchange\OneC\ConverterDocumentShipment
{
	protected function getFieldsInfo()
	{
		return ShipmentDocument::getFieldsInfo();
	}

	public function externalizeItems(array $items, array $info)
	{
		$orderDocumentConverter = new ConverterDocumentOrder();
		return $orderDocumentConverter->externalizeItems($items, $info);
	}

	protected function getBasePriceDelivery($list = [])
	{
		if(is_array($list) && count($list)>0)
		{
			foreach($list as $item)
			{
				$xmlId = key($item);

				if($xmlId == ImportOneCBase::DELIVERY_SERVICE_XMLID && $item[$xmlId]['TYPE'] == ImportBase::ITEM_SERVICE)
				{
					return $item[$xmlId]["PRICE"];
				}
			}
		}
		return 0;
	}
	/*
		public function externalizeStories(array $stories, array $info)
		{
			$orderDocumentConverter = new ConverterDocumentOrder();
			return $orderDocumentConverter->externalizeStories($stories, $info);
		}

		public function externalizeTaxes(array $items, array $info)
		{
			$orderDocumentConverter = new ConverterDocumentOrder();
			return $orderDocumentConverter->externalizeTaxes($items, $info);
		}
	*/
}