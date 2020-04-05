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
		if ($action && strpos($action, '::'))
		{
			$action = self::getNamespacePublicClasses() . '\\' . $action;
			if (is_callable(explode('::', $action)))
			{
				list($class, $method) = explode('::', $action);
				$info = array(
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

		if (!$isRest && (!defined('BX_UTF') || BX_UTF !== true))
		{
			$data = Manager::getApplication()->convertCharsetArray(
				$data, 'UTF-8', SITE_CHARSET
			);
		}

		$error = new Error;

		//@tmp
		if (!Manager::getUserId())
		{
			$error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_ACCESS_DENIED')
			);
		}
		// siteman
		else if (
			!ModuleManager::isModuleInstalled('bitrix24') &&
			Manager::getApplication()->getGroupRight('landing') < 'W'
		)
		{
			$error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_ACCESS_DENIED2')
			);
		}
		else if (
			\Bitrix\Main\Loader::includeModule('bitrix24') &&
			Manager::getOption('temp_permission_admin_only') &&
			!\CBitrix24::isPortalAdmin(Manager::getUserId())
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
					if ($result->isSuccess())
					{
						return array(
							'type' => 'success',
							'result' => $result->getResult()
						);
					}
					else
					{
						$error->copyError($result->getError());
					}
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
		return array(
			'type' => 'error',
			'result' => $errors
		);
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
					'block', 'site', 'landing', 'repo', 'template', 'demos'
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
									   strtolower($className) . '.' .
									   strtolower($method->getName());
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
	 * @params array $fields Rest fields.
	 * @params mixed $t Var.
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
		$method = substr($method, strpos($method, '.') + 1);// delete module-prefix
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
				Repo::deleteByAppCode($app['CODE']);
				Placement::deleteByAppId($app['ID']);
				Demos::deleteByAppCode($app['CODE']);
			}
		}
	}
}