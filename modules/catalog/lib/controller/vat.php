<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\VatTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * @todo temporary - remake it when Vat gets implemented as a \Bitrix\Catalog\Model\Entity
 */
final class Vat extends Controller
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use CheckExists; // default implementation of existence check

	//region Actions

	/**
	 * @param array $fields
	 * @return array|null
	 */
	public function addAction(array $fields): ?array
	{
		$application = self::getApplication();
		$application->ResetException();

		$addResult = \CCatalogVat::Add($fields);
		if (!$addResult)
		{
			if ($application->GetException())
			{
				$this->addError(new Error($application->GetException()->GetString()));
			}
			else
			{
				$this->addError(new Error('Error adding VAT'));
			}

			return null;
		}

		return [$this->getServiceItemName() => $this->get($addResult)];
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

		$updateResult = \CCatalogVat::Update($id, $fields);
		if (!$updateResult)
		{
			$this->addError(new Error('Error updating VAT'));

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

		$deleteResult = \CCatalogVat::Delete($id);
		if (!$deleteResult)
		{
			$this->addError(new Error('Error deleting VAT'));

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
		return new VatTable();
	}

	/**
	 * @inheritDoc
	 */
	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_VAT_EDIT))
		{
			$r->addError($this->getErrorModifyAccessDenied());
		}

		return $r;
	}

	/**
	 * @inheritDoc
	 */
	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& !$this->accessController->check(ActionDictionary::ACTION_VAT_EDIT)
		)
		{
			$r->addError($this->getErrorReadAccessDenied());
		}

		return $r;
	}

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::VAT_ENTITY_NOT_EXISTS;
	}
}
