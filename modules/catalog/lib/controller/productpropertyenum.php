<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\DuplicateEntryException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class ProductPropertyEnum extends ProductPropertyBase
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization

	// region Actions

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

	public function addAction(array $fields): ?array
	{
		$checkFieldsResult = $this->checkFieldsBeforeModify($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$property = $this->getPropertyById($fields['PROPERTY_ID']);
		if (!$property)
		{
			$this->addError($this->getErrorEntityNotExists());

			return null;
		}

		$propertyType = $property['PROPERTY_TYPE'];
		if ($propertyType !== PropertyTable::TYPE_LIST)
		{
			$this->addError(new Error('Only list properties are supported'));

			return null;
		}

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			$result = PropertyEnumerationTable::add($fields);
		}
		catch (DuplicateEntryException)
		{
			$result = new Result();
			$result->addError(new Error('A value with xmlId \'' . $fields['XML_ID'] . '\' already exists.'));
		}
		catch (SqlQueryException)
		{
			$result = new Result();
			$result->addError(new Error('Internal error adding enumeration value. Try adding again.'));
		}

		if (!$result->isSuccess())
		{
			$conn->rollbackTransaction();
			$this->addErrors($result->getErrors());

			return null;
		}
		$conn->commitTransaction();

		return [
			$this->getServiceItemName() => $this->get($result->getId()),
		];
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

		$checkFieldsResult = $this->checkFieldsBeforeModify($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$propertyId = $this->get($id)['PROPERTY_ID'];

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			$result = PropertyEnumerationTable::update(
				[
					'ID' => $id,
					'PROPERTY_ID' => $propertyId,
				],
				$fields
			);
		}
		catch (DuplicateEntryException)
		{
			$result = new Result();
			$result->addError(new Error('A value with xmlId \'' . $fields['XML_ID'] . '\' already exists.'));
		}
		catch (SqlQueryException)
		{
			$result = new Result();
			$result->addError(new Error('Internal error updating enumeration value. Try updating again.'));
		}

		if (!$result->isSuccess())
		{
			$conn->rollbackTransaction();
			$this->addErrors($result->getErrors());

			return null;
		}
		$conn->commitTransaction();

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

		$propertyId = $this->get($id)['PROPERTY_ID'];

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			$result = PropertyEnumerationTable::delete([
				'ID' => $id,
				'PROPERTY_ID' => $propertyId,
			]);
		}
		catch (SqlQueryException)
		{
			$result = new Result();
			$result->addError(new Error('Internal error deleting enumeration value. Try deleting again.'));
		}

		if (!$result->isSuccess())
		{
			$conn->rollbackTransaction();
			$this->addErrors($result->getErrors());

			return null;
		}
		$conn->commitTransaction();

		return true;
	}

	// endregion

	/**
	 * @inheritDoc
	 */
	protected function get($id)
	{
		return PropertyEnumerationTable::getRow([
			'select' => ['*', 'IBLOCK_ID' => 'PROPERTY.IBLOCK_ID'],
			'filter' => ['=ID' => $id],
		]);
	}

	/**
	 * @inheritDoc
	 */
	protected function exists($id)
	{
		$result = new Result();
		$propertyEnum = $this->get($id);
		if (!$propertyEnum || !$this->isIblockCatalog((int)$propertyEnum['IBLOCK_ID']))
		{
			$result->addError($this->getErrorEntityNotExists());
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return PropertyEnumerationTable::class;
	}

	/**
	 * @inheritDoc
	 * @param array $params
	 * @return array
	 */
	protected function modifyListActionParameters(array $params): array
	{
		$params['filter']['PROPERTY.IBLOCK_ID'] = $this->getCatalogIds();

		return $params;
	}
}
