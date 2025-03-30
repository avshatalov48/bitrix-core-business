<?php

namespace Bitrix\Rest\Infrastructure\Market;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\Date;
use Bitrix\Rest\Contract\OptionContract;

class MarketOption
{
	public const TRANSITION_PERIOD_DATE_FORMAT = 'd.m.Y';
	private const DEFAULT_TRANSITION_PERIOD_END_DATE = '14.03.2025';

	public function __construct(
		private readonly OptionContract $option,
	)
	{}

	public function isNewPoliticsEnabled(): bool
	{
		return $this->option->get('isMarketNewPoliticsEnabled', 'N') === 'Y';
	}

	public function enableNewPolitics(): void
	{
		$this->option->set('isMarketNewPoliticsEnabled', 'Y');
	}

	public function isTransitionPeriodEnabled(): bool
	{
		return $this->option->get('isMarketTransitionPeriod', 'N') === 'Y';
	}

	public function isDiscountAvailable(): bool
	{
		return $this->option->get('isMarketDiscountAvailable', 'N') === 'Y';
	}

	public function getSavedTransitionPeriodEndDate(): Date
	{
		$endDateValue = $this->option->get('marketTransitionPeriodEndDate', self::DEFAULT_TRANSITION_PERIOD_END_DATE);

		try
		{
			$endDate = new Date((string)$endDateValue, self::TRANSITION_PERIOD_DATE_FORMAT);
		}
		catch (ObjectException $e)
		{
			$endDate = new Date(self::DEFAULT_TRANSITION_PERIOD_END_DATE, self::TRANSITION_PERIOD_DATE_FORMAT);
		}

		return $endDate;
	}
}
