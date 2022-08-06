<?php

namespace Bitrix\Bizproc\Debugger\Mixins;

trait WriterDebugTrack
{
	protected function writeDebugTrack(
		string $workflowId,
		string $name,
		int $status,
		int $result,
		string $title = '',
		$toWrite = [],
		int $trackType = \CBPTrackingType::Debug
	): ?int
	{
		/** @var $trackingService \Bitrix\Bizproc\Debugger\Services\TrackingService | null */
		$trackingService = \CBPRuntime::GetRuntime(true)->getDebugService('TrackingService');
		if ($trackingService && $trackingService->canWrite($trackType, $workflowId))
		{
			return $trackingService->write($workflowId, $trackType, $name, $status, $result, $title, $toWrite);
		}

		return null;
	}

	protected function preparePropertyForWritingToTrack($value, string $name = ''): array
	{
		$toWrite = [];
		if ($name)
		{
			$toWrite['propertyName'] = $name;
		}

		$toWrite['propertyValue'] = $value;

		return $toWrite;
	}

	protected function writeSessionLegendTrack($workflowId): ?int
	{
		if (!$workflowId)
		{
			return null;
		}

		$debugSession = \Bitrix\Bizproc\Debugger\Session\Manager::getActiveSession();
		if (!$debugSession)
		{
			return null;
		}

		return $this->writeDebugTrack(
			$workflowId,
			'SESSION_LEGEND',
			\CBPActivityExecutionStatus::Closed,
			\CBPActivityExecutionResult::Succeeded,
			'',
			$debugSession->getShortDescription()
		);
	}

	protected function writeDocumentStatusTrack($workflowId, array $status): ?int
	{
		if (!$workflowId)
		{
			return null;
		}

		$statusLog = [
			'STATUS_ID' => $status['STATUS_ID'],
			'NAME' => $status['NAME'],
			'COLOR' => $status['COLOR'],
		];

		return $this->writeDebugTrack(
			$workflowId,
			'STATUS_CHANGED',
			\CBPActivityExecutionStatus::Closed,
			\CBPActivityExecutionResult::Succeeded,
			'',
			$statusLog,
			\CBPTrackingType::DebugAutomation
		);
	}

	protected function writeAppliedTriggerTrack($workflowId, array $trigger): ?int
	{
		if (!$workflowId)
		{
			return null;
		}

		return $this->writeDebugTrack(
			$workflowId,
			'TRIGGER_LOG',
			\CBPActivityExecutionStatus::Closed,
			\CBPActivityExecutionResult::Succeeded,
			$trigger['NAME'],
			$trigger['APPLIED_RULE_LOG'] ?? [],
			\CBPTrackingType::DebugAutomation
		);
	}

	protected function writeDocumentCategoryTrack($workflowId, $categoryName): ?int
	{
		if (!$workflowId)
		{
			return null;
		}

		return $this->writeDebugTrack(
			$workflowId,
			'CATEGORY_CHANGED',
			\CBPActivityExecutionStatus::Closed,
			\CBPActivityExecutionResult::Succeeded,
			'',
			$categoryName,
			\CBPTrackingType::DebugAutomation
		);
	}
}