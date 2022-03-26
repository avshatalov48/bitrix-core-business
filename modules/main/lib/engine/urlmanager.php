<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;

final class UrlManager
{
	const AJAX_END_POINT = '/bitrix/services/main/ajax.php';

	const ABSOLUTE_URL = true;

	/** @var UrlManager */
	private static $instance;

	private function __clone()
	{}

	private function __construct()
	{}

	/**
	 * @return UrlManager
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * Creates uri for the action.
	 *
	 * @param string $action The fully qualified action name.
	 * @param array $params Additional parameters for action.
	 *
	 * @param bool $absolute Generate absolute uri or not.
	 *
	 * @return Uri
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function create($action, $params = [], $absolute = false)
	{
		$uri = $this->getEndPoint($absolute);
		$uri->addParams([
			'action' => $action,
		]);

		if (defined('SITE_ID') && !Context::getCurrent()->getRequest()->isAdminSection())
		{
			$uri->addParams([
				'SITE_ID' => SITE_ID,
			]);
		}

		$uri->addParams($params);

		return $uri;
	}

	/**
	 * Creates uri for the controller and it's action.
	 *
	 * @param Controller $controller Controller.
	 * @param string $action Relative action name.
	 * @param array $params Additional parameters for action.
	 *
	 * @param bool $absolute Generate absolute uri or not.
	 *
	 * @return Uri
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function createByController(Controller $controller, $action, $params = array(), $absolute = false)
	{
		$name = Resolver::getNameByController($controller);
		if (!$controller->isLocatedUnderPsr4())
		{
			$name = mb_strtolower($name);
		}

		list($vendor) = $this->getVendorAndModule($controller->getModuleId());
		if ($vendor === 'bitrix')
		{
			$name = mb_substr($name, mb_strlen('bitrix:'));
		}

		return $this->create(
			$name . '.' . $action,
			$params,
			$absolute
		);
	}

	protected function getVendorAndModule($moduleId)
	{
		$parts = explode('.', $moduleId);
		if (!isset($parts[1]))
		{
			return ['bitrix', $moduleId];
		}

		if ($parts[0] === 'bitrix')
		{
			return ['bitrix', $moduleId];
		}

		return [$parts[0], $moduleId];
	}

	/**
	 * Creates uri for the controller in component and it's action.
	 *
	 * @param Controller $controller Controller.
	 * @param string $action Relative action name.
	 * @param array $params Additional parameters for action.
	 *
	 * @param bool $absolute Generate absolute uri or not.
	 *
	 * @return Uri
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \ReflectionException
	 */
	public function createByComponentController(Controller $controller, $action, $params = array(), $absolute = false)
	{
		$reflector = new \ReflectionClass($controller);
		$path = dirname($reflector->getFileName());
		if (DIRECTORY_SEPARATOR === '\\')
		{
			$path = str_replace('\\', '/', $path);
		}
		$pathWithoutLocal = mb_substr($path, mb_strpos($path, '/components/') + mb_strlen('/components/'));
		list($vendor, $componentName) = explode('/', $pathWithoutLocal);

		if (!$componentName)
		{
			$componentName = $vendor;
		}
		else
		{
			$componentName = "{$vendor}:{$componentName}";
		}

		$params['c'] = $componentName;
		$params['mode'] = Router::COMPONENT_MODE_AJAX;

		return $this->create(
			$action,
			$params,
			$absolute
		);
	}

	/**
	 * Creates uri for the bitrix component which implements @see \Bitrix\Main\Engine\Contract\Controllerable
	 * and it's action.
	 *
	 * @param \CBitrixComponent $component
	 * @param string $action Relative action name.
	 * @param array $params Additional parameters for action.
	 *
	 * @param bool $absolute Generate absolute uri or not.
	 *
	 * @return Uri
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function createByBitrixComponent(\CBitrixComponent $component, $action, $params = array(), $absolute = false)
	{
		$params['c'] = $component->getName();
		$params['mode'] = Router::COMPONENT_MODE_CLASS;

		return $this->create(
			$action,
			$params,
			$absolute
		);
	}

	/**
	 * Returns uri for the end point.
	 *
	 * @param bool $absolute Generate absolute uri or not.
	 *
	 * @return Uri
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function getEndPoint($absolute = false)
	{
		$endPoint = self::AJAX_END_POINT;
		if ($absolute === self::ABSOLUTE_URL)
		{
			$endPoint = $this->getHostUrl() . $endPoint;
		}

		return new Uri($endPoint);
	}

	/**
	 * Returns host url with port and scheme.
	 *
	 * @return string
	 */
	public function getHostUrl(): string
	{
		$request = Context::getCurrent()->getRequest();

		$protocol = $request->isHttps() ? 'https' : 'http';
		$port = (int)$request->getServerPort();

		if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME)
		{
			$host = SITE_SERVER_NAME;
		}
		else
		{
			$host = Option::get('main', 'server_name', $request->getHttpHost()) ? : $request->getHttpHost();
		}

		$portSuffix = '';
		if ($port && !in_array($port, [443, 80], true))
		{
			$portSuffix = ':' . $port;
		}
		$parsedUri = new Uri($protocol . '://' . $host . $portSuffix);

		return rtrim($parsedUri->getLocator(), '/');
	}
}
