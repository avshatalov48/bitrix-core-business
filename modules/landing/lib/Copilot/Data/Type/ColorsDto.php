<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Type;

class ColorsDto
{
	private const DEFAULT_THEME_COLOR = '#6ab8ee';
	private const DEFAULT_BG_COLOR = '#f8fcff';
	private const DEFAULT_TEXT_THEME = '#f8fcff';
	private const DEFAULT_TEXT_BG = '#009e45';

	public string $theme = self::DEFAULT_THEME_COLOR;
	public string $themeName = '';
	public string $background = self::DEFAULT_BG_COLOR;
	public string $backgroundName = '';
	public string $headersBg = self::DEFAULT_TEXT_BG;
	public string $headersTheme = self::DEFAULT_TEXT_THEME;
	public string $textsBg = self::DEFAULT_TEXT_BG;
	public string $textsTheme = self::DEFAULT_TEXT_THEME;
}
