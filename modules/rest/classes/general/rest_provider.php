<?
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Resolver;
use Bitrix\Main\Engine\Router;
use Bitrix\Main\HttpRequest;
use Bitrix\Rest\Engine\ScopeManager;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Rest\RestException;
use Bitrix\Rest\AccessException;
use Bitrix\OAuth;

class CRestProvider
	extends \IRestService
{
	const ERROR_BATCH_LENGTH_EXCEEDED = 'ERROR_BATCH_LENGTH_EXCEEDED';
	const ERROR_BATCH_METHOD_NOT_ALLOWED = 'ERROR_BATCH_METHOD_NOT_ALLOWED';

	// default license shown instead of absent or unknown
	const LICENSE_DEFAULT = "project";

	// controller group id => rest license id
	protected static $licenseList = array(
		"project" => "project",
		"corporation" => "corporation",
		"company" => "company",
		"company2" => "company2",
		"company3" => "company3",
		"team" => "team",
		"demo" => "demo",
		"nfr" => "nfr",
		"tf" => "tf",
		"crm" => "crm",
		"tasks" => "tasks",
		"basic" => "basic",
		"start" => "start",
		"std" => "std",
		"pro" => "pro",
		"ent" => "ent",
		"pro100" => "pro100",
		"ent250" => "ent250",
		"ent500" => "ent500",
		"ent1000" => "ent1000",
		"ent2000" => "ent2000",
		"ent3000" => "ent3000",
		"ent4000" => "ent4000",
		"ent5000" => "ent5000",
		"ent6000" => "ent6000",
		"ent7000" => "ent7000",
		"ent8000" => "ent8000",
		"ent9000" => "ent9000",
		"ent10000" => "ent10000",
	);

	protected static $arApp = null;
	protected static $arScope = null;
	protected static $arMethodsList = null;

	public function getDescription()
	{
		if(!is_array(self::$arMethodsList))
		{
			$globalMethods = array(
				\CRestUtil::GLOBAL_SCOPE => array(
					'batch' => array(__CLASS__, 'methodsBatch'),

					'scope' => array(__CLASS__, 'scopeList'),
					'methods' => array(__CLASS__, 'methodsList'),
					'method.get' => array(__CLASS__, 'getMethod'),

					'server.time' => array(__CLASS__, 'getServerTime'),
				),
			);

			$ownMethods = array(
				\CRestUtil::GLOBAL_SCOPE => array(
					'app.option.get' => array(__CLASS__, 'appOptionGet'),
					'app.option.set' => array(__CLASS__, 'appOptionSet'),
					'user.option.get' => array(__CLASS__, 'userOptionGet'),
					'user.option.set' => array(__CLASS__, 'userOptionSet'),

					\CRestUtil::EVENTS => array(
						'OnAppUninstall' => array(
							'rest',
							'OnRestAppDelete',
							array(__CLASS__, 'OnAppEvent'),
							array(
								"sendAuth" => false,
								"category" => \Bitrix\Rest\Sqs::CATEGORY_IMPORTANT,
							)
						),
						'OnAppInstall' => array(
							'rest',
							'OnRestAppInstall',
							array(__CLASS__, 'OnAppEvent'),
							array(
								"sendRefreshToken" => true,
								"category" => \Bitrix\Rest\Sqs::CATEGORY_IMPORTANT,
							)
						),
						'OnAppUpdate' => array(
							'rest',
							'OnRestAppUpdate',
							array(__CLASS__, 'OnAppEvent'),
							array(
								"sendRefreshToken" => true,
								"category" => \Bitrix\Rest\Sqs::CATEGORY_IMPORTANT,
							)
						),
						'OnAppPayment' => array(
							'bitrix24',
							'OnAfterAppPaid',
							array(__CLASS__, 'OnAppPayment'),
							array(
								"category" => \Bitrix\Rest\Sqs::CATEGORY_IMPORTANT,
							)
						),
						'OnSubscriptionRenew' => [
							'rest',
							'onAfterSubscriptionRenew',
							[
								__CLASS__,
								'onSubscriptionRenew',
							],
							[
								'sendRefreshToken' => true,
							],
						],
						'OnAppTest' => array(
							'rest',
							'OnRestAppTest',
							array(__CLASS__, 'OnAppEvent'),
							array(
								"sendRefreshToken" => true,
							)
						),
						'OnAppMethodConfirm' => array(
							'rest',
							'OnRestAppMethodConfirm',
							array(__CLASS__, 'OnAppEvent'),
							array(
								"sendAuth" => false,
								"category" => \Bitrix\Rest\Sqs::CATEGORY_IMPORTANT,
							)
						),
					),
					\CRestUtil::PLACEMENTS => array(
						\CRestUtil::PLACEMENT_APP_URI => array(
							'max_count' => 1
						)
					)
				),
			);

			if(!\Bitrix\Main\ModuleManager::isModuleInstalled('oauth'))
			{
				$ownMethods[\CRestUtil::GLOBAL_SCOPE]['app.info'] = array(__CLASS__, 'appInfo');
				$ownMethods[\CRestUtil::GLOBAL_SCOPE]['feature.get'] = array(__CLASS__, 'getFeature');
			}

			$arDescription = array();

			foreach(GetModuleEvents("rest", "OnRestServiceBuildDescription", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent);
				if(is_array($res))
				{
					$arDescription = array_merge_recursive($res, $arDescription);
				}
			}

			self::$arMethodsList = array_merge_recursive(
				$globalMethods,
				$ownMethods,
				$arDescription
			);

			if(!array_key_exists('profile', self::$arMethodsList[\CRestUtil::GLOBAL_SCOPE]))
			{
				self::$arMethodsList[\CRestUtil::GLOBAL_SCOPE]['profile'] = array(
					'callback' => array(__CLASS__, 'getProfile'),
					'options' => array(),
				);
			}

			array_change_key_case(self::$arMethodsList, CASE_LOWER);

			foreach(self::$arMethodsList as $scope => $arScopeMethods)
			{
				self::$arMethodsList[$scope] = array_change_key_case(self::$arMethodsList[$scope], CASE_LOWER);
				if(
					array_key_exists(\CRestUtil::EVENTS, self::$arMethodsList[$scope])
					&& is_array(self::$arMethodsList[$scope][\CRestUtil::EVENTS])
				)
				{
					self::$arMethodsList[$scope][\CRestUtil::EVENTS] = array_change_key_case(self::$arMethodsList[$scope][\CRestUtil::EVENTS], CASE_UPPER);
				}
				if(
					array_key_exists(\CRestUtil::PLACEMENTS, self::$arMethodsList[$scope])
					&& is_array(self::$arMethodsList[$scope][\CRestUtil::PLACEMENTS])
				)
				{
					self::$arMethodsList[$scope][\CRestUtil::PLACEMENTS] = array_change_key_case(self::$arMethodsList[$scope][\CRestUtil::PLACEMENTS], CASE_UPPER);
				}
			}
		}

		return self::$arMethodsList;
	}

	public static function getProfile($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$USER->isAuthorized())
		{
			throw new \Bitrix\Rest\AccessException("User authorization required");
		}

		$dbRes = CUser::getById($USER->getId());
		$userInfo = $dbRes->fetch();

		$result = array();

		if($userInfo['ACTIVE'] == 'Y')
		{
			$result = array(
				'ID' => $userInfo['ID'],
				'ADMIN' => \CRestUtil::isAdmin(),
				'NAME' => $userInfo['NAME'],
				'LAST_NAME' => $userInfo['LAST_NAME'],
				'PERSONAL_GENDER' => $userInfo['PERSONAL_GENDER'],
			);

			if($userInfo['PERSONAL_PHOTO'] > 0)
			{
				$result['PERSONAL_PHOTO'] = \CRestUtil::GetFile($userInfo["PERSONAL_PHOTO"]);
			}

			$result['TIME_ZONE'] = \CTimeZone::IsAutoTimeZone($userInfo['AUTO_TIME_ZONE']) === true
				? ''
				: $userInfo['TIME_ZONE'];
			$result['TIME_ZONE_OFFSET'] = \CTimeZone::GetOffset($USER->getId()) + date('Z');

			$securityState = array(
				"ID" => $result['ID'],
				"NAME" => $result['NAME'],
				"LAST_NAME" => $result['LAST_NAME'],
			);

			$server->setSecurityState($securityState);
		}

		return $result;
	}


	public static function methodsBatch($arQuery, $start, \CRestServer $server)
	{
		$arQuery = array_change_key_case($arQuery, CASE_UPPER);

		$bHalt = (isset($arQuery['HALT'])) ? ((bool) $arQuery['HALT']) : false;

		$arResult = array(
			'result' => array(),
			'next' => array(),
			'total' => array(),
			'time' => array(),
			'error' => array(),
		);
		if(isset($arQuery['CMD']))
		{
			$cnt = 0;

			$authData = $server->getAuth();
			foreach ($arQuery['CMD'] as $key => $call)
			{
				if(($cnt++) < \CRestUtil::BATCH_MAX_LENGTH)
				{
					if (!is_string($call))
					{
						continue;
					}
					$queryData = parse_url($call);

					$method = $queryData['path'];
					$query = $queryData['query'];

					$arParams = \CRestUtil::ParseBatchQuery($query, $arResult);

					if($method === \CRestUtil::METHOD_DOWNLOAD || $method === \CRestUtil::METHOD_UPLOAD)
					{
						$res = array('error' => self::ERROR_BATCH_METHOD_NOT_ALLOWED, 'error_description' => 'Method is not allowed for batch usage');
					}
					else
					{
						if(is_array($authData))
						{
							foreach($authData as $authParam => $authValue)
							{
								$arParams[$authParam] = $authValue;
							}
						}

						$methods = [ToLower($method), $method];

						// try lowercase first, then original
						foreach ($methods as $restMethod)
						{
							$pseudoServer = new \CRestServerBatchItem([
								'CLASS' => __CLASS__,
								'METHOD' => $restMethod,
								'QUERY' => $arParams
							], false);
							$pseudoServer->setApplicationId($server->getClientId());
							$pseudoServer->setAuthKeys(array_keys($authData));
							$pseudoServer->setAuthData($server->getAuthData());
							$pseudoServer->setAuthType($server->getAuthType());
							$res = $pseudoServer->process();

							unset($pseudoServer);

							// try original controller name if lower is not found
							if (is_array($res) && !empty($res['error']) && $res['error'] === 'ERROR_METHOD_NOT_FOUND')
							{
								continue;
							}

							// output result
							break;
						}
					}
				}
				else
				{

					$res = array('error' => self::ERROR_BATCH_LENGTH_EXCEEDED, 'error_description' => 'Max batch length exceeded');
				}

				if(is_array($res))
				{
					if(isset($res['error']))
					{
						$res['error'] = $res;
					}

					foreach ($res as $k=>$v)
					{
						$arResult[$k][$key] = $v;
					}
				}

				if(isset($res['error']) && $res['error'] && $bHalt)
				{
					break;
				}
			}
		}

		return array(
			'result' => $arResult['result'],
			'result_error' => $arResult['error'],
			'result_total' => $arResult['total'],
			'result_next' => $arResult['next'],
			'result_time' => $arResult['time'],
		);
	}

	public static function scopeList($arQuery, $n, \CRestServer $server)
	{
		$arQuery = array_change_key_case($arQuery, CASE_UPPER);

		if($arQuery['FULL'] == true)
		{
			$arScope = \Bitrix\Rest\Engine\ScopeManager::getInstance()->listScope();
		}
		else
		{
			$arScope = self::getScope($server);
		}

		return $arScope;
	}

	public static function methodsList($arQuery, $n, \CRestServer $server)
	{
		$arMethods = $server->getServiceDescription();

		$arScope = array(\CRestUtil::GLOBAL_SCOPE);
		$arResult = array();

		$arQuery = array_change_key_case($arQuery, CASE_UPPER);

		if(isset($arQuery['SCOPE']))
		{
			if($arQuery['SCOPE'] != '')
				$arScope = array($arQuery['SCOPE']);
		}
		elseif($arQuery['FULL'] == true)
		{
			$arScope = array_keys($arMethods);
		}
		else
		{
			$arScope = self::getScope($server);
			$arScope[] = \CRestUtil::GLOBAL_SCOPE;
		}

		foreach ($arMethods as $scope => $arScopeMethods)
		{
			if(in_array($scope, $arScope))
			{
				unset($arScopeMethods[\CRestUtil::METHOD_DOWNLOAD]);
				unset($arScopeMethods[\CRestUtil::METHOD_UPLOAD]);
				unset($arScopeMethods[\CRestUtil::EVENTS]);
				unset($arScopeMethods[\CRestUtil::PLACEMENTS]);

				foreach($arScopeMethods as $method => $methodDesc)
				{
					if(isset($methodDesc["options"]) && $methodDesc["options"]["private"] === true)
					{
						unset($arScopeMethods[$method]);
					}
				}

				$arResult = array_merge($arResult, array_keys($arScopeMethods));
			}
		}

		return $arResult;
	}

	public static function getMethod($query, $n, \CRestServer $server): array
	{
		$result = [
			'isExisting' => false,
			'isAvailable' => false,
		];
		$name = $query['name'];
		if (!empty($name))
		{
			$currentScope = self::getScope($server);
			$currentScope[] = \CRestUtil::GLOBAL_SCOPE;
			$cache = Cache::createInstance();
			if ($cache->initCache(
				ScopeManager::CACHE_TIME,
				'info' . md5($name . implode('|', $currentScope)),
				ScopeManager::CACHE_DIR . 'method/'
			))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$method = ScopeManager::getInstance()->getMethodInfo($name);

				$arMethods = $server->getServiceDescription();
				foreach ($arMethods as $scope => $methodList)
				{
					if (!empty($methodList[$name]))
					{
						if (in_array($scope, $currentScope, true))
						{
							$result['isAvailable'] = true;
						}
						$result['isExisting'] = true;
					}
				}

				if (!$result['isExisting'])
				{
					$request = new HttpRequest(
						Context::getCurrent()->getServer(),
						[
							'action' => $method['method'],
						],
						[],
						[],
						[]
					);
					$router = new Router($request);

					/** @var Controller $controller */
					[$controller, $action] = Resolver::getControllerAndAction(
						$router->getVendor(),
						$router->getModule(),
						$router->getAction(),
						Controller::SCOPE_REST
					);
					if ($controller)
					{
						if (in_array($method['scope'], $currentScope, true))
						{
							$result['isAvailable'] = true;
						}
						$result['isExisting'] = true;
					}
				}

				$cache->endDataCache($result);
			}
		}

		return $result;
	}

	public static function appInfo($params, $n, \CRestServer $server)
	{
		$licensePrevious = '';
		if(\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$result = self::getBitrix24LicenseName();
			$license = $result['LICENSE'];

			if ($result['TYPE'] == 'demo')
			{
				$result = self::getBitrix24LicenseName(\CBitrix24::LICENSE_TYPE_PREVIOUS);
				$licensePrevious = $result['LICENSE'];
			}
		}
		else
		{
			$license = LANGUAGE_ID.'_selfhosted';
		}

		if($server->getClientId())
		{
			$arApp = self::getApp($server);

			$info = \Bitrix\Rest\AppTable::getAppStatusInfo($arApp, '');

			$res = array(
				'ID' => $arApp['ID'],
				'CODE' => $arApp['CODE'],
				'VERSION' => intval($arApp['VERSION']),
				'STATUS' => $info['STATUS'],
				'INSTALLED' => $arApp['INSTALLED'] == \Bitrix\Rest\AppTable::INSTALLED,
				'PAYMENT_EXPIRED' => $info['PAYMENT_EXPIRED'],
				'DAYS' => $info['DAYS_LEFT'],
				'LANGUAGE_ID' => \CRestUtil::getLanguage(),
				'LICENSE' => $license,
			);
			if ($licensePrevious)
			{
				$res['LICENSE_PREVIOUS'] = $licensePrevious;
			}
			if (CModule::IncludeModule('bitrix24'))
			{
				$res['LICENSE_TYPE'] = CBitrix24::getLicenseType();
				$res['LICENSE_FAMILY'] = CBitrix24::getLicenseFamily();
			}

			$server->setSecurityState($res);
		}
		elseif($server->getPasswordId())
		{
			$res = array(
				'SCOPE' => static::getScope($server),
				'LICENSE' => $license,
			);
		}
		else
		{
			throw new AccessException("Application context required");
		}

		foreach(GetModuleEvents('rest', 'OnRestAppInfo', true) as $event)
		{
			$eventData = ExecuteModuleEventEx($event, array($server, &$res));
			if(is_array($eventData))
			{
				if(!isset($res['ADDITIONAL']))
				{
					$res['ADDITIONAL'] = array();
				}

				$res['ADDITIONAL'] = array_merge($res['ADDITIONAL'], $eventData);
			}
		}

		return $res;
	}

	/**
	 * Return feature information.
	 *
	 * @param $params
	 * @param $n
	 * @param CRestServer $server
	 *
	 * @return array
	 *
	 * @throws RestException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getFeature($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);
		$result = [
			'value' => '',
		];
		if (empty($params['CODE']))
		{
			throw new RestException(
				'CODE can\'t be empty',
				'CODE_EMPTY',
				\CRestServer::STATUS_WRONG_REQUEST
			);
		}

		if(\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24'))
		{
			$result['value'] = \Bitrix\Bitrix24\Feature::isFeatureEnabled($params['CODE']) ? 'Y' : 'N';
		}
		else
		{
			foreach (GetModuleEvents('rest', 'onRestGetFeature', true) as $event)
			{
				$eventData = ExecuteModuleEventEx(
					$event,
					[
						$params['CODE'],
					]
				);
				if (is_array($eventData))
				{
					if ($eventData['value'] === true || $eventData['value'] === 'Y')
					{
						$result['value'] = 'Y';
					}
					else
					{
						$result['value'] = 'N';
					}
				}
			}

			if (empty($result['value']))
			{
				$result['value'] = LANGUAGE_ID . '_selfhosted';
			}
		}

		return $result;
	}

	/**
	 * Gets application option values
	 *
	 * @param array $params array([option => option_name])
	 * @param int $n Standard pagination param
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return array|mixed|null|string
	 *
	 * @throws AccessException
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function appOptionGet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!$USER->IsAuthorized())
		{
			throw new AccessException("User authorization required");
		}

		$appOptions = Option::get("rest", "options_".$server->getClientId(), "");

		if($appOptions <> '')
		{
			$appOptions = unserialize($appOptions, ['allowed_classes' => false]);
		}
		else
		{
			$appOptions = array();
		}

		if(isset($params['option']))
		{
			return isset($appOptions[$params['option']]) ? $appOptions[$params['option']] : null;
		}
		else
		{
			return $appOptions;
		}
	}

	/**
	 * Sets application options values
	 *
	 * @param array $params array(option_name => option_value) || array(options => array(option_name => option_value,....))
	 * @param int $n Standard pagination param
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return true
	 *
	 * @throws AccessException
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function appOptionSet($params, $n, \CRestServer $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!isset($params["options"]))
		{
			$params['options'] = $params;
		}

		if(count($params['options']) <= 0)
		{
			throw new ArgumentNullException('options');
		}

		if(\CRestUtil::isAdmin())
		{
			$appOptions = Option::get("rest", "options_".$server->getClientId(), "");
			if($appOptions <> '')
			{
				$appOptions = unserialize($appOptions, ['allowed_classes' => false]);
			}
			else
			{
				$appOptions = array();
			}

			foreach($params['options'] as $key => $value)
			{
				$appOptions[$key] = $value;
			}

			Option::set('rest', "options_".$server->getClientId(), serialize($appOptions));
		}
		else
		{
			throw new AccessException("Administrator authorization required");
		}

		return true;
	}

	/**
	 * Gets user option values for application
	 *
	 * @param array $params array([option => option_name])
	 * @param int $n Standard pagination param
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return array|mixed|null|string
	 *
	 * @throws AccessException
	 */
	public static function userOptionGet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!$USER->IsAuthorized())
		{
			throw new AccessException("User authorization required");
		}

		$userOptions = \CUserOptions::GetOption("app_options", "options_".$server->getClientId(), array());

		if(isset($params['option']))
		{
			return isset($userOptions[$params['option']]) ? $userOptions[$params['option']] : null;
		}
		else
		{
			return $userOptions;
		}
	}

	/**
	 * Sets user options values for application
	 *
	 * @param array $params array(option_name => option_value) || array(options => array(option_name => option_value,....))
	 * @param int $n Standard pagination param.
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return bool
	 *
	 * @throws AccessException
	 * @throws ArgumentNullException
	 */
	public static function userOptionSet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!$USER->IsAuthorized())
		{
			throw new AccessException("User authorization required");
		}

		if(!isset($params["options"]))
		{
			$params['options'] = $params;
		}

		if(count($params['options']) <= 0)
		{
			throw new ArgumentNullException('options');
		}

		$userOptions = \CUserOptions::GetOption("app_options", "options_".$server->getClientId(), array());

		foreach($params['options'] as $key => $value)
		{
			$userOptions[$key] = $value;
		}

		\CUserOptions::SetOption("app_options", "options_".$server->getClientId(), $userOptions);

		return true;
	}

	public static function getServerTime($params)
	{
		return date('c', time());
	}

	public static function OnAppEvent($arParams, $arHandler)
	{
		$arEventFields = $arParams[0];
		if($arEventFields['APP_ID'] == $arHandler['APP_ID'] || $arEventFields['APP_ID'] == $arHandler['APP_CODE'])
		{
			$arEventFields["LANGUAGE_ID"] = \CRestUtil::getLanguage();

			unset($arEventFields['APP_ID']);
			return $arEventFields;
		}
		else
		{
			throw new Exception('Wrong app!');
		}
	}

	public static function OnAppPayment($arParams, $arHandler)
	{
		if($arParams[0] == $arHandler['APP_ID'])
		{
			$app = \Bitrix\Rest\AppTable::getByClientId($arHandler['APP_ID']);
			if($app)
			{
				$info = \Bitrix\Rest\AppTable::getAppStatusInfo($app, '');

				return array(
					'CODE' => $app['CODE'],
					'VERSION' => intval($app['VERSION']),
					'STATUS' => $info['STATUS'],
					'PAYMENT_EXPIRED' => $info['PAYMENT_EXPIRED'],
					'DAYS' => $info['DAYS_LEFT']
				);
			}
		}

		throw new Exception('Wrong app!');
	}

	private static function getBitrix24LicenseName($licenseType = \CBitrix24::LICENSE_TYPE_CURRENT)
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			return null;
		}

		$licenseOption = ($licenseType == \CBitrix24::LICENSE_TYPE_CURRENT? "~controller_group_name": "~prev_controller_group_name");

		$licenseInfo = COption::GetOptionString("main", $licenseOption);

		[$lang, $licenseName, $additional] = explode("_", $licenseInfo, 3);

		if(!array_key_exists($licenseName, static::$licenseList))
		{
			$licenseName = static::LICENSE_DEFAULT;
		}

		if(!$lang)
		{
			$lang = LANGUAGE_ID;
		}

		return [
			'LANG' => $lang,
			'TYPE' => static::$licenseList[$licenseName],
			'LICENSE' => $lang."_".static::$licenseList[$licenseName]
		];
	}

	protected static function getApp(\CRestServer $server)
	{
		if(self::$arApp == null)
		{
			if(CModule::IncludeModule('oauth'))
			{
				$client = OAuth\Base::instance($server->getClientId());

				if($client)
				{
					self::$arApp = $client->getClient();

					if(is_array(self::$arApp) && is_array(self::$arApp['SCOPE']))
					{
						self::$arApp['SCOPE'] = implode(',', self::$arApp['SCOPE']);
					}
				}
			}
			elseif($server->getClientId())
			{
				self::$arApp = \Bitrix\Rest\AppTable::getByClientId($server->getClientId());
			}
			else
			{
				throw new AccessException("Application context required");
			}
		}

		return self::$arApp;
	}

	protected static function getScope(\CRestServer $server)
	{
		return $server->getAuthScope();
	}
}
?>