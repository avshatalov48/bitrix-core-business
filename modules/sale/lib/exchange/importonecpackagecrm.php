<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Crm\History\InvoiceStatusHistoryEntry;
use Bitrix\Crm\Statistics\InvoiceSumStatisticEntry;
use Bitrix\Main\Error;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\Exchange\Entity\OrderImport;
use Bitrix\Sale\Result;

final class ImportOneCPackageCRM extends ImportOneCPackage
{
	/**
	 * @param OneC\DocumentBase[] $documents
	 * @return Result
	 */
	protected function checkDocuments(array $documents)
	{
		$result = new Result();

		if(!$this->hasDocumentByTypeId(EntityType::ORDER, $documents))
		{
			$result->addError(new Error(GetMessage('CRM_PACKAGE_NOT_FOUND_ORDER'), 'CRM_PACKAGE_NOT_FOUND_ORDER'));
		}
		else
		{
			$documentOrder = $this->getDocumentByTypeId(EntityType::ORDER, $documents);

			if(!$this->hasDocumentByTypeId(EntityType::PAYMENT_CASH, $documents) &&
				!$this->hasDocumentByTypeId(EntityType::PAYMENT_CASH_LESS, $documents) &&
				!$this->hasDocumentByTypeId(EntityType::PAYMENT_CARD_TRANSACTION, $documents))
			{
				$result->addError(new Error(GetMessage('CRM_PACKAGE_NOT_FOUND_PAYMENT', array('#XML_1C_DOCUMENT_ID#'=>$documentOrder->getExternalId())), 'CRM_PACKAGE_NOT_FOUND_PAYMENT'));
			}

			$countShipment = 0;
			foreach ($documents as $document)
			{
				if($document->getOwnerEntityTypeId() == EntityType::SHIPMENT)
				{
					$countShipment++;
				}
			}

			if($countShipment>=2)
			{
				$result->addError(new Error(GetMessage('CRM_PACKAGE_PARTIAL_SHIPMENT_NOT_SUPPORTED', array('#XML_1C_DOCUMENT_ID#'=>$documentOrder->getExternalId())), 'CRM_PACKAGE_NOT_FOUND_PAYMENT'));
			}
		}

		return $result;
	}

	/**
	 * @param Entity\EntityImport|ProfileImport $item
	 * @return Result
	 */
	protected function modifyEntity($item)
	{
		$result = new Result();

		if($item instanceof OrderImport)
		{
			if($item->getId()>0)
			{
				$traits = $item->getField('TRAITS');

				$invoice = new \CCrmInvoice(false);
				if (!$invoice->SetStatus($item->getId(), $traits['STATUS_ID']))
				{
					$result->addError(new Error('Status error!'));
				}
			}
		}

		if($result->isSuccess())
			$result = parent::modifyEntity($item);


		return $result;
	}

	/**
	 * @param OrderImport $orderImport
	 * @param Entity\EntityImport[] $items
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult|Result|mixed
	 */
	protected function save(Exchange\Entity\OrderImport $orderImport, $items)
	{
		$isNew = !($orderImport->getId()>0);

		$result = parent::save($orderImport, $items);

		if($result->isSuccess())
		{
			if($orderImport->getId()>0)
			{
				InvoiceStatusHistoryEntry::register($orderImport->getId(), null, array('IS_NEW' => $isNew));
				InvoiceSumStatisticEntry::register($orderImport->getId(), null);
			}
		}
		return $result;
	}

	/**
	 * @param OneC\DocumentBase[] $documents
	 * @return Result
	 */
	protected function convert(array $documents)
	{
		$result = $this->checkDocuments($documents);

		if($result->isSuccess())
		{
			$documentShipment = $this->getDocumentByTypeId(EntityType::SHIPMENT, $documents);

			if($documentShipment !== null)
			{
				$settings = ManagerImport::getSettingsByType($documentShipment->getOwnerEntityTypeId());

				$convertor = OneC\Converter::getInstance($documentShipment->getOwnerEntityTypeId());
				$convertor->loadSettings($settings);
				$fields = $convertor->resolveParams($documentShipment);

				$shipmentPrice = $fields['TRAITS']['BASE_PRICE_DELIVERY'];

				if($shipmentPrice>0)
				{
					$documentOrder = $this->getDocumentByTypeId(EntityType::ORDER, $documents);
					$fieldsOrder = $documentOrder->getFieldValues();
					$items = $this->getProductsItems($fieldsOrder);

					if(!$this->deliveryServiceExists($items))
					{
						$fieldsOrder['ITEMS'][][self::DELIVERY_SERVICE_XMLID] = array(
							'ID' => self::DELIVERY_SERVICE_XMLID,
							'NAME' => GetMessage('CRM_PACKAGE_DELIVERY_SERVICE_ITEM'),
							'PRICE' => $shipmentPrice,
							'PRICE_ONE' => $shipmentPrice,
							'QUANTITY' => 1,
							'TYPE' => ImportBase::ITEM_ITEM,
							'MEASURE_CODE' => 796,
							'MEASURE_NAME' => GetMessage('CRM_PACKAGE_DELIVERY_SERVICE_ITEM_MEASURE_796'),
						);

						$documentOrder->setFields($fieldsOrder);
					}
				}

				foreach ($documents as $k=>$document)
				{
					if($document->getOwnerEntityTypeId() == EntityType::SHIPMENT)
					{
						unset($documents[$k]);
					}
				}
			}

			$result = parent::convert($documents);
		}

		return $result;
	}
}