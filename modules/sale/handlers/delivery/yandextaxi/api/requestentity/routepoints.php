<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class RoutePoints
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class RoutePoints extends RequestEntity
{
	/** @var RoutePoint */
	protected $source;

	/** @var RoutePoint */
	protected $destination;

	/** @var RoutePoint */
	protected $return;

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
