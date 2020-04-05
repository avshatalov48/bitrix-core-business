<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


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
}