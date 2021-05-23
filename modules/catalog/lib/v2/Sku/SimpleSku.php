<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Fields\FieldStorage;
use Bitrix\Catalog\v2\Image\ImageCollection;
use Bitrix\Catalog\v2\Property\PropertyCollection;
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
	protected function loadPropertyCollection(): PropertyCollection
	{
		return $this->getParent()->getPropertyCollection();
	}

	protected function unsetPropertyCollection(): BaseIblockElementEntity
	{
		if ($parent = $this->getParent())
		{
			$parent->unsetPropertyCollection();
		}

		return parent::unsetPropertyCollection();
	}

	/**
	 * @return \Bitrix\Catalog\v2\Image\ImageCollection|\Bitrix\Catalog\v2\Image\BaseImage[]
	 */
	protected function loadImageCollection(): ImageCollection
	{
		return $this->getParent()->getImageCollection();
	}

	protected function unsetImageCollection(): BaseIblockElementEntity
	{
		if ($parent = $this->getParent())
		{
			$parent->unsetImageCollection();
		}

		return parent::unsetImageCollection();
	}

	public function setPropertyCollection(PropertyCollection $propertyCollection): BaseIblockElementEntity
	{
		// avoiding reinitialize of property collection with our simple sku parent
		$this->propertyCollection = $propertyCollection;

		return $this;
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