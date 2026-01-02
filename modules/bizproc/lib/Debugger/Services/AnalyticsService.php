<?php

namespace Bitrix\Bizproc\Debugger\Services;

use Bitrix\Bizproc;

class AnalyticsService extends Bizproc\Service\Analytics
{
	public function write(array $documentId, $eventName, $tool): void
	{
		$eventName = 'debug_' . $eventName;

		parent::write($documentId, $eventName, $tool);
	}
}
