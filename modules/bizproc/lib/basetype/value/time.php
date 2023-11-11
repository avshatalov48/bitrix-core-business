<?php

namespace Bitrix\Bizproc\BaseType\Value;

use Bitrix\Main\Application;

class Time implements \JsonSerializable
{
	protected int $timestamp = 0;
	protected int $offset = 0;

	public function __construct(string $timeFormatted, int $offset = 0)
	{
		if (!static::isCorrect($timeFormatted))
		{
			$this->timestamp = (new \Bitrix\Main\Type\Date('0000-00-00', 'Y-m-d'))->getTimestamp();
		}
		elseif (static::isSerialized($timeFormatted))
		{
			preg_match('#(\d{2}:\d{2})\s\[([0-9\-]+)\]#i', $timeFormatted, $matches);
			$timeFormatted = $matches[1];
			$userOffset = (int)$matches[2];
			$dateTime = new \Bitrix\Main\Type\DateTime($timeFormatted, static::getRenderFormat());

			$this->timestamp = $dateTime->getTimestamp() - $userOffset;
		}
		else
		{
			$format = static::isRenderFormat($timeFormatted) ? static::getRenderFormat() : static::getFormat();
			$dateTime = new \Bitrix\Main\Type\DateTime($timeFormatted, $format);
			$this->timestamp = $dateTime->getTimestamp() - $offset;
		}

		$this->offset = $offset;
	}

	public function getTimestamp(): int
	{
		return $this->timestamp;
	}

	public function getOffset(): int
	{
		return $this->offset;
	}

	public function __toString(): string
	{
		return ($this->toSystemObject()->format(static::getFormat()));
	}

	public function toSystemObject(): \Bitrix\Main\Type\DateTime
	{
		return \Bitrix\Main\Type\DateTime::createFromTimestamp($this->getTimestamp() + $this->getOffset());
	}

	public function toServerTime(): \Bitrix\Main\Type\DateTime
	{
		return \Bitrix\Main\Type\DateTime::createFromTimestamp($this->getTimestamp());
	}

	public function toUserTime(int $userOffset): \Bitrix\Main\Type\DateTime
	{
		return \Bitrix\Main\Type\DateTime::createFromTimestamp($this->getTimestamp() + $userOffset);
	}

	public static function getFormat(): ?string
	{
		return Application::getInstance()->getContext()->getCulture()?->getShortTimeFormat();
	}

	public static function getRenderFormat(): string
	{
		return 'H:i';
	}

	public static function isCorrect(string $timeFormatted): bool
	{
		return (
			!\CBPHelper::isEmptyValue($timeFormatted)
			&& (
				\Bitrix\Main\Type\DateTime::isCorrect($timeFormatted, static::getFormat())
				|| static::isRenderFormat($timeFormatted)
				|| static::isSerialized($timeFormatted)
			)
		);
	}

	public function jsonSerialize()
	{
		return $this->serialize();
	}

	public function serialize(): string
	{
		$timeFormatted = $this->toSystemObject()->format(static::getRenderFormat());

		return sprintf('%s [%d]', $timeFormatted, $this->offset);
	}

	private static function isSerialized(string $timeFormatted): bool
	{
		if (preg_match('#(\d{2}:\d{2})\s\[([0-9\-]+)\]#i', $timeFormatted, $matches))
		{
			$timeFormatted = $matches[1];

			return \Bitrix\Main\Type\DateTime::isCorrect($timeFormatted, static::getRenderFormat());
		}

		return false;
	}

	private static function isRenderFormat(string $timeFormatted): bool
	{
		$timeFormatted = trim($timeFormatted);
		if (preg_match('#^\d{2}:\d{2}$#i', $timeFormatted, $matches))
		{
			return \Bitrix\Main\Type\DateTime::isCorrect($timeFormatted, static::getRenderFormat());
		}

		return false;
	}

	public static function tryMakeCorrectFormat(string $timeFormatted, int $offset = 0): string
	{
		$correct = $timeFormatted;

		if (static::isCorrect($timeFormatted))
		{
			$time = new static($timeFormatted, $offset);
			$correct = (string)$time;
		}

		return $correct;
	}
}
