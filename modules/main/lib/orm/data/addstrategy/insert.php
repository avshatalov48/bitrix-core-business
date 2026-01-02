<?php

namespace Bitrix\Main\ORM\Data\AddStrategy;

use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Entity;

final class Insert implements AddStrategy
{
	public function __construct(
		private readonly Entity $entity,
	)
	{
	}

	public function add(array $dbFields): AddedData
	{
		// save data
		$connection = $this->entity->getConnection();

		$tableName = $this->entity->getDBTableName();
		$identity = $this->entity->getAutoIncrement();

		/** @noinspection PhpCastIsUnnecessaryInspection - typehints are lying */
		$id = (int)$connection->add($tableName, $dbFields, $identity);

		return new AddedData($id);
	}

	public function addMulti(array $multiDbFields): AddedMultiData
	{
		$connection = $this->entity->getConnection();

		$tableName = $this->entity->getDBTableName();
		$identity = $this->entity->getAutoIncrement();

		$connection->addMulti($tableName, $multiDbFields, $identity);

		return new AddedMultiData();
	}
}
