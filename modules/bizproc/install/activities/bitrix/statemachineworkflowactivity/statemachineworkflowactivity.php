<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPStateMachineWorkflowActivity
	extends CBPCompositeActivity
	implements IBPActivityEventListener
{
	private $documentId = array();
	private $workflowTemplateId = null;
	protected $documentType = array();

	private $workflowStatus = CBPWorkflowStatus::Created;

	private $customStatusMode = false;

	protected $arVariables = array();
	protected $arVariablesTypes = array();

	protected $arFieldTypes = array();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "InitialStateName" => "");
	}

	public function ToString()
	{
		return $this->name.
			" [".get_class($this)."] (status=".
			CBPActivityExecutionStatus::Out($this->executionStatus).
			", result=".
			CBPActivityExecutionResult::Out($this->executionResult).
			", workflowStatus=".
			"[".$this->workflowStatus."] ".
			CBPWorkflowStatus::Out($this->workflowStatus).
			")";
	}

	public function GetDocumentId()
	{
		return $this->documentId;
	}

	public function SetDocumentId($documentId)
	{
		$this->documentId = $documentId;
	}

	public function GetWorkflowTemplateId()
	{
		return $this->workflowTemplateId;
	}

	public function SetWorkflowTemplateId($workflowTemplateId)
	{
		$this->workflowTemplateId = $workflowTemplateId;
	}

	public function GetWorkflowStatus()
	{
		return $this->workflowStatus;
	}

	public function SetWorkflowStatus($status)
	{
		$this->workflowStatus = $status;

		if ($status == CBPWorkflowStatus::Running && $this->{CBPDocument::PARAM_USE_FORCED_TRACKING})
		{
			/** @var CBPTrackingService $trackingService */
			$trackingService = $this->workflow->GetService("TrackingService");
			$trackingService->setForcedMode($this->workflow->GetInstanceId());
		}
		if ($status == CBPWorkflowStatus::Completed || $status == CBPWorkflowStatus::Terminated)
		{
			$this->ClearVariables();
			$this->ClearProperties();

			/** @var CBPActivity $event */
			foreach ($this->arEventsMap as $eventName)
			{
				foreach ($eventName as $event)
				{
					if (method_exists($event, 'Cancel'))
						$event->Cancel();
				}
			}
			//Clean workflow subscriptions
			\Bitrix\Bizproc\SchedulerEventTable::deleteByWorkflow($this->workflow->GetInstanceId());
			//Finalize workflow activities
			$this->workflow->FinalizeActivity($this);
		}
		try
		{
			/**
			 * @var CBPDocumentService $documentService
			 */

			$documentService = $this->workflow->GetService("DocumentService");
			$documentService->onWorkflowStatusChange(
				$this->GetDocumentId(),
				$this->workflow->GetInstanceId(),
				$status,
				$this
			);

			/**
			 * @var CBPAllStateService $stateService
			 */

			$stateService = $this->workflow->GetService("StateService");
			$stateService->onStatusChange($this->workflow->GetInstanceId(), $status);
		}
		catch (Exception $e)
		{

		}
	}

	public function SetCustomStatusMode()
	{
		$this->customStatusMode = true;
	}

	public function Execute()
	{
		$initialStateActivity = $this->GetStateActivityByName($this->arProperties["InitialStateName"]);
		if ($initialStateActivity == null)
			throw new Exception("initialStateActivity");

		$initialStateActivity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($initialStateActivity);

		return CBPActivityExecutionStatus::Executing;
	}

	protected function GetStateActivityByName($name)
	{
		$activity = null;

		for ($i = 0, $cnt = count($this->arActivities); $i < $cnt; $i++)
		{
			if ($this->arActivities[$i]->GetName() == $name)
			{
				$activity = $this->arActivities[$i];
				break;
			}
		}

		return $activity;
	}

	public function OnEvent(CBPActivity $sender, $arEventParameters = array())
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);

		if (array_key_exists("NextStateName", $arEventParameters) && strlen($arEventParameters["NextStateName"]) > 0)
		{
			$nextStateActivity = $this->GetStateActivityByName($arEventParameters["NextStateName"]);
			if ($nextStateActivity == null)
				throw new Exception("nextStateActivity");

			$nextStateActivity->ReInitialize();
			$nextStateActivity->AddStatusChangeHandler(self::ClosedEvent, $this);
			$this->workflow->ExecuteActivity($nextStateActivity);
		}
		else
		{
			$this->workflow->CloseActivity($this);
		}
	}

	public function Cancel()
	{
		$flag = true;
		for ($i = 0, $cnt = count($this->arActivities); $i < $cnt; $i++)
		{
			$activity2 = $this->arActivities[$i];

			$flag = false;
			if ($activity2->executionStatus == CBPActivityExecutionStatus::Executing)
				$this->workflow->CancelActivity($activity2);
		}

		if (!$flag)
			return $this->executionStatus;

		return CBPActivityExecutionStatus::Closed;
	}

	/**
	* Returns available events for current state
	* 
	*/
	public function GetAvailableStateEvents()
	{
		for ($i = 0, $cnt = count($this->arActivities); $i < $cnt; $i++)
		{
			$activity2 = $this->arActivities[$i];
			if ($activity2->executionStatus == CBPActivityExecutionStatus::Executing)
				return $activity2->GetAvailableStateEvents();
		}

		return array();
	}

	public static function ValidateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = array();

		$child = "CBP".$childActivity;

		$bCorrect = false;
		while (strlen($child) > 0)
		{
			if ($child == "CBPStateActivity")
			{
				$bCorrect = true;
				break;
			}
			$child = get_parent_class($child);
		}

		if (!$bCorrect)
			$arErrors[] = array("code" => "WrongChildType", "message" => GetMessage("BPSMWA_INVALID_CHILD"));

		return array_merge($arErrors, parent::ValidateChild($childActivity, $bFirstChild));
	}
}
?>