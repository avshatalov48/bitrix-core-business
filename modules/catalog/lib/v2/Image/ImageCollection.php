<?php

namespace Bitrix\Catalog\v2\Image;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Main\NotSupportedException;

/**
 * Class ImageCollection
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class ImageCollection extends BaseCollection
{
	/** @var ImageFactory */
	protected $factory;

	public function __construct(ImageFactory $factory)
	{
		$this->factory = $factory;
	}

	public function create(string $type = null): BaseImage
	{
		if ($type === ImageFactory::DETAIL_IMAGE || $type === ImageFactory::PREVIEW_IMAGE)
		{
			$entity = $this->findByType($type);
			if ($entity)
			{
				throw new NotSupportedException(sprintf(
					'Collection {%s} already contains {%s} entity.', static::class, $type
				));
			}
		}

		$image = $this->factory->createEntity($type);

		$this->add($image);

		return $image;
	}

	public function getDetailImage(): BaseImage
	{
		$detailImage = $this->findByType(ImageFactory::DETAIL_IMAGE);

		if (!$detailImage)
		{
			$detailImage = $this->create(ImageFactory::DETAIL_IMAGE);
		}

		return $detailImage;
	}

	public function getPreviewImage(): BaseImage
	{
		$previewImage = $this->findByType(ImageFactory::PREVIEW_IMAGE);

		if (!$previewImage)
		{
			$previewImage = $this->create(ImageFactory::PREVIEW_IMAGE);
		}

		return $previewImage;
	}

	protected function findByType(string $type): ?BaseImage
	{
		/** @var \Bitrix\Catalog\v2\Image\BaseImage $item */
		foreach ($this->getIterator() as $item)
		{
			if ($item instanceof $type)
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * @return MorePhotoImage[]
	 */
	public function getMorePhotos(): array
	{
		$morePhotos = [];
		foreach ($this->getIterator() as $item)
		{
			if ($item instanceof MorePhotoImage)
			{
				$morePhotos[] = $item;
			}
		}

		return $morePhotos;
	}

	public function getFrontImage(): ?BaseImage
	{
		$picture = $this->getDetailImage();
		if (!$picture->isNew())
		{
			return $picture;
		}

		$picture = $this->getPreviewImage();
		if (!$picture->isNew())
		{
			return $picture;
		}

		/** @var BaseImage $picture */
		$picture = $this->getFirst();

		return !$picture->isNew() ? $picture : null;
	}

	public function getValues(): array
	{
		$values = [];

		/** @var \Bitrix\Catalog\v2\Image\BaseImage $image */
		foreach ($this->getIterator() as $image)
		{
			$values[] = $image->isNew() ? $image->getFileStructure() : $image->getId();
		}

		return $values;
	}

	/**
	 * @param mixed $values
	 * @return $this
	 */
	public function setValues(array $values): self
	{
		$this->removeOldValues($values);
		$this->addValues($values);

		return $this;
	}

	public function addValues(array $values): self
	{
		foreach ($this->prepareValues($values) as $value)
		{
			if (is_array($value))
			{
				$this->addValue($value);
			}
		}

		return $this;
	}

	public function addValue(array $value): void
	{
		if (!$value)
		{
			return;
		}

		$entity = $this->create();
		$entity->setFileStructure($value);
	}

	private function prepareValues(array $values): array
	{
		if (isset($values['name']) || isset($values['tmp_name']))
		{
			$values = [$values];
		}

		return $values;
	}

	private function removeOldValues(array $values): void
	{
		$valuesToSave = [];

		foreach ($this->prepareValues($values) as $value)
		{
			if (!empty($value) && is_numeric($value))
			{
				$valuesToSave[] = (int)$value;
			}
		}

		foreach ($this->getIterator() as $entity)
		{
			if ($entity->isNew() || !in_array($entity->getId(), $valuesToSave, true))
			{
				$entity->remove();
			}
		}
	}
}