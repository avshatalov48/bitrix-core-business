<?php

namespace Bitrix\Calendar\Core\Builders\Rooms;

class RoomBuilderFromArray extends RoomBuilder
{
	/**
	 * @var array
	 */
	private array $params;

	/**
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		$params = [
			'ID' => $params['ID'] ?? null,
			'LOCATION_ID' => $params['LOCATION_ID'] ?? null,
			'CAPACITY' => $params['CAPACITY'] ?? null,
			'NECESSITY' => $params['NECESSITY'] ?? null,
			'NAME' => $params['NAME'] ?? null,
			'COLOR' => $params['COLOR'] ?? null,
			'OWNER_ID' => $params['OWNER_ID'] ?? null,
			'ACCESS' => $params['ACCESS'] ?? null,
			'CATEGORY_ID' => $params['CATEGORY_ID'] ?? null,
		];
		$this->params = $params;
	}

	function getId(): int
	{
		return (int)$this->params['ID'];
	}

	function getLocationId(): int
	{
		return (int)$this->params['LOCATION_ID'];
	}

	function getCapacity()
	{
		return $this->params['CAPACITY'];
	}

	function getNecessity()
	{
		return $this->params['NECESSITY'];
	}

	function getName()
	{
		return $this->params['NAME'];
	}

	function getColor()
	{
		return $this->params['COLOR'];
	}

	function getOwnerId(): int
	{
		return (int)$this->params['OWNER_ID'];
	}

	function getAccess()
	{
		return $this->params['ACCESS'];
	}

	function getCategoryId(): int
	{
		return (int)$this->params['CATEGORY_ID'];
	}
}