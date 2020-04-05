<?php

namespace Bitrix\Main\Engine\Component;


use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class ComponentController extends Controller
{
	/**
	 * @var \CBitrixComponent
	 */
	private $component;

	/**
	 * ComponentController constructor.
	 *
	 * @param Controllerable $component
	 */
	public function __construct(Controllerable $component)
	{
		$this->component = $component;

		parent::__construct();
	}

	public function configureActions()
	{
		return $this->component->configureActions();
	}

	protected function create($actionName)
	{
		$config = $this->getActionConfig($actionName);
		$methodName = $this->generateActionMethodName($actionName);

		if (method_exists($this->component, $methodName))
		{
			$method = new \ReflectionMethod($this->component, $methodName);
			if ($method->isPublic() && strtolower($method->getName()) === strtolower($methodName))
			{
				return new InlineAction($actionName, $this->component, $this, $config);
			}
		}

		return parent::create($actionName);
	}

}