<?php

namespace Bitrix\Sale\Delivery\Services;

use Bitrix\Sale\Order;
use Bitrix\Main\Loader;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Internals\Input;
use Bitrix\Sale\Delivery\Helper;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\ShipmentCollection;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Inputs\MultiControlString;

Loc::loadMessages(__FILE__);

/**
 * Class Automatic
 * Proxy for old delivery handlers to work with new API.
 * @package Bitrix\Sale\Delivery\Services
 */
class Automatic extends Base
{
	/** @var string */
	protected $handlerCode = 'BITRIX_AUTOMATIC';

	protected $sid = "";
	protected $oldConfig = array();
	protected $handlerInitParams = array();

	protected static $canHasProfiles = true;

	public function __construct(array $initParams)
	{
		parent::__construct($initParams);

		if(isset($this->config["MAIN"]["SID"]) && $this->config["MAIN"]["SID"] <> '')
		{
			$initedHandlers = self::getRegisteredHandlers("SID");

			if(!isset($initedHandlers[$this->config["MAIN"]["SID"]]))
			{
				throw new SystemException("Can't initialize service with code\"".$this->config["MAIN"]["SID"]."\"");
			}

			$this->sid = $this->code = $this->config["MAIN"]["SID"];
			$this->handlerInitParams = $this->getHandlerInitParams($this->sid);

			if(!empty($this->handlerInitParams["TRACKING_CLASS_NAME"]))
				$this->setTrackingClass($this->handlerInitParams["TRACKING_CLASS_NAME"]);

			if($this->handlerInitParams == false)
				throw new SystemException("Can't get delivery services init params. Delivery id: ".$this->id.", sid: ".$this->sid);

			if($this->currency == '' && !empty($this->handlerInitParams["BASE_CURRENCY"]))
				$this->currency = $this->handlerInitParams["BASE_CURRENCY"];
		}

		$initParams = self::convertNewServiceToOld($initParams, $this->sid);

		if(isset($initParams["CONFIG"]))
			$this->oldConfig = $initParams["CONFIG"];
	}

	/**
	 * @inheritDoc
	 */
	public function getHandlerCode(): string
	{
		return 'BITRIX_' . (string)$this->sid;
	}

	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_HANDL_AUT_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_HANDL_AUT_DESCRIPTION");
	}

	protected function getConfigStructure()
	{
		static $handlers = null;
		static $jsData = array();

		$initedHandlers = self::getRegisteredHandlers("SID");

		sortByColumn($initedHandlers, array(mb_strtoupper("NAME") => SORT_ASC));

		if($handlers === null)
		{
			$handlers = array("" => "");

			foreach($initedHandlers as $handler)
			{
				if (isset($handler["DEPRECATED"]) && $handler["DEPRECATED"] = "Y")
				{
					continue;
				}

				if (!self::isAutomaticHandlerCompatible($handler))
				{
					continue;
				}

				$handlers[$handler["SID"]] = $handler["NAME"]." [".$handler["SID"]."]";
				$jsData[$handler["SID"]] = array(
					htmlspecialcharsbx($handler["NAME"]),
					htmlspecialcharsbx($handler["DESCRIPTION"]),
					htmlspecialcharsbx($handler["DESCRIPTION_INNER"])
				);
			}
		}

		if (($this->handlerInitParams["SID"] ?? '') == '' || $this->id <=0)
		{
			$result = array(
				"MAIN" => array(
					"TITLE" => Loc::getMessage("SALE_DLVR_HANDL_AUT_HANDLER_SETTINGS"),
					"DESCRIPTION" => Loc::getMessage("SALE_DLVR_HANDL_AUT_HANDLER_SETTINGS_DSCR"),
					"ITEMS" => array (
						"SID" => array(
							"TYPE" => "ENUM",
							"NAME" => Loc::getMessage("SALE_DLVR_HANDL_AUT_HANDLER_CHOOSE"),
							"OPTIONS" => $handlers,
							"ONCHANGE" => "var data=".\CUtil::PhpToJSObject($jsData)."; BX.onCustomEvent('onDeliveryServiceNameChange',[{name: data[this.value][0], description: data[this.value][1]}]); BX('adm-sale-delivery-auto-description_inner_view').innerHTML=data[this.value][2]; //this.form.submit();"
						),
						"DESCRIPTION_INNER" => array(
								"TYPE" => "DELIVERY_READ_ONLY",
								"NAME" => Loc::getMessage("SALE_DLVR_HANDL_AUT_DESCRIPTION_INNER"),
								"ID" => "adm-sale-delivery-auto-description_inner",
								"DEFAULT" => ""
						),
					)
				)
			);
		}
		else
		{
			$handler = $this->handlerInitParams["SID"];

			$result = array(
				"MAIN" => array(
					"TITLE" => Loc::getMessage("SALE_DLVR_HANDL_AUT_HANDLER_SETTINGS"),
					"DESCRIPTION" => Loc::getMessage("SALE_DLVR_HANDL_AUT_HANDLER_SETTINGS_DSCR"),
					"ITEMS" => array (
						"SID" => array(
							"TYPE" => "DELIVERY_READ_ONLY",
							"NAME" => Loc::getMessage("SALE_DLVR_HANDL_AUT_HANDLER_CHOOSE"),
							"VALUE" => $handler,
							"VALUE_VIEW" => $handlers[$handler]
						),
						"DESCRIPTION_INNER" => array(
							"TYPE" => "DELIVERY_READ_ONLY",
							"NAME" => Loc::getMessage("SALE_DLVR_HANDL_AUT_DESCRIPTION_INNER"),
							"VALUE" => $this->handlerInitParams["DESCRIPTION_INNER"]
						)
					)
				)
			);
		}

		$serviceCurrency = $this->currency;

		if(\Bitrix\Main\Loader::includeModule('currency'))
		{
			$currencyList = CurrencyManager::getCurrencyList();

			if (isset($currencyList[$this->currency]))
				$serviceCurrency = $currencyList[$this->currency];

			unset($currencyList);
		}

		$marginTypes = array(
			"%" => "%",
			"CURRENCY" => $serviceCurrency
		);

		$result["MAIN"]["ITEMS"]["MARGIN_VALUE"] = array(
			"TYPE" => "STRING",
			"NAME" => Loc::getMessage("SALE_DLVR_HANDL_AUT_MARGIN_VALUE"),
			"DEFAULT" => 0
		);

		$result["MAIN"]["ITEMS"]["MARGIN_TYPE"] = array(
			"TYPE" => "ENUM",
			"NAME" => Loc::getMessage("SALE_DLVR_HANDL_AUT_MARGIN_TYPE"),
			"DEFAULT" => "%",
			"OPTIONS" => $marginTypes
		);

		if($this->sid <> '')
		{
			$configProfileIds = array_keys($this->handlerInitParams["PROFILES"]);
		}
		else
		{
			$configProfileIds = array();
		}

		if(isset($this->oldConfig["CONFIG_GROUPS"]))
		{
			$groupProfileIds = array_keys($this->oldConfig["CONFIG_GROUPS"]);
			$intersect = array_intersect($groupProfileIds, $configProfileIds);

			foreach($intersect as $pid)
				unset($this->oldConfig["CONFIG_GROUPS"][$pid]);
		}

		$oldConfig = $this->convertOldConfigToNew($this->oldConfig);

		if(!empty($oldConfig))
		{
			if(isset($oldConfig["CONFIG_GROUPS"]["MAIN"]))
			{
				$oldConfig["CONFIG_GROUPS"]["MAIN_OLD"] = $oldConfig["CONFIG_GROUPS"]["MAIN"];
				unset($oldConfig["CONFIG_GROUPS"]["MAIN"]);
			}

			$result = array_merge($result, $oldConfig);
		}

		return $result;
	}

	public function prepareFieldsForSaving(array $fields)
	{
		$fields = parent::prepareFieldsForSaving($fields);

		if(!isset($fields["CONFIG"]))
			return $fields;

		if(!isset($fields["CONFIG"]["MAIN"]["SID"]) || $fields["CONFIG"]["MAIN"]["SID"] == '')
			throw new SystemException(Loc::getMessage("SALE_DLVR_HANDL_AUT_ERROR_HANDLER"));

		if($this->sid == '')
			return $fields;

		$fields["CODE"] = $this->sid;

		$configMain = $fields["CONFIG"]["MAIN"];

		if (isset($this->handlerInitParams["DBSETSETTINGS"]) && is_callable($this->handlerInitParams["DBSETSETTINGS"]))
		{
			$oldSettings = $fields["CONFIG"];
			unset($oldSettings["MAIN"]);

			$oldSettings = self::convertNewSettingsToOld($oldSettings);

			if (!$strOldSettings = call_user_func($this->handlerInitParams["DBSETSETTINGS"], $oldSettings))
				throw new SystemException("Can't save delivery services's old settings");
		}
		else
		{
			$strOldSettings = "";
		}

		$strOldSettings = serialize($strOldSettings);
		$fields["CONFIG"] = array(
			"MAIN" => $configMain
		);

		$fields["CONFIG"]["MAIN"]["OLD_SETTINGS"] = $strOldSettings;

		if(isset($this->handlerInitParams["CURRENCY"]) && $this->handlerInitParams["CURRENCY"] <> '')
			$fields["CURRENCY"] = $this->handlerInitParams["CURRENCY"];

		return $fields;
	}

	public static function convertNewSettingsToOld(array $newSettings = array())
	{
		$result = array();

		foreach($newSettings as $key => $value)
		{
			if(is_array($value))
				$result = array_merge($result, self::convertNewSettingsToOld($value));
			else
				$result[$key] = $value;
		}

		return $result;
	}

	public static function convertOldConfigToNew($oldConfig)
	{
		if(!isset($oldConfig["CONFIG_GROUPS"]) || !is_array($oldConfig["CONFIG_GROUPS"]) || !isset($oldConfig["CONFIG"]) || !is_array($oldConfig["CONFIG"]))
			return array();

		$result = array();


		Input\Manager::getTypes();
		$mc = new MultiControlString();

		foreach($oldConfig["CONFIG_GROUPS"] as $groupId => $groupName)
		{
			$handlerConfig = array(
				"TITLE" =>	$groupName,
				"DESCRIPTION" => $groupName,
				"ITEMS" => array()
			);

			foreach($oldConfig["CONFIG"] as $key =>  $param)
			{
				if($param["GROUP"] == $groupId)
				{
					$newParam = self::convertOldConfigParamToNew($param);

					if(isset($param["MCS_ID"]))
					{
						if($newParam["TYPE"] == 'DELIVERY_MULTI_CONTROL_STRING')
						{
							if(!$mc->isClean())
							{
								$handlerConfig["ITEMS"][$mc->getKey()] = $mc->getParams();
								$mc->clean();
							}

							$mc->setParams($key, $newParam);
						}
						elseif(!$mc->isClean())
						{
							$mc->addItem($key, $newParam);
						}
						else
						{
							$handlerConfig["ITEMS"][$key] = $newParam;
						}
					}
					elseif(!$mc->isClean())
					{
						$handlerConfig["ITEMS"][$mc->getKey()] = $mc->getParams();
						$mc->clean();
						$handlerConfig["ITEMS"][$key] = $newParam;
					}
					else
					{
						$handlerConfig["ITEMS"][$key] = $newParam;
					}
				}
			}

			if(!$mc->isClean())
			{
				$handlerConfig["ITEMS"][$mc->getKey()] = $mc->getParams();
				$mc->clean();
			}

			$result[$groupId] = $handlerConfig;
		}

		return $result;
	}

	protected static function convertOldConfigParamToNew(array $oldParam)
	{
		$result = array();

		if(isset($oldParam["TYPE"]))
		{
			switch($oldParam["TYPE"])
			{
				case 'STRING':
				case 'TEXT':
					$result["TYPE"] = 'STRING';
					break;

				case 'DROPDOWN':
					$result["TYPE"] = 'ENUM';
					break;

				case 'RADIO':
					$result["TYPE"] = 'ENUM';
					$result["MULTIELEMENT"] = 'Y';
					break;

				case 'CHECKBOX':
					$result["TYPE"] = 'Y/N';
					break;

				case 'SECTION':
					$result["TYPE"] = 'DELIVERY_SECTION';
					break;

				case 'MULTI_CONTROL_STRING':
					$result["TYPE"] = 'DELIVERY_MULTI_CONTROL_STRING';
					break;

				default:
					$result["TYPE"] = 'DELIVERY_READ_ONLY';
					break;
			}
		}
		else
		{
			$result["TYPE"] = 'STRING';
		}

		if(isset($oldParam["TITLE"]))
			$result["NAME"] = $oldParam["TITLE"];

		if(isset($oldParam["DEFAULT"]))
			$result["DEFAULT"] = $oldParam["DEFAULT"];

		if(isset($oldParam["VALUE"]))
			$result["VALUE"] = $oldParam["VALUE"];

		if(isset($oldParam["VALUES"]))
			$result["OPTIONS"] = $oldParam["VALUES"];

		return $result;
	}

	protected static function convertNewOrderToOld(\Bitrix\Sale\Shipment $shipment)
	{
		return \CSaleDelivery::convertOrderNewToOld($shipment);
	}

	public static function getHandlerInitParams($sid)
	{
		if($sid == '')
			return false;

		$handlers = self::getRegisteredHandlers("SID");

		return isset($handlers[$sid]) ? $handlers[$sid] : false;
	}

	public static function initHandlers()
	{
		static $isHandlerInited = false;

		if($isHandlerInited)
			return true;

		$arPathList = array( // list of valid services include files paths (security)
			\Bitrix\Main\Config\Option::get('sale', 'delivery_handles_custom_path', BX_PERSONAL_ROOT.'/php_interface/include/sale_delivery/'),
			"/bitrix/modules/sale/delivery/",
		);

		$arLoadedHandlers = array();

		foreach ($arPathList as $basePath)
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$basePath) && is_dir($_SERVER["DOCUMENT_ROOT"].$basePath))
			{
				$handle = @opendir($_SERVER["DOCUMENT_ROOT"].$basePath);
				while(($filename = readdir($handle)) !== false)
				{
					if($filename == "." || $filename == ".." || in_array($filename, $arLoadedHandlers))
						continue;

					if (!is_dir($_SERVER["DOCUMENT_ROOT"].$basePath."/".$filename) && mb_substr($filename, 0, 9) == "delivery_")
					{
						if(\Bitrix\Main\IO\Path::getExtension($filename) == 'php')
						{
							$arLoadedHandlers[] = $filename;
							require_once($_SERVER["DOCUMENT_ROOT"].$basePath."/".$filename);
						}
					}
				}
				@closedir($handle);
			}
		}

		$isHandlerInited = true;
		return true;
	}

	public static function getRegisteredHandlers($indexBy = "")
	{
		static $arHandlersList = array();

		if(isset($arHandlersList[$indexBy]) && is_array($arHandlersList[$indexBy]))
			return $arHandlersList[$indexBy];

		self::initHandlers();

		$arHandlersList[$indexBy] = array();

		foreach(GetModuleEvents("sale", "onSaleDeliveryHandlersBuildList", true) as $arHandler)
		{
			$initParams = ExecuteModuleEventEx($arHandler);


			if($indexBy <> '' && isset($initParams[$indexBy]))
				$arHandlersList[$indexBy][$initParams[$indexBy]] = $initParams;
			else
				$arHandlersList[$indexBy][] = $initParams;
		}

		return $arHandlersList[$indexBy];
	}

	public static function convertNewServiceToOld($service, $sid = false)
	{
		if(!$sid && !isset($service["CONFIG"]["MAIN"]["SID"]))
			return array();

		self::initHandlers();
		$handlers = self::getRegisteredHandlers("SID");

		if($sid !== false)
			$service["SID"] = $sid;
		else
			$service["SID"] = $service["CONFIG"]["MAIN"]["SID"];

		$handlerExists = isset($handlers[$service["SID"]]) && is_array($handlers[$service["SID"]]);

		$service["TAX_RATE"] = $service["CONFIG"]["MAIN"]["MARGIN_VALUE"]; //todo: %, CURRENCY
		$service["INSTALLED"] = 'Y';

		$service["BASE_CURRENCY"] = $service["CURRENCY"];
		$service["SETTINGS"] = $service["CONFIG"]["MAIN"]["OLD_SETTINGS"] ?? '';
		$service["HANDLER"] = '';
		if ($handlerExists)
		{
			$service["HANDLER"] = $handlers[$service["SID"]]["HANDLER"] ?? '';
		}

		$service['LOGOTIP'] = (int)($service['LOGOTIP'] ?? 0);
		if ($service["LOGOTIP"] > 0)
		{
			$service["LOGOTIP"] = \CFile::getFileArray($service["LOGOTIP"]);
		}

		$siteId = false;

		$serviceId = (int)($service['ID'] ?? 0);
		if ($serviceId)
		{
			$restrictions = \Bitrix\Sale\Delivery\Restrictions\Manager::getRestrictionsList($serviceId);

			foreach($restrictions as $restriction)
			{
				if($restriction["CLASS_NAME"] == '\Bitrix\Sale\Delivery\Restrictions\BySite' && !empty($restriction["PARAMS"]["SITE_ID"]))
				{
					if(is_array($restriction["PARAMS"]["SITE_ID"]))
					{
						reset($restriction["PARAMS"]["SITE_ID"]);
						$siteId = current($restriction["PARAMS"]["SITE_ID"]);
					}
					else
					{
						$siteId = $restriction["PARAMS"]["SITE_ID"];
					}

					break;
				}
			}
		}

		if(!$siteId)
			$siteId = Helper::getDefaultSiteId();

		$service['CONFIG'] = [];
		if ($handlerExists)
		{
			$service["CONFIG"] = self::createConfig($handlers[$service["SID"]], $service["SETTINGS"], $siteId);
		}
		$service["SETTINGS"] = unserialize($service["SETTINGS"], ['allowed_classes' => false]);
		$service["PROFILES"] = array();

		if ($serviceId > 0)
		{
			foreach(Manager::getByParentId($serviceId) as $profile)
			{
				$profileId = $profile["CONFIG"]["MAIN"]["PROFILE_ID"];
				$profileParams = array(
					"TITLE" => $profile["NAME"],
					"DESCRIPTION" => $profile["DESCRIPTION"],
					"TAX_RATE" => $service["CONFIG"]["MAIN"]["MARGIN_VALUE"] ?? '',
					"ACTIVE" =>  $profile["ACTIVE"]
				);

				$restrictions = Restrictions\Manager::getRestrictionsList($profile["ID"]);

				foreach($restrictions as $restriction)
				{
					switch($restriction["CLASS_NAME"])
					{
						case '\Bitrix\Sale\Delivery\Restrictions\ByWeight':
							$profileParams["RESTRICTIONS_WEIGHT"] = array($restriction["PARAMS"]["MIN_WEIGHT"], $restriction["PARAMS"]["MAX_WEIGHT"]);
							break;

						case '\Bitrix\Sale\Delivery\Restrictions\ByPrice':
							$profileParams["RESTRICTIONS_SUM"] = array($restriction["PARAMS"]["MIN_PRICE"], $restriction["PARAMS"]["MAX_PRICE"]);
							break;

						case '\Bitrix\Sale\Delivery\Restrictions\ByDimensions':
							$profileParams["RESTRICTIONS_DIMENSIONS"] = array(
								$restriction["PARAMS"]["LENGTH"],
								$restriction["PARAMS"]["WIDTH"],
								$restriction["PARAMS"]["HEIGHT"]
							);

							$profileParams["RESTRICTIONS_MAX_SIZE"] = $restriction["PARAMS"]["MAX_DIMENSION"];
							$profileParams["RESTRICTIONS_DIMENSIONS_SUM"] = $restriction["PARAMS"]["MAX_DIMENSIONS_SUM"];
							break;

						default:
							break;
					}

				}

				$service["PROFILES"][$profileId] = $profileParams;
			}
		}

		unset($service["CODE"]);

		if ($handlerExists)
		{
			$result = array_merge($handlers[$service["SID"]], $service);
		}
		else
		{
			$result = $service;
		}

		return $result;
	}

	public function getOldDbSettings($settings)
	{
		if($settings == '')
			return array();

		if(!is_callable($this->handlerInitParams["DBGETSETTINGS"]))
			return $settings;

		return call_user_func($this->handlerInitParams["DBGETSETTINGS"], $settings);
	}

	public static function createConfig($initHandlerParams, $settings, $siteId = false)
	{
		static $result = array();
		$hitCacheId = md5(serialize($initHandlerParams))."_".md5(serialize($settings))."_".strval($siteId);

		if(!isset($result[$hitCacheId]))
		{
			$config = array(
				"CONFIG_GROUPS" => array(),
				"CONFIG" => array(),
			);

			if (is_callable($initHandlerParams["GETCONFIG"]))
			{
				$conf = call_user_func($initHandlerParams["GETCONFIG"], $siteId);

				if(isset($conf["CONFIG_GROUPS"]))
					$config["CONFIG_GROUPS"] = $conf["CONFIG_GROUPS"];

				if ($settings <> '' && is_callable($initHandlerParams["DBGETSETTINGS"]))
				{
					$settings = unserialize($settings, ['allowed_classes' => false]);
					$arConfigValues = call_user_func($initHandlerParams["DBGETSETTINGS"], $settings);
				}
				else
				{
					$arConfigValues = array();
				}

				foreach ($conf["CONFIG"] as $key => $arConfig)
				{
					if (is_array($conf["CONFIG"][$key]))
					{
						$config["CONFIG"][$key] = $conf["CONFIG"][$key];

						if(isset($arConfigValues[$key]))
							$config["CONFIG"][$key]["VALUE"] = $arConfigValues[$key];
						elseif(isset($conf["CONFIG"][$key]["DEFAULT"]))
							$config["CONFIG"][$key]["VALUE"] = $conf["CONFIG"][$key]["DEFAULT"];
						else
							$config["CONFIG"][$key]["VALUE"] = "";
					}
				}
			}

			$result[$hitCacheId] = $config;
		}

		return $result[$hitCacheId];
	}

	protected function getCalcultor()
	{
		$result = false;

		if(isset($this->handlerInitParams["CALCULATOR"]) && is_callable($this->handlerInitParams["CALCULATOR"]))
			$result = $this->handlerInitParams["CALCULATOR"];

		return $result;
	}

	protected function getCompability()
	{
		$result = false;

		if(isset($this->handlerInitParams["COMPABILITY"]) && is_callable($this->handlerInitParams["COMPABILITY"]))
			$result = $this->handlerInitParams["COMPABILITY"];

		return $result;
	}

	protected static function getCompatibleProfiles($sid, $compatibilityFunc, array $config, Shipment $shipment)
	{
		if($sid == '')
			throw new ArgumentNullException("sid");

		static $result = array();
		$oldOrder = self::convertNewOrderToOld($shipment);

		if(!empty($oldOrder["ITEMS"]) && is_array($oldOrder["ITEMS"]))
		{
			$maxDimensions = array();

			foreach($oldOrder["ITEMS"] as $item)
			{
				if (!isset($item['DIMENSIONS']))
				{
					continue;
				}
				if (is_string($item['DIMENSIONS']) && $item['DIMENSIONS'] !== '')
				{
					$item['DIMENSIONS'] = unserialize($item['DIMENSIONS'], ['allowed_classes' => false]);
				}

				if(!is_array($item["DIMENSIONS"]) || empty($item["DIMENSIONS"]))
					continue;

				$maxDimensions = \CSaleDeliveryHelper::getMaxDimensions(
					array(
						$item["DIMENSIONS"]["WIDTH"],
						$item["DIMENSIONS"]["HEIGHT"],
						$item["DIMENSIONS"]["LENGTH"]
					),
					$maxDimensions
				);
			}

			if(!empty($maxDimensions))
				$oldOrder["MAX_DIMENSIONS"] = $maxDimensions;
		}

		$hitCacheId = $sid.'_'.md5(serialize($oldOrder)).'_'.md5(serialize($config["CONFIG"]));

		if(!isset($result[$hitCacheId]))
		{
			$result[$hitCacheId] = call_user_func($compatibilityFunc, $oldOrder, $config["CONFIG"]);
		}

		return $result[$hitCacheId];
	}

	public function isProfileCompatible($profileId, $config, Shipment $shipment)
	{
		$compatibilityFunc = $this->getCompability();

		if($compatibilityFunc === false)
			return true;

		$res = $this->getCompatibleProfiles($this->sid.':'.$profileId, $compatibilityFunc, $config, $shipment);
		return is_array($res) && in_array($profileId, $res);
	}

	public function getOldConfig()
	{
		return $this->oldConfig;
	}

	public function getSid()
	{
		return $this->sid;
	}

	/**
	 * @param $profileId
	 * @param array $profileConfig
	 * @param \Bitrix\Sale\Shipment $shipment
	 * @return CalculationResult
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function calculateProfile($profileId, array $profileConfig, \Bitrix\Sale\Shipment $shipment)
	{
		static $result = array();
		$oldOrder = self::convertNewOrderToOld($shipment);
		$hitCacheId = $this->id.'_'.$profileId.'_'.md5(serialize($profileConfig)).'_'.md5(serialize($oldOrder));

		if(isset($result[$hitCacheId]))
			return clone $result[$hitCacheId];

		global $APPLICATION;
		$calcRes = new CalculationResult();
		$step = 0;
		$tmp = false;
		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		/** @var Order $order */
		$order = $shipmentCollection->getOrder();
		$shipmentCurrency = $order->getCurrency();

		if(!Loader::includeModule('currency'))
			throw new SystemException("Can't include module \"Currency\"");

		$calculator = $this->getCalcultor();

		if ($calculator!== false)
		{
			if ($res = call_user_func(
				$calculator,
				$profileId,
				$profileConfig["CONFIG"],
				$oldOrder,
				++$step,
				$tmp))
			{
				if (is_array($res))
				{
					if($res["RESULT"] == "OK" )
					{
						if(isset($res["TEXT"]))
							$calcRes->setDescription($res["TEXT"]);

						if(isset($res["VALUE"]))
							$calcRes->setDeliveryPrice(floatval($res["VALUE"]));

						if(isset($res["TRANSIT"]))
							$calcRes->setPeriodDescription($res["TRANSIT"]);

						if(isset($res["PERIOD_FROM"]))
							$calcRes->setPeriodFrom($res["PERIOD_FROM"]);

						if(isset($res["PERIOD_TO"]))
							$calcRes->setPeriodTo($res["PERIOD_TO"]);

						if(isset($res["PERIOD_TYPE"]))
							$calcRes->setPeriodType($res["PERIOD_TYPE"]);
					}
					else
					{
						if(isset($res["TEXT"]) && $res["TEXT"] <> '')
						{
							$calcRes->addError(new EntityError(
								$res["TEXT"],
								'DELIVERY_CALCULATION'
							));
						}
						else
						{
							$calcRes->addError(new EntityError(
								Loc::getMessage('SALE_DLVR_HANDL_AUT_ERROR_CALCULATION'),
								'DELIVERY_CALCULATION'
							));
						}
					}
				}
				elseif (is_numeric($res))
				{
					$calcRes->setDeliveryPrice(floatval($res));
				}
			}
			else
			{
				if ($ex = $APPLICATION->getException())
				{
					$calcRes->addError(new EntityError(
						$ex->getString(),
						'DELIVERY_CALCULATION'
					));
				}
				else
				{
					$calcRes->setDeliveryPrice(0);
				}
			}

			if ($calcRes->isSuccess() && $this->currency != $shipmentCurrency)
			{
				$calcRes->setDeliveryPrice(
					\CCurrencyRates::convertCurrency(
						$calcRes->getPrice(),
						$this->currency,
						$shipmentCurrency
				));
			}
		}

		$price = $calcRes->getPrice();

		$calcRes->setDeliveryPrice(
			$price + $this->getMarginPrice($price, $shipmentCurrency)
		);

		$result[$hitCacheId] = $calcRes;
		return clone $result[$hitCacheId];
	}

	public static function getChildrenClassNames()
	{
		return array(
			'\Bitrix\Sale\Delivery\Services\AutomaticProfile'
		);
	}

	public function getConfigValues()
	{
		return $this->config;
	}

	protected function getMarginPrice($price, $shipmentCurrency = '')
	{
		if($this->config["MAIN"]["MARGIN_TYPE"] == "%")
		{
			$marginPrice = $price * floatval($this->config["MAIN"]["MARGIN_VALUE"]) / 100;
		}
		else
		{
			$marginPrice = floatval($this->config["MAIN"]["MARGIN_VALUE"]);

			if($marginPrice && $shipmentCurrency != '' && $this->currency != $shipmentCurrency)
			{
				if(Loader::includeModule('currency'))
				{
					$marginPrice = \CCurrencyRates::convertCurrency(
						$marginPrice,
						$this->currency,
						$shipmentCurrency
					);
				}
			}
		}

		return $marginPrice;
	}

	public function getProfilesDefaultParams(array $fields = array())
	{
		if(empty($this->handlerInitParams["PROFILES"]) || !is_array($this->handlerInitParams["PROFILES"]))
			return array();

		$result = array();

		foreach($this->handlerInitParams["PROFILES"] as $profId => $params)
		{
			if(empty($params["TITLE"]))
				continue;

			$result[] = array(
				"CODE" => $this->handlerInitParams["SID"].":".$profId,
				"PARENT_ID" => $this->id,
				"NAME" => $params["TITLE"],
				"ACTIVE" => $this->active ? "Y" : "N",
				"SORT" => $this->sort,
				"DESCRIPTION" => isset($params["DESCRIPTION"]) ? $params["DESCRIPTION"] : "",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\AutomaticProfile',
				"CURRENCY" => $this->currency,
				"XML_ID" => Manager::generateXmlId(),
				"CONFIG" => array(
					"MAIN" => array(
						"PROFILE_ID" => $profId,
						"MARGIN_VALUE" => 0,
						"MARGIN_TYPE" => "%"
					)
				)
			);
		}

		return $result;
	}

	public static function onAfterAdd($serviceId, array $fields = array())
	{
		if($serviceId <= 0)
			return false;

		$fields["ID"] = $serviceId;
		$srv = new self($fields);
		$profiles = $srv->getProfilesDefaultParams($fields);

		if(!is_array($profiles))
			return false;

		$result = true;

		foreach($profiles as $profile)
		{
			$res = Manager::add($profile);
			$result = $result && $res->isSuccess();
		}

		return $result;
	}

	public function getProfilesList()
	{
		static $result = null;

		if($result === null)
		{
			$result = array();

			foreach($this->handlerInitParams["PROFILES"] as $profId => $params)
				if(!empty($params["TITLE"]))
					$result[$profId] = $params["TITLE"];
		}

		return $result;
	}

	public static function canHasProfiles()
	{
		return self::$canHasProfiles;
	}

	public function getAdminMessage()
	{
		$result = array();

		if(isset($this->handlerInitParams["GET_ADMIN_MESSAGE"]) && is_callable($this->handlerInitParams["GET_ADMIN_MESSAGE"]))
			$result = call_user_func($this->handlerInitParams["GET_ADMIN_MESSAGE"]);

		return $result;
	}

	public function execAdminAction()
	{
		$result = new Result();

		if(isset($this->handlerInitParams["EXEC_ADMIN_ACTION"]) && is_callable($this->handlerInitParams["EXEC_ADMIN_ACTION"]))
			$result = call_user_func($this->handlerInitParams["EXEC_ADMIN_ACTION"]);

		return $result;
	}

	public function getAdditionalInfoShipmentEdit(Shipment $shipment)
	{
		$result = '';

		if(isset($this->handlerInitParams["GET_ADD_INFO_SHIPMENT_EDIT"]) && is_callable($this->handlerInitParams["GET_ADD_INFO_SHIPMENT_EDIT"]))
		{
			$result = call_user_func(
				$this->handlerInitParams["GET_ADD_INFO_SHIPMENT_EDIT"],
				$shipment
			);

			if(!is_array($result))
				throw new SystemException('GET_ADD_INFO_SHIPMENT_EDIT return value must be array!');
		}

		return $result;
	}

	public function processAdditionalInfoShipmentEdit(Shipment $shipment, array $requestData)
	{
		$result = '';

		if(isset($this->handlerInitParams["PROCESS_ADD_INFO_SHIPMENT_EDIT"]) && is_callable($this->handlerInitParams["PROCESS_ADD_INFO_SHIPMENT_EDIT"]))
		{
			$result = call_user_func(
				$this->handlerInitParams["PROCESS_ADD_INFO_SHIPMENT_EDIT"],
				$shipment,
				$requestData
			);

			if($result && get_class($result) != 'Bitrix\Sale\Shipment')
				throw new SystemException('PROCESS_ADD_INFO_SHIPMENT_EDIT return value myst be of type "Bitrix\Sale\Result" !');
		}

		return $result;
	}

	public function getAdditionalInfoShipmentView(Shipment $shipment)
	{
		$result = '';

		if(isset($this->handlerInitParams["GET_ADD_INFO_SHIPMENT_VIEW"]) && is_callable($this->handlerInitParams["GET_ADD_INFO_SHIPMENT_VIEW"]))
		{
			$result = call_user_func(
				$this->handlerInitParams["GET_ADD_INFO_SHIPMENT_VIEW"],
				$shipment
			);

			if(!is_array($result))
				throw new SystemException('GET_ADD_INFO_SHIPMENT_VIEW return value must be array!');
		}

		return $result;
	}

	/**
	 * Checks is automatic handler compatible
	 *
	 * @param mixed $handler Old automatic handler.
	 * @return bool
	 */
	protected static function isAutomaticHandlerCompatible($handler): bool
	{
		$result = true;

		if (isset($handler["IS_HANDLER_COMPATIBLE"])
			&& is_callable($handler["IS_HANDLER_COMPATIBLE"]))
		{
			$result = call_user_func($handler["IS_HANDLER_COMPATIBLE"]);
		}

		return $result;
	}
}