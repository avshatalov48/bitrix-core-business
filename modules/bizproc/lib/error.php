<?php

namespace Bitrix\Bizproc;

use Bitrix\Main\Localization\Loc;

class Error extends \Bitrix\Main\Error
{
	public const MODULE_NOT_INSTALLED = 'MODULE_NOT_INSTALLED';
	public const USER_NOT_FOUND = 'USER_NOT_FOUND';
	public const FILE_NOT_FOUND = 'FILE_NOT_FOUND';

	public static function fromCode(string $code, $customData = null): self
	{
		$locals = static::getLocalizationIdMap();

		$replacement = [];
		if ($code === static::MODULE_NOT_INSTALLED && is_array($customData) && isset($customData['moduleName']))
		{
			$replacement = ['#MODULE_NAME#' => $customData['moduleName']];
		}

		if (isset($locals[$code]))
		{
			return new static(Loc::getMessage($locals[$code], $replacement), $code, $customData);
		}
		else
		{
			return new static('', $code, $customData);
		}
	}

	public static function getCodes(): array
	{
		return [
			static::MODULE_NOT_INSTALLED,
			static::USER_NOT_FOUND,
			static::FILE_NOT_FOUND,
		];
	}

	protected static function getLocalizationIdMap(): array
	{
		$map = [];
		foreach (static::getCodes() as $code)
		{
			$map[$code] = 'BIZPROC_ERROR_' . $code;
		}

		return $map;
	}
}