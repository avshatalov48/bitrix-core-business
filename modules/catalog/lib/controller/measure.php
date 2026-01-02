<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\MeasureTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\DuplicateEntryException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use Bitrix\Rest\Event\EventBindInterface;

final class Measure extends Controller implements EventBindInterface
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use CheckExists; // default implementation of existence check

	//region Actions
	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * @param array $fields
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addAction(array $fields): ?array
	{
		$result = $this->existsByFilter([
			'=CODE' => $fields['CODE'],
		]);
		if ($result->isSuccess())
		{
			$this->addError($this->getErrorDublicateFieldCode());

			return null;
		}

		$result = $this->checkDefaultValue($fields);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			$result = parent::add($fields);
		}
		catch (DuplicateEntryException)
		{
			$result = new Result();
			$result->addError(new Error('A measure with code \'' . $fields['CODE'] . '\' already exists.'));
		}
		catch (SqlQueryException)
		{
			$result = new Result();
			$result->addError(new Error('Internal error adding measure. Try adding again.'));
		}

		if (!$result->isSuccess())
		{
			$conn->rollbackTransaction();
			$this->addErrors($result->getErrors());

			return null;
		}
		$conn->commitTransaction();

		return [
			$this->getServiceItemName() => $this->get($result->getPrimary())
		];
	}

	/**
	 * @param int $id
	 * @param array $fields
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateAction(int $id, array $fields): ?array
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());

			return null;
		}

		$result = $this->checkMeasureBeforeUpdate($id, $fields);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$result = parent::update($id, $fields);
		if ($result->isSuccess())
		{
			return [$this->getServiceItemName() => $this->get($id)];
		}

		$this->addErrors($result->getErrors());

		return null;
	}

	/**
	 * @param int $id
	 * @return bool|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function deleteAction(int $id): ?bool
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());

			return null;
		}

		$r = parent::delete($id);
		if ($r->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */
	//endregion

	protected function checkDefaultValue(array $fields): Result
	{
		$r = new Result();

		/**
		 * to ensure compatibility
		 * the correct option would be to mark the field as required
		 * @see \Bitrix\Catalog\RestView\Measure::getFields().IS_DEFAULT.ATTRIBUTES.REQUIRED
		 */
		$fields['IS_DEFAULT'] = $fields['IS_DEFAULT'] ?? 'N';

		if ($fields['IS_DEFAULT'] === 'Y')
		{
			$exist = $this->existsByFilter([
				'=IS_DEFAULT' => $fields['IS_DEFAULT'],
			]);
			if ($exist->isSuccess())
			{
				$r->addError(new Error('default value can be set once [isDefault]', 200600000010));
			}
		}

		return $r;
	}

	protected function getEntityTable(): DataManager
	{
		return new MeasureTable();
	}

	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_STORE_VIEW))
		{
			$r->addError($this->getErrorModifyAccessDenied());
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!(
				$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
				|| $this->accessController->check(ActionDictionary::ACTION_STORE_VIEW)
			)
		)
		{
			$r->addError($this->getErrorReadAccessDenied());
		}
		return $r;
	}

	protected function checkMeasureBeforeUpdate(int $id, array $fields): Result
	{
		if (isset($fields['CODE']))
		{
			$existsResult = $this->existsByFilter([
				'!=ID' => $id,
				'=CODE' => $fields['CODE'],
			]);
			if ($existsResult->isSuccess())
			{
				$result = new Result();
				$result->addError($this->getErrorDublicateFieldCode());

				return $result;
			}
		}

		return $this->checkDefaultValue($fields);
	}

	private function getErrorDublicateFieldCode(): Error
	{
		return new Error('Duplicate entry for key [code]', 200600000000);
	}

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::MEASURE_ENTITY_NOT_EXISTS;
	}
}
