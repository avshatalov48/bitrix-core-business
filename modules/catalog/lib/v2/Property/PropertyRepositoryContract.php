<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\RepositoryContract;

/**
 * Interface PropertyRepositoryContract
 *
 * @package Bitrix\Catalog\v2\Property
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface PropertyRepositoryContract extends RepositoryContract
{
	public function getCollectionByParent(BaseIblockElementEntity $entity): PropertyCollection;
}