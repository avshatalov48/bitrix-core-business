<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI;

use Bitrix\Landing\Copilot\Connector\AI\Type\LimitType;
use Bitrix\Landing\Metrika\Statuses;

/**
 * Data transfer object representing the result of a limit check for AI requests.
 *
 * Contains information about the type of limit, a localized message for the user,
 * and a flag indicating whether the limit has been exceeded.
 *
 * @property LimitType $type The type of the limit that was checked.
 * @property string|null $message Localized message describing the limit status.
 * @property bool $isExceeded Whether the limit has been exceeded.
 */
class LimitCheckResult
{
	public LimitType $type;
	public ?string $message;
	public bool $isExceeded;

	/**
	 * LimitCheckResult constructor.
	 *
	 * @param LimitType $type The type of the limit that was checked.
	 * @param bool $isExceeded Whether the limit has been exceeded.
	 * @param string|null $message Localized message describing the limit status.
	 */
	public function __construct(LimitType $type, bool $isExceeded, ?string $message = null)
	{
		$this->type = $type;
		$this->isExceeded = $isExceeded;
		$this->message = $message;
	}

	/**
	 * Returns the type of the limit that was checked.
	 *
	 * @return LimitType|null The type of the limit.
	 */
	public function getType(): ?LimitType
	{
		return $this->type;
	}

	/**
	 * Returns whether the limit has been exceeded.
	 *
	 * @return bool True if the limit is exceeded, false otherwise.
	 */
	public function isExceeded(): bool
	{
		return $this->isExceeded;
	}

	/**
	 * Returns the localized message describing the limit status.
	 *
	 * @return string|null Localized message.
	 */
	public function getMessage(): ?string
	{
		return $this->message;
	}

	/**
	 * Returns the metrika status corresponding to the limit type.
	 *
	 * @return Statuses Metrika status for analytics.
	 */
	public function getMetrikaStatus(): Statuses
	{
		return match ($this->getType())
		{
			LimitType::Baas => Statuses::ErrorLimitBaas,
			LimitType::Daily => Statuses::ErrorLimitDaily,
			LimitType::Monthly => Statuses::ErrorLimitMonthly,
			LimitType::Market => Statuses::ErrorMarket,
			default => Statuses::ErrorB24,
		};
	}
}