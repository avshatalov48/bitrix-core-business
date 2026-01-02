<?php

namespace Bitrix\Im\V2\Recent\Config;

class RecentConfig
{
	private ?string $ownSectionName = null;

	public function __construct(
		public readonly bool $useDefaultRecentSection = true,
		public readonly bool $hasOwnRecentSection = false,
	){}

	public function setOwnSectionName(string $name): self
	{
		$this->ownSectionName = $name;

		return $this;
	}

	public function getOwnSectionName(): ?string
	{
		return $this->ownSectionName;
	}
}