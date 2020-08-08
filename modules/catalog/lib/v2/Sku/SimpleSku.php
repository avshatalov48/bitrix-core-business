<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\FieldStorage;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Result;

/**
 * Class SimpleSku
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class SimpleSku extends BaseSku
{
	/**
	 * @return \Bitrix\Catalog\v2\BaseIblockElementEntity
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function getParent(): BaseEntity
	{
		$parent = parent::getParent();

		if (!$parent)
		{
			throw new NotSupportedException(sprintf('{%s} must have a parent.', static::class));
		}

		return $parent;
	}

	protected function createFieldStorage(): FieldStorage
	{
		return $this->getParent()->getFieldStorage();
	}

	/**
	 * @return \Bitrix\Catalog\v2\Property\PropertyCollection|\Bitrix\Catalog\v2\Property\Property[]
	 */
	protected function loadPropertyCollection(): BaseCollection
	{
		return $this->getParent()->getPropertyCollection();
	}

	public function deleteInternal(): Result
	{
		$result = new Result();

		// delete child collections without entity fields itself (it was deleted with parent product entity)
		foreach ($this->getChildCollections(true) as $childCollection)
		{
			$res = $childCollection->deleteInternal();

			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}
}