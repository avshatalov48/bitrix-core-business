<?php

namespace Bitrix\Rest\Infrastructure\Market;

class MarketDiscount
{
	public function __construct(
		private bool $isAvailable,
		private int $percentage,
		private string $termsUrl,
	)
	{}

	/**
	 * @return bool
	 */
	public function isAvailable(): bool
	{
		return $this->isAvailable;
	}

	/**
	 * @return int
	 */
	public function getPercentage(): int
	{
		return $this->percentage;
	}

	/**
	 * @return string
	 */
	public function getTermsUrl(): string
	{
		return $this->termsUrl;
	}

	public function toArray(): array
	{
		return [
			'isAvailable' => $this->isAvailable(),
			'percentage' => $this->getPercentage(),
			'termsUrl' => $this->getTermsUrl(),
		];
	}
}
