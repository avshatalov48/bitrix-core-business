<?php
declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Text\BbCode;

use Bitrix\Im\V2\Message\Text\BbCode\Timestamp\DateFormat;
use Bitrix\Im\V2\Message\Text\OpenBbCode;
use Bitrix\Main\Application;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

final class Timestamp extends OpenBbCode
{
	private Date $date;
	private DateFormat $formatCode;

	private function __construct(Date $date, DateFormat $formatCode)
	{
		$this->date = $date;
		$this->formatCode = $formatCode;
	}

	public static function build(Date $date, DateFormat $formatCode): self
	{
		return new self($date, $formatCode);
	}

	public function toPlain(): string
	{
		$format = $this->getFormat();

		return FormatDate($format, $this->date);
	}

	public static function getName(): string
	{
		return 'TIMESTAMP';
	}

	protected function getValue(): ?string
	{
		return (string)$this->date->getTimestamp();
	}

	protected function getAdditionalParams(): array
	{
		return ['FORMAT' => $this->formatCode->value];
	}

	private function getFormat(): string
	{
		$culture = Application::getInstance()->getContext()->getCulture();
		if ($culture === null)
		{
			return '';
		}

		$format = $culture->get($this->formatCode->value);

		if ($this->formatCode === DateFormat::FormatDatetime || $this->formatCode === DateFormat::FormatDate)
		{
			return DateTime::convertFormatToPhp($format);
		}

		return $format;
	}
}
