<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Result;

final class ControllerBinder extends Binder
{
	/** @var Controller */
	private $controller;

	public function getController(): Controller
	{
		return $this->controller;
	}

	public function setController(Controller $controller): ControllerBinder
	{
		$this->controller = $controller;

		return $this;
	}

	protected function constructValue(\ReflectionParameter $parameter, Parameter $autoWireParameter, Result $captureResult): Result
	{
		$result = new Result();

		$controller = $this->getController();

		$errorsBefore = $controller->getErrors();
		$constructedValue = $autoWireParameter->constructValue($parameter, $captureResult, $controller);
		$errorsAfter = $controller->getErrors();

		$newErrors = array_diff($errorsAfter, $errorsBefore);

		$result->setData([
			'value' => $constructedValue,
		]);

		if ($newErrors)
		{
			$result->addErrors($newErrors);
		}

		return $result;
	}
}