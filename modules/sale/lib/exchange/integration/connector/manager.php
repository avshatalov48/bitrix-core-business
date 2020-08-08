<?php


namespace Bitrix\Sale\Exchange\Integration\Connector;

use Bitrix\Main\EventManager;
use Bitrix\Sale\Exchange\Integration\App\IntegrationB24;
use Bitrix\Sale\Exchange\Integration\Connector\Placement\IntegrationB24NewOrder;
use Bitrix\Sale\Exchange\Integration\Connector\Placement\IntegrationB24Registry;
use Bitrix\Sale\Exchange\Integration\Service\Scenarios;
use Bitrix\Sale\Exchange\Integration\Token;

class Manager
{
	protected $app;

	public function __construct()
	{
		$this->app = new IntegrationB24();
	}

	public function isOn()
	{
		return Token::getExistsByGuid($this->app->getCode());
	}

	public function delete()
	{
		$registry = new IntegrationB24Registry($this->app);
		$neworder = new IntegrationB24NewOrder($this->app);

		$result['PLACEMENTS'][] = [
			'PLACEMENT'=>$registry->getPlacement(),
			'HANDLER'=>$registry->getPlacmentHandler()
		];

		$result['PLACEMENTS'][] = [
			'PLACEMENT'=>$neworder->getPlacement(),
			'HANDLER'=>$neworder->getPlacmentHandler()
		];

		$result['OPTIONS'] = ['url'=>$this->app->getAppUrl()];

		(new Scenarios\Connector())->delete($result);

		Token::delete($this->app->getCode());
	}

	public function add()
	{
		$registry = new IntegrationB24Registry($this->app);
		$neworder = new IntegrationB24NewOrder($this->app);

		$result['PLACEMENTS'][] = [
			'PLACEMENT'=>$registry->getPlacement(),
			'HANDLER'=>$registry->getPlacmentHandler(),
			'GROUP_NAME'=>$registry->getGroupName(),
			'TITLE'=>$registry->getTitle(),
		];

		$result['PLACEMENTS'][] = [
			'PLACEMENT'=>$neworder->getPlacement(),
			'HANDLER'=>$neworder->getPlacmentHandler(),
			'GROUP_NAME'=>$neworder->getGroupName(),
			'TITLE'=>$neworder->getTitle(),
		];

		$result['OPTIONS'] = ['url'=>$this->app->getAppUrl()];

		(new Scenarios\Connector())->add($result);

		static::registerEvents();
	}

	protected static function registerEvents()
	{
		$eventManager = EventManager::getInstance();

		//region Order handlerCallback
		$eventManager->registerEventHandler(
			"sale",
			"OnSaleOrderSaved",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Handler',
			'handlerCallbackOnSaleOrderSaved');
		$eventManager->registerEventHandler(
			"sale",
			"OnSaleStatusOrderChange",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Order',
			'statusNotify');
		$eventManager->registerEventHandler(
			"sale",
			"OnSaleOrderCanceled",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Order',
			'canceledNotify');
		//endregion
		//region Payment handlerCallback
		$eventManager->registerEventHandler(
			"sale",
			"OnPaymentPaid",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Payment',
			'paidNotify');
		//endregion
		//region Shipment handlerCallback
		$eventManager->registerEventHandler(
			"sale",
			"OnSaleStatusShipmentChange",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Shipment',
			'statusNotify');
		$eventManager->registerEventHandler(
			"sale",
			"OnShipmentAllowDelivery",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Shipment',
			'allowDeliveryNotify');
		$eventManager->registerEventHandler(
			"sale",
			"OnShipmentDeducted",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Shipment',
			'deductedNotify');
		//endregion
	}

	protected static function unRegisterEvents()
	{
		//region Order handlerCallback
		UnRegisterModuleDependences(
			"sale",
			"OnSaleOrderSaved",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Handler',
			'handlerCallbackOnSaleOrderSaved');
		UnRegisterModuleDependences(
			"sale",
			"OnSaleStatusOrderChange",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Order',
			'statusNotify');
		UnRegisterModuleDependences(
			"sale",
			"OnSaleOrderCanceled",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Order',
			'canceledNotify');
		//endregion
		//region Payment handlerCallback
		UnRegisterModuleDependences(
			"sale",
			"OnPaymentPaid",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Payment',
			'paidNotify');
		//endregion
		//region Shipment handlerCallback
		UnRegisterModuleDependences(
			"sale",
			"OnSaleStatusShipmentChange",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Shipment',
			'statusNotify');
		UnRegisterModuleDependences(
			"sale",
			"OnShipmentAllowDelivery",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Shipment',
			'allowDeliveryNotify');
		UnRegisterModuleDependences(
			"sale",
			"OnShipmentDeducted",
			"sale",
			'\Bitrix\Sale\Exchange\Integration\Timeline\Shipment',
			'deductedNotify');
		//endregion
	}
}

