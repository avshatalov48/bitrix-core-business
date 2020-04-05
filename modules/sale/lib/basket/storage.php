<?php

namespace Bitrix\Sale\Basket;

use Bitrix\Sale;
use Bitrix\Main;

class Storage
{
	/** @var array $instance */
	private static $instance = array();
	/** @var Sale\Basket $basket */
	protected $basket;
	/** @var Sale\Basket $orderableBasket */
	protected $orderableBasket;

	private $fUserId;
	private $siteId;

	private function __construct($fUserId, $siteId)
	{
		$this->fUserId = $fUserId;
		$this->siteId = $siteId;
	}

	private function __clone()
	{
	}

	protected static function getHash($fUserId, $siteId)
	{
		return "{$fUserId}_{$siteId}";
	}

	protected function getFUserId()
	{
		return $this->fUserId;
	}

	protected function getSiteId()
	{
		return $this->siteId;
	}

	public static function getInstance($fUserId, $siteId)
	{
		if (empty($fUserId))
		{
			throw new Main\ArgumentNullException('fUserId');
		}

		if (empty($siteId))
		{
			throw new Main\ArgumentNullException('siteId');
		}

		$hash = static::getHash($fUserId, $siteId);

		if (!isset(static::$instance[$hash]))
		{
			static::$instance[$hash] = new static($fUserId, $siteId);
		}

		return static::$instance[$hash];
	}

	public function getBasket()
	{
		if (!isset($this->basket))
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Basket $basketClassName */
			$basketClassName = $registry->getBasketClassName();

			$this->basket = $basketClassName::loadItemsForFUser($this->getFUserId(), $this->getSiteId());
		}

		return $this->basket;
	}

	public function getOrderableBasket()
	{
		if (!isset($this->orderableBasket))
		{
			/** @var Sale\Basket $basketClone */
			$basketClone = $this->getBasket()->createClone();
			$this->orderableBasket = $basketClone->getOrderableItems();
		}

		return $this->orderableBasket;
	}
}