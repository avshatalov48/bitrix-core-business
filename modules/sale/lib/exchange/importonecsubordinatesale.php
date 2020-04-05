<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\Entity\OrderImport;
use Bitrix\Sale\Exchange\Entity\SubordinateSale\EntityImportFactory;
use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\DocumentType;
use Bitrix\Sale\Exchange\OneC\PaymentCardDocument;
use Bitrix\Sale\Exchange\OneC\SubordinateSale\ConverterFactory;
use Bitrix\Sale\Exchange\OneC\SubordinateSale\CriterionShipment;
use Bitrix\Sale\Exchange\OneC\SubordinateSale\DocumentFactory;
use Bitrix\Sale\Exchange\OneC\SubordinateSale\ShipmentDocument;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;

final class ImportOneCSubordinateSale extends ImportOneCPackage
{
	public static function configuration()
	{
		ManagerImport::registerInstance(static::getShipmentEntityTypeId(), OneC\ImportSettings::getCurrent(), new OneC\CollisionShipment(), new CriterionShipment());

		parent::configuration();
	}

	/**
	 * @param DocumentBase[] $documents
	 * @return \Bitrix\Sale\Result
	 */
	protected function convert(array $documents)
	{
		$documentOrder = $this->getDocumentByTypeId(EntityType::ORDER, $documents);

		if($documentOrder instanceof OneC\OrderDocument)
		{
			$fieldsOrder = $documentOrder->getFieldValues();
			$itemsOrder = $this->getProductsItems($fieldsOrder);

			if(is_array($fieldsOrder['SUBORDINATES']))
			{
				foreach ($fieldsOrder['SUBORDINATES'] as $subordinateDocumentFields)
				{
					$typeId = $this->resolveSubordinateDocumentTypeId($subordinateDocumentFields);

					if($typeId == static::getShipmentEntityTypeId())
					{
						$subordinateDocumentItems = array();
						$itemsSubordinate = $this->getProductsItems($subordinateDocumentFields);

						foreach ($itemsSubordinate as $itemSubordinate)
						{
							$xmlId = key($itemSubordinate);

							if($xmlId == self::DELIVERY_SERVICE_XMLID)
							{
								$itemSubordinate[$xmlId]['TYPE'] = ImportBase::ITEM_SERVICE;
								$subordinateDocumentItems[] = $itemSubordinate;
							}
							else
							{
								$item = $this->getItemByParam($xmlId, $itemsOrder);

								if($item !== null)
								{
									$item[$xmlId]['QUANTITY'] = $itemSubordinate[$xmlId]['QUANTITY'];
									$subordinateDocumentItems[] = $item;
								}
							}
						}

						unset($subordinateDocumentFields['ITEMS']);
						unset($subordinateDocumentFields['ITEMS_FIELDS']);

						if(count($subordinateDocumentItems)>0)
						{
							$subordinateDocumentFields['ITEMS'] = $subordinateDocumentItems;
						}
					}

					$document = OneC\DocumentImportFactory::create($typeId);
					$document->setFields($subordinateDocumentFields);
					$documents[] = $document;
				}
				$documentOrder->setField('SUBORDINATES', '');
			}

			//region Presset - генерируем фэйковую отгрузку
			/*
			 * генерируем фэйковую отгрузку, если выполнены условия
			 * 1 обмен с новым модулем от 1С,
			 * 2 отгрузки не переданы в подчиненных документах
			 * 3 все отгрузки по заказу в БУС в статусе не отгружено
			 * 4 и от 1С в табличной части заказа передана ORDER_DELIVERY
			 * */
			if(!$this->hasDocumentByTypeId(static::getShipmentEntityTypeId(), $documents))
			{
				if($this->deliveryServiceExists($itemsOrder))
				{
					//$deliveryItem
					$entityOrder = $this->convertDocument($documentOrder);
					if($entityOrder->getFieldValues()['TRAITS']['ID']>0)
					{
						self::load($entityOrder, ['ID'=>$entityOrder->getFieldValues()['TRAITS']['ID']]);
						/** @var Order $order */
						$order = $entityOrder->getEntity();
						if(!$order->isShipped())
						{
							$shipmentList = [];
							$shipmentIsShipped = false;
							/** @var Shipment $shipment */
							foreach ($order->getShipmentCollection() as $shipment)
							{
								if($shipment->isShipped())
								{
									$shipmentIsShipped = true;
									break;
								}

								if(!$shipment->isSystem())
								{
									$shipmentList[] = $shipment->getFieldValues();
								}
							}

							if(!$shipmentIsShipped)
							{
								if(count($shipmentList)>0)
								{
									//системная и реальная отгрузка
									$externalId = current($shipmentList)['ID_1C'];
									$shipmentFields['ID_1C'] = strlen($externalId)<=0? $documentOrder->getField('ID_1C'):$externalId;
									$shipmentFields['ID'] = current($shipmentList)['ID'];
								}
								else
								{
									//только системная отгрузка
									$shipmentFields['ID_1C'] = $documentOrder->getField('ID_1C');
								}
								// колличество и вся табличная часть всегда береться из заказа т.к. все отгрузки вводятся в 1С и на сайте вообще не может измениться что-то в отгрузке. (требования 1С)
								$shipmentFields['ITEMS'] = $itemsOrder;

								$documentShipment = new ShipmentDocument();
								$documentShipment->setFields($shipmentFields);
								$documents[] = $documentShipment;
							}
						}
					}
				}
			}
			//endregion

			//region - оплаты

			// от 1С может приходить только оплаченные оплаты, шаблоны приходить не могут.
			// Удаляем все не оплаченные оплаты из коллекции документов оплат

			$documents = $this->deletePaymentDocumentNotPaid($documents);

			/** @var OrderImport $entityOrder */
			$entityOrder = $this->convertDocument($documentOrder);
			if($entityOrder->getFieldValues()['TRAITS']['ID']>0)
			{
				self::load($entityOrder, ['ID'=>$entityOrder->getFieldValues()['TRAITS']['ID']]);
				/** @var Order $order */
				$order = $entityOrder->getEntity();

				if(!empty($order))
				{
					$hasPayment = $order->isPaid() || $this->orderPartiallyIsPaid($order);

					if($this->hasPaymentDocuments($documents))
					{
						// оплаченных оплат - нет
						if(!$hasPayment)
						{
							if($order->getPrice() <= $this->getPaymentDocumentsPaidSum($documents))
							{
								//region 2. Из 1С приходит полная оплата - удаляем шаблоны в БУС, заменяем на оплату из 1С
								//endregion
							}
						}

						// частичная оплата заказа коллекцией документов оплат
						$paymentDocumentsPaidSum = $this->getPaymentDocumentsPaidSum($documents);
						if($order->getPrice() > $paymentDocumentsPaidSum)
						{
							//region 3. Из 1С приходит частичная оплата - пытаемся сверить по сумме и по типу,
							// если подходит - удаляем шаблон с такой же суммой и типом, создаем оплату из 1С.
							// Остальные шаблоны оплаты не трогаем.
							// Если по выбранным критериям (сумма + тип) выбираются 2 и более шаблонов выбираем последнюю оплату по ID

							//$r = $this->documentPaymentReplaceId($order, $documents);
							//TODO: оплата должна удаляться через срзданный документ удаления оплаты.
							$r = $this->deletePaymentToReplace($order, $documents);
							if($r->isSuccess())
							{
								$entityOrder->save();
							}

							if($r->getData()['IS_REPLACE'] === true)
							{
								static::setConfig(static::DELETE_IF_NOT_FOUND_RELATED_PAYMENT_DOCUMENT, false);
							}
							else
							{
								//3.1. Если по сумме ничего не находим.
								//- удаляем все шаблоны
								//- если есть оплаченная оплата в БУС, которая не пришла из 1С – коллизия
								//-обновляем те оплаты информация по которым пришла от 1С
								//-создаем частичную оплату от 1С
								//-создаем шаблон оплаты согласно настройкам интеграции на оставшуюся сумму

								$documentPayment = new PaymentCardDocument();
								$documentPayment->setFields([
									'ID_1C'=>$documentOrder->getField('ID_1C'),
									'AMOUNT'=>abs($order->getPrice() - $paymentDocumentsPaidSum)

								]);
								$documents[] = $documentPayment;
							}

							//endregion
						}
					}
					else
					{
						//region 1. Из 1С не приходит оплат - в БУС шаблоны оплат не удаляем
						if(!$hasPayment)
						{
							static::setConfig(static::DELETE_IF_NOT_FOUND_RELATED_PAYMENT_DOCUMENT, false);
						}
						//endregion
					}
				}
			}
			//TODO: доделать удаления оплат из списка документов оплат к импорту. И создания шаблона на всю оставшуюся сумму после импорта частичной оплаты от 1С

			//endregion
		}
		return parent::convert($documents);
	}

	/**
	 * @param DocumentBase[] $documents
	 * @return array
	 */
	protected function deletePaymentDocumentNotPaid(array $documents)
	{
		$result=[];

		foreach ($documents as $document)
		{
			if($this->isPaymentDocument($document))
			{
				if($this->documentIsPaid($document))
				{
					$result[] = $document;
				}
			}
			else
			{
				$result[] = $document;
			}
		}

		return $result;
	}

	/**
	 * @param DocumentBase $document
	 * @return bool
	 */
	protected function documentIsPaid(DocumentBase $document)
	{
		return ($document->getField('REK_VALUES')['1C_PAYED'] == 'Y');
	}

	/**
	 * @param DocumentBase $document
	 * @return bool
	 */
	protected function isPaymentDocument(DocumentBase $document)
	{
		return ($document->getTypeId() == static::getPaymentCardEntityTypeId()
			|| $document->getTypeId() == static::getPaymentCashLessEntityTypeId()
			|| $document->getTypeId() == static::getPaymentCashEntityTypeId());
	}


	protected function documentPaymentReplaceId(Order $order, $documents)
	{
		$result = new Result();

		$paymentCollection = $order->getPaymentCollection();
		$paymentIsReplace = false;

		/** @var Payment $payment */
		foreach($paymentCollection as $payment)
		{
			if(!$payment->isPaid())
			{
				/** @var DocumentBase $document */
				foreach($this->getPaymentDocuments($documents) as $document)
				{
					if(
						$payment->getSum() == (float)$document->getField('AMOUNT') &&
						$this->resolveEntityTypeId($payment) == DocumentType::resolveID($document->getField('OPERATION'))
					)
					{
						$document->setField('ID', $payment->getId());
						$paymentIsReplace = true;
					}
				}
			}
		}

		$result->setData(['IS_REPLACE'=>$paymentIsReplace]);

		return $result;
	}

	/**
	 * @param Order $order
	 * @param DocumentBase[] $documents
	 */
	protected function deletePaymentToReplace(Order $order, $documents)
	{
		$result = new Result();

		$paymentCollection = $order->getPaymentCollection();
		$paymentIsReplace = false;
		$list = [];

		foreach ($documents as $document)
		{
			if($this->isPaymentDocument($document))
			{
				$list[] = [
					'AMOUNT'=>(float)$document->getField('AMOUNT'),
					'OPERATION'=>DocumentType::resolveID($document->getField('OPERATION'))
				];
			}
		}

		if(count($list)>0)
		{
			/** @var Payment $payment */
			foreach($paymentCollection as $payment)
			{
				if(!$payment->isPaid())
				{
					foreach($list as $k=>$documentPayment)
					{
						//echo $this->resolveEntityTypeId($payment);

						if(
							$payment->getSum() == $documentPayment['AMOUNT'] &&
							$this->resolveEntityTypeId($payment) == $documentPayment['OPERATION']
						)
						{
							$r = $this->paymentDelete($payment);
							if(!$r->isSuccess())
							{
								$result->addErrors($r->getErrors());
							}
							else
							{
								$paymentIsReplace = true;
							}

							unset($list[$k]);
						}
					}
				}
			}
		}

		if($result->isSuccess() && $paymentIsReplace)
			$result->setData(['IS_REPLACE'=>true]);

		return $result;
	}

	/**
	 * @param DocumentBase[] $documents
	 * @return bool
	 */
	protected function hasPaymentDocuments(array $documents)
	{
		return ($this->hasDocumentByTypeId(static::getPaymentCardEntityTypeId(), $documents)
				|| $this->hasDocumentByTypeId(static::getPaymentCashLessEntityTypeId(), $documents)
				|| $this->hasDocumentByTypeId(static::getPaymentCashEntityTypeId(), $documents));
	}

	/**
	 * @param DocumentBase[] $documents
	 * @return array
	 */
	protected function getPaymentDocuments($documents)
	{
		$list = [];

		foreach ($documents as $document)
		{
			if($this->isPaymentDocument($document))
			{
				$list[] = $document;
			}
		}
		return $list;
	}

	/**
	 * @param DocumentBase[] $documents
	 * @return float|int
	 */
	protected function getPaymentDocumentsPaidSum($documents)
	{
		$sum = 0;
		/** @var DocumentBase $document */
		foreach ($this->getPaymentDocuments($documents) as $document)
		{
			//echo '<pre>';print_r($document->getFieldValues());
			if($this->documentIsPaid($document))
			{
				$sum += (float)$document->getField('AMOUNT');
			}
		}
		return $sum;
	}

	protected function orderPartiallyIsPaid(Order $order)
	{
		$paymentCollection = $order->getPaymentCollection();
		if(count($paymentCollection)>0)
		{
			/** @var Payment $payment */
			foreach ($paymentCollection as $payment)
			{
				if($payment->isPaid())
					return true;
			}
		}
		return false;
	}

	/**
	 * @param array $fields
	 * @return int
	 */
	protected function resolveSubordinateDocumentTypeId(array $fields)
	{
		$typeId = EntityType::UNDEFINED;

		if(isset($fields['OPERATION']))
		{
			$typeId = EntityType::resolveID($fields['OPERATION']);
		}
		return $typeId;
	}

	/**
	 * @param $xmlId
	 * @param array $items
	 * @param array|null $params
	 * @return mixed|null
	 */
	protected function getItemByParam($key, array $items, array $params=null)
	{
		foreach ($items as $item)
		{
			if(array_key_exists($key, $item))
			{
				return $item;
			}
		}
		return null;
	}

	/**
	 * @param $typeId
	 * @return IConverter
	 */
	protected function converterFactoryCreate($typeId)
	{
		return ConverterFactory::create($typeId);
	}

	/**
	 * @param $typeId
	 * @return DocumentBase
	 */
	protected function documentFactoryCreate($typeId)
	{
		return DocumentFactory::create($typeId);
	}

	/**
	 * @param $typeId
	 * @return ImportBase
	 */
	protected function entityFactoryCreate($typeId)
	{
		return EntityImportFactory::create($typeId);
	}
}