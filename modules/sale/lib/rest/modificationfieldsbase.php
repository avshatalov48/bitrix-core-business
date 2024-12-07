<?php

namespace Bitrix\Sale\Rest;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\RestException;

class ModificationFieldsBase
{
	const TO_WHITE_LIST = 'TO_WHITE_LIST';
	const TO_CAMEL = 'TO_CAMEL';
	const TO_SNAKE = 'TO_SNAKE';
	const SORTING_KEYS = 'SORTING_KEYS';
	const CHECK_REQUIRED = 'CHECK_REQUIRED';

	protected string $name;
	protected array $arguments;
	protected Controller $controller;
	protected $format;
	protected $data;
	protected $scope;

	static public function buildByAction(Action $action, $data=[], $scope='')
	{
		return new static($action->getName(), $action->getArguments(), $action->getController(), $data, $scope);
	}

	public function __construct($name, $arguments,  $controller, $data=[], $scope='')
	{
		$this->name = $name;
		$this->arguments = $arguments;
		$this->controller = $controller;

		$this->data = $data;
		$this->scope = $scope;
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

	/**
	 * @param mixed $format
	 */
	public function setFormat($format): void
	{
		$this->format = $format;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getScope()
	{
		return $this->scope;
	}

	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param Controller $controller
	 * @return Entity\BasketItem|Entity\Order|Entity\Payment|Entity\PersonType|Entity\Property|Entity\PropertyGroup|null
	 * @throws RestException
	 */
	protected function getEntity(Controller $controller)
	{
		$entity = null;
		if($controller instanceof \Bitrix\Sale\Controller\Order)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Order();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\BasketItem)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\BasketItem();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\BasketProperties)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\BasketProperties();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\Payment)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Payment();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\Property)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Property();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\Shipment)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Shipment();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\ShipmentItem)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\ShipmentItem();
		}
		else
		{
			throw new RestException('Unknown object ' . get_class($controller));
		}

		return $entity;
	}
}
