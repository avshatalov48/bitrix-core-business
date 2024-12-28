<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Socialnetwork\Collab\Internals\CollabLogTable;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryBuilderFromEntityObject;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class CollabLogProvider
{
	use InstanceTrait;

	private CollabLogEntryBuilderFromEntityObject $builderFromEntityObject;

	private function __construct()
	{
		$this->builderFromEntityObject = new CollabLogEntryBuilderFromEntityObject();
	}

	public function getList(CollabLogFilter $filter): array
	{
		$query = CollabLogTable::query();

		$query->setSelect([
			'ID',
			'TYPE',
			'COLLAB_ID',
			'ENTITY_TYPE',
			'ENTITY_ID',
			'USER_ID',
			'DATETIME',
			'DATA',
		]);

		if ($filter->collabId > 0)
		{
			$query->where('COLLAB_ID', $filter->collabId);
		}

		if ($filter->userId > 0)
		{
			$query->where('USER_ID', $filter->userId);
		}

		if ($filter->entity)
		{
			$query->where('ENTITY_TYPE', $filter->entity->getType());
			$query->where('ENTITY_ID', $filter->entity->getId());
		}

		if ($filter->from)
		{
			$query->where('DATETIME', '>=', $filter->from);
		}

		if ($filter->to)
		{
			$query->where('DATETIME', '<=', $filter->to);
		}

		$query->setLimit($filter->limit);
		$query->setOffset($filter->offset);

		$collection = $query->fetchCollection();

		$result = [];

		foreach ($collection as $logEntry)
		{
			try
			{
				$result[] = $this->builderFromEntityObject->build($logEntry);
			}
			catch (\Exception)
			{}
		}

		return $result;
	}
}
