<?

use Bitrix\Sale\TradingPlatform\YMarket;
use Bitrix\Sale;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\EntityMarker;
use Bitrix\Main\EventResult;
use Bitrix\Sale\Delivery;
use Bitrix\Main\Loader;
use Bitrix\Main\Event;


IncludeModuleLangFile(__FILE__);

/**
 * Yandex market purchase processing
 */

class CSaleYMHandler
{
	const JSON = 0;
	const XML = 1;

	const ERROR_STATUS_400 = "400 Bad Request";
	const ERROR_STATUS_401 = "401 Unauthorized";
	const ERROR_STATUS_403 = "403 Forbidden";
	const ERROR_STATUS_404 = "404 Not Found";
	const ERROR_STATUS_405 = "405 Method Not Allowed";
	const ERROR_STATUS_415 = "415 Unsupported Media Type";
	const ERROR_STATUS_420 = "420 Enhance Your Calm";
	const ERROR_STATUS_500 = "500 Internal Server Error";
	const ERROR_STATUS_503 = "503 Service Unavailable";

	const DATE_FORMAT = "d-m-Y";
	const XML_ID_PREFIX = "ymarket_";

	const TRADING_PLATFORM_CODE = "ymarket";

	protected $communicationFormat = self::JSON;
	protected $siteId = "";
	protected $authType = "HEADER"; // or URL

	const LOG_LEVEL_DISABLE = 0;
	const LOG_LEVEL_ERROR = 10;
	const LOG_LEVEL_INFO = 20;
	const LOG_LEVEL_DEBUG = 30;

	const NOT_ACCEPT_OLD_PRICE = 0;
	const ACCEPT_OLD_PRICE = 1;

	protected $logLevel = self::LOG_LEVEL_ERROR;

	protected $oAuthToken = null;
	protected $oAuthClientId = null;
	protected $oAuthLogin = null;

	protected $mapDelivery = array();
	protected $outlets = array();
	protected $mapPaySystems = array();

	protected $personTypeId = null;
	protected $campaignId = null;
	protected $yandexApiUrl = null;
	protected $yandexToken = null;
	protected $orderProps = array(
		"FIO" => "FIO",
		"EMAIL" => "EMAIL",
		"PHONE" => "PHONE",
		"ZIP" => "ZIP",
		"CITY" => "CITY",
		"LOCATION" => "LOCATION",
		"ADDRESS" => "ADDRESS"
	);

	protected $locationMapper = null;
	protected $active = true;
	protected $isAcceptOldPrice = self::NOT_ACCEPT_OLD_PRICE;
	protected $defaultDeliveryPeriodFrom = 7;  //days
	protected $defaultDeliveryPeriodTo = 21;
	protected $deliveryToPaysystem = array();

	protected static $isYandexRequest = false;

	/**
	 * CSaleYMHandler constructor.
	 * @param array $arParams
	 */
	public function __construct($arParams = array())
	{
		$this->siteId = $this->getSiteId($arParams);

		$settings = $this->getSettingsBySiteId($this->siteId);

		if(isset($settings["OAUTH_TOKEN"]))
			$this->oAuthToken = $settings["OAUTH_TOKEN"];

		if(isset($settings["OAUTH_CLIENT_ID"]))
			$this->oAuthClientId = $settings["OAUTH_CLIENT_ID"];

		if(isset($settings["OAUTH_LOGIN"]))
			$this->oAuthLogin = $settings["OAUTH_LOGIN"];

		if(isset($settings["DELIVERIES"]))
			$this->mapDelivery = $settings["DELIVERIES"];

		if(isset($settings["OUTLETS_IDS"]))
			$this->outlets = $settings["OUTLETS_IDS"];

		if(isset($settings["PAY_SYSTEMS"]))
			$this->mapPaySystems = $settings["PAY_SYSTEMS"];

		if(isset($settings["PERSON_TYPE"]))
			$this->personTypeId = $settings["PERSON_TYPE"];

		if(isset($settings["CAMPAIGN_ID"]))
			$this->campaignId = $settings["CAMPAIGN_ID"];

		if(isset($settings["YANDEX_URL"]))
			$this->yandexApiUrl = $settings["YANDEX_URL"];

		if(isset($settings["YANDEX_TOKEN"]))
			$this->yandexToken = $settings["YANDEX_TOKEN"];

		if(isset($settings["AUTH_TYPE"]))
			$this->authType = $settings["AUTH_TYPE"];

		if(isset($settings["DATA_FORMAT"]))
			$this->communicationFormat = $settings["DATA_FORMAT"];

		if(isset($settings["LOG_LEVEL"]))
			$this->logLevel = $settings["LOG_LEVEL"];

		if(isset($settings["ORDER_PROPS"]) && is_array($settings["ORDER_PROPS"]))
			$this->orderProps = $settings["ORDER_PROPS"];

		if(isset($settings["IS_ACCEPT_OLD_PRICE"]))
			$this->isAcceptOldPrice = $settings["IS_ACCEPT_OLD_PRICE"];

		if(isset($settings["PERIOD_FROM"]))
			$this->defaultDeliveryPeriodFrom = intval($settings["PERIOD_FROM"]);

		if(isset($settings["PERIOD_TO"]))
			$this->defaultDeliveryPeriodTo = intval($settings["PERIOD_TO"]);

		if(isset($settings["DLV_PS"]))
			$this->deliveryToPaysystem = $settings["DLV_PS"];

		$this->active = static::isActive();
		$this->locationMapper = new CSaleYMLocation;
	}

	public static function isActive()
	{
		return YMarket\YandexMarket::getInstance()->isActive();
	}

	/**
	 * @param bool $activity Set or unset activity
	 * @return \Bitrix\Main\Entity\UpdateResult|bool
	 */
	public static function setActivity($activity)
	{
		if($activity)
			static::eventsStart();
		else
			static::eventsStop();

		$settings = static::getSettings();

		if($activity && empty($settings) && static::install())
		{
				$settings = static::getSettings(false);
		}

		if(!empty($settings))
		{
			if($activity)
				$result = YMarket\YandexMarket::getInstance()->setActive();
			else
				$result = YMarket\YandexMarket::getInstance()->unsetActive();
		}
		else
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * @param string $siteId
	 * @return bool
	 */
	protected function checkSiteId($siteId)
	{
		$result = false;
		$rsSites = CSite::GetList($b = "", $o = "", Array(
			"LID" => $siteId,
			"ACTIVE"=>"Y"
		));

		if($arRes = $rsSites->Fetch())
			$result = true;

		return $result;
	}

	/**
	 * @param array $arParams
	 * @return mixed|string
	 */
	protected function getSiteId($arParams)
	{
		$result = "";

		if(
			isset($arParams["SITE_ID"])
			&& strlen($arParams["SITE_ID"]) > 0
			&& $this->checkSiteId($arParams["SITE_ID"])
		)
		{
			$result = $arParams["SITE_ID"];
		}
		elseif(defined("SITE_ID"))
		{
			$result = SITE_ID;
		}
		else
		{
			$rsSites = CSite::GetList($b = "", $o = "", Array(
				"ACTIVE"=> "Y",
				"DEF" => "Y"
			));

			if($arRes = $rsSites->Fetch())
				$result = $arRes["LID"];
		}

		return $result;
	}

	/**
	 * Returns Yandex-Market settings
	 * @param bool $cached Return cached or ont value
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getSettings($cached = true)
	{
		static $settings = null;

		if($settings === null || !$cached)
		{
			$settingsRes = Bitrix\Sale\TradingPlatformTable::getList(array(
				'filter'=>array('=CODE' => static::TRADING_PLATFORM_CODE)
			));

			$settings = $settingsRes->fetch();

			if(!$settings || !is_array($settings))
				$settings = array();
		}

		return $settings;
	}

	/**
	 * Returns yandex-market settings for concrete site
	 * @param $siteId string Site idenifier
	 * @param bool $cached Return cached or ont value
	 * @return array
	 */
	public static function getSettingsBySiteId($siteId, $cached = true)
	{
		$settings = static::getSettings($cached);
		return isset($settings["SETTINGS"][$siteId]) ? $settings["SETTINGS"][$siteId] : array();
	}

	/**
	 * Saves settings
	 * @param $arSettings array Settings array to save
	 * @return bool
	 */
	public static function saveSettings($arSettings)
	{
		if(!is_array($arSettings))
			return false;

		$result = true;
		
		foreach ($arSettings as $siteId => $siteSett)
		{
			if(isset($siteSett["OUTLETS_IDS"]) && is_array($siteSett["OUTLETS_IDS"]))
			{
				$newOutletsIds = array();

				foreach ($siteSett["OUTLETS_IDS"] as $outletId)
					if(strlen($outletId) > 0)
						$newOutletsIds[] = $outletId;

				$arSettings[$siteId]["OUTLETS_IDS"] = $newOutletsIds;
			}

			if(isset($arSettings[$siteId]["DELIVERIES"]) && is_array($arSettings[$siteId]["DELIVERIES"]))
			{
				foreach($arSettings[$siteId]["DELIVERIES"] as $id => $type)
				{
					if(strlen($type) <= 0)
					{
						unset($arSettings[$siteId]["DELIVERIES"][$id]);
						unset($arSettings[$siteId]["DLV_PS"][$id]);
						continue;
					}

					foreach(self::getExistPaymentMethods() as $methodIdx => $method)
						if(!isset($arSettings[$siteId]["DLV_PS"][$id][$methodIdx]) || $arSettings[$siteId]["DLV_PS"][$id][$methodIdx] != 'Y')
							$arSettings[$siteId]["DLV_PS"][$id][$methodIdx] = 'N';
				}
			}
		}

		$settings = static::getSettings(false);

		if(!empty($settings))
		{
			if(is_array($settings))
			$result = Bitrix\Sale\TradingPlatformTable::update(
				YMarket\YandexMarket::getInstance()->getId(),
				array("SETTINGS" => $arSettings)
			);
		}
		else
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * @param int $period
	 * @param string $type
	 * @return DateInterval
	 */
	public function getTimeInterval($period, $type)
	{
		$interval = 'P';

		if($type == 'H' || $type == 'MIN')
			$interval .= 'T';

		$interval .= strval(intval($period));

		if($type == 'MIN')
			$type = 'M';

		$interval .= $type;

		return new DateInterval($interval);
	}

	/**
	 * @param DateTime $today
	 * @param DateTime $nextDate
	 * @return bool
	 */
	protected function checkTimeInterval($today, $nextDate)
	{
		$interval = $today->diff($nextDate);
		return (intval($interval->format('%a')) <= 92);
	}

	/**
	 * @param int $from
	 * @param int $to
	 * @param string $type
	 * @return array
	 */
	protected function getDeliveryDates($from, $to, $type)
	{
		$from = intval($from);
		$to = intval($to);
		$arResult = array();

		if($from <= $to)
		{
			$today = new DateTime('now', new DateTimeZone('Europe/Moscow'));
			$dateFrom = new DateTime('now', new DateTimeZone('Europe/Moscow'));
			$dateFrom->add($this->getTimeInterval($from, $type));

			if($this->checkTimeInterval($today, $dateFrom))
			{
				$arResult["fromDate"] = $dateFrom->format(self::DATE_FORMAT);
				$dateTo = $today->add($this->getTimeInterval($to, $type));

				if($this->checkTimeInterval($today, $dateTo))
					$arResult["toDate"] = $dateTo->format(self::DATE_FORMAT);
			}
		}

		return $arResult;
	}

	/**
	 * @return array
	 */
	protected function getPaymentMethods()
	{
		$result = array();

		foreach ($this->mapPaySystems as $psType => $psId)
			if(isset($psId) && intval($psId) > 0)
				$result[] = $psType;

		return $result;
	}

	/**
	 * @param array $arPostData
	 * @return bool
	 */
	protected function checkCartStructure($arPostData)
	{
		return	isset($arPostData["cart"])
			&& isset($arPostData["cart"]["items"])
			&& is_array($arPostData["cart"]["items"])
			&& isset($arPostData["cart"]["currency"])
			&& isset($arPostData["cart"]["delivery"])
			&& is_array($arPostData["cart"]["delivery"]);
	}

	/**
	 * POST /cart
	 * max timeout 5,5s.
	 * @param array $arPostData
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function processCartRequest($arPostData)
	{
		if(!$this->checkCartStructure($arPostData))
			return $this->processError(self::ERROR_STATUS_400, GetMessage("SALE_YMH_ERROR_BAD_STRUCTURE"));

		$result = array(
			"cart" => array(
				"items" => array(),
				"paymentMethods" => array(),
				"deliveryOptions" => array()
			)
		);

		$locationId = 0;

		if(strlen($this->orderProps["LOCATION"]) > 0)
		{
			$locationId = $this->locationMapper->getLocationId($arPostData["cart"]["delivery"]["region"]);

			if(intval($locationId) <= 0)
			{
				$this->log(
					self::LOG_LEVEL_INFO,
					"YMARKET_LOCATION_MAPPING",
					$arPostData["cart"]["delivery"]["region"]["name"],
					GetMessage("SALE_YMH_LOCATION_NOT_FOUND")
				);

				return $result;
			}
		}

		$properties = $this->makeAdditionalOrderProps(
			$arPostData["cart"]["delivery"]["address"],
			array(),
			'',
			'',
			$locationId
		);

		$res = \Bitrix\Sale\TradingPlatform\YMarket\Order::create(array(
			'CURRENCY' => $arPostData['cart']['currency'],
			'SITE_ID' => $this->siteId,
			'PERSON_TYPE_ID' => $this->personTypeId,
			'PROPERTIES' => $properties,
			'CART_ITEMS' => $arPostData['cart']['items']
		));
		
		if(!$res->isSuccess())
		{
			$this->log(
				self::LOG_LEVEL_ERROR,
				"YMARKET_ORDER_CREATE_ERROR",
				'processCartRequest',
				implode('; ',$res->getErrorMessages())
			);

			return $result;
		}

		$data = $res->getData();
		/** @var \Bitrix\Sale\Order $order */
		$order = $data['ORDER'];
		$basket = $order->getBasket();
		$resultItems = array();
		$items = $arPostData['cart']['items'];
		$itemKeyToBasketCode = array_flip($data['ITEMS_MAP']);
		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach($items as $itemKey => $item)
		{
			$price = 0;
			$count = 0;

			if(!empty($itemKeyToBasketCode[$itemKey]))
			{
				$basketCode = $itemKeyToBasketCode[$itemKey];
				$basketItem = $basket->getItemByBasketCode($basketCode);
				
				if(!$basketItem)
					continue;

				$price = round(floatval($basketItem->getPrice()), 2);

				if($price <= 0)
					continue;

				$count = $basketItem->getQuantity();
			}

			$resItem = array(
				'feedId' => $item['feedId'],
				'offerId' => $item['offerId'],				
				'count' => $count,
				'delivery' => $count > 0 ? TRUE : FALSE
			);
			
			if($count > 0)
				$resItem['price'] = $price;

			$resultItems[] = $resItem;
		}

		$shipment = YMarket\Order::createShipment($order);
		YMarket\Order::createPayment($order);
		$deliveryObjs = Delivery\Services\Manager::getRestrictedObjectsList(
			$shipment,
			Delivery\Restrictions\Manager::MODE_CLIENT
		);

		$deliveryOptions = array();

		/** @var Delivery\Services\Base $delivery */
		foreach($deliveryObjs as $delivery)
		{
			if(empty($this->mapDelivery[$delivery->getId()]))
				continue;

			$orderClone = $order->createClone();
			$clonedShipment = null;
			$orderClone->isStartField();

			foreach ($orderClone->getShipmentCollection() as $shp)
			{
				if($shp->isSystem())
					continue;

				$clonedShipment = $shp;
				break;
			}

			/** @var \Bitrix\Sale\Shipment $clonedShipment*/
			$clonedShipment->setDeliveryService($delivery);
			$deliveryCalcRes = $orderClone->getShipmentCollection()->calculateDelivery();

			if(!$deliveryCalcRes->isSuccess())
				continue;

			$orderClone->doFinalAction(true);
			$calcResult = $clonedShipment->calculateDelivery();

			if(!$calcResult->isSuccess())
				continue;

			$dateFrom = $calcResult->getPeriodFrom();
			$dateTo = $calcResult->getPeriodTo();

			if($dateFrom === null)
				$dateFrom = $this->defaultDeliveryPeriodFrom;

			if($dateTo === null)
				$dateTo = $this->defaultDeliveryPeriodTo;

			$arDates = $this->getDeliveryDates($dateFrom, $dateTo, $calcResult->getPeriodType());

			if(!empty($arDates))
			{
				$deliveryType = $this->mapDelivery[$delivery->getId()];

				$arDeliveryTmp = array(
					"id" => $delivery->getId(),
					"type" => $deliveryType,
					"serviceName" => substr($delivery->getNameWithParent(), 0, 50),
					"price" => round(floatval($orderClone->getDeliveryPrice()), 2),
					"dates" => $arDates
				);

				$dlvToPs = array();
				$payMethods = self::getExistPaymentMethods();

				if(!empty($this->deliveryToPaysystem[$delivery->getId()]) && is_array($this->deliveryToPaysystem[$delivery->getId()]))
					foreach($this->deliveryToPaysystem[$delivery->getId()] as $methodIdx => $value)
						if($value == 'Y' && !empty($payMethods[$methodIdx]))
							$dlvToPs[] = $payMethods[$methodIdx];

				if(!empty($dlvToPs))
					$arDeliveryTmp["paymentMethods"] = $dlvToPs;

				if($deliveryType == "PICKUP" && !empty($this->outlets))
					foreach($this->outlets as $outlet)
						$arDeliveryTmp["outlets"][] = array("id" => intval($outlet));

				$deliveryOptions[] = $arDeliveryTmp;
			}
		}

		if(!empty($resultItems) && !empty($deliveryOptions))
		{
			$result['cart']['items'] = $resultItems;
			$result['cart']['deliveryOptions'] = $deliveryOptions;
			$result['cart']['paymentMethods'] = $this->getPaymentMethods();
		}

		return $result;
	}

	/**
	 * @param string $eventName
	 * @param array $params
	 * @return mixed
	 */
	protected function processCustomEvents($eventName, array $params)
	{
		$event = new Event('sale', $eventName, $params);
		$event->send();
		$resultList = $event->getResults();
		$result = $params["RESULT"];

		if (is_array($resultList) && !empty($resultList))
		{
			foreach ($resultList as &$eventResult)
			{
				if ($eventResult->getType() != EventResult::SUCCESS)
					continue;

				$params = $eventResult->getParameters();

				if(isset($params["RESULT"]))
					$result = $params["RESULT"];
			}
		}

		return $result;
	}

	/**
	 * @param array $arPostData
	 * @return bool
	 */
	protected function checkOrderAcceptStructure($arPostData)
	{
		return	isset($arPostData["order"])
			&& isset($arPostData["order"]["id"])
			&& isset($arPostData["order"]["currency"])
			&& isset($arPostData["order"]["fake"])
			&& isset($arPostData["order"]["items"]) && is_array($arPostData["order"]["items"])
			&& isset($arPostData["order"]["delivery"]) && is_array($arPostData["order"]["delivery"]);
	}

	/**
	 * @param array $buyer
	 * @param array $address
	 * @param array $region
	 * @return bool|int|string
	 */
	protected function createUser($buyer, $address, $region)
	{
		$userRegister = array(
			"NAME" => $buyer["firstName"],
			"PERSONAL_MOBILE" => $buyer["phone"]
		);

		if(isset($buyer["lastName"]))
			$userRegister["LAST_NAME"] = $buyer["lastName"];

		if(isset($buyer["middleName"]))
			$userRegister["SECOND_NAME"] = $buyer["middleName"];

		$arPersonal = array();

		if(strlen($buyer["phone"]) > 0)
			$arPersonal = array("PERSONAL_MOBILE" => $buyer["phone"]);

		$arErrors = array();
		$userId = CSaleUser::DoAutoRegisterUser(
			$buyer["email"],
			$userRegister,
			$this->siteId,
			$arErrors,
			$arPersonal);

		$this->log(
			empty($arErrors) ? self::LOG_LEVEL_INFO : self::LOG_LEVEL_ERROR,
			"YMARKET_USER_CREATE",
			$userId ? $userId : print_r($buyer, true),
			empty($arErrors) ? GetMessage("SALE_YMH_USER_PROFILE_CREATED") : print_r($arErrors, true)
		);

		return $userId;
	}

	/**
	 * @param array $address
	 * @param array $buyer
	 * @param int $psId
	 * @param int $deliveryId
	 * @param int $locationId
	 * @return array
	 */
	protected function makeAdditionalOrderProps($address, $buyer, $psId, $deliveryId, $locationId)
	{
		$psId = intval($psId);
		$arResult = array();

		$arPropFilter = array(
			"PERSON_TYPE_ID" => $this->personTypeId,
			"ACTIVE" => "Y"
		);

		if ($psId != 0)
		{
			$arPropFilter["RELATED"]["PAYSYSTEM_ID"] = $psId;
			$arPropFilter["RELATED"]["TYPE"] = "WITH_NOT_RELATED";
		}

		if (strlen($deliveryId) > 0)
		{
			$arPropFilter["RELATED"]["DELIVERY_ID"] = $deliveryId;
			$arPropFilter["RELATED"]["TYPE"] = "WITH_NOT_RELATED";
		}

		$dbOrderProps = CSaleOrderProps::GetList(
			array(),
			$arPropFilter,
			false,
			false,
			array("ID", "CODE")
		);

		while ($arOrderProps = $dbOrderProps->Fetch())
		{
			if(strlen($this->orderProps["FIO"]) > 0 && $arOrderProps["CODE"] == $this->orderProps["FIO"] && !empty($buyer))
			{
				$fio = $buyer["firstName"];

				if(isset($buyer["middleName"]))
					$fio .= ' '.$buyer["middleName"];

				if(isset($buyer["lastName"]))
					$fio .= ' '.$buyer["lastName"];

				$arResult[$arOrderProps["ID"]] = $fio;
			}
			elseif(strlen($this->orderProps["EMAIL"]) > 0 && $arOrderProps["CODE"] == $this->orderProps["EMAIL"] && isset($buyer["email"]))
				$arResult[$arOrderProps["ID"]] = $buyer["email"];
			elseif(strlen($this->orderProps["PHONE"]) > 0 && $arOrderProps["CODE"] == $this->orderProps["PHONE"] && isset($buyer["phone"]))
				$arResult[$arOrderProps["ID"]] = $buyer["phone"];
			elseif(strlen($this->orderProps["ZIP"]) > 0 && $arOrderProps["CODE"] == $this->orderProps["ZIP"] && isset($address["postcode"]))
				$arResult[$arOrderProps["ID"]] = $address["postcode"];
			elseif(strlen($this->orderProps["CITY"]) > 0 && $arOrderProps["CODE"] == $this->orderProps["CITY"])
				$arResult[$arOrderProps["ID"]] = $address["city"];
			elseif(strlen($this->orderProps["LOCATION"]) > 0 && $arOrderProps["CODE"] == $this->orderProps["LOCATION"])
			{
				if($locationId > 0)
				{
					$dbRes = \Bitrix\Sale\Location\LocationTable::getById($locationId);

					if($loc = $dbRes->fetch())
						$arResult[$arOrderProps["ID"]] =  $loc['CODE'];
				}
			}
			elseif(strlen($this->orderProps["ADDRESS"]) > 0 && $arOrderProps["CODE"] == $this->orderProps["ADDRESS"])
				$arResult[$arOrderProps["ID"]] = $this->createAddressString($address);
		}

		return $arResult;
	}

	protected function createAddressString($address)
	{
		$strAddr = "";

		if(isset($address["postcode"]))
			$strAddr .= $address["postcode"].", ";

		$strAddr .= $address["country"].", ".$address["city"].", ";

		if(isset($address["street"]))
			$strAddr .= GetMessage("SALE_YMH_ADDRESS_STREET")." ".$address["street"].", ";

		if(isset($address["subway"]))
			$strAddr .= GetMessage("SALE_YMH_ADDRESS_SUBWAY")." ".$address["subway"].", ";

		$strAddr .= GetMessage("SALE_YMH_ADDRESS_HOUSE")." ".$address["house"];

		if(isset($address["block"]))
			$strAddr .= ", ".GetMessage("SALE_YMH_ADDRESS_BLOCK")." ".$address["block"];

		if(isset($address["entrance"]))
			$strAddr .= ", ".GetMessage("SALE_YMH_ADDRESS_ENTRANCE")." ".$address["entrance"];

		if(isset($address["entryphone"]))
			$strAddr .= ", ".GetMessage("SALE_YMH_ADDRESS_ENTRYPHONE")." ".$address["entryphone"];

		if(isset($address["floor"]))
			$strAddr .= ", ".GetMessage("SALE_YMH_ADDRESS_FLOOR")." ".$address["floor"];

		if(isset($address["apartment"]))
			$strAddr .= ", ".GetMessage("SALE_YMH_ADDRESS_APARTMENT")." ".$address["apartment"];

		if(isset($address["recipient"]))
			$strAddr .= ", ".GetMessage("SALE_YMH_ADDRESS_RECIPIENT")." ".$address["recipient"];

		if(isset($address["phone"]))
			$strAddr .= ", ".GetMessage("SALE_YMH_ADDRESS_PHONE")." ".$address["phone"];

		return $strAddr;
	}

	/**
	 * POST /order/accept timeout 10s
	 * @param array $arPostData
	 * @return array
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function processOrderAcceptRequest($arPostData)
	{
		if(!$this->checkOrderAcceptStructure($arPostData))
			return $this->processError(self::ERROR_STATUS_400, GetMessage("SALE_YMH_ERROR_BAD_STRUCTURE"));

		$deliveryId = $arPostData["order"]["delivery"]["id"];
		$paySystemId = $this->mapPaySystems[$arPostData["order"]["paymentMethod"]];

			$result = array(
			'order' => array(
				'accepted' => false,
				'reason' => 'OUT_OF_DATE'
			)
		);

		if(!Loader::includeModule('iblock'))
			return $result;

		if(!Loader::includeModule('catalog'))
			return $result;

		$dbRes = \Bitrix\Sale\TradingPlatform\OrderTable::getList(array(
			"filter" => array(
				"TRADING_PLATFORM_ID" => YMarket\YandexMarket::getInstance()->getId(),
				"EXTERNAL_ORDER_ID" => $arPostData["order"]["id"]
			)
		));

		$orderId = 0;

		if($orderCorrespondence = $dbRes->fetch())
			$orderId = $orderCorrespondence["ORDER_ID"];

		if($orderId <= 0)
		{
			$locationId = 0;

			if(strlen($this->orderProps["LOCATION"]) > 0)
			{
				$locationId = $this->locationMapper->getLocationId($arPostData["order"]["delivery"]["region"]);

				if(intval($locationId) <= 0)
				{
					$this->log(
						self::LOG_LEVEL_INFO,
						"YMARKET_LOCATION_MAPPING",
						$arPostData["order"]["delivery"]["region"]["name"],
						GetMessage("SALE_YMH_LOCATION_NOT_FOUND")
					);

					return $result;
				}
			}

			$properties = $this->makeAdditionalOrderProps(
				$arPostData["order"]["delivery"]["address"],
				array(),
				$paySystemId,
				$deliveryId,
				$locationId
			);

			$res = \Bitrix\Sale\TradingPlatform\YMarket\Order::create(array(
				'CURRENCY' => $arPostData['order']['currency'],
				'SITE_ID' => $this->siteId,
				'PERSON_TYPE_ID' => $this->personTypeId,
				'PROPERTIES' => $properties,
				'CART_ITEMS' => $arPostData['order']['items'],
				'IS_ACCEPT_OLD_PRICE' => $this->isAcceptOldPrice
			));

			if(!$res->isSuccess())
			{
				$this->log(
					self::LOG_LEVEL_ERROR,
					"YMARKET_ORDER_CREATE_ERROR",
					'processOrderAcceptRequest',
					implode('; ',$res->getErrorMessages())
				);

				return $result;
			}

			$data = $res->getData();
			/** @var \Bitrix\Sale\Order $order */
			$order = $data['ORDER'];

			if(!$this->checkBasketPrice($arPostData['order']['items'], $order))
			{
				if($this->isAcceptOldPrice == \CSaleYMHandler::ACCEPT_OLD_PRICE)
				{
					$this->setBasketOldPrice($arPostData['order']['items'], $order);
					$order->setField(
						"COMMENTS",
						GetMessage('SALE_YMARKET_ORDER_PRICE_CHANGED')
					);
				}
				else
				{
					return $result;
				}
			}

			YMarket\Order::createShipment($order, $deliveryId, $arPostData["order"]["delivery"]["price"]);
			YMarket\Order::createPayment($order, $paySystemId);
			$order->setField("PRICE", $order->getPrice());
			$order->setField("XML_ID", self::XML_ID_PREFIX.$arPostData["order"]["id"]);

			/* PRODUCT_XML_ID CATALOG_XML_ID */
			$xmls = array();
			$productIds = array();

			foreach ($arPostData["order"]["items"] as $item)
				$productIds[] = $item['offerId'];

			$dbRes = \Bitrix\Iblock\ElementTable::getList(array(
				'filter' => array(
					'=ID' => $productIds
				),
				'select' => array(
					'ID', 'XML_ID',
					'IBLOCK_EXTERNAL_ID' => 'IBLOCK.XML_ID',
				)
			));

			$parentsIds = array();

			while($iblockElement = $dbRes->fetch())
			{
				$xmls[$iblockElement['ID']] = array();

				if(strlen($iblockElement["XML_ID"]) > 0)
					$xmls[$iblockElement['ID']]["PRODUCT_XML_ID"] = $iblockElement["XML_ID"];

				if(strlen($iblockElement["IBLOCK_EXTERNAL_ID"]) > 0)
					$xmls[$iblockElement['ID']]["CATALOG_XML_ID"] = $iblockElement["IBLOCK_EXTERNAL_ID"];

				if(strpos($iblockElement["XML_ID"], '#') === false && $parent = \CCatalogSku::GetProductInfo($iblockElement['ID']))
					$parentsIds[$iblockElement['ID']] = $parent['ID'];
			}

			if(!empty($parentsIds))
			{
				$dbRes = \Bitrix\Iblock\ElementTable::getList(array(
					'filter' => array('=ID' => array_unique($parentsIds)),
					'select' => array('ID', 'XML_ID')
				));

				while($parent = $dbRes->fetch())
				{
					if(strlen($parent['XML_ID']) <= 0)
						continue;

					foreach($parentsIds as $childId => $parentId)
						if($parentId == $parent['ID'])
							$xmls[$childId]["PRODUCT_XML_ID"] = $parent['XML_ID'].'#'.$xmls[$childId]["PRODUCT_XML_ID"];
				}
			}
			/* */

			$basket = $order->getBasket();
			/** @var \Bitrix\Sale\BasketItem $basketItem */
			foreach($basket->getBasketItems() as $basketItem)
			{
				$productId = $basketItem->getProductId();

				if(!empty($xmls[$productId]['PRODUCT_XML_ID']))
					$basketItem->setField("PRODUCT_XML_ID", $xmls[$productId]['PRODUCT_XML_ID']);

				if(!empty($xmls[$productId]['CATALOG_XML_ID']))
					$basketItem->setField("CATALOG_XML_ID", $xmls[$productId]['CATALOG_XML_ID']);
			}

			if(!empty($arPostData["order"]["notes"]))
				$order->setField('USER_DESCRIPTION', $arPostData["order"]["notes"]);

			//Let's mark order that we don't have info about buyer yet.
			$r = new \Bitrix\Sale\Result();
			$r->addWarning(new \Bitrix\Main\Error(GetMessage('SALE_YMH_MARK_BUYER_WAITING'), 'YMARKET_BUYER_INFO_WAITING'));
			\Bitrix\Sale\EntityMarker::addMarker($order, $order, $r);
			$order->setField('MARKED', 'Y');

			$res = $order->save();

			if(!$res->isSuccess())
			{
				$this->log(
					self::LOG_LEVEL_ERROR,
					"YMARKET_PLATFORM_ORDER_ADD_ERROR",
					$arPostData["order"]["id"],
					implode('; ', $res->getErrorMessages())
				);

				return $result;
			}

			if (\Bitrix\Sale\Integration\Numerator\NumeratorOrder::isUsedNumeratorForOrder())
			{
				$orderId = $order->getField('ACCOUNT_NUMBER');
			}
			else
			{
				$orderId = $order->getId();
			}

			$res = \Bitrix\Sale\TradingPlatform\OrderTable::add(array(
				"ORDER_ID" => $res->getId(),
				"TRADING_PLATFORM_ID" => YMarket\YandexMarket::getInstance()->getId(),
				"EXTERNAL_ORDER_ID" => $arPostData["order"]["id"]
			));

			if(!$res->isSuccess())
			{
				$this->log(
					self::LOG_LEVEL_ERROR,
					"YMARKET_PLATFORM_ORDER_ADD_ERROR",
					$arPostData["order"]["id"],
					implode('; ', $res->getErrorMessages())
				);

				return $result;
			}
		}

		$result["order"]["accepted"] = TRUE;
		$result["order"]["id"] = '"'.$orderId.'"';
		unset($result["order"]["reason"]);

		$this->log(
			self::LOG_LEVEL_INFO,
			"YMARKET_ORDER_CREATE",
			$arPostData["order"]["id"],
			GetMessage("SALE_YMH_ORDER_CREATED")." ".$orderId
		);

		return $result;
	}

	protected function checkBasketPrice(array $items, \Bitrix\Sale\Order $order)
	{
		$yandexBasketPrice = 0;

		foreach($items as $item)
			if(isset($item['price']))
				$yandexBasketPrice += $item['price']*$item['count'];

		return round($order->getBasket()->getPrice(), 2) == round($yandexBasketPrice, 2);
	}

	protected function setBasketOldPrice(array $items, \Bitrix\Sale\Order $order)
	{
		$basket = $order->getBasket();
		
		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach($basket->getBasketItems() as $basketItem)
		{
			foreach($items as $key => $item)
			{
				if($item['offerId'] != $basketItem->getProductId())
					continue;

				$basketItem->setField("CUSTOM_PRICE", 'Y');
				$basketItem->setField("PRICE", $item['price']);
				unset($items[$key]);
			}
		}
	}

	/**
	 * @param array $arPostData
	 * @return bool
	 */
	protected function checkOrderStatusRequest($arPostData)
	{
		return
			(isset($arPostData["order"])
			&& isset($arPostData["order"]["id"])
			&& isset($arPostData["order"]["currency"])
			&& isset($arPostData["order"]["creationDate"])
			&& isset($arPostData["order"]["itemsTotal"])
			&& isset($arPostData["order"]["total"])
			&& isset($arPostData["order"]["status"])
			&& isset($arPostData["order"]["fake"])
			&& isset($arPostData["order"]["buyer"])
			&& isset($arPostData["order"]["items"]) && is_array($arPostData["order"]["items"])
			&& isset($arPostData["order"]["delivery"]) && is_array($arPostData["order"]["delivery"])) || true;
	}

	/**
	 * POST /order/status timeout 10s
	 *
	 * @param array $arPostData
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function processOrderStatusRequest($arPostData)
	{
		$arResult = array();
		if($this->checkOrderStatusRequest($arPostData))
		{
			$dbOrder = \Bitrix\Sale\Internals\OrderTable::getList(array(
				'filter' => array("XML_ID" => self::XML_ID_PREFIX.$arPostData["order"]["id"]),
				'select' => array('ID', 'LID', 'XML_ID')
			));

			if($arOrder = $dbOrder->fetch())
			{
				$order = \Bitrix\Sale\Order::load($arOrder['ID']);
				$reason = "";

				switch ($arPostData["order"]["status"])
				{
					case 'PROCESSING':
						$locationId = $this->locationMapper->getLocationId($arPostData["order"]["delivery"]["region"]);

						if($locationId === false)
						{
							$this->log(
								self::LOG_LEVEL_INFO,
								"YMARKET_LOCATION_MAPPING",
								$arPostData["order"]["delivery"]["region"]["name"],
								GetMessage("SALE_YMH_LOCATION_NOT_FOUND")
							);
						}

						if(isset($arPostData["order"]["paymentMethod"]) && $arPostData["order"]["paymentMethod"] == "YANDEX")
						{
							$paymentCollection = $order->getPaymentCollection();

							/** @var \Bitrix\Sale\Payment $payment */
							if($paymentCollection->count() > 0)
							{
								foreach ($paymentCollection as $payment)
								{
									$res = $payment->setPaid("Y");

									if(!$res->isSuccess())
									{
										$this->log(
											self::LOG_LEVEL_INFO,
											"YMARKET_INCOMING_ORDER_STATUS",
											'Set order paid',
											implode('; ',$res->getErrorMessages())
										);
									}
								}
							}
						}

						$arOrderPropsValues = $this->makeAdditionalOrderProps(
							$arPostData["order"]["delivery"]["address"],
							$arPostData["order"]["buyer"],
							isset($this->mapPaySystems[$arPostData["order"]["paymentMethod"]]) ? $this->mapPaySystems[$arPostData["order"]["paymentMethod"]] : "",
							$arPostData["order"]["delivery"]["id"],
							$locationId
						);

						if(!empty($arOrderPropsValues))
						{
							$propCollection = $order->getPropertyCollection();
							$res = $propCollection->setValuesFromPost(array('PROPERTIES' => $arOrderPropsValues), array());

							if(!$res->isSuccess())
							{
								$this->log(
									self::LOG_LEVEL_INFO,
									"YMARKET_INCOMING_ORDER_STATUS",
									'Set order properties',
									implode('; ',$res->getErrorMessages())
								);
							}
						}

						if($order->getUserId() == \CSaleUser::GetAnonymousUserID())
						{
							$userId = $this->createUser($arPostData["order"]["buyer"], null, null);

							if(intval($userId) > 0)
							{
								$order->setFieldNoDemand("USER_ID", $userId);

								EntityMarker::deleteByFilter(array(
									'=ORDER_ID' => $order->getId(),
									'=ENTITY_TYPE' => EntityMarker::ENTITY_TYPE_ORDER,
									'=ENTITY_ID' => $order->getId(),
									'=CODE' => 'YMARKET_BUYER_INFO_WAITING'
								));

								$order->setField('MARKED', 'N');
							}
						}

						if(!empty($arPostData["order"]["notes"]) && $order->getField('USER_DESCRIPTION') != $arPostData["order"]["notes"])
							$order->setField('USER_DESCRIPTION', $order->getField('USER_DESCRIPTION')."\n".$arPostData["order"]["notes"]);

						$res = $order->save();

						if(!$res->isSuccess())
						{
							$this->log(
								self::LOG_LEVEL_INFO,
								"YMARKET_INCOMING_ORDER_STATUS",
								'Save order',
								implode('; ',$res->getErrorMessages())
							);
						}

						$this->sendEmailNewOrder($arOrder["ID"], $arPostData["order"]["buyer"]);

						if(!empty($arErrors))
						{
							$this->log(
								self::LOG_LEVEL_ERROR,
								"YMARKET_INCOMING_ORDER_STATUS",
								$arPostData["order"]["id"],
								print_r($arErrors, true)
							);
						}
						else
						{
							$this->log(
								self::LOG_LEVEL_INFO,
								"YMARKET_INCOMING_ORDER_STATUS",
								$arPostData["order"]["id"],
								GetMessage("SALE_YMH_INCOMING_ORDER_STATUS_PROCESSING").": ".$arOrder["ID"]
							);
						}

						break;

					case 'UNPAID':
					case 'DELIVERY':
					case 'PICKUP':
					case 'DELIVERED ':
					case 'RESERVED ':
						break;

					case 'CANCELLED':
						if(isset($arPostData["order"]["substatus"]))
							$reason = $arPostData["order"]["substatus"];
						break;

					default:
						$arResult = $this->processError(self::ERROR_STATUS_400, GetMessage("SALE_YMH_ERROR_UNKNOWN_STATUS"));
						break;
				}

				$this->mapYandexStatusToOrder($arOrder, $arPostData["order"]["status"], $reason);
			}
		}
		else
		{
			$arResult = $this->processError(self::ERROR_STATUS_400, GetMessage("SALE_YMH_ERROR_BAD_STRUCTURE"));
		}

		return $arResult;
	}

	/**
	 * @param string $postData
	 * @return array
	 */
	protected function extractPostData($postData)
	{
		global $APPLICATION;
		$arResult = array();

		if($this->communicationFormat == self::JSON)
		{
			$arResult = json_decode($postData, true);
		}

		if(strtolower(SITE_CHARSET) != 'utf-8')
			$arResult = $APPLICATION->ConvertCharsetArray($arResult, 'utf-8', SITE_CHARSET);

		return $arResult;
	}

	/**
	 * @param array $arData
	 * @return string
	 */
	protected function prepareResult($arData)
	{
		global $APPLICATION;
		$result = array();

		if(strtolower(SITE_CHARSET) != 'utf-8')
			$arData = $APPLICATION->ConvertCharsetArray($arData, SITE_CHARSET, 'utf-8');

		if($this->communicationFormat == self::JSON)
		{
			header('Content-Type: application/json');
			$result = json_encode($arData);
		}

		return $result;
	}

	/**
	 * Let's check authorization,
	 * comparing incoming token with token stored in settings.
	 * @return bool
	 */
	public function checkAuth()
	{
		$incomingToken = "";

		if($this->authType == "HEADER")
		{
			if(isset($_SERVER["REMOTE_USER"]) && strlen($_SERVER["REMOTE_USER"]) > 0)
				$incomingToken = $_SERVER["REMOTE_USER"];
			elseif(isset($_SERVER["REDIRECT_REMOTE_USER"]) && strlen($_SERVER["REDIRECT_REMOTE_USER"]) > 0)
				$incomingToken = $_SERVER["REDIRECT_REMOTE_USER"];
			elseif(isset($_SERVER["HTTP_AUTHORIZATION"]) && strlen($_SERVER["HTTP_AUTHORIZATION"]) > 0)
				$incomingToken = $_SERVER["HTTP_AUTHORIZATION"];
		}
		elseif($this->authType == "URL")
		{
			if(isset($_REQUEST["auth-token"]) && strlen($_REQUEST["auth-token"]) > 0)
				$incomingToken = $_REQUEST["auth-token"];
		}

		if($incomingToken == "" && intval($_SERVER["argc"]) > 0)
		{
			foreach ($_SERVER["argv"] as $arg)
			{
				$e = explode("=", $arg);

				if(count($e) == 2 && $e[0] == "auth-token")
					$incomingToken = $e[1];
			}
		}

		return strlen($incomingToken) > 0 && $incomingToken == $this->yandexToken;
	}

	/**
	 * @param string $reqObject
	 * @param string $method
	 * @param string $postData
	 * @return string
	 */
	public function processRequest($reqObject, $method, $postData)
	{
		$arResult = array();
		$arPostData = $this->extractPostData($postData);

		$this->log(
			self::LOG_LEVEL_DEBUG,
			"YMARKET_INCOMING_REQUEST",
			$reqObject.":".$method,
			print_r($arPostData, true)
		);

		if(!$this->isActive())
		{
			$arResult = $this->processError(self::ERROR_STATUS_503, GetMessage("SALE_YMH_ERROR_OFF"));
		}
		elseif(!$this->checkAuth())
		{
			$arResult = $this->processError(self::ERROR_STATUS_403, GetMessage("SALE_YMH_ERROR_FORBIDDEN"));
		}
		else
		{
			self::$isYandexRequest = true;
			DiscountCouponsManager::init(DiscountCouponsManager::MODE_EXTERNAL);

			switch ($reqObject)
			{
				case 'cart':
					$arResult = $this->processCartRequest($arPostData);
					break;

				case 'order':

					if($method == "accept")
						$arResult = $this->processOrderAcceptRequest($arPostData);
					elseif($method == "status")
						$arResult = $this->processOrderStatusRequest($arPostData);
					break;

				default:
					$arResult = $this->processError(self::ERROR_STATUS_400, GetMessage("SALE_YMH_ERROR_UNKNOWN_REQ_OBJ"));
					break;
			}
		}

		$this->log(
			self::LOG_LEVEL_DEBUG,
			"YMARKET_INCOMING_REQUEST_RESULT",
			$reqObject.":".$method,
			print_r($arResult, true)
		);

		$arResult = $this->processCustomEvents(
			'OnSaleYandexMarketRequest_'.$reqObject.$method,
			array(
				"POST_DATA" => $arPostData,
				"RESULT" => $arResult
		));

		$arPreparedResult = $this->prepareResult($arResult);
		return  $arPreparedResult;
	}


	/**
	 * @param string $status
	 * @param string $message
	 * @return array
	 */
	protected function processError($status = "", $message = "")
	{
		if($status != "")
			CHTTP::SetStatus($status);

		if($message && $this->logLevel >= self::LOG_LEVEL_ERROR)
			$this->log(
				self::LOG_LEVEL_ERROR,
				"YMARKET_REQUEST_ERROR",
				"",
				$message);

		return array("error" => $message);
	}

	/**
	 * @param int $orderId
	 * @param string $status
	 * @param string $substatus
	 * @return bool
	 */
	public function sendStatus($orderId, $status, $substatus = false)
	{
		global $APPLICATION;

		if(
			strlen($this->yandexApiUrl) <= 0
			|| strlen($this->campaignId) <= 0
			|| intval($orderId) <= 0
			|| strlen($status) <=0
			|| strlen($this->oAuthToken) <=0
			|| strlen($this->oAuthClientId) <=0
			|| strlen($this->oAuthLogin) <=0
		)
			return false;

		$format = $this->communicationFormat == self::JSON ? 'json' : 'xml';
		$url = $this->yandexApiUrl."campaigns/".$this->campaignId."/orders/".$orderId."/status.".$format;

		$http = new \Bitrix\Main\Web\HttpClient(array(
			"version" => "1.1",
			"socketTimeout" => 30,
			"streamTimeout" => 30,
			"redirect" => true,
			"redirectMax" => 5,
		));

		$arQuery = array(
			"order" => array(
				"status" => $status,
			)
		);

		if($substatus)
			$arQuery["order"]["substatus"] = $substatus;

		if(strtolower(SITE_CHARSET) != 'utf-8')
			$arQuery = $APPLICATION->ConvertCharsetArray($arQuery, SITE_CHARSET, 'utf-8');

		$postData = '';
		if($this->communicationFormat == self::JSON)
			$postData = json_encode($arQuery);

		$http->setHeader("Content-Type", "application/".$format);
		$http->setHeader("Authorization", 'OAuth oauth_token="'.$this->oAuthToken.
					'", oauth_client_id="'.$this->oAuthClientId.
					'", oauth_login="'.$this->oAuthLogin.'"', false);


		$result = $http->query("PUT", $url, $postData);
		$errors = $http->getError();

		if (!$result && !empty($errors))
		{
			$bResult = false;
			$message = "HTTP ERROR: ";

			foreach($errors as $errorCode => $errMes)
				$message .= $errorCode.": ".$errMes;
		}
		else
		{
			$headerStatus = $http->getStatus();

			if ($headerStatus == 200)
			{
				$message = GetMessage("SALE_YMH_STATUS").": ".$status;

				if($substatus)
					$message .= ' ['.$substatus.']';

				$bResult = true;
			}
			else
			{
				$res = 	$http->getResult();
				$message = "HTTP error code: ".$headerStatus."(".$res.")";

				if($headerStatus == 403)
					$this->notifyAdmin("SEND_STATUS_ERROR_403");

				if($headerStatus == 500)
				{
					$intervalSeconds = 3600;
					$timeToStart = ConvertTimeStamp(strtotime(date('Y-m-d H:i:s', time() + $intervalSeconds)), 'FULL');
					\CAgent::AddAgent(
						'\CSaleYMHandler::sendStatusAgent("'.$orderId.'","'.$status.'", "'.$substatus.'", "'.$this->siteId.'");',
						'sale',
						"N",
						$intervalSeconds,
						$timeToStart,
						"Y",
						$timeToStart
					);
				}

				$bResult = false;
			}
		}

		$this->log(
			$bResult ? self::LOG_LEVEL_INFO : self::LOG_LEVEL_ERROR,
			"YMARKET_STATUS_CHANGE",
			$orderId,
			$message
		);

		if(!$bResult)
		{
			if($order = $this->loadOrderByYandexOrderId($orderId))
			{				
				$r = new \Bitrix\Sale\Result();
				$r->addWarning(new \Bitrix\Main\Error($message, 'YMARKET_STATUS_CHANGE_ERROR'));
				\Bitrix\Sale\EntityMarker::addMarker($order, $order, $r);
				$order->setField('MARKED', 'Y');
				$order->save();
			}
		}

		return $bResult;
	}

	/**
	 * @param string $yandexOrderId
	 * @param string $yandexStatus
	 * @param string $substatus
	 * @param string $siteId
	 * @return string
	 */
	public static function sendStatusAgent($yandexOrderId, $yandexStatus, $substatus, $siteId)
	{
		$YMHandler = new CSaleYMHandler(
			array("SITE_ID"=> $siteId)
		);

		$YMHandler->sendStatus($yandexOrderId, $yandexStatus, $substatus);

		return '';
	}

	/**
	 * @param int $orderId
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getOrderInfo($orderId)
	{
		if(intval($orderId) <= 0)
			return array();

		$res = \Bitrix\Sale\Internals\OrderTable::getList(array(
			'filter' => array(
				'=ID' => $orderId,
				'=SOURCE.TRADING_PLATFORM_ID' => YMarket\YandexMarket::getInstance()->getId()
			),
			'select' => array("LID", "XML_ID", "YANDEX_ID" => "SOURCE.EXTERNAL_ORDER_ID"),
			'runtime' => array(
				'SOURCE' => array(
					'data_type' => '\Bitrix\Sale\TradingPlatform\OrderTable',
					'reference' => array(
						'ref.ORDER_ID' => 'this.ID',
					),
				'join_type' => 'left'
				)
			)
		));

		if($arOrder = $res->fetch())
				return $arOrder;

		return array();
	}

	/**
	 * @param int $orderId
	 * @return bool
	 */
	public static function isOrderFromYandex($orderId)
	{
		$arOrder = self::getOrderInfo($orderId);
		return !empty($arOrder["YANDEX_ID"]);
	}

	/**
	 * @param \Bitrix\Main\Event $params
	 */
	public static function onSaleStatusOrderChange(Bitrix\Main\Event $params)
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = $params->getParameter("ENTITY");
		$value = $params->getParameter("VALUE");
		$oldValue = $params->getParameter("OLD_VALUE");

		if (!static::isOrderEntity($order))
		{
			return;
		}

		if($value == $oldValue)
			return;

		if($order->getId() <= 0)
			return;

		self::onSaleStatusOrder($order->getId(), $value);
	}

	/**
	 * @param \Bitrix\Main\Event $params
	 */
	public static function onSaleOrderCanceled(Bitrix\Main\Event $params)
	{
		global $USER;

		/** @var \Bitrix\Sale\Order $order */
		$order = $params->getParameter("ENTITY");

		if (!static::isOrderEntity($order))
		{
			return;
		}

		if($order->getId() <= 0)
			return;

		if(!$order->isCanceled())
			return;

		$arSubstatuses = self::getOrderSubstatuses();
		$description = $order->getField('REASON_CANCELED');

		if(strlen($description) <= 0 || !$USER->IsAdmin() || empty($arSubstatuses[$description]))
			$description = "USER_CHANGED_MIND";

		self::onSaleStatusOrder($order->getId(), "CANCELED", $description);
	}

	/**
	 * @param \Bitrix\Main\Event $params
	 */
	public static function onSaleShipmentDelivery(Bitrix\Main\Event $params)
	{
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $params->getParameter("ENTITY");

		if (!static::isOrderEntity($shipment))
		{
			return;
		}

		if($shipment->getId() <= 0)
			return;

		/** @var \Bitrix\Sale\ShipmentCollection $collection */
		$collection = $shipment->getCollection();

		if(!$collection->isAllowDelivery())
			return;

		self::onSaleStatusOrder($shipment->getField('ORDER_ID'), "ALLOW_DELIVERY");
	}

	/**
	 * @param \Bitrix\Main\Event $params
	 */
	public static function onSaleOrderPaid(Bitrix\Main\Event $params)
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = $params->getParameter("ENTITY");

		if (!static::isOrderEntity($order))
		{
			return;
		}

		if($order->getId() <= 0)
			return;

		if(!$order->isPaid())
			return;

		self::onSaleStatusOrder($order->getId(), "PAYED");
	}

	/**
	 * @param \Bitrix\Main\Event $params
	 */
	public static function onShipmentDeducted(Bitrix\Main\Event $params)
	{
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $params->getParameter("ENTITY");

		if (!static::isOrderEntity($shipment))
		{
			return;
		}

		if($shipment->getId() <= 0)
			return;

		/** @var \Bitrix\Sale\ShipmentCollection $collection */
		$collection = $shipment->getCollection();

		if(!$collection->isShipped())
			return;

		self::onSaleStatusOrder($shipment->getField('ORDER_ID'), "DEDUCTED");
	}

	/**
	 * Executes when order's status was changed in shop
	 * event OnSaleCancelOrder
	 * @param int $orderId Identifier
	 * @param string $status New status
	 * @param string $substatus Substatus.
	 * @return bool
	 */
	public function onSaleStatusOrder($orderId, $status, $substatus = false)
	{
		if(self::$isYandexRequest)
			return false;

		if(intval($orderId) <= 0)
			return false;

		$result = false;
		$arOrder = self::getOrderInfo($orderId);

		if(!empty($arOrder) && isset($arOrder["YANDEX_ID"]) && !self::$isYandexRequest)
		{
			$YMHandler = new CSaleYMHandler(
				array("SITE_ID"=> $arOrder["LID"])
			);

			$settings = $YMHandler->getSettingsBySiteId($arOrder["LID"]);

			if(!isset($settings["STATUS_OUT"][$status]) || strlen($settings["STATUS_OUT"][$status]) <= 0)
				return false;

			$yandexStatus = $settings["STATUS_OUT"][$status];
			$YMHandler->sendStatus($arOrder["YANDEX_ID"], $yandexStatus, $substatus);
			$result = true;
		}

		return $result;
	}

	public static function getOrderSubstatuses()
	{
		return array(
			"USER_UNREACHABLE" => GetMessage("SALE_YMH_SUBSTATUS_USER_UNREACHABLE"),
			"USER_CHANGED_MIND" => GetMessage("SALE_YMH_SUBSTATUS_USER_CHANGED_MIND"),
			"USER_REFUSED_DELIVERY"=> GetMessage("SALE_YMH_SUBSTATUS_USER_REFUSED_DELIVERY"),
			"USER_REFUSED_PRODUCT" => GetMessage("SALE_YMH_SUBSTATUS_USER_REFUSED_PRODUCT"),
			"SHOP_FAILED" => GetMessage("SALE_YMH_SUBSTATUS_SHOP_FAILED"),
			"REPLACING_ORDER" => GetMessage("SALE_YMH_SUBSTATUS_REPLACING_ORDER"),
			"PROCESSING_EXPIRED" => GetMessage("SALE_YMH_SUBSTATUS_PROCESSING_EXPIRED"),
			"RESERVATION_EXPIRED" => GetMessage("SALE_YMH_SUBSTATUS_RESERVATION_EXPIRED"),
			"USER_NOT_PAID" => GetMessage("SALE_YMH_SUBSTATUS_USER_NOT_PAID"),
			"USER_REFUSED_QUALITY" => GetMessage("SALE_YMH_SUBSTATUS_USER_REFUSED_QUALITY"),
		);
	}

	public static function getCancelReasonsAsSelect($name, $val=false, $id=false)
	{
		$arStatuses = self::getOrderSubstatuses();
		$result = '<select width="100%" name="'.$name.'"';

		if($id !== false)
			$result .= ' id="'.$id.'"';

		$result .='>';
		foreach ($arStatuses as $statusId => $statusName)
		{
			$result .='<option value="'.$statusId.'"';

			if($val == $statusId)
				$result .= ' selected';

			$result .= '>'.$statusName.'</option>';
		}

		$result .='</select>';

		return $result;
	}

	public static function getCancelReasonsAsRadio($name, $id=false, $val=false)
	{
		$result = "";
		$arStatuses = self::getOrderSubstatuses();
		$start = 0;

		if($id === false)
			$id = "cancelreasonid_".rand();

		foreach ($arStatuses as $statusId => $statusName)
		{
			$tmpId = $id.'_'.($start++);
			$result .=
				'<label for="'.$tmpId.'">'.
					'<input id="'.$tmpId.'" type="radio" name="'.$name.'_rb" value="'.$statusId.'">'.
					'<span id="'.$tmpId.'_lbl">'.$statusName.'</span>'.
				'</label><br>'.
				'<script type="text/javascript">'.
					'BX("'.$tmpId.'").onchange=function(){if(this.checked == true) { BX("'.$id.'").innerHTML = BX("'.$tmpId.'_lbl").innerHTML; }};'.
				'</script>';
		}
		return $result;
	}

	public function OnEventLogGetAuditTypes()
	{
		return array(
			"YMARKET_STATUS_CHANGE" => "[YMARKET_STATUS_CHANGE] ".GetMessage("SALE_YMH_LOG_TYPE_STATUS_CHANGE"),
			"YMARKET_INCOMING_ORDER_STATUS" => "[YMARKET_INCOMING_ORDER_STATUS] ".GetMessage("SALE_YMH_LOG_TYPE_INCOMING_ORDER_STATUS"),
			"YMARKET_USER_CREATE" => "[YMARKET_USER_CREATE] ".GetMessage("SALE_YMH_LOG_TYPE_USER_CREATE"),
			"YMARKET_ORDER_CREATE" => "[YMARKET_ORDER_CREATE] ".GetMessage("SALE_YMH_LOG_TYPE_ORDER_CREATE"),
			"YMARKET_REQUEST_ERROR" => "[YMARKET_REQUEST_ERROR] ".GetMessage("SALE_YMH_LOG_TYPE_REQUEST_ERROR"),
			"YMARKET_INCOMING_REQUEST" => "[YMARKET_INCOMING_REQUEST] ".GetMessage("SALE_YMH_LOG_TYPE_INCOMING_REQUEST"),
			"YMARKET_INCOMING_REQUEST_RESULT" => "[YMARKET_INCOMING_REQUEST_RESULT] ".GetMessage("SALE_YMH_LOG_TYPE_INCOMING_REQUEST_RESULT"),
			"YMARKET_LOCATION_MAPPING" => "[YMARKET_LOCATION_MAPPING] ".GetMessage("SALE_YMH_LOG_TYPE_YMARKET_LOCATION_MAPPING"),
			"YMARKET_ORDER_STATUS_CHANGE" => "[YMARKET_ORDER_STATUS_CHANGE] ".GetMessage("SALE_YMH_LOG_TYPE_ORDER_STATUS_CHANGE"),
			"YMARKET_ORDER_CREATE_ERROR" => "[YMARKET_ORDER_CREATE_ERROR] ".GetMessage("SALE_YMH_LOG_TYPE_ORDER_CREATE_ERROR"),

		);
	}

	/**
	 * @param int $level
	 * @param string $type
	 * @param string $itemId
	 * @param string $description
	 * @return bool
	 */
	protected function log($level, $type, $itemId, $description)
	{
		if($this->logLevel < $level)
			return false;

		CEventLog::Add(array(
			"SEVERITY" => $level >= CSaleYMHandler::LOG_LEVEL_ERROR ? "WARNING" : "NOTICE",
			"AUDIT_TYPE_ID" => $type,
			"MODULE_ID" => "sale",
			"ITEM_ID" => $itemId,
			"DESCRIPTION" => $description,
		));

		return true;
	}

	/**
	 * @param array $order
	 * @param string $yandexStatus
	 * @param string $cancelReason
	 * @return bool|int
	 */
	protected function mapYandexStatusToOrder($order, $yandexStatus, $cancelReason="")
	{
		global $APPLICATION;

		if(!is_array($order) || !isset($order["ID"]) || strlen($yandexStatus) <= 0)
			return false;

		$settings = $this->getSettingsBySiteId($order["LID"]);

		if(!isset($settings["STATUS_IN"][$yandexStatus]) || strlen($settings["STATUS_IN"][$yandexStatus]) <= 0)
			return false;

		$result = false;
		$bitrixStatus = $settings["STATUS_IN"][$yandexStatus];

		switch($bitrixStatus)
		{
			/* flags */
			case "CANCELED":

				$errorMessageTmp = "";
				$result = \CSaleOrder::CancelOrder($order["ID"], "Y", $cancelReason);

				if (!$result)
				{
					if ($ex = $APPLICATION->GetException())
					{
						if ($ex->GetID() != "ALREADY_FLAG")
							$errorMessageTmp .= $ex->GetString();
					}
					else
						$errorMessageTmp .= GetMessage("ERROR_CANCEL_ORDER").". ";
				}

				if($errorMessageTmp != "")
				{
					$this->log(
						self::LOG_LEVEL_ERROR,
						"YMARKET_INCOMING_ORDER_STATUS",
						$order["XML_ID"],
						$errorMessageTmp
					);
				}
				else
				{
					$this->log(
						self::LOG_LEVEL_INFO,
						"YMARKET_INCOMING_ORDER_STATUS",
						$order["XML_ID"],
						GetMessage("SALE_YMH_INCOMING_ORDER_STATUS_CANCELED").": ".$order["ID"]
					);
				}

				break;

			case "ALLOW_DELIVERY":
				$result = CSaleOrder::DeliverOrder($order["ID"], "Y");
				break;

			case "PAYED":
				$result = CSaleOrder::PayOrder($order["ID"], "Y");
				break;

			case "DEDUCTED":
				$result = CSaleOrder::DeductOrder($order["ID"], "Y");
				break;

			/* statuses */
			default:
				if(CSaleStatus::GetByID($bitrixStatus))
				{
					$result = CSaleOrder::StatusOrder($order["ID"], $bitrixStatus);
				}
				break;
		}

		$this->log(
			$result ? self::LOG_LEVEL_INFO : self::LOG_LEVEL_ERROR,
			"YMARKET_ORDER_STATUS_CHANGE",
			$order["ID"],
			($result ? GetMessage("SALE_YMH_LOG_TYPE_ORDER_STATUS_CHANGE_OK") : GetMessage("SALE_YMH_LOG_TYPE_ORDER_STATUS_CHANGE_ERROR"))." (".$bitrixStatus.")"
		);

		return  $result;
	}

	/**
	 * Starts exchange information between Yandex-market and shop
	 * @return bool
	 */
	public static function eventsStart()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('sale', 'OnSaleStatusOrderChange', 'sale', 'CSaleYMHandler', 'onSaleStatusOrderChange');
		$eventManager->registerEventHandler('sale', 'OnSaleOrderCanceled', 'sale', 'CSaleYMHandler', 'onSaleOrderCanceled');
		$eventManager->registerEventHandler('sale', 'OnSaleShipmentDelivery', 'sale', 'CSaleYMHandler', 'onSaleShipmentDelivery');
		$eventManager->registerEventHandler('sale', 'OnSaleOrderPaid', 'sale', 'CSaleYMHandler', 'onSaleOrderPaid');
		$eventManager->registerEventHandler('sale', 'OnShipmentDeducted', 'sale', 'CSaleYMHandler', 'onShipmentDeducted');

		return true;
	}

	/**
	 * Stops exchange information between Yandex-market and shop
	 * @return bool
	 */
	public static function eventsStop()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('sale', 'OnSaleStatusOrderChange', 'sale', 'CSaleYMHandler', 'onSaleStatusOrderChange');
		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderCanceled', 'sale', 'CSaleYMHandler', 'onSaleOrderCanceled');
		$eventManager->unRegisterEventHandler('sale', 'OnSaleShipmentDelivery', 'sale', 'CSaleYMHandler', 'onSaleShipmentDelivery');
		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderPaid', 'sale', 'CSaleYMHandler', 'onSaleOrderPaid');
		$eventManager->unRegisterEventHandler('sale', 'OnShipmentDeducted', 'sale', 'CSaleYMHandler', 'onShipmentDeducted');

		return true;
	}

	/**
	 * Installs service
	 * @return bool
	 */
	public static function install()
	{
		$settings = static::getSettings();

		if(empty($settings))
		{
			$res =  Bitrix\Sale\TradingPlatformTable::add(array(
				"CODE" => static::TRADING_PLATFORM_CODE,
				"ACTIVE" => "N",
				"NAME" => GetMessage("SALE_YMH_NAME"),
				"DESCRIPTION" => GetMessage("SALE_YMH_DESCRIPTION"),
				"SETTINGS" => "",
			));

			$b = "sort";
			$o = "asc";
			$dbSites = \CSite::GetList($b, $o, array("ACTIVE" => "Y"));

			while ($site = $dbSites->Fetch())
			{
				\CUrlRewriter::Add(
					array(
						"CONDITION" => "#^/bitrix/services/ymarket/#",
						"RULE" => "",
						"ID" => "",
						"PATH" => "/bitrix/services/ymarket/index.php",
						"SITE_ID" => $site["ID"]
					)
				);
			}
		}
		else
		{
			$res = true;
		}

		return $res ? true : false;
	}

	/**
	 * Uninstalls service
	 * @param bool $deleteRecord Delete, or not table record about this service
	 */
	public static function unInstall($deleteRecord = true)
	{
		static::eventsStop();

		$settings = static::getSettings();

		if(!empty($settings))
		{
			if($deleteRecord)
				Bitrix\Sale\TradingPlatformTable::delete(static::TRADING_PLATFORM_CODE);
			else
				static::setActivity(false);
		}

		\CUrlRewriter::Delete(
			array(
				"CONDITION" => "#^/bitrix/services/ymarket/#",
				"PATH" => "/bitrix/services/ymarket/index.php"
			)
		);
	}

	/**
	 * @param int $newOrderId
	 * @param $buyer
	 */
	protected function sendEmailNewOrder($newOrderId, $buyer)
	{
		global $DB;

		$strOrderList = "";
		$baseLangCurrency = Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency($this->siteId);
		$orderNew = CSaleOrder::GetByID($newOrderId);
		$orderNew["BASKET_ITEMS"] = array();

		$userEmail = $buyer["email"];
		$fio = $buyer["last-name"].(isset($buyer["first-name"]) ? $buyer["first-name"] : "");

		$dbBasketTmp = CSaleBasket::GetList(
			array("SET_PARENT_ID" => "DESC", "TYPE" => "DESC", "NAME" => "ASC"),
			array("ORDER_ID" => $newOrderId),
			false,
			false,
			array(
				"ID", "PRICE", "QUANTITY", "NAME"
			)
		);

		while ($arBasketTmp = $dbBasketTmp->GetNext())
		{
			$orderNew["BASKET_ITEMS"][] = $arBasketTmp;
		}

		$orderNew["BASKET_ITEMS"] = getMeasures($orderNew["BASKET_ITEMS"]);

		foreach ($orderNew["BASKET_ITEMS"] as $val)
		{
			if (CSaleBasketHelper::isSetItem($val))
				continue;

			$measure = (isset($val["MEASURE_TEXT"])) ? $val["MEASURE_TEXT"] : GetMessage("SALE_YMH_SHT");
			$strOrderList .= $val["NAME"]." - ".$val["QUANTITY"]." ".$measure." x ".SaleFormatCurrency($val["PRICE"], $baseLangCurrency);
			$strOrderList .= "</br>";
		}

		//send mail
		$arFields = array(
			"ORDER_ID" => $orderNew["ACCOUNT_NUMBER"],
			"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", $this->siteId))),
			"ORDER_USER" => $fio,
			"PRICE" => SaleFormatCurrency($orderNew["PRICE"], $baseLangCurrency),
			"BCC" => COption::GetOptionString("sale", "order_email", "order@".$_SERVER['SERVER_NAME']),
			"EMAIL" => array("PAYER_NAME" => $fio, "USER_EMAIL" => $userEmail),
			"ORDER_LIST" => $strOrderList,
			"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$_SERVER['SERVER_NAME']),
			"DELIVERY_PRICE" => $orderNew["DELIVERY_PRICE"],
		);

		$eventName = "SALE_NEW_ORDER";

		$bSend = true;
		foreach(GetModuleEvents("sale", "OnOrderNewSendEmail", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($newOrderId, &$eventName, &$arFields))===false)
				$bSend = false;

		if($bSend)
		{
			$event = new CEvent;
			$event->Send($eventName, $this->siteId, $arFields, "N");
		}

		CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER" => $orderNew));
	}

	/**
	 * @param string $code
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function notifyAdmin($code)
	{
		$tag = "YANDEX_MARKET_".$code;
		$problemsCount = intval(Option::get("sale", $tag, 0, $this->siteId));

		if($problemsCount < 3)
		{
			Option::set("sale", $tag, $problemsCount+1, $this->siteId);
			return false;
		}

		$dbRes = CAdminNotify::GetList(array(), array("TAG" => $tag));

		if($res = $dbRes->Fetch())
			return false;

		CAdminNotify::Add(array(
				"MESSAGE" => GetMessage("SALE_YMH_ADMIN_NOTIFY_".$code, array("##LANGUAGE_ID##" => LANGUAGE_ID)),
				"TAG" => "YANDEX_MARKET_".$code,
				"MODULE_ID" => "SALE",
				"ENABLE_CLOSE" => "Y"
			)
		);

		Option::set("sale", $tag, 0, $this->siteId);

		return true;
	}

	/**
	 * @return array
	 */
	public static function getExistPaymentMethods()
	{
		return array('YANDEX', 'CASH_ON_DELIVERY', 'CARD_ON_DELIVERY');
	}

	/** @deprecated */
	public static function onSaleCancelOrder($orderId, $value, $description)
	{
		if($value != "Y" || self::$isYandexRequest)
			return false;

		global $USER;

		$arSubstatuses = self::getOrderSubstatuses();

		if(strlen($description) <= 0 || !$USER->IsAdmin() || empty($arSubstatuses[$description]))
			$description = "USER_CHANGED_MIND";

		return self::onSaleStatusOrder($orderId, "CANCELED", $description);
	}

	/** @deprecated */
	public static function onSaleDeliveryOrder($orderId, $value)
	{
		if($value != "Y" || self::$isYandexRequest)
			return false;

		return self::onSaleStatusOrder($orderId, "ALLOW_DELIVERY");
	}

	/** @deprecated */
	public static function onSalePayOrder($orderId, $value)
	{
		if($value != "Y" || self::$isYandexRequest)
			return false;

		return self::onSaleStatusOrder($orderId, "PAYED");
	}

	/** @deprecated */
	public static function onSaleDeductOrder($orderId, $value)
	{
		if($value != "Y" || self::$isYandexRequest)
			return false;

		return self::onSaleStatusOrder($orderId, "DEDUCTED");
	}

	/** @deprecated */
	protected function getDeliveryOptions($delivery, $price, $weight = 0)
	{
		$arResult = array();

		$locationId = $this->locationMapper->getLocationId($delivery['region']);

		if($locationId > 0)
		{
			foreach ($this->mapDelivery as $deliveryId => $deliveryType)
			{
				if($deliveryType == "")
					continue;

				$filter = 	array(
					"ID" => $deliveryId,
					"LID" => $this->siteId,
					"ACTIVE" => "Y",
					"LOCATION" => $locationId,
					"+<=ORDER_PRICE_FROM" => $price,
					"+>=ORDER_PRICE_TO" => $price
				);

				if(intval($weight) > 0)
				{
					$filter["+<=WEIGHT_FROM"] = $weight;
					$filter["+>=WEIGHT_TO"] = $weight;
				}

				$dbDelivery = CSaleDelivery::GetList(
					array("SORT"=>"ASC", "NAME"=>"ASC"),
					$filter
				);

				if($arDelivery = $dbDelivery->Fetch())
				{
					$arDates = $this->getDeliveryDates(
						$arDelivery["PERIOD_FROM"],
						$arDelivery["PERIOD_TO"],
						$arDelivery["PERIOD_TYPE"]
					);

					if(!empty($arDates))
					{
						$arDeliveryTmp = array(
							"id" => $arDelivery["ID"],
							"type" =>$deliveryType,
							"serviceName" => substr($arDelivery["NAME"], 0, 50),
							"price" => round(floatval($arDelivery["PRICE"]), 2),
							"dates" => $arDates
						);

						if($deliveryType == "PICKUP" && !empty($this->outlets))
							foreach($this->outlets as $outlet)
								$arDeliveryTmp["outlets"][] = array("id" => intval($outlet));

						$arResult[] = $arDeliveryTmp;
					}
				}
			}
		}

		return $arResult;
	}

	/** @deprecated */
	protected function getLocationByCityName($cityName)
	{
		return $this->locationMapper->getLocationByCityName($cityName);
	}

	/**
	 * Moves settings from options to DB
	 * @deprecated
	 */
	public static function settingsConverter()
	{
		$settings = static::getSettings();

		if(!empty($settings) && !empty($settings["SETTINGS"]))
		{
			return false;
		}

		if(!CSaleYMHandler::install())
		{
			return false;
		}

		$settings = array();

		$rsSites = CSite::GetList($by = "sort", $order = "asc", Array());

		while ($arSite = $rsSites->Fetch())
		{
			$serSiteSett = COption::GetOptionString("sale", "yandex_market_purchase_settings", "", $arSite["ID"], true);
			$siteSett = unserialize($serSiteSett);

			if(is_array($siteSett) && !empty($siteSett))
				$settings[$arSite["ID"]] = $siteSett;
		}

		if(empty($settings))
		{
			$serSiteSett = COption::GetOptionString("sale", "yandex_market_purchase_settings", "");
			$siteSett = unserialize($serSiteSett);

			if(is_array($siteSett) && !empty($siteSett))
				$settings[CSite::GetDefSite()] = $siteSett;
		}

		if(empty($settings))
		{
			return false;
		}

		if(!CSaleYMHandler::saveSettings($settings))
		{
			return false;
		}

		if(!CSaleYMHandler::setActivity(true))
		{
			return false;
		}

		if(!CSaleYMHandler::eventsStart())
		{
			return false;
		}

		return true;
	}

	/**
	 * Take out correnspondence to
	 * @return string
	 * @deprecated
	 */
	public static function takeOutOrdersToCorrespondentTable()
	{
		$platformId = YMarket\YandexMarket::getInstance()->getId();

		if(intval($platformId) <= 0)
			return "";

		$conn = \Bitrix\Main\Application::getConnection();
		$helper = $conn->getSqlHelper();

		$correspondence = $conn->query(
			'SELECT ID
				FROM '.$helper->quote(\Bitrix\Sale\TradingPlatform\OrderTable::getTableName()).'
				WHERE '.$helper->quote('TRADING_PLATFORM_ID').'='.$platformId
		);

		//check if we already tried to convert
		if ($correspondence->fetch())
			return "";

		if($conn->getType() == "mssql")
			$lenOpName = "LEN";
		else
			$lenOpName = "LENGTH";

		if($conn->getType() == "oracle")
			$right = 'SUBSTR(XML_ID, -('.$lenOpName.'(XML_ID)-'.strlen(self::XML_ID_PREFIX).'))';
		else
			$right = 'RIGHT(XML_ID, '.$lenOpName.'(XML_ID)-'.strlen(self::XML_ID_PREFIX).')';

		//take out correspondence to
		$sql = 'INSERT INTO '.\Bitrix\Sale\TradingPlatform\OrderTable::getTableName().' (ORDER_ID, EXTERNAL_ORDER_ID, TRADING_PLATFORM_ID)
				SELECT ID, '.$right.', '.$platformId.'
					FROM '.\Bitrix\Sale\Internals\OrderTable::getTableName().'
					WHERE XML_ID LIKE '."'".self::XML_ID_PREFIX."%'";

		try
		{
			$conn->queryExecute($sql);
		}
		catch(\Bitrix\Main\DB\SqlQueryException $e)
		{
			CEventLog::Add(array(
				"SEVERITY" => "ERROR",
				"AUDIT_TYPE_ID" => "YMARKET_XML_ID_CONVERT_INSERT_ERROR",
				"MODULE_ID" => "sale",
				"ITEM_ID" => "YMARKET",
				"DESCRIPTION" => __FILE__.': '.$e->getMessage(),
			));
		}

		return "";
	}

	/** @internal  */
	public static function convertDeliveryAndPSIds()
	{
		if(\Bitrix\Main\Config\Option::get("sale", 'YANDEX_MARKET_DELIVERY_PS_IDS_CONVERTED', 'N') == 'Y')
			return '';

		$settings = \CSaleYMHandler::getSettings(false);

		if(!empty($settings['SETTINGS']) && is_array($settings['SETTINGS']))
		{
			$message = '';

			foreach($settings['SETTINGS'] as $siteId => $siteSettings)
			{
				if(!empty($siteSettings['DELIVERIES']) && is_array($siteSettings['DELIVERIES']))
				{
					$newDeliveries = array();
					$message .= 'Deliveries ids converted: ';

					foreach($siteSettings['DELIVERIES'] as $oldId => $type)
					{
						$newId = \Bitrix\Sale\Delivery\Services\Manager::getIdByCode($oldId);
						$message .= $oldId.'->'.$newId.', ';

						if(intval($newId) > 0)
							$newDeliveries[$newId] = $type;
					}

					$settings['SETTINGS'][$siteId]['DELIVERIES'] = $newDeliveries;
				}
			}

			if(!empty($message))
			{
				CEventLog::Add(array(
					"SEVERITY" => "INFO",
					"AUDIT_TYPE_ID" => "YANDEX_MARKET_DELIVERY_PS_IDS_CONVERTED",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "YMARKET",
					"DESCRIPTION" => $message,
				));

				$res = Bitrix\Sale\TradingPlatformTable::update(
					YMarket\YandexMarket::getInstance()->getId(),
					array("SETTINGS" => $settings['SETTINGS'])
				);

				if($res->isSuccess())
					\Bitrix\Main\Config\Option::set("sale", 'YANDEX_MARKET_DELIVERY_PS_IDS_CONVERTED', 'Y');
			}
		}

		return '';
	}

	/**
	 * @param string $yandexOrderId
	 * @return \Bitrix\Sale\Order|null
	 */
	public function loadOrderByYandexOrderId($yandexOrderId)
	{
		if (strlen($yandexOrderId) <= 0)
			return null;

		$filter = array(
			'filter' => array(
				'=SOURCE.EXTERNAL_ORDER_ID' => $yandexOrderId,
				'=SOURCE.TRADING_PLATFORM_ID' => YMarket\YandexMarket::getInstance()->getId()
			),
			'select' => array('*'),
			'runtime' => array(
				'SOURCE' => array(
					'data_type' => '\Bitrix\Sale\TradingPlatform\OrderTable',
					'reference' => array(
						'ref.ORDER_ID' => 'this.ID',
					),
					'join_type' => 'left'
				)
			)
		);

		$list = \Bitrix\Sale\Order::loadByFilter($filter);

		if (!empty($list) && is_array($list))
			return reset($list);

		return null;
	}

	/**
	 * Returns true if it is yandex request
	 * @return bool
	 */
	public static function isYandexRequest()
	{
		return self::$isYandexRequest;
	}

	/**
	 * @param Sale\Internals\Entity $entity
	 * @return bool
	 */
	protected static function isOrderEntity(Sale\Internals\Entity $entity)
	{
		if ($entity instanceof Sale\Order
			|| $entity instanceof Sale\Shipment
			|| $entity instanceof Sale\Payment
		)
		{
			return $entity::getRegistryType() === Sale\Registry::REGISTRY_TYPE_ORDER;
		}

		return false;
	}
}
