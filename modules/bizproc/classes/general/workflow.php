<?php

use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;

/**
* Workflow instance.
*/
class CBPWorkflow
{
	private bool $isNew = false;
	private bool $isAbandoned = false;
	private string $instanceId;

	protected CBPRuntime $runtime;
	protected CBPWorkflowPersister $persister;

	protected ?CBPCompositeActivity $rootActivity;

	protected array $activitiesQueue = [];
	protected array $eventsQueue = [];

	private array $activitiesNamesMap = [];

	/************************  PROPERTIES  *******************************/

	public function getInstanceId()
	{
		return $this->instanceId;
	}

	/**
	 * @return CBPRuntime
	 */
	public function getRuntime()
	{
		return $this->runtime;
	}

	public function getRootActivity(): CBPCompositeActivity
	{
		return $this->rootActivity;
	}

	private function getWorkflowStatus()
	{
		return $this->rootActivity->getWorkflowStatus();
	}

	protected function setWorkflowStatus($newStatus)
	{
		$this->rootActivity->setWorkflowStatus($newStatus);
		$this->getRuntime()->onWorkflowStatusChanged($this->getInstanceId(), $newStatus);
		$this->syncStatus($newStatus);
	}

	public function getService($name)
	{
		return $this->runtime->getService($name);
	}

	public function getDocumentId()
	{
		return $this->rootActivity->getDocumentId();
	}

	public function getDocumentType()
	{
		return $this->rootActivity->getDocumentType();
	}

	public function getTemplateId(): int
	{
		return (int)$this->rootActivity->getWorkflowTemplateId();
	}

	public function getStartedBy(): ?int
	{
		$startedBy = (int)CBPHelper::stripUserPrefix($this->rootActivity->{\CBPDocument::PARAM_TAGRET_USER});

		return $startedBy ?: null;
	}

	public function getPersister(): CBPWorkflowPersister
	{
		return $this->persister;
	}

	/************************  CONSTRUCTORS  ****************************************************/

	/**
	* Public constructor initializes a new workflow instance with the specified ID.
	*
	* @param mixed $instanceId - ID of the new workflow instance.
	* @param mixed $runtime - Runtime object.
	*/
	public function __construct($instanceId, CBPRuntime $runtime)
	{
		if (!$instanceId)
		{
			throw new Exception("instanceId");
		}

		$this->instanceId = $instanceId;
		$this->runtime = $runtime;
		$this->persister = CBPWorkflowPersister::GetPersister();
	}

	/**
	 * Remove workflow object from serialized data
	 * @return array
	 */
	public function __sleep()
	{
		return [];
	}

	/************************  CREATE / LOAD WORKFLOW  ****************************************/

	public function initialize(
		CBPActivity $rootActivity,
		$documentId,
		$workflowParameters = [],
		$workflowVariablesTypes = [],
		$workflowParametersTypes = [],
		$workflowTemplateId = 0
	)
	{
		$this->rootActivity = $rootActivity;
		$rootActivity->setWorkflow($this);
		if (method_exists($rootActivity, 'setWorkflowTemplateId'))
		{
			$rootActivity->setWorkflowTemplateId($workflowTemplateId);
		}

		if (method_exists($rootActivity, 'setTemplateUserId'))
		{
			$rootActivity->setTemplateUserId(
				CBPWorkflowTemplateLoader::getTemplateUserId($workflowTemplateId)
			);
		}

		$arDocumentId = CBPHelper::parseDocumentId($documentId);

		$rootActivity->setDocumentId($arDocumentId);

		$documentService = $this->getService("DocumentService");
		$documentType = $workflowParameters[CBPDocument::PARAM_DOCUMENT_TYPE]
			?? $documentService->getDocumentType($arDocumentId)
		;

		unset($workflowParameters[CBPDocument::PARAM_DOCUMENT_TYPE]);

		if ($documentType !== null)
		{
			$rootActivity->setDocumentType($documentType);
			$rootActivity->setFieldTypes($documentService->getDocumentFieldTypes($documentType));
		}

		$rootActivity->setProperties($workflowParameters);

		$rootActivity->setVariablesTypes($workflowVariablesTypes);
		if (is_array($workflowVariablesTypes))
		{
			foreach ($workflowVariablesTypes as $k => $v)
			{
				$variableValue = $v["Default"] ?? null;
				if ($documentType && $fieldTypeObject = $documentService->getFieldTypeObject($documentType, $v))
				{
					$fieldTypeObject->setDocumentId($arDocumentId);
					$variableValue = $fieldTypeObject->internalizeValue('Variable', $variableValue);
				}

				//set defaults on start
				$rootActivity->setVariable($k, $variableValue);
			}
		}

		$rootActivity->setPropertiesTypes($workflowParametersTypes);
	}

	public function reload(CBPActivity $rootActivity)
	{
		$this->rootActivity = $rootActivity;
		$rootActivity->setWorkflow($this);

		switch ($this->getWorkflowStatus())
		{
			case CBPWorkflowStatus::Completed:
			case CBPWorkflowStatus::Terminated:
				throw new Exception("InvalidAttemptToLoad");
		}
	}

	/************************  EXECUTE WORKFLOW  ************************************************/

	/**
	* Starts new workflow instance.
	*
	*/
	public function start(): void
	{
		if ($this->getWorkflowStatus() !== CBPWorkflowStatus::Created)
		{
			throw new Exception("CanNotStartInstanceTwice");
		}

		$this->isNew = true;
		$this->run();
	}

	/**
	* Resume existing workflow.
	*
	*/
	public function resume(): void
	{
		if ($this->getWorkflowStatus() !== CBPWorkflowStatus::Suspended)
		{
			throw new Exception("CanNotResumeInstance");
		}

		$this->run();
	}

	private function run(): void
	{
		try
		{
			$this->setWorkflowStatus(CBPWorkflowStatus::Running);
			if ($this->isNew)
			{
				$this->rootActivity->setReadOnlyData(
					$this->rootActivity->pullProperties()
				);

				$this->initializeActivity($this->rootActivity);
				$this->executeActivity($this->rootActivity);
			}

			$this->runQueue();
		}
		catch (Exception $e)
		{
			$this->terminate($e);

			throw $e;
		}

		if ($this->rootActivity->executionStatus === CBPActivityExecutionStatus::Closed)
		{
			$this->setWorkflowStatus(CBPWorkflowStatus::Completed);
		}
		elseif ($this->getWorkflowStatus() === CBPWorkflowStatus::Running)
		{
			$this->setWorkflowStatus(CBPWorkflowStatus::Suspended);
		}

		$this->persister->saveWorkflow($this->rootActivity, true);
	}

	public function isNew()
	{
		return $this->isNew;
	}

	public function abandon(): void
	{
		$this->isAbandoned = true;
	}

	public function isAbandoned(): bool
	{
		return $this->isAbandoned;
	}

	public function isFinished(): bool
	{
		if ($this->isAbandoned())
		{
			return true;
		}

		return CBPWorkflowStatus::isFinished((int)$this->getWorkflowStatus());
	}

	/**********************  EXTERNAL EVENTS  **************************************************************/

	/**
	* Resume the workflow instance and transfer the specified event to it.
	*
	* @param string $eventName - Event name.
	* @param array $eventParameters - Event parameters.
	*/
	public function sendExternalEvent(string $eventName, array $eventParameters = []): void
	{
		$this->addEventToQueue($eventName, $eventParameters);

		if ($this->getWorkflowStatus() !== CBPWorkflowStatus::Running)
		{
			$this->resume();
		}
	}

	/***********************  SEARCH ACTIVITY BY NAME  ****************************************************/

	private function fillNameActivityMapInternal(CBPActivity $activity)
	{
		$this->activitiesNamesMap[$activity->getName()] = $activity;

		if ($activity instanceof \CBPCompositeActivity)
		{
			$arSubActivities = $activity->collectNestedActivities();
			foreach ($arSubActivities as $subActivity)
			{
				$this->fillNameActivityMapInternal($subActivity);
			}
		}
	}

	private function fillNameActivityMap()
	{
		if (!is_array($this->activitiesNamesMap))
		{
			$this->activitiesNamesMap = [];
		}

		if (count($this->activitiesNamesMap) > 0)
		{
			return;
		}

		$this->fillNameActivityMapInternal($this->rootActivity);
	}

	/**
	* Returns activity by its name.
	*
	* @param mixed $activityName - Activity name.
	* @return CBPActivity - Returns activity object or null if activity is not found.
	*/
	public function getActivityByName($activityName)
	{
		if ($activityName == '')
		{
			throw new Exception('activityName');
		}

		$activity = null;

		$this->fillNameActivityMap();

		if (array_key_exists($activityName, $this->activitiesNamesMap))
		{
			$activity = $this->activitiesNamesMap[$activityName];
		}

		return $activity;
	}

	/************************  ACTIVITY EXECUTION  *************************************************/

	/**
	* Initializes the specified activity by calling its method Initialize.
	*
	* @param CBPActivity $activity
	*/
	public function initializeActivity(CBPActivity $activity)
	{
		if ($activity->executionStatus !== CBPActivityExecutionStatus::Initialized)
		{
			throw new Exception("InvalidInitializingState");
		}

		$activity->initialize();
	}

	/**
	* Plans specified activity for execution.
	*
	* @param CBPActivity $activity - Activity object.
	* @param mixed $eventParameters - Optional parameters.
	*/
	public function executeActivity(CBPActivity $activity, array $eventParameters = [])
	{
		if ($activity->executionStatus !== CBPActivityExecutionStatus::Initialized)
		{
			throw new Exception("InvalidExecutionState");
		}

		$activity->setStatus(CBPActivityExecutionStatus::Executing, $eventParameters);
		$this->addItemToQueue([$activity, CBPActivityExecutorOperationType::Execute]);
	}

	/**
	* Close specified activity.
	*
	* @param CBPActivity $activity - Activity object.
	* @param mixed $arEventParameters - Optional parameters.
	*/
	public function closeActivity(CBPActivity $activity, $arEventParameters = [])
	{
		switch ($activity->executionStatus)
		{
			case CBPActivityExecutionStatus::Executing:
				$activity->markCompleted($arEventParameters);
				return;

			case CBPActivityExecutionStatus::Canceling:
				$activity->markCanceled($arEventParameters);
				return;

			case CBPActivityExecutionStatus::Closed:
				return;

			case CBPActivityExecutionStatus::Faulting:
				$activity->markFaulted($arEventParameters);
				return;
		}

		throw new Exception("InvalidClosingState");
	}

	/**
	* Cancel specified activity.
	*
	* @param CBPActivity $activity - Activity object.
	* @param mixed $arEventParameters - Optional parameters.
	*/
	public function cancelActivity(CBPActivity $activity, $arEventParameters = [])
	{
		if ($activity->executionStatus !== CBPActivityExecutionStatus::Executing)
		{
			throw new Exception("InvalidCancelingState");
		}

		$activity->setStatus(CBPActivityExecutionStatus::Canceling, $arEventParameters);
		$this->addItemToQueue(array($activity, CBPActivityExecutorOperationType::Cancel));
	}

	public function faultActivity(CBPActivity $activity, Exception $e, $arEventParameters = [])
	{
		if ($activity->executionStatus === CBPActivityExecutionStatus::Closed)
		{
			if ($activity->parent === null)
			{
				$this->Terminate($e);
			}
			else
			{
				$this->FaultActivity($activity->parent, $e, $arEventParameters);
			}
		}
		else
		{
			$activity->setStatus(CBPActivityExecutionStatus::Faulting);
			$this->addItemToQueue(array($activity, CBPActivityExecutorOperationType::HandleFault, $e));
		}
	}

	/************************  ACTIVITIES QUEUE  ***********************************************/

	private function addItemToQueue($item)
	{
		$this->activitiesQueue[] = $item;
	}

	protected function runQueue()
	{
		$canRun = $this->runStep();

		while ($canRun)
		{
			$canRun = $this->runStep();
		}
	}

	protected function runStep(): bool
	{
		if (empty($this->activitiesQueue))
		{
			$this->ProcessQueuedEvents();
		}

		$item = array_shift($this->activitiesQueue);
		if ($item === null)
		{
			return false;
		}

		try
		{
			$this->runQueuedItem($item[0], $item[1], (count($item) > 2 ? $item[2] : null));
		}
		catch (Exception $e)
		{
			$this->faultActivity($item[0], $e);

			if ($this->getWorkflowStatus() === CBPWorkflowStatus::Terminated)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @throws Exception
	 */
	private function runQueuedItem(CBPActivity $activity, $activityOperation, Exception $exception = null): void
	{
		match ($activityOperation)
		{
			CBPActivityExecutorOperationType::Execute => $this->runExecuteActivityOperation($activity),
			CBPActivityExecutorOperationType::Cancel => $this->runCancelActivityOperation($activity),
			CBPActivityExecutorOperationType::HandleFault => $this->runHandleFaultActivityOperation($activity, $exception),
		};
	}

	/**
	 * @param CBPActivity $activity
	 * @return void
	 * @throws Exception
	 */
	private function runExecuteActivityOperation(CBPActivity $activity): void
	{
		if ($activity->executionStatus !== CBPActivityExecutionStatus::Executing)
		{
			return;
		}

		$newStatus = CBPActivityExecutionStatus::Closed;
		if ($activity->isActivated())
		{
			/** @var CBPTrackingService $trackingService */
			$trackingService = $this->getService('TrackingService');
			$trackingService->write(
				$this->getInstanceId(),
				CBPTrackingType::ExecuteActivity,
				$activity->getName(),
				$activity->executionStatus,
				$activity->executionResult,
				$activity->getTitle(),
				''
			);
			$newStatus = $activity->execute();
		}

		if ($newStatus === CBPActivityExecutionStatus::Closed)
		{
			$this->closeActivity($activity);
		}
		elseif ($newStatus !== CBPActivityExecutionStatus::Executing)
		{
			throw new Exception('InvalidExecutionStatus');
		}
	}

	/**
	 * @param CBPActivity $activity
	 * @return void
	 * @throws Exception
	 */
	private function runCancelActivityOperation(CBPActivity $activity): void
	{
		if ($activity->executionStatus !== CBPActivityExecutionStatus::Canceling)
		{
			return;
		}

		/** @var CBPTrackingService $trackingService */
		$trackingService = $this->getService("TrackingService");
		$trackingService->write(
			$this->getInstanceId(),
			CBPTrackingType::CancelActivity,
			$activity->getName(),
			$activity->executionStatus,
			$activity->executionResult,
			$activity->getTitle()
		);

		$newStatus = $activity->cancel();

		if ($newStatus === CBPActivityExecutionStatus::Closed)
		{
			$this->closeActivity($activity);
		}
		elseif ($newStatus !== CBPActivityExecutionStatus::Canceling)
		{
			throw new Exception("InvalidExecutionStatus");
		}
	}

	/**
	 * @param CBPActivity $activity
	 * @param Exception|null $exception
	 * @return void
	 * @throws Exception
	 */
	private function runHandleFaultActivityOperation(CBPActivity $activity, ?Exception $exception): void
	{
		if ($activity->executionStatus !== CBPActivityExecutionStatus::Faulting)
		{
			return;
		}

		/** @var CBPTrackingService $trackingService */
		$trackingService = $this->getService("TrackingService");
		$trackingService->write(
			$this->getInstanceId(),
			CBPTrackingType::FaultActivity,
			$activity->getName(),
			$activity->executionStatus,
			$activity->executionResult,
			$activity->getTitle(),
			($exception ? ($exception->getCode() ? "[" . $exception->getCode() . "] " : '') . $exception->getMessage() : "")
		);

		$newStatus = $activity->handleFault($exception);

		if ($newStatus === CBPActivityExecutionStatus::Closed)
		{
			$this->closeActivity($activity);
		}
		elseif ($newStatus !== CBPActivityExecutionStatus::Faulting)
		{
			throw new Exception("InvalidExecutionStatus");
		}
	}

	public function terminate(Exception $e = null, $stateTitle = '')
	{
		CBPTaskService::deleteByWorkflow($this->getInstanceId(), \CBPTaskStatus::Running);

		$this->setWorkflowStatus(CBPWorkflowStatus::Terminated);

		$this->persister->SaveWorkflow($this->rootActivity, true);

		/** @var CBPStateService $stateService */
		$stateService = $this->GetService("StateService");
		$stateService->SetState(
			$this->instanceId,
			[
				"STATE" => "Terminated",
				"TITLE" => $stateTitle ?: GetMessage("BPCGWF_TERMINATED_MSGVER_1"),
				"PARAMETERS" => [],
			],
			false
		);

		if ($e)
		{
			/** @var CBPTrackingService $trackingService */
			$trackingService = $this->getService("TrackingService");
			$trackingService->write(
				$this->instanceId,
				CBPTrackingType::FaultActivity,
				"none",
				CBPActivityExecutionStatus::Faulting,
				CBPActivityExecutionResult::Faulted,
				GetMessage('BPCGWF_EXCEPTION_TITLE'),
				($e->getCode() ? "[" . $e->getCode() . "] " : '') . $e->getMessage()
			);
		}
	}

	/**
	 * @param CBPActivity $activity
	 * @throws CBPArgumentNullException
	 * @throws Exception
	 */
	public function finalizeActivity(CBPActivity $activity)
	{
		$activity->finalize();
	}

	/************************  EVENTS QUEUE  ********************************************************/

	private function addEventToQueue($eventName, $arEventParameters = array())
	{
		$this->eventsQueue[] = [$eventName, $arEventParameters];
	}

	private function processQueuedEvents()
	{
		while (true)
		{
			$event = array_shift($this->eventsQueue);
			if ($event === null)
			{
				return;
			}

			[$eventName, $eventParameters] = $event;

			$this->processQueuedEvent($eventName, $eventParameters);
		}
	}

	private function processQueuedEvent($eventName, $eventParameters = [])
	{
		if (!array_key_exists($eventName, $this->rootActivity->arEventsMap))
		{
			return;
		}

		foreach ($this->rootActivity->arEventsMap[$eventName] as $eventHandler)
		{
			if (!empty($eventParameters['DebugEvent']) && $eventHandler instanceof IBPActivityDebugEventListener)
			{
				$eventHandler->onDebugEvent($eventParameters);

				continue;
			}

			if ($eventHandler instanceof IBPActivityExternalEventListener)
			{
				$eventHandler->onExternalEvent($eventParameters);
			}
		}
	}

	private function syncStatus(int $status): void
	{
		if ($status < CBPWorkflowStatus::Completed) // skip Created and Running
		{
			return;
		}

		WorkflowUserTable::syncOnWorkflowUpdated($this, $status);
	}

	/**
	* Add new event handler to the specified event.
	*
	* @param mixed $eventName - Event name.
	* @param IBPActivityExternalEventListener $eventHandler - Event handler.
	*/
	public function addEventHandler($eventName, IBPActivityExternalEventListener $eventHandler)
	{
		if (!is_array($this->rootActivity->arEventsMap))
		{
			$this->rootActivity->arEventsMap = [];
		}

		if (!array_key_exists($eventName, $this->rootActivity->arEventsMap))
		{
			$this->rootActivity->arEventsMap[$eventName] = [];
		}

		$this->rootActivity->arEventsMap[$eventName][] = $eventHandler;
	}

	public function getEventsMap(): array
	{
		return is_array($this->rootActivity->arEventsMap) ? $this->rootActivity->arEventsMap : [];
	}

	/**
	* Remove the event handler from the specified event.
	*
	* @param mixed $eventName - Event name.
	* @param IBPActivityExternalEventListener $eventHandler - Event handler.
	*/
	public function removeEventHandler($eventName, IBPActivityExternalEventListener $eventHandler)
	{
		if (!is_array($this->rootActivity->arEventsMap))
		{
			$this->rootActivity->arEventsMap = [];
		}

		if (!array_key_exists($eventName, $this->rootActivity->arEventsMap))
		{
			$this->rootActivity->arEventsMap[$eventName] = [];
		}

		$idx = array_search($eventHandler, $this->rootActivity->arEventsMap[$eventName], true);
		if ($idx !== false)
		{
			unset($this->rootActivity->arEventsMap[$eventName][$idx]);
		}

		if (count($this->rootActivity->arEventsMap[$eventName]) <= 0)
		{
			unset($this->rootActivity->arEventsMap[$eventName]);
		}
	}

	public function isDebug(): bool
	{
		return false;
	}
}
