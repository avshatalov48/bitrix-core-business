<?php

namespace Bitrix\Catalog\v2\Image;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Main\FileTable;
use Bitrix\Main\Result;

/**
 * Class ImageRepository
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class ImageRepository implements ImageRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\Image\ImageFactory */
	protected $factory;

	public function __construct(ImageFactory $factory)
	{
		$this->factory = $factory;
	}

	public function getEntityById(int $id, string $type = null): ?BaseEntity
	{
		if ($id <= 0)
		{
			throw new \OutOfRangeException($id);
		}

		$entities = $this->getEntitiesBy([
			'filter' => [
				'=ID' => $id,
			],
			'limit' => 1,
		], $type);

		return reset($entities) ?: null;
	}

	public function getEntitiesBy($params, string $type = null): array
	{
		$entities = [];

		foreach ($this->getList((array)$params) as $item)
		{
			$entities[] = $this->createEntity($item, $type);
		}

		return $entities;
	}

	public function save(BaseEntity ...$entities): Result
	{
		return new Result();
	}

	public function delete(BaseEntity ...$entities): Result
	{
		foreach ($entities as $entity)
		{
			\CFile::Delete($entity->getId());
		}

		return new Result();
	}

	public function getCollectionByParent(BaseIblockElementEntity $element): ImageCollection
	{
		$collection = $this->factory->createCollection();
		if ($element->isNew())
		{
			return $collection;
		}

		$items = [];

		$previewValue = (int)$element->getField(PreviewImage::CODE);
		if ($previewValue > 0)
		{
			$previewEntity = $this->getEntityById($previewValue, ImageFactory::PREVIEW_IMAGE);
			if ($previewEntity)
			{
				$items[] = $previewEntity;
			}
		}

		$detailValue = (int)$element->getField(DetailImage::CODE);
		if ($detailValue > 0)
		{
			$detailEntity = $this->getEntityById($detailValue, ImageFactory::DETAIL_IMAGE);
			if ($detailEntity)
			{
				$items[] = $detailEntity;
			}
		}

		foreach ($this->getMorePhotoEntities($element) as $item)
		{
			$items[] = $item;
		}

		if (!empty($items))
		{
			$collection->add(...$items);
		}

		return $collection;
	}

	private function getMorePhotoEntities(BaseIblockElementEntity $element): array
	{
		$morePhotos = [];
		$property = $element->getPropertyCollection()->findByCode(MorePhotoImage::CODE);
		if (!$property)
		{
			return [];
		}

		$morePhotoValueCollection = $property->getPropertyValueCollection();
		$morePhotoIds = $morePhotoValueCollection->getValues();
		if (empty($morePhotoIds))
		{
			return [];
		}
		$fields = $this->getList([
			'filter' => [
				'=ID' => $morePhotoIds,
			],
		]);
		if (empty($fields))
		{
			return [];
		}


		$fields = array_combine(array_column($fields, 'ID'), $fields);
		/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValue $value */
		foreach ($morePhotoValueCollection as $value)
		{
			$fileId = (int)$value->getValue();
			if ($fileId > 0 && isset($fields[$fileId]))
			{
				$fileFields = $fields[$fileId];
				if (empty($fileFields))
				{
					continue;
				}
				$fileFields['PROPERTY_VALUE_ID'] = $value->getId();
				$morePhotos[] = $this->createEntity($fileFields);
			}
		}

		return $morePhotos;
	}

	protected function getList(array $params): array
	{
		$files = [];
		$filesRaw = FileTable::getList($params);
		while ($file = $filesRaw->fetch())
		{
			$file['SRC'] = \CFile::getFileSRC($file);
			$file['FILE_STRUCTURE'] = $file;
			$files[] = $file;
		}

		return $files;
	}

	protected function createEntity(array $fields = [], string $type = null): BaseImage
	{
		$entity = $this->factory->createEntity($type);

		$entity->initFields($fields);

		return $entity;
	}
}