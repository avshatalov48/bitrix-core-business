<?php

namespace Bitrix\Socialnetwork\Space\List\RecentSearch;

use Bitrix\Main\Type\DateTime;

final class SpaceSearchData
{
	public function __construct(private int $spaceId, private ?DateTime $lastSearchDate = null)
	{}

	public function getSpaceId(): int
	{
		return $this->spaceId;
	}

	public function getLastSearchDate(): ?DateTime
	{
		return $this->lastSearchDate;
	}
}