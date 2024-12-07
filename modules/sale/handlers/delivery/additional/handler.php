<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main\Error,
	Bitrix\Main\Loader,
	Bitrix\Main\IO\File,
	Bitrix\Sale\Shipment,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Config\Option,
	Bitrix\Main\SystemException,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ArgumentNullException,
	Bitrix\Sale\Delivery\Services\Base,
	Bitrix\Sale\Delivery\ExtraServices,
	Bitrix\Sale\Location\ExternalTable,
	Bitrix\Sale\Location\LocationTable,
	Bitrix\Sale\Delivery\Services\Manager,
	Bitrix\Sale\Delivery\ExtraServices\Table,
	Bitrix\Sale\Location\Admin\LocationHelper,
	Sale\Handlers\Delivery\Additional\Location,
	Sale\Handlers\Delivery\Additional\RestClient,
	Bitrix\Sale\Internals\ServiceRestrictionTable,
	Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost,
	Sale\Handlers\Delivery\Additional\RusPost\Reliability;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses(
	'sale',
	array(
		__NAMESPACE__.'\Additional\Action' => 'handlers/delivery/additional/action.php',
		__NAMESPACE__.'\AdditionalProfile' => 'handlers/delivery/additional/profile.php',
		__NAMESPACE__.'\Additional\Location' => 'handlers/delivery/additional/location.php',
		__NAMESPACE__.'\Additional\CacheManager' => 'handlers/delivery/additional/cache.php',
		__NAMESPACE__.'\Additional\RestClient' => 'handlers/delivery/additional/restclient.php',
		__NAMESPACE__.'\Additional\RusPost\Helper' => 'handlers/delivery/additional/ruspost/helper.php',
		__NAMESPACE__.'\Additional\DeliveryRequests\RusPost\Handler' => 'handlers/delivery/additional/deliveryrequests/ruspost/handler.php',
	)
);

/**
 * Class AdditionalHandler
 * Allows to use additional delivery services
 * @package Sale\Handlers\Delivery
 */
class AdditionalHandler extends Base
{
	/** @var string The real type of the handler */
	protected $serviceType = "";
	protected static $canHasProfiles = true;
	protected static $whetherAdminExtraServicesShow = true;
	protected $trackingClass = '\Sale\Handlers\Delivery\AdditionalTracking';
	protected $trackingTitle = '';
	protected $trackingDescription = '';
	protected $profilesListFull = null;
	protected $extraServicesList = null;

	const LOGO_FILE_ID_OPTION = 'handlers_dlv_add_lgotip';

	/**
	 * AdditionalHandler constructor.
	 * @param array $initParams
	 * @throws ArgumentNullException
	 */

	public function __construct(array $initParams)
	{
		parent::__construct($initParams);

		if(isset($initParams['SERVICE_TYPE']) && $initParams['SERVICE_TYPE'] <> '')
			$this->serviceType = $initParams['SERVICE_TYPE'];
		elseif(isset($this->config["MAIN"]["SERVICE_TYPE"]))
			$this->serviceType = $this->config["MAIN"]["SERVICE_TYPE"];

		if($this->serviceType == '')
			throw new ArgumentNullException('initParams[SERVICE_TYPE]');

		if (
			isset($initParams['CONFIG']['MAIN']['SERVICE_TYPE'])
			&& $initParams['CONFIG']['MAIN']['SERVICE_TYPE'] === "RUSPOST"
		)
		{
			$this->setTrackingClass('\Bitrix\Sale\Delivery\Tracking\RusPost');
		}
		elseif(empty($this->config['MAIN']['TRACKING_TITLE']))
		{
			$this->trackingClass = '';
		}

		if(intval($this->id) <= 0)
		{
			$srvParams = $this->getServiceParams();

			if(!empty($srvParams['NAME']))
				$this->name = $srvParams['NAME'];

			if(!empty($srvParams['DESCRIPTION']))
				$this->description = $srvParams['DESCRIPTION'];

			if(!empty($srvParams['LOGOTIP']))
				$this->logotip = $srvParams['LOGOTIP'];
		}

		$this->deliveryRequestHandler = $this->getDeliveryRequestHandler();
	}

	/**
	 * @return string
	 */
	public function getHandlerCode(): string
	{
		return 'BITRIX_ADDITIONAL_' . (string)$this->serviceType;
	}

	public function getDeliveryRequestHandler()
	{
		$result = null;

		if($this->serviceType == "RUSPOST")
			if(!empty($this->config["MAIN"]["OTPRAVKA_AUTH_TOKEN"]) && !empty($this->config["MAIN"]["OTPRAVKA_AUTH_KEY"]))
				$result = new RusPost\Handler($this);

		return $result;
	}

	/**
	 * @return string Class title.
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVRS_ADD_NAME");
	}

	/**
	 * @return string Class, service description.
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVRS_ADD_DESCRIPTION");
	}

	/**
	 * @return array
	 * @throws SystemException
	 * todo: business values for default values
	 */
	protected function getConfigStructure()
	{
		$fields = $this->getServiceParams();

		if(empty($fields))
			throw new SystemException(Loc::getMessage('SALE_DLVRS_ADD_CONFIG_RECEIVE_ERROR'));

		$result = array(
			"MAIN" => array(
				"TITLE" => Loc::getMessage("SALE_DLVRS_ADD_MAIN_TITLE"),
				"DESCRIPTION" => Loc::getMessage("SALE_DLVRS_ADD_MAIN_DESCRIPTION"),
				"ITEMS" => array(
					"SERVICE_TYPE_NAME" => array(
						"TYPE" => "STRING",
						"NAME" => Loc::getMessage("SALE_DLVRS_ADD_SERVICE_TYPE"),
						"READONLY" => true,
						"DEFAULT" => $fields['NAME']
					),
					"SERVICE_TYPE" => array(
						"TYPE" => "STRING",
						"NAME" =>"SERVICE_TYPE",
						"HIDDEN" => true,
						"DEFAULT" => $this->serviceType
					)
				)
			)
		);

		if(!empty($fields['CONFIG']) && is_array($fields['CONFIG']))
		{
			foreach($fields['CONFIG'] as $key => $params)
			{
				if($this->serviceType == "RUSPOST" && $this->id <= 0 && $key == 'SHIPPING_POINT')
				{
					continue;
				}

				$result['MAIN']['ITEMS'][$key] = $params;
			}
		}

		$result['MAIN']['ITEMS']["DEFAULT_VALUES"] = array(
			"TYPE" => "DELIVERY_SECTION",
			"NAME" =>Loc::getMessage('SALE_DLVRS_ADD_MAIN_DEFAULT_VALUES'),
		);
		$result['MAIN']['ITEMS']["LENGTH_DEFAULT"] = array(
			"TYPE" => "STRING",
			"NAME" =>Loc::getMessage('SALE_DLVRS_ADD_MAIN_LENGTH_DEFAULT'),
			"DEFAULT" => 200
		);
		$result['MAIN']['ITEMS']["WIDTH_DEFAULT"] = array(
			"TYPE" => "STRING",
			"NAME" =>Loc::getMessage('SALE_DLVRS_ADD_MAIN_WIDTH_DEFAULT'),
			"DEFAULT" => 300
		);
		$result['MAIN']['ITEMS']["HEIGHT_DEFAULT"] = array(
			"TYPE" => "STRING",
			"NAME" =>Loc::getMessage('SALE_DLVRS_ADD_MAIN_HEIGHT_DEFAULT'),
			"DEFAULT" => 200
		);
		$result['MAIN']['ITEMS']["WEIGHT_DEFAULT"] = array(
			"TYPE" => "STRING",
			"NAME" =>Loc::getMessage('SALE_DLVRS_ADD_MAIN_WEIGHT_DEFAULT'),
			"DEFAULT" => 500
		);

		return $result;
	}

	/**
	 * @return array Supported types of services.
	 */
	public static function getSupportedServicesList()
	{
		static $result = null;

		if($result === null)
		{
			$client = new RestClient();
			$res = $client->getDeliveryList();

			if($res->isSuccess())
			{
				$result = $res->getData();
			}
			else
			{
				$errors = array();
				$notes = array();
				$nothingFound = false;

				/** @var Error $error */
				foreach($res->getErrorCollection() as $error)
				{
					if($error->getCode() === \Bitrix\Sale\Services\Base\RestClient::ERROR_NOTHING_FOUND)
					{
						$nothingFound = true;
						continue;
					}

					$message = $error->getMessage();

					if($message == 'verification_needed. License check failed.')
						$notes[$error->getCode()] = Loc::getMessage('SALE_DLVRS_ADD_LIST_LICENSE_WRONG');
					else
						$errors[$error->getCode()] = $message;
				}

				if(!empty($errors))
					$result = array("ERRORS" => $errors);

				if(!empty($notes))
					$result['NOTES'] = $notes;

				if(empty($errors) && empty($notes))
				{
					if($nothingFound === false || $res->getErrorCollection()->count() !== 1)
					{
						$errors[] = Loc::getMessage('SALE_DLVRS_ADD_LIST_RECEIVE_ERROR');
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getServiceParams()
	{
		$result = array();
		$client = new RestClient();
		$res = $client->getDeliveryFields($this->serviceType);

		if($res->isSuccess())
		{
			$logo = false;
			$logoId = intval($this->getLogoFileId());

			if($logoId > 0)
			{
				$logo = \CFile::GetByID($logoId)->Fetch();
			}

			$result = $res->getData();

			if(($logoId <= 0 || !$logo) && !empty($result['LOGOTIP']['CONTENT']) && !empty($result['LOGOTIP']['NAME']))
			{
				$tmpDir = \CTempFile::GetDirectoryName();
				CheckDirPath($tmpDir);
				$filePath = $tmpDir."/".$result['LOGOTIP']['NAME'];

				$res = File::putFileContents(
					$filePath,
					base64_decode($result['LOGOTIP']['CONTENT'])
				);

				if($res)
				{
					$file = \CFile::MakeFileArray($tmpDir."/".$result['LOGOTIP']['NAME']);
					$file['MODULE_ID'] = "sale";
					$logoId = intval(\CFile::SaveFile($file, "sale/delivery/logotip"));
					$this->setLogoFileId($logoId);
				}
			}

			$result['LOGOTIP'] = $logoId > 0 ? $logoId : 0;
		}

		return $result;
	}

	protected function getLogoFileId()
	{
		return intval(Option::get('sale', self::LOGO_FILE_ID_OPTION.'_'.$this->serviceType, ''));
	}

	protected function setLogoFileId($logoId)
	{
		if(intval($logoId) > 0)
			Option::set('sale', self::LOGO_FILE_ID_OPTION.'_'.$this->serviceType, $logoId);
	}

	/**
	 * @return array
	 */
	public static function getChildrenClassNames()
	{
		return array(
			'\Sale\Handlers\Delivery\AdditionalProfile'
		);
	}

	/**
	 * @return array profiles ids and names
	 */
	public function getProfilesList()
	{
		$result =array();

		$profiles = $this->getProfilesListFull();

		foreach($profiles as $profileType => $profile)
			$result[$profileType] = $profile['NAME'];

		return $result;
	}

	public function getTrackingStatuses(array $trackingNumbers = array())
	{
		$result = array();
		$client = new RestClient();
		$res = $client->getTrackingStatuses(
			$this->serviceType,
			AdditionalProfile::extractConfigValues($this->getConfig()),
			$trackingNumbers
		);

		if($res->isSuccess())
		{
			$data = $res->getData();

			if(!empty($data['STATUSES']) && is_array($data['STATUSES']))
				$result = $data['STATUSES'];
		}

		return $result;
	}

	public function getTrackingClassTitle()
	{
		return !empty($this->config['MAIN']['TRACKING_TITLE']) ? $this->config['MAIN']['TRACKING_TITLE'] : '';
	}

	public function getTrackingClassDescription()
	{
		return !empty($this->config['MAIN']['TRACKING_DESCRIPTION']) ? $this->config['MAIN']['TRACKING_DESCRIPTION'] : '';
	}

	/**
	 * @return array All profile fields.
	 */
	public function getProfilesListFull()
	{
		if($this->profilesListFull === null)
		{
			$this->profilesListFull = array();
			$client = new RestClient();
			$res = $client->getDeliveryProfilesList($this->serviceType);

			if($res->isSuccess())
				$this->profilesListFull = $res->getData();
		}

		return $this->profilesListFull;
	}

	/**
	 * @return bool
	 */
	public static function whetherAdminExtraServicesShow()
	{
		return self::$whetherAdminExtraServicesShow;
	}

	/**
	 * @return string
	 */
	public function getServiceType()
	{
		return $this->serviceType;
	}

	/**
	 * @param $shipment
	 * @return array
	 */
	public function getCompatibleProfiles($shipment)
	{
		return $this->getProfilesList();
	}

	/**
	 * @return bool
	 */
	public static function canHasProfiles()
	{
		return self::$canHasProfiles;
	}

	public static function onAfterUpdate($serviceId, array $fields = array())
	{
		/** @var self $service */
		$service = new self($fields);

		if ($service->getServiceType() == 'RUSPOST')
		{
			$config = $service->getConfigValues();
			$doInstall = isset($config['MAIN']['RELIABILITY']) && $config['MAIN']['RELIABILITY'] == 'Y';
			self::installReliability($serviceId, $doInstall);
		}
	}

	/**
	 * @param int $serviceId
	 * @param array $fields
	 * @return bool
	 */
	public static function onAfterAdd($serviceId, array $fields = array())
	{
		if($serviceId <= 0)
			return false;

		$result = true;

		//Add profiles
		$fields["ID"] = $serviceId;
		$srv = new self($fields);
		$profiles = $srv->getProfilesListFull();

		if(is_array($profiles) && !empty($profiles))
		{
			foreach($profiles as $profileType => $pFields)
			{
				if(isset($pFields['DEFAULT_INSTALL_SKIP']) && $pFields['DEFAULT_INSTALL_SKIP'] == 'Y')
					continue;

				$profile = $srv->getProfileDefaultParams($profileType, $pFields);
				$res = Manager::add($profile);

				if($res->isSuccess() && !empty($pFields["RESTRICTIONS"]) && is_array($pFields["RESTRICTIONS"]))
				{
					$profileId = $res->getId();

					foreach($pFields["RESTRICTIONS"] as $restrictionType => $params)
					{
						$srv->addRestriction($restrictionType, $profileId, $params);
					}
				}

				$result = $result && $res->isSuccess();
			}
		}

		$extraservices = $srv->getEmbeddedExtraServicesList();

		if(!empty($extraservices))
		{
			//Add extra services
			foreach($extraservices as $code => $esFields)
			{
				$esFields['DELIVERY_ID'] = $serviceId;
				$esFields['CODE'] = $code;
				$res = Table::add($esFields);
				$result = $result && $res->isSuccess();
			}
		}

		if ($srv->getServiceType() == 'RUSPOST')
		{
			$config = $srv->getConfigValues();
			$doInstall = isset($config['MAIN']['RELIABILITY']) && $config['MAIN']['RELIABILITY'] == 'Y';
			self::installReliability($serviceId, $doInstall);
		}

		return $result;
	}

	protected static function installReliability(int $serviceId, bool $doInstall)
	{
		if($doInstall)
		{
			Reliability\Service::install($serviceId);
		}
		else
		{
			Reliability\Service::unInstall($serviceId);
		}
	}

	/**
	 * @param string $type
	 * @param string $profileId
	 * @param array $params
	 * @throws \Exception
	 */
	protected function addRestriction($type, $profileId, array $params)
	{
		$fields  = array();
		$className = null;

		switch($type)
		{
			case "WEIGHT":
				$className = \Bitrix\Sale\Delivery\Restrictions\ByWeight::class;

				$p = array();
				if(isset($params['MIN']))	$p['MIN_WEIGHT'] = $params['MIN'];
				if(isset($params['MAX']))	$p['MAX_WEIGHT'] = $params['MAX'];

				if(!empty($p))
				{
					$fields = array(
						"SERVICE_ID" => $profileId,
						"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
						"PARAMS" => $p
					);
				}

				break;

			case "DIMENSIONS":
				$className = \Bitrix\Sale\Delivery\Restrictions\ByDimensions::class;

				$p = array();
				if(isset($params['LENGTH']))	$p['LENGTH'] = $params['LENGTH'];
				if(isset($params['WIDTH']))	$p['WIDTH'] = $params['WIDTH'];
				if(isset($params['HEIGHT']))	$p['HEIGHT'] = $params['HEIGHT'];

				if(!empty($p))
				{
					$fields = array(
						"SERVICE_ID" => $profileId,
						"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
						"PARAMS" => $p
					);
				}

				break;

			case "MAX_SIZE":

				$className = \Bitrix\Sale\Delivery\Restrictions\ByMaxSize::class;

				$p = array();
				if(isset($params['MAX_SIZE']) && intval($params['MAX_SIZE']) > 0)	$p['MAX_SIZE'] = $params['MAX_SIZE'];

				if(!empty($p))
				{
					$fields = array(
						"SERVICE_ID" => $profileId,
						"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
						"PARAMS" => $p
					);
				}

				break;

			case 'BY_LOCATION':
			case 'EXCLUDE_LOCATION':

				$className = ($type === 'BY_LOCATION')
					? \Bitrix\Sale\Delivery\Restrictions\ByLocation::class
					: \Bitrix\Sale\Delivery\Restrictions\ExcludeLocation::class;

				if (isset($params['LOCATION']))
				{
					$p['LOCATION'] = $params['LOCATION'];
				}

				if(!empty($p))
				{
					$fields = array(
						'SERVICE_ID' => $profileId,
						'SERVICE_TYPE' => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
						'PARAMS' => $p,
					);
				}

				break;
		}

		if($className && !empty($fields))
		{
			$className::save($fields);
		}
	}

	/**
	 * @param string $type
	 * @param array $fields
	 * @return array
	 */
	protected function 	getProfileDefaultParams($type, array $fields)
	{
		if(isset($fields["ACTIVE"]))
			$active = $fields["ACTIVE"];
		else
			$active = $this->active ? "Y" : "N";

		if(isset($fields["SORT"]))
			$sort = $fields["SORT"];
		else
			$sort = $this->sort;

		$result = array(
			"CODE" => "",
			"PARENT_ID" => $this->id,
			"NAME" => $fields["NAME"],
			"ACTIVE" => $active,
			"SORT" => $sort,
			"DESCRIPTION" => $fields["DESCRIPTION"],
			"CLASS_NAME" => '\Sale\Handlers\Delivery\AdditionalProfile',
			"CURRENCY" => $this->currency,
			"CONFIG" => array(
				"MAIN" => array(
					"PROFILE_TYPE" => $type,
					"NAME" => $fields["NAME"],
					"DESCRIPTION" => $fields["DESCRIPTION"]
				)
			)
		);

		if(!empty($fields["MODE"]))
			$result['CONFIG']['MAIN']["MODE"] = $fields["MODE"];

		if(!empty($fields['DEFAULT']['MAIN']))
			$result['CONFIG']['MAIN'] = array_merge($result['CONFIG']['MAIN'], $fields['DEFAULT']['MAIN']);

		return $result;
	}

	public function getAdminMessage()
	{
		$result = array();
		$message = '';

		if($this->isLicenseWrong())
			$message = Loc::getMessage('SALE_DLVRS_ADD_LICENSE_WRONG');
		elseif(!Location::isInstalled() && !empty($_REQUEST['ID']))
			$message = Loc::getMessage('SALE_DLVRS_ADD_LOC_INSTALL');

		if($message <> '')
		{
			$result = array(
				"DETAILS" => $message,
				"TYPE" => "ERROR",
				"HTML" => true
			);
		}

		return $result;
	}

	protected function isLicenseWrong()
	{
		return Option::get('sale', RestClient::WRONG_LICENSE_OPTION, 'N') == 'Y';
	}

	public function execAdminAction()
	{
		$result = new \Bitrix\Sale\Result();
		\Bitrix\Main\UI\Extension::load("main.core");
		Asset::getInstance()->addJs("/bitrix/js/sale/additional_delivery.js");
		Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="/bitrix/css/sale/additional_delivery.css">');
		Asset::getInstance()->addString('<script>
			if(top.BX)
			{
				BX.addCustomEvent(
					\'onSaleDeliveryRusPostShippingPointSelect\', 
					BX.Sale.Handler.Delivery.Additional.onRusPostShippingPointsSelect
				);
			}
		</script>');
		return $result;
	}

	public function getAdminAdditionalTabs()
	{
		self::install();

		ob_start();
		require_once(__DIR__.'/location/admin_tab.php');
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

	public static function install()
	{
		global $DB;

		if(!file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/css/sale/additional_delivery.css'))
		{
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/css",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/css/sale", true, true
			);
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/js",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/js/sale", true, true
			);
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/tools",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/sale", true, true
			);
		}

		$con = \Bitrix\Main\Application::getConnection();

		if(!$con->isTableExists('b_sale_hdale'))
		{
			$DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/db/".$con->getType()."/install.sql");
		}

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('sale', 'onSaleDeliveryExtraServicesClassNamesBuildList' , 'sale', '\Sale\Handlers\Delivery\AdditionalHandler', 'onSaleDeliveryExtraServicesClassNamesBuildList');
		$eventManager->registerEventHandler('sale', 'onSaleDeliveryTrackingClassNamesBuildList', 'sale', '\Sale\Handlers\Delivery\AdditionalHandler', 'onSaleDeliveryTrackingClassNamesBuildList');

		return parent::install();
	}

	public static function unInstall()
	{
		global $DB;

		if(file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/css/sale/additional_delivery.css'))
		{
			DeleteDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/css",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/css/sale"
			);
			DeleteDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/js",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/js/sale"
			);
			DeleteDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/tools",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/sale"
			);
		}

		$con = \Bitrix\Main\Application::getConnection();

		if(!$con->isTableExists('b_sale_hdale'))
		{
			$DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/delivery/additional/install/db/".$con->getType()."/uninstall.sql");
		}

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('sale', 'onSaleDeliveryExtraServicesClassNamesBuildList' , 'sale', '\Sale\Handlers\Delivery\AdditionalHandler', 'onSaleDeliveryExtraServicesClassNamesBuildList');
		$eventManager->unRegisterEventHandler('sale', 'onSaleDeliveryTrackingClassNamesBuildList' , 'sale', '\Sale\Handlers\Delivery\AdditionalHandler', 'onSaleDeliveryTrackingClassNamesBuildList');

		return parent::install();
	}

	public static function onSaleDeliveryTrackingClassNamesBuildList()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			array(
				'\Sale\Handlers\Delivery\AdditionalTracking' => '/bitrix/modules/sale/handlers/delivery/additional/tracking.php'
			),
			'sale'
		);
	}

	public function getEmbeddedExtraServicesList()
	{
		if($this->extraServicesList === null)
		{
			$this->extraServicesList = array();
			$client = new RestClient();
			$res = $client->getDeliveryExtraServices($this->serviceType);

			if($res->isSuccess())
				$this->extraServicesList = $res->getData();
		}

		return $this->extraServicesList;
	}

	public static function onSaleDeliveryExtraServicesClassNamesBuildList()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			array(
				'\Sale\Handlers\Delivery\Additional\ExtraServices\Insurance' => '/bitrix/modules/sale/handlers/delivery/additional/extra_services/insurance.php',
				'\Sale\Handlers\Delivery\Additional\ExtraServices\Lift' => '/bitrix/modules/sale/handlers/delivery/additional/extra_services/lift.php'
			),
			'sale'
		);
	}

	public function isCompatible(Shipment $shipment)
	{
		$client = new RestClient();
		return $client->isServerAlive();
	}

	public function getTrackingUrlTempl()
	{
		$config = \Sale\Handlers\Delivery\AdditionalProfile::extractConfigValues($this->getConfig());
		return !empty($config["MAIN"]["TRACKING_URL_TEMPL"]) ? $config["MAIN"]["TRACKING_URL_TEMPL"] : '';
	}

	/**
	 * @param Shipment $shipment
	 * @param string $serviceType
	 * @return array
	 */
	public static function getShipmentParams(Shipment $shipment, $serviceType)
	{
		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		/** @var \Bitrix\Sale\Order $order */
		$order = $shipmentCollection->getOrder();
		$props = $order->getPropertyCollection();
		$loc = $props->getDeliveryLocation();
		$locToInternalCode = !!$loc ? $loc->getValue() : "";
		$locFromRequest = array();
		$locToRequest = array();

		if(!empty($locToInternalCode))
			$locToRequest = self::getLocationForRequest($locToInternalCode);

		$shopLocation = \CSaleHelper::getShopLocation();

		if(!empty($shopLocation['CODE']))
			$locFromRequest = self::getLocationForRequest($shopLocation['CODE']);

		$result = array(
			"ITEMS" => array(),
			"LOCATION_FROM" => $locFromRequest['EXTERNAL_ID'] ?? null,
			"LOCATION_FROM_NAME" => $locFromRequest['NAME'] ??  null,
			"LOCATION_FROM_CODE" => (!empty($shopLocation['CODE'])) ? $shopLocation['CODE'] : '',
			"LOCATION_TO" => $locToRequest['EXTERNAL_ID'] ?? null,
			"LOCATION_TO_NAME" => $locToRequest['NAME'] ?? null,
			"LOCATION_TO_CODE" => $locToInternalCode,
			"LOCATION_TO_TYPES" => self::getLocationChainByTypes($locToInternalCode, LANGUAGE_ID)
		);

		if($address = $props->getAddress())
			$result["ADDRESS"] = $address->getValue();

		if($phone = $props->getPhone())
			$result["PHONE"] = $phone->getValue();

		if($payerName = $props->getPayerName())
			$result["PAYER_NAME"] = $payerName->getValue();

		if($serviceType == "RUSPOST" )
		{
			$zipFrom = \CSaleHelper::getShopLocationZIP();

			if($zipFrom <> '')
			{
				$result["ZIP_FROM"] = $zipFrom;
			}
			elseif(!empty($shopLocation['CODE']))
			{
				$extLoc = LocationHelper::getZipByLocation($shopLocation['CODE'], array('limit' => 1))->fetch();

				if(!empty($extLoc['XML_ID']))
					$result["ZIP_FROM"] = $extLoc['XML_ID'];
			}

			$zipTo = $props->getDeliveryLocationZip();
			$zipTo = !!$zipTo ? $zipTo->getValue() : "";

			if($zipTo <> '')
			{
				$result["ZIP_TO"] = $zipTo;
			}
			elseif(!empty($locToInternalCode))
			{
				$extLoc = LocationHelper::getZipByLocation($locToInternalCode, array('limit' => 1))->fetch();

				if(!empty($extLoc['XML_ID']))
					$result["ZIP_TO"] = $extLoc['XML_ID'];
			}
		}

		$price = 0;
		$weight = 0;

		/** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
		foreach($shipment->getShipmentItemCollection()->getShippableItems() as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();

			if(!$basketItem)
				continue;

			//$itemFieldValues = $basketItem->getFieldValues();
			$itemFieldValues = array(
				"PRICE" => $basketItem->getPrice(),
				"WEIGHT" => $basketItem->getWeight(),
				"CURRENCY" => $basketItem->getCurrency(),
				"QUANTITY" => $shipmentItem->getQuantity(),
				"DIMENSIONS" => $basketItem->getField("DIMENSIONS")
			);

			$price += $itemFieldValues["PRICE"] * $itemFieldValues["QUANTITY"];

			if(!empty($itemFieldValues["DIMENSIONS"]) && is_string($itemFieldValues["DIMENSIONS"]))
				$itemFieldValues["DIMENSIONS"] = unserialize($itemFieldValues["DIMENSIONS"], ['allowed_classes' => false]);

			$result["ITEMS"][] = $itemFieldValues;
		}

		//Extra services
		$esList = \Bitrix\Sale\Delivery\ExtraServices\Manager::getExtraServicesList($shipment->getDeliveryId(), false);

		if(!empty($esList))
		{
			$result['EXTRA_SERVICES'] = array();

			foreach($shipment->getExtraServices() as $esId => $esVal)
			{
				if(empty($esList[$esId]['CODE']))
					continue;

				if($esList[$esId]['CLASS_NAME'] == '\Bitrix\Sale\Delivery\ExtraServices\Checkbox' && $esVal != 'Y')
					continue;

				$result['EXTRA_SERVICES'][$esList[$esId]['CODE']] = $esVal;
			}
		}

		$delivery= Manager::getObjectById($shipment->getDeliveryId());
		$result['DELIVERY_SERVICE_CONFIG'] = $delivery ? $delivery->getConfigValues() : [];
		$result['WEIGHT'] = $shipment->getWeight();
		$result['PRICE'] = $price;
		$result['SHIPMENT_ID'] = $shipment->getId();
		$result['PRICE_DELIVERY'] = $shipment->getField('PRICE_DELIVERY');

		return $result;
	}

	/**
	 * @param $locationCode
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function getLocationForRequest($locationCode)
	{
		if($locationCode == '')
			return array();

		static $result = array();

		if(!isset($result[$locationCode]))
		{
			$externalId = Location::getExternalId($locationCode);
			$name = '';

			if($externalId <> '')
			{
				$dbRes = ExternalTable::getList(array(
					'filter' => array(
						'XML_ID' => $externalId,
						'SERVICE_ID' => Location::getExternalServiceId(),
						'LOCATION.NAME.LANGUAGE_ID' => 'ru'
					),
					'select' => array('NAME' => 'LOCATION.NAME.NAME')
				));

				if($rec = $dbRes->fetch())
					$name = $rec['NAME'];
			}

			$result[$locationCode] = array(
				'EXTERNAL_ID' => $externalId,
				'NAME' => $name
			);
		}

		return $result[$locationCode];
	}

	/**
	 * @param int $locationCode Location code.
	 * @param string $lang Language identifier.
	 * @return array Location components by type.
	 */
	protected static function getLocationChainByTypes($locationCode, $lang = LANGUAGE_ID)
	{
		if ($locationCode == '')
		{
			return [];
		}

		$res = LocationTable::getList([
			'filter' => [
				'=CODE' => $locationCode,
			],
			'select' => [
				'ID',
				'CODE',
				'LEFT_MARGIN',
				'RIGHT_MARGIN',
			]
		]);

		if (!$loc = $res->fetch())
		{
			return [];
		}

		$result = [];

		$res = LocationTable::getList(array(
			'filter' => [
				'<=LEFT_MARGIN' => $loc['LEFT_MARGIN'],
				'>=RIGHT_MARGIN' => $loc['RIGHT_MARGIN'],
				'NAME.LANGUAGE_ID' => $lang,
				'TYPE.NAME.LANGUAGE_ID' => $lang,
			],
			'select' => [
				'ID',
				'CODE',
				'LOCATION_NAME' => 'NAME.NAME',
				'TYPE_NAME' => 'TYPE.NAME.NAME',
				'TYPE_CODE' => 'TYPE.CODE',
			]
		));

		while ($locParent = $res->fetch())
		{
			if (!isset($result[$locParent['TYPE_CODE']]))
			{
				$result[$locParent['TYPE_CODE']] = [];
			}

			$result[$locParent['TYPE_CODE']][] = $locParent['LOCATION_NAME'];
		}

		return $result;
	}

	public function prepareFieldsForSaving(array $fields)
	{
		if(isset($fields['CONFIG']['MAIN']['SHIPPING_POINT']['NAME']))
			$fields['CONFIG']['MAIN']['SHIPPING_POINT']['NAME'] = htmlspecialcharsback($fields['CONFIG']['MAIN']['SHIPPING_POINT']['NAME']);

		if(isset($fields['CONFIG']['MAIN']['SHIPPING_POINT']['VALUE']))
			$fields['CONFIG']['MAIN']['SHIPPING_POINT']['VALUE'] = htmlspecialcharsback($fields['CONFIG']['MAIN']['SHIPPING_POINT']['VALUE']);

		if(isset($fields['CONFIG']['MAIN']['SHIPPING_POINT']['ADDITIONAL']))
			$fields['CONFIG']['MAIN']['SHIPPING_POINT']['ADDITIONAL'] = htmlspecialcharsback($fields['CONFIG']['MAIN']['SHIPPING_POINT']['ADDITIONAL']);

		return parent::prepareFieldsForSaving($fields);
	}

	/** @inheritDoc */
	public static function isHandlerCompatible()
	{
		if(!parent::isHandlerCompatible())
		{
			return false;
		}

		return in_array(
			\Bitrix\Sale\Delivery\Helper::getPortalZone(),
			['', 'ru', 'kz', 'by'],
			true
		);
	}
}
