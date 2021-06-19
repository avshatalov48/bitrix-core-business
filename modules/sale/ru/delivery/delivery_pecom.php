<?
/********************************************************************************
 * Delivery services  http://pecom.ru/
 * https://kabinet.pecom.ru/api/v1/
 *******************************************************************************/

use \Bitrix\Main\Loader;
use \Bitrix\Sale\Result;
use \Bitrix\Sale\Shipment;

Loader::includeModule("sale");

Loader::registerAutoLoadClasses(
	'sale',
	array(
		'Bitrix\\Sale\\Delivery\\Pecom\\Request' => 'ru/delivery/pecom/request.php',
		'Bitrix\\Sale\\Delivery\\Pecom\\Adapter' => 'ru/delivery/pecom/adapter.php',
		'Bitrix\\Sale\\Delivery\\Pecom\\Calculator' => 'ru/delivery/pecom/calculator.php',
		'Bitrix\\Sale\\Delivery\\Pecom\\Location' => 'ru/delivery/pecom/location.php'
	)
);

use Bitrix\Sale\Delivery\Pecom\Adapter;
use Bitrix\Sale\Delivery\Pecom\Request;
use Bitrix\Sale\Delivery\Pecom\Calculator;
use Bitrix\Sale\Delivery\Pecom\Location;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/delivery/delivery_pecom.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/delivery/inputs.php");

class CDeliveryPecom
{
	public static $EXTRA_DEMENSIONS_WEIGHT = 1000; // (kg)
	public static $EXTRA_DIMENSIONS_SIZE = 5; // (m)

	public static $PAYER_SHOP = "1";
	public static $PAYER_BUYER = "2";

	public static function Init()
	{
		return array(
			/* Basic description */
			'SID' => 'pecom',
			'NAME' => GetMessage('SALE_DH_PECOM_NAME'),
			'DESCRIPTION' => GetMessage('SALE_DH_PECOM_DESCRIPTION').' <a href="http://pecom.ru">http://pecom.ru</a>',
			'DESCRIPTION_INNER' => GetMessage('SALE_DH_PECOM_DESCRIPTION').' <a href="http://pecom.ru">http://pecom.ru</a>',
			'BASE_CURRENCY' => 'RUB',
			'HANDLER' => __FILE__,

			/* Handler methods */
			'DBGETSETTINGS' => array('CDeliveryPecom', 'getSettings'),
			'DBSETSETTINGS' => array('CDeliveryPecom', 'setSettings'),
			'GETCONFIG' => array('CDeliveryPecom', 'getConfig'),
			'GETFEATURES' => array('CDeliveryPecom', 'getFeatures'),
			'COMPABILITY' => array('CDeliveryPecom', 'compability'),
			'CALCULATOR' => array('CDeliveryPecom', 'calculate'),
			'GETEXTRAINFOPARAMS' => array('CDeliveryPecom', 'getExtraInfoParams'),
			'GETORDERSACTIONSLIST' => array('CDeliveryPecom', 'getActionsList'),
			'EXECUTEACTION' => array('CDeliveryPecom', 'executeAction'),
			'GET_ADMIN_MESSAGE' => array('CDeliveryPecom', 'getAdminMessage'),
			'EXEC_ADMIN_ACTION' => array('CDeliveryPecom', 'execAdminAction'),
			'GET_ADD_INFO_SHIPMENT_VIEW' => array('CDeliveryPecom', 'getAdditionalInfoShipmentView'),
			'GET_ADD_INFO_SHIPMENT_EDIT' => array('CDeliveryPecom', 'getAdditionalInfoShipmentEdit'),
			'PROCESS_ADD_INFO_SHIPMENT_EDIT' => array('CDeliveryPecom', 'processAdditionalInfoShipmentEdit'),

			/* List of delivery profiles */
			"PROFILES" => array(
				"auto" => array(
					"TITLE" => GetMessage("SALE_DH_PECOM_AUTO_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_PECOM_AUTO_DESCR"),
					'RESTRICTIONS_WEIGHT' => array(0, 0),
					'RESTRICTIONS_SUM' => array(0),
					'TAX_RATE' => 0,
					'RESTRICTIONS_DIMENSIONS' => array("425", "265", "380")
					),
				"avia" => array(
					"TITLE" => GetMessage("SALE_DH_PECOM_AVIA_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_PECOM_AVIA_DESCR"),
					'RESTRICTIONS_WEIGHT' => array(0, 0),
					'RESTRICTIONS_SUM' => array(0),
					'TAX_RATE' => 0,
					'RESTRICTIONS_DIMENSIONS' => array("425", "265", "380")
				)
			)
		);
	}

	public static function getExtraInfoParams($arOrder, $arConfig, $profileId, $siteId)
	{
		$result = array();

		$locationsTo = Adapter::mapLocation($arOrder["LOCATION_TO"]);

		if(count($locationsTo) > 1)
		{
			$locValues = array();

			foreach($locationsTo as $locId => $locName)
			{
				$locValues[$locId] = $locName;
			}

			$result["location"] =  array(
				"TYPE" => "DROPDOWN",
				"TITLE" => GetMessage("SALE_DH_PECOM_EXTRA_LOCATION"),
				"VALUES" => $locValues
			);
		}

		return $result;
	}

	public static function getConfig($siteId = false)
	{
		$shopLocationId = CSaleHelper::getShopLocationId($siteId);
		$arShopLocation = CSaleHelper::getLocationByIdHitCached($shopLocationId);

		$locString = $arShopLocation["COUNTRY_NAME_LANG"] <> '' ? $arShopLocation["COUNTRY_NAME_LANG"] : "";
		$locString .= ($arShopLocation["REGION_NAME_LANG"] <> '' ? ($locString <> '' ? ", " : "").$arShopLocation["REGION_NAME_LANG"] : "");
		$locString .= ($arShopLocation["CITY_NAME_LANG"] <> '' ? ($locString <> '' ? ", " : "").$arShopLocation["CITY_NAME_LANG"] : "");

		$locDelivery = Adapter::mapLocation($shopLocationId);

		$arConfig = array(
			'CONFIG_GROUPS' => array(
				'exchange_sett' => GetMessage('SALE_DH_PECOM_EXCH_TITLE'),
				'add_services' => GetMessage('SALE_DH_PECOM_ADD_SERVICES_TITLE'),
				'auto' => GetMessage('SALE_DH_PECOM_AUTO_TITLE'),
				'avia' => GetMessage('SALE_DH_PECOM_AVIA_TITLE'),
			),

			"CONFIG" => array(
				"LOGIN" => array(
					"DEFAULT" => '',
					"TITLE" => GetMessage('SALE_DH_PECOM_EXCH_LOGIN'),
					"GROUP" => "exchange_sett"
				),
				"KEY" => array(
					"DEFAULT" => '',
					"TITLE" => GetMessage('SALE_DH_PECOM_EXCH_KEY'),
					"GROUP" => "exchange_sett"
				),

				"NAME" => array(
					"DEFAULT" => '',
					"TITLE" => GetMessage('SALE_DH_PECOM_EXCH_NAME'),
					"GROUP" => "exchange_sett"
				),
				"INN" => array(
					"DEFAULT" => '',
					"TITLE" => GetMessage('SALE_DH_PECOM_EXCH_INN'),
					"GROUP" => "exchange_sett"
				),

				"CITY" => array(
					"TYPE" => "TEXT_RO",
					"TITLE" => GetMessage('SALE_DH_PECOM_EXCH_CITY'),
					"DEFAULT" => $locString,
					"GROUP" => "exchange_sett"
				),
				"CITY_DELIVERY" => array(
					"TYPE" => "DROPDOWN",
					"TITLE" => GetMessage('SALE_DH_PECOM_EXCH_CITY_DELIVERY'),
					"VALUES" => $locDelivery,
					"GROUP" => "exchange_sett"
				),

				"PHONE" => array(
					"DEFAULT" => '',
					"TITLE" => GetMessage('SALE_DH_PECOM_EXCH_PHONE'),
					"GROUP" => "exchange_sett"
				),

				"PAYMENT_FORM" => array(
					"TYPE" => "DROPDOWN",
					"DEFAULT" => self::$PAYER_BUYER,
					"TITLE" => GetMessage('SALE_DH_PECOM_AS_PAYMENT_FORM'),
					"GROUP" => "exchange_sett",
					"VALUES" => array(
						self::$PAYER_SHOP => GetMessage('SALE_DH_PECOM_AS_PAYMENT_BANK'),
						self::$PAYER_BUYER => GetMessage('SALE_DH_PECOM_AS_PAYMENT_KASSA')
					)
				),

				"SERVICE_TAKE" => array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_TAKE'),
					'GROUP' => 'add_services',
				),

				"SERVICE_TAKE_ENABLED" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_TAKE_ENABLE'),
					'GROUP' => 'add_services',
					'DEFAULT' => '',
					'HIDE_BY_NAMES' => array('SERVICE_TAKE_TENT_ENABLED', 'SERVICE_TAKE_HYDRO_ENABLED')
				),

				"SERVICE_TAKE_TENT_ENABLED" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_TAKE_TENT'),
					'GROUP' => 'add_services',
					'DEFAULT' => ''
				),

				"SERVICE_TAKE_HYDRO_ENABLED" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_TAKE_HYDRO'),
					'GROUP' => 'add_services',
					'DEFAULT' => ''
				),

				"SERVICE_DELIVERY" => array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_DELIVERY'),
					'GROUP' => 'add_services',
				),

				"SERVICE_DELIVERY_ENABLED" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_DELIVERY_ENABLE'),
					'GROUP' => 'add_services',
					'DEFAULT' => '',
					'HIDE_BY_NAMES' => array('SERVICE_DELIVERY_TENT_ENABLED', 'SERVICE_DELIVERY_HYDRO_ENABLED', 'SERVICE_OTHER_DELIVERY_PAYER')
				),

				"SERVICE_DELIVERY_TENT_ENABLED" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_DELIVERY_TENT'),
					'GROUP' => 'add_services',
					'DEFAULT' => ''
				),

				"SERVICE_DELIVERY_HYDRO_ENABLED" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_DELIVERY_HYDRO'),
					'GROUP' => 'add_services',
					'DEFAULT' => ''
				),

				"SERVICE_OTHER_DELIVERY_PAYER" => array(
					'TYPE' => 'DROPDOWN',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_PAYER'),
					'GROUP' => 'add_services',
					'DEFAULT' => self::$PAYER_BUYER,
					"VALUES" => array(
						self::$PAYER_SHOP => GetMessage('SALE_DH_PECOM_AS_PAYER_SHOP'),
						self::$PAYER_BUYER => GetMessage('SALE_DH_PECOM_AS_PAYER_BUYER')
					)
				),

				"SERVICE_OTHER" => array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_OTHER'),
					'GROUP' => 'add_services',
				),

				"SERVICE_OTHER_PLOMBIR_ENABLE" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_OTHER_PLOMBIR_ENABLE'),
					'GROUP' => 'add_services',
					'DEFAULT' => '',
					'HIDE_BY_NAMES' => array('SERVICE_OTHER_PLOMBIR_COUNT', 'SERVICE_OTHER_PLOMBIR_PAYER')
				),

				"SERVICE_OTHER_PLOMBIR_COUNT" => array(
					'TYPE' => 'STRING',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_OTHER_PLOMBIR_COUNT'),
					'GROUP' => 'add_services',
					'DEFAULT' => '0',
				),

				"SERVICE_OTHER_PLOMBIR_PAYER" => array(
					'TYPE' => 'DROPDOWN',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_PAYER'),
					'GROUP' => 'add_services',
					'DEFAULT' => self::$PAYER_BUYER,
					"VALUES" => array(
						self::$PAYER_SHOP => GetMessage('SALE_DH_PECOM_AS_PAYER_SHOP'),
						self::$PAYER_BUYER => GetMessage('SALE_DH_PECOM_AS_PAYER_BUYER')
					)
				),

				"SERVICE_OTHER_PALLETE" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_OTHER_PALLETE'),
					'GROUP' => 'add_services',
					'DEFAULT' => '',
					'TOP_LINE' => 'Y',
					'HIDE_BY_NAMES' => array('SERVICE_OTHER_PALLETE_PAYER')
				),

				"SERVICE_OTHER_PALLETE_PAYER" => array(
					'TYPE' => 'DROPDOWN',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_PAYER'),
					'GROUP' => 'add_services',
					'DEFAULT' => self::$PAYER_BUYER,
					"VALUES" => array(
						self::$PAYER_SHOP => GetMessage('SALE_DH_PECOM_AS_PAYER_SHOP'),
						self::$PAYER_BUYER => GetMessage('SALE_DH_PECOM_AS_PAYER_BUYER')
					)
				),

				"SERVICE_OTHER_INSURANCE" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_OTHER_INSURANCE'),
					'GROUP' => 'add_services',
					'DEFAULT' => '',
					'TOP_LINE' => 'Y',
					'HIDE_BY_NAMES' => array('SERVICE_OTHER_INSURANCE_PAYER')
				),

				"SERVICE_OTHER_INSURANCE_PAYER" => array(
					'TYPE' => 'DROPDOWN',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_PAYER'),
					'GROUP' => 'add_services',
					'DEFAULT' => self::$PAYER_BUYER,
					"VALUES" => array(
						self::$PAYER_SHOP => GetMessage('SALE_DH_PECOM_AS_PAYER_SHOP'),
						self::$PAYER_BUYER => GetMessage('SALE_DH_PECOM_AS_PAYER_BUYER')
					)
				),

				"SERVICE_OTHER_RIGID_PACKING" => array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_OTHER_RIGID_PACKING'),
					'GROUP' => 'add_services',
					'DEFAULT' => '',
					'TOP_LINE' => 'Y',
					'HIDE_BY_NAMES' => array('SERVICE_OTHER_RIGID_PAYER')
				),

				"SERVICE_OTHER_RIGID_PAYER" => array(
					'TYPE' => 'DROPDOWN',
					'TITLE' => GetMessage('SALE_DH_PECOM_AS_PAYER'),
					'GROUP' => 'add_services',
					'DEFAULT' => self::$PAYER_BUYER,
					"VALUES" => array(
						self::$PAYER_SHOP => GetMessage('SALE_DH_PECOM_AS_PAYER_SHOP'),
						self::$PAYER_BUYER => GetMessage('SALE_DH_PECOM_AS_PAYER_BUYER')
					)
				)
			)
		);

		$aviableBoxes = self::getAviableBoxes();

		foreach ($aviableBoxes as $boxId => $arBox)
		{
			CSaleDeliveryHelper::makeBoxConfig($boxId, $arBox, 'auto', $arConfig);
			CSaleDeliveryHelper::makeBoxConfig($boxId, $arBox, 'avia', $arConfig);
		}

		return $arConfig;
	}

	public static function getSettings($strSettings)
	{
		return unserialize($strSettings, ['allowed_classes' => false]);
	}

	public static function setSettings($arSettings)
	{
		unset($arSettings["CITY"]);

		foreach ($arSettings as $key => $value)
		{
			if ($value <> '')
				$arSettings[$key] = $value;
			else
				unset($arSettings[$key]);
		}

		return serialize($arSettings);
	}

	public static function getFeatures($arConfig)
	{
		$arResult = array();

		$mesEnabled = GetMessage("SALE_DH_PECOM_FEATURE_ENABLED");

		if($arConfig["SERVICE_TAKE_ENABLED"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_TAKE_ENABLE")] = $mesEnabled;

		if($arConfig["SERVICE_TAKE_TENT_ENABLED"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_TAKE_TENT")] = $mesEnabled;

		if($arConfig["SERVICE_TAKE_HYDRO_ENABLED"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_TAKE_HYDRO")] = $mesEnabled;

		if($arConfig["SERVICE_DELIVERY_ENABLED"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_DELIVERY_ENABLE")] = $mesEnabled;

		if($arConfig["SERVICE_DELIVERY_TENT_ENABLED"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_DELIVERY_TENT")] = $mesEnabled;

		if($arConfig["SERVICE_DELIVERY_HYDRO_ENABLED"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_DELIVERY_HYDRO")] = $mesEnabled;

		if($arConfig["SERVICE_OTHER_DELIVERY_PAYER"]["VALUE"] == self::$PAYER_SHOP)
			$arResult[GetMessage("SALE_DH_PECOM_AS_PAYER")] = GetMessage('SALE_DH_PECOM_AS_PAYER_SHOP');
		else
			$arResult[GetMessage("SALE_DH_PECOM_AS_PAYER")] = GetMessage('SALE_DH_PECOM_AS_PAYER_BUYER');

		if($arConfig["SERVICE_OTHER_PLOMBIR_ENABLE"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_OTHER_PLOMBIR_ENABLE")] = $mesEnabled;

		if($arConfig["SERVICE_OTHER_PALLETE"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_OTHER_PALLETE")] = $mesEnabled;

		if($arConfig["SERVICE_OTHER_INSURANCE"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_OTHER_INSURANCE")] = $mesEnabled;

		if($arConfig["SERVICE_OTHER_RIGID_PACKING"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_PECOM_AS_OTHER_RIGID_PACKING")] = $mesEnabled;

		return $arResult;
	}

	public static function calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		$ttl = 604800; //week
		$cacheId = "SaleDeliveryPecomCalc_".$profile."_".md5(serialize($arConfig))."_".md5(serialize($arOrder));
		$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();

		if($cacheManager->read($ttl, $cacheId))
		{
			$result = $cacheManager->get($cacheId);
		}
		else
		{
			$calc = new Calculator($arOrder, $arConfig, $profile);
			$result = $calc->getPriceInfo();
			$cacheManager->set($cacheId, $result);
		}

		return $result;
	}

	public static function compability($arOrder, $arConfig)
	{
		$ttl = 604800;
		$cacheId = "SaleDeliveryPecomCompability".$arConfig["CITY_DELIVERY"]["VALUE"].$arOrder["LOCATION_TO"];
		$arResult = array();

		$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();

		if($cacheManager->read($ttl, $cacheId))
		{
			$arResult = $cacheManager->get($cacheId);
		}

		if(empty($arResult))
		{
			$calc = new Calculator($arOrder, $arConfig);
			$arResult =$calc->getCompabilityInfo();
			$cacheManager->set($cacheId, $arResult);
		}

		return $arResult;
	}

	public static function getConfValue(&$arConfig, $key)
	{
		return CSaleDeliveryHelper::getConfValue($arConfig[$key]);
	}

	public static function isConfCheckedVal(&$arConfig, $key)
	{
		return 	$arConfig[$key]['VALUE'] == 'Y'
		||(
			!isset($arConfig[$key]['VALUE'])
			&& $arConfig[$key]['DEFAULT'] == 'Y'
		);
	}

	public static function getActionsList()
	{
		$actions = CSaleDeliveryHandler::getActionsNames();

		return array(
			"REQUEST_SELF" => $actions["REQUEST_SELF"],
			//"REQUEST_TAKE" => $actions["REQUEST_TAKE"]
		);
	}

	protected static function sendRequest($apiLogin, $apiKey, $controller, $action, $data)
	{
		$pcr = new Request($apiLogin, $apiKey);

		try
		{
			$requestResult = $pcr->send($controller, $action, $data);

			if(isset($requestResult["error"]))
			{
				$result = array(
					"RESULT" => "ERROR",
					"TEXT" => $requestResult["error"]["title"].": ".$requestResult["error"]["message"],
					"DATA" => $requestResult
				);
			}
			else
			{
				$result = array(
					"RESULT" => "OK",
					"DATA" => $requestResult
				);
			}
		}
		catch(\Exception $e)
		{
			$result = array(
				"RESULT" => "ERROR",
				"TEXT" => $e->getMessage()
			);
		}

		return $result;
	}

	protected static function getPhoneEmail($orderId)
	{
		$result = array(
			"EMAIL" => "",
			"PHONE" => ""
		);

		$dbOrderProps = \CSaleOrderPropsValue::GetOrderProps($orderId);

		while ($arOrderProps = $dbOrderProps->Fetch())
		{
			if($arOrderProps["CODE"] == "EMAIL")
				$result["EMAIL"] = $arOrderProps["VALUE"];

			if($arOrderProps["CODE"] == "PHONE")
				$result["PHONE"] = $arOrderProps["VALUE"];
		}

		return  $result;
	}

	public static function executeAction($actionId, $profileId, $arOrder, $arConfig)
	{
		$reqResult = array();
		$result = array();

		switch($actionId)
		{
			case "REQUEST_SELF":
				$controller = 'preregistration';
				$action = 'submit';
				$data = Adapter::preparePreregistrationReqData($arOrder, $profileId, $arConfig);
				$reqResult = static::sendRequest($arConfig["LOGIN"]["VALUE"], $arConfig["KEY"]["VALUE"], $controller, $action, $data);

				if( isset($reqResult["DATA"]["cargos"][0]["cargoCode"]))
				{
					$result["TRACKING_NUMBER"] = $reqResult["DATA"]["cargos"][0]["cargoCode"];

					if(isset($reqResult["DATA"]["documentId"]))
						$result["DELIVERY_DOC_NUM"] = $reqResult["DATA"]["documentId"];

					$phoneAndEmail = static::getPhoneEmail($arOrder["ID"]);
					$subsData = Adapter::prepareSubscribeReqData(
						array($reqResult["DATA"]["cargos"][0]["cargoCode"]),
						$phoneAndEmail["EMAIL"],
						$phoneAndEmail["PHONE"]
					);

					$subsResult = static::sendRequest($arConfig["LOGIN"]["VALUE"], $arConfig["KEY"]["VALUE"], "notification", "cargosubscribe", $subsData);
				}

				break;

				case "REQUEST_STATUS":
					$controller = 'cargos';
					$action = 'status';
					$data = array(
						'cargoCodes' => array(
							$arOrder['TRACKING_NUMBER']
						)
					);
					$reqResult = static::sendRequest($arConfig["LOGIN"]["VALUE"], $arConfig["KEY"]["VALUE"], $controller, $action, $data);
					break;

				case "REQUEST_TAKE":
					$reqResult = array(
						"RESULT" => "ERROR",
						"TEXT" => ""
					);
					break;
		}

		$result["RESULT"] = $reqResult["RESULT"];

		if(isset($reqResult["TEXT"]))
			$result["TEXT"] = $reqResult["TEXT"];

		if(isset($reqResult["DATA"]))
			$result["DATA"] = $reqResult["DATA"];

		return $result;
	}

	protected static function getAviableBoxes()
	{
		return array(
			array(
				"NAME" => GetMessage("SALE_DH_RP_STNDRD_BOX"),
				"DIMENSIONS" => array("425", "265", "380")
			)
		);
	}

	public static function getAdminMessage()
	{
		$result = array();
		$message = '';

		if(!Location::isInstalled())
		{
			$message =
				GetMessage('SALE_DH_PECOM_LOC_INSTALL').
				'. <a href="javascript:void(0)" onclick="startInstallPecomLocations()"> '.
				GetMessage('SALE_DH_PECOM_LOC_INSTALL_START').
				'</a>
				<script>
					function startInstallPecomLocations()
					{
						window.location.href.search(\'PECOM_LOCATIONS_START_MAP\') != -1 ? window.location.reload(true) : window.location.href += \'&PECOM_LOCATIONS_START_MAP=Y\';
					}
				</script>';
		}

		if(isset($_SESSION['PECOM_LOCATIONS_MAP_ERRORS']) && is_array($_SESSION['PECOM_LOCATIONS_MAP_ERRORS']))
		{
			/** @var \Bitrix\Main\Error  $error */
			foreach($_SESSION['PECOM_LOCATIONS_MAP_ERRORS'] as $error)
		 		$message .= $error->getMessage()."\n<br>";

			unset($_SESSION['PECOM_LOCATIONS_MAP_ERRORS']);
		}

		if($message <> '')
		{
			$result = array(
				'MESSAGE' => $message,
				"TYPE" => "ERROR",
				"HTML" => true
			);
		}

		return $result;
	}

	public static function execAdminAction()
	{
		$result = new \Bitrix\Sale\Result();

		if(isset($_REQUEST['PECOM_LOCATIONS_START_MAP']) &&  $_REQUEST['PECOM_LOCATIONS_START_MAP'] == 'Y' && !Location::isInstalled())
			$result = Location::install();

		if(!$result->isSuccess())
			$_SESSION['PECOM_LOCATIONS_MAP_ERRORS'] = $result->getErrors();

		return $result;
	}

	public static function getAdditionalInfoShipmentEdit(Shipment $shipment)
	{
		$shipmentId = $shipment->getId();

		if(intval($shipmentId) <= 0)
			return array();

		if(self::isRequestSelfSent($shipmentId))
		{
			$date = self::getRequestSelfSentDate($shipmentId);

			$inputs = array(
				'REQUEST_SELF' => array(
					'LABEL' => GetMessage('SALE_DH_PECOM_PRELIMINARY_REQUEST'),
					'TYPE' => 'DELIVERY_READ_ONLY',
					'VALUE' => GetMessage('SALE_DH_PECOM_PRELIMINARY_REQUEST_SENT').' '.$date.'.'
				)
			);
		}
		else
		{
			$inputs = array(
				'REQUEST_SELF' => array(
					'LABEL' => GetMessage('SALE_DH_PECOM_PRELIMINARY_SEND_REQUEST'),
					'TYPE' => 'Y/N',
					'VALUE' => 'N'
				)
			);
		}

		return $inputs;
	}

	public static function getAdditionalInfoShipmentView(Shipment $shipment)
	{
		$shipmentId = $shipment->getId();

		if(intval($shipmentId) <= 0)
			return array();

		if(self::isRequestSelfSent($shipmentId))
			$value = GetMessage('SALE_DH_PECOM_PRELIMINARY_REQUEST_SENT').' '.self::getRequestSelfSentDate($shipmentId);
		else
			$value = GetMessage('SALE_DH_PECOM_PRELIMINARY_REQUEST_NOT_SENT');

		$inputs = array(
			'REQUEST_SELF' => array(
				'LABEL' => GetMessage('SALE_DH_PECOM_PRELIMINARY_REQUEST'),
				'TYPE' => 'DELIVERY_READ_ONLY',
				'VALUE' => $value
			)
		);

		return $inputs;
	}

	protected static function isRequestSelfSent($shipmentId)
	{
		return self::getRequestSelfSentDate($shipmentId) !== null;
	}

	protected static function getRequestSelfSentDate($shipmentId)
	{
		static $result = array();

		if(!isset($result[$shipmentId]))
		{
			$result[$shipmentId] = null;

			$dbRes = \Bitrix\Sale\Internals\OrderDeliveryReqTable::getList(array(
					'filter'=>array('=SHIPMENT_ID' => $shipmentId),
			));

			while($req = $dbRes->fetch())
			{
				if(!is_null($req["DATE_REQUEST"]) && !empty($req["PARAMS"]["TYPE"]) && $req["PARAMS"]["TYPE"] == "REQUEST_SELF")
				{
					$result[$shipmentId] = $req["DATE_REQUEST"];
					break;
				}
			}
		}

		return $result[$shipmentId];
	}

	public static function processAdditionalInfoShipmentEdit(Shipment $shipment, array $requestData)
	{
		if(empty($requestData['REQUEST_SELF']) || $requestData['REQUEST_SELF'] != 'Y')
			return null;

		$shipmentId = $shipment->getId();

		if(intval($shipmentId) <= 0)
			return null;

		$dt = new \Bitrix\Main\Type\DateTime();

		$arResult = CSaleDeliveryHandler::executeAction(
			\CSaleDelivery::getCodeById(
				$shipment->getDeliveryId()
			),
			'REQUEST_SELF',
			\CAllSaleDelivery::convertOrderNewToOld(
				$shipment
			)
		);

		if(!$arResult)
			return null;

		$res = \Bitrix\Sale\Internals\OrderDeliveryReqTable::add(
			array(
				"SHIPMENT_ID" => $shipmentId,
				"ORDER_ID" => $shipment->getCollection()->getOrder()->getId(),
				"DATE_REQUEST" => $dt,
				"PARAMS" => array(
					"TYPE" => "REQUEST_SELF",
					"RESULT" => $arResult
				)
			)
		);

		if(!$res->isSuccess())
			return null;

		if(isset($arResult["TRACKING_NUMBER"]))
			$shipment->setField("TRACKING_NUMBER", $arResult["TRACKING_NUMBER"]);

		if(isset($arResult["DELIVERY_DOC_NUM"]))
		{
			$shipment->setField("DELIVERY_DOC_NUM", $arResult["DELIVERY_DOC_NUM"]);
			$shipment->setField("DELIVERY_DOC_DATE", $dt);
		}

		return $shipment;
	}
}

AddEventHandler('sale', 'onSaleDeliveryHandlersBuildList', array('CDeliveryPecom', 'Init'));
?>
