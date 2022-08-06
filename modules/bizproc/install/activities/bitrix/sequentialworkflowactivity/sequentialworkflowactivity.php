<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$runtime = CBPRuntime::GetRuntime();
$runtime->IncludeActivityFile('SequenceActivity');

class CBPSequentialWorkflowActivity extends CBPSequenceActivity implements IBPRootActivity
{
	private $documentId = [];
	private $workflowTemplateId = null;
	private $templateUserId = null;
	protected $documentType = [];

	private $workflowStatus = CBPWorkflowStatus::Created;

	private $customStatusMode = false;

	protected $arVariables = [];
	protected $arVariablesTypes = [];

	protected $arFieldTypes = [];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = ['Title' => '', 'Permission' => []];
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

	public function getTemplateUserId()
	{
		return $this->templateUserId;
	}

	public function setTemplateUserId($userId)
	{
		$this->templateUserId = (int) $userId;
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

			//Finalize workflow activities
			$this->workflow->FinalizeActivity($this);

			/** @var CBPTrackingService $trackingService */
			$trackingService = $this->workflow->GetService("TrackingService");
			if ($trackingService::shouldClearCompletedTracksOnly())
			{
				$trackingService->setCompletedByWorkflow($this->workflow->GetInstanceId());
			}
		}

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

	public function SetCustomStatusMode()
	{
		$this->customStatusMode = true;
	}

	public function ToString()
	{
		return $this->name.
			" [".get_class($this)."] (status=".
			CBPActivityExecutionStatus::Out($this->executionStatus).
			", result=".
			CBPActivityExecutionResult::Out($this->executionResult).
			", wfStatus=".
			CBPWorkflowStatus::Out($this->workflowStatus).
			", count(arEventsMap)=".
			count($this->arEventsMap).
			")";
	}

	public function Execute()
	{
		$stateService = $this->workflow->GetService("StateService");
		$stateService->SetState(
			$this->GetWorkflowInstanceId(),
			array(
				"STATE" => "InProgress",
				"TITLE" => GetMessage("BPSWA_IN_PROGRESS"),
				"PARAMETERS" => array()
			),
			$this->Permission
		);

		return parent::Execute();
	}

	protected function OnSequenceComplete()
	{
		parent::OnSequenceComplete();

		if (!$this->customStatusMode)
		{
			$stateService = $this->workflow->GetService("StateService");
			$stateService->SetState(
				$this->GetWorkflowInstanceId(),
				array(
					"STATE" => "Completed",
					"TITLE" => GetMessage("BPSWA_COMPLETED"),
					"PARAMETERS" => array()
				),
				false
			);
		}
	}
}