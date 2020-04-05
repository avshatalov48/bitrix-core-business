<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;
use Bitrix\Sale\Result;
use Bitrix\Sale\ShipmentCollection;

class Shipment extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Sale\Shipment::class,
			'shipment',
			function($className, $id) {

				$r = \Bitrix\Sale\Shipment::getList([
					'select'=>['ORDER_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($row = $r->fetch())
				{
					$order = \Bitrix\Sale\Order::load($row['ORDER_ID']);
					$shipment = $order->getShipmentCollection()->getItemById($id);
					if($shipment instanceof \Bitrix\Sale\Shipment)
					{
						return $shipment;
					}
				}
				else
				{
					$this->addError(new Error('shipment is not exists', 201140400001));
				}
				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\Shipment();
		return ['SHIPMENT'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function modifyAction($fields)
	{
		$builder = $this->getBuilder();
		$builder->buildEntityShipments($fields);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		elseif ($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());
			return null;
		}

		//TODO: return $shipment->toArray();
		return ['SHIPMENTS'=>$this->toArray($order)['ORDER']['SHIPMENTS']];
	}

	public function addAction(array $fields)
	{
		$data = [];

		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['SHIPMENTS'] = [$fields];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deleteShipmentIfNotExists' => false,
				'deleteShipmentItemIfNotExists' => false
			])
		);
		$builder->buildEntityShipments($data);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

		$idx=0;
		$collection = $order->getShipmentCollection();
		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach($collection as $shipment)
		{
			if($shipment->getId() <= 0)
			{
				$idx = $shipment->getInternalIndex();
				break;
			}
		}

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		elseif ($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());
			return null;
		}

		/** @var \Bitrix\Sale\Shipment $entity */
		$entity = $order->getShipmentCollection()->getItemByIndex($idx);
		return ['SHIPMENT'=>$this->get($entity)];
	}

	public function updateAction(\Bitrix\Sale\Shipment $shipment, array $fields)
	{
		$data = [];

		$fields['ID'] = $shipment->getId();
		$fields['ORDER_ID'] = $shipment->getParentOrderId();

		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['SHIPMENTS'] = [$fields];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deleteShipmentIfNotExists' => false,
				'deleteShipmentItemIfNotExists' => false
			])
		);
		$builder->buildEntityShipments($data);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

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

		/** @var \Bitrix\Sale\Shipment $entity */
		$entity = $order->getShipmentCollection()->getItemById($shipment->getId());
		return ['SHIPMENT'=>$this->get($entity)];
	}

	public function deleteAction(\Bitrix\Sale\Shipment $shipment)
	{
		$r = $shipment->delete();
		return $this->save($shipment, $r);
	}

	public function getAction(\Bitrix\Sale\Shipment $shipment)
	{
		return ['SHIPMENT'=>$this->get($shipment)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;
		$filter['!SYSTEM'] = 'Y';

		$runtime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'STATUS_TABLE',
				'\Bitrix\Sale\Internals\StatusTable',
				array('=this.STATUS_ID' => 'ref.ID')
			),
			new \Bitrix\Main\Entity\ReferenceField(
				'DELIVERY',
				'\Bitrix\Sale\Delivery\Services\Table',
				array('=this.DELIVERY_ID' => 'ref.ID')
			),
		];

		$shipments = \Bitrix\Sale\Shipment::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
				'runtime' => $runtime
			]
		)->fetchAll();

		return new Page('SHIPMENTS', $shipments, function() use ($select, $filter, $runtime)
		{
			return count(
				\Bitrix\Sale\Shipment::getList(['select'=>$select, 'filter'=>$filter, 'runtime'=>$runtime])->fetchAll()
			);
		});
	}

	public function getAllowDeliveryDateAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getAllowDeliveryDate();
	}

	public function getAllowDeliveryUserIdAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getAllowDeliveryUserId();
	}

	public function getCompanyIdAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getCompanyId();
	}

	public function getCurrencyAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getCurrency();
	}

	public function getDeliveryIdAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getDeliveryId();
	}

	public function getDeliveryNameAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getDeliveryName();
	}

	public function getParentOrderIdAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getParentOrderId();
	}

	public function getPersonTypeIdAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getPersonTypeId();
	}

	public function getPriceAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getPrice();
	}

	public function getShippedDateAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getShippedDate();
	}

	public function getShippedUserIdAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getShippedUserId();
	}

	public function getStoreIdAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getStoreId();
	}

	public function getUnshipReasonAction(\Bitrix\Sale\Shipment $shipment)
	{
		$shipment->getUnshipReason();
	}

	public function getVatRateAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getVatRate();
	}

	public function getVatSumAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getVatSum();
	}

	public function getWeightAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->getWeight();
	}

	public function isAllowDeliveryAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->isAllowDelivery()? 'Y':'N';
	}

	public function isCanceledAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->isCanceled()? 'Y':'N';
	}

	public function isCustomPriceAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->isCustomPrice()? 'Y':'N';
	}

	public function isEmptyAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->isEmpty()? 'Y':'N';
	}

	public function isMarkedAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->isMarked()? 'Y':'N';
	}

	public function isReservedAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->isReserved()? 'Y':'N';
	}

	public function isShippedAction(\Bitrix\Sale\Shipment $shipment)
	{
		return $shipment->isShipped()? 'Y':'N';
	}

	public function setBasePriceDeliveryAction(\Bitrix\Sale\Shipment $shipment, $value, $custom=false)
	{
		$r=$shipment->setBasePriceDelivery($value, $custom);
		return $this->save($shipment, $r);
	}
	//endregion

	private function save(\Bitrix\Sale\Shipment $shipment, Result $r)
	{
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			/** @var ShipmentCollection $collection */
			$collection = $shipment->getCollection();
			$r = $collection->getOrder()->save();
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}
		}

		return $r->isSuccess();
	}

	protected function get(\Bitrix\Sale\Shipment $shipment, array $fields=[])
	{
		$shipments = $this->toArray($shipment->getCollection()->getOrder(), $fields)['ORDER']['SHIPMENTS'];
		foreach ($shipments as $item)
		{
			if($item['ID']==$shipment->getId())
			{
				return $item;
			}
		}
		return [];
	}

	static public function prepareFields($fields)
	{
		$data=null;

		if(isset($fields["SHIPMENTS"]) && is_array($fields["SHIPMENTS"]))
		{
			foreach($fields['SHIPMENTS'] as $k=>$shipmentFormData)
			{
				$data[$k] = $shipmentFormData;
				if(isset($shipmentFormData['SHIPMENT_ITEMS']))
				{
					unset($data[$k]['SHIPMENT_ITEMS']);

					$i=0;
					foreach($shipmentFormData['SHIPMENT_ITEMS'] as $item)
					{
						$shipmentItem = [];

						if(isset($item['ID']) && intval($item['ID'])>0)
						{
							$shipmentItem['ORDER_DELIVERY_BASKET_ID'] = intval($item['ID']);
						}
						else
						{
							if(isset($item['BASKET_ID']))
								$shipmentItem['BASKET_CODE'] = $item['BASKET_ID'];
						}

						if(isset($item['XML_ID']))
							$shipmentItem['XML_ID'] = $item['XML_ID'];

						$shipmentItem['AMOUNT'] = $item['QUANTITY'];

						//$basketCode = $item['BASKET_ID'];
						//unset($item['BASKET_ID']);

						//region fill Id - ShipmentItemStore
						$storesInfo = isset($item['BARCODE_INFO'])? $item['BARCODE_INFO']:[];
						foreach($storesInfo as &$storeInfo)
						{
							if(isset($storeInfo['BARCODE']))
							{
								foreach ($storeInfo['BARCODE'] as &$barCode)
									$barCode['ID'] = isset($barCode['ID'])?$barCode['ID']:0;
							}
						}
						//endregion
/*
						$data[$k]['PRODUCT'][$basketCode] =	[
							'AMOUNT'=>$item['QUANTITY'],
							'BASKET_CODE'=>$basketCode,
							'XML_ID'=>$item['XML_ID'],
							'BARCODE_INFO'=> $storesInfo
						];
*/
						$data[$k]['PRODUCT'][] = $shipmentItem + ['BARCODE_INFO'=> $storesInfo];
					}
				}
				else
				{
					$data[$k]['PRODUCT'] = false;
				}
			}
		}

		return is_array($data)?['SHIPMENT'=>$data]:[];
	}

	protected function checkPermissionEntity($name)
	{
		if($name == 'getallowdeliverydate'
			|| $name == 'getallowdeliveryuserid'
			|| $name == 'getcompanyid'
			|| $name == 'getcurrency'
			|| $name == 'getdeliveryid'
			|| $name == 'getdeliveryname'
			|| $name == 'getparentorderid'
			|| $name == 'getpersontypeid'
			|| $name == 'getprice'
			|| $name == 'getphippeddate'
			|| $name == 'getstoreid'
			|| $name == 'getunshipreason'
			|| $name == 'getvatrate'
			|| $name == 'getvatsum'
			|| $name == 'getweight'
			|| $name == 'getshippeddate'
			|| $name == 'getshippeduserid'
			|| $name == 'isallowdelivery'
			|| $name == 'iscanceled'
			|| $name == 'iscustomprice'
			|| $name == 'isempty'
			|| $name == 'ismarked'
			|| $name == 'isreserved'
			|| $name == 'isshipped'
		)
		{
			$r = $this->checkReadPermissionEntity();
		}
		elseif($name == 'setbasepricedelivery')
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