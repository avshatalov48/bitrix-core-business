<?php
namespace Bitrix\Sale\Exchange\Integration;


use Bitrix\Main\Event;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Exchange\Integration;

final class Handler
{
	static public function handlerCallbackOnSaleOrderSaved(Event $event)
	{
		/** @var Order $order */
		$order = $event->getParameters()['ENTITY'];

		if((new Integration\Connector\Manager())->isOn())
		{
			if($order->isNew())
			{
				$fields = new \Bitrix\Sale\Internals\Fields(
					\Bitrix\Main\Context::getCurrent()
						->getRequest()
						->toArray());

				$placement = new Integration\CRM\Placement\PlacementDeal($fields->getValues());
				if($placement->getTypeHandler() == Integration\HandlerType::ORDER_NEW)	//deal exists + new order
				{

					if($fields->get('entityTypeId') == Integration\CRM\EntityType::DEAL
						&& $fields->get('entityId')>0)
					{
						$params[$order->getId()] = [
							'OWNER_TYPE_ID'=>Integration\CRM\EntityType::DEAL,
							'OWNER_ID'=>$fields->get('entityId')
						];

						(new Integration\Service\Scenarios\DealUpdate())
							->update($fields->get('entityId'), $params);

						(new static())
							->syncB24($order);
					}
				}
				else	//new deal + new order
				{
					/*
					(new Integration\Service\Scenarios\DealAdd())
						->adds([$order->getId()=>$order->getFieldValues()]);

					(new static())
						->syncB24($order);
					*/
				}
			}
		}

	}
	static public function syncB24(Order $order)
	{
		$order->setFieldNoDemand('IS_SYNC_B24', 'Y');

		OrderTable::update($order->getId(), ['IS_SYNC_B24' => 'Y']);
	}
}