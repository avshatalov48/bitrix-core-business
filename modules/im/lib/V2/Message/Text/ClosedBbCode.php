<?php
declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Text;

abstract class ClosedBbCode extends BaseBbCode
{
	protected string $innerText;

	public function __construct(string $innerText)
	{
		$this->innerText = $innerText;
	}

	final public function compile(): string
	{
		return parent::compile() . $this->innerText . self::getClosingPart();
	}

	public function toPlain(): string
	{
		return $this->innerText;
	}

	private static function getClosingPart(): string
	{
		$name = static::getName();

		return "[/{$name}]";
	}
}
