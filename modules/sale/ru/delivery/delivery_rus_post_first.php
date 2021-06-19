<?
/********************************************************************************
Delivery services for Russian Post Service (http://www.russianpost.ru/)
"First class" service.
Calculations based on RP rates:
http://www.russianpost.ru/rp/servise/ru/home/postuslug/1class/1class_tariffs
********************************************************************************/
CModule::IncludeModule('sale');

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/delivery/delivery_rus_post_first.php');

define('DELIVERY_RPF_CSV_PATH', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/sale/ru/delivery/rus_post_first'); //where we can found csv files

class CDeliveryRusPostFirst
{
	private static $MAX_WEIGHT = 2500; 	// (g)
	private static $MAX_SUMM = 20000; 	// RUB
	private static $MAX_SIZE = 360; //milimeters
	private static $MAX_DIMENSIONS_SUMM = 700; //milimeters
	private static $MAX_DIMENSIONS = array("165", "100", "190"); //milimeters

	private static $BASE_WEIGHT = 100;	// Base weight gramm

	private static $TARIFS = array();
	private static $SERVICES = array();

	private static $TARIF_IDX = 0;
	private static $TARIF_DESCR = 1;

	/* Standard mandatory delivery services functions */
	public static function Init()
	{
		self::$TARIFS = array(
							'WEIGHT_LESS_100' => array(6, GetMessage('SALE_DH_RPF_WRP_LESS_100')),
							'WEIGHT_LESS_100_DECLARED_VALUE' => array(11, GetMessage('SALE_DH_RPF_WRP_LESS_100_DECLARED_VALUE')),
							'WEIGHT_MORE_100' => array(7, GetMessage('SALE_DH_RPF_WRP_MORE_100'))
			);

		self::$SERVICES = array(
							'NOTIFICATION_SIMPLE' => array(8, GetMessage('SALE_DH_RPF_SMPL_NTF')),
							'NOTIFICATION_REG' => array(9, GetMessage('SALE_DH_RPF_RGST_NTF')),
							'DECLARED_VALUE' => array(10, GetMessage('SALE_DH_RPF_DCL_VAL'))
			);

		return array(
			/* Basic description */
			'SID' => 'rus_post_first',
			'NAME' => GetMessage('SALE_DH_RPF_NAME'),
			'DESCRIPTION' => GetMessage('SALE_DH_RPF_DESCR').' <a href="http://www.russianpost.ru/rp/servise/ru/home/postuslug/1class">http://www.russianpost.ru/rp/servise/ru/home/postuslug/1class</a>',
			'DESCRIPTION_INNER' => GetMessage('SALE_DH_RPF_DESCR').' <a href="http://www.russianpost.ru/rp/servise/ru/home/postuslug/1class">http://www.russianpost.ru/rp/servise/ru/home/postuslug/1class</a>',
			'BASE_CURRENCY' => 'RUB',
			'HANDLER' => __FILE__,
			
			/* Handler methods */
			'DBGETSETTINGS' => array('CDeliveryRusPostFirst', 'GetSettings'),
			'DBSETSETTINGS' => array('CDeliveryRusPostFirst', 'SetSettings'),
			'GETCONFIG' => array('CDeliveryRusPostFirst', 'GetConfig'),
			'GETFEATURES' => array('CDeliveryRusPostFirst', 'GetFeatures'),
			'COMPABILITY' => array('CDeliveryRusPostFirst', 'Compability'),
			'CALCULATOR' => array('CDeliveryRusPostFirst', 'Calculate'),
			'DEPRECATED' => 'Y',
			"GET_ADMIN_MESSAGE" => array("CDeliveryRUSSIANPOST", "getAdminMessage"),
			"TRACKING_CLASS_NAME" => '\Bitrix\Sale\Delivery\Tracking\RusPost',

			/* List of delivery profiles */
			'PROFILES' => array(
				'wrapper' => array(
					'TITLE' => GetMessage('SALE_DH_RPF_WRP_TITLE'),
					'DESCRIPTION' => GetMessage('SALE_DH_RPF_WRP_DESCR'),
					'RESTRICTIONS_WEIGHT' => array(0, self::$MAX_WEIGHT),
					'RESTRICTIONS_SUM' => array(0, self::$MAX_SUMM),
					'TAX_RATE' => 0,
					'RESTRICTIONS_MAX_SIZE' => self::$MAX_SIZE,
					'RESTRICTIONS_DIMENSIONS_SUM' => self::$MAX_DIMENSIONS_SUMM,
					'RESTRICTIONS_DIMENSIONS' => self::$MAX_DIMENSIONS
					)
			)
		);
	}

	public static function GetConfig($siteId = false)
	{
		$shopLocationId = CSaleHelper::getShopLocationId($siteId);
		$arShopLocation = CSaleHelper::getLocationByIdHitCached($shopLocationId);

		if(!$arShopLocation)
			$arShopLocation = array();

		$shopPrevLocationId = COption::GetOptionString('sale', 'delivery_rus_post_first_prev_loc', 0);

		/* if shop's location was changed */
		if($shopPrevLocationId != $shopLocationId)
		{
			COption::SetOptionString('sale', 'delivery_rus_post_first_prev_loc', $shopLocationId);
			COption::RemoveOption('sale', 'delivery_rus_post_first_tarifs');
		}

		$arConfig = array(
			'CONFIG_GROUPS' => array(
				'wrapper' => GetMessage('SALE_DH_RPF_WRP_TITLE'),
			),
		);

		$aviableBoxes = self::getAviableBoxes();

		foreach ($aviableBoxes as $boxId => $arBox)
			CSaleDeliveryHelper::makeBoxConfig($boxId, $arBox, 'wrapper', $arConfig);

		$arConfig['CONFIG']['tarif_section_1'] = array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_RPF_TARIFS'),
					'GROUP' => 'wrapper',
		);

		$arConfig['CONFIG']['RESET_TARIF_SETTINGS'] = array(
			'TYPE' => 'CUSTOM',
			'TITLE' => GetMessage('SALE_DH_RPF_SET_DEFAULT_TARIF'),
			'GROUP' => 'wrapper',
			'DEFAULT' => '<a href="javascript:void(0);" onclick="BX.Sale.Delivery.resetRusPostTarifSettings();">'.GetMessage('SALE_DH_RPF_SET_DEFAULT_TARIF_SET').'</a>'
		);

		$arTarifs = CSaleHelper::getOptionOrImportValues(
									'delivery_rus_post_first_tarifs',
									array('CDeliveryRusPostFirst', 'getTarifsByRegionFromCsv'),
									array($arShopLocation)
						);

		foreach (self::$TARIFS as $arTarif)
		{
			$tarifId = $arTarif[self::$TARIF_IDX];

			$arConfig['CONFIG']['TARIF_'.$tarifId] = array(
						'TYPE' => 'STRING',
						'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
						'TITLE' => $arTarif[self::$TARIF_DESCR],
						'GROUP' => 'wrapper',
			);
		}

		/* Additional services */
		foreach (self::$SERVICES as $serviceId => $arService)
		{
			$tarifId = $arService[self::$TARIF_IDX];

			$arConfig['CONFIG']['service_'.$tarifId.'_section'] = array(
						'TYPE' => 'SECTION',
						'TITLE' => $arService[self::$TARIF_DESCR],
						'GROUP' => 'wrapper',
			);

			$arConfig['CONFIG']['service_'.$tarifId.'_enabled'] = array(
						'TYPE' => 'CHECKBOX',
						'TITLE' => GetMessage('SALE_DH_RPF_SRV_ALLOW'),
						'GROUP' => 'wrapper',
						'DEFAULT' => $serviceId == 'NOTIFICATION_REG' ? 'N' : 'Y',
						'HIDE_BY_NAMES' => array('service_'.$tarifId.'_value')
			);

			$arConfig['CONFIG']['service_'.$tarifId.'_value'] = array(
						'TYPE' => 'STRING',
						'TITLE' => GetMessage('SALE_DH_RPF_SRV_PRICE'),
						'GROUP' => 'wrapper',
						'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
			);
		}

		return $arConfig;
	}

	public static function GetSettings($strSettings)
	{
		$result = unserialize($strSettings, ['allowed_classes' => false]);

		if(isset($result['RESET_TARIF_SETTINGS']))
			unset($result['RESET_TARIF_SETTINGS']);

		if(isset($_REQUEST["RESET_TARIF_SETTINGS"]) && $_REQUEST["RESET_TARIF_SETTINGS"] == "Y" && !isset($_REQUEST["apply"]))
		{
			COption::RemoveOption('sale', 'delivery_rus_post_first_tarifs');

			foreach($result as $key => $value)
				if(mb_substr($key, 0, 6) == 'TARIF_' || mb_substr($key, 0, 8) == 'service_')
					unset($result[$key]);
		}

		return $result;
	}

	public static function SetSettings($arSettings)
	{
		if(isset($arSettings['RESET_TARIF_SETTINGS']))
			unset($arSettings['RESET_TARIF_SETTINGS']);

		foreach ($arSettings as $key => $value)
		{
			if ($value <> '')
				$arSettings[$key] = $value;
			else
				unset($arSettings[$key]);
		}

		return serialize($arSettings);
	}

	public static function GetFeatures($arConfig)
	{
		$arResult = array();

		if ($arConfig["service_".array_shift(array_values(self::$SERVICES["NOTIFICATION_SIMPLE"]))."_enabled"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_RPF_SMPL_NTF")] = GetMessage("SALE_DH_RPF_FEATURE_ENABLED");

		if ($arConfig["service_".array_shift(array_values(self::$SERVICES["NOTIFICATION_REG"]))."_enabled"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_RPF_RGST_NTF")] = GetMessage("SALE_DH_RPF_FEATURE_ENABLED");

		if ($arConfig["service_".array_shift(array_values(self::$SERVICES["DECLARED_VALUE"]))."_enabled"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_RPF_FEATURE_VALUE")] = GetMessage("SALE_DH_RPF_FEATURE_ENABLED");

		return $arResult;
	}

	public static function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		$arPacks = CSaleDeliveryHelper::getBoxesFromConfig($profile, $arConfig);

		$arPackagesParams = CSaleDeliveryHelper::getRequiredPacks(
													$arOrder["ITEMS"],
													$arPacks,
													self::$MAX_WEIGHT);

		$packageCount = count($arPackagesParams);

		if(intval($packageCount) <= 0)
		{
			return array(
						"RESULT" => "ERROR",
						"TEXT" => GetMessage("SALE_DH_RPF_OVERLOAD"),
					);
		}

		$totalPrice = 0;
		$arLocationTo = CSaleHelper::getLocationByIdHitCached($arOrder['LOCATION_TO']);

		foreach ($arPackagesParams as $arPackage)
			$totalPrice += self::calculatePackPrice($arPackage, $profile, $arConfig, $arLocationTo);

		$arResult = array(
			'RESULT' => 'OK',
			'VALUE' => $totalPrice,
			'PACKS_COUNT' => $packageCount
		);
		return $arResult;
	}

	public static function Compability($arOrder, $arConfig)
	{
		$result = array();

		$aviableBoxes = CSaleDeliveryHelper::getBoxesFromConfig('wrapper', $arConfig);

		foreach ($aviableBoxes as $arBox)
		{
			if (CSaleDeliveryHandler::checkDimensions($arOrder["MAX_DIMENSIONS"], $arBox["DIMENSIONS"]))
			{
				$result = array('wrapper');
				break;
			}
		}

		return $result;
	}

	/* Particular services helper functions*/

	public static function getTarifNumFromCsv(array $arShopLocation)
	{
		if(empty($arShopLocation) || !isset($arShopLocation["REGION_ID"]) || !isset($arShopLocation['REGION_NAME_LANG']))
			return false;

		$regionCodeFromCode = $regionCodeFromName = "";

		$dbRes = \Bitrix\Sale\Location\LocationTable::getById($arShopLocation["REGION_ID"]);

		if($locReg = $dbRes->fetch())
			$regionCodeFromCode = $locReg["CODE"];

		$regionCodeFromName = self::getRegionCodeByOldName($arShopLocation['REGION_NAME_LANG']);

		$csvFile = CSaleHelper::getCsvObject(DELIVERY_RP_CSV_PATH.'/tarif_regions.csv');
		$tarifNumber = false;
		$COL_TARIF_NUM = 0;

		while ($arRes = $csvFile->Fetch())
		{
			if(
				($regionCodeFromCode <> '' && in_array($regionCodeFromCode, $arRes))
				|| ($regionCodeFromName <> '' && in_array($regionCodeFromName, $arRes))
			)
			{
				$tarifNumber = $arRes[$COL_TARIF_NUM];
				break;
			}
		}
		return $tarifNumber;
	}

	public static function getTarifsByRegionFromCsv(array $arShopLocation)
	{
		if(empty($arShopLocation))
			return false;

		$tarifNumber = self::getTarifNumFromCsv($arShopLocation);

		if($tarifNumber === false)
			return false;

		$csvFile = CSaleHelper::getCsvObject(DELIVERY_RPF_CSV_PATH.'/tarif_data.csv');
		$COL_TARIF_ITEMS = 0;
		$arTarifs = array();
		$arRes = $csvFile->Fetch();

		while ($arRes = $csvFile->Fetch())
		{
			if(!isset($arRes[$tarifNumber]))
				break;

			$arTarifs[$arRes[$COL_TARIF_ITEMS]] = $arRes[$tarifNumber];
		}

		return $arTarifs;
	}

	private static function getConfValue(&$arConfig, $key)
	{
		return CSaleDeliveryHelper::getConfValue($arConfig[$key]);
	}

	private static function isConfCheckedVal(&$arConfig, $key)
	{
		return 	$arConfig[$key]['VALUE'] == 'Y'
				||(
					!isset($arConfig[$key]['VALUE'])
					&& $arConfig[$key]['DEFAULT'] == 'Y'
				);
	}

	private static function getAviableBoxes()
	{
		return array(
					array(
						"NAME" => GetMessage("SALE_DH_RPF_STNRD_BOX"),
						"DIMENSIONS" => array("165", "100", "190")
						)
			);
	}

	private static function calculatePackPrice($arPackage, $profile, $arConfig, $arLocationTo)
	{
		$arDebug = array();
		$totalPrice = 0;
		$declaredValue = self::isConfCheckedVal($arConfig, 'service_'.self::$SERVICES['DECLARED_VALUE'][self::$TARIF_IDX].'_enabled');

		//2. Wrapper
		//2.1, 2.2  declared value, weight less 100 gramm

		if($declaredValue && floatval($arConfig['TARIF_'.self::$TARIFS['WEIGHT_LESS_100_DECLARED_VALUE'][self::$TARIF_IDX]]['VALUE']) > 0)
			$basePrice = floatval(self::getConfValue($arConfig, 'TARIF_'.self::$TARIFS['WEIGHT_LESS_100_DECLARED_VALUE'][self::$TARIF_IDX]));
		else
			$basePrice = floatval(self::getConfValue($arConfig, 'TARIF_'.self::$TARIFS['WEIGHT_LESS_100'][self::$TARIF_IDX]));

		$arDebug[] = 'Base Price less 100 g: '.$basePrice;

		// 2.3 weight more than 100 g
		if($arPackage['WEIGHT'] > self::$BASE_WEIGHT)
		{
			$addWeight = ceil($arPackage['WEIGHT'] / self::$BASE_WEIGHT - 1);
			$addPrice = floatval(self::getConfValue($arConfig, 'TARIF_'.self::$TARIFS['WEIGHT_MORE_100'][self::$TARIF_IDX]));
			$arDebug[] = 'Price for additional weight more than 100 g: '.$addPrice;
			$basePrice += $addWeight * $addPrice;
		}

		$totalPrice = $basePrice;

		// 3.1 simple notification
		$snPrice = 0;
		if(self::isConfCheckedVal($arConfig, 'service_'.self::$SERVICES['NOTIFICATION_SIMPLE'][self::$TARIF_IDX].'_enabled'))
		{
			$snPrice = floatval(self::getConfValue($arConfig, 'service_'.self::$SERVICES['NOTIFICATION_SIMPLE'][self::$TARIF_IDX].'_value'));
			$arDebug[] = 'Simple notification: '.$snPrice;
			$totalPrice += $snPrice;
		}

		// 3.2. registered notification
		$rnPrice = 0;
		if(self::isConfCheckedVal($arConfig, 'service_'.self::$SERVICES['NOTIFICATION_REG'][self::$TARIF_IDX].'_enabled'))
		{
			$rnPrice = floatval(self::getConfValue($arConfig, 'service_'.self::$SERVICES['NOTIFICATION_REG'][self::$TARIF_IDX].'_value'));
			$arDebug[] = 'Registered notification: '.$rnPrice;
			$totalPrice += $rnPrice;
		}

		// 4. Service "declared value"
		$dvPrice = 0;
		if($declaredValue)
		{
			$dvTarif = floatval(self::getConfValue($arConfig, 'service_'.self::$SERVICES['DECLARED_VALUE'][self::$TARIF_IDX].'_value'));
			$dvPrice += ($arPackage['PRICE'])*$dvTarif;
			$arDebug[] = 'Declared value: '.$dvPrice;
			$totalPrice += $dvPrice;
		}

		$arDebug[] = 'Total value: '.$totalPrice;
		return $totalPrice;
	}

	protected static function getRegionCodeByOldName($regionLangName)
	{
		if($regionLangName == '')
			return "";

		static $data = array();

		if(empty($data))
		{
			require_once(dirname(__FILE__).'/rus_post/old_loc_to_codes.php');
			$data = $locToCode;
		}

		return isset($data[$regionLangName]) ? $data[$regionLangName] : "";
	}

	public static function getAdminMessage()
	{
		return array(
			'MESSAGE' => GetMessage(
				'SALE_DH_RPF_DEPRECATED_MESSAGE',
				array(
					'#A1#' => '<a href="/bitrix/admin/sale_delivery_service_edit.php?lang='.LANGUAGE_ID.'&PARENT_ID=0&CLASS_NAME=%5CSale%5CHandlers%5CDelivery%5CAdditionalHandler&SERVICE_TYPE=RUSPOST">',
					'#A2#' => '</a>'
				)
			),
			"TYPE" => "ERROR",
			"HTML" => true
		);
	}
}

AddEventHandler('sale', 'onSaleDeliveryHandlersBuildList', array('CDeliveryRusPostFirst', 'Init'));

?>