<?php

namespace Bitrix\Rest\V3\Realisation\Controllers;

use Bitrix\Main\Composite\Internals\Locker;
use Bitrix\Main\SystemException;
use Bitrix\Rest\V3\Attributes\Scope;
use Bitrix\Rest\V3\Controllers\RestController;
use Bitrix\Rest\V3\Documentation\DocumentationManager;
use Bitrix\Rest\V3\Interaction\Response\ArrayResponse;
use Bitrix\Rest\V3\CacheManager;

class Documentation extends RestController
{
	private const DOCUMENTATION_CACHE_KEY = 'rest.v3.documentation.cache.key';

	#[Scope(\CRestUtil::GLOBAL_SCOPE)]
	public function openApiAction(): ArrayResponse
	{
		if (!Locker::lock(self::DOCUMENTATION_CACHE_KEY))
		{
			throw new SystemException('Generation in progress.');
		}

		$result = CacheManager::get(self::DOCUMENTATION_CACHE_KEY);
		if ($result === null)
		{
			$manager = new DocumentationManager();
			$result = $manager->generateDataForJson();
			CacheManager::set(self::DOCUMENTATION_CACHE_KEY, $result);
		}

		return (new ArrayResponse($result))->setShowDebugInfo(false)->setShowRawData(true);
	}
}
