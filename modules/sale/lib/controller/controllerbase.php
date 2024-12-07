<?php


namespace Bitrix\Sale\Controller;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Rest\Integration\Controller\Base;
use Bitrix\Sale\Rest\View\SaleViewManager;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;
use Bitrix\Sale\TradeBindingEntity;

class ControllerBase extends Base
{
	protected function createViewManager(Action $action)
	{
		return new SaleViewManager($action);
	}

	protected static function getNavData($start, $orm = false)
	{
		if($start >= 0)
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT, 'offset' => intval($start)]
				:['nPageSize' => \IRestService::LIST_LIMIT, 'iNumPage' => intval($start / \IRestService::LIST_LIMIT) + 1]
			);
		}
		else
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT]
				:['nTopCount' => \IRestService::LIST_LIMIT]
			);
		}
	}

	protected static function getApplication()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		return $APPLICATION;
	}

	protected function isCrmModuleInstalled()
	{
		return ModuleManager::isModuleInstalled('crm');
	}

	public function getBuilder(SettingsContainer $settings=null)
	{
		$settings = $settings === null? $this->getSettingsContainerDefault():$settings;

		return ($this->isCrmModuleInstalled() && Loader::includeModule('crm'))
			? new \Bitrix\Crm\Order\Builder\OrderBuilderRest($settings)
			: new \Bitrix\Sale\Helpers\Order\Builder\OrderBuilderRest($settings);
	}

	protected function getSettingsContainerDefault()
	{
		return new SettingsContainer([
			'deleteClientsIfNotExists' => true,
			'deleteTradeBindingIfNotExists' => true,
			'deletePaymentIfNotExists' => true,
			'deleteShipmentIfNotExists' => true,
			'deleteShipmentItemIfNotExists' => true,
			'deletePropertyValuesIfNotExists' => true,
			'createDefaultPaymentIfNeed' => false,
			'createDefaultShipmentIfNeed' => false,
			'createUserIfNeed' => false,
			'cacheProductProviderData' => false,
			'propsFiles' => $this->getFielsPropertyValuesFromRequest(),
			'acceptableErrorCodes' => []
		]);
	}

	protected function getFielsPropertyValuesFromRequest()
	{
		$orderProperties = [];

		if(isset($this->request->getFileList()['fields']['PROPERTIES']))
		{
			foreach ($this->request->getFileList()['fields']['PROPERTIES'] as $orderPropId => $arFileData)
			{
				if (is_array($arFileData))
				{
					foreach ($arFileData as $param_name => $value)
					{
						if (is_array($value))
						{
							foreach ($value as $nIndex => $val)
							{
								if ($arFileData["name"][$nIndex] <> '')
									$orderProperties[$orderPropId][$nIndex][$param_name] = $val;
							}
						}
						else
							$orderProperties[$orderPropId][$param_name] = $value;
					}
				}
			}
		}
		return $orderProperties;
	}

	protected function toArray(\Bitrix\Sale\Order $order, array $fields=[])
	{
		//добавляем те поля, к существующим полям сущности, которые у неё отсутствуют
		$fields = array_merge($fields, $this->getAdditionalFields($order));

		if($this->isCrmModuleInstalled() && Loader::includeModule('crm'))
		{
			$director = new \Bitrix\Crm\Order\Rest\Normalizer\Director();
			$normalizer = new \Bitrix\Crm\Order\Rest\Normalizer\ObjectNormalizer($fields);
		}
		else
		{
			$director = new \Bitrix\Sale\Rest\Normalizer\Director();
			$normalizer = new \Bitrix\Sale\Rest\Normalizer\ObjectNormalizer($fields);
		}

		return $director->normalize($normalizer, $order);
	}

	private function getAdditionalFields(\Bitrix\Sale\Order $order)
	{
		$ixInternal = [];
		//region fill internal Index
		foreach(\Bitrix\Sale\PersonType::getList(['select'=>['ID', 'XML_ID']]) as $row)
			$ixInternal['personType'][$row['ID']] = $row['XML_ID'];

		foreach(\Bitrix\Sale\OrderStatus::getList(['select'=>['ID', 'XML_ID']]) as $row)
			$ixInternal['orderStatus'][$row['ID']] = $row['XML_ID'];

		foreach(\Bitrix\Sale\Property::getList(['select'=>['ID', 'XML_ID']])->fetchAll() as $row)
			$ixInternal['properties'][$row['ID']] = $row['XML_ID'];

		foreach(\Bitrix\Sale\PaySystem\Manager::getList(['select'=>['ID', 'XML_ID', 'IS_CASH']])->fetchAll() as $row)
		{
			$ixInternal['paySystems'][$row['ID']]['XML_ID'] = $row['XML_ID'];
			$ixInternal['paySystems'][$row['ID']]['IS_CASH'] = $row['IS_CASH'];
		}

		foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $row)
			$ixInternal['deliverySystems'][$row['ID']] = $row['XML_ID'];

		foreach(\Bitrix\Sale\DeliveryStatus::getList(['select'=>['ID', 'XML_ID']]) as $row)
			$ixInternal['deliveryStatus'][$row['ID']] = $row['XML_ID'];

		foreach(\Bitrix\Sale\TradingPlatformTable::getList(['select'=>['ID', 'XML_ID']])->fetchAll() as $row)
			$ixInternal['tradingPlatform'][$row['ID']] = $row['XML_ID'];
		//endregion

		$r['ORDER'][$order->getInternalId()] = [
			'PERSON_TYPE_XML_ID'=>$ixInternal['personType'][$order->getPersonTypeId()],
			'STATUS_XML_ID'=>$ixInternal['orderStatus'][$order->getField('STATUS_ID')]];

		foreach ($order->getPropertyCollection() as $property)
			$r['PROPERTIES'][$property->getInternalIndex()] = ['ORDER_PROPS_XML_ID'=>$ixInternal['properties'][$property->getPropertyId()]];

		foreach ($order->getPaymentCollection() as $payment)
			$r['PAYMENTS'][$payment->getInternalIndex()] = [
				'PAY_SYSTEM_XML_ID'=>$ixInternal['paySystems'][$payment->getPaymentSystemId()]['XML_ID'],
				'PAY_SYSTEM_IS_CASH'=>$ixInternal['paySystems'][$payment->getPaymentSystemId()]['IS_CASH']
			];

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			$shipmentIndex = $shipment->getInternalIndex();
			$r['SHIPMENTS'][$shipmentIndex] = [
				'DELIVERY_XML_ID'=>$ixInternal['deliverySystems'][$shipment->getDeliveryId()],
				'STATUS_XML_ID'=>$ixInternal['deliveryStatus'][$shipment->getField('STATUS_ID')]];
		}

		/** @var TradeBindingEntity $binding */
		foreach ($order->getTradeBindingCollection() as $binding)
			if($binding->getTradePlatform() !== null)
				$r['TRADE_BINDINGS'][$binding->getInternalIndex()] = ['TRADING_PLATFORM_XML_ID'=>$ixInternal['tradingPlatform'][$binding->getTradePlatform()->getId()]];

		return $r;
	}
}