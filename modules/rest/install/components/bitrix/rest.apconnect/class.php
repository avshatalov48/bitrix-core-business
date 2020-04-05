<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Rest\APAuth\PasswordTable;

class CAPConnectComponent extends CBitrixComponent
{
	const SESSION_KEY = 'APCONNECT_CLIENT_INFO';
	const PATH_AP_MANAGE = '/marketplace/hook/ap/';

	protected static $presetPermission = array(
		'ap' => array('crm', 'telephony', 'imopenlines'),
	);

	protected $errors;
	protected $clientInfo = null;
	protected $clientAccess = array();

	public function __construct($component = null)
	{
		$this->errors = new ErrorCollection();

		parent::__construct($component);
	}

	/**
	 * Load language file.
	 */
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	/**
	 * Prepare Component Params.
	 *
	 * @param array $params Component parameters.
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		global $USER;

		$params["USER_ID"] = intval($params["USER_ID"]);
		$params["CLIENT_ID"] = trim($params["CLIENT_ID"]);
		$params["CLIENT_STATE"] = trim($params["CLIENT_STATE"]);

		if($params["USER_ID"] <= 0)
		{
			$params["USER_ID"] = $USER->GetID();
		}

		return $params;
	}

	protected function getConnectionData($password)
	{
		return array(
			'ENDPOINT' => \CRestUtil::getWebhookEndpoint($password, $this->arParams['USER_ID']),
		);
	}

	/**
	 * Process incoming request
	 * @return void
	 */
	protected function processRequest()
	{
		if($this->arParams['USER_ID'] <= 0)
		{
			throw new SystemException(Loc::getMessage('APC_NOT_AUTHORIZED'));
		}

		if(strlen($this->arParams['CLIENT_ID']) <= 0)
		{
			throw new SystemException(Loc::getMessage('APC_NO_CLIENT'));
		}

		$request = Context::getCurrent()->getRequest();

		if(isset($request['preset']) && array_key_exists($request['preset'], static::$presetPermission))
		{
			$this->clientAccess = static::$presetPermission[$request['preset']];
		}

		if($request->isPost() && check_bitrix_sessid() && !empty($_SESSION[static::SESSION_KEY][$this->arParams['CLIENT_ID']]))
		{
			$clientInfo = $_SESSION[static::SESSION_KEY][$this->arParams['CLIENT_ID']];

			$password = PasswordTable::createPassword($this->arParams['USER_ID'], $this->clientAccess, $clientInfo['TITLE']);
			if($password != false)
			{
				$connection = $this->getConnectionData($password);

				$client = \CBitrix24NetPortalTransport::init();
				$result = $client->call(
					'client.authorize',
					array(
						'CLIENT_ID' => $clientInfo['CLIENT_ID'],
						'CONNECTION' => $connection,
					)
				);

				if(!$result['result'])
				{
					if($result['error'])
					{
						throw new SystemException($result['error'].': '.$result['error_description']);
					}
					else
					{
						throw new SystemException(Loc::getMessage('APC_PASSWORD_NOT_REGISTERD'));
					}
				}

				$url = $clientInfo['REDIRECT_URI'];

				$url .= (strpos($url, '?') !== false ? '&' : '?').http_build_query(array(
					'apcode' => $result['result']['apcode'],
					'state' => $this->arParams['CLIENT_STATE'],
				));

				unset($_SESSION[static::SESSION_KEY]);

				LocalRedirect($url, true);
			}
			else
			{
				$this->errors[] = new Error(Loc::getMessage('APC_PASSWORD_NOT_CREATED'));
			}
		}

	}


	/**
	 * Check Required Modules
	 *
	 * @throws Exception
	 */
	protected function checkModules()
	{
		if(!Loader::includeModule('rest'))
		{
			throw new SystemException(Loc::getMessage('APC_REST_MODULE_NOT_INSTALLED'));
		}

		if(!Loader::includeModule('socialservices'))
		{
			throw new SystemException(Loc::getMessage('APC_SOCIALSERVICES_MODULE_NOT_INSTALLED'));
		}
	}

	/**
	 * Get main data - client info
	 *
	 * @throws SystemException
	 *
	 * @return void
	 */
	protected function prepareData()
	{
		$query = \CBitrix24NetPortalTransport::init();
		if(!$query)
		{
			throw new SystemException(Loc::getMessage('APC_TRANSPORT_INITIALIZE_FAILED'));
		}

		$clientId = $this->arParams['CLIENT_ID'];

		$queryResult = $query->call('client.verify', array(
			'CLIENT_ID' => $clientId,
		));

		if(!$queryResult)
		{
			throw new SystemException(Loc::getMessage('APC_VERIFY_REQUEST_FAILED'));
		}

		if($queryResult['error'])
		{
			switch($queryResult['error'])
			{
				case 'WRONG_CLIENT':
					$errorText = Loc::getMessage('APC_ERROR_WRONG_CLIENT');
					break;
				case 'CLIENT_TYPE_NOT_ALLOWED':
					$errorText = Loc::getMessage('APC_ERROR_CLIENT_TYPE_NOT_ALLOWED');
					break;
				default:
					$errorText = $queryResult['error'].(!empty($queryResult['error_description']) ? ': '.$queryResult['error_description'] : '');
			}

			throw new SystemException($errorText);
		}

		$this->clientInfo = $queryResult['result'];

		$_SESSION[static::SESSION_KEY] = array($clientId => $this->clientInfo);
	}


	/**
	 * Prepare data to render
	 *
	 * @return void
	 */
	protected function formatResult()
	{
		$this->arResult['CLIENT_INFO'] = $this->clientInfo;
		$this->arResult['ERRORS'] = $this->errors;

		$this->arResult['AP_MANAGE_URL'] = static::PATH_AP_MANAGE;

		$this->arResult['CLIENT_ACCESS'] = $this->clientAccess;
	}

	/**
	 * Extract data from cache
	 * @return bool
	 */
	protected function extractDataFromCache()
	{
		return false;
	}

	protected function putDataToCache()
	{
	}

	protected function abortDataCache()
	{
	}

	public function executeComponent()
	{
		global $APPLICATION;

		$APPLICATION->SetTitle(Loc::getMessage('APC_TITLE'));

		try
		{
			$this->checkModules();
			$this->processRequest();
			if(!$this->extractDataFromCache())
			{
				$this->prepareData();
				$this->formatResult();
				$this->setResultCacheKeys(array());
				$this->includeComponentTemplate();
				$this->putDataToCache();
			}
		}
		catch(SystemException $e)
		{
			$this->abortDataCache();
			ShowError($e->getMessage());
		}
	}
}