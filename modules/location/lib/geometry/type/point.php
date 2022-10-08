<?php

namespace Bitrix\Location\Geometry\Type;

use InvalidArgumentException;

class Point extends BaseGeometry
{
	/** @var float */
	protected $lat;

	/** @var float */
	protected $lng;

	/**
	 * @param float $lat -90.0 .. +90.0
	 * @param float $lng -180.0 .. +180.0
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(float $lat, float $lng)
	{
		if (!$this->isValidLatitude($lat))
		{
			throw new InvalidArgumentException('Latitude value must be numeric -90.0 .. +90.0 (given: ' . $lat . ')');
		}

		if (!$this->isValidLongitude($lng))
		{
			throw new InvalidArgumentException(
				'Longitude value must be numeric -180.0 .. +180.0 (given: ' . $lng . ')'
			);
		}

		$this->lat = $lat;
		$this->lng = $lng;
	}

	/**
	 * @return float
	 */
	public function getLat(): float
	{
		return $this->lat;
	}

	/**
	 * @return float
	 */
	public function getLng(): float
	{
		return $this->lng;
	}

	/**
	 * @inheritDoc
	 */
	public function asArray(): array
	{
		return [
			$this->lng,
			$this->lat,
		];
	}

	/**
	 * @param float $latitude
	 * @return bool
	 */
	protected function isValidLatitude(float $latitude): bool
	{
		return $this->isNumericInBounds($latitude, -90.0, 90.0);
	}

	/**
	 * @param float $longitude
	 * @return bool
	 */
	protected function isValidLongitude(float $longitude): bool
	{
		return $this->isNumericInBounds($longitude, -180.0, 180.0);
	}

	/**
	 * Checks if the given value is (1) numeric, and (2) between lower
	 * and upper bounds (including the bounds values).
	 */
	protected function isNumericInBounds(float $value, float $lower, float $upper): bool
	{
		return !($value < $lower || $value > $upper);
	}
}
