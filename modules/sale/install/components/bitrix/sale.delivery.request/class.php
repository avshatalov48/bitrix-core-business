<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Sale\Delivery\Services,
	Bitrix\Sale\Delivery\Requests,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Internals;


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/internals/input.php");

class CSaleDeliveryRequestComponent extends CBitrixComponent
{
	public function checkParams($params)
	{
		if(!isset($params["SHIPMENT_IDS"]))
			throw new \Bitrix\Main\ArgumentNullException('params["SHIPMENT_IDS"]');

		return true;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		if (!isset($params["ACTION"]))
			$params["ACTION"] = "";

		return $params;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		if ($APPLICATION->GetGroupRight("sale") < "U")
		{
			ShowError(Loc::getMessage('SALE_CSDR_ACCESS_DENIED'));
			return;
		}

		if (!\Bitrix\Main\Loader::includeModule('sale'))
		{
			ShowError(Loc::getMessage('SALE_CSDR_SALE_NOT_INCLUDED'));
			return;
		}

		try
		{
			$this->checkParams($this->arParams);
		}
		catch(\Exception $e)
		{
			ShowError($e->getMessage());
			return;
		}

		$deliveries = array();

		$res = Internals\ShipmentTable::getList(array(
			'filter' => array(
				'=ID' => $this->arParams['SHIPMENT_IDS']
			),
			'select' => array(
				'*',
				'DELIVERY_CLASS' => 'DELIVERY.CLASS_NAME',
				'DELIVERY_PARENT_ID' => 'DELIVERY.PARENT_ID',
				'DELIVERY_PARENT_CLASS_NAME' => 'DELIVERY.PARENT.CLASS_NAME',
				'DELIVERY_LOGOTIP' => 'DELIVERY.LOGOTIP'
			)
		));

		while($shipmentFields = $res->fetch())
		{
			if(!Services\Manager::isDeliveryServiceClassValid($shipmentFields['DELIVERY_CLASS']))
				continue;

			$deliveryId = $shipmentFields['DELIVERY_ID'];

			if(!($deliveryRequestHandler = Requests\Manager::getDeliveryRequestHandlerByDeliveryId($deliveryId)))
				continue;

			$deliveryId = $deliveryId = $deliveryRequestHandler->getHandlingDeliveryServiceId();

			if(!isset($deliveries[$deliveryId]))
			{
				if(!($delivery = Services\Manager::getObjectById($deliveryId)))
					continue;

				$logo = intval($shipmentFields['DELIVERY_LOGOTIP']) > 0 ? CFile::GetFileArray($shipmentFields['DELIVERY_LOGOTIP']) : array();

				$deliveries[$deliveryId] = array(
					'NAME' => htmlspecialcharsbx($delivery->getNameWithParent()),
					'SHIPMENT_IDS' => array(),
					'WEIGHT' => 0,
					'LOGO_SRC' => isset($logo['SRC']) ? $logo['SRC'] : "/bitrix/images/sale/logo-default-d.gif",
					'EDIT_LINK' => '/bitrix/admin/sale_delivery_service_edit.php?ID='.$deliveryId.'&lang='.LANGUAGE_ID,
					'DELIVERY_ID' => $deliveryId
				);
			}

			$deliveries[$deliveryId]['SHIPMENT_IDS'][] = $shipmentFields['ID'];

			$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var \Bitrix\Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			if(!($order = $orderClass::load($shipmentFields['ORDER_ID'])))
				continue;

			if(!($shipmentCollection = $order->getShipmentCollection()))
				continue;

			/** @var \Bitrix\Sale\Shipment $shipment */
			if(!($shipment = $shipmentCollection->getItemById($shipmentFields['ID'])))
				continue;

			$deliveries[$deliveryId]['WEIGHT'] += round($shipment->getWeight()/1000, 2);
		}

		$this->arResult['DELIVERIES'] = $deliveries;
		$this->arResult['AJAX_URL'] = $this->getPath()."/ajax.php";
		$this->includeComponentTemplate();
	}
}