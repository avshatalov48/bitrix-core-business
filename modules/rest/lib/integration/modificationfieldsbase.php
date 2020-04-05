<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine\Controller;

class ModificationFieldsBase
{
	const TO_WHITE_LIST 	= 0x00010;
	const TO_CAMEL 			= 0x00020;
	const TO_SNAKE 			= 0x00030;
	const SORTING_KEYS		= 0x00040;
	const CHECK_REQUIRED	= 0x00050;

	protected $data;
	protected $name;
	protected $format;
	protected $manager;
	protected $arguments;
	protected $controller;

	public function __construct(ViewManager $manager, $data=[])
	{
		$this->manager = $manager;

		$this->name = $this->getManager()->getControllerAction()->getName();
		$this->arguments = $this->getManager()->getControllerAction()->getArguments();
		$this->controller = $this->getManager()->getControllerAction()->getController();

		$this->data = $data;
	}

	/**
	 * @return ViewManager
	 */
	public function getManager()
	{
		return $this->manager;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getArguments()
	{
		return $this->arguments;
	}

	public function setArguments($arguments)
	{
		$this->arguments = $arguments;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getData()
	{
		return $this->data;
	}

	protected function getView(Controller $controller)
	{
		return $this->getManager()->getView($controller);
	}
}