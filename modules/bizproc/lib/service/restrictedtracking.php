<?php

namespace Bitrix\Bizproc\Service;

use Bitrix\Main\Config\Option;

class RestrictedTracking extends \CBPTrackingService
{
	private array $templateIdMap = [];
	private array $offTemplateMap = [];

	public function canWrite($type, $workflowId)
	{
		if (!$this->isForcedMode($workflowId) && $this->isOff($workflowId))
		{
			return false;
		}

		return parent::canWrite($type, $workflowId);
	}

	private function isOff($workflowId): bool
	{
		$this->templateIdMap[$workflowId] ??= $this->getTemplateId($workflowId);
		$templateId = $this->templateIdMap[$workflowId];

		$this->offTemplateMap[$templateId] ??= $this->isTemplateOff($templateId);

		return $this->offTemplateMap[$templateId];
	}

	private function getTemplateId($workflowId): int
	{
		try
		{
			return \CBPRuntime::getRuntime()->getWorkflow($workflowId, true)->getTemplateId();
		}
		catch (\Exception $e)
		{
			return 0;
		}
	}

	private function isTemplateOff(int $templateId): bool
	{
		$optionName = 'tpl_track_on_' . $templateId;
		$onTime = (int)Option::get('bizproc', $optionName, 0);

		if ($onTime > 0)
		{
			$sevenDays = 7 * 86400;

			return ($onTime + $sevenDays) <= time();
		}

		return true;
	}
}
