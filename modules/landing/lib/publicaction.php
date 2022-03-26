<?php
namespace Bitrix\Landing;

use \Bitrix\Rest\AppTable;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class PublicAction
{
	/**
	 * Scope for REST (default commands).
	 */
	const REST_SCOPE_DEFAULT = 'landing';

	/**
	 * Scope for REST (cloud repo commands).
	 */
	const REST_SCOPE_CLOUD = 'landing_cloud';

	/**
	 * Code indicating used blocks in REST statistics.
	 */
	public const REST_USAGE_TYPE_BLOCK = 'blocks';

	/**
	 * Code indicating used pages in REST statistics.
	 */
	public const REST_USAGE_TYPE_PAGE = 'pages';

	/**
	 * REST application.
	 * @var array
	 */
	protected static $restApp = null;

	/**
	 * Raw data from waf.
	 * @var mixed
	 */
	protected static $rawData = null;

	/**
	 * Get full namespace for public classes.
	 * @return string
	 */
	protected static function getNamespacePublicClasses()
	{
		return __NAMESPACE__ . '\\PublicAction';
	}

	/**
	 * Get info about method - class/method/params.
	 * @param string $action Full name of action (\Namespace\Class::method).
	 * @param array $data Array of data.
	 * @return array
	 * @throws \ReflectionException
	 */
	protected static function getMethodInfo($action, $data = array())
	{
		$info = array();

		// if action exist and is callable
		if ($action && mb_strpos($action, '::'))
		{
			$actionOriginal = $action;
			$action = self::getNamespacePublicClasses() . '\\' . $action;
			if (is_callable(explode('::', $action)))
			{
				[$class, $method] = explode('::', $action);
				$info = array(
					'action' => $actionOriginal,
					'class' => $class,
					'method' => $method,
					'params_init' => array(),
					'params_missing' => array()
				);
				// parse func params
				$reflection = new \ReflectionMethod($class, $method);
				$static = $reflection->getStaticVariables();
				$mixedParams = isset($static['mixedParams'])
								? $static['mixedParams']
								: [];
				foreach ($reflection->getParameters() as $param)
				{
					$name = $param->getName();
					if (isset($data[$name]))
					{
						if (!in_array($name, $mixedParams))
						{
							if (
								$param->isArray() &&
								!is_array($data[$name])
								||
								!$param->isArray() &&
								is_array($data[$name])

							)
							{
								throw new \Bitrix\Main\ArgumentTypeException(
									$name
								);
							}
						}
						$info['params_init'][$name] = $data[$name];
					}
					elseif ($param->isDefaultValueAvailable())
					{
						$info['params_init'][$name] = $param->getDefaultValue();
					}
					else
					{
						$info['params_missing'][] = $name;
					}
				}
			}
		}

		return $info;
	}

	/**
	 * Returns true if current user out of the extranet.
	 * @return bool
	 */
	protected static function checkForExtranet(): bool
	{
		if (\Bitrix\Landing\Manager::isAdmin())
		{
			return true;
		}
		if (\Bitrix\Main\Loader::includeModule('extranet'))
		{
			return \CExtranet::isIntranetUser(
				\CExtranet::getExtranetSiteID(),
				\Bitrix\Landing\Manager::getUserId()
			);
		}

		return true;
	}

	/**
	 * Processing the AJAX/REST action.
	 * @param string $action Action name.
	 * @param mixed $data Data.
	 * @param boolean $isRest Is rest call.
	 * @return array|null
	 * @throws \ReflectionException
	 */
	protected static function actionProcessing($action, $data, $isRest = false)
	{
		if (!is_array($data))
		{
			$data = array();
		}

		if (isset($data['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope($data['scope']);
		}

		if (!$isRest && (!defined('BX_UTF') || BX_UTF !== true))
		{
			$data = Manager::getApplication()->convertCharsetArray(
				$data, 'UTF-8', SITE_CHARSET
			);
		}

		$error = new Error;

		// not for guest
		if (!Manager::getUserId() || !self::checkForExtranet())
		{
			$error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_ACCESS_DENIED2')
			);
		}
		// tmp flag for compatibility
		else if (
			ModuleManager::isModuleInstalled('bitrix24') &&
			Manager::getOption('temp_permission_admin_only') &&
			!\CBitrix24::isPortalAdmin(Manager::getUserId())
		)
		{
			$error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_ACCESS_DENIED2')
			);
		}
		// check common permission
		else if (
			!Rights::hasAdditionalRight(
				Rights::ADDITIONAL_RIGHTS['menu24'],
				null,
				true
			)
		)
		{
			$error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_ACCESS_DENIED2')
			);
		}
		// if method::action exist in PublicAction, call it
		elseif (($action = self::getMethodInfo($action, $data)))
		{
			if (!$isRest && !check_bitrix_sessid())
			{
				$error->addError(
					'SESSION_EXPIRED',
					Loc::getMessage('LANDING_SESSION_EXPIRED')
				);
			}
			if (!empty($action['params_missing']))
			{
				$error->addError(
					'MISSING_PARAMS',
					Loc::getMessage('LANDING_MISSING_PARAMS', array(
						'#MISSING#' => implode(', ', $action['params_missing'])
					))
				);
			}
			if (method_exists($action['class'], 'init'))
			{
				$result = call_user_func_array(
					array($action['class'], 'init'),
					[]
				);
				if (!$result->isSuccess())
				{
					$error->copyError($result->getError());
				}
			}
			// all right - execute
			if ($error->isEmpty())
			{
				try
				{
					$result = call_user_func_array(
						array($action['class'], $action['method']),
						$action['params_init']
					);
					// answer
					if ($result === null)// void is accepted as success
					{
						return array(
							'type' => 'success',
							'result' => true
						);
					}
					else if ($result->isSuccess())
					{
						$restResult = $result->getResult();
						$event = new \Bitrix\Main\Event('landing', 'onSuccessRest', [
							'result' => $restResult,
							'action' => $action
						]);
						$event->send();
						foreach ($event->getResults() as $eventResult)
						{
							if (($modified = $eventResult->getModified()))
							{
								if (isset($modified['result']))
								{
									$restResult = $modified['result'];
								}
							}
						}
						return array(
							'type' => 'success',
							'result' => $restResult
						);
					}
					else
					{
						$error->copyError($result->getError());
					}
				}
				catch (\TypeError $e)
				{
					$error->addError(
						'TYPE_ERROR',
						$e->getMessage()
					);
				}
				catch (\Exception $e)
				{
					$error->addError(
						'SYSTEM_ERROR',
						$e->getMessage()
					);
				}
			}
		}
		// error
		$errors = array();
		foreach ($error->getErrors() as $error)
		{
			$errors[] = array(
				'error' => $error->getCode(),
				'error_description' => $error->getMessage()
			);
		}
		if (!$isRest)
		{
			return [
				'sessid' => bitrix_sessid(),
				'type' => 'error',
				'result' => $errors
			];
		}
		else
		{
			return [
				'type' => 'error',
				'result' => $errors
			];
		}
	}

	/**
	 * Get raw data of curring processing.
	 * @return mixed
	 */
	public static function getRawData()
	{
		return self::$rawData;
	}

	/**
	 * Listen commands from ajax.
	 * @return array|null
	 * @throws \ReflectionException
	 */
	public static function ajaxProcessing()
	{
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		$files = $request->getFileList();
		$postlist = $context->getRequest()->getPostList();

		\Bitrix\Landing\Site\Type::setScope($request->get('type'));

		// multiple commands
		if (
			$request->offsetExists('batch') &&
			is_array($request->get('batch'))
		)
		{
			$result = array();
			// additional site id detect
			if ($request->offsetExists('site_id'))
			{
				$siteId = $request->get('site_id');
			}
			foreach ($request->get('batch') as $key => $batchItem)
			{
				if (
					isset($batchItem['action']) &&
					isset($batchItem['data'])
				)
				{
					$batchItem['data'] = (array)$batchItem['data'];
					if (isset($siteId))
					{
						$batchItem['data']['siteId'] = $siteId;
					}
					if ($files)
					{
						foreach ($files as $code => $file)
						{
							$batchItem['data'][$code] = $file;
						}
					}
					$rawData = $postlist->getRaw('batch');
					if (isset($rawData[$key]['data']))
					{
						self::$rawData = $rawData[$key]['data'];
					}
					$result[$key] = self::actionProcessing(
						$batchItem['action'],
						$batchItem['data']
					);
				}
			}

			return $result;
		}
		// or single command
		else if (
			$request->offsetExists('action') &&
			$request->offsetExists('data') &&
			is_array($request->get('data'))
		)
		{
			$data = $request->get('data');
			// additional site id detect
			if ($request->offsetExists('site_id'))
			{
				$data['siteId'] = $request->get('site_id');
			}
			if ($files)
			{
				foreach ($files as $code => $file)
				{
					$data[$code] = $file;
				}
			}
			$rawData = $postlist->getRaw('data');
			if (isset($rawData['data']))
			{
				self::$rawData = $rawData['data'];
			}
			return self::actionProcessing(
				$request->get('action'),
				$data
			);
		}

		return null;
	}

	/**
	 * Register methods in REST.
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function restBase()
	{
		static $restMethods = array();

		if (empty($restMethods))
		{
			$restMethods[self::REST_SCOPE_DEFAULT] = array();
			$restMethods[self::REST_SCOPE_CLOUD] = array();

			$classes = array(
				self::REST_SCOPE_DEFAULT => array(
					'block', 'site', 'landing', 'repo', 'template',
					'demos', 'role', 'syspage', 'chat'
				),
				self::REST_SCOPE_CLOUD => array(
					'cloud'
				)
			);

			// then methods list for each class
			foreach ($classes as $scope => $classList)
			{
				foreach ($classList as $className)
				{
					$fullClassName = self::getNamespacePublicClasses() . '\\' . $className;
					$class = new \ReflectionClass($fullClassName);
					$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
					foreach ($methods as $method)
					{
						$static = $method->getStaticVariables();
						if (!isset($static['internal']) || !$static['internal'])
						{
							$command = $scope.'.'.
								mb_strtolower($className).'.'.
								mb_strtolower($method->getName());
							$restMethods[$scope][$command] = array(
								__CLASS__, 'restGateway'
							);
						}
					}
				}
			}
		}

		return array(
			self::REST_SCOPE_DEFAULT => $restMethods[self::REST_SCOPE_DEFAULT],
			self::REST_SCOPE_CLOUD => $restMethods[self::REST_SCOPE_CLOUD]
		);
	}

	/**
	 * Gateway between REST and publicaction.
	 * @param array $fields Rest fields.
	 * @param mixed $t Var.
	 * @param \CRestServer $server Server instance.
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public static function restGateway($fields, $t, $server)
	{
		// get context app
		self::$restApp = AppTable::getByClientId($server->getClientId());
		// prepare method and call action
		$method = $server->getMethod();
		$method = mb_substr($method, mb_strpos($method, '.') + 1);// delete module-prefix
		$method = preg_replace('/\./', '\\', $method, substr_count($method, '.') - 1);
		$method = str_replace('.', '::', $method);
		$result = self::actionProcessing(
			$method,
			$fields,
			true
		);
		// prepare answer
		if ($result['type'] == 'error')
		{
			foreach ($result['result'] as $error)
			{
				throw new \Bitrix\Rest\RestException(
					$error['error_description'],
					$error['error']
				);
			}
		}
		else
		{
			return $result['result'];
		}
	}

	/**
	 * Get current REST application.
	 * @return array
	 */
	public static function restApplication()
	{
		return self::$restApp;
	}

	/**
	 * On REST app delete.
	 * @param array $app App info.
	 * @return void
	 */
	public static function restApplicationDelete($app)
	{
		if (isset($app['APP_ID']) && $app['APP_ID'])
		{
			if (($app = AppTable::getByClientId($app['APP_ID'])))
			{
				Rights::setOff();
				Repo::deleteByAppCode($app['CODE']);
				Placement::deleteByAppId($app['ID']);
				Demos::deleteByAppCode($app['CODE']);
				Rights::setOn();
			}
		}
	}

	/**
	 * Before REST app delete.
	 * @param \Bitrix\Main\Event $event Event data.
	 * @return \Bitrix\Main\EventResult
	 */
	public static function beforeRestApplicationDelete(\Bitrix\Main\Event $event)
	{
		$parameters = $event->getParameters();

		if ($app = AppTable::getByClientId($parameters['ID']))
		{
			$stat = self::getRestStat(true);
			if (isset($stat[self::REST_USAGE_TYPE_BLOCK][$app['CODE']]))
			{
				$eventResult = new \Bitrix\Main\EventResult(
					\Bitrix\Main\EventResult::ERROR,
					new \Bitrix\Main\Error(
						Loc::getMessage('LANDING_REST_DELETE_EXIST_BLOCKS'),
						'LANDING_EXISTS_BLOCKS'
					)
				);

				return $eventResult;
			}
			else if (isset($stat[self::REST_USAGE_TYPE_PAGE][$app['CODE']]))
			{
				$eventResult = new \Bitrix\Main\EventResult(
					\Bitrix\Main\EventResult::ERROR,
					new \Bitrix\Main\Error(
						Loc::getMessage('LANDING_REST_DELETE_EXIST_PAGES'),
						'LANDING_EXISTS_PAGES'
					)
				);

				return $eventResult;
			}
		}
	}

	/**
	 * Gets stat data of using rest app.
	 * @param bool $humanFormat Gets data in human format.
	 * @param bool $onlyActive Gets data only in active states.
	 * @param array $additionalFilter Additional filter array.
	 * @return array
	 */
	public static function getRestStat(bool $humanFormat = false, bool $onlyActive = true, array $additionalFilter = []): array
	{
		$blockCnt = [];
		$fullStat = [
			self::REST_USAGE_TYPE_BLOCK => [],
			self::REST_USAGE_TYPE_PAGE => []
		];
		$activeValues = $onlyActive ? 'Y' : ['Y', 'N'];
		$filter = [
			'CODE' => 'repo_%',
			'=DELETED' => 'N',
			'=PUBLIC' => $activeValues,
			'=LANDING.ACTIVE' => $activeValues,
			'=LANDING.SITE.ACTIVE' => $activeValues
		];

		if (isset($additionalFilter['SITE_ID']))
		{
			$filter['LANDING.SITE_ID'] = $additionalFilter['SITE_ID'];
		}

		Rights::setOff();

		// gets all partners active block, placed on pages
		$res = Internals\BlockTable::getList([
			'select' => [
				'CODE', 'CNT'
			],
			'filter' => $filter,
			'group' => [
				'CODE'
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			]
		]);
		while ($row = $res->fetch())
		{
			$blockCnt[mb_substr($row['CODE'], 5)] = $row['CNT'];
		}

		// gets apps for this blocks
		$res = Repo::getList([
			'select' => [
				'ID', 'APP_CODE'
			],
			'filter' => [
				'ID' => array_keys($blockCnt)
			]
 		]);
		while ($row = $res->fetch())
		{
			if (!$row['APP_CODE'])
			{
				continue;
			}
			if (!isset($fullStat[self::REST_USAGE_TYPE_BLOCK][$row['APP_CODE']]))
			{
				$fullStat[self::REST_USAGE_TYPE_BLOCK][$row['APP_CODE']] = 0;
			}
			$fullStat[self::REST_USAGE_TYPE_BLOCK][$row['APP_CODE']] += $blockCnt[$row['ID']];
		}
		unset($blockCnt);

		// gets additional partners active block with not empty INITIATOR_APP_CODE, placed on pages
		$filter['!CODE'] = $filter['CODE'];
		unset($filter['CODE']);
		$filter['!=INITIATOR_APP_CODE'] = null;
		$res = Internals\BlockTable::getList([
			'select' => [
				'INITIATOR_APP_CODE', 'CNT'
			],
			'filter' => $filter,
			'group' => [
				'INITIATOR_APP_CODE'
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			]
		]);
		while ($row = $res->fetch())
		{
			$appCode = $row['INITIATOR_APP_CODE'];
			if (!isset($fullStat[self::REST_USAGE_TYPE_BLOCK][$appCode]))
			{
				$fullStat[self::REST_USAGE_TYPE_BLOCK][$appCode] = 0;
			}
			$fullStat[self::REST_USAGE_TYPE_BLOCK][$appCode] += $row['CNT'];
		}

		// gets all partners active pages
		$filter = [
			'=DELETED' => 'N',
			'=ACTIVE' => $activeValues,
			'=SITE.ACTIVE' => $activeValues,
			'!=INITIATOR_APP_CODE' => null
		];
		if (isset($additionalFilter['SITE_ID']))
		{
			$filter['SITE_ID'] = $additionalFilter['SITE_ID'];
		}
		$res = Landing::getList([
			'select' => [
				'INITIATOR_APP_CODE', 'CNT'
			],
			'filter' => $filter,
			'group' => [
				'INITIATOR_APP_CODE'
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			]
		]);
		while ($row = $res->fetch())
		{
			$appCode = $row['INITIATOR_APP_CODE'];
			if (!isset($fullStat[self::REST_USAGE_TYPE_PAGE][$appCode]))
			{
				$fullStat[self::REST_USAGE_TYPE_PAGE][$appCode] = 0;
			}
			$fullStat[self::REST_USAGE_TYPE_PAGE][$appCode] += $row['CNT'];
		}

		// get client id for apps
		if (!$humanFormat && \Bitrix\Main\Loader::includeModule('rest'))
		{
			$appsCode = array_merge(
				array_keys($fullStat[self::REST_USAGE_TYPE_BLOCK]),
				array_keys($fullStat[self::REST_USAGE_TYPE_PAGE])
			);
			$fullStatNew = [
				self::REST_USAGE_TYPE_BLOCK => [],
				self::REST_USAGE_TYPE_PAGE => []
			];
			if ($appsCode)
			{
				$appsCode = array_unique($appsCode);
				$res = AppTable::getList([
					'select' => [
						'CLIENT_ID', 'CODE'
					],
					'filter' => [
						'=CODE' => $appsCode
					]
				]);
				while ($row = $res->fetch())
				{
					foreach ($fullStat as $code => $stat)
					{
						if (isset($stat[$row['CODE']]))
						{
							$fullStatNew[$code][$row['CLIENT_ID']] = $stat[$row['CODE']];
						}
					}
				}
			}

			return $fullStatNew;
		}

		Rights::setOn();

		return $fullStat;
	}
}