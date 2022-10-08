<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Diag;

class EventLogger extends Logger
{
	protected $module;
	protected $auditType;
	protected $callback;

	/**
	 * @param string|null $module
	 * @param string|null $auditType
	 * @param callable|null $callback Should return an array with fields for CEventLog::Add(). function (array $context, string $message): array
	 */
	public function __construct(string $module = null, string $auditType = null, callable $callback = null)
	{
		$this->module = $module;
		$this->auditType = $auditType;
		$this->callback = $callback;
	}

	protected function logMessage(string $level, string $message)
	{
		if (is_callable($this->callback))
		{
			$info = call_user_func($this->callback, $this->context, $this->message);
		}

		\CEventLog::Add([
			'SEVERITY' => $info['SEVERITY'] ?? strtoupper($level),
			'AUDIT_TYPE_ID' => $info['AUDIT_TYPE_ID'] ?? $this->auditType,
			'MODULE_ID' => $info['MODULE_ID'] ?? $this->module,
			'ITEM_ID' => $info['ITEM_ID'] ?? '',
			'DESCRIPTION' => $info['DESCRIPTION'] ?? $message
		]);
	}
}
