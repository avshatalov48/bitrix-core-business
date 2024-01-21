<?php

namespace Bitrix\Sale\Discount;

use Bitrix\Main;

final class CouponLocker
{
	private const STATUS_LOCKED = 1;
	private const STATUS_NOT_LOCKED = 0;
	private const STATUS_EMPTY = -1;

	private static null|self $instance;

	private array $couponState;

	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		$this->clear();
	}

	public function __destruct()
	{
		$this->unlockAll();
		self::$instance = null;
	}

	private function getUniqueLockName(string $coupon): string
	{
		return 'sale_coupon_' . md5($coupon);
	}

	private function clear(): void
	{
		$this->couponState = [];
	}

	private function setLockState(string $coupon, int $state): void
	{
		$this->couponState[$coupon] = $state;
	}

	private function getLockState(string $coupon): int
	{
		return $this->couponState[$coupon] ?? self::STATUS_EMPTY;
	}

	public function isNotLocked(string $coupon): bool
	{
		return $this->getLockState($coupon) === self::STATUS_NOT_LOCKED;
	}

	public function isLocked(string $coupon): bool
	{
		return $this->getLockState($coupon) === self::STATUS_LOCKED;
	}

	public function lock(string $coupon): void
	{
		$coupon = trim($coupon);
		if ($coupon === '')
		{
			return;
		}

		$connection = Main\Application::getConnection();
		if ($connection->lock($this->getUniqueLockName($coupon)))
		{
			$this->setLockState($coupon, self::STATUS_LOCKED);
		}
		unset($connection);
	}

	public function unlock(string $coupon): void
	{
		$coupon = trim($coupon);
		if ($coupon === '')
		{
			return;
		}

		$connection = Main\Application::getConnection();
		if ($connection->unlock($this->getUniqueLockName($coupon)))
		{
			$this->setLockState($coupon, self::STATUS_NOT_LOCKED);
		}
		unset($connection);
	}

	public function unlockAll(): void
	{
		if (empty($this->couponState))
		{
			return;
		}

		$connection = Main\Application::getConnection();

		foreach (array_keys($this->couponState) as $coupon)
		{
			if (!$this->isLocked($coupon))
			{
				continue;
			}
			$connection->unlock($this->getUniqueLockName($coupon));
		}

		unset($connection);

		$this->clear();
	}
}
