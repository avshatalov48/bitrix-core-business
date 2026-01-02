<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity\ProcessingParam;

use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class DelayParam implements ProcessingParamInterface
{
	/**
	 * @param int $delay The delay in seconds
	 */
	public function __construct(public readonly int $delay)
	{
	}

	public static function delayFor(\DateInterval $interval): self
	{
		$now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
		$end = $now->add($interval);

		return new self($end->getTimestamp() - $now->getTimestamp());
	}

	public static function delayUntil(Date $dateTime): self
	{
		return new self($dateTime->getTimestamp() - time());
	}

	public function applyTo(MessageBox $messageBox): MessageBox
	{
		if ($this->delay > 0)
		{
			$availableAt = new DateTime();

			$availableAt->add(sprintf('+%d seconds', $this->delay));

			$messageBox->setAvailableAt($availableAt);
		}

		return $messageBox;
	}
}
