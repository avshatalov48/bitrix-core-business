<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\GroupLangTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Result;

class PriceTypeLang extends Controller
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use PriceTypeRights;

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
			$this->addErrorEntityNotExists();

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
			$this->addErrorEntityNotExists();

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

		if (isset($fields['CATALOG_GROUP_ID']))
		{
			$priceTypeId = (int)$fields['CATALOG_GROUP_ID'];
			$priceType = \Bitrix\Catalog\GroupTable::getById($priceTypeId)->fetch();
			if (!$priceType)
			{
				$result->addError(new Error('The specified price type does not exist', ErrorCode::PRICE_TYPE_ENTITY_NOT_EXISTS));
			}
		}

		if (isset($fields['LANG']))
		{
			$language = LanguageTable::getById($fields['LANG'])->fetch();

			if (!$language)
			{
				$result->addError(new Error('The specified language does not exist', ErrorCode::PRICE_TYPE_LANG_LANGUAGE_NOT_EXISTS));
			}
		}

		return $result;
	}

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::PRICE_TYPE_LANG_ENTITY_NOT_EXISTS;
	}
}
