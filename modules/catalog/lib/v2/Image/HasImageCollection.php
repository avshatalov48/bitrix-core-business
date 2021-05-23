<?php

namespace Bitrix\Catalog\v2\Image;

/**
 * Interface HasImageCollection
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface HasImageCollection
{
	public function getImageCollection(): ImageCollection;

	public function setImageCollection(ImageCollection $imageCollection);
}