<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\Integration\View\Base;

abstract class ViewManager
{
	public function __construct(\Bitrix\Main\Engine\Action $controllerAction)
	{
		$this->controllerAction = $controllerAction;
	}

	/**
	 * @return \Bitrix\Main\Engine\Action
	 */
	public function getControllerAction()
	{
		return $this->controllerAction;
	}

	/**
	 * @param Controller $controller
	 * @return Base
	 */
	abstract public function getView(Controller $controller);
}