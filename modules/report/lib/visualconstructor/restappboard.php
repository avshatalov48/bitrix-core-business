<?php

namespace Bitrix\Report\VisualConstructor;

class RestAppBoard extends AnalyticBoard
{
	protected $placement;
	protected $restAppId;
	protected $placementHandlerId;

	/**
	 * @return string
	 */
	public function getPlacement()
	{
		return $this->placement;
	}

	/**
	 * @param string $placement
	 */
	public function setPlacement($placement): void
	{
		$this->placement = $placement;
	}

	/**
	 * @return int
	 */
	public function getRestAppId()
	{
		return $this->restAppId;
	}

	/**
	 * @param int $restAppId
	 */
	public function setRestAppId($restAppId): void
	{
		$this->restAppId = $restAppId;
	}

	/**
	 * @return int
	 */
	public function getPlacementHandlerId()
	{
		return $this->placementHandlerId;
	}

	/**
	 * @param int $placementHandlerId
	 */
	public function setPlacementHandlerId($placementHandlerId): void
	{
		$this->placementHandlerId = $placementHandlerId;
	}

	public function getDisplayComponentName()
	{
		return 'bitrix:app.layout';
	}

	public function getDisplayComponentParams()
	{
		return [
			'PLACEMENT' => $this->getPlacement(),
			'PLACEMENT_OPTIONS' => [],
			'ID' => $this->getRestAppId(),
			'PLACEMENT_ID' => $this->getPlacementHandlerId(),
		];
	}
}