<?php

namespace Bitrix\Bizproc\BaseType\Value;

use Bitrix\Main\Application;

class Time
{
	protected int $timestamp = 0;
	protected int $offset = 0;

	public function __construct(string $timeFormatted, int $offset = 0)
	{
		$isCorrectTime = static::isCorrect($timeFormatted);

		$this->timestamp =
			$isCorrectTime
				? ((new \Bitrix\Main\Type\DateTime($timeFormatted, static::getFormat()))->getTimestamp() - $offset)
				: (new \Bitrix\Main\Type\Date('0000-00-00', 'Y-m-d'))->getTimestamp()
		;

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
		return (
			(\Bitrix\Main\Type\DateTime::createFromTimestamp($this->getTimestamp() + $this->getOffset()))
				->format(static::getFormat())
		);
	}

	public function toSystemObject(): \Bitrix\Main\Type\DateTime
	{
		return \Bitrix\Main\Type\DateTime::createFromTimestamp($this->getTimestamp() + $this->offset);
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
		$culture = Application::getInstance()->getContext()->getCulture();
		if (!$culture)
		{
			return null;
		}

		return $culture->getShortTimeFormat();
	}

	public static function isCorrect(string $timeFormatted): bool
	{
		return (
			!\CBPHelper::isEmptyValue($timeFormatted)
			&& \Bitrix\Main\Type\DateTime::isCorrect($timeFormatted, static::getFormat())
		);
	}
}