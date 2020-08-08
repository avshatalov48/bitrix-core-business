<?php


namespace Bitrix\Sale\Exchange\Integration\Timeline;


use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Exchange\Manager;

class Base
{
	static protected function onReceive($orderId, array $settings)
	{
		$item = \Bitrix\Sale\Exchange\Integration\Relation\Relation::getByEntity(
			Integration\EntityType::ORDER,
			$orderId,
			Integration\CRM\EntityType::DEAL,
			''
		);

		$relation = Integration\Relation\Relation::createFromArray([
			'SRC_ENTITY_TYPE_ID'=>$item['SRC_ENTITY_TYPE_ID'],
			'SRC_ENTITY_ID'=>$item['SRC_ENTITY_ID'],
			'DST_ENTITY_TYPE_ID'=>$item['DST_ENTITY_TYPE_ID'],
			'DST_ENTITY_ID'=>$item['DST_ENTITY_ID']
		]);

		$proxy = new Integration\Rest\RemoteProxies\CRM\Timeline();

		$r = $proxy->onReceive(
			$relation->getDestinationEntityId(),
			$relation->getDestinationEntityTypeId(),
			$settings
		);

		if($r->isSuccess())
		{
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			$result['error'] = $r->getErrorMessages();
		}

		//ECHO '<pre>'; PRINT_R($order->getFields()->getOriginalValues());DIE;
		//ECHO '<pre>'; PRINT_R($result);DIE;
	}

	static protected function isSync(\Bitrix\Sale\Order $order)
	{
		return (new Integration\Connector\Manager())->isOn() && $order->getField('IS_SYNC_B24') == 'Y';
	}
}