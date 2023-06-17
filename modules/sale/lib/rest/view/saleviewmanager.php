<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\Integration\ViewManager;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Controller\BusinessValuePersonDomain;
use Bitrix\Sale\Controller\DeliveryServices;
use Bitrix\Sale\Controller\Enum;
use Bitrix\Sale\Controller\PaymentItemBasket;
use Bitrix\Sale\Controller\PaymentItemShipment;
use Bitrix\Sale\Controller\PersonType;
use Bitrix\Sale\Controller\Profile;
use Bitrix\Sale\Controller\ProfileValue;
use Bitrix\Sale\Controller\PropertyGroup;
use Bitrix\Sale\Controller\PropertyRelation;
use Bitrix\Sale\Controller\PropertyValue;
use Bitrix\Sale\Controller\PropertyVariant;
use Bitrix\Sale\Controller\Status;
use Bitrix\Sale\Controller\StatusLang;
use Bitrix\Sale\Controller\TradeBinding;
use Bitrix\Sale\Controller\TradePlatform;
use Bitrix\Sale\Exchange\Integration;

final class SaleViewManager extends ViewManager
{
	/**
	 * @param Controller $controller
	 * @return \Bitrix\Sale\Rest\View\Base
	 * @throws RestException
	 */
	public function getView(Controller $controller)
	{
		$entity = null;
		if($controller instanceof Integration\Controller\StatisticProvider)
		{
			$entity = new Integration\RestView\StatisticProvider();
		}
		elseif($controller instanceof Integration\Controller\Statistic)
		{
			$entity = new Integration\RestView\Statistic();
		}
		elseif($controller instanceof Profile)
		{
			$entity = new \Bitrix\Sale\Rest\View\Profile();
		}
		elseif($controller instanceof ProfileValue)
		{
			$entity = new \Bitrix\Sale\Rest\View\ProfileValue();
		}
		elseif ($controller instanceof PersonType)
		{
			$entity = new \Bitrix\Sale\Rest\View\PersonType();
		}
		elseif ($controller instanceof PropertyGroup)
		{
			$entity = new \Bitrix\Sale\Rest\View\PropertyGroup();
		}
		elseif ($controller instanceof PropertyRelation)
		{
			$entity = new \Bitrix\Sale\Rest\View\PropertyRelation();
		}
		elseif ($controller instanceof PropertyValue)
		{
			$entity = new \Bitrix\Sale\Rest\View\PropertyValue();
		}
		elseif ($controller instanceof Enum)
		{
			$entity = new \Bitrix\Sale\Rest\View\Enum();
		}
		elseif ($controller instanceof DeliveryServices)
		{
			$entity = new \Bitrix\Sale\Rest\View\DeliveryServices();
		}
		elseif ($controller instanceof PropertyVariant)
		{
			$entity = new \Bitrix\Sale\Rest\View\PropertyVariant();
		}
		elseif ($controller instanceof Status)
		{
			$entity = new \Bitrix\Sale\Rest\View\Status();
		}
		elseif ($controller instanceof StatusLang)
		{
			$entity = new \Bitrix\Sale\Rest\View\StatusLang();
		}
		elseif ($controller instanceof TradeBinding)
		{
			$entity = new \Bitrix\Sale\Rest\View\TradeBinding();
		}
		elseif ($controller instanceof TradePlatform)
		{
			$entity = new \Bitrix\Sale\Rest\View\TradePlatform();
		}
		elseif ($controller instanceof BusinessValuePersonDomain)
		{
			$entity = new \Bitrix\Sale\Rest\View\BusinessValuePersonDomain();
		}
		elseif ($controller instanceof PaymentItemBasket)
		{
			$entity = new \Bitrix\Sale\Rest\View\PaymentItemBasket();
		}
		elseif ($controller instanceof PaymentItemShipment)
		{
			$entity = new \Bitrix\Sale\Rest\View\PaymentItemShipment();
		}
		else
		{
			throw new RestException('Unknown object ' . get_class($controller));
		}
		return $entity;
	}
}