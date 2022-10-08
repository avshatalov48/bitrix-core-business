<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\MeasureTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Event\EventBindInterface;

final class Measure extends Controller implements EventBindInterface
{
	//region Actions
	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return ['MEASURE' => $this->getViewFields()];
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
		$r = $this->existsByFilter(['CODE'=>$fields['CODE']]);
		if ($r->isSuccess() === false)
		{
			$r = $this->checkDefaultValue($fields);
			if ($r->isSuccess())
			{
				$r = parent::add($fields);
				if ($r->isSuccess())
				{
					return ['MEASURE' => $this->get($r->getPrimary())];
				}
			}
		}
		else
		{
			$r->addError(new Error('Duplicate entry for key [code]'));
		}

		$this->addErrors($r->getErrors());
		return null;
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

		$r = $this->checkDefaultValue($fields);
		if ($r->isSuccess())
		{
			$r = parent::update($id, $fields);
			if ($r->isSuccess())
			{
				return ['MEASURE' => $this->get($id)];
			}
		}

		$this->addErrors($r->getErrors());
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
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation $pageNavigation
	 * @return Page
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listAction(array $select = [], array $filter = [], array $order = [], PageNavigation $pageNavigation): Page
	{
		return new Page('MEASURES',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param $id
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['MEASURE' => $this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
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

		if($fields['IS_DEFAULT'] === 'Y')
		{
			$exist = $this->existsByFilter(['IS_DEFAULT' => $fields['IS_DEFAULT']]);
			if($exist->isSuccess())
			{
				$r->addError(new Error('default value can be set once [isDefault]'));
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
		$r = $this->checkReadPermissionEntity();
		if ($r->isSuccess())
		{
			if (!static::getGlobalUser()->CanDoOperation('catalog_store'))
			{
				$r->addError(new Error('Access Denied', 200040300020));
			}
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (!(static::getGlobalUser()->CanDoOperation('catalog_read') || static::getGlobalUser()->CanDoOperation('catalog_store')))
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}