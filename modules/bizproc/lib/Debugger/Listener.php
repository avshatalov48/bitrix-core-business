<?php

namespace Bitrix\Bizproc\Debugger;

use Bitrix\Bizproc\Automation\Engine\Template;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\CurrentUser;

class Listener
{
	private static self $instance;

	public function onDocumentStatusChanged(string $status)
	{
		$this->pushEvent('documentStatus', ['status' => $status]);
	}

	public function onWorkflowStatusChanged(string $workflowId, int $status)
	{
		$this->pushEvent('workflowStatus', [
			'workflowId' => $workflowId,
			'status' => $status,
		]);
	}

	public function onWorkflowEventAdded(string $workflowId, string $eventName)
	{
		$session = Session\Manager::getActiveSession();
		$template = null;
		foreach ($session->getWorkflowContexts() as $workflowContext)
		{
			if ($workflowContext->getWorkflowId() === $workflowId)
			{
				$template = Template::createByTpl($workflowContext->fillTemplateShards()->fillTemplate());
				break;
			}
		}

		$robotId = null;
		if ($template)
		{
			foreach ($template->getRobots() as $robot)
			{
				if ($robot->getDelayName() === $eventName)
				{
					$robotId = $robot->getName();
					break;
				}
			}
		}

		$this->pushEvent('workflowEventAdd', [
			'workflowId' => $workflowId,
			'eventName' => $eventName,
			'sourceId' => $robotId,
		]);
	}

	public function onWorkflowEventRemoved(string $workflowId, string $eventName)
	{
		$this->pushEvent('workflowEventRemove', [
			'workflowId' => $workflowId,
			'eventName' => $eventName,
		]);
	}

	public function onDocumentUpdated(array $changedFields)
	{
		$documentId = Session\Manager::getActiveSession()->getFixedDocument()->getParameterDocumentId();
		$documentType = Session\Manager::getActiveSession()->getParameterDocumentType();

		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$documentFields = $documentService->getDocumentFields($documentType);
		$lazyList = $documentService->GetDocument($documentId);

		$rawValues = [];
		$values = [];
		$changedFields[] = 'DATE_MODIFY';

		//TODO - temporary
		if (in_array('ASSIGNED_BY_ID', $changedFields))
		{
			$changedFields[] = 'ASSIGNED_BY_EMAIL';
			$changedFields[] = 'ASSIGNED_BY_WORK_PHONE';
			$changedFields[] = 'ASSIGNED_BY_PERSONAL_MOBILE';
		}

		foreach ($changedFields as $fieldId)
		{
			$property = $documentFields[$fieldId] ?? null;

			if (!$property)
			{
				continue;
			}

			$fieldType = $documentService->getFieldTypeObject($documentType, $property);

			if (!$fieldType)
			{
				continue;
			}

			$fieldType->setDocumentId($documentId);

			$rawValues[$fieldId] = $lazyList[$fieldId];
			$values[$fieldId] = $fieldType->formatValue($rawValues[$fieldId]);
		}

		$this->pushEvent(
			'documentValues',
			[
				'values' => $values,
				'rawValues' => $rawValues,
			]
		);
	}

	public function onDocumentDeleted()
	{
		$this->pushEvent('documentDelete', []);
	}

	public function onTrackWrite($row)
	{
		$this->pushEvent('trackRow', ['row' => $row]);
	}

	public function onSessionFinished(string $sessionId)
	{
		$this->pushEvent('sessionFinish', ['sessionId' => $sessionId]);
	}

	private function canPush()
	{
		return Loader::includeModule('pull');
	}

	private function pushEvent(string $command, array $params)
	{
		if ($this->canPush())
		{
			\Bitrix\Pull\Event::add(
				CurrentUser::get()->getId(),
				[
					'module_id' => 'bizproc',
					'command' => $command,
					'params' => $params,
				]
			);
		}
	}

	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	private function __construct()
	{
	}
	private function __clone()
	{
	}
}
