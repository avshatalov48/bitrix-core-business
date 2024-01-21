<?php

namespace Bitrix\Bizproc\Api\Response;

use Bitrix\Main\Localization\Loc;

class Error extends \Bitrix\Bizproc\Error
{
	public const UNAUTHORIZED = 'UNAUTHORIZED';
	public const WORKFLOW_NOT_FOUND = 'WORKFLOW_NOT_FOUND';

	public static function getCodes(): array
	{
		return array_merge(
			parent::getCodes(),
			[
				static::UNAUTHORIZED,
				static::WORKFLOW_NOT_FOUND,
			],
		);
	}

	public static function getLocalizationIdMap(): array
	{
		Loc::loadMessages(__FILE__);
		$prefix = 'BIZPROC_LIB_SERVICES_ERROR_';

		return array_merge(
			parent::getLocalizationIdMap(),
			[
				static::UNAUTHORIZED => $prefix . static::UNAUTHORIZED,
				static::WORKFLOW_NOT_FOUND => $prefix . static::WORKFLOW_NOT_FOUND,
			],
		);
	}
}