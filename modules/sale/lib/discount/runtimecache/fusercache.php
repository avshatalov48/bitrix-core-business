<?php

namespace Bitrix\Sale\Discount\RuntimeCache;


use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

final class FuserCache
{
	/** @var array */
	private $fuserIds = array();
	/** @var FuserCache */
	private static $instance;

	private function __construct()
	{}

	private function __clone()
	{}

	/**
	 * Returns Singleton of FuserCache.
	 * @return FuserCache
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * Returns user by fuserId.
	 * @param int $fuserId Fuser Id.
	 * @return int
	 */
	public function getUserIdById($fuserId)
	{
		if(!isset($this->fuserIds[$fuserId]))
		{
			$this->fuserIds[$fuserId] = \Bitrix\Sale\Fuser::getUserIdById($fuserId);
		}

		return $this->fuserIds[$fuserId];
	}

	/**
	 * Cleans fusers cache.
	 * @return void
	 */
	public function clean()
	{
		$this->fuserIds = array();
	}
}