<?php

namespace Bitrix\Catalog\v2\Image;

use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\RepositoryContract;

/**
 * Interface ImageRepositoryContract
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface ImageRepositoryContract extends RepositoryContract
{
	public function getCollectionByParent(BaseIblockElementEntity $element): ImageCollection;
}