<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Result;

trait CheckExists
{
	abstract protected function getEntityTable();

	protected function isExistsRow(int $id): bool
	{
		$entityTable = $this->getEntityTable();

		$row = $entityTable::getRow([
			'select' => [
				'ID',
			],
			'filter' => [
				'=ID' => $id,
			],
		]);

		return !empty($row);
	}

	/**
	 * @inheritDoc
	 */
	protected function exists($id)
	{
		$result = new Result();

		if (!$this->isExistsRow($id))
		{
			$result->addError($this->getErrorEntityNotExists());
		}

		return $result;
	}
}
