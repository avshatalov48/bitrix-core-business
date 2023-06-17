<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Pull;

class PullManager
{
	public const MODULE_ID = 'sale';
	public const PAYMENT_COMMAND = 'PAYMENT';

	public const SUCCESSFUL_PAYMENT = 'SUCCESSFUL_PAYMENT';
	public const FAILURE_PAYMENT = 'FAILURE_PAYMENT';

	public static function subscribeOnPayment(int $userId): void
	{
		if ($userId <= 0)
		{
			return;
		}

		if (self::includePullModule())
		{
			\CPullWatch::Add($userId, self::PAYMENT_COMMAND);
		}
	}

	public static function onSuccessfulPayment(Sale\Payment $payment): void
	{
		if (!self::includePullModule())
		{
			return;
		}

		self::sendEvent(
			self::SUCCESSFUL_PAYMENT,
			$payment->getId()
		);
	}

	public static function onFailurePayment(Sale\Payment $payment): void
	{
		if (!self::includePullModule())
		{
			return;
		}

		self::sendEvent(
			self::FAILURE_PAYMENT,
			$payment->getId()
		);
	}

	private static function sendEvent(string $eventName, int $id): void
	{
		$userIds = self::getSubscribedUserIds();
		if (empty($userIds))
		{
			return;
		}

		Pull\Event::add(
			$userIds,
			[
				'module_id' => self::MODULE_ID,
				'command' => self::PAYMENT_COMMAND,
				'params' => [
					'eventName' => $eventName,
					'paymentId' => $id,
				],
			]
		);
	}

	private static function getSubscribedUserIds(): array
	{
		return Pull\Model\WatchTable::getUserIdsByTag(
			self::PAYMENT_COMMAND
		);
	}

	private static function includePullModule(): bool
	{
		return Main\Loader::includeModule('pull');
	}
}
