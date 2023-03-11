<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\GroupLangTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

class PriceTypeLang extends Controller
{
	use PriceTypeRights;

	// region Actions

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return ['PRICE_TYPE_LANG' => $this->getViewFields()];
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
			'PRICE_TYPE_LANGS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param int $id
	 * @return array|null
	 */
	public function getAction(int $id): ?array
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());

			return null;
		}

		return ['PRICE_TYPE_LANG' => $this->get($id)];
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

		$addResult = GroupLangTable::add($fields);
		if (!$addResult)
		{
			$this->addErrors($addResult->getErrors());

			return null;
		}

		return ['PRICE_TYPE_LANG' => $this->get($addResult->getId())];
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

		$updateResult = GroupLangTable::update($id, $fields);
		if (!$updateResult)
		{
			$this->addErrors($updateResult->getErrors());

			return null;
		}

		return ['PRICE_TYPE_LANG' => $this->get($id)];
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

		$deleteResult = GroupLangTable::delete($id);
		if (!$deleteResult)
		{
			$this->addErrors($deleteResult->getErrors());

			return null;
		}

		return true;
	}

	/**
	 * @return Page
	 */
	public function getLanguagesAction(): Page
	{
		$items = $this->getLanguages();

		return new Page('LANGUAGES', $items, LanguageTable::getCount());
	}
	// endregion

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return GroupLangTable::class;
	}

	/**
	 * @inheritDoc
	 */
	protected function checkPermissionEntity($name, $arguments = [])
	{
		if ($name === 'getlanguages')
		{
			$result = $this->checkReadPermissionEntity();
		}
		else
		{
			$result = parent::checkPermissionEntity($name);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getLanguages(): array
	{
		return LanguageTable::getList(
			[
				'select' => ['ACTIVE', 'NAME', 'LID'],
				'order' => ['LID' => 'ASC']
			]
		)->fetchAll();
	}

	/**
	 * @param array $fields
	 * @return Result
	 */
	private function checkFields(array $fields): Result
	{
		$result = new Result();

		$priceTypeId = (int)$fields['CATALOG_GROUP_ID'];
		$priceType = \Bitrix\Catalog\GroupTable::getById($priceTypeId)->fetch();
		if (!$priceType)
		{
			$result->addError(new Error('The specified price type does not exist'));
		}

		$languageId = $fields['LANG'];
		$language = LanguageTable::getById($languageId)->fetch();
		if (!$language)
		{
			$result->addError(new Error('The specified language does not exist'));
		}

		return $result;
	}
}
