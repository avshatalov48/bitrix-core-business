<?php


namespace Bitrix\Sale\Rest\Synchronization;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Result;

Loc::loadMessages(__FILE__);

class Manager
{
	protected $action;
	protected $handlerExecuted;

	protected $client;
	protected $clientOAuth;
	protected $clientId;
	protected $clientSecret;


	protected $serviceUrl;
	protected $oauthKey;
	protected $accessToken;
	protected $refreshToken;

	/** @var  HttpRequest */
	protected $request;

	protected static $instance = null;

	const B24_APP_GRANT_TYPE = 'refresh_token';

	const ACTION_UNDEFINED = 'undefined';
	const ACTION_DELETED = 'deleted';
	const ACTION_IMPORT = 'import';

	const END_POINT = '/bitrix/services/sale/synchronizer/push.php';

	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new static();
		}
		return self::$instance;
	}

	public function isActive()
	{
		return Option::get("sale", "config_external_is_active")=='Y';
	}
	public function activate()
	{
		Option::set("sale", "config_external_is_active", 'Y');
	}
	public function deactivate()
	{
		Option::set("sale", "config_external_is_active", 'N');
	}

	public function pushHandlerExecuted($name)
	{
		$this->handlerExecuted[$name] = true;
	}

	public function isExecutedHandler($name)
	{
		return is_set($this->handlerExecuted, $name);
	}

	public function checkDefaultSettings()
	{
		$result = new Result();

		$siteId='';
		$r = \CSite::GetList();
		while ($row = $r->fetch())
			if($row['ID']==$this->getDefaultSiteId())
				$siteId=$row['ID'];

		if($siteId=='')
			$result->addError(new Error(Loc::getMessage('MAN_ERROR_EMPTY_FIELD_SITE')));

		$deliverySystemId=0;
		foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $row)
			if($row['ID']==$this->getDefaultDeliverySystemId())
				$deliverySystemId = $row['ID'];
		if($deliverySystemId==0)
			$result->addError(new Error(Loc::getMessage('MAN_ERROR_EMPTY_FIELD_DELIVERY_SERVICES')));

		if(count(\Bitrix\Sale\PaySystem\Manager::getList(['filter'=>['ID'=>$this->getDefaultPaySystemId()]]))<=0)
			$result->addError(new Error(Loc::getMessage('MAN_ERROR_EMPTY_FIELD_PAY_SYSTEM')));
		if(count(\Bitrix\Sale\PersonType::getList(['filter'=>['ID'=>$this->getDefaultPersonTypeId()]]))<=0)
			$result->addError(new Error(Loc::getMessage('MAN_ERROR_EMPTY_FIELD_PERSON_TYPE')));
		if(count(\Bitrix\Sale\OrderStatus::getList(['filter'=>['ID'=>$this->getDefaultOrderStatusId()]]))<=0)
			$result->addError(new Error('MAN_ERROR_EMPTY_FIELD_ORDER_STATUS'));
		if(count(\Bitrix\Sale\DeliveryStatus::getList(['filter'=>['ID'=>$this->getDefaultDeliveryStatusId()]]))<=0)
			$result->addError(new Error('MAN_ERROR_EMPTY_FIELD_DELIVERY_STATUS'));

		$catalogList=[];
		if(\Bitrix\Main\Loader::includeModule('catalog'))
		{
			$catalogList = \Bitrix\Catalog\CatalogIblockTable::getList([
				'select' => ['IBLOCK_ID', 'IBLOCK.NAME'],
				'filter' => ['=IBLOCK.ACTIVE'=>'Y']
			])->fetchAll();
		}
		if(!count($catalogList)>0)
			$result->addError(new Error(Loc::getMessage('MAN_ERROR_CATALOGS')));

		return $result;
	}

	public function getClient()
	{
		if(!$this->client)
		{
			$this->client = new Client(
				$this->getClientId(),
				$this->getClientSecret(),
				$this->getSchemeServiceUrl().'://'.$this->getServiceUrl()
			);
		}
		return $this->client;
	}

	public function setSchemeServiceUrl($code)
	{
		Option::set("sale", "config_external_scheme_service_url", $code);
	}

	public function getSchemeServiceUrl()
	{
		return Option::get("sale", "config_external_scheme_service_url", false);
	}

	public function setServiceUrl($code)
	{
		Option::set("sale", "config_external_service_url", $code);
	}

	public function getServiceUrl()
	{
		return Option::get("sale", "config_external_service_url", false);
	}

	public function getClientId()
	{
		return 'app.5c05614270fdc0.60242739';
	}

	public function getClientSecret()
	{
		return 'cvdpAuyaHdC9ngJHctyRwu2xFtZamw85P3CWV8mIg7ESBfrVIa';
	}

	public function setAccessToken($accessToken)
	{
		Option::set("sale", "config_external_access_token", $accessToken);
	}
	public function getAccessToken()
	{
		return Option::get("sale", "config_external_access_token", false);
	}

	public function setRefreshToken($refreshToken)
	{
		Option::set("sale", "config_external_refresh_token", $refreshToken);
	}
	public function getRefreshToken()
	{
		return Option::get("sale", "config_external_refresh_token", false);
	}

	public function setOauthKey($key)
	{
		Option::set("sale", "config_external_oauth_key", $key);
	}
	public function getOauthKey()
	{
		return Option::get("sale", "config_external_oauth_key", "");
	}

	public function setAction($action)
	{
		$this->action = $action;
	}
	public function getAction()
	{
		return $this->action;
	}

	public function getDefaultDeliverySystemId()
	{
		return (int)Option::get("sale", "config_external_default_delivery_system_id");
	}
	public function setDefaultDeliverySystemId($code)
	{
		Option::set("sale", "config_external_default_delivery_system_id", $code);
	}

	public function getDefaultPaySystemId()
	{
		return (int)Option::get("sale", "config_external_default_pay_system_id");
	}
	public function setDefaultPaySystemId($code)
	{
		Option::set("sale", "config_external_default_pay_system_id", $code);
	}

	public function getDefaultSiteId()
	{
		return Option::get("sale", "config_external_default_site_id");
	}
	public function setDefaultSiteId($code)
	{
		Option::set("sale", "config_external_default_site_id", $code);
	}

	public function getDefaultPersonTypeId()
	{
		return (int)Option::get("sale", "config_external_default_person_type_id");
	}
	public function setDefaultPersonTypeId($code)
	{
		Option::set("sale", "config_external_default_person_type_id", $code);
	}

	public function getDefaultOrderStatusId()
	{
		return Option::get("sale", "config_external_default_order_status_id");
	}
	public function setDefaultOrderStatusId($code)
	{
		Option::set("sale", "config_external_default_order_status_id", $code);
	}

	public function getDefaultDeliveryStatusId()
	{
		return Option::get("sale", "config_external_default_delivery_status_id");
	}
	public function setDefaultDeliveryStatusId($code)
	{
		Option::set("sale", "config_external_default_delivery_status_id", $code);
	}

	public function getTradePlatformsXmlId($siteId)
	{
		$r = unserialize(Option::get("sale", "config_external_trade_platforms_xml_id"), ['allowed_classes' => false]);
		return $r[$siteId];
	}
	public function setTradePlatformsXmlId($siteId, $code)
	{
		$r = unserialize(Option::get("sale", "config_external_trade_platforms_xml_id"), ['allowed_classes' => false]);

		$r[$siteId] = $code;
		Option::set("sale", "config_external_trade_platforms_xml_id", serialize($r));
	}

	public function isMarked()
	{
		return Option::get("sale", "config_external_order_marked", 'N') == 'Y';
	}
	public function marked($code)
	{
		Option::set("sale", "config_external_order_marked", $code);
	}

}