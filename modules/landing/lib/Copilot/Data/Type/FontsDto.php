<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Type;

class FontsDto
{
	private const DEFAULT_HEADERS_FONT = 'Montserrat';
	private const DEFAULT_TEXTS_FONT = 'Open Sans';

	public string $headers = self::DEFAULT_HEADERS_FONT;
	public string $texts = self::DEFAULT_TEXTS_FONT;
}
