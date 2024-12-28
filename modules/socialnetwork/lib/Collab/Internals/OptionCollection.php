<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Internals;

class OptionCollection extends EO_CollabOption_Collection
{
	public function find(int $collabId, string $name): ?OptionEntity
	{
		return $this->getByPrimary([
			'COLLAB_ID' => $collabId,
			'NAME' => $name,
			]);
	}
}
