<?php

namespace Bitrix\Main\Engine\Component;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Binder;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Errorable;

final class InlineAction extends Action
{
	/**
	 * @var string
	 */
	protected $methodName;
	/**
	 * @var Controllerable
	 */
	private $controllerable;

	/**
	 * InlineAction constructor.
	 *
	 * @param string $name
	 * @param Controllerable $controllerable
	 * @param Controller $controller
	 * @param array $config
	 */
	public function __construct($name, Controllerable $controllerable, Controller $controller, $config = array())
	{
		$this->methodName = $controller->generateActionMethodName($name);
		$this->controllerable = $controllerable;
		parent::__construct($name, $controller, $config);
	}

	protected function buildBinder()
	{
		if ($this->binder === null)
		{
			$this->binder = new Binder(
				$this->controllerable,
				$this->methodName,
				$this->getController()->getSourceParametersList()
			);
		}

		return $this;
	}

	public function runWithSourceParametersList()
	{
		$result = parent::runWithSourceParametersList();

		if ($this->controllerable instanceof Errorable)
		{
			$this->errorCollection->add(
				$this->controllerable->getErrors()
			);
		}

		return $result;
	}
}