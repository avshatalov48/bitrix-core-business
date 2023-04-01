<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class RoutePoint
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class RoutePoint extends RequestEntity
{
	/** @var int */
	protected $id;

	/** @var int */
	protected $pointId;

	/** @var int */
	protected $visitOrder;

	/** @var string */
	protected $type;

	/** @var Contact */
	protected $contact;

	/** @var Address */
	protected $address;

	/** @var bool */
	protected $skipConfirmation;

	/** @var VisitedAt */
	protected ?VisitedAt $visitedAt = null;

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return RoutePoint
	 */
	public function setId(int $id): RoutePoint
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @param Contact $contact
	 * @return RoutePoint
	 */
	public function setContact(Contact $contact): RoutePoint
	{
		$this->contact = $contact;

		return $this;
	}

	/**
	 * @param Address $address
	 * @return RoutePoint
	 */
	public function setAddress(Address $address): RoutePoint
	{
		$this->address = $address;

		return $this;
	}

	/**
	 * @return VisitedAt|null
	 */
	public function getVisitedAt(): ?VisitedAt
	{
		return $this->visitedAt;
	}

	/**
	 * @param VisitedAt|null $visitedAt
	 * @return RoutePoint
	 */
	public function setVisitedAt(?VisitedAt $visitedAt): RoutePoint
	{
		$this->visitedAt = $visitedAt;

		return $this;
	}

	/**
	 * @param bool $skipConfirmation
	 * @return RoutePoint
	 */
	public function setSkipConfirmation(bool $skipConfirmation): RoutePoint
	{
		$this->skipConfirmation = $skipConfirmation;

		return $this;
	}

	/**
	 * @param int $pointId
	 * @return RoutePoint
	 */
	public function setPointId(int $pointId): RoutePoint
	{
		$this->pointId = $pointId;
		return $this;
	}

	/**
	 * @param int $visitOrder
	 * @return RoutePoint
	 */
	public function setVisitOrder(int $visitOrder): RoutePoint
	{
		$this->visitOrder = $visitOrder;
		return $this;
	}

	/**
	 * @param string $type
	 * @return RoutePoint
	 */
	public function setType(string $type): RoutePoint
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}
}
