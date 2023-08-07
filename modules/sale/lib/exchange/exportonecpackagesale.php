<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\Entity\PaymentImport;
use Bitrix\Sale\Exchange\Entity\ShipmentImport;
use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\DocumentType;
use Bitrix\Sale\Exchange\OneC\PaymentDocument;
use Bitrix\Sale\Exchange\OneC\ShipmentDocument;
use Bitrix\Sale\Exchange\OneC\UserProfileDocument;
use Bitrix\Sale\Result;

class ExportOneCPackageSale extends ExportOneCPackage
{
	/**
	 * @param DocumentBase[] $documents
	 */
	protected function convertDocumentFields(array $documents)
	{
		parent::convertDocumentFields($documents);

		foreach ($documents as $document)
		{
			if($document instanceof ShipmentDocument || $document instanceof PaymentDocument)
			{
				$fields = $document->getFieldValues();
				$fields['AGENT'] = $this->getDocumentByTypeId(EntityType::USER_PROFILE, $documents)
					->getFieldValues();

				$document->setFields($fields);
			}
		}

		//region shipment.fields delivery address
		/** @var UserProfileDocument $documentProfile */
		$documentProfile = $this->getDocumentByTypeId(EntityType::USER_PROFILE, $documents);
		foreach ($documents as $document)
		{
			if($document instanceof ShipmentDocument)
			{
				$this->prepareDocumentFieldsDeliveryAddress($document, $documentProfile);
			}
		}
		//endregion

		//region order.fields delivery address
		$documentOrder = $this->getDocumentByTypeId(DocumentType::ORDER, $documents);
		$this->prepareDocumentFieldsDeliveryAddress($documentOrder, $documentProfile);
		//endregion
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result;
	 */
	protected function convertEntityFields(array $items)
	{
		$orderImport = $this->getEntityByTypeId(static::getParentEntityTypeId(), $items);

		//region order stories
		$orderFields = $orderImport->getFieldValues();
		foreach ($items as $item)
		{
			if($item instanceof ShipmentImport)
			{
				$shipmentFields = $item->getFieldValues();
				$orderFields['STORIES'][] = $shipmentFields['STORIES'];
			}
		}
		$orderImport->setFields($orderFields);
		//endregion

		//region shipment taxes from order taxes
		foreach ($items as $item)
		{
			if($item instanceof ShipmentImport)
			{
				$shipmentFields = $item->getFieldValues();
				$shipmentFields['TAXES'] = $orderFields['TAXES'];
				$item->setFields($shipmentFields);
			}
		}
		//endregion

		//region cash box checks payment
		foreach ($items as $item)
		{
			if($item instanceof PaymentImport)
			{
				$this->prepareEntityFieldsCashBoxChecks($item, $orderFields['CASH_BOX_CHECKS']);
			}
		}
		//endregion

		//region order.shipment fields
		$shipmentImport = $this->getEntityByTypeId(static::getShipmentEntityTypeId(), $items);
		if($shipmentImport instanceof ShipmentImport)
		{
			$shipmentFields = $shipmentImport->getFieldValues();

			$orderFields = $orderImport->getFieldValues();
			$orderFields['TRAITS']['DELIVERY_SERVICE'] = $shipmentFields['TRAITS']['DELIVERY_NAME'];
			$orderFields['TRAITS']['DELIVERY_ID'] = $shipmentFields['TRAITS']['DELIVERY_ID'];
			$orderImport->setFields($orderFields);
		}
		//endregion

		//region order.payment fields
		$orderFields = $orderImport->getFieldValues();
		$paymentFields = array();
		foreach ($items as $item)
		{
			if($item instanceof PaymentImport)
			{
				$paymentFields = $item->getFieldValues();
				break;
			}
		}
		if(count($paymentFields)>0)
		{
			$orderFields['TRAITS']['PAY_SYSTEM'] = $paymentFields['TRAITS']['PAY_SYSTEM_NAME'];
			$orderFields['TRAITS']['PAY_SYSTEM_ID'] = $paymentFields['TRAITS']['PAY_SYSTEM_ID'];
			$orderImport->setFields($orderFields);
		}
		//endregion

		return parent::convertEntityFields($items);
	}

	/**
	 * @param PaymentImport $item
	 * @param $checks
	 */
	protected function prepareEntityFieldsCashBoxChecks(PaymentImport $item, $checks)
	{
		$paymentFields = $item->getFieldValues();
		$paymentFields['CASH_BOX_CHECKS'] = array();
		foreach ($checks as $checkId=>$check)
		{
			if($check['PAYMENT_ID'] == $item->getId())
			{
				$paymentFields['CASH_BOX_CHECKS'][$checkId] = $check;
			}
		}
		$item->setFields($paymentFields);
	}

	/**
	 * @param DocumentBase[] $documents
	 * @param int $level
	 * @return Result
	 */
	protected function outputXmlDocuments(array $documents, $level = 0)
	{
		$result = new Result();
		$containers = array();
		$r = parent::outputXmlDocuments($documents, 1);
		if($r->isSuccess())
			$containers[] = $this->outputXmlContainer(implode('', $r->getData()));

		$result->setData($containers);

		return $result;
	}

	/**
	 * @param string $xml
	 * @return string
	 */
	public function outputXmlContainer($xml)
	{
		$document = new DocumentBase();
		$result = $document->openNodeDirectory(0, $document::getLangByCodeField('CONTAINER'));
		$result .= $xml;
		$result .= $document->closeNodeDirectory(0, $document::getLangByCodeField('CONTAINER'));
		return $result;
	}

	/**
	 * @return string
	 */
	protected function getShemVersion()
	{
		return static::SHEM_VERSION_3_1;
	}
}