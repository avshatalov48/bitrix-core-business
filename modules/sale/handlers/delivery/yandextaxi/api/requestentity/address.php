<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class Address
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class Address extends RequestEntity
{
	/** @var string */
	protected $fullname;

	/** @var string */
	protected $country;

	/** @var string */
	protected $city;

	/** @var string */
	protected $street;

	/** @var string */
	protected $building;

	/** @var string */
	protected $porch;

	/** @var int */
	protected $floor;

	/** @var int */
	protected $flat;

	/** @var string */
	protected $doorCode;

	/** @var string */
	protected $comment;

	/** @var array */
	protected $coordinates;

	/**
	 * @return string
	 */
	public function getFullName()
	{
		return $this->fullname;
	}

	/**
	 * @param string $fullname
	 * @return Address
	 */
	public function setFullName(string $fullname): Address
	{
		$this->fullname = $fullname;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param string $country
	 * @return Address
	 */
	public function setCountry(string $country): Address
	{
		$this->country = $country;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @param string $city
	 * @return Address
	 */
	public function setCity(string $city): Address
	{
		$this->city = $city;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getStreet()
	{
		return $this->street;
	}

	/**
	 * @param string $street
	 * @return Address
	 */
	public function setStreet(string $street): Address
	{
		$this->street = $street;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBuilding()
	{
		return $this->building;
	}

	/**
	 * @param string $building
	 * @return Address
	 */
	public function setBuilding(string $building): Address
	{
		$this->building = $building;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPorch()
	{
		return $this->porch;
	}

	/**
	 * @param string $porch
	 * @return Address
	 */
	public function setPorch(string $porch): Address
	{
		$this->porch = $porch;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getFloor()
	{
		return $this->floor;
	}

	/**
	 * @param int $floor
	 * @return Address
	 */
	public function setFloor(int $floor): Address
	{
		$this->floor = $floor;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getFlat()
	{
		return $this->flat;
	}

	/**
	 * @param int $flat
	 * @return Address
	 */
	public function setFlat(int $flat): Address
	{
		$this->flat = $flat;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDoorCode()
	{
		return $this->doorCode;
	}

	/**
	 * @param string $doorCode
	 * @return Address
	 */
	public function setDoorCode(string $doorCode): Address
	{
		$this->doorCode = $doorCode;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @param string $comment
	 * @return Address
	 */
	public function setComment(string $comment): Address
	{
		$this->comment = $comment;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getCoordinates()
	{
		return $this->coordinates;
	}

	/**
	 * @param array $coordinates
	 * @return Address
	 */
	public function setCoordinates(array $coordinates): Address
	{
		$this->coordinates = $coordinates;

		return $this;
	}
}
