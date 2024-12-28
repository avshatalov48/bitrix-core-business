<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Socialnetwork\Collab\Internals\CollabOptionTable;
use Bitrix\Socialnetwork\Collab\Property\Option;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class CollabOptionProvider
{
	use InstanceTrait;

	protected const CACHE_TTL = 10;

	/** @return Option[] */
	public function get(int $collabId): array
	{
		$collabOptions = CollabOptionTable::query()
			->setSelect(['NAME', 'VALUE'])
			->where('COLLAB_ID', $collabId)
			->setCacheTtl(static::CACHE_TTL)
			->exec()
			->fetchAll();

		$options = [];

		foreach ($collabOptions as $collabOption)
		{
			$options[] = new Option($collabOption['NAME'], $collabOption['VALUE']);
		}

		return $options;
	}
}
