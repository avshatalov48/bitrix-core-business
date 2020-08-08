<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Result;

/**
 * Class Logger
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class Logger
{
	/**
	 * @param string $source
	 * @param string $code
	 * @param Result|string $messages
	 */
	public function log(string $source, string $code, $messages = null)
	{
		if ($messages instanceof Result)
		{
			$messages = implode(';', $messages->getErrorMessages());
		}

		\CEventLog::add(
			[
				'SEVERITY' => \CEventLog::SEVERITY_ERROR,
				'MODULE_ID' => 'sale',
				'AUDIT_TYPE_ID' => 'SALE_DELIVERY_YANDEX_TAXI',
				'ITEM_ID' => sprintf('%s.%s', $source, $code),
				'DESCRIPTION' => (string)$messages,
			]
		);
	}
}
