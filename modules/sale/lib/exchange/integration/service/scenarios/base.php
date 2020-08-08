<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios;


use Bitrix\Sale\Exchange\Integration\Entity\B24IntegrationRelationTable;
use Bitrix\Sale\Exchange\Integration\Relation;
use Bitrix\Sale\Exchange\Integration\Exception;
use Bitrix\Sale\Exchange\Integration\Service\Batchable;
use Bitrix\Sale\Exchange\Integration\CRM;

class Base
{
	static public function dealAddsRelation(array $params)
	{
		$deal = new Batchable\Deal();

		foreach ($params as $index=>$param)
		{
			if(CRM\EntityType::isDefined($param['OWNER_TYPE_ID']))
			{
				if($param['OWNER_TYPE_ID'] == $deal->getDstEntityTypeId())
				{
					static::addRelation(
						$deal->getSrcEntityTypeId(), $index,
						$deal->getDstEntityTypeId(), $param['OWNER_ID']);
				}
				else
				{
					throw new Exception\ScenariosException('OwnerTypeId is not Deal');
				}
			}
			else
			{
				throw new Exception\ScenariosException('OwnerTypeId UNDEFINED');
			}
		}
	}

	static protected function loadRelation($id, $srcEntityTypeId, $dstEntityTypeId)
	{
		$result = null;
		$item = B24IntegrationRelationTable::getRow(['filter'=>[
			'SRC_ENTITY_ID' => $id,
			'SRC_ENTITY_TYPE_ID' => $srcEntityTypeId,
			'DST_ENTITY_TYPE_ID' => $dstEntityTypeId]]);

		if(is_null($item) == false)
		{
			$result = Relation\Relation::createFromArray([

				'SRC_ENTITY_TYPE_ID'=>$item['SRC_ENTITY_TYPE_ID'],
				'SRC_ENTITY_ID'=>$item['SRC_ENTITY_ID'],
				'DST_ENTITY_TYPE_ID'=>$item['DST_ENTITY_TYPE_ID'],
				'DST_ENTITY_ID'=>$item['DST_ENTITY_ID']
			]);
		}
		return $result;
	}

	static protected function addRelation($sourceEntityTypeId, $sourceEntityId, $destinationEntityTypeId, $destinationEntityId)
	{
		$relation = new Relation\Relation(
			$sourceEntityTypeId,
			$sourceEntityId,
			$destinationEntityTypeId,
			$destinationEntityId);

		$relation->save();
	}
	//deleteRelation()
}