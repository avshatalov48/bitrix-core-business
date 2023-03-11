<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\RoundingTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Event\EventBindInterface;

final class RoundingRule extends Controller implements EventBindInterface
{
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

		return ['ROUNDING_RULE' => $this->get($addResult->getId())];
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

		return ['ROUNDING_RULE' => $this->get($id)];
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
		return ['ROUNDING_RULE' => $this->getViewFields()];
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation $pageNavigation
	 * @return Page
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		return new Page(
			'ROUNDING_RULES',
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
		if ($r->isSuccess())
		{
			return ['ROUNDING_RULE' => $this->get($id)];
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
		if (isset($this->get($id)['ID']) == false)
		{
			$r->addError(new Error('Rounding does not exist'));
		}

		return $r;
	}

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
			if (!in_array($fields['ROUND_TYPE'], RoundingTable::getRoundTypes()))
			{
				$result->addError(
					new Error(
						'Invalid rounding type provided. The available values are: '
						. implode(', ', RoundingTable::getRoundTypes())
					)
				);
			}
		}

		if (array_key_exists('ROUND_PRECISION', $fields))
		{
			if (!in_array($fields['ROUND_PRECISION'], RoundingTable::getPresetRoundingValues()))
			{
				$result->addError(
					new Error(
						'Invalid rounding precision provided. The available values are: '
						. implode(', ', RoundingTable::getPresetRoundingValues())
					)
				);
			}
		}

		return $result;
	}
}
