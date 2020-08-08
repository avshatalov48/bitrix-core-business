<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class RoutePoints
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class RoutePoints implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var RoutePoint */
	private $source;

	/** @var RoutePoint */
	private $destination;

	/** @var RoutePoint */
	private $return;

	/**
	 * @return RoutePoint
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param RoutePoint $source
	 * @return RoutePoints
	 */
	public function setSource(RoutePoint $source): RoutePoints
	{
		$this->source = $source;

		return $this;
	}

	/**
	 * @return RoutePoint
	 */
	public function getDestination()
	{
		return $this->destination;
	}

	/**
	 * @param RoutePoint $destination
	 * @return RoutePoints
	 */
	public function setDestination(RoutePoint $destination): RoutePoints
	{
		$this->destination = $destination;

		return $this;
	}

	/**
	 * @return RoutePoint
	 */
	public function getReturn()
	{
		return $this->return;
	}

	/**
	 * @param RoutePoint $return
	 * @return RoutePoints
	 */
	public function setReturn(RoutePoint $return): RoutePoints
	{
		$this->return = $return;

		return $this;
	}
}
