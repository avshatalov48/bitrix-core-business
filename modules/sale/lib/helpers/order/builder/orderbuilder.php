<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\PropertyValueCollection;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Shipment;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Type\Date;
use Bitrix\Sale\Registry;
use \Bitrix\Sale\Delivery;
use Bitrix\Sale\Result;
use Bitrix\Sale\Configuration;
use Bitrix\Sale\ShipmentItem;
use Bitrix\Sale\TradeBindingEntity;
use Bitrix\Sale\TradingPlatformTable;

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/sale/lib/helpers/admin/blocks/orderbasketshipment.php');

/**
 * Class OrderBuilder
 * @package Bitrix\Sale\Helpers\Order\Builder
 * @internal
 */
abstract class OrderBuilder
{
	/** @var OrderBuilderExist|OrderBuilderNew */
	protected $delegate = null;
	/** @var BasketBuilder  */
	protected $basketBuilder = null;

	/** @var SettingsContainer */
	protected $settingsContainer = null;

	/** @var Order  */
	protected $order = null;
	/** @var array */
	protected $formData = array();
	/** @var ErrorsContainer  */
	protected $errorsContainer = null;

	/** @var bool */
	protected $isStartField;

	/** @var Registry */
	protected $registry = null;

	public function __construct(SettingsContainer $settings)
	{
		$this->settingsContainer = $settings;
		$this->errorsContainer = new ErrorsContainer();
		$this->errorsContainer->setAcceptableErrorCodes(
			$this->settingsContainer->getItemValue('acceptableErrorCodes')
		);

		$this->registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
	}

	/**
	 * @param $data
	 * @throws BuildingException
	 */
	public function build($data)
	{
		$this->initFields($data)
			->delegate()
			->createOrder()
			->setDiscounts() //?
			->setFields()
			->buildTradeBindings()
			->setProperties()
			->setUser()
			->buildProfile()
			->buildBasket()
			->buildPayments()
			->buildShipments()
			->setRelatedProperties()
			->setDiscounts() //?
			->finalActions();
	}

	public function setBasketBuilder(BasketBuilder $basketBuilder)
	{
		$this->basketBuilder = $basketBuilder;
	}

	public function getRegistry()
	{
		return $this->registry;
	}

	protected function prepareFields(array $fields)
	{
		$fields["ID"] = (isset($fields["ID"]) ? (int)$fields["ID"] : 0);
		return $fields;
	}

	public function initFields(array $data)
	{
		$data = $this->prepareFields($data);
		$this->formData = $data;
		return $this;
	}

	/**
	 * @param array $data
	 * @return $this
	 * @deprecated
	 */
	public function initOrder(array $data)
	{
		$data["ID"] = (isset($data["ID"]) ? (int)$data["ID"] : 0);
		$this->formData = $data;
		return $this;
	}

	public function delegate()
	{
		$data = $this->formData;
		$this->delegate = (int)$data['ID'] > 0 ? new OrderBuilderExist($this) : new OrderBuilderNew($this);

		return $this;
	}

	public function createOrder()
	{
		$data = $this->formData;
		if($this->order = $this->delegate->createOrder($data))
		{
			$this->isStartField = $this->order->isStartField();
		}

		return $this;
	}

	protected function getSettableTradeBindingFields()
	{
		return [];
	}

	protected function getSettableShipmentFields()
	{
		$shipmentClassName = $this->registry->getShipmentClassName();
		return array_merge(['PROPERTIES'], $shipmentClassName::getAvailableFields());
	}

	protected function getSettablePaymentFields()
	{
		$paymentClassName = $this->registry->getPaymentClassName();
		return $paymentClassName::getAvailableFields();
	}

	protected function getSettableOrderFields()
	{
		return ['RESPONSIBLE_ID', 'USER_DESCRIPTION', 'ORDER_TOPIC', 'ACCOUNT_NUMBER'];
	}

	public function setFields()
	{
		$fields = $this->getSettableOrderFields();

		foreach($fields as $field)
		{
			if(isset($this->formData[$field]))
			{
				$r = $this->order->setField($field, $this->formData[$field]);

				if(!$r->isSuccess())
				{
					$this->getErrorsContainer()->addErrors($r->getErrors());
				}
			}
		}

		if(isset($this->formData["PERSON_TYPE_ID"]) && intval($this->formData["PERSON_TYPE_ID"]) > 0)
		{
			/** @var \Bitrix\Sale\Result $r */
			$r = $this->order->setPersonTypeId(intval($this->formData['PERSON_TYPE_ID']));
		}
		else
		{
			/** @var \Bitrix\Sale\Result $r */
			$r = $this->order->setPersonTypeId(
				OrderBuyer::getDefaultPersonType(
					$this->order->getSiteId()
				)
			);
		}

		if(!$r->isSuccess())
		{
			$this->getErrorsContainer()->addErrors($r->getErrors());
		}

		return $this;
	}

	public function setProperties()
	{
		if(!$this->formData["PROPERTIES"])
		{
			return $this;
		}

		$propCollection = $this->order->getPropertyCollection();
		$res = $propCollection->setValuesFromPost(
			$this->formData,
			$this->settingsContainer->getItemValue('propsFiles')
		);

		if (!$res->isSuccess())
		{
			$this->getErrorsContainer()->addErrors($res->getErrors());
		}

		return $this;
	}

	public function setRelatedProperties()
	{
		if (!$this->formData["PROPERTIES"])
		{
			return $this;
		}

		$propCollection = $this->order->getPropertyCollection();

		/** @var PropertyValue $propertyValue */
		foreach ($propCollection as $propertyValue)
		{
			if (!$propertyValue->getRelations())
			{
				continue;
			}

			$post = Internals\Input\File::getPostWithFiles($this->formData, $this->settingsContainer->getItemValue('propsFiles'));

			$res = $propertyValue->setValueFromPost($post);
			if (!$res->isSuccess())
			{
				$this->getErrorsContainer()->addErrors($res->getErrors());
			}
		}

		return $this;
	}

	public function setUser()
	{
		$this->delegate->setUser();
		return $this;
	}

	public function buildProfile()
	{
		if (empty($this->formData["PROPERTIES"]) || empty($this->formData["USER_ID"]))
		{
			return $this;
		}

		$profileId = $this->formData["USER_PROFILE"]["ID"] ?? 0;
		$profileName = $this->formData["USER_PROFILE"]["NAME"] ?? '';

		$errors = [];
		\CSaleOrderUserProps::DoSaveUserProfile(
			$this->getUserId(),
			$profileId,
			$profileName,
			$this->order->getPersonTypeId(),
			$this->formData["PROPERTIES"],
			$errors
		);

		foreach ($errors as $error)
		{
			$this->errorsContainer->addError(new Main\Error($error['TEXT'], $error['CODE'], 'PROFILE'));
		}

		return $this;
	}

	public function setDiscounts()
	{
		if(isset($this->formData["DISCOUNTS"]) && is_array($this->formData["DISCOUNTS"]))
		{
			$this->order->getDiscount()->setApplyResult($this->formData["DISCOUNTS"]);

			$r = $this->order->getDiscount()->calculate();

			if($r->isSuccess())
			{
				$discountData = $r->getData();
				$this->order->applyDiscount($discountData);
			}
		}

		return $this;
	}

	public function buildBasket()
	{
		$this->delegate->buildBasket();
		return $this;
	}

	protected function createEmptyShipment()
	{
		$shipments = $this->order->getShipmentCollection();
		return $shipments->createItem();
	}

	protected function removeShipments()
	{
		if($this->getSettingsContainer()->getItemValue('deleteShipmentIfNotExists'))
		{
			$shipmentCollection = $this->order->getShipmentCollection();

			$shipmentIds = [];
			foreach($this->formData["SHIPMENT"] as $shipmentData)
			{
				if(!isset($shipmentData['ID']))
					continue;

				$shipment = $shipmentCollection->getItemById($shipmentData['ID']);

				if ($shipment == null)
					continue;

				$shipmentIds[] = $shipment->getId();
			}

			foreach ($shipmentCollection as $shipment)
			{
				if($shipment->isSystem())
					continue;

				if(!in_array($shipment->getId(), $shipmentIds))
				{
					$r = $shipment->delete();
					if (!$r->isSuccess())
					{
						$this->errorsContainer->addErrors($r->getErrors());
						return false;
					}
				}
			}
		}
		return true;
	}

	protected function prepareFieldsStatusId($isNew, $item, $defaultFields)
	{
		$statusId = '';

		if($isNew)
		{
			$deliveryStatusClassName = $this->registry->getDeliveryStatusClassName();
			$statusId = $deliveryStatusClassName::getInitialStatus();
		}
		elseif (isset($item['STATUS_ID']) && $item['STATUS_ID'] !== $defaultFields['STATUS_ID'])
		{
			$statusId = $item['STATUS_ID'];
		}

		return $statusId;
	}

	public function buildShipments()
	{
		$isEmptyShipmentData = empty($this->formData["SHIPMENT"]) || !is_array($this->formData["SHIPMENT"]);
		if ($isEmptyShipmentData)
		{
			$this->formData["SHIPMENT"] = [];
		}

		if ($isEmptyShipmentData && !$this->getSettingsContainer()->getItemValue('createDefaultShipmentIfNeed'))
		{
			return $this;
		}

		if($isEmptyShipmentData && $this->getOrder()->isNew())
		{
			$this->createEmptyShipment();
			return $this;
		}

		if(!$this->removeShipments())
		{
			throw new BuildingException();
		}

		global $USER;
		$basketResult = null;
		$shipmentCollection = $this->order->getShipmentCollection();

		foreach($this->formData["SHIPMENT"] as $item)
		{
			$shipmentId = intval($item['ID']);
			$isNew = ($shipmentId <= 0);
			$deliveryService = null;

			if (!isset($item['DEDUCTED']) || $item['DEDUCTED'] !== 'Y')
			{
				$item['DEDUCTED'] = 'N';
			}

			$extraServices = ($item['EXTRA_SERVICES']) ? $item['EXTRA_SERVICES'] : array();

			$settableShipmentFields = $this->getSettableShipmentFields();
			if(count($settableShipmentFields)>0)
			{
				//for backward compatibility
				$product = $item['PRODUCT'];
				$storeId = $item['DELIVERY_STORE_ID'];
				$item = array_intersect_key($item, array_flip($settableShipmentFields));
				$item['PRODUCT'] = $product;
			}

			if($isNew)
			{
				$shipment = $shipmentCollection->createItem();
			}
			else
			{
				$shipment = $shipmentCollection->getItemById($shipmentId);

				if(!$shipment)
				{
					$this->errorsContainer->addError(new Error(Loc::getMessage("SALE_HLP_ORDERBUILDER_SHIPMENT_NOT_FOUND")." - ".$shipmentId));
					continue;
				}
			}

			$defaultFields = $shipment->getFieldValues();

			/** @var \Bitrix\Sale\BasketItem $product */
			$systemShipment = $shipmentCollection->getSystemShipment();
			$systemShipmentItemCollection = $systemShipment->getShipmentItemCollection();
			//We suggest that if  products is null - ShipmentBasket not loaded yet, if array ShipmentBasket loaded, but empty.
			$products = null;

			if(
				!isset($item['PRODUCT'])
				&& $shipment->getId() <= 0
			)
			{
				$products = array();
				$basket = $this->order->getBasket();

				if($basket)
				{
					$basketItems = $basket->getBasketItems();
					foreach($basketItems as $product)
					{
						$systemShipmentItem = $systemShipmentItemCollection->getItemByBasketCode($product->getBasketCode());

						if($product->isBundleChild() || !$systemShipmentItem || $systemShipmentItem->getQuantity() <= 0)
							continue;

						$products[] = array(
							'AMOUNT' => $systemShipmentItem->getQuantity(),
							'BASKET_CODE' => $product->getBasketCode(),
						);
					}
				}
			}
			elseif(is_array($item['PRODUCT']))
			{
				$products = $item['PRODUCT'];
			}

			if($item['DEDUCTED'] == 'Y' && $products !== null)
			{
				$basketResult = $this->buildShipmentBasket($shipment, $products);

				if(!$basketResult->isSuccess())
				{
					$this->errorsContainer->addErrors($basketResult->getErrors());
				}
			}

			$shipmentFields = array(
				'COMPANY_ID' => (isset($item['COMPANY_ID']) && intval($item['COMPANY_ID']) > 0) ? intval($item['COMPANY_ID']) : 0,
				'DEDUCTED' => $item['DEDUCTED'],
				'DELIVERY_DOC_NUM' => $item['DELIVERY_DOC_NUM'],
				'TRACKING_NUMBER' => $item['TRACKING_NUMBER'],
				'CURRENCY' => $this->order->getCurrency(),
				'COMMENTS' => $item['COMMENTS'],
			);

			if (!empty($item['IS_REALIZATION']))
			{
				$shipmentFields['IS_REALIZATION'] = $item['IS_REALIZATION'];
			}

			if(isset($item['ACCOUNT_NUMBER']) && $item['ACCOUNT_NUMBER']<>'')
				$shipmentFields['ACCOUNT_NUMBER'] = $item['ACCOUNT_NUMBER'];

			if(isset($item['XML_ID']) && $item['XML_ID']<>'')
				$shipmentFields['XML_ID'] = $item['XML_ID'];

			$statusId = $this->prepareFieldsStatusId($isNew, $item, $defaultFields);
			if($statusId <> '')
				$shipmentFields['STATUS_ID'] = $statusId;

			if(empty($item['COMPANY_ID']))
			{
				$shipmentFields['COMPANY_ID'] = $this->order->getField('COMPANY_ID');
			}
			if(empty($item['RESPONSIBLE_ID']))
			{
				$shipmentFields['RESPONSIBLE_ID'] = $this->order->getField('RESPONSIBLE_ID');
				$shipmentFields['EMP_RESPONSIBLE_ID'] = $USER->GetID();
			}

			$shipmentFields['DELIVERY_ID'] = ((int)$item['PROFILE_ID'] > 0) ? (int)$item['PROFILE_ID'] : (int)$item['DELIVERY_ID'];

			$dateFields = ['DELIVERY_DOC_DATE', 'DATE_DEDUCTED', 'DATE_MARKED', 'DATE_CANCELED', 'DATE_RESPONSIBLE_ID'];

			foreach($dateFields as $fieldName)
			{
				if(isset($item[$fieldName]))
				{
					if (is_string($item[$fieldName]))
					{
						try
						{
							$shipmentFields[$fieldName] = new Date($item[$fieldName]);
						}
						catch (ObjectException $exception)
						{
							$this->errorsContainer->addError(new Error('Wrong field "'.$fieldName.'"'));
						}
					}
					elseif ($item[$fieldName] instanceof Date)
					{
						$shipmentFields[$fieldName] = $item[$fieldName];
					}
				}
			}

			try
			{
				if($deliveryService = Delivery\Services\Manager::getObjectById($shipmentFields['DELIVERY_ID']))
				{
					if($deliveryService->isProfile())
					{
						$shipmentFields['DELIVERY_NAME'] = $deliveryService->getNameWithParent();
					}
					else
					{
						$shipmentFields['DELIVERY_NAME'] = $deliveryService->getName();
					}
				}
			}
			catch (ArgumentNullException $e)
			{
				$this->errorsContainer->addError(new Error(Loc::getMessage('SALE_HLP_ORDERBUILDER_DELIVERY_NOT_FOUND'), 'OB_DELIVERY_NOT_FOUND'));
				return $this;
			}

			$responsibleId = $shipment->getField('RESPONSIBLE_ID');

			if($item['RESPONSIBLE_ID'] != $responsibleId || empty($responsibleId))
			{
				if(isset($item['RESPONSIBLE_ID']))
				{
					$shipmentFields['RESPONSIBLE_ID'] = $item['RESPONSIBLE_ID'];
				}
				else
				{
					$shipmentFields['RESPONSIBLE_ID'] = $this->order->getField('RESPONSIBLE_ID');
				}

				if(!empty($shipmentFields['RESPONSIBLE_ID']))
				{
					$shipmentFields['EMP_RESPONSIBLE_ID'] = $USER->getID();
				}
			}

			if($extraServices)
			{
				$shipment->setExtraServices($extraServices);
			}

			$setFieldsResult = $shipment->setFields($shipmentFields);

			if(!$setFieldsResult->isSuccess())
			{
				$this->errorsContainer->addErrors($setFieldsResult->getErrors());
			}

			// region Properties
			if (isset($item['PROPERTIES']) && is_array($item['PROPERTIES']))
			{
				/** @var PropertyValueCollection $propCollection */
				$propCollection = $shipment->getPropertyCollection();
				$res = $propCollection->setValuesFromPost($item, []);

				if (!$res->isSuccess())
				{
					foreach ($res->getErrors() as $error)
					{
						$this->getErrorsContainer()->addError(
							new Main\Error($error->getMessage(), $error->getCode(), 'SHIPMENT_PROPERTIES')
						);
					}
				}

				/** @var \Bitrix\Sale\PropertyValue $propValue */
				foreach ($propCollection as $propValue)
				{
					if ($propValue->isUtil())
					{
						continue;
					}

					$property = $propValue->getProperty();
					$relatedDeliveryIds = (isset($property['RELATION']) && is_array($property['RELATION']))
						? array_column(
							array_filter($property['RELATION'], function ($item) {
								return $item['ENTITY_TYPE'] === 'D';
							}),
							'ENTITY_ID'
						)
						: [];

					if (
						!empty($relatedDeliveryIds)
						&& !in_array($shipment->getField('DELIVERY_ID'), $relatedDeliveryIds)
					)
					{
						continue;
					}

					$res = $propValue->verify();
					if (!$res->isSuccess())
					{
						foreach ($res->getErrors() as $error)
						{
							$this->getErrorsContainer()->addError(
								new Main\Error($error->getMessage(), $propValue->getPropertyId(), 'SHIPMENT_PROPERTIES')
							);
						}
					}

					$res = $propValue->checkRequiredValue($propValue->getPropertyId(), $propValue->getValue());
					if (!$res->isSuccess())
					{
						foreach ($res->getErrors() as $error)
						{
							$this->getErrorsContainer()->addError(
								new Main\Error($error->getMessage(), $propValue->getPropertyId(), 'SHIPMENT_PROPERTIES')
							);
						}
					}
				}
			}
			// endregion

			$shipment->setStoreId((int)$storeId);

			if($item['DEDUCTED'] == 'N' && $products !== null)
			{
				$basketResult = $this->buildShipmentBasket($shipment, $products);

				if(!$basketResult->isSuccess())
				{
					$this->errorsContainer->addErrors($basketResult->getErrors());
				}
			}

			$fields = array(
				'CUSTOM_PRICE_DELIVERY' => $item['CUSTOM_PRICE_DELIVERY'] === 'Y' ? 'Y' : 'N',
				'ALLOW_DELIVERY' => $item['ALLOW_DELIVERY'],
				'PRICE_DELIVERY' => (float)str_replace(',', '.', $item['PRICE_DELIVERY']),
			);

			if(isset($item['BASE_PRICE_DELIVERY']))
			{
				$fields['BASE_PRICE_DELIVERY'] = (float)str_replace(',', '.', $item['BASE_PRICE_DELIVERY']);
			}

			$shipment = $this->delegate->setShipmentPriceFields($shipment, $fields);

			if($deliveryService && !empty($item['ADDITIONAL']))
			{
				$modifiedShipment = $deliveryService->processAdditionalInfoShipmentEdit($shipment, $item['ADDITIONAL']);

				$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
				if ($modifiedShipment && get_class($modifiedShipment) == $registry->getShipmentClassName())
				{
					$shipment = $modifiedShipment;
				}
			}
		}

		return $this;
	}

	protected function removeShipmentItems(\Bitrix\Sale\Shipment $shipment, $products, $idsFromForm)
	{
		$result = new Result();

		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		/** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			if (!array_key_exists($shipmentItem->getBasketCode(), $idsFromForm))
			{
				/** @var Result $r */
				$r = $shipmentItem->delete();
				if (!$r->isSuccess())
					$result->addErrors($r->getErrors());
			}

			$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();

			/** @var \Bitrix\Sale\ShipmentItemStore $shipmentItemStore */
			foreach ($shipmentItemStoreCollection as $shipmentItemStore)
			{
				$shipmentItemId = $shipmentItemStore->getId();
				if (!isset($idsFromForm[$shipmentItem->getBasketCode()]['BARCODE_IDS'][$shipmentItemId]))
				{
					$delResult = $shipmentItemStore->delete();
					if (!$delResult->isSuccess())
						$result->addErrors($delResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @param array $shipmentBasket
	 * @return Result
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotSupportedException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	public function buildShipmentBasket(&$shipment, $shipmentBasket)
	{
		/**@var \Bitrix\Sale\Shipment $shipment */
		$result = new Result();
		$shippingItems = array();
		$idsFromForm = array();
		$basket = $this->order->getBasket();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		$useStoreControl = Configuration::useStoreControl();

		if(is_array($shipmentBasket))
		{
			// PREPARE DATA FOR SET_FIELDS
			foreach ($shipmentBasket as $items)
			{
				$items['QUANTITY'] = floatval(str_replace(',', '.', $items['QUANTITY']));
				$items['AMOUNT'] = floatval(str_replace(',', '.', $items['AMOUNT']));

				$r = $this->prepareDataForSetFields($shipment, $items);
				if($r->isSuccess())
				{
					$items = $r->getData()[0];
				}
				else
				{
					$result->addErrors($r->getErrors());
					return $result;
				}

				if (isset($items['BASKET_ID']) && $items['BASKET_ID'] > 0)
				{
					if (!$basketItem = $basket->getItemById($items['BASKET_ID']))
					{
						$result->addError( new Error(
								Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_BASKET_ITEM_NOT_FOUND',  array(
									'#BASKET_ITEM_ID#' => $items['BASKET_ID'],
								)),
								'PROVIDER_UNRESERVED_SHIPMENT_ITEM_WRONG_BASKET_ITEM')
						);
						return $result;
					}
					/** @var \Bitrix\Sale\BasketItem $basketItem */
					$basketCode = $basketItem->getBasketCode();
				}
				else
				{
					$basketCode = $items['BASKET_CODE'];
					if(!$basketItem = $basket->getItemByBasketCode($basketCode))
					{
						$result->addError( new Error(
								Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_BASKET_ITEM_NOT_FOUND',  array(
									'#BASKET_ITEM_ID#' => $items['BASKET_ID'],
								)),
								'PROVIDER_UNRESERVED_SHIPMENT_ITEM_WRONG_BASKET_ITEM')
						);
						return $result;
					}
				}

				$tmp = array(
					'BASKET_CODE' => $basketCode,
					'AMOUNT' => $items['AMOUNT'],
					'ORDER_DELIVERY_BASKET_ID' => isset($items['ORDER_DELIVERY_BASKET_ID']) ? $items['ORDER_DELIVERY_BASKET_ID'] : 0,
					'XML_ID' => $items['XML_ID'],
					'IS_SUPPORTED_MARKING_CODE' => $items['IS_SUPPORTED_MARKING_CODE'],
				);
				$idsFromForm[$basketCode] = array();

				if ($items['BARCODE_INFO'] && ($useStoreControl || $items['IS_SUPPORTED_MARKING_CODE'] == 'Y'))
				{
					foreach ($items['BARCODE_INFO'] as $item)
					{
						if ($basketItem->isBundleParent())
						{
							$shippingItems[] = $tmp;
							continue;
						}

						$barcodeQuantity = ($basketItem->isBarcodeMulti() || $basketItem->isSupportedMarkingCode()) ? 1 : $item['QUANTITY'];
						$barcodeStoreId = $item['STORE_ID'];

						$tmp['BARCODE'] = array(
							'ORDER_DELIVERY_BASKET_ID' => $items['ORDER_DELIVERY_BASKET_ID'],
							'STORE_ID' => $barcodeStoreId,
							'QUANTITY' => $barcodeQuantity,
						);

						$tmp['BARCODE_INFO'] = [
							$item['STORE_ID'] => [
								'STORE_ID' => (int)$barcodeStoreId,
								'QUANTITY' => (float)$barcodeQuantity,
							],
						];

						$barcodeCount = 0;
						if ($item['BARCODE'])
						{
							foreach ($item['BARCODE'] as $barcode)
							{
								$barcode['ID'] = (int)$barcode['ID'];

								$tmp['BARCODE_INFO'][$barcodeStoreId]['BARCODE'] = [$barcode];

								if (isset($barcode['MARKING_CODE']))
								{
									$barcode['MARKING_CODE'] = (string)$barcode['MARKING_CODE'];
								}
								else
								{
									$barcode['MARKING_CODE'] = '';
								}

								$idsFromForm[$basketCode]['BARCODE_IDS'][$barcode['ID']] = true;

								if ($barcode['ID'] > 0)
								{
									$tmp['BARCODE']['ID'] = $barcode['ID'];
								}
								else
								{
									unset($tmp['BARCODE']['ID']);
								}

								$tmp['BARCODE']['BARCODE'] = (string)$barcode['VALUE'];
								$tmp['BARCODE']['MARKING_CODE'] = $barcode['MARKING_CODE'];

								$shippingItems[] = $tmp;
								$barcodeCount++;
							}
						}
						elseif (!$basketItem->isBarcodeMulti() && !$basketItem->isSupportedMarkingCode())
						{
							$shippingItems[] = $tmp;
							continue;
						}


						if ($basketItem->isBarcodeMulti() || $basketItem->isSupportedMarkingCode())
						{
							while ($barcodeCount < $item['QUANTITY'])
							{
								unset($tmp['BARCODE']['ID']);
								$tmp['BARCODE']['BARCODE'] = '';
								$tmp['BARCODE']['MARKING_CODE'] = '';
								$shippingItems[] = $tmp;
								$barcodeCount++;
							}
						}
					}
				}
				else
				{
					$shippingItems[] = $tmp;
				}
			}
		}

		// DELETE FROM COLLECTION
		$r = $this->removeShipmentItems($shipment, $shipmentBasket, $idsFromForm);
		if(!$r->isSuccess())
			$result->addErrors($r->getErrors());

		$isStartField = $shipmentItemCollection->isStartField();

		// SET DATA
		foreach ($shippingItems as $shippingItem)
		{
			if ((int)$shippingItem['ORDER_DELIVERY_BASKET_ID'] <= 0)
			{
				$basketCode = $shippingItem['BASKET_CODE'];
				/** @var \Bitrix\Sale\Order $this->order */
				$basketItem = $this->order->getBasket()->getItemByBasketCode($basketCode);

				/** @var \Bitrix\Sale\BasketItem $basketItem */
				$shipmentItem = $shipmentItemCollection->createItem($basketItem);

				if ($shipmentItem === null)
				{
					$result->addError(
						new Error(
							Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED')
						)
					);
					return $result;
				}

				unset($shippingItem['BARCODE']['ORDER_DELIVERY_BASKET_ID']);
			}
			else
			{
				$shipmentItem = $shipmentItemCollection->getItemById($shippingItem['ORDER_DELIVERY_BASKET_ID']);

				if($shipmentItem)
				{
					$basketItem = $shipmentItem->getBasketItem();
				}
				else //It's a possible case when we are creating new shipment.
				{
					/** @var \Bitrix\Crm\Order\Shipment $systemShipment */
					$systemShipment = $shipment->getCollection()->getSystemShipment();
					/** @var \Bitrix\Crm\Order\ShipmentItemCollection $systemShipmentItemCollection */
					$systemShipmentItemCollection = $systemShipment->getShipmentItemCollection();

					$shipmentItem = $systemShipmentItemCollection->getItemById($shippingItem['ORDER_DELIVERY_BASKET_ID']);

					if($shipmentItem)
					{
						$basketItem = $shipmentItem->getBasketItem();
						$shipmentItem = $shipmentItemCollection->createItem($basketItem);
						$shipmentItem->setField('QUANTITY', $shipmentItem->getField('QUANTITY'));
					}
					else
					{
						$result->addError(
							new Error(
								Loc::getMessage('SALE_HLP_ORDERBUILDER_SHIPMENT_ITEM_ERROR',[
									'#ID#' => $shippingItem['ORDER_DELIVERY_BASKET_ID'],
								])
							)
						);

						continue;
					}
				}
			}

			if ($shippingItem['AMOUNT'] <= 0)
			{
				$result->addError(
					new Error(
						Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_ERROR_QUANTITY', array('#BASKET_ITEM#' => $basketItem->getField('NAME'))),
						'BASKET_ITEM_'.$basketItem->getBasketCode()
					)
				);
				continue;
			}

			$r = $this->modifyQuantityShipmentItem($shipmentItem, $shippingItem);
			if(!$r->isSuccess())
				$result->addErrors($r->getErrors());

			$setFieldResult = $shipmentItem->setField('XML_ID', $shippingItem['XML_ID']);
			if (!$setFieldResult->isSuccess())
				$result->addErrors($setFieldResult->getErrors());
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $shipmentItemCollection->hasMeaningfulField();

			/** @var Result $r */
			$r = $shipmentItemCollection->doFinalAction($hasMeaningfulFields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	protected function prepareDataForSetFields(\Bitrix\Sale\Shipment $shipment, $items)
	{
		$result = new Result();
		return $result->setData([$items]);
	}

	protected function modifyQuantityShipmentItem(ShipmentItem $shipmentItem, array $params)
	{
		$result = new Result();
		if ($shipmentItem->getQuantity() < $params['AMOUNT'])
		{
			$this->order->setMathActionOnly(true);
			$setFieldResult = $shipmentItem->setField('QUANTITY', $params['AMOUNT']);
			$this->order->setMathActionOnly(false);

			if (!$setFieldResult->isSuccess())
			{
				$result->addErrors($setFieldResult->getErrors());
			}
		}

		$r = $this->setBarcodeShipmentItem($shipmentItem, $params);

		if($r->isSuccess() == false)
		{
			$result->addErrors($r->getErrors());
		}

		$setFieldResult = $shipmentItem->setField('QUANTITY', $params['AMOUNT']);

		if (!$setFieldResult->isSuccess())
		{
			$result->addErrors($setFieldResult->getErrors());
		}

		return $result;
	}

	protected function setBarcodeShipmentItem(ShipmentItem $shipmentItem, array $params)
	{
		$result = new Result();
		$basketItem = $shipmentItem->getBasketItem();

		$useStoreControl = Configuration::useStoreControl();

		if (!empty($params['BARCODE']) && ($useStoreControl || $params['IS_SUPPORTED_MARKING_CODE'] == 'Y' ))
		{
			$barcode = $params['BARCODE'];

			/** @var \Bitrix\Sale\ShipmentItemStoreCollection $shipmentItemStoreCollection */
			$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
			if (!$basketItem->isBarcodeMulti() && !$basketItem->isSupportedMarkingCode())
			{
				/** @var Result $r */
				$r = $shipmentItemStoreCollection->setBarcodeQuantityFromArray($params);
				if(!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			if (isset($barcode['ID']) && intval($barcode['ID']) > 0)
			{
				/** @var \Bitrix\Sale\ShipmentItemStore $shipmentItemStore */
				if ($shipmentItemStore = $shipmentItemStoreCollection->getItemById($barcode['ID']))
				{
					unset($barcode['ID']);
					$setFieldResult = $shipmentItemStore->setFields($barcode);

					if (!$setFieldResult->isSuccess())
						$result->addErrors($setFieldResult->getErrors());
				}
			}
			else
			{
				$shipmentItemStore = $shipmentItemStoreCollection->createItem($basketItem);
				$setFieldResult = $shipmentItemStore->setFields($barcode);
				if (!$setFieldResult->isSuccess())
					$result->addErrors($setFieldResult->getErrors());
			}
		}
		return $result;
	}

	protected function createEmptyPayment()
	{
		$innerPaySystem = PaySystem\Manager::getObjectById(PaySystem\Manager::getInnerPaySystemId());

		$paymentCollection = $this->order->getPaymentCollection();
		$payment = $paymentCollection->createItem($innerPaySystem);
		$payment->setField('SUM', $this->order->getPrice());

		return $payment;
	}

	protected function removePayments()
	{
		if($this->getSettingsContainer()->getItemValue('deletePaymentIfNotExists'))
		{
			$paymentCollection = $this->order->getPaymentCollection();

			$paymentIds = [];
			foreach($this->formData["PAYMENT"] as $paymentData)
			{
				if(!isset($paymentData['ID']))
					continue;

				$payment = $paymentCollection->getItemById($paymentData['ID']);

				if ($payment == null)
					continue;

				$paymentIds[] = $payment->getId();
			}

			foreach ($paymentCollection as $payment)
			{
				if(!in_array($payment->getId(), $paymentIds))
				{
					$r = $payment->delete();
					if (!$r->isSuccess())
					{
						$this->errorsContainer->addErrors($r->getErrors());
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * @return bool
	 */
	protected function isEmptyPaymentData(): bool
	{
		return empty($this->formData["PAYMENT"]) || !is_array($this->formData["PAYMENT"]);
	}

	/**
	 * @return bool
	 */
	protected function needCreateDefaultPayment(): bool
	{
		return $this->getSettingsContainer()->getItemValue('createDefaultPaymentIfNeed');
	}

	public function buildPayments()
	{
		global $USER;

		$isEmptyPaymentData = $this->isEmptyPaymentData();
		if ($isEmptyPaymentData)
		{
			$this->formData['PAYMENT'] = [];
		}

		if ($isEmptyPaymentData && !$this->needCreateDefaultPayment())
		{
			return $this;
		}

		if($isEmptyPaymentData && $this->getOrder()->isNew())
		{
			$this->createEmptyPayment();
			return $this;
		}

		if(!$this->removePayments())
		{
			$this->errorsContainer->addError(new Error('Payments remove - error'));
			throw new BuildingException();
		}

		$paymentCollection = $this->order->getPaymentCollection();

		foreach($this->formData["PAYMENT"] as $paymentData)
		{
			$paymentId = intval($paymentData['ID']);
			$isNew = ($paymentId <= 0);
			$hasError = false;
			$products = $paymentData['PRODUCT'] ?? [];

			$settablePaymentFields = $this->getSettablePaymentFields();

			if(count($settablePaymentFields)>0)//for backward compatibility
				$paymentData = array_intersect_key($paymentData, array_flip($settablePaymentFields));

			/** @var \Bitrix\Sale\Payment $paymentItem */
			if($isNew)
			{
				$paymentItem = $paymentCollection->createItem();
				if (isset($paymentData['CURRENCY']) && !empty($paymentData['CURRENCY']) && $paymentData['CURRENCY'] !== $this->order->getCurrency())
				{
					$paymentData["SUM"] = \CCurrencyRates::ConvertCurrency($paymentData["SUM"], $paymentData["CURRENCY"], $this->order->getCurrency());
					$paymentData['CURRENCY'] = $this->order->getCurrency();
				}
			}
			else
			{
				$paymentItem = $paymentCollection->getItemById($paymentId);

				if(!$paymentItem)
				{
					$this->errorsContainer->addError(new Error(Loc::getMessage("SALE_HLP_ORDERBUILDER_PAYMENT_NOT_FOUND")." - ".$paymentId));
					continue;
				}
			}

			$isReturn = (isset($paymentData['IS_RETURN']) && ($paymentData['IS_RETURN'] == 'Y' || $paymentData['IS_RETURN'] == 'P'));
			$psService = null;

			if((int)$paymentData['PAY_SYSTEM_ID'] > 0)
			{
				$psService = PaySystem\Manager::getObjectById((int)$paymentData['PAY_SYSTEM_ID']);
			}

			$paymentData['COMPANY_ID'] = (isset($paymentData['COMPANY_ID']) && intval($paymentData['COMPANY_ID']) > 0) ? intval($paymentData['COMPANY_ID']) : 0;
			$paymentData['PAY_SYSTEM_NAME'] = ($psService) ? $psService->getField('NAME') : '';

			$paymentFields['PAID'] = $paymentData['PAID'];
			unset($paymentData['PAID']);

			if($isNew)
			{
				if(empty($paymentData['COMPANY_ID']))
				{
					$paymentData['COMPANY_ID'] = $this->order->getField('COMPANY_ID');
				}

				if(empty($paymentData['RESPONSIBLE_ID']))
				{
					$paymentData['RESPONSIBLE_ID'] = $this->order->getField('RESPONSIBLE_ID');
					$paymentData['EMP_RESPONSIBLE_ID'] = $USER->GetID();
				}
			}

			$dateFields = ['DATE_PAID', 'DATE_PAY_BEFORE', 'DATE_BILL', 'PAY_RETURN_DATE', 'PAY_VOUCHER_DATE'];

			foreach($dateFields as $fieldName)
			{
				if(isset($paymentData[$fieldName]) && is_string($paymentData[$fieldName]))
				{
					try
					{
						$paymentData[$fieldName] = new Date($paymentData[$fieldName]);
					}
					catch (ObjectException $exception)
					{
						$this->errorsContainer->addError(new Error('Wrong field "'.$fieldName.'"'));
						$hasError = true;
					}
				}
			}

			if($paymentItem->isPaid()
				&& isset($paymentData['SUM'])
				&& abs(floatval($paymentData['SUM']) - floatval($paymentItem->getSum())) > 0.001)
			{
				$this->errorsContainer->addError(new Error(Loc::getMessage("SALE_HLP_ORDERBUILDER_ERROR_PAYMENT_SUM")));
				$hasError = true;
			}

			/*
			 * We are editing an order. We have only one payment. So the payment fields are mostly in view mode.
			 * If we have changed the price of the order then the sum of the payment must be changed automaticaly by payment api earlier.
			 * But if the payment sum was received from the form we will erase the previous changes.
			 */
			if(isset($paymentData['SUM']))
			{
				$paymentData['SUM'] = (float)str_replace(',', '.', $paymentData['SUM']);
			}

			if($paymentData['PRICE_COD'])
			{
				$paymentData['PRICE_COD'] = $paymentData['PRICE_COD'];
			}


			if(isset($paymentData['RESPONSIBLE_ID']))
			{
				$paymentData['RESPONSIBLE_ID'] = !empty($paymentData['RESPONSIBLE_ID']) ? $paymentData['RESPONSIBLE_ID'] : $USER->GetID();

				if($paymentData['RESPONSIBLE_ID'] != $paymentItem->getField('RESPONSIBLE_ID'))
				{
					if(!$isNew)
					{
						$paymentData['EMP_RESPONSIBLE_ID'] = $USER->GetID();
					}
				}
			}

			if(!$hasError)
			{
				if($paymentItem->isInner() && isset($paymentData['SUM']) && $paymentData['SUM'] === 0)
				{
					unset($paymentData['SUM']);
				}

				$setResult = $paymentItem->setFields($paymentData);

				if(!$setResult->isSuccess())
				{
					$this->errorsContainer->addErrors($setResult->getErrors());
				}

				if ($products)
				{
					$this->buildPayableItems($paymentItem, $products);
				}

				if($isReturn && $paymentData['IS_RETURN'])
				{
					$setResult = $paymentItem->setReturn($paymentData['IS_RETURN']);

					if(!$setResult->isSuccess())
					{
						$this->errorsContainer->addErrors($setResult->getErrors());
					}
				}

				if(!empty($paymentFields['PAID']))
				{
					$setResult = $paymentItem->setPaid($paymentFields['PAID']);

					if(!$setResult->isSuccess())
					{
						$this->errorsContainer->addErrors($setResult->getErrors());
					}
				}
			}
		}

		return $this;
	}

	/**
	 * @param Payment $payment
	 * @param array $payableItems
	 * @return Result
	 * @throws ArgumentNullException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public function buildPayableItems(Payment $payment, array $payableItems): Result
	{
		$result = new Result();

		$basket = $this->order->getBasket();
		$payableItemCollection = $payment->getPayableItemCollection();

		foreach ($payableItems as $item)
		{
			$payableItem = null;

			if (isset($item['BASKET_CODE']))
			{
				/** @var BasketItem $basketItem */
				$basketItem = $basket->getItemByBasketCode($item['BASKET_CODE']);
				if ($basketItem)
				{
					$payableItem = $payableItemCollection->createItemByBasketItem($basketItem);
				}
			}
			elseif (isset($item['DELIVERY_ID']))
			{
				/** @var Shipment $shipment */
				foreach ($this->order->getShipmentCollection()->getNotSystemItems() as $shipment)
				{
					if (
						$shipment->getId() === 0
						&& (int)$item['DELIVERY_ID'] === $shipment->getDeliveryId()
					)
					{
						$payableItem = $payableItemCollection->createItemByShipment($shipment);
					}
				}
			}

			if ($payableItem === null)
			{
				continue;
			}

			$quantity = floatval(str_replace(',', '.', $item['QUANTITY']));

			$payableItem->setField('QUANTITY', $quantity);
		}

		return $result;
	}

	public function buildTradeBindings()
	{
		if(!isset($this->formData["TRADE_BINDINGS"]))
		{
			return $this;
		}

		if(!$this->removeTradeBindings())
		{
			return $this;
		}

		if(isset($this->formData["TRADE_BINDINGS"]) && count($this->formData["TRADE_BINDINGS"])>0)
		{
			$tradeBindingCollection = $this->order->getTradeBindingCollection();

			foreach($this->formData["TRADE_BINDINGS"] as $fields)
			{
				if ((int)$fields['TRADING_PLATFORM_ID'] === 0)
				{
					continue;
				}

				$r = $this->tradingPlatformExists($fields['TRADING_PLATFORM_ID']);

				if($r->isSuccess())
				{
					$id = intval($fields['ID']);
					$isNew = ($id <= 0);

					if($isNew)
					{
						$binding = $tradeBindingCollection->createItem();
					}
					else
					{
						$binding = $tradeBindingCollection->getItemById($id);

						if(!$binding)
						{
							$this->errorsContainer->addError(new Error('Can\'t find Trade Binding with id:"'.$id.'"', 'TRADE_BINDING_NOT_EXISTS'));
							continue;
						}
					}

					$fields = array_intersect_key($fields, array_flip(TradeBindingEntity::getAvailableFields()));

					$r = $binding->setFields($fields);
				}

				if(!$r->isSuccess())
					$this->errorsContainer->addErrors($r->getErrors());
			}
		}

		return $this;
	}

	protected function tradingPlatformExists($id)
	{
		$r = new Result();

		$platformFields = TradingPlatformTable::getById($id)->fetchAll();

		if (!isset($platformFields[0]))
		{
			$r->addError(new Error('tradingPlatform is not exists'));
		}

		return $r;
	}

	protected function removeTradeBindings()
	{
		if($this->getSettingsContainer()->getItemValue('deleteTradeBindingIfNotExists'))
		{
			$tradeBindingCollection = $this->order->getTradeBindingCollection();

			$internalIx = [];
			foreach($this->formData["TRADE_BINDINGS"] as $tradeBinding)
			{
				if(!isset($tradeBinding['ID']))
					continue;

				$binding = $tradeBindingCollection->getItemById($tradeBinding['ID']);

				if ($binding == null)
					continue;

				$internalIx[] = $binding->getId();
			}

			foreach ($tradeBindingCollection as $binding)
			{
				if(!in_array($binding->getId(), $internalIx))
				{
					$r = $binding->delete();
					if (!$r->isSuccess())
					{
						$this->errorsContainer->addErrors($r->getErrors());
						return false;
					}
				}
			}
		}

		return true;
	}

	public function finalActions()
	{
		if($this->isStartField)
		{
			$hasMeaningfulFields = $this->order->hasMeaningfulField();
			$r = $this->order->doFinalAction($hasMeaningfulFields);

			if(!$r->isSuccess())
			{
				$this->errorsContainer->addErrors($r->getErrors());
			}
		}

		return $this;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function getSettingsContainer()
	{
		return $this->settingsContainer;
	}

	public function getErrorsContainer()
	{
		return $this->errorsContainer;
	}

	public function getFormData($fieldName = '')
	{
		if($fieldName <> '')
		{
			$result = isset($this->formData[$fieldName]) ? $this->formData[$fieldName]:null;
		}
		else
		{
			$result = $this->formData;
		}

		return $result;
	}

	public function getBasketBuilder()
	{
		return $this->basketBuilder;
	}

	public static function getDefaultPersonType($siteId)
	{
		$personTypes = self::getBuyerTypesList($siteId);
		reset($personTypes);
		return key($personTypes);
	}

	public static function getBuyerTypesList($siteId)
	{
		static $result = array();

		if(!isset($result[$siteId]))
		{
			$result[$siteId] = array();

			$res = \Bitrix\Sale\Internals\PersonTypeTable::getList(array(
				'order' => array('SORT' => 'ASC', 'NAME' => 'ASC'),
				'filter' => array('=ACTIVE' => 'Y', '=PERSON_TYPE_SITE.SITE_ID' => $siteId),
			));

			while ($personType = $res->fetch())
			{
				$result[$siteId][$personType["ID"]] = htmlspecialcharsbx($personType["NAME"])." [".$personType["ID"]."]";
			}
		}

		return $result[$siteId];
	}

	public function getUserId()
	{
		if((int)$this->formData["USER_ID"] > 0)
		{
			return (int)$this->formData["USER_ID"];
		}

		$userId = 0;

		if (!isset($this->formData["USER_ID"]) || (int)($this->formData["USER_ID"]) <= 0)
		{
			$settingValue = (int)$this->getSettingsContainer()->getItemValue('createUserIfNeed');

			if ($settingValue === \Bitrix\Sale\Helpers\Order\Builder\SettingsContainer::SET_ANONYMOUS_USER)
			{
				$userId = \CSaleUser::GetAnonymousUserID();
			}
			elseif ($settingValue === \Bitrix\Sale\Helpers\Order\Builder\SettingsContainer::ALLOW_NEW_USER_CREATION)
			{
				$userId = $this->createUserFromFormData();
			}
		}

		if ($userId > 0 && empty($this->formData["USER_ID"]))
		{
			$this->formData["USER_ID"] = $userId;
		}

		return $userId;
	}

	/**
	 * @return false|int|mixed|string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function createUserFromFormData()
	{
		$errors = [];
		$orderProps = $this->order->getPropertyCollection();

		if ($email = $orderProps->getUserEmail())
		{
			$email = $email->getValue();
		}

		if ($name = $orderProps->getPayerName())
		{
			$name = $name->getValue();
		}

		if ($phone = $orderProps->getPhone())
		{
			$phone = $phone->getValue();
		}

		if ($this->getSettingsContainer()->getItemValue('searchExistingUserOnCreating'))
		{
			$userId = $this->searchExistingUser($email, $phone);
		}

		if (!isset($userId))
		{
			$userId = $this->searchExistingUser($email, $phone);
		}

		if (!isset($userId))
		{
			$userId = \CSaleUser::DoAutoRegisterUser(
				$email,
				$name,
				$this->formData['SITE_ID'],
				$errors,
				[
					'PERSONAL_PHONE' => $phone,
					'PHONE_NUMBER' => $phone,
				]
			);

			if (!empty($errors))
			{
				foreach ($errors as $val)
				{
					$this->errorsContainer->addError(new Error($val['TEXT'], 0, 'USER'));
				}
			}
			else
			{
				// ToDo remove it? when to authorize buyer?
				global $USER;
				$USER->Authorize($userId);
			}
		}

		return $userId;
	}

	/**
	 * @param $email
	 * @param $phone
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function searchExistingUser($email, $phone): ?int
	{
		$existingUserId = null;

		if (!empty($email))
		{
			$res = Main\UserTable::getRow([
				'filter' => [
					'=ACTIVE' => 'Y',
					'=EMAIL' => $email,
				],
				'select' => ['ID'],
			]);
			if (isset($res['ID']))
			{
				$existingUserId = (int)$res['ID'];
			}
		}

		if (!$existingUserId && !empty($phone))
		{
			$normalizedPhone = NormalizePhone($phone);
			$normalizedPhoneForRegistration = Main\UserPhoneAuthTable::normalizePhoneNumber($phone);

			if (!empty($normalizedPhone))
			{
				$res = Main\UserTable::getRow([
					'filter' => [
						'ACTIVE' => 'Y',
						[
							'LOGIC' => 'OR',
							'=PHONE_AUTH.PHONE_NUMBER' => $normalizedPhoneForRegistration,
							'=PERSONAL_PHONE' => $normalizedPhone,
							'=PERSONAL_MOBILE' => $normalizedPhone,
						],
					],
					'select' => ['ID'],
				]);
				if (isset($res['ID']))
				{
					$existingUserId = (int)$res['ID'];
				}
			}
		}

		return $existingUserId;
	}
}