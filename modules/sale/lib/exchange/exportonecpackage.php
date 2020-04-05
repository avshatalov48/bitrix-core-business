<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Main\ArgumentException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Exchange\Entity\EntityImport;
use Bitrix\Sale\Exchange\Entity\OrderImport;
use Bitrix\Sale\Exchange\Entity\PaymentImport;
use Bitrix\Sale\Exchange\Entity\ShipmentImport;
use Bitrix\Sale\Exchange\Entity\UserImportBase;
use Bitrix\Sale\Exchange\OneC\Converter;
use Bitrix\Sale\Exchange\OneC\ConverterFactory;
use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\UserProfileDocument;
use Bitrix\Sale\IBusinessValueProvider;
use Bitrix\Sale\Internals\CollectionBase;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;

abstract class ExportOneCPackage extends ExportOneCBase
{
	use PackageTrait
	{
		PackageTrait::load as protected load_AliasTrait;
	}
	use LoggerTrait;
	use BaseTrait;

	private static $instance = null;

	/**
	 * @return static
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new static();
		}
		return self::$instance;
	}

	private function __clone() {}
	private function __construct() {}

	/**
	 * @param array $fields
	 * @return Result
	 */
	protected function getItems(array $fields)
	{
		$result = new Result();

		$orderId = $fields['ORDER_ID'];

		$orderImport = ManagerExport::create(EntityType::ORDER);
		self::load($orderImport, array('ID'=>$orderId));

		/** @var \Bitrix\Sale\Order $order */
		$order = $orderImport->getEntity();

		$profileImport = ManagerExport::create(EntityType::USER_PROFILE);
		self::load($profileImport, array('ID'=>$order->getUserId()));

		$list = array_merge(
			$this->loadItemsByCollection($order->getPaymentCollection(), $order),
			$this->loadItemsByCollection($order->getShipmentCollection(), $order));

		$list[] = $orderImport;
		$list[] = $profileImport;

		$this->initLogger($list);

		$result->setData($list);
		return $result;
	}

	/**
	 * @param CollectionBase $collection
	 * @param Order $order
	 * @return array
	 */
	protected function loadItemsByCollection(CollectionBase $collection, Order $order)
	{
		$list = array();

		if(count($collection)>0)
		{
			foreach ($collection as $entity)
			{
				if($entity instanceof Shipment)
				{
					if($entity->isSystem())
						continue;
				}

				$typeId = $this->resolveEntityTypeId($entity);

				/** @var EntityImport $entityExport */
				$entityExport = ManagerExport::create($typeId);
				self::load($entityExport, array('ID'=>$entity->getId()), $order);
				$list[] = $entityExport;
			}
		}

		return $list;
	}

	/**
	 * @param Entity $entity
	 * @return int
	 */
	protected function resolveEntityTypeId(Entity $entity)
	{
		$typeId = EntityType::UNDEFINED;

		if($entity instanceof Order)
			$typeId = OrderImport::resolveEntityTypeId($entity);
		elseif($entity instanceof Payment)
			$typeId = PaymentImport::resolveEntityTypeId($entity);
		elseif($entity instanceof Shipment)
			$typeId = ShipmentImport::resolveEntityTypeId($entity);

		return $typeId;
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	protected function convert(array $items)
	{
		$result = new Result();
		$list = array();

		$this->convertEntityFields($items);

		foreach ($items as $item)
		{
			$list[] = $this->convertEntity($item);
		}

		$this->convertDocumentFields($list);

		$list = $this->modifyDocumentsCollection($list);

		if($result->isSuccess())
		{
			$result->setData($list);
		}
		return $result;
	}

	/**
	 * @param DocumentBase[] $documents
	 */
	protected function convertDocumentFields(array $documents)
	{
		$documentOrder = $this->getDocumentByTypeId(EntityType::ORDER, $documents);
		$orderFields = $documentOrder->getFieldValues();

		/** @var UserProfileDocument $documentProfile */
		$documentProfile = $this->getDocumentByTypeId(EntityType::USER_PROFILE, $documents);
		$orderFields['AGENT'] = $documentProfile->getFieldValues();

		$documentOrder->setFields($orderFields);
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result;
	 */
	protected function convertEntityFields(array $items)
	{
		$result = new Result();

		$orderImport = $this->getEntityByTypeId(EntityType::ORDER, $items);
		/** @var ProfileImport $profileImport */
		$profileImport = $this->getEntityByTypeId(EntityType::USER_PROFILE, $items);

		//region business value order profile shipment payment
		$this->prepareEntityFieldsBusinessValue($orderImport);
		$this->prepareEntityFieldsBusinessValue($profileImport, $orderImport);

		foreach ($items as $item)
		{
			if($item instanceof ShipmentImport || $item instanceof PaymentImport)
			{
				$this->prepareEntityFieldsBusinessValue($item);
			}
		}
		//endregion

		//region payment.lid && payment.vesion
		$orderFields = $orderImport->getFieldValues();
		foreach ($items as $item)
		{
			if($item instanceof PaymentImport)
			{
				$paymentFields = $item->getFieldValues();
				$paymentFields['TRAITS']['LID'] = $orderFields['TRAITS']['LID'];
				$paymentFields['TRAITS']['VERSION'] = $orderFields['TRAITS']['VERSION'];
				$item->setFields($paymentFields);
			}
		}
		//endregion

		//region shipment.lid && shipment.version
		$orderFields = $orderImport->getFieldValues();
		foreach ($items as $item)
		{
			if($item instanceof ShipmentImport)
			{
				$shipmentFields = $item->getFieldValues();
				$shipmentFields['TRAITS']['LID'] = $orderFields['TRAITS']['LID'];
				$shipmentFields['TRAITS']['VERSION'] = $orderFields['TRAITS']['VERSION'];
				$item->setFields($shipmentFields);
			}
		}
		//endregion

		$this->addItemOrderDelivery($items);

		return $result;
	}

	/**
	 * @param ImportBase[] $items
	 */
	protected function addItemOrderDelivery(array $items)
	{
		$orderImport = $this->getEntityByTypeId(EntityType::ORDER, $items);

		//region shipment.items ORDER_DELIVERY
		$orderFields = $orderImport->getFieldValues();
		foreach ($items as $item)
		{
			if($item instanceof ShipmentImport)
			{
				$shipmentFields = $item->getFieldValues();
				/** @var Shipment $shipmemt */
				$shipmemt = $item->getEntity();
				if($shipmemt->getPrice()>0)
				{

					$shipmentFields['ITEMS'][] = array_merge(
						array(
							'PRODUCT_XML_ID'=>ImportOneCBase::DELIVERY_SERVICE_XMLID,
							'NAME'=>DocumentBase::getLangByCodeField(ImportOneCBase::DELIVERY_SERVICE_XMLID),
							'MEASURE_CODE'=>Converter::MEASURE_CODE_DEFAULT,
							'KOEF'=>Converter::KOEF_DEFAULT,
							'PRICE'=>$shipmemt->getPrice(),
							'QUANTITY'=>1
						),
						$this->getVatRateByShipment($item, $this->getSummOrderTaxes($orderFields['TAXES']))
					);
				}
				$item->setFields($shipmentFields);
			}
		}
		//endregion

		//region order.items ORDER_DELIVERY
		$orderFields = $orderImport->getFieldValues();
		foreach ($items as $item)
		{
			if($item instanceof ShipmentImport)
			{
				$shipmentItems = $this->getProductsItems($item->getFieldValues());
				if($this->deliveryServiceExists($shipmentItems))
				{
					$orderFields['ITEMS'][] = $this->getDeliveryServiceItem($shipmentItems);
				}
			}
		}
		$orderImport->setFields($orderFields);
		//endregion
	}

	/**
	 * @param array $list
	 * @return mixed|null
	 */
	protected function getDeliveryServiceItem(array $list)
	{
		foreach ($list as $k=>$items)
		{
			if($items['PRODUCT_XML_ID'] == ImportOneCBase::DELIVERY_SERVICE_XMLID)
			{
				return $items;
			}
		}

		return null;
	}

	/**
	 * @param $taxes
	 * @return int
	 */
	protected function getSummOrderTaxes($taxes)
	{
		$orderTax = 0;
		foreach ($taxes as $tax)
		{
			$tax["VALUE_MONEY"] = roundEx($tax["VALUE_MONEY"], 2);
			$orderTax += $tax["VALUE_MONEY"];
		}
		return $orderTax;
	}

	/**
	 * @param ShipmentImport $item
	 * @param $orderTax
	 * @return array
	 */
	protected function getVatRateByShipment(ShipmentImport $item, $orderTax)
	{
		$result = array();

		$shipmentFields = $item->getFieldValues();
		/** @var Shipment $shipmemt */
		$shipmemt = $item->getEntity();
		if($shipmemt->getPrice()>0)
		{
			$vatRate = 0;
			$vatSum = 0;
			$order = $shipmemt->getParentOrder();
			/** @var BasketItem $basket */
			foreach ($order->getBasket() as $basket)
			{
				$vatRate = $basket->getVatRate();
				$basketVatSum = $basket->getPrice()/($vatRate+1) * $vatRate;
				$vatSum += roundEx($basketVatSum * $basket->getQuantity(), 2);
			}

			$tax = roundEx((($shipmemt->getPrice() / ($vatRate+1)) * $vatRate), 2);

			if($orderTax > $vatSum && $orderTax == roundEx($vatSum + $tax, 2))
			{
				$result = array('VAT_RATE'=>$vatRate*100);
			}
		}
		return $result;
	}

	/**
	 * @param DocumentBase $document
	 * @param UserProfileDocument $documentProfile
	 */
	protected function prepareDocumentFieldsDeliveryAddress(DocumentBase $document, UserProfileDocument $documentProfile)
	{
		$fields = $document->getFieldValues();
		$profileFields = $documentProfile->getFieldValues();

		$fields['REK_VALUES'] = is_array($fields['REK_VALUES'])? $fields['REK_VALUES']:array();
		$profileFields['REK_VALUES'] = is_array($profileFields['REK_VALUES'])? $profileFields['REK_VALUES']:array();

		$fields['REK_VALUES'] = array_merge($fields['REK_VALUES'], $profileFields['REK_VALUES']);

		$document->setFields($fields);
	}

	/**
	 * @param ImportBase $item
	 * @param OrderImport $orderImport
	 * @throws ArgumentException
	 */
	protected function prepareEntityFieldsBusinessValue(ImportBase $item, OrderImport $orderImport=null)
	{
		if(!($item instanceof OrderImport || $item instanceof ShipmentImport || $item instanceof PaymentImport || $item instanceof UserImportBase))
			throw new ArgumentException("Entity must be instanceof OrderImport or ShipmentImport or PaymentImport or ProfileImport");

		$fields = $item->getFieldValues();

		/** @var IBusinessValueProvider $provider */
		$provider = ($item instanceof UserImportBase ? $orderImport->getEntity():$item->getEntity());
		$fields['BUSINESS_VALUE'] = $item::getBusinessValue($provider);

		$item->setFields($fields);
	}

	/**
	 * @param DocumentBase[] $documents
	 * @param int $level
	 * @return Result
	 */
	protected function outputXmlDocuments(array $documents, $level=0)
	{
		$result = new Result();
		$list = array();

		foreach ($documents as $document)
		{
			$list[] = $this->outputXmlDocument($document, $level);
		}
		$result->setData($list);

		return $result;
	}

	/**
	 * @param DocumentBase $document
	 * @param int $level
	 * @return string
	 */
	protected function outputXmlDocument(DocumentBase $document, $level)
	{
		$xml = $document->openNodeDirectory($level, $document::getLangByCodeField($document->getNameNodeDocument()));
		$xml .= $document->output($level+1);
		$xml .= $document->closeNodeDirectory($level, $document::getLangByCodeField($document->getNameNodeDocument()));
		return $xml;
	}

	/**
	 * @param DocumentBase[] $list
	 * @return DocumentBase[]
	 */
	protected function modifyDocumentsCollection(array $list)
	{
		$result = array();
		foreach ($list as $document)
		{
			if(!($document instanceof UserProfileDocument))
				$result[] = $document;
		}
		return $result;
	}

	/**
	 * @param EntityImport[] $items
	 */
	protected function initLogger(array $items)
	{
		foreach ($items as $item)
		{
			$item->initLogger();
		}
	}

	/**
	 * @param ImportBase $item
	 * @return DocumentBase
	 */
	protected function convertEntity(ImportBase $item)
	{
		$params = $item->getFieldValues();

		$settings = ManagerImport::getSettingsByType($item->getOwnerTypeId());

		$convertor = $this->converterFactoryCreate($item->getOwnerTypeId());
		$convertor->loadSettings($settings);

		$fields = $convertor->externalize($params);

		$document = $this->documentFactoryCreate($item->getOwnerTypeId());
		$document->setFields($fields);

		return $document;
	}

	/**
	 * @param DocumentBase[] $documents
	 * @return Result
	 */
	protected function export(array $documents)
	{
		$result = new Result();

		$xml = '';
		$r = $this->outputXmlDocuments($documents);
		if($r->isSuccess())
		{
			$xml = implode('', $r->getData());

			$this->setRawData($xml);
		}

		return $result->setData([$xml]);
	}

	/**
	 * @param ImportBase $item
	 * @param array $fields
	 * @param null $order
	 */
	protected static function load(ImportBase $item, array $fields, $order=null)
	{
		static::load_AliasTrait($item, $fields, $order);
		$item->initFields();
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	protected function logger(array $items)
	{
		/** @var OrderImport $orderItem */
		$orderItem = $this->getEntityByTypeId(EntityType::ORDER, $items);
		return $this->loggerEntitiesPackage($items, $orderItem);
	}
}