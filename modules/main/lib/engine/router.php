<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\Component\ComponentController;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Security;
use Bitrix\Main\SystemException;

final class Router
{
	const COMPONENT_MODE_AJAX  = 'ajax';
	const COMPONENT_MODE_CLASS = 'class';

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

		$this->component = $this->request->get('c') ?: null;
		$this->mode = $this->request->get('mode') ?: null;

		$this->action = $this->request->get('action');
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
				"The component {$this->component} must be implement interface \Bitrix\Main\Engine\Contract\Controllerable"
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
		if ($this->mode === self::COMPONENT_MODE_CLASS)
		{
			$component = $this->buildComponent($this->component, $this->request->getPost('signedParameters'));
			if (!$component)
			{
				throw new SystemException("Could not build component instance {$this->component}");
			}

			return array(new ComponentController($component), $this->action);
		}
		elseif ($this->mode === self::COMPONENT_MODE_AJAX)
		{
			$ajaxClass = $this->includeComponentAjaxClass($this->component);

			/** @var Controller $controller */
			/** @see \Bitrix\Main\Engine\Controller::__construct */
			$controller = new $ajaxClass();
			$controller->setScope(Controller::SCOPE_AJAX);
			$controller->setCurrentUser(CurrentUser::get());

			return array($controller, $this->action);
		}
		else
		{
			$modeAsString = var_export($this->mode, true);
			throw new SystemException("Unknown ajax mode ({$modeAsString}) to work {$this->component}");
		}
	}

	private function includeModule($module)
	{
		if (!Configuration::getInstance($module)->get('controllers'))
		{
			throw new SystemException("There is no configuration in {$module} with 'controllers' value.");
		}

		if (!Loader::includeModule($module))
		{
			throw new SystemException("Could not find module {$module}");
		}
	}

	private function includeComponentAjaxClass($name)
	{
		$path2Comp = \CComponentEngine::makeComponentPath($name);
		if ($path2Comp === '')
		{
			throw new SystemException("{$name} is not a valid component name");
		}

		$componentPath = getLocalPath("components" . $path2Comp);
		$ajaxClass = $this->getAjaxClassForPath($componentPath);

		if (!$ajaxClass)
		{
			throw new SystemException("Could not find ajax class {$componentPath}");
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
		for ($i = $afterClassesCount - 1; $i >= $beforeClassesCount; $i--)
		{
			if (
				is_subclass_of($afterClasses[$i], Controller::className()) ||
				in_array(Controller::className(), class_parents($afterClasses[$i])) //5.3.9
			)
			{
				return $afterClasses[$i];
			}
		}

		return null;
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
