<?php

namespace Bitrix\Seo\BusinessSuite\Exception;

use Bitrix\Main\SystemException;

class ConfigException extends SystemException
{
	const EXCEPTION_TYPE = '';
	const EXCEPTION_TYPE_INVALID_VALUE = 'INVALID_VALUE';
	const EXCEPTION_TYPE_REQUIRED_FIELD = 'REQUIRED_FIELD';
	const EXCEPTION_TYPE_SERVICE_LOAD = 'SERVICE_LOAD';
	const EXCEPTION_TYPE_UNKNOWN_FIELD = 'UNKNOWN_FIELD';
	const EXCEPTION_TYPE_UNRESOLVED_DEPENDENCY = 'UNRESOLVED_DEPENDENCY';

	/**
	 * get exception data
	 * @return array
	 */
	public function getCustomData() : array
	{
		return [
			'type' => static::EXCEPTION_TYPE
		];
	}
}