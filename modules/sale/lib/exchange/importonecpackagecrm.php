<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Crm\History\InvoiceStatusHistoryEntry;
use Bitrix\Crm\Statistics\InvoiceSumStatisticEntry;
use Bitrix\Main\Error;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\Exchange\Entity\OrderImport;
use Bitrix\Sale\Exchange\OneC\CRM\ConverterFactory;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;

final class ImportOneCPackageCRM extends ImportOneCPackage
{
	/**
	 * @param OneC\DocumentBase[] $documents
	 * @return Result
	 */
	protected function checkDocuments(array $documents)
	{
		$result = new Result();

		if(!$this->hasDocumentByTypeId(Exchange\OneC\DocumentType::ORDER, $documents))
		{
			$result->addError(new Error(GetMessage('CRM_PACKAGE_NOT_FOUND_ORDER'), 'CRM_PACKAGE_NOT_FOUND_ORDER'));
		}
		else
		{
			$documentOrder = $this->getDocumentByTypeId(Exchange\OneC\DocumentType::ORDER, $documents);

			if(!$this->hasDocumentByTypeId(Exchange\OneC\DocumentType::PAYMENT_CASH, $documents) &&
				!$this->hasDocumentByTypeId(Exchange\OneC\DocumentType::PAYMENT_CASH_LESS, $documents) &&
				!$this->hasDocumentByTypeId(Exchange\OneC\DocumentType::PAYMENT_CARD_TRANSACTION, $documents))
			{
				$result->addError(new Error(GetMessage('CRM_PACKAGE_NOT_FOUND_PAYMENT', array('#XML_1C_DOCUMENT_ID#'=>$documentOrder->getExternalId())), 'CRM_PACKAGE_NOT_FOUND_PAYMENT'));
			}

			$countShipment = 0;
			foreach ($documents as $document)
			{
				if($document->getTypeId() == Exchange\OneC\DocumentType::SHIPMENT)
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
	 * @param $typeId
	 * @return \Bitrix\Sale\Exchange\OneC\Converter
	 */
	protected function converterFactoryCreate($typeId)
	{
		return ConverterFactory::create($typeId);
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
			$documentShipment = $this->getDocumentByTypeId(Exchange\OneC\DocumentType::SHIPMENT, $documents);

			if($documentShipment !== null)
			{
				$entityTypeId = $this->resolveOwnerEntityTypeId($documentShipment->getTypeId());
				$settings = ManagerImport::getSettingsByType($entityTypeId);

				$convertor = Exchange\OneC\ConverterFactory::create($entityTypeId);
				$convertor->init(
					$settings,
					$entityTypeId,
					$documentShipment->getTypeId()
				);

				$fields = $convertor->resolveParams($documentShipment);

				$shipmentPrice = $fields['TRAITS']['BASE_PRICE_DELIVERY'];

				if($shipmentPrice>0)
				{
					$documentOrder = $this->getDocumentByTypeId(Exchange\OneC\DocumentType::ORDER, $documents);
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
					if($document->getTypeId() == Exchange\OneC\DocumentType::SHIPMENT)
					{
						unset($documents[$k]);
					}
				}
			}

			$result = parent::convert($documents);

			if($result->isSuccess())
			{
				$personTypeId = 0;
				$paySystemId = 0;
				$personTypes = \CCrmPaySystem::getPersonTypeIDs();

				/** @var ImportBase[] $entityItems */
				$entityItems = $result->getData();
				foreach ($entityItems as $entityItem)
				{
					if($entityItem->getOwnerTypeId() == EntityType::USER_PROFILE)
					{
						/** @var Exchange\Entity\UserProfileImport $entityItem */
						$personTypeId = $entityItem->isFiz()? (int)$personTypes['CONTACT']:(int)$personTypes['COMPANY'];
						break;
					}
				}

				if($personTypeId>0)
				{
					$billList = \CCrmPaySystem::GetPaySystemsListItems($personTypeId);
					foreach($billList as $billId => $billName)
					{
						$paySystemId = $billId;
						break;
					}
				}

				if($paySystemId>0)
				{
					$list = [];
					foreach ($entityItems as $entityItem)
					{
						if(
							$entityItem->getOwnerTypeId() == EntityType::INVOICE_PAYMENT_CASH ||
							$entityItem->getOwnerTypeId() == EntityType::INVOICE_PAYMENT_CASH_LESS ||
							$entityItem->getOwnerTypeId() == EntityType::INVOICE_PAYMENT_CARD_TRANSACTION)
						{
							$traits = $entityItem->getFieldValues()['TRAITS'];
							$traits['PAY_SYSTEM_ID'] = $paySystemId;
							$entityItem->setField('TRAITS', $traits);
						}
						$list[] = $entityItem;
					}
					$result->setData($list);
				}
			}
		}

		return $result;
	}

	public static function configuration()
	{
		ManagerImport::registerInstance(static::getShipmentEntityTypeId(), OneC\ImportSettings::getCurrent(), new OneC\CollisionShipment(), new OneC\CriterionShipmentInvoice());

		parent::configuration();
	}

	protected function resolveEntityTypeId(\Bitrix\Sale\Internals\Entity $entity)
	{
		$typeId = EntityType::UNDEFINED;

		if($entity instanceof Order)
			$typeId = Exchange\Entity\Invoice::resolveEntityTypeId($entity);
		elseif ($entity instanceof Payment)
			$typeId = Exchange\Entity\PaymentInvoiceBase::resolveEntityTypeId($entity);
		elseif ($entity instanceof Shipment)
			$typeId = Exchange\Entity\ShipmentInvoice::resolveEntityTypeId($entity);

		return $typeId;
	}

	static protected function getParentEntityTypeId()
	{
		return EntityType::INVOICE;
	}

	static protected function getShipmentEntityTypeId()
	{
		return EntityType::INVOICE_SHIPMENT;
	}

	static protected function getPaymentCardEntityTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CARD_TRANSACTION;
	}

	static protected function getPaymentCashEntityTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CASH;
	}

	static protected function getPaymentCashLessEntityTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CASH_LESS;
	}
}