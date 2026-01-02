<?php

namespace Bitrix\Rest\Notification\MarketExpired;

use Bitrix\Main\Application;
use Bitrix\Rest\Infrastructure\Market\MarketSubscription;
use Bitrix\Rest\Notification\MarketExpired\Curtain\MarketExpiredCurtain;
use Bitrix\Rest\Notification\MarketExpired\Popup\MarketExpiredPopup;
use Bitrix\Rest\Service\RestUserOption;

abstract class MarketExpiredNotification
{
	protected const AFTER_SHOW_DURATION = 7 * 24 * 60 * 60; // 1 week
	protected const BEFORE_SHOW_DURATION = 7 * 24 * 60 * 60; // 1 week

	private static ?MarketExpiredNotification $instance = null;

	public function __construct(
		protected readonly RestUserOption $userOption,
		public readonly MarketSubscription $marketSubscription,
	)
	{}

	public static function createByDefault(): MarketExpiredNotification
	{
		if (isset(self::$instance))
		{
			return self::$instance;
		}

		$marketSubscription = MarketSubscription::createByDefault();
		$transitionEndTs = $marketSubscription->getTransitionPeriodEndDate()->getTimestamp();

		if ($transitionEndTs + self::AFTER_SHOW_DURATION > time())
		{
			self::$instance = new MarketTransitionNotification(
				new RestUserOption(),
				$marketSubscription
			);
		}
		else
		{
			self::$instance = new MarketSubscriptionNotification(
				new RestUserOption(),
				$marketSubscription
			);
		}

		return self::$instance;
	}

	abstract public function getPopup(): MarketExpiredPopup;

	abstract public function getCurtain(): MarketExpiredCurtain;

	abstract public function getEndDateTimestamp(): int;

	abstract public function isAvailable(): bool;

	abstract public function getType(): MarketExpiredType;

	abstract public function getCategory(): MarketExpiredCategory;

	final public function getFormattedEndDate(): string
	{
		return FormatDate(
			Application::getInstance()->getContext()->getCulture()->get('LONG_DATE_FORMAT'),
			$this->getEndDateTimestamp(),
		);
	}

	final public function getFormattedDaysLeft(): string
	{
		return FormatDate(
			'ddiff',
			time(),
			$this->getEndDateTimestamp(),
		);
	}

	public function getOpenLinesWidgetCode(): string
	{
		return '';
	}
}
