<?
/********************************************************************************
Delivery services for Russian Post Service (http://www.russianpost.ru/)
Calculations based on RP rates:
http://www.russianpost.ru/rp/servise/ru/home/postuslug/bookpostandparcel/local#parcel
********************************************************************************/
CModule::IncludeModule('sale');

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/delivery/delivery_rus_post.php');

define('DELIVERY_RP_CSV_PATH', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/sale/ru/delivery/rus_post'); //where we can found csv files

use Bitrix\Main\Config\Option,
	Bitrix\Sale\Location;

class CDeliveryRusPost
{
	private static $MAX_WEIGHT_HEAVY = 20000; // (g)
	private static $MAX_WEIGHT = 10000; // (g)
	private static $ZONES_COUNT = 5;	// Tarif zones count
	private static $BASE_WEIGHT = 500;	// Base weight gram


	//1.1	zone_number => tarif_number
	private static $TARIF_LESS_500 = array(
								1 => 1,
								2 => 2,
								3 => 3,
								4 => 4,
								5 => 5
	);

	private static $TARIF_MORE_500 = array(
								1 => 6,
								2 => 7,
								3 => 8,
								4 => 9,
								5 => 10
	);

	private static $TARIF_HEAVY_WEIGHT = 11;	//1.2
	private static $TARIF_FRAGILE = 14; 		//1.5
	private static $TARIF_DECLARED_VAL = 20;	//4.
	private static $TARIF_AVIA_STANDART = 15;	//2.1
	private static $TARIF_AVIA_HEAVY = 16;		//2.2

	private static $MAX_DIMENSIONS = array("425", "265", "380");

	const LOCATION_CODE_RUSSIA = "0000028023";

	function Init()
	{
		return array(
			/* Basic description */
			'SID' => 'rus_post',
			'MULTISITE_CONFIG' => "Y",
			'NAME' => GetMessage('SALE_DH_RP_NAME'),
			'DESCRIPTION' => GetMessage('SALE_DH_RP_DESCRIPTION').' <a href="http://www.russianpost.ru">http://www.russianpost.ru</a>',
			'DESCRIPTION_INNER' => GetMessage('SALE_DH_RP_DESCRIPTION_INNER'),
			'BASE_CURRENCY' => 'RUB',

			'HANDLER' => __FILE__,

			/* Handler methods */
			'DBGETSETTINGS' => array('CDeliveryRusPost', 'GetSettings'),
			'DBSETSETTINGS' => array('CDeliveryRusPost', 'SetSettings'),
			'GETCONFIG' => array('CDeliveryRusPost', 'GetConfig'),
			'GETFEATURES' => array('CDeliveryRusPost', 'GetFeatures'),

			'COMPABILITY' => array('CDeliveryRusPost', 'Compability'),
			'CALCULATOR' => array('CDeliveryRusPost', 'Calculate'),
			"TRACKING_CLASS_NAME" => '\Bitrix\Sale\Delivery\Tracking\RusPost',

			/* List of delivery profiles */
			'PROFILES' => array(
				'land' => array(
					'TITLE' => GetMessage('SALE_DH_RP_LAND_TITLE'),
					'DESCRIPTION' => GetMessage('SALE_H_RP_LAND_DESCRIPTION'),
					'RESTRICTIONS_WEIGHT' => array(0, self::$MAX_WEIGHT_HEAVY),
					'RESTRICTIONS_SUM' => array(0),
					'TAX_RATE' => 0,
					'RESTRICTIONS_DIMENSIONS' => self::$MAX_DIMENSIONS
					),

				'avia' => array(
					'TITLE' => GetMessage('SALE_DH_RP_AVIA_TITLE'),
					'DESCRIPTION' => GetMessage('SALE_DH_RP_AVIA_DESCRIPTION'),
					'RESTRICTIONS_WEIGHT' => array(0, self::$MAX_WEIGHT_HEAVY),
					'RESTRICTIONS_SUM' => array(0),
					'TAX_RATE' => 0,
					'RESTRICTIONS_DIMENSIONS' => self::$MAX_DIMENSIONS
					)
			)
		);
	}

	function GetConfig($siteId = false)
	{
		$shopLocationId = CSaleHelper::getShopLocationId($siteId);
		$arShopLocation = \CSaleHelper::getLocationByIdHitCached($shopLocationId);

		if(!$arShopLocation)
			$arShopLocation = array();

		if(isset($_REQUEST["RESET_HANDLER_SETTINGS"]) && $_REQUEST["RESET_HANDLER_SETTINGS"] == "Y" && !isset($_REQUEST["apply"]))
		{
			Option::delete('sale', array('name' => 'delivery_rus_post_prev_loc', 'site_id' => $siteId));
			Option::delete('sale', array('name' => 'delivery_rus_post_prev_loc'));
		}

		if(isset($_REQUEST["RESET_TARIF_SETTINGS"]) && $_REQUEST["RESET_TARIF_SETTINGS"] == "Y" && !isset($_REQUEST["apply"]))
		{
			Option::delete('sale', array('name' => 'delivery_rus_post_tarifs', 'site_id' => $siteId));
			Option::delete('sale', array('name' => 'delivery_rus_post_tarifs'));
		}

		$shopPrevLocationId = Option::get('sale', 'delivery_rus_post_prev_loc', "", $siteId);

		/* if shop's location was changed */
		if($shopPrevLocationId == '' || $shopPrevLocationId != $shopLocationId)
		{
			Option::set('sale', 'delivery_rus_post_prev_loc', $shopLocationId, $siteId);
			Option::delete('sale', array('name' => 'delivery_regs_to_zones', 'site_id' => $siteId));
			Option::delete('sale', array('name' => 'delivery_rus_post_tarifs', 'site_id' => $siteId));
		}

		$arConfig = array(
			'CONFIG_GROUPS' => array(
				'zones' => GetMessage('SALE_DH_RP_CONFIG_GROUP_ZONES'),
				'tarifs' => GetMessage('SALE_DH_RP_CONFIG_GROUP_TARIFS'),
				'land' => GetMessage('SALE_DH_RP_CONFIG_GROUP_LAND'),
				'avia' => GetMessage('SALE_DH_RP_CONFIG_GROUP_AVIA'),
			)
		);

		// Zones tab
		$arRegions = CSaleDeliveryHelper::getRegionsList();
		$arZones = array();
		$arZones[0] = GetMessage('SALE_DH_RP_CONFIG_ZONES_EMPTY');

		for ($i = 1; $i <= self::$ZONES_COUNT; $i++)
			$arZones[$i] = GetMessage('SALE_DH_RP_CONFIG_ZONE').' '.$i;

		$arRegsToZones = CSaleHelper::getOptionOrImportValues(
			'delivery_regs_to_zones',
			array('CDeliveryRusPost', 'importZonesFromCsv'),
			array($arShopLocation),
			$siteId
		);

		$arConfig['CONFIG']['RESET_HANDLER_SETTINGS'] = array(
			'TYPE' => 'CUSTOM',
			'TITLE' => GetMessage('SALE_DH_RP_SET_DEFAULT_TARIF_ZONES'),
			'GROUP' => 'zones',
			'DEFAULT' => '<a href="javascript:void(0);" onclick="BX.Sale.Delivery.resetRusPostSettings();">'.GetMessage('SALE_DH_RP_SET_DEFAULT_TARIF_ZONES_SET').'</a>'
		);

		foreach ($arRegions as $regId => $regName)
		{
			$codeByName = self::getRegionCodeByOldName($regName); // old location
			$code = $codeByName <> '' ? $codeByName : $regId;

			if(isset($arRegsToZones[$code]))
			{
				$arConfig['CONFIG']['REG_'.$code] = array(
							'TYPE' => 'DROPDOWN',
							'DEFAULT' => isset($arRegsToZones[$code]) ? $arRegsToZones[$code] : '0',
							'TITLE' => $regName,
							'GROUP' => 'zones',
							'VALUES'=> $arZones
				);
			}
		}

		/*
		tarifs tab
		1. land
		1.1. Base Price
		*/

		$arConfig['CONFIG']['RESET_TARIF_SETTINGS'] = array(
			'TYPE' => 'CUSTOM',
			'TITLE' => GetMessage('SALE_DH_RP_SET_DEFAULT_TARIF'),
			'GROUP' => 'tarifs',
			'DEFAULT' => '<a href="javascript:void(0);" onclick="BX.Sale.Delivery.resetRusPostTarifSettings();">'.GetMessage('SALE_DH_RP_SET_DEFAULT_TARIF_SET').'</a>'
		);

		$arConfig['CONFIG']['tarif_section_1'] = array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_RP_WEIGHT_LESS'),
					'GROUP' => 'tarifs',
		);

		$arTarifs = CSaleHelper::getOptionOrImportValues(
			'delivery_rus_post_tarifs',
			array('CDeliveryRusPost', 'getTarifsByRegionFromCsv'),
			array($arShopLocation),
			$siteId
		);

		foreach ($arZones as $zoneId => $zoneName)
		{
			if($zoneId <= 0)
				continue;

			$tarifId = self::$TARIF_LESS_500[$zoneId];
			$arConfig['CONFIG']['ZONE_RATE_MAIN_'.$zoneId] = array(
						'TYPE' => 'STRING',
						'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
						'TITLE' => $zoneName,
						'GROUP' => 'tarifs',
			);
		}

		$arConfig['CONFIG']['tarif_section_2'] = array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_RP_WEIGHT_MORE'),
					'GROUP' => 'tarifs',
		);

		foreach ($arZones as $zoneId => $zoneName)
		{
			if($zoneId <= 0)
				continue;

			$tarifId = self::$TARIF_MORE_500[$zoneId];

			$arConfig['CONFIG']['ZONE_RATE_ADD_'.$zoneId] = array(
						'TYPE' => 'STRING',
						'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
						'TITLE' => $zoneName,
						'GROUP' => 'tarifs',
			);
		}

		/* Additional services */
		$arConfig['CONFIG']['tarif_add_services'] = array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_RP_ADD_SRV'),
					'GROUP' => 'tarifs',
		);

		/* 1.2 Service heavy weight 10 - 20 kg */
		$tarifId = self::$TARIF_HEAVY_WEIGHT;
		$arConfig['CONFIG']['service_'.$tarifId.'_enabled'] = array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_RP_SRV_HEAVY'),
					'GROUP' => 'tarifs',
					'DEFAULT' => 'Y',
					'HIDE_BY_NAMES' => array('service_'.$tarifId.'_value')
		);

		$arConfig['CONFIG']['service_'.$tarifId.'_value'] = array(
					'TYPE' => 'STRING',
					'TITLE' => GetMessage('SALE_DH_RP_SRV_HEAVY_VAL').' %',
					'GROUP' => 'tarifs',
					'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
		);

		/* 1.5 Service fragile */
		$tarifId = self::$TARIF_FRAGILE;
		$arConfig['CONFIG']['service_'.$tarifId.'_enabled'] = array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_RP_SRV_FRGL'),
					'GROUP' => 'tarifs',
					'DEFAULT' => 'Y',
					'HIDE_BY_NAMES' => array('service_'.$tarifId.'_value'),
					'TOP_LINE' => 'Y'
		);

		$arConfig['CONFIG']['service_'.$tarifId.'_value'] = array(
					'TYPE' => 'STRING',
					'TITLE' => GetMessage('SALE_DH_RP_SRV_FRGL_VAL').' %',
					'GROUP' => 'tarifs',
					'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0'
		);

		/* 4. Service declared value */
		$tarifId = self::$TARIF_DECLARED_VAL;
		$arConfig['CONFIG']['service_'.$tarifId.'_enabled'] = array(
					'TYPE' => 'CHECKBOX',
					'TITLE' => GetMessage('SALE_DH_RP_SRV_DECL'),
					'GROUP' => 'tarifs',
					'DEFAULT' => 'Y',
					'HIDE_BY_NAMES' => array('service_'.$tarifId.'_value'),
					'TOP_LINE' => 'Y'
		);

		$arConfig['CONFIG']['service_'.$tarifId.'_value'] = array(
					'TYPE' => 'STRING',
					'TITLE' => GetMessage('SALE_DH_RP_SRV_DECL_VAL'),
					'GROUP' => 'tarifs',
					'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
		);

		// land tab
		$aviableBoxes = self::getAviableBoxes();

		foreach ($aviableBoxes as $boxId => $arBox)
			CSaleDeliveryHelper::makeBoxConfig($boxId, $arBox, 'land', $arConfig);

		/* 2.1 avia tab*/

		foreach ($aviableBoxes as $boxId => $arBox)
			CSaleDeliveryHelper::makeBoxConfig($boxId, $arBox, 'avia', $arConfig);

		$tarifId = self::$TARIF_AVIA_STANDART;
		$arConfig['CONFIG']['tarif_avia_services'] = array(
					'TYPE' => 'SECTION',
					'TITLE' => GetMessage('SALE_DH_RP_TARIFS_AVIA'),
					'GROUP' => 'avia',
		);

		$arConfig['CONFIG']['tarif_avia_'.$tarifId.'_value'] = array(
					'TYPE' => 'STRING',
					'TITLE' => GetMessage('SALE_DH_RP_TARIF_AVIA_STNDRT'),
					'GROUP' => 'avia',
					'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
		);

		$tarifId = self::$TARIF_AVIA_HEAVY;
		$arConfig['CONFIG']['tarif_avia_'.$tarifId.'_value'] = array(
					'TYPE' => 'STRING',
					'TITLE' => GetMessage('SALE_DH_RP_TARIF_AVIA_HEAVY'),
					'GROUP' => 'avia',
					'DEFAULT' => isset($arTarifs[$tarifId]) ? $arTarifs[$tarifId] : '0',
		);

		return $arConfig;
	}

	function GetSettings($strSettings)
	{
		$result = unserialize($strSettings);

		if(isset($result['RESET_HANDLER_SETTINGS']))
			unset($result['RESET_HANDLER_SETTINGS']);

		if(isset($result['SET_DEFAULT_TARIF_ZONES']))
			unset($result['SET_DEFAULT_TARIF_ZONES']);

		if(isset($result['RESET_TARIF_SETTINGS']))
			unset($result['RESET_TARIF_SETTINGS']);

		if(isset($_REQUEST["RESET_HANDLER_SETTINGS"]) && $_REQUEST["RESET_HANDLER_SETTINGS"] == "Y" && !isset($_REQUEST["apply"]))
		{
			foreach($result as $key => $value)
				if(mb_substr($key, 0, 4) == 'REG_')
					unset($result[$key]);
		}

		if(isset($_REQUEST["RESET_TARIF_SETTINGS"]) && $_REQUEST["RESET_TARIF_SETTINGS"] == "Y" && !isset($_REQUEST["apply"]))
		{
			foreach($result as $key => $value)
				if(mb_substr($key, 0, 5) == 'ZONE_' || mb_substr($key, 0, 6) == 'tarif_' || mb_substr($key, 0, 8) == 'service_')
					unset($result[$key]);
		}

		return $result;
	}

	function SetSettings($arSettings)
	{
		if(isset($arSettings['RESET_HANDLER_SETTINGS']))
			unset($arSettings['RESET_HANDLER_SETTINGS']);

		if(isset($arSettings['SET_DEFAULT_TARIF_ZONES']))
			unset($arSettings['SET_DEFAULT_TARIF_ZONES']);

		if(isset($arSettings['RESET_TARIF_SETTINGS']))
			unset($arSettings['RESET_TARIF_SETTINGS']);


		foreach ($arSettings as $key => $value)
		{
			if ($value <> '' && (mb_substr($key, 0, 4) != 'REG_' || $value != '0'))
				$arSettings[$key] = $value;
			else
				unset($arSettings[$key]);
		}

		return serialize($arSettings);
	}

	function GetFeatures($arConfig)
	{
		$arResult = array();

		if ($arConfig["service_".self::$TARIF_FRAGILE."_enabled"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_RP_FEATURE_MARK")] = GetMessage("SALE_DH_RP_FEATURE_MARKED");

		if ($arConfig["service_".self::$TARIF_DECLARED_VAL."_enabled"]["VALUE"] == "Y")
			$arResult[GetMessage("SALE_DH_RP_FEATURE_VALUE")] = GetMessage("SALE_DH_RP_FEATURE_ENABLED");

		return $arResult;
	}

	function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		$maxWeight = self::isHeavyEnabled($arConfig) ? self::$MAX_WEIGHT_HEAVY : self::$MAX_WEIGHT;

		$arPacks = CSaleDeliveryHelper::getBoxesFromConfig($profile, $arConfig);

		$arPackagesParams = CSaleDeliveryHelper::getRequiredPacks(
													$arOrder["ITEMS"],
													$arPacks,
													$maxWeight);

		$packageCount = count($arPackagesParams);

		if(intval($packageCount) <= 0)
		{
			return array(
						"RESULT" => "ERROR",
						"TEXT" => GetMessage("SALE_DH_RP_OVERLOAD"),
					);
		}

		$totalPrice = 0;
		$arLocationTo = \CSaleHelper::getLocationByIdHitCached($arOrder['LOCATION_TO']);

		try
		{
			foreach ($arPackagesParams as $arPackage)
				$totalPrice += self::calculatePackPrice($arPackage, $profile, $arConfig, $arLocationTo);
		}
		catch(\Bitrix\Main\SystemException $e)
		{
			return array(
				"RESULT" => "ERROR",
				"TEXT" => $e->getMessage()
			);
		}

		$arResult = array(
			'RESULT' => 'OK',
			'VALUE' => $totalPrice,
			'PACKS_COUNT' => $packageCount
		);
		return $arResult;
	}

	function Compability($arOrder, $arConfig)
	{
		$profiles = array('land', 'avia');

		$bHevyWeightEnabled = self::isConfCheckedVal($arConfig, 'service_'.self::$TARIF_HEAVY_WEIGHT.'_enabled');

		$maxWeight = $bHevyWeightEnabled ? self::$MAX_WEIGHT_HEAVY : self::$MAX_WEIGHT;

		if (!empty($arOrder["ITEMS"]) && is_array($arOrder["ITEMS"]))
		{
			foreach ($arOrder["ITEMS"] as $arItem)
			{
				if (floatval($arItem["WEIGHT"]) > $maxWeight)
				{
					$profiles = array();
					break;
				}
			}
		}

		$locationToCode = CSaleHelper::getLocationByIdHitCached($arOrder['LOCATION_TO']);

		if(self::getLocationToCode($locationToCode) == '')
			$profiles = array();

		$arRes = array();

		foreach ($profiles as $profile)
		{
			$aviableBoxes = CSaleDeliveryHelper::getBoxesFromConfig($profile, $arConfig);

			foreach ($aviableBoxes as $arBox)
			{
				if (CSaleDeliveryHandler::checkDimensions($arOrder["MAX_DIMENSIONS"], $arBox["DIMENSIONS"]))
				{
					$arRes[] = $profile;
					break;
				}
			}
		}

		return $arRes;
	}

	/* Particular services helper functions*/

	public function importZonesFromCsv(array $arShopLocation)
	{
		if(empty($arShopLocation) || !isset($arShopLocation["REGION_ID"]) || !isset($arShopLocation['REGION_NAME_LANG']))
			return array();

		$regionCodeFromCode = $regionCodeFromName = "";

		$dbRes = Location\LocationTable::getList(array(
			'filter' => array(
				'=TYPE.CODE' => 'REGION',
				'=REGION_ID' => intval($arShopLocation["REGION_ID"]),
				'=CITY_ID' => false
			),
			'select' => array(
				'ID', 'CODE'
			)
		));

		if($locReg = $dbRes->fetch())
			$regionCodeFromCode = $locReg["CODE"];

		$regionCodeFromName = self::getRegionCodeByOldName($arShopLocation['REGION_NAME_LANG']);

		$COL_REG_CODE = 0;
		$csvFile = CSaleHelper::getCsvObject(DELIVERY_RP_CSV_PATH.'/zones.csv');
		$arRegsTo = $csvFile->Fetch();
		$arRegionsZones = array();

		while ($arRes = $csvFile->Fetch())
		{
			if(isset($arRes[$COL_REG_CODE])
				&& (
					$regionCodeFromCode == $arRes[$COL_REG_CODE]
					|| $regionCodeFromName == $arRes[$COL_REG_CODE]
				)
			)
			{
				for ($i = 1, $l = count($arRes) - 1; $i <= $l; $i++)
				{
					if(isset($arRegsTo[$i])
						&&
						isset($arRes[$i])
					)
					{
						$arRegionsZones[$arRegsTo[$i]] = $arRes[$i];
					}
				}

				break;
			}
		}

		return $arRegionsZones;
	}

	/**
	 * If zip codes imported to locations, we try to link regions to zones
	 * using file /bitrix/modules/sale/delivery/rus_post/zip_zones.csv created
	 * from http://info.russianpost.ru/database/tzones.html
	 */
	public function importZonesFromZipCsv()
	{
		$COL_ZIP = 0;
		$COL_ZONE = 1;
		$csvFile = CSaleHelper::getCsvObject(DELIVERY_RP_CSV_PATH.'/zip_zones.csv');
		$arRes = $csvFile->Fetch();

		$arRegions = CSaleDeliveryHelper::getRegionsList();
		$arRegionsZones = array();

		while ($arRes = $csvFile->Fetch())
		{
			$location = CSaleLocation::GetByZIP($arRes[$COL_ZIP]);

			if($location === false)
				continue;

			if(isset($arRegions[$location['REGION_ID']]))
				$arRegionsZones[$location['REGION_ID']] = $arRes[$COL_ZONE];

			unset($arRegions[$location['REGION_ID']]);

			if(empty($arRegions))
				break;
		}

		return $arRegionsZones;
	}

	public static function getTarifNumFromCsv(array $arShopLocation)
	{
		if(empty($arShopLocation) || !isset($arShopLocation['REGION_NAME_LANG'], $arShopLocation["ID"]))
			return false;

		$regionCodeFromCode = $regionCodeFromName = "";

		$loc = \CSaleHelper::getLocationByIdHitCached($arShopLocation["ID"]);

		$res = \Bitrix\Sale\Location\LocationTable::getList(array(
				'filter' => array('=ID' => $loc["REGION_ID"]),
				'select' => array('CODE')
		));

		if($locReg = $res->fetch())
		{
			$regionCodeFromCode = $locReg["CODE"];
		}

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
		$tarifNumber = self::getTarifNumFromCsv($arShopLocation);

		if($tarifNumber === false)
			return false;

		$csvFile = CSaleHelper::getCsvObject(DELIVERY_RP_CSV_PATH.'/tarif_data.csv');
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

	private static function isHeavyEnabled(&$arConfig)
	{
		return self::isConfCheckedVal($arConfig, 'service_'.self::$TARIF_HEAVY_WEIGHT.'_enabled');
	}

	private static function getAviableBoxes()
	{
		return array(
					array(
						"NAME" => GetMessage("SALE_DH_RP_STNDRD_BOX"),
						"DIMENSIONS" => array("425", "265", "380")
						)
			);
	}

	private static function getLocationToCode($arLocationTo)
	{
		$code = self::getRegionCodeByOldName($arLocationTo['REGION_NAME_LANG']); // old location

		if($code == '' && CSaleLocation::isLocationProMigrated())
		{
			$dbRes = Location\LocationTable::getList(array(
				'filter' => array(
					'=TYPE.CODE' => 'REGION',
					'=REGION_ID' => intval($arLocationTo["REGION_ID"]),
					'=CITY_ID' => false
				),
				'select' => array(
					'ID', 'CODE', 'NAME'
				)
			));

			if($locReg = $dbRes->fetch())
				$code = $locReg["CODE"];
		}

		return $code;
	}

	private static function calculatePackPrice($arPackage, $profile, $arConfig, $arLocationTo)
	{
		$arDebug = array();

		/*1 Land price
		1.1 Base Price less 10 kg*/

		$code = self::getLocationToCode($arLocationTo);

		if($code == '')
			throw new \Bitrix\Main\SystemException(GetMessage("SALE_DH_RP_ERROR_LOCATION_NOT_FOUND"));

		$zoneTo = self::getConfValue($arConfig, 'REG_'.$code);
		$basePrice = floatval(self::getConfValue($arConfig, 'ZONE_RATE_MAIN_'.$zoneTo));

		if($basePrice <=0)
			throw new \Bitrix\Main\SystemException(GetMessage("SALE_DH_RP_CALCULATE_ERROR"));

		$arDebug[] = 'Base Price less 500 g: '.$basePrice;

		if($arPackage['WEIGHT'] > self::$BASE_WEIGHT)
		{
			$addWeight = ceil($arPackage['WEIGHT'] / self::$BASE_WEIGHT - 1);
			$addPrice = floatval(self::getConfValue($arConfig, 'ZONE_RATE_ADD_'.$zoneTo));
			$arDebug[] = 'Price for additional weight more than 500 g: '.$addWeight * $addPrice;
			$basePrice += $addWeight * $addPrice;
		}

		$totalPrice = $basePrice;

		/* 1.2 Service "heavy weight" 10 - 20 kg*/
		$hwPrice = 0;
		if($arPackage['WEIGHT'] >= self::$MAX_WEIGHT)
		{
			$hwTarif = floatval(self::getConfValue($arConfig, 'service_'.self::$TARIF_HEAVY_WEIGHT.'_value'));
			$hwPrice += $totalPrice*$hwTarif/100;
			$arDebug[] = 'Heavy weight: '.$hwPrice;
			$totalPrice += $hwPrice;
		}

		/* 1.5 Service "fragile" */
		$fPrice = 0;
		if(self::isConfCheckedVal($arConfig, 'service_'.self::$TARIF_FRAGILE.'_enabled'))
		{
			$fTarif = floatval(self::getConfValue($arConfig, 'service_'.self::$TARIF_FRAGILE.'_value'));
			$fPrice += $totalPrice*$fTarif/100;
			$arDebug[] = 'Fragile: '.$fPrice;
			$totalPrice += $fPrice;
		}

		/* 4. Service "declared value" */
		$dvPrice = 0;
		if(self::isConfCheckedVal($arConfig, 'service_'.self::$TARIF_DECLARED_VAL.'_enabled'))
		{
			$dvTarif = floatval(self::getConfValue($arConfig, 'service_'.self::$TARIF_DECLARED_VAL.'_value'));
			$dvPrice += ($arPackage['PRICE'])*$dvTarif;
			$arDebug[] = 'Declared value: '.$dvPrice;
			$totalPrice += $dvPrice;
		}

		if($profile == 'avia')
		{
			$aviaPrice = 0;
			$aviaPrice = floatval(self::getConfValue($arConfig, 'tarif_avia_'.self::$TARIF_AVIA_STANDART.'_value'));
			$arDebug[] = 'avia price: '.$aviaPrice;
			$totalPrice += $aviaPrice;

			$aviaHeavyPrice = 0;
			if($arPackage['WEIGHT'] > self::$MAX_WEIGHT)
			{
				$aviaHeavyPrice = floatval(self::getConfValue($arConfig, 'tarif_avia_'.self::$TARIF_AVIA_HEAVY.'_value'));
				$arDebug[] = 'avia heavy price: '.$aviaHeavyPrice;
				$totalPrice += $aviaHeavyPrice;
			}
		}

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
}

AddEventHandler('sale', 'onSaleDeliveryHandlersBuildList', array('CDeliveryRusPost', 'Init'));
?>