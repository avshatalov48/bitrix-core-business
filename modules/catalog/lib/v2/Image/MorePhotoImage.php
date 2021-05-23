<?php

namespace Bitrix\Catalog\v2\Image;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\BaseIblockElementEntity;

/**
 * Class MorePhotoImage
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class MorePhotoImage extends BaseImage
{
	public const CODE = 'MORE_PHOTO';

	public function setFileStructure(array $fileFields): BaseImage
	{
		parent::setFileStructure($fileFields);

		/** @var BaseIblockElementEntity $parent */
		if ($parent = $this->getParent())
		{
			$property = $parent->getPropertyCollection()->findByCode(self::CODE);
			if ($property)
			{
				/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValue $item */
				$item = $property->getPropertyValueCollection()->findByValue($this->getId());

				if ($item)
				{
					$item->setValue($this->getFileStructure());
				}
				else
				{
					$values = $property->getPropertyValueCollection()->getValues();
					$values[] = $this->getFileStructure();
					$property->getPropertyValueCollection()->setValues($values);
				}
			}
		}

		return $this;
	}

	public function remove(): BaseEntity
	{
		/** @var  $parent BaseIblockElementEntity */
		if ($parent = $this->getParent())
		{
			$property = $parent->getPropertyCollection()->findByCode(MorePhotoImage::CODE);
			if ($property)
			{
				$valueCollection = $property->getPropertyValueCollection();

				foreach ($valueCollection as $item)
				{
					if ((int)$item->getValue() === $this->getId())
					{
						$valueCollection->remove($item);
						break;
					}
				}
			}
		}

		return parent::remove();
	}
}