<?php
namespace Bitrix\Sale\Exchange\Entity;

use Bitrix\Sale;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Order;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\EntityCollisionType;
use Bitrix\Main;
use Bitrix\Sale\EntityMarker;

/**
 * Class OrderImport
 * @package Bitrix\Sale\Exchange\Entity
 * @internal
 */
class OrderImport extends EntityImport
{
    private static $FIELD_INFOS = null;

    public function __construct($parentEntityContext = null)
    {
        if($parentEntityContext instanceof Internals\Entity)
        {
            throw new Main\ArgumentException('The parentEntityContext is not supported in current context.');
        }

        parent::__construct(null);
    }

    /**
     * @return int
     */
    public function getOwnerTypeId()
    {
        return EntityType::ORDER;
    }

    /**
     * @param Internals\Entity $entity
     * @throws Main\ArgumentException
     */
    public function setEntity(Internals\Entity $entity)
    {
        if(!($entity instanceof Order))
            throw new Main\ArgumentException("Entity must be instanceof Order");

        $this->entity = $entity;
    }

    /**
     * @param array $fields
     * @return Sale\Result
     */
    protected function checkFields(array $fields)
    {
        return new Sale\Result();
    }

	/**
	 * @return Main\Entity\AddResult|Main\Entity\UpdateResult|Sale\Result|mixed
	 */
	public function save()
    {
        /** @var Order $entity */
        $entity = $this->getEntity();
        return $entity->save();
    }

    public static function getFieldsInfo()
    {
        if(!self::$FIELD_INFOS)
        {
            self::$FIELD_INFOS = array(
                "LID",
                "PERSON_TYPE_ID",
                "PAYED",
                "CANCELED",
                "STATUS_ID",
                "PRICE",
                "CURRENCY",
                "COMMENTS"
            );
        }
        return self::$FIELD_INFOS;
    }

    /**
     * @param array $params
     * @return Sale\Result
     * @throws Main\ObjectNotFoundException
     */
    public function add(array $params)
    {
        $result = new Sale\Result();

        $fields = $params['TRAITS'];
        $basketItems = $params['ITEMS'];
        $taxes = $params['TAXES'];

        $userId = $fields['USER_ID'];
        $personalTypeId = $fields['PERSON_TYPE_ID'];

        $propertyFields = '';
        if(isset($fields['ORDER_PROP']))
        {
            $propertyFields = $fields['ORDER_PROP'];
            unset($fields['ORDER_PROP']);
        }

        if(empty($personalTypeId))
        {
            $result->addError(new Main\Error('Person type is not load'));
        }

        if(empty($userId))
        {
            $result->addError(new Main\Error('User id is not load'));
        }
        if(!$result->isSuccess())
        {
            return $result;
        }

        /** @var Sale\Order $order */
        $order = Sale\Order::create($this->settings->getSiteId(), $userId, $this->settings->getCurrency());
        $order->setPersonTypeId($personalTypeId);

        $result = $this->fillProperty($order, $propertyFields);
        if(!$result->isSuccess())
        {
            return $result;
        }

        $basket = Sale\Basket::create($order->getSiteId());

        $result = $this->fillBasket($basket, $basketItems);
        if($result->isSuccess())
        {
            $order->setBasket($basket);
            $items = $result->getData();
            $this->fillTax($order, $taxes, $items['modifyTaxList']);
        }
        else
        {
            return $result;
        }

        $order->setFields($fields);

        /** @var Sale\Result $r */
        $result = $order->doFinalAction(true);
        if ($result->isSuccess())
        {
            $this->setEntity($order);
        }

        return $result;
        //static::transformationLocation($order);
    }

    /**
     * @param array $params
     * @return Sale\Result
     */
    public function update(array $params)
    {
        $result = new Sale\Result();

        $criterion = $this->getCurrentCriterion($this->getEntity());

        /** @var Sale\Order $order*/
        $order = $this->getEntity();

        $fields = $params['TRAITS'];
        $basketItems = $params['ITEMS'];
        $taxes = $params['TAXES'];

        $propertyFields = '';
        if(isset($fields['ORDER_PROP']))
        {
            $propertyFields = $fields['ORDER_PROP'];
            unset($fields['ORDER_PROP']);
        }

        if($criterion->equals($fields))
        {
            $result = $this->fillProperty($order, $propertyFields);
            if(!$result->isSuccess())
            {
                return $result;
            }

            $basket = $order->getBasket();

            $result = $this->fillBasket($basket, $basketItems);
            if($result->isSuccess())
            {
                $items = $result->getData();
                $this->fillTax($order, $taxes, $items['modifyTaxList']);
            }
            else
            {
                return $result;
            }

            $result = $order->doFinalAction(true);
            if(!$result->isSuccess())
            {
                return $result;
            }

			foreach ($fields as $k =>$field)
			{
				if(!in_array($k, $order::getSettableFields()))
					unset($fields[$k]);
            }

            $result = $order->setFields($fields);
        }

        return $result;
    }

    /**
     * @param array|null $params
     * @return Sale\Result
     */
    public function delete(array $params = null)
    {
        return new Sale\Result();
    }

    /**
     * @return string
     */
    protected function getExternalFieldName()
    {
        return 'EXTERNAL_ORDER';
    }

	/**
	 * @param array $fields
	 * @return Sale\Result
	 */
	public function load(array $fields)
    {
		$result = $this->checkFields($fields);

    	if($result->isSuccess())
		{
			if(!empty($fields['ID']))
			{
				$order = Order::load($fields['ID']);
			}

			/** @var Order $order*/
			if(!empty($order))
			{
				$this->setEntity($order);
			}
			else
			{
				$this->setExternal();
			}
		}

        return $result;
    }

    /**
     * @param $collisions
     */
    public function markedEntityCollisions($collisions)
    {
        /** @var Order $entity */
        $entity = $this->getEntity();

        foreach($collisions as $collision)
        {
			$entity->setField('MARKED', 'Y');

        	/** @var Exchange\ICollision $collision*/
            $result = new Sale\Result();
            $result->addWarning(new Sale\ResultError(EntityCollisionType::getDescription($collision->getTypeId()).($collision->getMessage() != null ? " ".$collision->getMessage():'' ), $collision->getTypeName()));

            EntityMarker::addMarker($entity, $entity, $result);
        }
    }

    private static function prepareFieldsBasketProperty($item)
    {
        $result = array();

        if(!empty($item['ATTRIBUTES']))
        {
            foreach($item['ATTRIBUTES'] as $id => $value)
            {
                $result[] = array(
                    'NAME' => $id,
                    'CODE' => $id,
                    'VALUE' => $value
                );
            }
        }
        return $result;
    }

	private function prepareFieldsBasketItem($productXML_ID, $item)
	{
    	/** @var Exchange\ISettingsImport $settings */
    	$settings = $this->getSettings();

    	$code = $this->getCodeAfterDelimiter($productXML_ID);
		$product = $code<>'' ? self::getProduct($code):array();

		if(empty($product))
			$product = self::getProduct($productXML_ID);

		if(!empty($product))
        {
            $result = array(
                "PRODUCT_ID" => $product["ID"],
                "NAME" => $product["NAME"],
                "MODULE" => "catalog",
                "PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
                "CATALOG_XML_ID" => $product["IBLOCK_XML_ID"],
                "DETAIL_PAGE_URL" => $product["DETAIL_PAGE_URL"],
                "WEIGHT" => $product["WEIGHT"],
                "NOTES" => $product["CATALOG_GROUP_NAME"]
            );
        }
        else
        {
            $ri = new Main\Type\RandomSequence($productXML_ID);
            $result = array(
                "PRODUCT_ID" => $ri->rand(1000000, 9999999),
                "NAME" => $item["NAME"],
                "MODULE" => "1c_exchange",
                "PRODUCT_PROVIDER_CLASS" => false,
                "CATALOG_XML_ID" => "1c_exchange",
                "DISCOUNT_PRICE" => $item['DISCOUNT']['PRICE'],
                "MEASURE_CODE" => $item["MEASURE_CODE"],
                "MEASURE_NAME" => $item["MEASURE_NAME"]
            );
        }

        $result["CURRENCY"] = $settings->getCurrency();
        $result["LID"] = $settings->getSiteId();
        $result["QUANTITY"] = $item["QUANTITY"];
        $result["DELAY"] = "N";
        $result["CAN_BUY"] = "Y";
        $result["IGNORE_CALLBACK_FUNC"] = "Y";
        $result["PRODUCT_XML_ID"] = $productXML_ID;

        return $result;
    }


    private function prepareFieldsTax($fields)
    {
        return array(
            array(
                "NAME" => $fields["NAME"],
                "VALUE" => $fields["VALUE"],
                "IS_PERCENT" => "Y",
                "IS_IN_PRICE" => $fields["IN_PRICE"],
                "VALUE_MONEY" => $fields["SUMM"],
                "CODE" => "VAT1C",
                "APPLY_ORDER" => "100"
            )
        );
	}

	/**
	 * @param $code
	 * @return string|null
	 */
	protected function getCodeAfterDelimiter($code)
	{
		$result = '';

		if(strpos($code, '#') !== false)
		{
			$code = explode('#', $code);
			$result = $code[1];
		}
		return $result;
	}

    private static function getProduct($code)
    {
        $result = array();

		$r = \CIBlockElement::GetList(array(),
			array("=XML_ID" => $code, "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "Y"),
			false,
			false,
			array("ID", "IBLOCK_ID", "XML_ID", "NAME", "DETAIL_PAGE_URL")
		);
		if($ar = $r->GetNext())
		{
			$result = $ar;
			$product = \CCatalogProduct::GetByID($ar["ID"]);

			$result["WEIGHT"] = $product["WEIGHT"];
			$result["CATALOG_GROUP_NAME"] = $product["CATALOG_GROUP_NAME"];

			$productIBlock = static::getIBlockProduct($ar["IBLOCK_ID"]);
			$result["IBLOCK_XML_ID"] = $productIBlock[$ar["IBLOCK_ID"]]["XML_ID"];
		}

		return $result;
	}

	/**
	 * @param Sale\Basket $basket
	 * @param array $item
	 * @return Sale\BasketItem|bool
	 */
	static public function getBasketItemByItem(Sale\Basket $basket, array $item)
    {
        foreach($basket as $basketItem)
        {
            /** @var  Sale\BasketItem $basketItem*/
            if($item['ID'] == $basketItem->getField('PRODUCT_XML_ID'))
            {
                $fieldsBasketProperty = self::prepareFieldsBasketProperty($item);

                $propertyBasketItem = array();
                /** @var Sale\BasketPropertiesCollection $basketPropertyCollection */
                if($basketPropertyCollection = $basketItem->getPropertyCollection())
                    $propertyBasketItem = $basketPropertyCollection->getPropertyValues();

                if(!empty($fieldsBasketProperty) && is_array($fieldsBasketProperty))
                {
                    if($basketPropertyCollection->isPropertyAlreadyExists($fieldsBasketProperty))
                    {
                        return $basketItem;
                    }
                }
                elseif(count($propertyBasketItem)<=0)
                {
                    return $basketItem;
                }
            }
            else
                continue;
        }
        return false;
    }

	/**
	 * @param array $fields
	 * @return array
	 */
	static public function getGroupItemsBasketFields($fields)
	{
		$result = array();

		if(is_array($fields))
		{
			foreach($fields as $k=>$items)
			{
				foreach($items as $productXML_ID => $item)
				{
					if($item['TYPE'] == Exchange\ImportBase::ITEM_ITEM)
					{
						$result[$k][$productXML_ID] = $item;
					}
				}
			}
		}

		return $result;
	}

    private function fillBasket(Sale\Basket $basket, array $basketItems)
    {
        $result = new Sale\Result();

        $taxListModify = array();
        $basketItemsIndexList = array();

        /** @var Sale\BasketItem $basketItem */
        foreach ($basket as $basketItem)
        {
            $basketItemsIndexList[$basketItem->getId()] = $basketItem->getQuantity();
        }

        $basketItems = self::getGroupItemsBasketFields($basketItems);

        if(!empty($basketItems))
		{
			$sort = 100;
			foreach($basketItems as $items)
			{
				foreach($items as $productXML_ID => $item)
				{
					$fieldsBasket = array();
					if($basketItem = self::getBasketItemByItem($basket, $item))
					{
						$criterionBasketItems = $this->getCurrentCriterion($basket->getOrder());

						if($criterionBasketItems->equalsBasketItem($basketItem, $item))
						{
							if($item['PRICE'] != $basketItem->getPrice())
								$basketItem->setPrice($item['PRICE'], true);

							if($item['QUANTITY'] != $basketItem->getQuantity())
								$fieldsBasket['QUANTITY'] = $item['QUANTITY'];

							$criterionBasketItemsTax = $this->getCurrentCriterion($basket->getOrder());

							if($criterionBasketItemsTax->equalsBasketItemTax($basketItem, $item))
							{
								$taxListModify[$basketItem->getBasketCode()] = $item['TAX'];
							}

							$criterionBasketItemsDiscount = $this->getCurrentCriterion($basket->getOrder());

							if($criterionBasketItemsDiscount->equalsBasketItemDiscount($basketItem, $item))
							{
								$fieldsBasket['DISCOUNT_PRICE'] = $item['DISCOUNT']['PRICE'];
							}
						}

						if (isset($basketItemsIndexList[$basketItem->getId()]))
							unset($basketItemsIndexList[$basketItem->getId()]);
					}
					else
					{

						$fieldsBasket = $this->prepareFieldsBasketItem($productXML_ID, $item);
						$fieldsCurrency = $this->convertCurrency($item);

						$fieldsBasket['CURRENCY'] = $fieldsCurrency['CURRENCY'];
						$fieldsBasket['SORT'] = $sort;
						$sort += 100;

						/** @var Sale\BasketItem $basketItem */
						$basketItem = Sale\BasketItem::create($basket, $fieldsBasket['MODULE'], $fieldsBasket['PRODUCT_ID']);

						$basket->addItem($basketItem);

						$basketItem->setPrice($fieldsCurrency['PRICE'], true);

						unset($fieldsBasket['MODULE'], $fieldsBasket['PRODUCT_ID']);

						$taxListModify[$basketItem->getBasketCode()] = $item['TAX'];
					}

					if(!empty($fieldsBasket))
					{
						$r = $basketItem->setFields($fieldsBasket);
						if ($r->isSuccess())
						{
							$fieldsBasketProperty = self::prepareFieldsBasketProperty($item);
							if(!empty($fieldsBasketProperty))
							{
								/** @var Sale\BasketPropertiesCollection $propertyCollection */
								if ($propertyCollection = $basketItem->getPropertyCollection())
								{
									$propertyCollection->setProperty($fieldsBasketProperty);
								}
							}
						}
						else
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}
		}

        if($result->isSuccess())
        {
            $result->setData(array('modifyTaxList'=>$taxListModify));

            $r = $this->synchronizeQuantityBasketItems($basketItemsIndexList);
            if($r->isSuccess())
            {
                if(!empty($basketItemsIndexList) && is_array($basketItemsIndexList))
                {
                    foreach ($basketItemsIndexList as $basketIndexId => $basketIndexValue)
                    {
                        /** @var Sale\BasketItem $foundedBasketItem */
                        if ($foundedBasketItem = $basket->getItemById($basketIndexId))
                        {
                            $result = $foundedBasketItem->delete();
                        }
                    }
                }
            }
            else
            {
                $result->addErrors($r->getErrors());
            }
        }

        return $result;
    }

	/**
	 * @param array $item
	 * @return array
	 */
	protected function convertCurrency(array $item)
	{
		$result = array();
		$result['CURRENCY'] = $this->settings->getCurrency();

			/** @var Order $order */
		$order = $this->getEntity();

		if($this->getEntityId()>0 && $order->getCurrency() <> $this->settings->getCurrency())
		{
			$item['PRICE'] = \CCurrencyRates::ConvertCurrency($item['PRICE'], $this->settings->getCurrency(), $order->getCurrency());
			$this->setCollisions(EntityCollisionType::OrderBasketItemsCurrencyModify, $this->getEntity());
			$result['CURRENCY'] = $order->getCurrency();
		}

		$result['PRICE'] = $item['PRICE'];

		return $result;
	}

	/**
	 * @internal
	 * @param Sale\Basket $basket
	 * @return array
	 */
	private static function getProductsVatRate(Sale\Basket $basket)
	{
		$result = array();
		static $vatFields = null;

		foreach($basket as $basketItem)
		{
			if($provider = $basketItem->getProvider())
			{
				$vatRate = 0.0;
				if($vatFields[$basketItem->getProductId()] === null)
				{
					$rsVAT = \CCatalogProduct::GetVATInfo($basketItem->getProductId());
					if ($arVAT = $rsVAT->Fetch())
						$vatFields[$basketItem->getProductId()] = $arVAT['RATE'];
				}


				if (isset($vatFields[$basketItem->getProductId()]))
					$vatRate = (float)$vatFields[$basketItem->getProductId()] * 0.01;

				$result[$basketItem->getBasketCode()] = array('VAT_RATE'=>$vatRate);
			}
			else
			{
				continue;
			}
		}
		return $result;
	}

	/**
	 * @param Order $order
	 * @param array $fields
	 * @param $modifyTaxList
	 */
	private function fillTax(Order $order, array $fields, $modifyTaxList)
    {
        if(isset($modifyTaxList))
        {
            /** @var Sale\Tax $tax */
            $tax = $order->getTax();
            $tax->resetTaxList();

            $basket = $order->getBasket();
            $productVatData = self::getProductsVatRate($basket);

            /** @var Sale\BasketItem $basketItem */
            foreach($basket as $basketItem)
            {
                $code = $basketItem->getBasketCode();
                if(isset($modifyTaxList[$code]))
                {
                    if($basketItem->getId()>0)
                    {
                        $this->setCollisions(EntityCollisionType::OrderBasketItemTaxValueError, $this->getEntity(), $basketItem->getField('NAME'));
                    }
                    else
                    {
                        $productVatFields = $productVatData[$basketItem->getBasketCode()];
                        if(!empty($productVatFields))
                        {
                            if($productVatFields['VAT_RATE'] <> $modifyTaxList[$code]['VAT_RATE'])
                            {
                                $this->setCollisions(EntityCollisionType::OrderBasketItemTaxValueError, $order, $basketItem->getField('NAME'));
                            }
                        }
                    }

                    $basketItem->setField('VAT_RATE', $modifyTaxList[$code]['VAT_RATE']);
                    $basketItem->setField('VAT_INCLUDED', $modifyTaxList[$code]['VAT_INCLUDED']);
                }
            }

            $tax->initTaxList($this->prepareFieldsTax($fields));
            $order->refreshVat();
        }
     }

    private function fillProperty(Order $order, $fieldsOrderProperty)
    {
        $result = new Sale\Result();

        /** @var Sale\PropertyValueCollection $propCollection */
        $propCollection = $order->getPropertyCollection();

        if (!empty($fieldsOrderProperty) && is_array($fieldsOrderProperty))
        {
            $fields['PROPERTIES'] = $fieldsOrderProperty;

            /** @var Sale\Result $r */
            $r = $propCollection->setValuesFromPost($fields, $_FILES);
            if (!$r->isSuccess())
            {
                $result->addErrors($r->getErrors());
                return $result;
            }
        }
        return $result;
    }

    public function refreshData(array $fields)
    {
    }

    /**
     * @param Internals\Entity $order
     * @return int
     * @throws Main\ArgumentException
     */
    public static function resolveEntityTypeId(Internals\Entity $order)
    {
        if(!($order instanceof Order))
            throw new Main\ArgumentException("Entity must be instanceof Order");

        return EntityType::ORDER;
    }

    private static function getIBlockProduct($iblockId)
    {
        static $iblock_fields = null;

        if($iblock_fields[$iblockId] == null)
        {
            $r = \CIBlock::GetList(array(), array("ID" => $iblockId));
            if ($ar = $r->Fetch())
                $iblock_fields[$iblockId] = $ar;
        }
        return $iblock_fields;
    }

    /**
     * Calculate the difference between externally submitted item quantity in the cart and the current quantity
     * @param Sale\Basket $basket
     * @param array $basketItems
     * @return array
     */
    public static function calculateDeltaQuantity(Sale\Basket $basket, array $basketItems)
    {
        $basketItemsIndexQuantityList = array();

        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($basket as $basketItem)
        {
            $basketItemsIndexQuantityList[$basketItem->getId()] = $basketItem->getQuantity();
        }

        if(!empty($basketItems) && is_array($basketItems))
        {
            foreach($basketItems as $items)
            {
                foreach($items as $productXML_ID => $item)
                {
                    if($basketItem = Exchange\Entity\OrderImport::getBasketItemByItem($basket, $item))
                    {
                        if(isset($basketItemsIndexQuantityList[$basketItem->getId()]))
                        {
                            if($basketItemsIndexQuantityList[$basketItem->getId()] <= $item['QUANTITY'])
                            {
                                unset($basketItemsIndexQuantityList[$basketItem->getId()]);
                            }
                            else
                            {
                                $basketItemsIndexQuantityList[$basketItem->getId()] -= $item['QUANTITY'];
                            }
                        }
                    }
                }
            }
        }

        return $basketItemsIndexQuantityList;
    }

    /**
     * Decrease item quantity by calculated value for all shipments.
     * Do it for all shipments matching selection parameters and containing the product (Exchange\IShipmentCriterion implementation).
     * @param array $basketItemsIndex
     * @return Sale\Result
     * @throws Main\ArgumentNullException
     */
    public function synchronizeQuantityBasketItems(array $basketItemsIndex)
    {
        $result = new Sale\Result();

        /** @var Order $order */
        $order = $this->getEntity();
        if(empty($order))
            return $result;

        $basket = $order->getBasket();

        $shipmentCollection = $order->getShipmentCollection();
        /** @var \Bitrix\Sale\Shipment $systemShipment */
        $systemShipment = $shipmentCollection->getSystemShipment();

        if(!empty($basketItemsIndex) && is_array($basketItemsIndex))
        {
            foreach ($basketItemsIndex as $basketIndexId => $basketIndexQuantity)
            {
                /** @var \Bitrix\Sale\BasketItem $foundedBasketItem */
                if ($foundedBasketItem = $basket->getItemById($basketIndexId))
                {
                    $systemBasketQuantity = $systemShipment->getBasketItemQuantity($foundedBasketItem);

                    if($basketIndexQuantity>$systemBasketQuantity)
                    {
                        $needQuantity = $basketIndexQuantity-$systemBasketQuantity;

                        /** @var ShipmentImport $shipmentImport */
                        $shipmentImport = Exchange\ManagerImport::create(EntityType::SHIPMENT);
                        $shipmentImport->setParentEntity($order);

                        $r = $shipmentImport->synchronizeQuantityShipmentItems($foundedBasketItem, $needQuantity);
                        if($r->isSuccess())
                        {
                            $this->setCollisions(EntityCollisionType::OrderSynchronizeBasketItemsModify, $order);
                        }
                        else
                        {
							$this->setCollisions(EntityCollisionType::OrderSynchronizeBasketItemsModifyError, $order, implode(',', $result->getErrorMessages()));
                        	$result->addErrors($r->getErrors());
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function initFields()
	{
		$this->setFields(
			array(
			'TRAITS'=>$this->getFieldsTraits(),
			'ITEMS'=>$this->getFieldsItems(),
			'TAXES'=>$this->getFieldsTaxes(),
			'CASH_BOX_CHECKS'=>$this->getCashBoxChecks()
			)
		);
	}

	/**
	 * @return array
	 * @internal
	 */
	protected function getFieldsItems()
	{
		$result = array();
		$order = $this->getEntity();
		if($order instanceof Order)
		{
			/** @var Sale\BasketItem[] $basketItems */
			$basketItems = $order->getBasket();
			foreach ($basketItems as $basket)
			{
				$attributes = array();
				$attributeFields = static::getAttributesItem($basket);
				if(count($attributeFields)>0)
					$attributes['ATTRIBUTES'] = $attributeFields;

				$result[] = array_merge($basket->getFieldValues(), $attributes);
			}
		}

		return $result;
	}

	/**
	 * @param Sale\BasketItem $basket
	 * @return array
	 */
	static public function getAttributesItem(Sale\BasketItem $basket)
	{
		$result = array();
		/** @var Sale\BasketPropertyItemBase[] $propertyItems */
		$propertyItems = $basket->getPropertyCollection();
		foreach ($propertyItems as $property)
		{
			$result[] = $property->getFieldValues();
		}
		return $result;
	}

	/**
	 * @return array
	 * @internal
	 */
	protected function getFieldsTaxes()
	{
		$result = array();
		$order = $this->getEntity();
		if($order instanceof Order)
		{
			$res = \CSaleOrderTax::GetList(
				array(),
				array("ORDER_ID" => $order->getId()),
				false,
				false,
				array("ID", "TAX_NAME", "VALUE", "VALUE_MONEY", "CODE", "IS_IN_PRICE")
			);
			while ($tax = $res->Fetch())
			{
				$result[] = $tax;
			}
		}
		return $result;
	}

	/**
	 * @return array
	 */
	protected function getCashBoxChecks()
	{
		$result = array();
		$cashBoxOneCId = \Bitrix\Sale\Cashbox\Cashbox1C::getId();
		$order = $this->getEntity();
		if($order instanceof Order)
		{
			if($cashBoxOneCId>0)
			{
				$result = \Bitrix\Sale\Cashbox\CheckManager::getPrintableChecks(array($cashBoxOneCId), array($order->getId()));
			}
		}
		return $result;
	}

	/**
	 * @return array
	 * @internal
	 */
	protected function getFieldsProperty()
	{
		//ORDER_PROPS
	}

	/**
	 * @param Sale\IBusinessValueProvider $entity
	 * @return Order
	 */
	static protected function getBusinessValueOrderProvider(Sale\IBusinessValueProvider $entity)
	{
		if(!($entity instanceof Order))
			throw new Main\ArgumentException("entity must be instanceof Order");

		return $entity;
	}
}