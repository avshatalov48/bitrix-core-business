<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Sale\Delivery\Services,
	Bitrix\Sale\Delivery\Requests,
	Bitrix\Main\Localization\Loc;


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/internals/input.php");

class CSaleDeliveryRequestProcessComponent extends CBitrixComponent
{
	public function checkParams($params)
	{
		if(!isset($params["SHIPMENTS_ERRORS"]))
			throw new \Bitrix\Main\ArgumentNullException('params["SHIPMENTS_ERRORS"]');

		if(!isset($params["DELIVERY_ID"]))
			throw new \Bitrix\Main\ArgumentNullException('params["DELIVERY_ID"]');

		if(!isset($params["DELIVERY_REQUESTS"]))
			throw new \Bitrix\Main\ArgumentNullException('params["DELIVERY_REQUESTS"]');

		if(!isset($params["SHIPMENTS_COUNT"]))
			throw new \Bitrix\Main\ArgumentNullException('params["SHIPMENTS_COUNT"]');

		if(!isset($params["WEIGHT"]))
			throw new \Bitrix\Main\ArgumentNullException('params["WEIGHT"]');

		if(!isset($params["ACTION"]))
			throw new \Bitrix\Main\ArgumentNullException('params["ACTION"]');

		return true;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		$params["WEIGHT"] = round(floatval($params["WEIGHT"]), 2);
		$params["SHIPMENTS_COUNT"] = intval($params["SHIPMENTS_COUNT"]);
		$params["DELIVERY_ID"] = intval($params["DELIVERY_ID"]);
		$params["SHIPMENTS_ERRORS"] = intval($params["SHIPMENTS_ERRORS"]);

		return $params;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		if ($APPLICATION->GetGroupRight("sale") < "U")
		{
			ShowError(Loc::getMessage('SALE_CSDRP_ACCESS_DENIED'));
			return;
		}

		if (!\Bitrix\Main\Loader::includeModule('sale'))
		{
			ShowError(Loc::getMessage('SALE_CSDRP_SALE_NOT_INCLUDED'));
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

		$res = Services\Table::getList(array(
			'filter' => array(
				'=ID' => $this->arParams['DELIVERY_ID']
			),
			'select' => array('*', 'PARENT_CLASS_NAME' => 'PARENT.CLASS_NAME')
		));

		if($fields = $res->fetch())
		{
			if(!Services\Manager::isDeliveryServiceClassValid($fields['CLASS_NAME']))
			{
				ShowError('Delivery class "'.$fields['CLASS_NAME'].'" is not valid');
				return;
			}

			$deliveryId = $fields['ID'];

			if(!($deliveryRequestHandler = Requests\Manager::getDeliveryRequestHandlerByDeliveryId($deliveryId)))
			{
				ShowError(Loc::getMessage('SALE_CSDRP_DELIVERY_NOT_SUPPORTED', array('#DELIVERY_ID#' => $fields['ID'])));
				return;
			}

			$deliveryId = $deliveryRequestHandler->getHandlingDeliveryServiceId();

			if(!($delivery = Services\Manager::getObjectById($deliveryId)))
			{
				ShowError(Loc::getMessage('SALE_CSDRP_OBJECT_DELIVERY_ERROR', array('#DELIVERY_ID#' => $fields['ID'])));
				return;
			}

			$logo = intval($fields['LOGOTIP']) > 0 ? CFile::GetFileArray($fields['LOGOTIP']) : array();

			$this->arResult['DELIVERY'] = array(
				'NAME' => htmlspecialcharsbx($delivery->getNameWithParent()),
				'LOGO_SRC' => isset($logo['SRC']) ? $logo['SRC'] : "/bitrix/images/sale/logo-default-d.gif",
				'EDIT_LINK' => '/bitrix/admin/sale_delivery_service_edit.php?ID='.$deliveryId.'&lang='.LANGUAGE_ID,
				'ID' => $deliveryId
			);
		}

		$this->arResult['DELIVERY_REQUESTS'] = array();

		foreach($this->arParams['DELIVERY_REQUESTS'] as $requestId => $params)
		{
			if($params['SHIPMENTS_COUNT'] > 0)
			{
				$this->arResult['DELIVERY_REQUESTS'][$requestId] = array(
					'VIEW_LINK' => Requests\Helper::getRequestViewLink($requestId),
					'SHIPMENTS_COUNT' => $params['SHIPMENTS_COUNT']
				);
			}
		}

		$this->includeComponentTemplate();
	}
}