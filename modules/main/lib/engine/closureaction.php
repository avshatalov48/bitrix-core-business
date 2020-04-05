<?php

namespace Bitrix\Main\Engine;


final class ClosureAction extends Action
{
	/**
	 * @var callable
	 */
	protected $callable;

	/**
	 * ClosureAction constructor.
	 *
	 * @param string $name
	 * @param Controller $controller
	 * @param callable $callable
	 * @param array $config
	 */
	public function __construct($name, Controller $controller, $callable, $config = array())
	{
		$this->callable = $callable;
		parent::__construct($name, $controller, $config);
	}

	protected function buildBinder()
	{
		if ($this->binder === null)
		{
			$this->binder = Binder::buildForFunction(
				$this->callable,
				$this->getController()->getSourceParametersList()
			);
		}

		return $this;
	}
}