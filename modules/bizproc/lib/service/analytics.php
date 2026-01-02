<?php
namespace Bitrix\Bizproc\Service;

use Bitrix\Main\Analytics\AnalyticsEvent;

class Analytics extends \CBPRuntimeService
{
	public function write(array $documentId, $eventName, $tool): void
	{
		$category = 'bizproc:' . $documentId[0];

		$event = new AnalyticsEvent($eventName, $tool, $category);
		$event->send();
	}
}
