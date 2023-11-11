<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\Component\ComponentController;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Security;
use Bitrix\Main\SystemException;

final class Router
{
	public const COMPONENT_MODE_AJAX = 'ajax';
	public const COMPONENT_MODE_CLASS = 'class';

	public const EXCEPTION_INVALID_COMPONENT_INTERFACE = 2210201;
	public const EXCEPTION_INVALID_COMPONENT = 2210202;
	public const EXCEPTION_INVALID_AJAX_MODE = 2210203;
	public const EXCEPTION_NO_CONFIGURATION = 2210204;
	public const EXCEPTION_NO_MODULE = 2210205;
	public const EXCEPTION_INVALID_MODULE_NAME = 22102051;
	public const EXCEPTION_INVALID_COMPONENT_NAME = 2210206;
	public const EXCEPTION_NO_COMPONENT = 2210207;
	public const EXCEPTION_NO_COMPONENT_AJAX_CLASS = 2210208;

	protected $vendor = Resolver::DEFAULT_VENDOR;
	protected $module = 'main';
	protected $action = 'index';
	protected $component;
	protected $mode;

	/**
	 * @var HttpRequest
	 */
	private $request;

	/**
	 * Router constructor.
	 * @param HttpRequest $request
	 */
	public function __construct(HttpRequest $request)
	{
		$this->request = $request;

		$this->component = $this->request->getQuery('c') ?: null;
		$this->mode = $this->request->getQuery('mode') ?: null;

		$this->action = $this->request->getQuery('action');
		if ($this->action && is_string($this->action) && !$this->component)
		{
			list($this->vendor, $this->action) = $this->resolveVendor($this->action);
			list($module, $this->action) = $this->resolveModuleAndAction($this->action);

			$this->module = $this->refineModuleName($this->vendor, $module);
		}
	}

	private function resolveModuleAndAction($action)
	{
		$actionParts = explode('.', $action);
		$module = array_shift($actionParts);
		$action = implode('.', $actionParts);

		return [
			$module, $action
		];
	}

	private function resolveVendor($action)
	{
		list($vendor, $action) = explode(':', $action) + [null, null];
		if (!$action)
		{
			$action = $vendor;
			$vendor = Resolver::DEFAULT_VENDOR;
		}

		return [
			$vendor, $action
		];
	}

	protected function refineModuleName($vendor, $module)
	{
		if ($vendor === Resolver::DEFAULT_VENDOR)
		{
			return $module;
		}

		return $vendor . '.' . $module;
	}

	/**
	 * @param $componentName
	 *
	 * @param string|null $signedParameters
	 *
	 * @param null $template
	 *
	 * @return Controllerable|null
	 * @throws Security\Sign\BadSignatureException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	protected function buildComponent($componentName, $signedParameters = null, $template = null)
	{
		$class = \CBitrixComponent::includeComponentClass($componentName);
		if (!is_subclass_of($class, 'CBitrixComponent'))
		{
			return null;
		}

		$parameters = array();
		if ($signedParameters)
		{
			$parameters = ParameterSigner::unsignParameters($componentName, $signedParameters);
		}

		/** @var \CBitrixComponent $component */
		$component = new $class();

		if (!($component instanceof Controllerable))
		{
			throw new SystemException(
				"The component {$this->component} must be implement interface \Bitrix\Main\Engine\Contract\Controllerable",
				self::EXCEPTION_INVALID_COMPONENT_INTERFACE
			);
		}

		$component->initComponent($componentName, $template);
		$component->onIncludeComponentLang();
		$component->arParams = $component->onPrepareComponentParams($parameters);
		$component->__prepareComponentParams($component->arParams);

		return $component;
	}

	/**
	 * @return array
	 */
	public function getControllerAndAction()
	{
		if ($this->component)
		{
			return $this->getComponentControllerAndAction();
		}

		$this->includeModule($this->module);
		$controllerAndAction = Resolver::getControllerAndAction($this->vendor, $this->module, $this->action);
		if ($controllerAndAction)
		{
			return $controllerAndAction;
		}
		//default ajax class
		$ajaxClass = DefaultController::className();

		/** @see \Bitrix\Main\Engine\Controller::__construct */
		return array(new $ajaxClass, $this->action);
	}

	private function getComponentControllerAndAction()
	{
		$componentAsString = var_export($this->component, true);
		if ($this->mode === self::COMPONENT_MODE_CLASS)
		{
			$component = $this->buildComponent($this->component, $this->request->getPost('signedParameters'));
			if (!$component)
			{
				throw new SystemException(
					"Could not build component instance {$componentAsString}",
					self::EXCEPTION_INVALID_COMPONENT
				);
			}

			return array(new ComponentController($component), $this->action);
		}
		elseif ($this->mode === self::COMPONENT_MODE_AJAX)
		{
			$ajaxClass = $this->includeComponentAjaxClass($this->component);

			$controller = ControllerBuilder::build($ajaxClass, [
				'scope' => Controller::SCOPE_AJAX,
				'currentUser' => CurrentUser::get(),
			]);

			return [$controller, $this->action];
		}
		else
		{
			$modeAsString = var_export($this->mode, true);
			throw new SystemException(
				"Unknown ajax mode ({$modeAsString}) to work {$componentAsString}",
				self::EXCEPTION_INVALID_AJAX_MODE
			);
		}
	}

	private function includeModule($module)
	{
		if (!ModuleManager::isValidModule($module))
		{
			throw new SystemException(
				"Invalid module name {$module}",
				self::EXCEPTION_INVALID_MODULE_NAME
			);
		}

		if (!Configuration::getInstance($module)->get('controllers'))
		{
			throw new SystemException(
				"There is no configuration in {$module} with 'controllers' value.",
				self::EXCEPTION_NO_CONFIGURATION
			);
		}

		if (!Loader::includeModule($module))
		{
			throw new SystemException("Could not find module {$module}", self::EXCEPTION_NO_MODULE);
		}
	}

	private function includeComponentAjaxClass($name)
	{
		$path2Comp = \CComponentEngine::makeComponentPath($name);
		if ($path2Comp === '')
		{
			throw new SystemException("{$name} is not a valid component name", self::EXCEPTION_INVALID_COMPONENT_NAME);
		}

		$componentPath = getLocalPath("components" . $path2Comp);
		if ($componentPath === false)
		{
			throw new SystemException("Could not find component by name {$name}", self::EXCEPTION_NO_COMPONENT);
		}

		$ajaxClass = $this->getAjaxClassForPath($componentPath);
		if (!$ajaxClass)
		{
			throw new SystemException("Could not find ajax class {$componentPath}", self::EXCEPTION_NO_COMPONENT_AJAX_CLASS);
		}

		return $ajaxClass;
	}

	private function getAjaxClassForPath($componentPath)
	{
		$filename = \Bitrix\Main\Application::getDocumentRoot() . $componentPath . '/ajax.php';
		if (!file_exists($filename) || !is_file($filename))
		{
			return null;
		}

		$beforeClasses = get_declared_classes();
		$beforeClassesCount = count($beforeClasses);
		include_once($filename);
		$afterClasses = get_declared_classes();
		$afterClassesCount = count($afterClasses);
		$furthestClass = null;
		for ($i = $afterClassesCount - 1; $i >= $beforeClassesCount; $i--)
		{
			if (
				is_subclass_of($afterClasses[$i], Controller::class) ||
				($furthestClass && is_subclass_of($afterClasses[$i], $furthestClass))
			)
			{
				$furthestClass = $afterClasses[$i];
			}
		}

		return $furthestClass;
	}

	/**
	 * @param HttpRequest $request
	 * @return $this
	 */
	public function setRequest(HttpRequest $request)
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getVendor()
	{
		return $this->vendor;
	}

	/**
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @return string
	 */
	public function getComponent()
	{
		return $this->component;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}
}
