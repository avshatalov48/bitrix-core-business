<?php
declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Text\BbCode;

use Bitrix\Im\V2\Message\Text\BbCode\Image\Size;
use Bitrix\Im\V2\Message\Text\ClosedBbCode;
use Bitrix\Main\Localization\Loc;

class Image extends ClosedBbCode
{
	private Size $size;

	private function __construct(string $innerText, Size $size)
	{
		parent::__construct($innerText);
		$this->size = $size;
	}

	public static function build(string $innerText, Size $size): self
	{
		// Add a space at the end of the link, or the main parser will break the BbCode
		return new self($innerText . ' ', $size);
	}

	protected function getValue(): ?string
	{
		return null;
	}

	public function toPlain(): string
	{
		return trim($this->innerText);
	}

	public function toPlaceholder(): string
	{
		return ' [' . Loc::getMessage('IM_MESSAGE_BB_CODE_IMAGE_PLACEHOLDER') . ']';
	}

	protected function getAdditionalParams(): array
	{
		return ['SIZE' => $this->size->value];
	}

	public static function getName(): string
	{
		return 'IMG';
	}
}
