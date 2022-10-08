<?php

namespace Bitrix\Location\Entity;

use Bitrix\Location\Geometry\Type\BaseGeometry;
use Bitrix\Location\Geometry\Type\Point;

/**
 * Class Area
 *
 * @package Bitrix\Location\Entity
 */
final class Area
{
	/** @var string */
	private $type;

	/** @var string|null */
	private $code;

	/** @var int */
	private $sort;

	/** @var BaseGeometry */
	private $geometry;

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return Area
	 */
	public function setType(string $type): Area
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCode(): ?string
	{
		return $this->code;
	}

	/**
	 * @param string|null $code
	 * @return Area
	 */
	public function setCode(?string $code): Area
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSort(): int
	{
		return $this->sort;
	}

	/**
	 * @param int $sort
	 * @return Area
	 */
	public function setSort(int $sort): Area
	{
		$this->sort = $sort;
		return $this;
	}

	/**
	 * @return BaseGeometry
	 */
	public function getGeometry(): BaseGeometry
	{
		return $this->geometry;
	}

	/**
	 * @param BaseGeometry $geometry
	 * @return Area
	 */
	public function setGeometry(BaseGeometry $geometry): Area
	{
		$this->geometry = $geometry;
		return $this;
	}

	/**
	 * @param Point $point
	 * @return bool|null
	 */
	public function containsPoint(Point $point): ?bool
	{
		if (!$this->geometry)
		{
			return null;
		}

		return $this->geometry->contains($point);
	}
}
