<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\GroupAccessTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

class PriceTypeGroup extends Controller
{
	use PriceTypeRights;

	// region Actions

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return ['PRICE_TYPE_GROUP' => $this->getViewFields()];
	}

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

		$addResult = GroupAccessTable::add($fields);
		if (!$addResult)
		{
			$this->addErrors($addResult->getErrors());

			return null;
		}

		return ['PRICE_TYPE_GROUP' => $this->get($addResult->getId())];
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
			'PRICE_TYPE_GROUPS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
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

		$deleteResult = GroupAccessTable::delete($id);
		if (!$deleteResult)
		{
			$this->addErrors($deleteResult->getErrors());

			return null;
		}

		return true;
	}
	// endregion

	/**
	 * @param array $fields
	 * @return Result
	 */
	private function checkFields(array $fields): Result
	{
		$result = new Result();

		$priceTypeId = $fields['CATALOG_GROUP_ID'];
		$priceType = \Bitrix\Catalog\GroupTable::getById($priceTypeId)->fetch();
		if (!$priceType)
		{
			$result->addError(new Error('The specified price type does not exist'));
		}

		$groupId = $fields['GROUP_ID'];
		$group = GroupTable::getById($groupId)->fetch();
		if (!$group)
		{
			$result->addError(new Error('The specified group does not exist'));
		}

		$accessTypeValues = [GroupAccessTable::ACCESS_BUY, GroupAccessTable::ACCESS_VIEW];
		$access = $fields['ACCESS'];
		if (!in_array($access, $accessTypeValues, true))
		{
			$result->addError(
				new Error(
					'Invalid access type provided. The available values are: '
					. implode(', ', $accessTypeValues)
				)
			);
		}

		$exists = (bool)GroupAccessTable::getRow([
			'select' => ['ID'],
			'filter' => [
				'=CATALOG_GROUP_ID' => $fields['CATALOG_GROUP_ID'],
				'=GROUP_ID' => $fields['GROUP_ID'],
				'=ACCESS' => $fields['ACCESS'],
			]
		]);

		if ($exists)
		{
			$result->addError(new Error('The specified access type for this group already exists'));
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return GroupAccessTable::class;
	}
}
