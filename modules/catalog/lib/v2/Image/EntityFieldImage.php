<?php

namespace Bitrix\Catalog\v2\Image;

use Bitrix\Catalog\v2\BaseEntity;

/**
 * Class EntityFieldImage
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class EntityFieldImage extends BaseImage
{
	public function setFileStructure(array $fileFields): BaseImage
	{
		parent::setFileStructure($fileFields);

		if ($parent = $this->getParent())
		{
			$parent->setFieldNoDemand(static::CODE, $this->getFileStructure());
		}

		return $this;
	}

	public function setId(int $id): BaseEntity
	{
		if ($parent = $this->getParent())
		{
			$parent->setFieldNoDemand(static::CODE, $id);
		}

		return parent::setId($id);
	}

	public function remove(): BaseEntity
	{
		if ($parent = $this->getParent())
		{
			$parent->setFieldNoDemand(static::CODE, '');
		}

		return parent::remove();
	}
}