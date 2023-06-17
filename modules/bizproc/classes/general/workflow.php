<?php

/**
* Workflow instance.
*/
class CBPWorkflow
{
	private bool $isNew = false;
	private bool $isAbandoned = false;
	private string $instanceId = "";

	protected CBPRuntime $runtime;
	protected CBPWorkflowPersister $persister;

	/** @var CBPCompositeActivity */
	protected $rootActivity = null;

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

	private function getWorkflowStatus()
	{
		return $this->rootActivity->GetWorkflowStatus();
	}

	protected function setWorkflowStatus($newStatus)
	{
		$this->rootActivity->SetWorkflowStatus($newStatus);
		$this->GetRuntime()->onWorkflowStatusChanged($this->GetInstanceId(), $newStatus);
	}

	public function getService($name)
	{
		return $this->runtime->GetService($name);
	}

	public function getDocumentId()
	{
		return $this->rootActivity->GetDocumentId();
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
	* @return CBPWorkflow
	*/
	public function __construct($instanceId, CBPRuntime $runtime)
	{
		if ($instanceId == '')
			throw new Exception("instanceId");
		if (!$runtime)
			throw new Exception("runtime");

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
		return array();
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
		$rootActivity->SetWorkflow($this);
		if (method_exists($rootActivity, 'SetWorkflowTemplateId'))
		{
			$rootActivity->SetWorkflowTemplateId($workflowTemplateId);
		}

		if (method_exists($rootActivity, 'setTemplateUserId'))
		{
			$rootActivity->setTemplateUserId(
				CBPWorkflowTemplateLoader::getTemplateUserId($workflowTemplateId)
			);
		}

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$rootActivity->SetDocumentId($arDocumentId);

		$documentService = $this->GetService("DocumentService");
		$documentType = $workflowParameters[CBPDocument::PARAM_DOCUMENT_TYPE]
			?? $documentService->GetDocumentType($arDocumentId)
		;

		unset($workflowParameters[CBPDocument::PARAM_DOCUMENT_TYPE]);

		if ($documentType !== null)
		{
			$rootActivity->SetDocumentType($documentType);
			$rootActivity->SetFieldTypes($documentService->GetDocumentFieldTypes($documentType));
		}

		$rootActivity->SetProperties($workflowParameters);

		$rootActivity->SetVariablesTypes($workflowVariablesTypes);
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
				$rootActivity->SetVariable($k, $variableValue);
			}
		}

		$rootActivity->SetPropertiesTypes($workflowParametersTypes);
	}

	public function reload(CBPActivity $rootActivity)
	{
		$this->rootActivity = $rootActivity;
		$rootActivity->SetWorkflow($this);

		switch ($this->GetWorkflowStatus())
		{
			case CBPWorkflowStatus::Completed:
			case CBPWorkflowStatus::Terminated:
				throw new Exception("InvalidAttemptToLoad");
		}
	}

	public function onRuntimeStopped()
	{
		$workflowStatus = $this->GetWorkflowStatus();

		if ($workflowStatus == CBPWorkflowStatus::Suspended)
			return;

		if ($this->rootActivity->executionStatus == CBPActivityExecutionStatus::Closed)
		{
			$this->SetWorkflowStatus(CBPWorkflowStatus::Completed);
		}
		else
		{
			$workflowStatus = $this->GetWorkflowStatus();
			if ($workflowStatus == CBPWorkflowStatus::Running)
				$this->SetWorkflowStatus(CBPWorkflowStatus::Suspended);
		}

		$this->persister->SaveWorkflow($this->rootActivity, true);
	}

	/************************  EXECUTE WORKFLOW  ************************************************/

	/**
	* Starts new workflow instance.
	*
	*/
	public function start()
	{
		if ($this->GetWorkflowStatus() != CBPWorkflowStatus::Created)
			throw new Exception("CanNotStartInstanceTwice");

		$this->isNew = true;
		$this->SetWorkflowStatus(CBPWorkflowStatus::Running);

		$this->rootActivity->setReadOnlyData(
			$this->rootActivity->pullProperties()
		);

		try
		{
			$this->InitializeActivity($this->rootActivity);
			$this->ExecuteActivity($this->rootActivity);
			$this->RunQueue();
		}
		catch (Exception $e)
		{
			$this->Terminate($e);
			throw $e;
		}

		if ($this->rootActivity->executionStatus == CBPActivityExecutionStatus::Closed)
		{
			$this->SetWorkflowStatus(CBPWorkflowStatus::Completed);
		}
		else
		{
			$workflowStatus = $this->GetWorkflowStatus();
			if ($workflowStatus == CBPWorkflowStatus::Running)
				$this->SetWorkflowStatus(CBPWorkflowStatus::Suspended);
		}

		$this->persister->SaveWorkflow($this->rootActivity, true);
	}

	/**
	* Resume existing workflow.
	*
	*/
	public function resume()
	{
		if ($this->GetWorkflowStatus() != CBPWorkflowStatus::Suspended)
			throw new Exception("CanNotResumeInstance");

		try
		{
			$this->SetWorkflowStatus(CBPWorkflowStatus::Running);
			$this->RunQueue();
		}
		catch (Exception $e)
		{
			$this->Terminate($e);
			throw $e;
		}

		if ($this->rootActivity->executionStatus == CBPActivityExecutionStatus::Closed)
		{
			$this->SetWorkflowStatus(CBPWorkflowStatus::Completed);
		}
		else
		{
			$workflowStatus = $this->GetWorkflowStatus();
			if ($workflowStatus == CBPWorkflowStatus::Running)
				$this->SetWorkflowStatus(CBPWorkflowStatus::Suspended);
		}

		$this->persister->SaveWorkflow($this->rootActivity, true);
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

	/**********************  EXTERNAL EVENTS  **************************************************************/

	/**
	* Resume the workflow instance and transfer the specified event to it.
	*
	* @param mixed $eventName - Event name.
	* @param mixed $arEventParameters - Event parameters.
	*/
	public function sendExternalEvent($eventName, $arEventParameters = array())
	{
		$this->AddEventToQueue($eventName, $arEventParameters);
		$this->Resume();
	}

	/***********************  SEARCH ACTIVITY BY NAME  ****************************************************/

	private function fillNameActivityMapInternal(CBPActivity $activity)
	{
		$this->activitiesNamesMap[$activity->getName()] = $activity;

		if (is_a($activity, 'CBPCompositeActivity'))
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
		if ($activity == null)
			throw new CBPArgumentNullException("activity");

		if ($activity->executionStatus != CBPActivityExecutionStatus::Initialized)
			throw new Exception("InvalidInitializingState");

		$activity->Initialize();
	}

	/**
	* Plans specified activity for execution.
	*
	* @param CBPActivity $activity - Activity object.
	* @param mixed $arEventParameters - Optional parameters.
	*/
	public function executeActivity(CBPActivity $activity, $arEventParameters = array())
	{
		if ($activity == null)
			throw new Exception("activity");

		if ($activity->executionStatus != CBPActivityExecutionStatus::Initialized)
			throw new Exception("InvalidExecutionState");

		$activity->SetStatus(CBPActivityExecutionStatus::Executing, $arEventParameters);
		$this->AddItemToQueue(array($activity, CBPActivityExecutorOperationType::Execute));
	}

	/**
	* Close specified activity.
	*
	* @param CBPActivity $activity - Activity object.
	* @param mixed $arEventParameters - Optional parameters.
	*/
	public function closeActivity(CBPActivity $activity, $arEventParameters = array())
	{
		switch ($activity->executionStatus)
		{
			case CBPActivityExecutionStatus::Executing:
				$activity->MarkCompleted($arEventParameters);
				return;

			case CBPActivityExecutionStatus::Canceling:
				$activity->MarkCanceled($arEventParameters);
				return;

			case CBPActivityExecutionStatus::Closed:
				return;

			case CBPActivityExecutionStatus::Faulting:
				$activity->MarkFaulted($arEventParameters);
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
	public function cancelActivity(CBPActivity $activity, $arEventParameters = array())
	{
		if ($activity == null)
			throw new Exception("activity");

		if ($activity->executionStatus != CBPActivityExecutionStatus::Executing)
			throw new Exception("InvalidCancelingState");

		$activity->SetStatus(CBPActivityExecutionStatus::Canceling, $arEventParameters);
		$this->AddItemToQueue(array($activity, CBPActivityExecutorOperationType::Cancel));
	}

	public function faultActivity(CBPActivity $activity, Exception $e, $arEventParameters = array())
	{
		if ($activity->executionStatus == CBPActivityExecutionStatus::Closed)
		{
			if ($activity->parent == null)
				$this->Terminate($e);
			else
				$this->FaultActivity($activity->parent, $e, $arEventParameters);
		}
		else
		{
			$activity->SetStatus(CBPActivityExecutionStatus::Faulting);
			$this->AddItemToQueue(array($activity, CBPActivityExecutorOperationType::HandleFault, $e));
		}
	}

	/************************  ACTIVITIES QUEUE  ***********************************************/

	private function addItemToQueue($item)
	{
		array_push($this->activitiesQueue, $item);
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
		$this->ProcessQueuedEvents();

		$item = array_shift($this->activitiesQueue);
		if ($item === null)
		{
			return false;
		}

		try
		{
			$this->RunQueuedItem($item[0], $item[1], (count($item) > 2 ? $item[2] : null));
		}
		catch (Exception $e)
		{
			$this->FaultActivity($item[0], $e);

			if ($this->GetWorkflowStatus() == CBPWorkflowStatus::Terminated)
			{
				return false;
			}
		}

		return true;
	}

	private function runQueuedItem(CBPActivity $activity, $activityOperation, Exception $exception = null)
	{
		/** @var $trackingService CBPTrackingService */
		if ($activityOperation == CBPActivityExecutorOperationType::Execute)
		{
			if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
			{
				try
				{
					$trackingService = $this->GetService("TrackingService");
					$trackingService->Write($this->GetInstanceId(), CBPTrackingType::ExecuteActivity, $activity->GetName(), $activity->executionStatus, $activity->executionResult, ($activity->IsPropertyExists("Title") ? $activity->Title : ""), "");
					$newStatus = $activity->Execute();

					//analyse robots - Temporary, it is prototype
					if ($trackingService->isForcedMode($this->GetInstanceId()))
					{
						$activityType = mb_substr(get_class($activity), 3);
						if (!in_array($activityType, [
							'SequentialWorkflowActivity',
							'ParallelActivity',
							'SequenceActivity',
							'DelayActivity',
							'IfElseActivity',
							'IfElseBranchActivity'
						]))
						{
							/** @var \Bitrix\Bizproc\Service\Analytics $analyticsService */
							$analyticsService = $this->GetService("AnalyticsService");
							if ($analyticsService->isEnabled())
							{
								$analyticsService->write(
									$activity->GetDocumentId(),
									'robot_run',
									$activityType
								);
							}
						}
					}

					if ($newStatus == CBPActivityExecutionStatus::Closed)
						$this->CloseActivity($activity);
					elseif ($newStatus != CBPActivityExecutionStatus::Executing)
						throw new Exception("InvalidExecutionStatus");
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}
		}
		elseif ($activityOperation == CBPActivityExecutorOperationType::Cancel)
		{
			if ($activity->executionStatus == CBPActivityExecutionStatus::Canceling)
			{
				try
				{
					$trackingService = $this->GetService("TrackingService");
					$trackingService->Write($this->GetInstanceId(), CBPTrackingType::CancelActivity, $activity->GetName(), $activity->executionStatus, $activity->executionResult, ($activity->IsPropertyExists("Title") ? $activity->Title : ""), "");

					$newStatus = $activity->Cancel();

					if ($newStatus == CBPActivityExecutionStatus::Closed)
						$this->CloseActivity($activity);
					elseif ($newStatus != CBPActivityExecutionStatus::Canceling)
						throw new Exception("InvalidExecutionStatus");
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}
		}
		elseif ($activityOperation == CBPActivityExecutorOperationType::HandleFault)
		{
			if ($activity->executionStatus == CBPActivityExecutionStatus::Faulting)
			{
				try
				{
					$trackingService = $this->GetService("TrackingService");
					$trackingService->Write($this->GetInstanceId(), CBPTrackingType::FaultActivity, $activity->GetName(), $activity->executionStatus, $activity->executionResult, ($activity->IsPropertyExists("Title") ? $activity->Title : ""), ($exception != null ? ($exception->getCode()? "[".$exception->getCode()."] " : '').$exception->getMessage() : ""));

					$newStatus = $activity->HandleFault($exception);

					if ($newStatus == CBPActivityExecutionStatus::Closed)
						$this->CloseActivity($activity);
					elseif ($newStatus != CBPActivityExecutionStatus::Faulting)
						throw new Exception("InvalidExecutionStatus");
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}
		}
	}

	public function terminate(Exception $e = null, $stateTitle = '')
	{
		/** @var CBPTaskService $taskService */
		$taskService = $this->GetService("TaskService");
		$taskService->DeleteByWorkflow($this->GetInstanceId(), \CBPTaskStatus::Running);

		$this->SetWorkflowStatus(CBPWorkflowStatus::Terminated);

		$this->persister->SaveWorkflow($this->rootActivity, true);

		/** @var CBPStateService $stateService */
		$stateService = $this->GetService("StateService");
		$stateService->SetState(
			$this->instanceId,
			array(
				"STATE" => "Terminated",
				"TITLE" => $stateTitle ? $stateTitle : GetMessage("BPCGWF_TERMINATED"),
				"PARAMETERS" => array()
			),
			false//array()
		);

		if ($e != null)
		{
			$trackingService = $this->GetService("TrackingService");
			$trackingService->Write(
				$this->instanceId,
				CBPTrackingType::FaultActivity,
				"none",
				CBPActivityExecutionStatus::Faulting,
				CBPActivityExecutionResult::Faulted,
				GetMessage('BPCGWF_EXCEPTION_TITLE'),
				($e->getCode()? "[".$e->getCode()."] " : '').$e->getMessage()
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
		if ($activity == null)
			throw new CBPArgumentNullException("activity");

		//if ($activity->executionStatus != CBPActivityExecutionStatus::Closed)
		//	throw new Exception("InvalidFinalizingState");

		$activity->Finalize();
	}

	/************************  EVENTS QUEUE  ********************************************************/

	private function addEventToQueue($eventName, $arEventParameters = array())
	{
		array_push($this->eventsQueue, array($eventName, $arEventParameters));
	}

	private function processQueuedEvents()
	{
		while (true)
		{
			$arEvent = array_shift($this->eventsQueue);
			if ($arEvent == null)
				return;

			$eventName = $arEvent[0];
			$arEventParameters = $arEvent[1];

			$this->ProcessQueuedEvent($eventName, $arEventParameters);
		}
	}

	private function processQueuedEvent($eventName, $eventParameters = [])
	{
		if (!array_key_exists($eventName, $this->rootActivity->arEventsMap))
			return;

		foreach ($this->rootActivity->arEventsMap[$eventName] as $eventHandler)
		{
			if (!empty($eventParameters['DebugEvent']) && $eventHandler instanceof IBPActivityDebugEventListener)
			{
				$eventHandler->onDebugEvent($eventParameters);

				continue;
			}

			if ($eventHandler instanceof IBPActivityExternalEventListener)
			{
				$eventHandler->OnExternalEvent($eventParameters);
			}
		}
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
			$this->rootActivity->arEventsMap = array();

		if (!array_key_exists($eventName, $this->rootActivity->arEventsMap))
			$this->rootActivity->arEventsMap[$eventName] = array();

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
			$this->rootActivity->arEventsMap = array();

		if (!array_key_exists($eventName, $this->rootActivity->arEventsMap))
			$this->rootActivity->arEventsMap[$eventName] = array();

		$idx = array_search($eventHandler, $this->rootActivity->arEventsMap[$eventName], true);
		if ($idx !== false)
			unset($this->rootActivity->arEventsMap[$eventName][$idx]);

		if (count($this->rootActivity->arEventsMap[$eventName]) <= 0)
			unset($this->rootActivity->arEventsMap[$eventName]);
	}

	/*******************  UTILITIES  ***************************************************************/

	/**
	* Returns available events for current state of state machine workflow activity.
	*
	*/
	public function getAvailableStateEvents()
	{
		if (!is_a($this->rootActivity, "CBPStateMachineWorkflowActivity"))
			throw new Exception("NotAStateMachineWorkflow");

		return $this->rootActivity->GetAvailableStateEvents();
	}

	public function isDebug(): bool
	{
		return false;
	}
}
