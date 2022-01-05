<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\CompanyTable;
use Bitrix\Sale\Result;
use \Bitrix\Sale\Shipment;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\Spsr\Location;
use Bitrix\Sale\Delivery\CalculationResult;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses(
	'sale',
	array(
		'Sale\Handlers\Delivery\Spsr\Cache' => 'handlers/delivery/spsr/cache.php',
		'Sale\Handlers\Delivery\SpsrProfile' => 'handlers/delivery/spsr/profile.php',
		'Sale\Handlers\Delivery\Spsr\Request' => 'handlers/delivery/spsr/request.php',
		'Sale\Handlers\Delivery\SpsrTracking' => 'handlers/delivery/spsr/tracking.php',
		'Sale\Handlers\Delivery\Spsr\Location' => 'handlers/delivery/spsr/location.php',
		'Sale\Handlers\Delivery\Spsr\Calculator' => 'handlers/delivery/spsr/calculator.php'
	)
);

class SpsrHandler extends \Bitrix\Sale\Delivery\Services\Base
{
	/** @var string */
	protected $handlerCode = 'BITRIX_SPSR';

	/** @var bool $canHasProfiles This handler can has profiles */
	protected static $canHasProfiles = true;
	/** @var bool $whetherAdminExtraServicesShow This handler uses extra services */
	protected static $whetherAdminExtraServicesShow = true;
	/** @var string Tracking class */
	protected $trackingClass = '\Sale\Handlers\Delivery\SpsrTracking';

	/**
	 * @inheritDoc
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLV_SRV_SPSR_TITLE");
	}

	/**
	 * @inheritDoc
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage(
			"SALE_DLV_SRV_SPSR_DESCRIPTION",
			array(
				'#A1#' => '<a href="http://www.spsr.ru/" target="_blank">',
				'#A2#' => '</a>',
			)
		);
	}

	/**
	 * Calculates prices for concrete service
	 * @param Shipment $shipment
	 * @param $tariff
	 * @return CalculationResult
	 */
	public function calculateTariff(Shipment $shipment, $tariff)
	{
		return (new CalculationResult())->addError(new Error('The company no longer exists'));
	}

	/**
	 * @inheritDoc
	 */
	protected function getConfigStructure()
	{
		$natures = array_intersect_key(
			self::getNaturesList(),
			array_flip(
					self::getProfileNatures()
			)
		);

		$result = array(
			"MAIN" => array(
				"TITLE" => Loc::getMessage("SALE_DLV_SRV_SPSR_MAIN_TITLE"),
				"DESCRIPTION" => Loc::getMessage("SALE_DLV_SRV_SPSR_MAIN_DSCR"),
				"ITEMS" => array(
					"CALCULATE_IMMEDIATELY" => array(
						'TYPE' => 'Y/N',
						"NAME" => Loc::getMessage("SALE_DLV_SRV_SPSR_CALCULATE_IMMEDIATELY"),
						"DEFAULT" => "Y"
					),
					"DEFAULT_WEIGHT" => array(
						'TYPE' => 'NUMBER',
						"NAME" => Loc::getMessage("SALE_DLV_SRV_SPSR_DEFAULT_WEIGHT"),
						"DEFAULT" => "1000"
					),
					"AMOUNT_CHECK" => array(
						"TYPE" => "ENUM",
						"NAME" => Loc::getMessage("SALE_DLV_SRV_SPSR_AMOUNT_CHECK"),
						"DEFAULT" => "1",
						"OPTIONS" => array(
								-1 => Loc::getMessage("SALE_DLV_SRV_SPSR_AMOUNT_CHECK__1"),
								0 => Loc::getMessage("SALE_DLV_SRV_SPSR_AMOUNT_CHECK_0"),
								1 => Loc::getMessage("SALE_DLV_SRV_SPSR_AMOUNT_CHECK_1")
						)
					),
					"NATURE" => array(
						"TYPE" => "ENUM",
						"NAME" => Loc::getMessage("SALE_DLV_SRV_SPSR_NATURE"),
						"DEFAULT" => "1",
						"REQUIRED" => true,
						"OPTIONS" => $natures
					),
					"LOGIN" => array(
						"TYPE" => "STRING",
						"NAME" => Loc::getMessage("SALE_DLV_SRV_SPSR_LOGIN"),
						"DEFAULT" => ""
					),
					"PASS" => array(
						"TYPE" => "STRING",
						"NAME" => Loc::getMessage("SALE_DLV_SRV_SPSR_PASS"),
						"DEFAULT" => ""
					),
					"ICN" => array(
						"TYPE" => "STRING",
						"NAME" => Loc::getMessage("SALE_DLV_SRV_SPSR_ICN"),
						"DEFAULT" => ""
					)
				)
			)
		);

		return $result;
	}

	/**
	 * @return int[] Natures of the cargo code => names
	 */
	protected static function getNaturesList()
	{
		$result = array();
		$codes = array(1, 2, 17, 18, 19, 20, 21, 22, 23, 24);

		foreach($codes as $code)
			$result[$code] = Loc::getMessage('SALE_DLV_SRV_SPSR_NATURE_'.$code);

		return $result;
	}

	/**
	 * @param int $profileId
	 * @return int[]
	 */
	protected static function getProfileNatures($profileId = 0)
	{
		if($profileId <= 0)
			return array(1, 2, 17, 18, 19, 20, 21, 22, 23, 24);

		$natures = array(
				20 => array(1, 2, 17), 								//colibri
				21 => array(1, 2, 17), 								//gepard-express 13
				22 => array(1, 2, 17), 								//gepard-express 18
				23 => array(1, 2, 17), 								//gepard-express
				24 => array(1, 2, 17, 18, 19, 20, 21, 22, 23, 24), 	//pelican-standart
				25 => array(1, 2, 17, 18, 19, 20, 21, 22, 23, 24),	//pelican-econom
				26 => array(1, 2, 17, 18, 19, 20, 21, 22 ), 		//bizon-cargo
				27 => array(1, 2, 17, 18, 19, 20, 21, 22, 23, 24), 	//fraxt
				28 => array(1, 2, 17, 18, 19, 20, 21, 22, 23, 24), 	//pelican-online
				35 => array(1, 2, 17 ), 							//gepard-online
				36 => array(1, 2, 17, 18, 19, 20, 21, 22, 23, 24) 	//zebra-online
		);

		return isset($natures[$profileId]) ? $natures[$profileId] : array();
	}

	public function isCalculatePriceImmediately()
	{
		return $this->config['MAIN']['CALCULATE_IMMEDIATELY'] == 'Y';
	}

	/**
	 * @return array Business values.
	 */
	public static function onGetBusinessValueConsumers()
	{
		if(!self::isHoldingUsed())
			return array();

		static $consumers;

		if(!$consumers)
		{
			$providerKeys = array('', 'VALUE', 'COMPANY');

			$codes = array(
				'DELIVERY_SPSR_LOGIN' => array('NAME' => Loc::getMessage('SALE_DLV_SRV_SPSR_LOGIN'), 'SORT' =>  100, 'GROUP' => 'DELIVERY_SPSR_AUTH', 'PROVIDERS' => $providerKeys),
				'DELIVERY_SPSR_PASS' => array('NAME' => Loc::getMessage('SALE_DLV_SRV_SPSR_PASS'), 'SORT' =>  200, 'GROUP' => 'DELIVERY_SPSR_AUTH', 'PROVIDERS' => $providerKeys),
				'DELIVERY_SPSR_ICN' => array('NAME' => Loc::getMessage('SALE_DLV_SRV_SPSR_ICN'), 'SORT' =>  300, 'GROUP' => 'DELIVERY_SPSR_AUTH', 'PROVIDERS' => $providerKeys),
			);

			$consumers = array(
				'SORT'  => 400,
				'GROUP' => 'DELIVERY',
				'CODES' => $codes
			);
		}

		return $consumers;
	}

	public static function onGetBusinessValueGroups()
	{
		if(!self::isHoldingUsed())
			return array();

		return array(
			'DELIVERY_SPSR_AUTH' => array('NAME' => Loc::getMessage('SALE_DLV_SRV_SPSR_BV_AUTH'), 'SORT' => 100),
		);
	}

	private static function isHoldingUsed()
	{
		static $result = null;

		if($result !== null)
			return $result;

		$dbRes = CompanyTable::getList(array(
			'filter' => array('=ACTIVE' => 'Y'),
			'select' => array('CNT'),
    		'runtime' => array(
				new ExpressionField('CNT', 'COUNT(*)'
			))
		));

		if($row = $dbRes->fetch())
			if(intval($row['CNT']) > 1)
				$result = true;

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public static function getChildrenClassNames()
	{
		return array(
			'\Sale\Handlers\Delivery\SpsrProfile'
		);
	}

	/**
	 * @return string Company name.
	 */
	public static function getCompanyName()
	{
		return '';
	}

	/**
	 * Returns SID required for requests.
	 * @return Result
	 */
	public function getSidResult($shipment = null)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}

	/**
	 * @param Shipment|null $shipment
	 * @return string
	 */
	public function getICN($shipment = null)
	{
		return '';
	}

	/**
	 * Returns services list with parameters.
	 * @return Result
	 */
	public function getServiceTypes($shipment = null)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}

	/**
	 * @return int[] Services ids we can process.
	 */
	protected function getKnownServices()
	{
		return array(20, 21, 22, 23, 24, 25, 26, 27, 28, 35, 36);
	}

	/**
	 * @inheritDoc
	 */
	public function getEmbeddedExtraServicesList()
	{
		return self::getAlltExtraServices();
	}

	public static function getAlltExtraServices()
	{
		return array(
			"SMS" => array(
				"NAME" => Loc::getMessage('SALE_DLV_SRV_SPSR_SMS'),
				"SORT" => 100,
				"RIGHTS" => "NYN",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => Loc::getMessage('SALE_DLV_SRV_SPSR_SMS_DESCR'),
				"INIT_VALUE" => "N",
				"PARAMS" => array("PRICE" => 0)
			),
			"SMS_RECV" => array(
				"NAME" => Loc::getMessage('SALE_DLV_SRV_SPSR_SMS_RECV'),
				"SORT" => 100,
				"RIGHTS" => "NYY",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => Loc::getMessage('SALE_DLV_SRV_SPSR_SMS_RECV_DESCR'),
				"INIT_VALUE" => "Y",
				"PARAMS" => array("PRICE" => 0)
			),
			"BEFORE_SIGNAL" => array(
				"NAME" => Loc::getMessage('SALE_DLV_SRV_SPSR_BEFORE_SIGNAL'),
				"SORT" => 100,
				"RIGHTS" => "NYY",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => Loc::getMessage('SALE_DLV_SRV_SPSR_BEFORE_SIGNAL_DESCR'),
				"INIT_VALUE" => "N",
				"PARAMS" => array("PRICE" => 0)
			),
			"BY_HAND" => array(
				"NAME" => Loc::getMessage('SALE_DLV_SRV_SPSR_BY_HAND'),
				"SORT" => 100,
				"RIGHTS" => "NYY",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => Loc::getMessage('SALE_DLV_SRV_SPSR_BY_HAND_DESCR'),
				"INIT_VALUE" => "N",
				"PARAMS" => array("PRICE" => 0)
			),
			"ICD" => array(
				"NAME" => Loc::getMessage('SALE_DLV_SRV_SPSR_ICD'),
				"SORT" => 100,
				"RIGHTS" => "NYY",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => Loc::getMessage('SALE_DLV_SRV_SPSR_ICD_DESCR'),
				"INIT_VALUE" => "N",
				"PARAMS" => array("PRICE" => 0)
			),
			"TO_BE_CALLED_FOR" => array(
				"NAME" => Loc::getMessage('SALE_DLV_SRV_SPSR_TO_BE_CALLED_FOR'),
				"SORT" => 100,
				"RIGHTS" => "NYY",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => "",
				"INIT_VALUE" => "N",
				"PARAMS" => array("PRICE" => 0)
			),
			"PLAT_TYPE" => array(
				"NAME" => Loc::getMessage('SALE_DLV_SRV_SPSR_PLAT_TYPE'),
				"SORT" => 100,
				"RIGHTS" => "NYN",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => Loc::getMessage('SALE_DLV_SRV_SPSR_PLAT_TYPE_DESCR'),
				"INIT_VALUE" => "N",
				"PARAMS" => array("PRICE" => 0)
			)
		);
	}

	/**
	 * @param Shipment|null $shipment
	 * @return array Profiles list code => name
	 */
	public function getProfilesList($shipment = null)
	{
		return [];
	}

	/**
	 * @param Shipment $shipment
	 * @return int[] Services types ids compatible with shipment
	 */
	public function getCompatibleProfiles(Shipment $shipment)
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public static function install()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandler(
			'sale',
			'onSaleDeliveryTrackingClassNamesBuildList',
			'sale',
			'\Sale\Handlers\Delivery\SpsrHandler',
			'onSaleDeliveryTrackingClassNamesBuildList'
		);

		Location::install();
	}

	/**
	 * @inheritDoc
	 */
	public static function unInstall()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->unRegisterEventHandler(
			'sale',
			'onSaleDeliveryTrackingClassNamesBuildList',
			'sale',
			'\Sale\Handlers\Delivery\SpsrHandler',
			'onSaleDeliveryTrackingClassNamesBuildList'
		);
		Location::unInstall();
	}

	public static function onSaleDeliveryTrackingClassNamesBuildList()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			array(
				'\Sale\Handlers\Delivery\SpsrTracking' => '/bitrix/modules/sale/handlers/delivery/spsr/tracking.php'
			),
			'sale'
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function isInstalled()
	{
		return Location::isInstalled();
	}

	public function getProfilesDefaultParams()
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public static function canHasProfiles()
	{
		return self::$canHasProfiles;
	}

	/**
	 * @inheritDoc
	 */
	public static function whetherAdminExtraServicesShow()
	{
		return self::$whetherAdminExtraServicesShow;
	}

	/**
	 * @inheritDoc
	 */
	public function getAdminAdditionalTabs()
	{
		global $APPLICATION;

		ob_start();
		$APPLICATION->IncludeComponent(
			"bitrix:sale.location.map",
			"",
			array(
				"EXTERNAL_LOCATION_CLASS" => '\Sale\Handlers\Delivery\Spsr\Location'
			),
			false
		);
		$content = ob_get_contents();
		ob_end_clean();

		return array(
			array(
				"TAB" => Loc::getMessage('SALE_DLVRS_ADD_LOC_TAB'),
				"TITLE" => Loc::getMessage('SALE_DLVRS_ADD_LOC_TAB_TITLE'),
				"CONTENT" => $content
			)
		);
	}

	/** @inheritDoc */
	public static function isHandlerCompatible()
	{
		return false;
	}
}
