<?php
namespace Bitrix\Main\UpdateSystem;

class Coupon
{
	private string $coupon;

	public function __construct(string $coupon)
	{
		$this->coupon = $coupon;
	}

	public function isCoupone(): bool
	{
		if (!preg_match("#^[A-Z0-9]{3}-[A-Z]{2}-?[A-Z0-9]{12,30}$#i", $this->coupon)
			&& !preg_match("#^[A-Z0-9]{3}-[A-Z0-9]{10}-[A-Z0-9]{10}$#i", $this->coupon))
		{
			return false;
		}

		return true;
	}

	public function getKey(): string
	{
		return $this->coupon;
	}
}