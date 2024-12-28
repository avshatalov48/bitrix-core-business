<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control;

use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Control\GroupResult;

class CollabResult extends GroupResult
{
	public function getCollab(): ?Collab
	{
		$group = $this->getGroup();
		if (!$group instanceof Collab)
		{
			return null;
		}

		return $group;
	}
}