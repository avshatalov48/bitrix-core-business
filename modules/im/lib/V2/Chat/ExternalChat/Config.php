<?php

namespace Bitrix\Im\V2\Chat\ExternalChat;

class Config
{
	public function __construct(
		public readonly bool  $hasOwnRecentSection = false,
		public readonly array $permissions = [],
		public readonly bool $isAutoJoinEnabled = false,
	){}
}