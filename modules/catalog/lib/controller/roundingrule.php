<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\RoundingTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Event\EventBindInterface;

final class RoundingRule extends Controller implements EventBindInterface
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use CheckExists; // default implementation of existence check
	use PriceTypeRights;

	//region Actions

	/**
	 * @param array $fields
	 * @return array|null
	 */
	public function addAction(array $fields): ?array
	{
		$checkFieldsResult = $this->checkFields($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$addResult = RoundingTable::add($fields);
		if (!$addResult->isSuccess())
		{
			$this->addErrors($addResult->getErrors());
			return null;
		}

		return [$this->getServiceItemName() => $this->get($addResult->getId())];
	}

	/**
	 * @param int $id
	 * @param array $fields
	 * @return array|null
	 */
	public function updateAction(int $id, array $fields): ?array
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());
			return null;
		}

		$checkFieldsResult = $this->checkFields($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$updateResult = RoundingTable::update($id, $fields);
		if (!$updateResult)
		{
			$this->addErrors($updateResult->getErrors());
			return null;
		}

		return [$this->getServiceItemName() => $this->get($id)];
	}

	/**
	 * @param int $id
	 * @return bool|null
	 */
	public function deleteAction(int $id): ?bool
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());
			return null;
		}

		$deleteResult = RoundingTable::delete($id);
		if (!$deleteResult)
		{
			$this->addErrors($deleteResult->getErrors());
			return null;
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return new RoundingTable();
	}

	/**
	 * @param array $fields
	 * @return Result
	 */
	private function checkFields(array $fields): Result
	{
		$result = new Result();

		if (array_key_exists('ROUND_TYPE', $fields))
		{
			$roundTypes = RoundingTable::getRoundTypes();
			if (!in_array($fields['ROUND_TYPE'], $roundTypes))
			{
				$result->addError(
					new Error(
						'Invalid rounding type provided. The available values are: '
						. implode(', ', $roundTypes)
					)
				);
			}
			unset($roundTypes);
		}

		if (array_key_exists('ROUND_PRECISION', $fields))
		{
			$precisionList = RoundingTable::getPresetRoundingValues();
			if (!in_array($fields['ROUND_PRECISION'], $precisionList))
			{
				$result->addError(
					new Error(
						'Invalid rounding precision provided. The available values are: '
						. implode(', ', $precisionList)
					)
				);
			}
			unset($precisionList);
		}

		return $result;
	}

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::ROUNDING_RULE_ENTITY_NOT_EXISTS;
	}
}
