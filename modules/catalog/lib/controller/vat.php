<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\VatTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Event\EventBindInterface;

/**
 * @todo temporary - remake it when Vat gets implemented as a \Bitrix\Catalog\Model\Entity
 */
final class Vat extends Controller
{
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

		return ['VAT' => $this->get($addResult)];
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

		return ['VAT' => $this->get($id)];
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
		return ['VAT' => $this->getViewFields()];
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation|null $pageNavigation
	 * @return Page
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		return new Page(
			'VATS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param $id
	 * @return array|null
	 */
	public function getAction($id): ?array
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['VAT' => $this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}
	//endregion

	/**
	 * @inheritDoc
	 */
	protected function exists($id)
	{
		$r = new Result();
		if (!isset($this->get($id)['ID']))
		{
			$r->addError(new Error('VAT does not exist'));
		}

		return $r;
	}

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
			$r->addError(new Error('Access Denied', 200040300020));
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
			$r->addError(new Error('Access Denied', 200040300010));
		}

		return $r;
	}
}
