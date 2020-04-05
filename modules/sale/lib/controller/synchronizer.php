<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Crm\Timeline\OrderController;
use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Sale\Delivery\Services\EmptyDeliveryService;
use Bitrix\Sale\DeliveryStatus;
use Bitrix\Sale\OrderStatus;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\Result;
use Bitrix\Sale;

class Synchronizer extends Engine\Controller
{
	protected function isB24()
	{
		return ModuleManager::isModuleInstalled('crm');
	}

	public function getDefaultSettingsAction()
	{
		return $this->getSettings();
	}

	public function setDefaultSettingsAction()
	{
		$r = new Result();
		$manager = new \Bitrix\Sale\Rest\Synchronization\Manager();



		$personId = \Bitrix\Sale\PersonType::getList(['select'=>['ID', 'NAME'],'order'=>'ID', 'limit'=>1])->fetch()['ID'];

		if((int)$personId>0)
		{
			$manager->setDefaultPersonTypeId($personId);
		}
		else
		{
			$r->addError(new Error('person type not found'));
		}

		$ps = Manager::getList(
			[
				'select'=>['ID'],
				'filter'=>['!ID'=>Manager::getInnerPaySystemId(), 'ENTITY_REGISTRY_TYPE'=>'ORDER'],
				'order'=>['ID'=>'ASC'],
				'limit'=>1
			]
		)->fetchAll();

		$paySystemId = isset($ps[0])? $ps[0]['ID']:0;
		if((int)$paySystemId>0)
		{
			$manager->setDefaultPaySystemId($paySystemId);
		}
		else
		{
			$r->addError(new Error('paysystem not found'));
		}

		$deliverySystemId = EmptyDeliveryService::getEmptyDeliveryServiceId();
		if((int)$deliverySystemId>0)
		{
			$manager->setDefaultDeliverySystemId($deliverySystemId);
		}
		else
		{
			$r->addError(new Error('deliverysystem not found'));
		}

		$manager->setDefaultSiteId(SITE_ID);
		$manager->setDefaultDeliveryStatusId(DeliveryStatus::getInitialStatus());
		$manager->setDefaultOrderStatusId(OrderStatus::getInitialStatus());

		if($r->isSuccess())
		{
			$manager->activate();

			return true;
		}
		else
		{
			$manager->deactivate();
			$this->addErrors($r->getErrors());

			return null;
		}
	}

	public function isActiveAction()
	{
		$instance = \Bitrix\Sale\Rest\Synchronization\Manager::getInstance();
		return $instance->isActive();
	}

	protected function getSettings()
	{
		$manager = new \Bitrix\Sale\Rest\Synchronization\Manager();

		$internal = [];
		foreach(\Bitrix\Sale\PersonType::getList(['select'=>['ID', 'NAME']]) as $row)
			$internal['PERSON_TYPE'][$row['ID']] = $row;

		foreach(\Bitrix\Sale\PaySystem\Manager::getList(['select'=>['ID', 'NAME']])->fetchAll() as $row)
			$internal['PAY_SYSTEMS'][$row['ID']] = $row;

		foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $row)
			$internal['DELIVERY_SYSTEMS'][$row['ID']] = $row;

		$r = \CSite::GetList($by,$order);
		while ($row = $r->fetch())
			$internal['SITES'][$row['ID']] = $row;

		foreach(OrderStatus::getList(['select' => ['*', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'],
			'filter' => [
				'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID
			]]) as $row)
			$internal['ORDER_STATUSES'][$row['ID']] = $row;

		foreach(DeliveryStatus::getList(['select' => ['*', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'],
			'filter' => [
				'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID
			]]) as $row)
			$internal['DELIVERY_STATUSES'][$row['ID']] = $row;

		$catalogList = [];
		if(\Bitrix\Main\Loader::includeModule('catalog'))
		{
			$r = \Bitrix\Catalog\CatalogIblockTable::getList([
				'select' => ['IBLOCK_ID', 'IBLOCK.NAME'],
				'filter' => ['=IBLOCK.ACTIVE'=>'Y']]);

			while($row = $r->fetch())
				$catalogList[] = ['id'=>$row['IBLOCK_ID'], 'name'=>$row['CATALOG_CATALOG_IBLOCK_IBLOCK_NAME']];
		}

		$site=[];
		if(isset($internal['SITES'][$manager->getDefaultSiteId()]))
			$site = $internal['SITES'][$manager->getDefaultSiteId()];

		$paySystem=[];
		if(isset($internal['PAY_SYSTEMS'][$manager->getDefaultPaySystemId()]))
			$paySystem = $internal['PAY_SYSTEMS'][$manager->getDefaultPaySystemId()];

		$deliverySystem=[];
		if(isset($internal['DELIVERY_SYSTEMS'][$manager->getDefaultDeliverySystemId()]))
			$deliverySystem = $internal['DELIVERY_SYSTEMS'][$manager->getDefaultDeliverySystemId()];

		$personType=[];
		if(isset($internal['PERSON_TYPE'][$manager->getDefaultPersonTypeId()]))
			$personType = $internal['PERSON_TYPE'][$manager->getDefaultPersonTypeId()];

		$orderStatuses=[];
		if(isset($internal['ORDER_STATUSES'][$manager->getDefaultOrderStatusId()]))
			$orderStatuses = $internal['ORDER_STATUSES'][$manager->getDefaultOrderStatusId()];

		$deliveryStatus=[];
		if(isset($internal['DELIVERY_STATUSES'][$manager->getDefaultDeliveryStatusId()]))
			$deliveryStatus = $internal['DELIVERY_STATUSES'][$manager->getDefaultDeliveryStatusId()];


		return [
			'synchronizer'=>[
				'isActive'=>$manager->isActive() && $manager->checkDefaultSettings()->isSuccess()?'Y':'N',
				'site'=>count($site)>0? ['id'=>$site['ID'], 'name'=>$site['NAME']]:[],
				'paySystem'=>count($paySystem)>0? ['id'=>$paySystem['ID'], 'name'=>$paySystem['NAME']]:[],
				'deliverySystem'=>count($deliverySystem)>0? ['id'=>$deliverySystem['ID'], 'name'=>$deliverySystem['NAME']]:[],
				'personType'=>count($personType)>0? ['id'=>$personType['ID'], 'name'=>$personType['NAME']]:[],
				'orderStatus'=>count($orderStatuses)>0? ['id'=>$orderStatuses['ID'], 'name'=>$orderStatuses['NAME']]:[],
				'deliveryStatus'=>count($deliveryStatus)>0? ['id'=>$deliveryStatus['ID'], 'name'=>$deliveryStatus['NAME']]:[],
				'catalogs'=>$catalogList
			],
		];
	}

	public function addTimelineAfterOrderModifyAction($orderId, array $params)
	{
		if ($this->isB24())
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			/** @var \Bitrix\Sale\Order $className */
			$order = $orderClass::load($orderId);
			if($order)
			{
				OrderController::getInstance()->afterModifyExternalEntity($order->getId(), ['TYPE'=>$params['type'], 'MESSAGE'=>$params['message']]);
			}
		}
	}
}