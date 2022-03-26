<?php
namespace Bitrix\Bizproc\Script\Queue;

use Bitrix\Bizproc\Script\Entity\EO_Script;
use Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument;
use Bitrix\Bizproc\Script\Entity\ScriptQueueTable;
use Bitrix\Bizproc\Script\Manager;
use Bitrix\Main;

final class Stepper extends Main\Update\Stepper
{
	protected static $moduleId = 'bizproc';

	public function execute(array &$result)
	{
		$params = $this->getOuterParams();
		$queueId = reset($params);
		$scriptId = next($params);

		$counters = ScriptQueueTable::getDocumentCounters($queueId);

		$result['count'] = $counters['all'];
		$result['steps'] = $counters['completed'];

		if ($result['steps'] >= $result['count'])
		{
			ScriptQueueTable::markCompleted($queueId);
			return self::FINISH_EXECUTION;
		}

		$script = Manager::getById($scriptId);

		if (!$script)
		{
			ScriptQueueTable::delete($queueId);
			return self::FINISH_EXECUTION;
		}

		$document = ScriptQueueTable::getNextQueuedDocument($queueId);

		if (!$document)
		{
			ScriptQueueTable::markCompleted($queueId);
			return self::FINISH_EXECUTION;
		}

		ScriptQueueTable::markExecuting($queueId);
		return $this->executeDocument($document, $script);
	}

	private function executeDocument(EO_ScriptQueueDocument $document, EO_Script $script)
	{
		$document->setStatus(Status::EXECUTING)->save();

		$document->fillQueue();
		$queue = $document->getQueue();
		$documentType = $documentId = [$script->getModuleId(), $script->getEntity(), $script->getDocumentType()];
		$documentId[2] = $document->getDocumentId();

		$workflowId = null;
		$errors = [];

		$canStart = \CBPDocument::canUserOperateDocument(
			\CBPCanUserOperateOperation::StartWorkflow,
			$queue->getStartedBy(),
			$documentId
		);

		if ($canStart)
		{
			$startParameters = $queue->getWorkflowParameters();
			if (!is_array($startParameters))
			{
				$startParameters = [];
			}

			$startParameters[\CBPDocument::PARAM_TAGRET_USER] = $queue->getStartedBy();
			$startParameters[\CBPDocument::PARAM_USE_FORCED_TRACKING] = true;
			$startParameters[\CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT] = true;
			$startParameters[\CBPDocument::PARAM_DOCUMENT_TYPE] = $documentType;
			$startParameters[\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE] = \CBPDocumentEventType::Script;

			$workflowId = \CBPDocument::StartWorkflow($script->getWorkflowTemplateId(), $documentId, $startParameters, $errors);
		}
		else
		{
			$errors[] = ['message' => Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_QUEUE_CAN_START_ERROR')];
		}

		if ($workflowId)
		{
			$document->setWorkflowId($workflowId);
			$document->setStatus(Status::COMPLETED);
		}
		if ($errors)
		{
			$document->setStatus(Status::FAULT);
			$document->setStatusMessage(reset($errors)['message']);
		}

		$document->save();
		return self::CONTINUE_EXECUTION;
	}

	public static function getTitle()
	{
		return "Script queues";
	}
}