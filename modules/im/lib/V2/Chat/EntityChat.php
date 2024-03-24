<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Localization\Loc;

class EntityChat extends GroupChat
{
	protected const ENTITY_SEPARATOR = '|';

	protected const ENTITY_MAP_FIELDS = ['entityId', 'entityData1', 'entityData2', 'entityData3'];

	protected $entityMap = [
		'entityId' => [],
		'entityData1' => [],
		'entityData2' => [],
		'entityData3' => [],
	];

	protected $entityData = [];

	public function setEntityMap(array $entityMap): self
	{
		foreach ($entityMap as $field => $map)
		{
			if (in_array($field, self::ENTITY_MAP_FIELDS, true) && is_array($map))
			{
				$this->entityMap[$field] = array_values($map);
			}
		}

		return $this;
	}

	public function getEntityMap(): array
	{
		return $this->entityMap;
	}

	/**
	 * @param bool $force
	 * @return array
	 */
	public function getEntityData(bool $force = false): array
	{
		if (!count($this->entityData) || $force)
		{
			$this->entityData = $this->unmapEntity();
		}

		return $this->entityData;
	}

	private function unmapEntity(): array
	{
		$result = [];
		foreach ($this->getEntityMap() as $entityType => $entityFields)
		{
			if (!count($entityFields))
			{
				continue;
			}

			if ($this->$entityType)
			{
				$data = explode(self::ENTITY_SEPARATOR, $this->$entityType);
				if (count($entityFields) === count($data))
				{
					$result[$entityType] = array_combine($entityFields, $data);
				}
				else
				{
					$result[$entityType] = $data;
				}
			}
			else
			{
				$result[$entityType] = array_fill_keys($entityFields, null);
			}
		}
		return $result;
	}

	/**
	 * @param array $params
	 * <pre>
	 * [
	 * 	string ENTITY_TYPE
	 * 	string ENTITY_ID
	 * ]
	 * </pre>
	 * @return Result
	 */
	public static function find(array $params = [], ?Context $context = null): Result
	{
		$result = new Result;

		if (empty($params['ENTITY_TYPE']) || empty($params['ENTITY_ID']))
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARAMETER));
		}

		$row = ChatTable::query()
			->setSelect(['ID', 'TYPE', 'ENTITY_TYPE', 'ENTITY_ID'])
			->where('ENTITY_TYPE', $params['ENTITY_TYPE'])
			->where('ENTITY_ID', $params['ENTITY_ID'])
			->fetch()
		;

		if ($row)
		{
			$result->setResult([
				'ID' => (int)$row['ID'],
				'TYPE' => $row['TYPE'],
				'ENTITY_TYPE' => $row['ENTITY_TYPE'],
				'ENTITY_ID' => $row['ENTITY_ID'],
			]);
		}

		return $result;
	}
}
