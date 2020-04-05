<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $USER, $APPLICATION;
IncludeModuleLangFile(__FILE__);
$SALE_RIGHT = $APPLICATION->GetGroupRight("sale");
if ($SALE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$ORDER_ID = (isset($_REQUEST['ORDER_ID']) ? (int)$_REQUEST['ORDER_ID'] : 0);

function GetRealPath2Report($rep_name)
{
	$rep_name = str_replace("\0", "", $rep_name);
	$rep_name = preg_replace("#[\\\\\\/]+#", "/", $rep_name);
	$rep_name = preg_replace("#\\.+[\\\\\\/]#", "", $rep_name);

	$rep_file_name = $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$rep_name;
	if (!file_exists($rep_file_name))
	{
		$rep_file_name = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$rep_name;
		if (!file_exists($rep_file_name))
		{
			return "";
		}
	}

	return $rep_file_name;
}


/*
 * distribution of discount products
 *
 * array $arBasket - user basket
 * float $discount - discount of order
 * float $priceTotal - summa of basket price
 */
function GetUniformDestribution($arBasket, $discount, $priceTotal)
{
	foreach ($arBasket as $key => $val)
	{
		$val["PRICE_DEFAULT"] = $val["PRICE"];
		$val["DISCOUNT_RATION_PERCENT"] = round(($val["PRICE"] * 100) / $priceTotal, 5);
		$val["DISCOUNT_RATION_VALUE"] = round(($discount * $val["DISCOUNT_RATION_PERCENT"] / 100), 5);
		$val["PRICE"] -= $val["DISCOUNT_RATION_VALUE"];
		$arBasket[$key] = $val;
	}
	return $arBasket;
}

if (CModule::IncludeModule("sale"))
{
	if ($arOrder = CSaleOrder::GetByID($ORDER_ID))
	{
		$order = \Bitrix\Sale\Order::load($ORDER_ID);
		$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));


		if (isset($_REQUEST['SHIPMENT_ID']) && intval($_REQUEST['SHIPMENT_ID']) > 0)
		{
			$shipmentId = $_REQUEST['SHIPMENT_ID'];
			$res = \Bitrix\Sale\Internals\ShipmentTable::getList(array(
				'select' => array('PRICE_DELIVERY', 'STATUS_ID'),
				'filter' => array(
					'ID' => $_REQUEST['SHIPMENT_ID']
				)
			));
			$data = $res->fetch();

			$allowedStatusesDeliveryView = \Bitrix\Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
			if(!in_array($data["STATUS_ID"], $allowedStatusesDeliveryView))
			{
				ShowError(\Bitrix\Main\Localization\Loc::getMessage("SALE_PRINT_DENIED_PRINT_PERMISSION"));
				exit;
			}

			$arOrder['PRICE_DELIVERY'] = $data['PRICE_DELIVERY'];
		}
		elseif (!in_array($arOrder["STATUS_ID"], $allowedStatusesView))
		{
			ShowError(\Bitrix\Main\Localization\Loc::getMessage("SALE_PRINT_DENIED_PRINT_PERMISSION"));
			exit;
		}

		$shipmentRes = \Bitrix\Sale\Shipment::getList(array(
			'select' => array('ID', 'DATE_ALLOW_DELIVERY', 'EMP_ALLOW_DELIVERY_ID', 'DATE_DEDUCTED', 'EMP_ALLOW_DELIVERY_ID', 'TRACKING_NUMBER', 'DELIVERY_DOC_NUM', 'DELIVERY_DOC_DATE' ),
			'filter' => array(
				'ORDER_ID' => $arOrder['ID'],
				'SYSTEM' => 'N'
			),
			'order' => array('ID' => 'DESC'),
			'limit' => 1
		));

		if ($shipmentData = $shipmentRes->fetch())
		{
			$shipmentId = $shipmentData['ID'];
			unset($shipmentData['ID']);

			$arOrder = array_merge($arOrder, $shipmentData);
			$shipmentCollection = $order->getShipmentCollection();
			/** @var \Bitrix\Sale\Shipment $shipment */
			$shipment = $shipmentCollection->getItemById($shipmentId);
			$arOrder['DELIVERY_VAT_SUM'] = $shipment->getVatSum();
			$arOrder['DELIVERY_VAT_RATE'] = $shipment->getVatRate();
		}

		$rep_file_name = GetRealPath2Report($doc.".php");
		if (strlen($rep_file_name)<=0)
		{
			ShowError("PRINT TEMPLATE NOT FOUND");
			die();
		}

		$arOrderProps = array();
		$dbOrderPropVals = CSaleOrderPropsValue::GetList(
				array(),
				array("ORDER_ID" => $ORDER_ID),
				false,
				false,
				array("ID", "CODE", "VALUE", "ORDER_PROPS_ID", "PROP_TYPE")
			);
		while ($arOrderPropVals = $dbOrderPropVals->Fetch())
		{
			$arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
					$arOrderPropVals["ORDER_PROPS_ID"],
					$arOrderPropVals["CODE"],
					$arOrderPropVals["PROP_TYPE"],
					$arOrderPropVals["VALUE"],
					LANGUAGE_ID
				);
			foreach ($arCurOrderPropsTmp as $key => $value)
			{
				$arOrderProps[$key] = $value;
			}
		}

		if(CSaleLocation::isLocationProMigrated())
		{
			if(strlen($arOrderProps['LOCATION_VILLAGE']) && !strlen($arOrderProps['LOCATION_CITY']))
				$arOrderProps['LOCATION_CITY'] = $arOrderProps['LOCATION_VILLAGE'];

			// street added to the beginning of address, as it used to be before
			if(strlen($arOrderProps['LOCATION_STREET']) && isset($arOrderProps['ADDRESS']))
				$arOrderProps['ADDRESS'] = $arOrderProps['LOCATION_STREET'].(strlen($arOrderProps['ADDRESS']) ? ', '.$arOrderProps['ADDRESS'] : '');
		}

		$arBasketIDs = array();
		$arQuantities = array();

		if (!isset($SHOW_ALL) || $SHOW_ALL == "N")
		{
			$arBasketIDs_tmp = explode(",", $BASKET_IDS);
			$arQuantities_tmp = explode(",", $QUANTITIES);

			if (count($arBasketIDs_tmp)!=count($arQuantities_tmp)) die("INVALID PARAMS");
			for ($i = 0, $countBasket = count($arBasketIDs_tmp); $i < $countBasket; $i++)
			{
				if (IntVal($arBasketIDs_tmp[$i])>0 && doubleVal($arQuantities_tmp[$i])>0)
				{
					$arBasketIDs[] = IntVal($arBasketIDs_tmp[$i]);
					$arQuantities[] = doubleVal($arQuantities_tmp[$i]);
				}
			}
			unset($countBasket);
		}
		else
		{
			$params = array(
				'select' => array("ID", "QUANTITY", "SET_PARENT_ID"),
				'filter' => array(
					"ORDER_ID" => $ORDER_ID
				),
				'order' => array("ID" => "ASC")
			);
			$db_basket = \Bitrix\Sale\Internals\BasketTable::getList($params);
			while ($arBasket = $db_basket->Fetch())
			{
				if (intval($arBasket['SET_PARENT_ID']) > 0)
					continue;

				$arBasketIDs[] = $arBasket["ID"];
				$arQuantities[] = $arBasket["QUANTITY"];
			}
		}

		$dbUser = CUser::GetByID($arOrder["USER_ID"]);
		$arUser = $dbUser->Fetch();

		$report = "";
		$serCount = IntVal(COption::GetOptionInt("sale", "reports_count"));
		if($serCount > 0)
		{
			for($i=1; $i <= $serCount; $i++)
			{
				$report .= COption::GetOptionString("sale", "reports".$i);
			}
		}
		else
			$report = COption::GetOptionString("sale", "reports");

		$arOptions = unserialize($report);

		if(!empty($arOptions))
		{
			foreach($arOptions as $key => $val)
			{
				if(strlen($val["VALUE"]) > 0)
				{
					if($val["TYPE"] == "USER")
						$arParams[$key] = $arUser[$val["VALUE"]];
					elseif($val["TYPE"] == "ORDER")
						$arParams[$key] = $arOrder[$val["VALUE"]];
					elseif($val["TYPE"] == "PROPERTY")
						$arParams[$key] = $arOrderProps[$val["VALUE"]];
					else
						$arParams[$key] = $val["VALUE"];
					$arParams["~".$key] = $arParams[$key];
					$arParams[$key] = htmlspecialcharsEx($arParams[$key]);
				}
			}
		}

		CCurrencyLang::disableUseHideZero();
		include($rep_file_name);
		CCurrencyLang::enableUseHideZero();
	}
}
else
	ShowError("SALE MODULE IS NOT INSTALLED");
?>