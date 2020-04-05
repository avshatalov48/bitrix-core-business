<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\EntityMarker;
use Bitrix\Sale\Order;
use Bitrix\Sale\Result;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\Exchange\OneC;
use Bitrix\Sale\ResultWarning;

IncludeModuleLangFile(__FILE__);

class ImportOneCPackage extends ImportOneCBase
{
	use PackageTrait;
	use LoggerTrait;

	private static $instance = null;
	private static $settings = null;

    protected $order = null;

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
	 * @return Result|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	static public function checkSettings()
	{
		if(self::$settings === null)
		{
			$result = new Result();
			$message = self::getMessage();

			if(Option::get('catalog', 'default_use_store_control', 'N')=='Y' ||
				Option::get('catalog', 'enable_reservation', 'N')=='Y')
			{
				$result->addError(new Error($message["CC_BSC1_USE_STORE_SALE"]));
			}

			if(Option::get("main", "~sale_converted_15", 'N') <> 'Y')
			{
				$result->addError(new Error($message["CC_BSC1_CONVERT_SALE"]));
			}

			if(Option::get("sale", "allow_deduction_on_delivery", "N") == 'Y')
			{
				$result->addError(new Error($message["CC_BSC1_SALE_ALLOW_DEDUCTION_ON_DELIVERY_ERROR"]));
			}

			self::$settings = $result;
		}

		return self::$settings;
	}

	/**
	 * @param OneC\DocumentBase[] $documents
	 * @return Result
	 */
	protected function checkDocuments(array $documents)
	{
		return new Result();
	}

	/**
	 * @param array $list
	 * @return mixed|null
	 */
	protected function getDeliveryServiceItem(array $list)
	{
		foreach ($list as $k=>$items)
		{
			if(array_key_exists(self::DELIVERY_SERVICE_XMLID, $items))
			{
				return $items;
			}
		}

		return null;
	}

    /**
     * @param OneC\DocumentBase[] $documents
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     */
	protected function convert(array $documents)
    {
		$result = new Result();
        $list = array();

		$documentOrder = $this->getDocumentByTypeId(EntityType::ORDER, $documents);

		if($documentOrder instanceof OneC\OrderDocument)
		{
			$agentFieldValue = $documentOrder->getFieldValues();
			if(is_array($agentFieldValue['AGENT']))
			{
				$documentProfile = new OneC\UserProfileDocument();
				$documentProfile->setFields($agentFieldValue['AGENT']);
				$documents[] = $documentProfile;
			}
		}

        foreach($documents as $document)
        {
            $list[] = $this->convertDocument($document);
        }

        if($result->isSuccess())
        {
            $result = $this->checkFields($list);
            if($result->isSuccess())
            {
                $result->setData($list);
            }
        }

        return $result;
    }

    /**
     * @param array $items
     * @return Result
     */
    protected function checkFields(array $items)
    {
        $result = new Result();

        $parentEntityId = null;

		$item = $this->getEntityByTypeId(EntityType::ORDER, $items);
        if($item instanceof Exchange\Entity\OrderImport)
		{
			$params = $item->getFieldValues();
			$fields = $params['TRAITS'];

			if($fields['ID']<>'')
			{
				$parentEntityId = $fields['ID'];
			}
			elseif($fields[$item::getFieldExternalId()]<>'')
			{
				$parentEntityId = $fields[$item::getFieldExternalId()];
			}
		}

        if(empty($parentEntityId))
            $result->addErrors(array(new Error('Order not found')));

        foreach($items as $item)
        {
            if($item->getOwnerTypeId() <> EntityType::ORDER)
            {
                $params = $item->getFieldValues();
                $fields = $params['TRAITS'];

                if(!empty($parentEntityId) && $fields['ORDER_ID']<>'')
                {
                    if($parentEntityId <> $fields['ORDER_ID'])
                    {
                        $result->addErrors(array(new Error('Order not found')));
                        break 1;
                    }
                }
            }
        }

        if($result->isSuccess())
			$result = parent::checkFields($items);

        return $result;
    }

    public static function configuration()
    {
		parent::configuration();

    	ManagerImport::registerInstance(EntityType::ORDER, OneC\ImportSettings::getCurrent(), new OneC\CollisionOrder(), new OneC\CriterionOrder());
		ManagerImport::registerInstance(EntityType::SHIPMENT, OneC\ImportSettings::getCurrent(), new OneC\CollisionShipment(), new OneC\CriterionShipment());
		ManagerImport::registerInstance(EntityType::PAYMENT_CASH, OneC\ImportSettings::getCurrent(), new OneC\CollisionPayment(), new OneC\CriterionPayment());
		ManagerImport::registerInstance(EntityType::PAYMENT_CASH_LESS, OneC\ImportSettings::getCurrent(), new OneC\CollisionPayment(), new OneC\CriterionPayment());
		ManagerImport::registerInstance(EntityType::PAYMENT_CARD_TRANSACTION, OneC\ImportSettings::getCurrent(), new OneC\CollisionPayment(), new OneC\CriterionPayment());
		ManagerImport::registerInstance(EntityType::USER_PROFILE, OneC\ImportSettings::getCurrent());
    }

    /**
     * @param ImportBase[] $items
     * @return ImportBase[]
     */
    protected function sortItems(array $items)
    {
        $list = array();
        $i = 0;

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::USER_PROFILE)
            {
                $list[$i++] = $item;
            }
        }

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::ORDER)
            {
                $list[$i++] = $item;
            }
        }

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() <> EntityType::ORDER && $item->getOwnerTypeId() <> EntityType::USER_PROFILE)
            {
                $list[$i++] = $item;
            }
        }

        return $list;
    }

    /**
     * @param ImportBase[] $items
     * @return Result
     * @inernal
     */
    protected function import(array $items)
    {
        $result = new Result();

		$items = $this->sortItems($items);
        $itemOrder = $this->loadOrder($items);

        if($itemOrder->getEntityId()>0)
        {
        	$r = $this->UpdateCashBoxChecks($itemOrder, $items);
			if($r->isSuccess())
			{
				$this->save($itemOrder, $items);
				return $result;
			}

            $r = $this->onBeforeEntityModify($itemOrder, $items);
            if($r->hasWarnings())
                $this->marker($itemOrder, $r);
        }

		if(!$this->hasCollisionErrors($items))
		{
			/** Only sorted items */
			foreach($items as $item)
			{
				if($item->getOwnerTypeId() == EntityType::USER_PROFILE)
				{
					/** @var Exchange\Entity\UserImportBase $item */
					$r = new Result();
					if($itemOrder->getEntityId() == null)
					{
						$params = $item->getFieldValues();
						$fields = $params['TRAITS'];

						$personalTypeId = $params['TRAITS']['PERSON_TYPE_ID'] = $item->resolvePersonTypeId($fields);

						$property = $params['ORDER_PROPS'];
						if(!empty($property))
						{
							$params['ORDER_PROP'] = $item->getPropertyOrdersByConfig($personalTypeId, array(), $property);
						}

						unset($params['ORDER_PROPS']);
						$item->setFields($params);

						$r = $item->load($fields);

						if(intval($personalTypeId)<=0)
							$r->addError(new Error(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_PERSONAL_TYPE_IS_EMPTY", array("#DOCUMENT_ID#"=>$fields['XML_ID'])), "PACKAGE_ERROR_PERSONAL_TYPE_IS_EPMTY"));

						if($r->isSuccess())
						{
							if(!$this->importableItems($item))
							{
								return new Result();
							}

							$r = $this->modifyEntity($item);

							if(intval($item->getId())<=0)
								$r->addError(new Error(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_USER_IS_EMPTY", array("#DOCUMENT_ID#"=>$fields['XML_ID'])), "PACKAGE_ERROR_USER_IS_EPMTY"));

							if($r->isSuccess())
							{
								/** prepare for import Order */
								$paramsOrder = $itemOrder->getFieldValues();
								$fieldsOrder = &$paramsOrder['TRAITS'];

								if(!empty($property))
								{
									$fieldsOrder['ORDER_PROP'] = $params['ORDER_PROP'];
								}

								$fieldsOrder['USER_ID'] = $item->getId();
								$fieldsOrder['PERSON_TYPE_ID'] = $personalTypeId;
								$itemOrder->setFields($paramsOrder);
							}
						}
					}
				}
				elseif($item->getOwnerTypeId() == EntityType::ORDER)
				{
					if(!$this->importableItems($itemOrder))
					{
						return new Result();
					}

					$r = $this->modifyEntity($itemOrder);
				}
				else
				{
					/** @var Exchange\Entity\PaymentImport|Exchange\Entity\ShipmentImport $item */
					/** @var Order $order */
					$order = $itemOrder->getEntity();
					$params = $item->getFieldValues();
					$fields = $params['TRAITS'];

					$r = $this->orderIsLoad($order, $itemOrder);
					if(!$r->hasWarnings())
					{
						static::load($item, $fields, $order);

						$r = $this->checkParentById($fields['ID'], $item);
						if(!$r->hasWarnings())
						{
							$isShipped = $order->isShipped();

							$r = $this->modifyEntity($item);

							if($r->isSuccess())
							{
								if($item->getOwnerTypeId() == EntityType::SHIPMENT)
								{
									if(!$isShipped && $order->isShipped())
										$this->onAfterShipmentModifyChangeStatusOnDelivery($itemOrder);
								}
							}
						}
					}
				}

				if(!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					break;
				}
				elseif($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
					break;
				}
			}

			if($result->isSuccess() && !$result->hasWarnings() && !$this->hasCollisionErrors($items))
			{
				$r = $this->onAfterEntitiesModify($itemOrder, $items);
				if(!$r->isSuccess())
					$result->addErrors($r->getErrors());
				if($r->hasWarnings())
					$result->addWarnings($r->getWarnings());
			}
		}

        if($result->isSuccess())
        {
            $r = $this->save($itemOrder, $items);
			if(!$r->isSuccess())
				$result->addErrors($r->getErrors());
			if($r->hasWarnings())
				$result->addWarnings($r->getWarnings());
        }

        return $result;
    }

	/**
	 * @param ImportBase $item
	 * @return bool
	 */
	private function importableItems($item)
	{
		if($item->getId() == null && !$item->isImportable())
		{
			switch ($item->getOwnerTypeId())
			{
				case EntityType::ORDER:
				case EntityType::USER_PROFILE:
					return false;
					break;
			}
		}

		return true;
	}

    /**
     * @param ImportBase[] $items
     * @return Entity\OrderImport|null
     */
    protected function loadOrder(array $items)
    {
		$item = $this->getEntityByTypeId(EntityType::ORDER, $items);
		if($item instanceof Exchange\Entity\OrderImport)
		{
			$params = $item->getFieldValues();
			$fields = $params['TRAITS'];

			static::load($item, $fields);

			return $item;
		}

        return null;
    }

    /**
     * Modify the shipment collection.
     * Remove shipments that were not submitted for processing
     * Add a note to the order saying the shipment was removed
     * @internal
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     */
    protected function onBeforeShipmentCollectionModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $result = new Result();

        $shipmentItems = array();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if(empty($order))
            throw new ArgumentNullException('Order');

        foreach($items as $item)
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::SHIPMENT:
                    $params = $item->getFieldValues();
                    $fields = $params['TRAITS'];

                    if(!empty($fields['ID']))
                        $shipmentItems[] = $fields['ID'];
                    break;
            }
        }

        /** @var \Bitrix\Sale\Shipment $shipment */
        $shipmentList = array();
        $shipmentCollection = $order->getShipmentCollection();
        foreach($shipmentCollection as $shipment)
        {
            if($shipment->isSystem())
                continue;

            if(!in_array($shipment->getId(), $shipmentItems))
                $shipmentList[$shipment->getId()] = $shipment;
        }

        if(!empty($shipmentList))
        {
            foreach($shipmentList as $id=>$shipment)
            {
                $typeId = Entity\ShipmentImport::resolveEntityTypeId($shipment);

                /** @var Exchange\Entity\ShipmentImport $item */
                $item = ManagerImport::create($typeId);
                static::load($item, array('ID'=>$id), $order);
				$collision = $item->getLoadedCollision();

				$collision->resolve($item);
                if(!$item->hasCollisionErrors())
                {
                    $result = $item->delete();
                }
                else
                {
					$item->setCollisions(EntityCollisionType::BeforeUpdateShipmentDeletedError, $item->getParentEntity());
                }

                $collisions = $item->getCollisions();
                $item->markedEntityCollisions($collisions);
            }
        }

        return $result;
    }

    /**
     * Modify the payment collection.
     * Remove payments that were not submitted for processing
     * Add a note to the order saying the payment was removed
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     * @inernal
     */
    protected function onBeforePaymentCollectionModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $result = new Result();

        $paymentItems = array();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if(empty($order))
            throw new ArgumentNullException('Order');

        foreach($items as $item)
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::PAYMENT_CARD_TRANSACTION:
                case EntityType::PAYMENT_CASH:
                case EntityType::PAYMENT_CASH_LESS:
                    $params = $item->getFieldValues();
                    $fields = $params['TRAITS'];

                    if(!empty($fields['ID']))
                        $paymentItems[] = $fields['ID'];
                    break;
            }
        }

        /** @var \Bitrix\Sale\Payment $payment */
        $paymentList = array();
        $paymentCollection = $order->getPaymentCollection();
        foreach($paymentCollection as $payment)
        {
            if(!in_array($payment->getId(), $paymentItems))
                $paymentList[$payment->getId()] = $payment;
        }

        if(!empty($paymentList))
        {
            foreach($paymentList as $id=>$payment)
            {
                $typeId = Entity\PaymentImport::resolveEntityTypeId($payment);

                /** @var Exchange\Entity\PaymentImport $item */
                $item = ManagerImport::create($typeId);
                static::load($item, array('ID'=>$id), $order);
				$collision = $item->getLoadedCollision();

				$collision->resolve($item);
				if(!$item->hasCollisionErrors())
                {
                    $result = $item->delete();
                }
                else
                {
					$item->setCollisions(Exchange\EntityCollisionType::BeforeUpdatePaymentDeletedError, $item->getParentEntity());
                }

                $collisions = $item->getCollisions();
                $item->markedEntityCollisions($collisions);
            }
        }

        return $result;
    }

    /**
     * Modify the order and all dependent entities before import
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @throws ArgumentNullException
     * @internal param Entity\OrderImport $order
     * @inernal
     */
    protected function onBeforeBasketModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $basketItems = array();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if(empty($order))
            throw new ArgumentNullException('Order');

        $basket = $order->getBasket();

        foreach($items as $item)
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::ORDER:
                    $params = $item->getFieldValues();
                    $basketItems = $params['ITEMS'];
                    break;
            }
        }

        $basketItemsIndex = $orderImport::calculateDeltaQuantity($basket, $basketItems);

        $result = $orderImport->synchronizeQuantityBasketItems($basketItemsIndex);

        return $result;
    }

    /**
     * Modify all dependent entities before the order is changed
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @inernal
     */
    protected function onBeforeEntityModify(Exchange\Entity\OrderImport $orderImport, array $items)
    {
        $result = new Result();

        /**
		 * @var Result $basketResult
		 * @var Result $paymentResult
		 * @var Result $shipmentResult
		 * */
		$basketItemsResult = $this->onBeforeBasketModify($orderImport, $items);
		$paymentResult = $this->onBeforePaymentCollectionModify($orderImport, $items);
		$shipmentResult = $this->onBeforeShipmentCollectionModify($orderImport, $items);

		if(!$paymentResult->isSuccess())
			$result->addWarnings($paymentResult->getErrors());

		if(!$shipmentResult->isSuccess())
			$result->addWarnings($shipmentResult->getErrors());

		if(!$basketItemsResult->isSuccess() || !$shipmentResult->isSuccess() || !$paymentResult->isSuccess())
			$result->addWarning(new ResultWarning(GetMessage('SALE_EXCHANGE_PACKAGE_ERROR_ORDER_CANNOT_UPDATE'), "PACKAGE_ERROR_ORDER_CANNOT_UPDATE"));

        return $result;
    }

    /**
     * Modify shipment after changed
     * @param Entity\OrderImport $orderImport
     * @return Result
     * @internal param ImportBase $entity
     */
    protected function onAfterShipmentModifyChangeStatusOnDelivery(Exchange\Entity\OrderImport $orderImport)
    {
        $result = new Result();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if($order->isShipped())
        {
            $settings = $orderImport->getSettings();
            $status = $settings->finalStatusOnDeliveryFor($orderImport->getOwnerTypeId());
            if($status !== '')
                $order->setField("STATUS_ID", $status);
        }
        return $result;
    }

    /**
     * Modify all dependent entities after the order is changed
     * @param Entity\OrderImport $orderImport
     * @param ImportBase[] $items
     * @return Result
     */
    protected function onAfterEntitiesModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $result = new Result();

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::ORDER)
            {
                /** @var Order $order */
                $order = $orderImport->getEntity();
                $params = $item->getFieldValues();
                $fields = $params['TRAITS'];

                if($fields['1C_PAYED_DATE'] instanceof DateTime)
                {
                    if(!$order->isPaid())
                    {
                        /** @var Exchange\Entity\OrderImport $item */
                        $item->setCollisions(EntityCollisionType::OrderPayedByStatusError, $order);
                    }
                }

                if($fields['1C_DELIVERY_DATE'] instanceof DateTime)
                {
                    if(!$order->isShipped())
                    {
                        /** @var Exchange\Entity\OrderImport $item */
                        $item->setCollisions(EntityCollisionType::OrderShippedByStatusError, $order);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $id
     * @param Entity\EntityImport $item
     * @return Result
     */
    protected function checkParentById($id, Entity\EntityImport $item)
    {
        $result = new Result();

        /** @var \Bitrix\Sale\Internals\Entity $entity */
        $entity = $item->getEntity();

        if(!empty($id) && empty($entity))
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::PAYMENT_CASH:
                case EntityType::PAYMENT_CARD_TRANSACTION:
                case EntityType::PAYMENT_CASH_LESS:
                    $result->addWarning(new ResultWarning(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_PAYMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED", array("#DOCUMENT_ID#"=>$id)), "PACKAGE_ERROR_PAYMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED"));
                    break;
                case EntityType::SHIPMENT:
                    $result->addWarning(new ResultWarning(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_SHIPMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED", array("#DOCUMENT_ID#"=>$id)), "PACKAGE_ERROR_SHIPMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED"));
                    break;
            }
        }
        return $result;
    }

	/**
	 * @param $order
	 * @param Entity\OrderImport $itemOrder
	 * @return Result
	 */
	protected function orderIsLoad($order, $itemOrder)
	{
		$result = new Result();

		if(!($order instanceof Order))
		{
			$params = $itemOrder->getFieldValues();
			$fields = $params['TRAITS'];

			$result->addWarning(new ResultWarning(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_ORDER_IS_NOT_LOADED", array("#DOCUMENT_ID#"=>$fields['ID_1C'])), "PACKAGE_ERROR_ORDER_IS_NOT_LOADED"));
		}

		return $result;
	}

    /**
     * @param Entity\OrderImport $orderImport
     * @param Result $result
     */
    protected function marker(Exchange\Entity\OrderImport $orderImport, Result $result)
    {
        /** @var Order $order */
        $order = $orderImport->getEntity();

        $orderImport->setField('MARKED', 'Y');
        EntityMarker::addMarker($order, $order, $result);
    }

    /**
     * @param Exchange\Entity\OrderImport $orderImport
     * @param ImportBase[] $items
     * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult|Result|mixed
     */
    protected function save(Exchange\Entity\OrderImport $orderImport, $items)
    {
        foreach ($items as $item)
        {
            if($item instanceof Exchange\Entity\EntityImport)
            {
                $collisions = $item->getCollisions();
                if (!empty($collisions))
                {
                    /** @var ICollision $collision */
                    $item->markedEntityCollisions($collisions);
                }
            }
        }
        return $orderImport->save();
    }

	/**
	 * @param ImportBase[] $items
	 * @return bool
	 */
	protected function hasCollisionErrors($items)
	{
		foreach($items as $item)
		{
			if($item->hasCollisionErrors())
				return true;
		}
		return false;
	}

	/**
	 * @param Entity\OrderImport $orderImport
	 * @param ProfileImport[]|Exchange\Entity\EntityImport[] $items
	 * @return Result
	 * @deprecated
	 */
	protected function UpdateCashBoxChecks(Exchange\Entity\OrderImport $orderImport, array $items)
	{
		$result = new Result();
		$result->addError(new Error('', 'CASH_BOX_CHECK_IGNORE'));

		return $result;
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	protected function logger(array $items)
	{
		/** @var Exchange\Entity\OrderImport $orderItem */
		$orderItem = $this->getEntityByTypeId(EntityType::ORDER, $items);
		return $this->loggerEntitiesPackage($items, $orderItem);
	}
}
