<?php

namespace Bitrix\Catalog\v2\Image;

use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class ImageFactory
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class ImageFactory
{
	public const MORE_PHOTO_IMAGE = MorePhotoImage::class;
	public const DETAIL_IMAGE = DetailImage::class;
	public const PREVIEW_IMAGE = PreviewImage::class;
	public const IMAGE_COLLECTION = ImageCollection::class;

	protected $container;

	/**
	 * ImageFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @param string|null $type
	 * @return \Bitrix\Catalog\v2\Image\BaseImage
	 */
	public function createEntity(string $type = null): BaseImage
	{
		switch ($type)
		{
			case self::PREVIEW_IMAGE:
				return $this->container->make(self::PREVIEW_IMAGE);

			case self::DETAIL_IMAGE:
				return $this->container->make(self::DETAIL_IMAGE);
		}

		return $this->container->make(self::MORE_PHOTO_IMAGE);
	}

	/**
	 * @return \Bitrix\Catalog\v2\Image\ImageCollection
	 */
	public function createCollection(): ImageCollection
	{
		return $this->container->make(self::IMAGE_COLLECTION);
	}
}