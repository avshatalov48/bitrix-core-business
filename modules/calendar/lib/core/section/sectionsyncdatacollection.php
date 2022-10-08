<?php

namespace Bitrix\Calendar\Core\Section;

use Bitrix\Calendar\Core\Base\Collection;

class SectionSyncDataCollection extends Collection
{
	/**
	 * @param $name
	 * @return SectionSyncData|null
	 *
	 */
	public function getByName($name): ?SectionSyncData
	{
		foreach ($this->collection as $item)
		{
			if ($item->getConnectionType() === $name)
			{
				return $item;
			}
		}

		return null;
	}
}