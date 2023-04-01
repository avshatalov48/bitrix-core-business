<?php
namespace Bitrix\Sale\Exchange\Entity;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Sale;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\Exchange\EntityCollisionType;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Delivery\Services\Manager;

IncludeModuleLangFile(__FILE__);

/**
 * Class ShipmentImport
 * @package Bitrix\Sale\Exchange\Entity
 * @internal
 */
class ShipmentImport extends EntityImport
{
	protected static $currentSettingsStores = null;

	public function __construct($parentEntityContext = null)
    {
        parent::__construct($parentEntityContext);
    }

    /**
     * @return int
     */
    public function getOwnerTypeId()
    {
        return Exchange\EntityType::SHIPMENT;
    }

    /**
     * @param Internals\Entity $entity
     * @throws Main\ArgumentException
     */
    public function setEntity(Internals\Entity $entity)
    {
        if(!($entity instanceof Shipment))
            throw new Main\ArgumentException("Entity must be instanceof Shipment");

        $this->entity = $entity;
    }

    /**
     * @param array $fields
     * @return Sale\Result
     */
    protected function checkFields(array $fields)
    {
        $result = new Sale\Result();

        if(intval($fields['ORDER_ID'])<=0 &&
            !$this->isLoadedParentEntity()
        )
        {
            $result->addError(new Error('ORDER_ID is not defined',''));
        }

        return $result;
    }

	/**
	 * @return Main\Entity\AddResult|Main\Entity\UpdateResult|Sale\Result|mixed
	 */
	public function save()
    {
        /** @var Order $parentEntity */
        $parentEntity = $this->getParentEntity();
        return $parentEntity->save();
    }

	/**
	 * @param array $params
	 * @return Sale\Result
	 */
	public function add(array $params)
    {
		$result = new Sale\Result();

		if(!$this->isLoadedParentEntity())
		{
			$result->addError(new Error(GetMessage('SALE_EXCHANGE_ENTITY_SHIPMENT_ORDER_IS_NOT_LOADED_ERROR'),'ENTITY_SHIPMENT_ORDER_IS_NOT_LOADED_ERROR'));
			return $result;
		}

		$fields = $params['TRAITS'];
		$parentEntity = $this->getParentEntity();

        if(($shipmentService = Manager::getObjectById($fields['DELIVERY_ID'])) == null)
        {
			$result->addError(new Error(GetMessage('SALE_EXCHANGE_ENTITY_SHIPMENT_DELIVERY_SERVICE_IS_NOT_AVAILABLE_ERROR'),'DELIVERY_SERVICE_IS_NOT_AVAILABLE_ERROR'));
		}
		else
		{
			$shipmentCollection = $parentEntity->getShipmentCollection();
			$shipment = $shipmentCollection->createItem($shipmentService);

			$shipment->setField('DELIVERY_NAME', $shipmentService->getName());

			$basket = $parentEntity->getBasket();
			$result = $this->fillShipmentItems($shipment, $basket, $params);
			if(!$result->isSuccess())
			{
				return $result;
			}

			$result = $shipment->setFields($fields);

			if($result->isSuccess())
			{
				$this->setEntity($shipment);
			}
		}

        return $result;
    }

	/**
	 * @param array $params
	 * @return Sale\Result
	 */
	public function update(array $params)
    {
    	$result = new Sale\Result();

		if(!$this->isLoadedParentEntity())
		{
			$result->addError(new Error(GetMessage('SALE_EXCHANGE_ENTITY_SHIPMENT_ORDER_IS_NOT_LOADED_ERROR'),'ORDER_IS_NOT_LOADED_ERROR'));
			return $result;
		}

		/** @var Shipment $shipment */
        $shipment = $this->getEntity();

        $parentEntity = $this->getParentEntity();

        $criterion = $this->getCurrentCriterion($this->getEntity());

        $fields = $params['TRAITS'];
        if($criterion->equals($fields))
        {
            $basket = $parentEntity->getBasket();
            $result = $this->fillShipmentItems($shipment, $basket, $params);
            if(!$result->isSuccess())
            {
                return $result;
            }
        }

        $result = $shipment->setFields($fields);

        return $result;
    }

    /**
     * @param array|null $params
     * @return Sale\Result
     * @throws Main\ObjectNotFoundException
     */
    public function delete(array $params = null)
    {
        /** @var Shipment $entity */
        $entity = $this->getEntity();
        $result = $entity->delete();
        if($result->isSuccess())
        {
            //$this->setCollisions(EntityCollisionType::OrderShipmentDeleted, $this->getParentEntity());
        }
        else
        {
            $this->setCollisions(EntityCollisionType::OrderShipmentDeletedError, $this->getParentEntity(), implode(',', $result->getErrorMessages()));
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getExternalFieldName()
    {
        return 'EXTERNAL_DELIVERY';
    }

    /**
     * @param array $fields
	 * @return Sale\Result
     * @throws Main\ArgumentException
     * @throws Main\ArgumentNullException
     */
    public function load(array $fields)
    {
        $r = $this->checkFields($fields);
        if(!$r->isSuccess())
        {
            throw new Main\ArgumentException('ORDER_ID is not defined');
        }

        if(!$this->isLoadedParentEntity() && !empty($fields['ORDER_ID']))
        {
			$this->setParentEntity(
				$this->loadParentEntity(['ID'=>$fields['ORDER_ID']])
			);
        }

        if($this->isLoadedParentEntity())
        {
            $parentEntity = $this->getParentEntity();

            if(!empty($fields['ID']))
            {
                $shipment = $parentEntity->getShipmentCollection()->getItemById($fields['ID']);
            }

            /** @var Shipment $shipment*/
            if(!empty($shipment) && !$shipment->isSystem())
            {
                $this->setEntity($shipment);
            }
            else
            {
                $this->setExternal();
            }
        }
		return new Sale\Result();
    }

    /**
     * @param Order $order
     * @param Sale\BasketItem $basketItem
     * @return int
     * @throws Main\ObjectNotFoundException
     */
    private function getBasketItemQuantity(Order $order, Sale\BasketItem $basketItem)
    {
        $allQuantity = 0;
        /** @var Shipment $shipment */
        foreach ($order->getShipmentCollection() as $shipment)
        {
            if($shipment->isShipped())
            	continue;

            $allQuantity += $shipment->getBasketItemQuantity($basketItem);
        }

        return $allQuantity;
    }

	/**
	 * @param Sale\BasketBase $basket
	 * @param array $item
	 * @return Sale\BasketItem
	 */
	protected function getBasketItemByItem(Sale\BasketBase $basket, array $item)
	{
		return OrderImport::getBasketItemByItem($basket, $item);
	}

    /**
     * @param Shipment $shipment
     * @param Sale\BasketBase $basket
     * @param array $params
     * @return Sale\Result
     * @throws Main\ObjectNotFoundException
     */
    private function fillShipmentItems(Shipment $shipment, Sale\BasketBase $basket, array $params)
    {
        $result = new Sale\Result();

        /** @var Order $order */
        $order = $basket->getOrder();

        $fieldsBasketItems = $params['ITEMS'];

        if(is_array($fieldsBasketItems))
        {
            foreach($fieldsBasketItems as $items)
            {
                foreach($items as $productXML_ID => $item)
                {
                    if($productXML_ID == Exchange\ImportOneCBase::DELIVERY_SERVICE_XMLID)
                    	continue;

                	if($item['TYPE'] == Exchange\ImportBase::ITEM_ITEM)
                    {
                        if($basketItem = $this->getBasketItemByItem($basket, $item))
                        {
                            $basketItemQuantity = $this->getBasketItemQuantity($order, $basketItem);

                            $shipmentItem = static::getShipmentItem($shipment, $basketItem);

                            $deltaQuantity = $item['QUANTITY'] - $shipmentItem->getQuantity();

                            if($deltaQuantity < 0)
                            {
                                $result = $this->fillShipmentItem($shipmentItem, 0, abs($deltaQuantity));
                            }
                            elseif($deltaQuantity > 0)
                            {
                                if($basketItemQuantity >= $item['QUANTITY'])
                                {
                                    $systemShipment = $order->getShipmentCollection()->getSystemShipment();
                                    $systemBasketQuantity = $systemShipment->getBasketItemQuantity($basketItem);

                                    if($systemBasketQuantity >= $deltaQuantity)
                                    {
                                        $this->fillShipmentItem($shipmentItem, $item['QUANTITY'], $shipmentItem->getQuantity());
                                    }
                                    else
                                    {
                                        $needQuantity = $deltaQuantity - $systemBasketQuantity;

                                        $r = $this->synchronizeQuantityShipmentItems($basketItem, $needQuantity);
                                        if($r->isSuccess())
                                        {
                                            $this->fillShipmentItem($shipmentItem, $item['QUANTITY'], $shipmentItem->getQuantity());
                                        }
                                        else
                                        {
                                            $this->setCollisions(EntityCollisionType::ShipmentBasketItemsModifyError, $shipment);
                                        }
                                    }
                                }
                                else
                                {
                                    $this->setCollisions(EntityCollisionType::ShipmentBasketItemQuantityError, $shipment, $item['NAME']);
                                }
                            }

							if (
								isset($item['MARKINGS'])
								&& is_array($item['MARKINGS'])
								&& count($item['MARKINGS']) > 0
							)
							{
								$result = $this->fillMarkingsShipmentItem($shipmentItem, $item['MARKINGS']);
							}
						}
                        else
                        {
                            $this->setCollisions(EntityCollisionType::ShipmentBasketItemNotFound, $shipment);
                        }
                    }
                    else
					{
						$this->setCollisions(EntityCollisionType::OrderBasketItemTypeError, $shipment, $item['NAME']);
					}
                }
            }
        }
        return $result;
    }

    /**
     * @param Shipment $shipment
     * @param Sale\BasketItem $basketItem
     * @return Sale\ShipmentItem|null
     * @throws Main\ObjectNotFoundException
     */
    private static function getShipmentItem(Sale\Shipment $shipment, Sale\BasketItem $basketItem)
    {
        /** @var Sale\ShipmentItemCollection $shipmentItemCollection */
        if (!$shipmentItemCollection = $shipment->getShipmentItemCollection())
        {
            throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
        }

        $shipmentItem = $shipmentItemCollection->getItemByBasketCode($basketItem->getBasketCode());
        if (empty($shipmentItem))
        {
            $shipmentItem = $shipmentItemCollection->createItem($basketItem);
        }
        return $shipmentItem;
    }

	protected function fillMarkingsShipmentItem(Sale\ShipmentItem $item, $markings)
	{
		$result = new Sale\Result();

		$itemStoreCollection = $item->getShipmentItemStoreCollection();
		if (!$itemStoreCollection)
		{
			return $result;
		}

		$this->resetMarkingsShipmentItem($item);

		$delta = min(count($markings), $item->getQuantity());

		if ($itemStoreCollection->count() < $delta)
		{
			for ($i = (count($markings) - $itemStoreCollection->count()); $i > 0; $i--)
			{
				$itemStore = $itemStoreCollection->createItem($itemStoreCollection->getShipmentItem()->getBasketItem());
				$r = $itemStore->setFields([
					'QUANTITY'=>1
				]);

				if($r->isSuccess() === false)
				{
					$result->addErrors($r->getErrors());
					break 1;
				}
			}
		}

		if ($result->isSuccess())
		{
			$k = 0;
			/** @var  Sale\ShipmentItemStore $storeItem */
			foreach ($itemStoreCollection as  $storeItem)
			{
				$r = $storeItem->setField('MARKING_CODE', $markings[$k++]);
				if ($r->isSuccess() === false)
				{
					$result->addErrors($r->getErrors());
					break 1;
				}
			}
		}

		return $result;
	}

	protected function resetMarkingsShipmentItem(Sale\ShipmentItem $item)
	{
		$itemStoreCollection = $item->getShipmentItemStoreCollection();
		if ($itemStoreCollection)
		{
			/** @var \Bitrix\Sale\ShipmentItemStore $barcode */
			foreach ($itemStoreCollection as $barcode)
			{
				$barcode->setField('MARKING_CODE', null);
			}
		}
	}

	private function syncRelationBarcodeMarkingsCode(Sale\ShipmentItem $shipmentItem, $value)
	{
		if ($shipmentItem->getBasketItem()->isSupportedMarkingCode())
		{
			$after = $shipmentItem->getQuantity() + $value;
			if ($after < $shipmentItem->getQuantity()) // minus
			{
				$deltaQuantity = $shipmentItem->getQuantity() - $after;

				$storeCollection = $shipmentItem->getShipmentItemStoreCollection();
				if ($storeCollection)
				{
					/** @var Sale\ShipmentItemStore $store */
					foreach ($storeCollection as $store)
					{
						if ($deltaQuantity > 0)
						{
							$store->delete();
							$deltaQuantity--;
						}
					}
				}

			}
		}
	}

    /**
     * @param Sale\ShipmentItem $shipmentItem
     * @param $value
     * @param $oldValue
     * @return Sale\Result
     */
    private function fillShipmentItem(Sale\ShipmentItem $shipmentItem, $value, $oldValue)
    {
        $result = new Sale\Result();

        $deltaQuantity = $value - $oldValue;

        if($shipmentItem->getQuantity() + $deltaQuantity == 0)
        {
            $r = $shipmentItem->delete();
        }
        else
        {
			$this->syncRelationBarcodeMarkingsCode($shipmentItem, $deltaQuantity);

            $r = $shipmentItem->setField(
                "QUANTITY",
                $shipmentItem->getQuantity() + $deltaQuantity
            );
        }

        /** @var Sale\ShipmentItemCollection $shipmentItemCollection */
        $shipmentItemCollection = $shipmentItem->getCollection();

        /** @var Shipment $shipment */
        if ($shipment = $shipmentItemCollection->getShipment())
        {
            if(!$r->isSuccess())
            {
                $result->addErrors($r->getErrors());
                $this->setCollisions(EntityCollisionType::OrderShipmentItemsModifyError, $shipment, implode(',', $r->getErrorMessages()));
            }
            else
            {
                //$this->setCollisions(EntityCollisionType::OrderShipmentItemsModify, $shipment);
            }
        }

        return $result;
    }

    /**
     * Decrease total product quantity existing across all shipments by the specified value.
     * Difference between the required decrease of quantity of shipped product and quantity existing in the system shipment.
     * System shipment will specify the quantity required to remove the product from the cart or update the selected shipment
     * Pass the decrease value to system shipment.
     * Thus we decrease product quantity in the shipments and add it to the system shipment.
     * We can decrease quantity for the shipments containing the product except the current shipment if it was selected and other shipments containing the product and matching selection citeria (Exchange\IShipmentCriterion implementation).
     * If we decrease quantity relative to a specific shipment, we assume the quantity relocated to the system shipment will later be added to the selected shipment.
     * @param Sale\BasketItem $basketItem
     * @param $needQuantity
     * @return Sale\Result
     * @throws Main\ObjectNotFoundException
     * @internal
     */
    public function synchronizeQuantityShipmentItems(Sale\BasketItem $basketItem, $needQuantity)
    {
        $result = new Sale\Result();

        if(intval($needQuantity) <= 0)
        {
            return $result;
        }

        $entity = $this->getEntity();

        /** @var Sale\Order $order */
        $order = $this->getParentEntity();
        $shipmentCollection = $order->getShipmentCollection();

        /** @var Sale\Shipment $entity */
        foreach ($shipmentCollection as $shipment)
        {
            /** @var Sale\Shipment $shipment */
            if(!empty($entity) && $entity->getId() == $shipment->getId())
                continue;

            if($shipment->isShipped() || $shipment->isSystem())
				continue;

            $basketQuantity = $shipment->getBasketItemQuantity($basketItem);
            if(empty($basketQuantity))
                continue;

            $shipmentItem = static::getShipmentItem($shipment, $basketItem);

            if($basketQuantity >= $needQuantity)
            {
                $this->fillShipmentItem($shipmentItem, 0, $needQuantity);
                $needQuantity = 0;
            }
            else
            {
                $this->fillShipmentItem($shipmentItem, 0, $basketQuantity);
                $needQuantity -= $basketQuantity;
            }

            $this->setCollisions(EntityCollisionType::ShipmentBasketItemsModify, $shipment);

            if($needQuantity == 0)
                break;
        }

        if($needQuantity != 0)
            $result->addError(new Error(GetMessage('SALE_EXCHANGE_ENTITY_SHIPMENT_SYNCHRONIZE_QUANTITY_ERROR'), 'SYNCHRONIZE_QUANTITY_ERROR'));

        return $result;
    }

	/**
	 * @param $fields
	 * @return array
	 */
	static public function getFieldsDeliveryService($fields)
	{
		$result = array();
		foreach($fields["ITEMS"] as $items)
		{
			foreach($items as $item)
			{
				if($item['TYPE'] == Exchange\ImportBase::ITEM_SERVICE)
				{
					$result = $item;
					break 2;
				}
			}
		}
		return $result;
	}

    /**
     * @param $fields
     * @return array
     */
    public function prepareFieldsDeliveryService($fields)
    {
        $result = array();

        $item = static::getFieldsDeliveryService($fields);
        if(count($item)>0)
		{
			$result = array(
				"CUSTOM_PRICE_DELIVERY" => "Y",
				"BASE_PRICE_DELIVERY" => $item["PRICE"],
				"CURRENCY" => $this->settings->getCurrency()
			);
		}

        return $result;
    }

    /**
     * @param array $fields
     */
    public function refreshData(array $fields)
    {
        /** @var Sale\Shipment $entity */
        $entity = $this->getEntity();
        if(!empty($entity) && $entity->isShipped())
        {
            if($fields['DEDUCTED'] == 'N')
                $entity->setField('DEDUCTED', 'N');
        }
    }

    /**
     * @param Internals\Entity $shipment
     * @return int
     * @throws Main\ArgumentException
     */
    public static function resolveEntityTypeId(Internals\Entity $shipment)
    {
        if(!($shipment instanceof Shipment))
            throw new Main\ArgumentException("Entity must be instanceof Shipment");

        return Exchange\EntityType::SHIPMENT;
    }

	public function initFields()
	{
		$this->setFields(
			array(
				'TRAITS' => $this->getFieldsTraits(),
				'ITEMS' => $this->getFieldsItems(),
				'STORIES' => $this->getFieldsStories()
			)
		);
	}

	/**
	 * @param Sale\BasketItem $basket
	 * @return array
	 */
	protected function getAttributesItem(Sale\BasketItem $basket)
	{
		return OrderImport::getAttributesItem($basket);
	}

	/**
	 * @return array
	 */
	protected function getFieldsItems()
	{
		$result = array();
		$shipment = $this->getEntity();
		if($shipment instanceof Shipment)
		{
			$order = $shipment->getParentOrder();
			/** @var Sale\BasketItem $basket */
			foreach ($order->getBasket() as $basket)
			{
				/** @var Sale\ShipmentItem $shipmentItem */
				$shipmentItem = $shipment->getShipmentItemCollection()
					->getItemByBasketCode($basket->getBasketCode());

				if($shipmentItem !== null)
				{
					$itemFields = $basket->getFieldValues();
					$itemFields['QUANTITY'] = $shipmentItem->getQuantity();

					$attributes = array();
					$attributeFields = $this->getAttributesItem($basket);
					if(count($attributeFields)>0)
						$attributes['ATTRIBUTES'] = $attributeFields;

					$result[] = array_merge($itemFields, $attributes);
				}
			}
		}
		return $result;
	}

	/**
	 * @return array
	 * @internal
	 */
	protected function getFieldsStories()
	{
		$result = array();
		$entity = $this->getEntity();
		if($entity instanceof Shipment)
		{
			$shipmentItemCollection = $entity->getShipmentItemCollection();
			if($shipmentItemCollection->count()>0)
			{
				/** @var Sale\ShipmentItem $shipmentItem */
				foreach ($shipmentItemCollection as $shipmentItem)
				{
					$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
					if ($shipmentItemStoreCollection && $shipmentItemStoreCollection->count() > 0)
					{
						/** @var Sale\ShipmentItemStore $shipmentItemStore */
						foreach ($shipmentItemStoreCollection as $shipmentItemStore)
						{
							$result[] = array('ID'=>$shipmentItemStore->getStoreId());
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @param Sale\IBusinessValueProvider $entity
	 * @return Sale\Order
	 */
	static protected function getBusinessValueOrderProvider(\Bitrix\Sale\IBusinessValueProvider $entity)
	{
		if(!($entity instanceof Shipment))
			throw new Main\ArgumentException("entity must be instanceof Shipment");

		/** @var Sale\ShipmentCollection $collection */
		$collection = $entity->getCollection();

		return $collection->getOrder();
	}
}