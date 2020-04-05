<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Type\RandomSequence;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Helpers\Order\Builder\BuildingException;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Rest\Synchronization\Loader\Factory;
use Bitrix\Sale\Rest\Synchronization\LoggerDiag;
use Bitrix\Sale\Result;

class Order extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Sale\Order::class,
			'order',
			function($className, $id) {

				/** @var \Bitrix\Sale\Order $className */
				$order = $className::load($id);
				if($order instanceof $className)
				{
					return $order;
				}
				else
				{
					$this->addError(new Error('order is not exists', 200540400001));
				}
				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\Order();
		return ['ORDER'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function getAction(\Bitrix\Sale\Order $order)
	{
		//TODO: return $order->toArray();
		return $this->toArray($order);
	}

	public function tryModifyAction(array $fields)
	{
		$r = $this->modify($fields);

		if($r->isSuccess())
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $r->getData()['ORDER'];

			$result = $this->toArray($order);

			if(is_array($result['ORDER']['PAYMENTS']))
			{
				foreach ($result['ORDER']['PAYMENTS'] as $ix=>&$fields)
				{
					$paySystems = Manager::getListWithRestrictions(
						$order
							->getPaymentCollection()
							->getItemByIndex($ix)
					);

					foreach ($paySystems as $paySystem)
					{
						if((int)$paySystem['PAY_SYSTEM_ID']>0) //Without Inner
						{
							$fields['LIST_PAY_SYSTEM_WITH_RESTRICTIONS'][]=[
								'ID'=>$paySystem['PAY_SYSTEM_ID'],
							];
						}
					}
				}
			}

			if(is_array($result['ORDER']['SHIPMENTS']))
			{
				foreach ($result['ORDER']['SHIPMENTS'] as $ix=>&$fields)
				{
					$services = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList(
						$order
							->getShipmentCollection()
							->getItemByIndex($ix)
					);

					foreach ($services as $service)
					{
						$fields['LIST_DELIIVERY_SERVICES_RESTRICTIONS'][]=[
							'ID'=>$service->getId(),
						];
					}
				}
			}
			return $result;
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function modifyAction(array $fields)
	{
		$r = $this->modify($fields);

		if($r->isSuccess())
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $r->getData()['ORDER'];

			$r = $order->save();
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}
			elseif($r->hasWarnings())
			{
				$this->addErrors($r->getWarnings());
				return null;
			}

			//TODO: return $order->toArray();
			return $this->toArray($order);
		}
		elseif($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());
			return null;
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function tryAddAction(array $fields)
	{
		$r = $this->add($fields);

		if($r->isSuccess())
		{
			$order = $r->getData()['ORDER'];
			return $this->toArray($order);
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function addAction(array $fields)
	{
		$result = null;

		$r = $this->add($fields);

		if($r->isSuccess())
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $r->getData()['ORDER'];

			$r = $order->save();
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}

			//TODO: return $order->toArray();
			return $this->toArray($order);
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function tryUpdateAction(\Bitrix\Sale\Order $order, array $fields)
	{
		$r = $this->update($order, $fields);

		if($r->isSuccess())
		{
			$order = $r->getData()['ORDER'];
			return $this->toArray($order);
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function updateAction(\Bitrix\Sale\Order $order, array $fields)
	{
		$result = null;
		$r = $this->update($order, $fields);

		if($r->isSuccess())
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $r->getData()['ORDER'];

			$r = $order->save();
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}

			//TODO: return $order->toArray();
			return $this->toArray($order);
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation=null)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;
		$runtime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'PERSON_TYPE',
				'\Bitrix\Sale\Internals\PersonType',
				array('=this.PERSON_TYPE_ID' => 'ref.ID')
			),
			new \Bitrix\Main\Entity\ReferenceField(
				'STATUS_TABLE',
				'\Bitrix\Sale\Internals\StatusTable',
				array('=this.STATUS_ID' => 'ref.ID')
			)
		];

		$orders = \Bitrix\Sale\Order::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset'=>$pageNavigation->getOffset(),
				'limit'=>$pageNavigation->getLimit(),
				'runtime'=>$runtime
			]
		)->fetchAll();

		return new Page('ORDERS', $orders, function() use ($select, $filter, $runtime)
		{
			return count(
				\Bitrix\Sale\Order::getList(['select'=>$select, 'filter'=>$filter, 'runtime'=>$runtime])->fetchAll()
			);
		});
	}

	public function deleteAction(\Bitrix\Sale\Order $order)
	{
		$r = $order->delete($order->getId());
		if($r->isSuccess())
			$r = $order->save();

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		if ($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());
			return null;
		}

		return true;
	}

	public function getDeliveryIdListAction(\Bitrix\Sale\Order $order)
	{
		return ['DELIVERY_ID_LIST'=>$order->getDeliveryIdList()];
	}

	public function getPaymentsAction(\Bitrix\Sale\Order $order)
	{
		return ['PAYMENTS'=>$this->toArray($order)['ORDER']['PAYMENTS']];
	}

	public function getPaySystemIdListAction(\Bitrix\Sale\Order $order)
	{
		return ['PAY_SYSTEM_ID_LIST'=>$order->getPaySystemIdList()];
	}

	public function getPrintedChecksAction(\Bitrix\Sale\Order $order)
	{
		return ['PRINTED_CHECKS'=>$order->getPrintedChecks()];
	}

	public function getShipmentsAction(\Bitrix\Sale\Order $order)
	{
		return ['SHIPMENTS'=>$this->toArray($order)['ORDER']['SHIPMENTS']];
	}

	public function getBasketAction(\Bitrix\Sale\Order $order)
	{
		return ['BASKET_ITEMS'=>$this->toArray($order)['ORDER']['BASKET_ITEMS']];
	}

	public function getCurrencyAction(\Bitrix\Sale\Order $order)
	{
		return $order->getField('CURRENCY');
	}

	public function getDateInsertAction(\Bitrix\Sale\Order $order)
	{
		return $order->getField('DATE_INSERT');
	}

	public function getApplyDiscountAction(\Bitrix\Sale\Order $order)
	{
		//TODO: return $order->getDiscount()->toArray();
		return $this->toArray($order)['DISCOUNTS'];
	}

	public function getPersonTypeIdAction(\Bitrix\Sale\Order $order)
	{
		return $order->getPersonTypeId();
	}

	public function getPriceAction(\Bitrix\Sale\Order $order)
	{
		return $order->getPrice();
	}

	public function getPropertyValuesAction(\Bitrix\Sale\Order $order)
	{
		return ['PROPERTY_VALUES'=>$this->toArray($order)['ORDER']['PROPERTY_VALUES']];
	}

	public function getSiteIdAction(\Bitrix\Sale\Order $order)
	{
		return $order->getSiteId();
	}

	public function getSumPaidAction(\Bitrix\Sale\Order $order)
	{
		return $order->getSumPaid();
	}
/*
	public function getTaxListAction(\Bitrix\Sale\Order $order)
	{
		//TODO: return $order->getTax()->toArray();
		return $this->toArray($order)['TAX'];
	}

	public function getTaxLocationAction(\Bitrix\Sale\Order $order)
	{
		return $order->getTaxLocation();
	}
*/
	public function getTaxPriceAction(\Bitrix\Sale\Order $order)
	{
		return $order->getTaxPrice();
	}

	public function getTaxValueAction(\Bitrix\Sale\Order $order)
	{
		return $order->getTaxValue();
	}

	public function getUserIdAction(\Bitrix\Sale\Order $order)
	{
		return $order->getUserId();
	}

	public function getVatRateAction(\Bitrix\Sale\Order $order)
	{
		return $order->getVatRate();
	}

	public function getVatSumAction(\Bitrix\Sale\Order $order)
	{
		return $order->getVatSum();
	}

	public function isCanceledAction(\Bitrix\Sale\Order $order)
	{
		return $order->isCanceled()?'Y':'N';
	}

	public function isExternalAction(\Bitrix\Sale\Order $order)
	{
		return $order->isExternal()?'Y':'N';
	}

	public function isMarkedAction(\Bitrix\Sale\Order $order)
	{
		return $order->isMarked()?'Y':'N';
	}

	public function isPaidAction(\Bitrix\Sale\Order $order)
	{
		return $order->isPaid()?'Y':'N';
	}

	public function isShippedAction(\Bitrix\Sale\Order $order)
	{
		return $order->isShipped()?'Y':'N';
	}

	public function isUsedVatAction(\Bitrix\Sale\Order $order)
	{
		return $order->isUsedVat()?'Y':'N';
	}

	//public function applyDiscountAction(\Bitrix\Sale\Order $order, array $data)
	//public function refreshAction(\Bitrix\Sale\Order $order, array $data)

	//endregion

	//region admin Actions
	//public function cancelOrderAction(\Bitrix\Sale\Order $order, array $data)
	//public function saveCommentsAction(\Bitrix\Sale\Order $order, array $data)
	//public function saveStatusAction(\Bitrix\Sale\Order $order, array $data)
	//public function changeResponsibleUserAction(\Bitrix\Sale\Order $order, array $data)
	//public function updatePaymentStatusAction()
	//public function updateShipmentStatusAction()
	//public function changeDeliveryServiceAction()
	//public function checkProductBarcodeAction()
	//public function deleteCouponAction(\Bitrix\Sale\Order $order, array $data)
	//public function addCouponsAction(\Bitrix\Sale\Order $order, array $data)
	//public function getProductIdByBarcodeAction()
	//public function refreshOrderDataAction(\Bitrix\Sale\Order $order, array $data)
	//endregion

	protected function modify(array $fields)
	{
		$r = new Result();

		$builder = $this->getBuilder();
		try{
			$builder->build($fields);
			$errorsContainer = $builder->getErrorsContainer();
		}
		catch(BuildingException $e)
		{
			if($builder->getErrorsContainer()->getErrorCollection()->count()<=0)
			{
				$builder->getErrorsContainer()->addError(new Error('unknow error', 200550000001));
			}
			$errorsContainer = $builder->getErrorsContainer();
		}

		if($errorsContainer->getErrorCollection()->count()>0)
			$r->addErrors($errorsContainer->getErrors());
		else
			$r->setData(['ORDER'=>$builder->getOrder()]);

		return $r;
	}

	protected function add(array $fields)
	{
		$r = new Result();

		$fields = ['ORDER'=>$fields];

		if($fields['ORDER']['ID'])
			unset($fields['ORDER']['ID']);

		$orderBuilder = $this->getBuilder();
		$order = $orderBuilder->buildEntityOrder($fields);

		if($orderBuilder->getErrorsContainer()->getErrorCollection()->count()>0)
			$r->addErrors($orderBuilder->getErrorsContainer()->getErrors());
		else
			$r->setData(['ORDER'=>$order]);

		return $r;
	}

	protected function update(\Bitrix\Sale\Order $order, array $fields)
	{
		$r = new Result();
		$data=[];

		$data['ORDER'] = $fields;
		$data['ORDER']['ID'] = $order->getId();

		$orderBuilder = $this->getBuilder();
		$order = $orderBuilder->buildEntityOrder($data);

		if($orderBuilder->getErrorsContainer()->getErrorCollection()->count()>0)
			$r->addErrors($orderBuilder->getErrorsContainer()->getErrors());
		else
			$r->setData(['ORDER'=>$order]);

		return $r;
	}

	protected function get(\Bitrix\Sale\Order $order, array $fields=[])
	{
		return $this->toArray($order, $fields);
	}

	static public function prepareFields(array $fields)
	{
		$fields = isset($fields['ORDER'])? $fields['ORDER']:[];

		if(isset($fields['BASKET_ITEMS']))
			unset($fields['BASKET_ITEMS']);
		if(isset($fields['PROPERTY_VALUES']))
			unset($fields['PROPERTY_VALUES']);
		if(isset($fields['PAYMENTS']))
			unset($fields['PAYMENTS']);
		if(isset($fields['SHIPMENTS']))
			unset($fields['SHIPMENTS']);
		if(isset($fields['TRADE_BINDINGS']))
			unset($fields['TRADE_BINDINGS']);
		if(isset($fields['CLIENTS']))
			unset($fields['CLIENTS']);
		if(isset($fields['REQUISITE_LINKS']))
			unset($fields['REQUISITE_LINKS']);

		return $fields;
	}

	private static function setFlagActionImport()
	{
		//TODO: huck для блокировки исходящего события в \Bitrix\Sale\Rest\RestManager::processEvent(). Блокируется действием - import т.к. запрос входящий
		$instance = \Bitrix\Sale\Rest\Synchronization\Manager::getInstance();
		$instance->setAction(\Bitrix\Sale\Rest\Synchronization\Manager::ACTION_IMPORT);
	}

	public function importDeleteAction(\Bitrix\Sale\Order $order)
	{
		self::setFlagActionImport();

		return $this->deleteAction($order);
	}

	public function resolveExternalIdToInternalId(array $fields)
	{
		LoggerDiag::addMessage('ORDER_RESOLVE_EXTERNAL_ID_TO_INTERNAL_ID_SOURCE_FIELDS', var_export($fields, true));

		$result = new Result();

		$instance = \Bitrix\Sale\Rest\Synchronization\Manager::getInstance();

		$ixInternal = [];
		$ixExternal = [];
		$internalOrderId = -1;

		$externalId = $fields['ORDER']['XML_ID'];
		$ixExternal['ORDER']['MAP'][$externalId] = $fields['ORDER']['ID'];

		unset($fields['ORDER']['ID']);
		$internalId = $this->getInternalId($fields['ORDER']['XML_ID'], Registry::ENTITY_ORDER);
		if(intval($internalId)>0)
		{
			$fields['ORDER']['ID'] = $internalId;
			$ixInternal['ORDER']['MAP'][$externalId] = $internalId;
			$internalOrderId = $fields['ORDER']['ID'];
		}

		$internalOrderStatusId = $this->getInternalId($fields['ORDER']['STATUS_XML_ID'], Registry::ENTITY_ORDER_STATUS);
		$fields['ORDER']['STATUS_ID' ] = $internalOrderStatusId<>''? $internalOrderStatusId:$instance->getDefaultOrderStatusId();

		// значения определются только для нового заказа.
		// в отличии от реста магазина в рамках реста импорта изменение значений полей заказа - сайта, типа плательщика или пользователя не производится
		if(intval($internalId)<=0)
		{
			//TODO: предусмотреть связь с внешней системой
			$internalPersonTypeId = $this->getInternalId($fields['ORDER']['PERSON_TYPE_XML_ID'], 'PERSON_TYPE_TYPE');
			$fields['ORDER']['PERSON_TYPE_ID'] = $internalPersonTypeId>0 ?  $internalPersonTypeId:$instance->getDefaultPersonTypeId();
			$fields['ORDER']['USER_ID'] = \CSaleUser::GetAnonymousUserID();
			$fields['ORDER']['SITE_ID'] = $instance->getDefaultSiteId();
		}
		else
		{
			$order = \Bitrix\Sale\Order::load($internalId);
			$fields['ORDER']['PERSON_TYPE_ID'] = $order->getPersonTypeId();
			$fields['ORDER']['USER_ID'] = $order->getUserId();
			$fields['ORDER']['SITE_ID'] = $order->getSiteId();
		}

		if(is_array($fields['ORDER']['PROPERTY_VALUES']))
		{
			foreach($fields['ORDER']['PROPERTY_VALUES'] as $k=>&$item)
			{
				$internalIdExternalSystem = $item['ORDER_PROPS_ID'];
				$externalId = $item['ORDER_PROPS_XML_ID'];

				unset($item['ORDER_PROPS_ID']);
				unset($item['ORDER_PROPS_XML_ID']);
				unset($item['ID']);//id не передается т.к. запись значения свойства идентифицируется только по orderPropsId

				if($externalId<>'')
				{
					$ixExternal['PROPERTIES'][$k]['MAP'][$externalId] = $internalIdExternalSystem;

					$internalId = $this->getInternalId($externalId, Registry::ENTITY_PROPERTY);
					if(intval($internalId)>0)
					{
						$item['ORDER_PROPS_ID'] = $internalId;
						$ixInternal['PROPERTIES'][$k]['MAP'][$externalId] = $internalId;
					}
				}
				else
				{
					unset($item);
				}
			}
		}

		if(is_array($fields['ORDER']['BASKET_ITEMS']))
		{
			$n = 1;
			foreach($fields['ORDER']['BASKET_ITEMS'] as $k=>&$item)
			{
				$internalIdExternalSystem = $item['ID'];
				$externalId = $item['XML_ID'];

				$internalId = $this->getInternalId($externalId, Registry::ENTITY_BASKET, ['ORDER_ID'=>$internalOrderId]);
				$internalBasketItemId = (intval($internalId)>0)? $internalId:-1;
				$ixInternal['BASKET_ITEMS'][$k]['MAP'][$externalId] = (intval($internalId)>0)? $internalId:'n'.$n++;
				$ixExternal['BASKET_ITEMS'][$k]['MAP'][$externalId] = $internalIdExternalSystem;

				$properties = $item['PROPERTIES'];
				if(count($properties)>0)
				{
					foreach ($properties as $kp=>&$property)
					{
						$property['BASKET_ID'] = $ixInternal['BASKET_ITEMS'][$k]['MAP'][$externalId];
						$internalIdBasketProps = $this->getInternalId($property['XML_ID'], Registry::ENTITY_BASKET_PROPERTIES_COLLECTION, ['BASKET_ID'=>$internalBasketItemId]);
						if(intval($internalIdBasketProps)>0)
						{
							$ixInternal['BASKET_ITEMS'][$k]['PROPERTIES'][$kp][$property['XML_ID']] = $internalIdBasketProps;
							$property['ID'] = $internalIdBasketProps;
						}
						$ixExternal['BASKET_ITEMS'][$k]['PROPERTIES'][$kp]['MAP'][$property['XML_ID']] = $property['ID'];
					}
				}


				$item = array_merge(
					['PROPERTIES'=>$properties],
					$this->prepareFieldsBasketItem($item)
				);

				$item['ID'] = $ixInternal['BASKET_ITEMS'][$k]['MAP'][$externalId];
			}
		}

		if(is_array($fields['ORDER']['PAYMENTS']))
		{
			foreach($fields['ORDER']['PAYMENTS'] as $k=>&$item)
			{
				$externalId = $item['XML_ID'];
				$ixExternal['PAYMENTS'][$k]['MAP'][$externalId] = $item['ID'];

				unset($item['ID']);
				$internalId = $this->getInternalId($externalId, Registry::ENTITY_PAYMENT_COLLECTION, ['ORDER_ID'=>$internalOrderId]);
				if(intval($internalId)>0)
				{
					$item['ID'] = $internalId;
					$ixInternal['PAYMENTS'][$k]['MAP'][$externalId] = $internalId;
				}

				$externalPaySystemId = $item['PAY_SYSTEM_XML_ID'];
				$ixExternal['PAYMENTS'][$k]['PAY_SYSTEMS']['MAP'][$externalPaySystemId] = $item['PAY_SYSTEM_ID'];

				unset($item['PAY_SYSTEM_XML_ID']);
				$internalPaySystemId = $this->getInternalId($externalPaySystemId, 'PAY_SYSTEM_TYPE');
				$item['PAY_SYSTEM_ID'] = $internalPaySystemId>0 ? $internalPaySystemId:$instance->getDefaultPaySystemId();
				$ixInternal['PAYMENTS'][$k]['PAY_SYSTEM']['MAP'][$externalPaySystemId] = $item['PAY_SYSTEM_ID'];
			}
		}

		if(is_array($fields['ORDER']['SHIPMENTS']))
		{
			foreach($fields['ORDER']['SHIPMENTS'] as $k=>&$item)
			{
				$externalId = $item['XML_ID'];
				$ixExternal['SHIPMENTS'][$k]['MAP'][$externalId] = $item['ID'];

				unset($item['ID']);
				$internalId = $this->getInternalId($item['XML_ID'], Registry::ENTITY_SHIPMENT_COLLECTION, ['ORDER_ID'=>$internalOrderId]);
				$internalShipmentId = (intval($internalId)>0)? $internalId:-1;
				if(intval($internalId)>0)
				{
					$item['ID'] = $internalId;
					$ixInternal['SHIPMENTS'][$k][$externalId] = $internalId;
				}

				$externalDeliveryId = $item['DELIVERY_XML_ID'];
				$ixExternal['SHIPMENTS'][$k]['DELIVERY_SYSTEM']['MAP'][$externalDeliveryId] = $item['DELIVERY_ID'];

				unset($item['DELIVERY_XML_ID']);
				$internalDeliveryId = $this->getInternalId($externalDeliveryId, 'DELIVERY_SYSTEM_TYPE');
				$item['DELIVERY_ID'] = $internalDeliveryId>0 ? $internalDeliveryId:$instance->getDefaultDeliverySystemId();
				$ixInternal['SHIPMENTS'][$k]['DELIVERY_SYSTEM']['MAP'][$externalDeliveryId] = $item['DELIVERY_ID'];

				$externalDeliveryStatusId = $item['STATUS_XML_ID'];
				$ixExternal['SHIPMENTS'][$k]['DELIVERY_STATUS']['MAP'][$externalDeliveryStatusId] = $item['STATUS_ID'];

				unset($item['STATUS_XML_ID']);
				$internalDeliveryStatusId = $this->getInternalId($externalDeliveryStatusId, Registry::ENTITY_DELIVERY_STATUS);
				$item['STATUS_ID'] = $internalDeliveryStatusId<>''? $internalDeliveryStatusId:$instance->getDefaultDeliveryStatusId();
				$ixInternal['SHIPMENTS'][$k]['DELIVERY_STATUS']['MAP'][$externalDeliveryStatusId] = $item['STATUS_ID'];

				foreach($item['SHIPMENT_ITEMS'] as $kb=>&$shipmentItem)
				{
					unset($shipmentItem['ID']);
					unset($shipmentItem['ORDER_DELIVERY_ID']);
					$internalIdShipmentItem = $this->getInternalId($shipmentItem['XML_ID'], Registry::ENTITY_SHIPMENT_ITEM_COLLECTION, ['ORDER_DELIVERY_ID'=>$internalShipmentId]);

					if(intval($internalIdShipmentItem)>0)
					{
						$shipmentItem['ID'] = $internalIdShipmentItem;
						if(intval($internalId)>0)
							$shipmentItem['ORDER_DELIVERY_ID'] = $internalId;

						$ixInternal['SHIPMENTS'][$k]['SHIPMENT_ITEMS'][$kb]['MAP'][$externalId] = $internalIdShipmentItem;
					}

					// получим из внешнего соответствие xmlId => id.внешней системы, внешний идентификатор по внутреннему id внейшней системы
					$external = '';
					foreach ($ixExternal['BASKET_ITEMS'] as $map)
					{
						$internal = current($map['MAP']);

						if($shipmentItem['BASKET_ID'] == $internal)
						{
							$external = key($map['MAP']);
							break;
						}
					}

					if($external=='')
						$result->addError(new Error('Modify fields error. ShipmentItem xmlId is invalid',200550000002));

					if($external<>'')
					{
						// получим реальный id корзины из внутренниго соответсвия xmlId => id.внутрений сиситемы
						foreach ($ixInternal['BASKET_ITEMS'] as $map)
						{
							if(isset($map['MAP'][$external]))
							{
								$shipmentItem['BASKET_ID'] = $map['MAP'][$external];
								break;
							}
						}
					}
				}

				$item = $this->prepareFieldsShipment($item);
			}
		}

		if($this->isB24())
		{
			if(is_array($fields['ORDER']['TRADE_BINDINGS']))
			{
				foreach($fields['ORDER']['TRADE_BINDINGS'] as $k=>&$item)
				{
					$externalId = $item['XML_ID'];
					$ixExternal['TRADE_BINDINGS'][$k]['MAP'][$externalId] = $item['ID'];

					unset($item['ID']);
					if($externalId<>'') // условие для БУС. xmlId из БУС не передается
					{
						$internalId = $this->getInternalId($externalId, Registry::ENTITY_TRADE_BINDING_COLLECTION, ['ORDER_ID'=>$internalOrderId]);
						if(intval($internalId)>0)
						{
							$item['ID'] = $internalId;
							$ixInternal['TRADE_BINDINGS'][$k]['MAP'][$externalId] = $internalId;
						}
					}

					$externalTradePlatformId = $item['TRADING_PLATFORM_XML_ID'];
					$ixExternal['TRADE_BINDINGS'][$k]['TRADING_PLATFORMS']['MAP'][$externalTradePlatformId] = $item['TRADING_PLATFORM_ID'];

					unset($item['TRADING_PLATFORM_XML_ID']);
					$internalTradePlatformId = $this->getInternalId($externalTradePlatformId, 'TRADING_PLATFORM_TYPE');
					//TODO: need default value <> 0
					$item['TRADING_PLATFORM_ID'] = $internalTradePlatformId>0 ? $internalTradePlatformId:0;
					$ixInternal['TRADE_BINDINGS'][$k]['TRADING_PLATFORM']['MAP'][$externalTradePlatformId] = $item['TRADING_PLATFORM_ID'];
				}
			}

			if(is_array($fields['ORDER']['CLIENTS']))
			{
				foreach($fields['ORDER']['CLIENTS'] as $k=>&$item)
				{
					$externalId = $item['XML_ID'];
					$ixExternal['CLIENTS'][$k]['MAP'][$externalId] = $item['ID'];

					unset($item['ID']);
					$internalId = $this->getInternalId($externalId, ENTITY_CRM_CONTACT_COMPANY_COLLECTION);
					if(intval($internalId)>0)
					{
						$item['ID'] = $internalId;
						$ixInternal['CLIENTS'][$k]['MAP'][$externalId] = $internalId;
					}
				}
			}
		}
		else
		{
			// оставляем ключ TRADE_BINDINGS чтобы на строне БУС не удалить реальные привязки источников к заказам.
			// источники в рамках обмена не поддерживаются. Их подменяет настройка сайт со стороны БУС и источник на строне Б24
			$fields['ORDER']['TRADE_BINDINGS'] = [];
			unset($fields['ORDER']['CLIENTS']);
		}

		if($result->isSuccess())
		{
			$result->setData(['DATA'=>$fields]);
			LoggerDiag::addMessage('ORDER_RESOLVE_EXTERNAL_ID_TO_INTERNAL_ID_SUCCESS', var_export($fields, true));
		}
		else
		{
			LoggerDiag::addMessage('ORDER_RESOLVE_EXTERNAL_ID_TO_INTERNAL_ID_ERROR');
		}

		return $result;
	}

	protected function getInternalId($externalId, $typeName, $params=[])
	{
		$loader = Factory::create($typeName, $params);
		return $loader->getFieldsByExternalId($externalId);
	}

	private function prepareFieldsBasketItem($fields)
	{
		$instance = \Bitrix\Sale\Rest\Synchronization\Manager::getInstance();
		$loader = Factory::create('PRODUCT');

		$code = $loader->getCodeAfterDelimiter($fields['PRODUCT_XML_ID']);
		$product = $code<>'' ? $loader->getFieldsByExternalId($code):array();
		if(empty($product))
			$product = $loader->getFieldsByExternalId($fields['PRODUCT_XML_ID']);

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
			$ri = new RandomSequence($fields['PRODUCT_XML_ID']);
			$result = array(
				"PRODUCT_ID" => $ri->rand(1000000, 9999999),
				"NAME" => $fields["NAME"],
				"MODULE" => null,
				"PRODUCT_PROVIDER_CLASS" => null,
				"CATALOG_XML_ID" => null,
				"MEASURE_CODE" => $fields["MEASURE_CODE"],
				"MEASURE_NAME" => $fields["MEASURE_NAME"],
				//"DISCOUNT_PRICE" => $item['DISCOUNT']['PRICE'],
			);
		}

		$result["LID"] = $instance->getDefaultSiteId();
		$result["QUANTITY"] = $fields["QUANTITY"];
		$result["CURRENCY"] = $fields["CURRENCY"];
		$result["DELAY"] = "N";
		$result["CAN_BUY"] = "Y";
		$result["IGNORE_CALLBACK_FUNC"] = "Y";
		$result["PRODUCT_XML_ID"] = $fields["PRODUCT_XML_ID"];
		$result["XML_ID"] = $fields["XML_ID"];

		$result["PRICE"] = $fields["PRICE"];

		$result["VAT_RATE"] = $fields["VAT_RATE"];
		$result["VAT_INCLUDED"] = $fields["VAT_INCLUDED"];

		return $result;
	}

	private function prepareFieldsShipment($item)
	{
		// т.к. сопоставление служб доставок через xml подразумевает передачу суммы от БУС в Б24 (а не расчет)
		// принудительно указываем что цена кастомная
		$item['CUSTOM_PRICE_DELIVERY'] = 'Y';

		return $item;
	}

	public function importAction(array $fields)
	{
		$result = new Result();

		self::setFlagActionImport();

		$fields = $this->prepareFieldsImport($fields);

		LoggerDiag::addMessage('ORDER_IMPORT_ACTION_WITH_RESOLVE_EXTERNAL_ID_TO_INTERNAL_ID', var_export($fields, true));

		$r = $this->resolveExternalIdToInternalId($fields);

		if($r->isSuccess())
		{
			$result = $this->modifyAction($r->getData()['DATA']);
		}
		else
		{
			$this->addErrors($r->getErrors());
		}

		if(count($this->getErrors())>0)
		{
			LoggerDiag::addMessage('ORDER_IMPORT_ACTION_WITH_RESOLVE_EXTERNAL_ID_TO_INTERNAL_ID_ERROR', var_export($this->getErrors(), true));
			return null;
		}
		else
		{
			LoggerDiag::addMessage('ORDER_IMPORT_ACTION_WITH_RESOLVE_EXTERNAL_ID_TO_INTERNAL_ID_SUCCESS');
			return $result;
		}
	}

	public function prepareFieldsImport($fields)
	{
		$orderFields = [
			'USER_ID',
			'CURRENCY',
			'LID',
			'PERSON_TYPE_XML_ID',
			'STATUS_XML_ID',
			'CANCELED',
			'REASON_CANCELED',
			'COMMENTS',
			'XML_ID',
			'ID',
		];

		$orderPropertyValuesFields = [
			'NAME',
			'CODE',
			'ORDER_PROPS_XML_ID',
			'VALUE',
			'ORDER_PROPS_ID'
		];

		$basketItemFields = [
			'PRODUCT_XML_ID',
			'NAME',
			'MEASURE_CODE',
			'MEASURE_NAME',
			'QUANTITY',
			'CURRENCY',
			'XML_ID',
			'ID',
			'PRICE',
			'VAT_RATE',
			'VAT_INCLUDED',
		];

		$basketItemPropertiesFields = [
			'NAME',
			'VALUE',
			'CODE',
			'XML_ID'
		];

		$paymentFields = [
			'PAY_SYSTEM_XML_ID',
			'PAY_SYSTEM_ID',
			'PAID',
			'PAY_VOUCHER_NUM',
			'PAY_VOUCHER_DATE',
			'XML_ID',
			'ID',
			'SUM',
			'IS_RETURN',
			'PAY_RETURN_NUM',
			'PAY_RETURN_DATE',
			'PAY_RETURN_COMMENT',
			'COMMENTS',
		];

		$shipmentFields = [
			'BASE_PRICE_DELIVERY',
			'PRICE_DELIVERY',
			'ALLOW_DELIVERY',
			'DEDUCTED',
			'REASON_UNDO_DEDUCTED',
			'DELIVERY_DOC_NUM',
			'DELIVERY_DOC_DATE',
			'TRACKING_NUMBER',
			'XML_ID',
			'ID',
			'CANCELED',
			'COMMENTS',
			'STATUS_XML_ID',
			'STATUS_ID',
			'DELIVERY_XML_ID',
			'DELIVERY_ID',
		];

		$shipmentItemsFields = [
			'BASKET_ID',
			'QUANTITY',
			'XML_ID',
		];

		$result['ORDER'] = array_intersect_key($fields['ORDER'], array_flip($orderFields));

		if(isset($fields['ORDER']['PROPERTY_VALUES']))
		{
			foreach($fields['ORDER']['PROPERTY_VALUES'] as $k=>$v)
				$result['ORDER']['PROPERTY_VALUES'][$k] = array_intersect_key($v, array_flip($orderPropertyValuesFields));
		}

		if(isset($fields['ORDER']['BASKET_ITEMS']))
		{
			foreach($fields['ORDER']['BASKET_ITEMS'] as $k=>$item)
			{
				$result['ORDER']['BASKET_ITEMS'][$k] = array_intersect_key($item, array_flip($basketItemFields));

				if(isset($item['PROPERTIES']))
				{
					foreach($item['PROPERTIES'] as $kProps=>$pros)
					{
						$result['ORDER']['BASKET_ITEMS'][$k]['PROPERTIES'][$kProps] = array_intersect_key($pros, array_flip($basketItemPropertiesFields));
					}
				}
			}
		}

		if(isset($fields['ORDER']['PAYMENTS']))
		{
			foreach($fields['ORDER']['PAYMENTS'] as $k=>$payment)
			{
				$result['ORDER']['PAYMENTS'][$k] = array_intersect_key($payment, array_flip($paymentFields));
			}
		}

		if(isset($fields['ORDER']['SHIPMENTS']))
		{
			foreach($fields['ORDER']['SHIPMENTS'] as $k=>$shipment)
			{
				$result['ORDER']['SHIPMENTS'][$k] = array_intersect_key($shipment, array_flip($shipmentFields));

				if(isset($shipment['SHIPMENT_ITEMS']))
				{
					foreach($shipment['SHIPMENT_ITEMS'] as $kShipmentItem=>$shipmentItem)
					{
						$result['ORDER']['SHIPMENTS'][$k]['SHIPMENT_ITEMS'][$kShipmentItem] = array_intersect_key($shipmentItem, array_flip($shipmentItemsFields));
					}
				}
			}
		}

		return $result;
	}

	protected function checkPermissionEntity($name)
	{
		if($name == 'getdeliveryidlist'
			|| $name == 'getpayments'
			|| $name == 'getpaysystemidlist'
			|| $name == 'getprintedchecks'
			|| $name == 'getshipments'
			|| $name == 'getbasket'
			|| $name == 'getcurrency'
			|| $name == 'getdateinsert'
			|| $name == 'getdeliverylocation'
			|| $name == 'getapplydiscount'
			|| $name == 'getpersontypeid'
			|| $name == 'getprice'
			|| $name == 'getpropertyvalues'
			|| $name == 'getsiteid'
			|| $name == 'getsumpaid'
			|| $name == 'gettaxlist'
			|| $name == 'gettaxlocation'
			|| $name == 'gettaxprice'
			|| $name == 'gettaxvalue'
			|| $name == 'getuserid'
			|| $name == 'getvatrate'
			|| $name == 'getvatsum'
			|| $name == 'iscanceled'
			|| $name == 'isexternal'
			|| $name == 'ismarked'
			|| $name == 'ispaid'
			|| $name == 'isshipped'
			|| $name == 'isusedvat'
		)
		{
			$r = $this->checkReadPermissionEntity();
		}
		elseif($name == 'import'
			|| $name == 'importdelete'
		)
		{
			$r = $this->checkModifyPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
		return $r;
	}
}