<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer;

use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

/**
 * Class Balancer
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer
 */
final class Balancer
{
	private int $productId;

	private ?DateTime $start = null;

	public function __construct(int $productId)
	{
		$this->productId = $productId;
	}

	public function getProductId(): int
	{
		return $this->productId;
	}

	public function setStartDate(DateTime $start): self
	{
		$this->start = $start;

		return $this;
	}

	public function getStartDate(): ?DateTime
	{
		return $this->start;
	}

	public function fill(): Result
	{
		if (CostPriceCalculator::getMethod() === CostPriceCalculator::METHOD_FIFO)
		{
			$method = new Method\Fifo($this);
		}
		else
		{
			$method = new Method\Average($this);
		}

		return $method->fill();
	}
}