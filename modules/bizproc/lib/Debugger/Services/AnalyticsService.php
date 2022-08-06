<?php

namespace Bitrix\Bizproc\Debugger\Services;

use Bitrix\Bizproc;

class AnalyticsService extends Bizproc\Service\Analytics
{
	public function write(array $documentId, $action, $tag)
	{
		$action = 'debug_' . $action;

		parent::write($documentId, $action, $tag);
	}
}
