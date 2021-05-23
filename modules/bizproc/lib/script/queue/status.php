<?php
namespace Bitrix\Bizproc\Script\Queue;

use Bitrix\Main\Localization\Loc;

class Status
{
	public const QUEUED = 0;
	public const EXECUTING = 1;
	public const FAULT = 2;
	public const TERMINATED = 3;
	public const COMPLETED = 4;

	public static function getLabel(int $status)
	{
		switch ($status)
		{
			case static::QUEUED:
				return Loc::getMessage('BIZPROC_SCRIPT_QUEUE_STATUS_QUEUED');
			case static::EXECUTING:
				return Loc::getMessage('BIZPROC_SCRIPT_QUEUE_STATUS_EXECUTING');
			case static::FAULT:
				return Loc::getMessage('BIZPROC_SCRIPT_QUEUE_STATUS_FAULT');
			case static::TERMINATED:
				return Loc::getMessage('BIZPROC_SCRIPT_QUEUE_STATUS_TERMINATED');
			case static::COMPLETED:
				return Loc::getMessage('BIZPROC_SCRIPT_QUEUE_STATUS_COMPLETED');
		}

		return 'unknown';
	}
}