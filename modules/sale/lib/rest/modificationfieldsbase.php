<?php


namespace Bitrix\Sale\Rest;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\RestException;

class ModificationFieldsBase
{
	const TO_WHITE_LIST 	= 0x00010;
	const TO_CAMEL 			= 0x00020;
	const TO_SNAKE 			= 0x00030;
	const SORTING_KEYS		= 0x00040;
	const CHECK_REQUIRED	= 0x00050;

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
		elseif ($controller instanceof \Bitrix\Sale\Controller\PersonType)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\PersonType();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\Property)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Property();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\PropertyGroup)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\PropertyGroup();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\PropertyValue)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\PropertyValue();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\Shipment)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Shipment();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\ShipmentItem)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\ShipmentItem();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\Status)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Status();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\StatusLang)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\StatusLang();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\DeliveryServices)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\DeliveryServices();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\TradeBinding)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\TradeBinding();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\TradePlatform)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\TradePlatform();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\Enum)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Enum();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\BusinessValuePersonDomain)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\BusinessValuePersonDomain();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\PropertyVariant)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\PropertyVariant();
		}
		elseif ($controller instanceof \Bitrix\Sale\Controller\PropertyRelation)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\PropertyRelation();
		}
		else
		{
			throw new RestException('Unknown object ' . get_class($controller));
		}

		return $entity;
	}
}