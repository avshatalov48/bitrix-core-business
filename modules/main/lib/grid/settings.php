<?php

namespace Bitrix\Main\Grid;

use Bitrix\Main\ArgumentException;

class Settings
{
	public const MODE_HTML = 'html';
	public const MODE_EXCEL = 'excel';

	private string $id = '';
	private string $mode;

	public function __construct(array $params)
	{
		// for supports \Bitrix\Main\Filter\Settings
		$this->id = $params['ID'] ?? '';
		$this->mode = $params['MODE'] ?? self::MODE_HTML;

		if ($this->id === '')
		{
			throw new ArgumentException('Collection does not contain value for id.', 'params');
		}
	}

	public function getID(): string
	{
		return $this->id;
	}

	public function setMode(string $mode): void
	{
		$this->mode = $mode;
	}

	public function isHtmlMode(): bool
	{
		return $this->mode === self::MODE_HTML;
	}

	public function isExcelMode(): bool
	{
		return $this->mode === self::MODE_EXCEL;
	}
}
