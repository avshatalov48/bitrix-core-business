<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Link;

class LinkParts
{
	public function __construct(
		public readonly int     $userId,
		public readonly int     $collabId,
		public readonly ?int    $entityId = null,
		public readonly ?string $view = null
	)
	{

	}

}