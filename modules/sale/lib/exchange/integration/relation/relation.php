<?php
namespace Bitrix\Sale\Exchange\Integration\Relation;


use Bitrix\Sale\Exchange\Integration\Entity;
use Bitrix\Sale\Result;

class Relation
{
	protected $sourceEntityTypeId = 0;
	protected $sourceEntityId = 0;

	protected $destinationEntityTypeId = 0;
	protected $destinationEntityId = 0;

	public function __construct($sourceEntityTypeId, $sourceEntityId, $destinationEntityTypeId, $destinationEntityId)
	{
		if(!is_int($sourceEntityTypeId))
		{
			$sourceEntityTypeId = (int)$sourceEntityTypeId;
		}

		if(!is_int($sourceEntityId))
		{
			$sourceEntityId = (int)$sourceEntityId;
		}

		if(!is_int($destinationEntityTypeId))
		{
			$destinationEntityTypeId = (int)$destinationEntityTypeId;
		}

		if(!is_int($destinationEntityId))
		{
			$destinationEntityId = (int)$destinationEntityId;
		}

		$this->sourceEntityTypeId = $sourceEntityTypeId;
		$this->sourceEntityId = $sourceEntityId;

		$this->destinationEntityTypeId = $destinationEntityTypeId;
		$this->destinationEntityId = $destinationEntityId;
	}

	public function save()
	{
		// region multiple relation
		Entity\B24IntegrationBindTable::upsert(
			[
				'SRC_ENTITY_TYPE_ID' => $this->sourceEntityTypeId,
				'SRC_ENTITY_ID' => $this->sourceEntityId,
				'DST_ENTITY_TYPE_ID' => $this->destinationEntityTypeId,
				'DST_ENTITY_ID' => $this->destinationEntityId
			]
		);
		// endregion
		// region single relation
		Entity\B24IntegrationRelationTable::upsert(
			[
				'SRC_ENTITY_TYPE_ID' => $this->sourceEntityTypeId,
				'SRC_ENTITY_ID' => $this->sourceEntityId,
				'DST_ENTITY_TYPE_ID' => $this->destinationEntityTypeId,
				'DST_ENTITY_ID' => $this->destinationEntityId
			]
		);
		// endregion

		return new Result();
	}

	public static function getBySourceEntity($entityTypeId, $entityId)
	{
		$dbResult = Entity\B24IntegrationRelationTable::getList([
			'filter' => [
				'=SRC_ENTITY_TYPE_ID' => $entityTypeId,
				'=SRC_ENTITY_ID' => $entityId
			]
		]);

		$results = [];
		while($fields = $dbResult->fetch())
		{
			$results[] = $fields;
		}
		return $results;
	}

	public static function getByEntity($sourceEntityTypeId, $sourceEntityId, $destinationEntityTypeId, $destinationEntityId='')
	{
		$filter = [];
		if($sourceEntityTypeId<>'')
			$filter['=SRC_ENTITY_TYPE_ID'] = $sourceEntityTypeId;
		if($sourceEntityId<>'')
			$filter['=SRC_ENTITY_ID'] = $sourceEntityId;
		if($destinationEntityTypeId<>'')
			$filter['=DST_ENTITY_TYPE_ID'] = $destinationEntityTypeId;
		if($destinationEntityId<>'')
			$filter['=DST_ENTITY_ID'] = $destinationEntityId;


		return Entity\B24IntegrationRelationTable::getRow([
			'filter' => [$filter]
		]);
	}

	public static function createFromArray(array $data)
	{
		$item = new Relation(
			$data['SRC_ENTITY_TYPE_ID'],
			$data['SRC_ENTITY_ID'],
			$data['DST_ENTITY_TYPE_ID'],
			$data['DST_ENTITY_ID']
		);

		return $item;
	}

	public function getSourceEntityTypeId()
	{
		return $this->sourceEntityTypeId;
	}
	public function getSourceEntityId()
	{
		return $this->sourceEntityId;
	}
	public function getDestinationEntityTypeId()
	{
		return $this->destinationEntityTypeId;
	}
	public function getDestinationEntityId()
	{
		return $this->destinationEntityId;
	}

	public function setDestinationEntityId($entityId)
	{
		if(!is_int($entityId))
		{
			$entityId = (int)$entityId;
		}

		$this->destinationEntityId = $entityId;
	}
}