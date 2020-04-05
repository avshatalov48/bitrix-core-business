<?php

namespace Bitrix\Main\Engine;


final class InlineAction extends Action
{
	/**
	 * @var string
	 */
	protected $methodName;

	/**
	 * InlineAction constructor.
	 *
	 * @param string $name
	 * @param Controller $controller
	 * @param array $config
	 */
	public function __construct($name, Controller $controller, $config = array())
	{
		$this->methodName = $controller->generateActionMethodName($name);
		parent::__construct($name, $controller, $config);
	}

	protected function buildBinder()
	{
		if ($this->binder === null)
		{
			$this->binder = new Binder(
				$this->controller,
				$this->methodName,
				$this->getController()->getSourceParametersList()
			);
		}

		return $this;
	}
}