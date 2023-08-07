<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Sale\Exchange\EntityType;

class ConverterDocumentShipmentInvoice extends ConverterDocumentShipment
{
	public function externalizeItems(array $taxes, array $info)
	{
		/** @var ConverterDocumentInvoice $converter */
		$converter = ConverterFactory::create(EntityType::INVOICE);
		return $converter->externalizeItems($taxes, $info);
	}
}