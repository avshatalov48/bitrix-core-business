<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Item;

final class LatestActivityData
{
	private ?int $id = null;
	private int $spaceId;
	private int $userId;
	private int $activityId;

	public static function createFromRecentActivityData(RecentActivityData $recentActivityData): self
	{
		return (new self())
			->setSpaceId($recentActivityData->getSpaceId())
			->setUserId($recentActivityData->getUserId())
			->setActivityId($recentActivityData->getId() ?? 0)
		;
	}

	public static function createFromQueryResult(array $queryResult): self
	{
		return (new self())
			->setId($queryResult['ID'])
			->setUserId($queryResult['USER_ID'])
			->setSpaceId($queryResult['SPACE_ID'])
			->setActivityId($queryResult['ACTIVITY_ID'])
		;
	}

	public function setId(?int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setSpaceId(int $spaceId): self
	{
		$this->spaceId = $spaceId;

		return $this;
	}

	public function getSpaceId(): int
	{
		return $this->spaceId;
	}

	public function setUserId(int $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setActivityId(int $activityId): self
	{
		$this->activityId = $activityId;

		return $this;
	}

	public function getActivityId(): int
	{
		return $this->activityId;
	}
}